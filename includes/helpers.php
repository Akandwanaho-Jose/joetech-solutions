<?php
// ── Output ────────────────────────────────────────────────────

// Escape output — always use this in views
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── Redirects ─────────────────────────────────────────────────

function redirect(string $path): void {
    header('Location: ' . SITE_URL . '/' . ltrim($path, '/'));
    exit;
}

function redirect_back(): void {
    $back = $_SERVER['HTTP_REFERER'] ?? SITE_URL;
    header('Location: ' . $back);
    exit;
}

// ── Flash messages ────────────────────────────────────────────

function flash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): string {
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function has_flash(string $key): bool {
    return isset($_SESSION['flash'][$key]);
}

// ── CSRF ──────────────────────────────────────────────────────

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(): void {
    $token = $_POST['_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Invalid request. Please go back and try again.');
    }
}

// ── Formatting ────────────────────────────────────────────────

function money(float $amount, string $currency = 'UGX'): string {
    return $currency . ' ' . number_format($amount, 0, '.', ',');
}

function date_fmt(string $datetime, string $fmt = 'd M Y'): string {
    return date($fmt, strtotime($datetime));
}

function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    return match(true) {
        $diff < 60     => 'just now',
        $diff < 3600   => (int)($diff/60) . 'm ago',
        $diff < 86400  => (int)($diff/3600) . 'h ago',
        $diff < 604800 => (int)($diff/86400) . 'd ago',
        default        => date('d M Y', strtotime($datetime)),
    };
}

function make_slug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function excerpt(string $text, int $words = 25): string {
    $w = explode(' ', strip_tags($text));
    return count($w) <= $words ? $text : implode(' ', array_slice($w, 0, $words)) . '…';
}

function read_time(string $text): int {
    return max(1, (int) ceil(str_word_count(strip_tags($text)) / 200));
}

// ── Input sanitisation ────────────────────────────────────────

function post(string $key, string $default = ''): string {
    return trim($_POST[$key] ?? $default);
}

function get(string $key, string $default = ''): string {
    return trim($_GET[$key] ?? $default);
}

function post_int(string $key, int $default = 0): int {
    return (int)($_POST[$key] ?? $default);
}

function get_int(string $key, int $default = 0): int {
    return (int)($_GET[$key] ?? $default);
}

// ── File upload ───────────────────────────────────────────────

function upload_image(array $file, string $folder): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK)           return false;
    if ($file['size'] > MAX_FILE_SIZE)               return false;
    if (!in_array($file['type'], ALLOWED_IMG))       return false;

    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $dir  = UPLOADS . '/' . $folder;

    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }

    $dest = $dir . '/' . $name;

    return move_uploaded_file($file['tmp_name'], $dest)
        ? $folder . '/' . $name
        : false;
}

// ── Pagination ────────────────────────────────────────────────

function paginate(string $sql, array $params, int $per_page, int $page): array {
    // Count total
    $count_sql = 'SELECT COUNT(*) FROM (' . $sql . ') AS _count';
    $total     = (int) db()->prepare($count_sql)->execute($params) ? : 0;
    $stmt = db()->prepare($count_sql);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $offset = ($page - 1) * $per_page;
    $rows   = db_all($sql . " LIMIT $per_page OFFSET $offset", $params);

    return [
        'data'      => $rows,
        'total'     => $total,
        'per_page'  => $per_page,
        'page'      => $page,
        'pages'     => (int) ceil($total / $per_page),
    ];
}

function pagination_links(array $p, string $base_url): string {
    if ($p['pages'] <= 1) return '';

    $sep  = str_contains($base_url, '?') ? '&' : '?';
    $html = '<div class="pagination">';

    if ($p['page'] > 1)
        $html .= '<a href="' . $base_url . $sep . 'page=' . ($p['page']-1) . '">&laquo; Prev</a>';

    for ($i = max(1, $p['page']-2); $i <= min($p['pages'], $p['page']+2); $i++) {
        $class = $i === $p['page'] ? ' class="active"' : '';
        $html .= "<a href=\"{$base_url}{$sep}page={$i}\"{$class}>{$i}</a>";
    }

    if ($p['page'] < $p['pages'])
        $html .= '<a href="' . $base_url . $sep . 'page=' . ($p['page']+1) . '">Next &raquo;</a>';

    return $html . '</div>';
}

// ── Order ref generator ───────────────────────────────────────

function order_ref(): string {
    return 'JT-' . date('Y') . '-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function repair_ref(): string {
    return 'REP-' . date('Ym') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function request_ref(): string {
    return 'REQ-' . date('Ym') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function app_settings(): array {
    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

    $settings = [];

    try {
        $rows = db_all("SELECT setting_key, setting_value, setting_type FROM site_settings ORDER BY id ASC");
        foreach ($rows as $row) {
            $value = $row['setting_value'];

            if (($row['setting_type'] ?? 'text') === 'boolean') {
                $value = in_array((string) $value, ['1', 'true', 'yes'], true);
            }

            $settings[$row['setting_key']] = $value;
        }
    } catch (Throwable $e) {
        $settings = [];
    }

    return $settings;
}

function site_setting(string $key, mixed $default = null): mixed {
    $settings = app_settings();
    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

function page_content_map(string $page_key): array {
    static $pages = [];

    if (array_key_exists($page_key, $pages)) {
        return $pages[$page_key];
    }

    $pages[$page_key] = [];

    try {
        $rows = db_all(
            "SELECT section_key, title, subtitle, body, json_data
             FROM page_content
             WHERE page_key = ?
               AND status = 'published'
             ORDER BY sort_order ASC, id ASC",
            [$page_key]
        );

        foreach ($rows as $row) {
            $data = [
                'title' => (string) ($row['title'] ?? ''),
                'subtitle' => (string) ($row['subtitle'] ?? ''),
                'body' => (string) ($row['body'] ?? ''),
            ];

            if (!empty($row['json_data'])) {
                $decoded = json_decode((string) $row['json_data'], true);
                if (is_array($decoded)) {
                    $data = array_merge($data, $decoded);
                }
            }

            $pages[$page_key][$row['section_key']] = $data;
        }
    } catch (Throwable $e) {
        $pages[$page_key] = [];
    }

    return $pages[$page_key];
}

function page_content(string $page_key, string $section_key, array $fallback = []): array {
    $sections = page_content_map($page_key);
    if (!isset($sections[$section_key])) {
        return $fallback;
    }

    return array_merge($fallback, $sections[$section_key]);
}
