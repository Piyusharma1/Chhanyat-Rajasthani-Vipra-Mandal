<?php
$isSecure = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1))
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['success' => false, 'error' => 'Unauthorized access.']));
}

// Check session expiration (24 hours)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 86400)) {
    session_unset();
    session_destroy();
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['success' => false, 'error' => 'Session expired. Please log in again.']));
}
?>
