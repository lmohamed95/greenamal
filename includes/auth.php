<?php
/**
 * GreenAmal · Admin authentication
 */

require_once __DIR__ . '/helpers.php';

function admin_user(): ?array {
    if (empty($_SESSION['admin_user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $user = db_one('SELECT id, email, first_name, last_name, role FROM admin_users WHERE id = ?', [$_SESSION['admin_user_id']]);
    }
    return $user;
}

function admin_logged_in(): bool {
    return admin_user() !== null;
}

function admin_require_login(): void {
    if (!admin_logged_in()) {
        redirect('login');
    }
}

function admin_login(string $email, string $password): bool {
    $user = db_one('SELECT id, password_hash FROM admin_users WHERE email = ?', [$email]);
    if (!$user) return false;
    if (!password_verify($password, $user['password_hash'])) return false;
    session_regenerate_id(true); // defeat session fixation across the privilege boundary
    $_SESSION['admin_user_id'] = (int) $user['id'];
    db_query('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
    return true;
}

function admin_logout(): void {
    unset($_SESSION['admin_user_id']);
    session_regenerate_id(true);
}
