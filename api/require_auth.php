<?php
// api/require_auth.php
// Enforces admin authentication and handles 24-hour expiration across all admin endpoints

require_once __DIR__ . '/session_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Unauthorized access. Please login.']));
}

// Check session expiration (24 hours = 86400 seconds)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 86400)) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Session expired. Please log in again.']));
}
?>
