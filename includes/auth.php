<?php
// Session helpers

function logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function admin_logged_in(): bool {
    return isset($_SESSION['staff_id']);
}

function current_user(): array|false {
    if (!logged_in()) {
        return false;
    }

    return db_one("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function current_staff(): array|false {
    if (!admin_logged_in()) {
        return false;
    }

    return db_one("SELECT * FROM staff WHERE id = ?", [$_SESSION['staff_id']]);
}

// Access guards

function require_login(): void {
    if (!logged_in()) {
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'];
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_staff(): void {
    if (!admin_logged_in()) {
        flash('error', 'Unauthorised. Please log in.');
        redirect('public/admin/login.php');
    }
}

function has_permission(string $key): bool {
    $perms = $_SESSION['staff_permissions'] ?? [];
    return in_array('all', $perms, true) || in_array($key, $perms, true);
}

function require_permission(string $key): void {
    require_staff();

    if (!has_permission($key)) {
        http_response_code(403);
        include INCLUDES . '/partials/403.php';
        exit;
    }
}

// Password helpers

function hash_password(string $plain): string {
    return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password(string $plain, string $hash): bool {
    return password_verify($plain, $hash);
}

// Token helpers

function generate_token(): string {
    return bin2hex(random_bytes(32));
}
