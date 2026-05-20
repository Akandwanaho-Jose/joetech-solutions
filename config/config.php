<?php
function load_env_file(string $path): void {
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function env_value(string $key, mixed $default = null): mixed {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

load_env_file(dirname(__DIR__) . '/.env');
// ── Site settings ────────────────────────────────────────────
define('SITE_NAME',    'Joetech Solutions');
define('SITE_URL',     env_value('SITE_URL', 'http://localhost/joetech'));
define('SITE_EMAIL',   env_value('SITE_EMAIL', 'info@joetechsolutions.com'));

// ── Paths ────────────────────────────────────────────────────
define('ROOT',         dirname(__DIR__));
define('INCLUDES',     ROOT . '/includes');
define('UPLOADS',      ROOT . '/uploads');
define('UPLOAD_URL',   SITE_URL . '/uploads');

// ── Environment: 'development' or 'production' ───────────────
define('APP_ENV',      env_value('APP_ENV', 'development'));

// SMTP email settings
define('SMTP_HOST',       env_value('SMTP_HOST', ''));
define('SMTP_PORT',       (int) env_value('SMTP_PORT', 587));
define('SMTP_USER',       env_value('SMTP_USER', ''));
define('SMTP_PASS',       env_value('SMTP_PASS', ''));
define('SMTP_ENCRYPTION', strtolower((string) env_value('SMTP_ENCRYPTION', 'tls')));
define('SMTP_FROM',       env_value('SMTP_FROM', SITE_EMAIL));
define('SMTP_FROM_NAME',  env_value('SMTP_FROM_NAME', SITE_NAME));
define('NOTIFICATION_EMAIL', env_value('NOTIFICATION_EMAIL', SITE_EMAIL));

// ── Upload limits ────────────────────────────────────────────
define('MAX_FILE_SIZE', 5 * 1024 * 1024);   // 5MB
define('ALLOWED_IMG',   ['image/jpeg', 'image/png', 'image/webp']);
