<?php
// api/logout.php
// Properly invalidates session and removes PHPSESSID cookie from browser

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/audit_helper.php';
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    try {
        log_activity('LOGOUT', 'auth', (string)($_SESSION['admin_id'] ?? ''), $_SESSION['admin_full_name'] ?? '');
    } catch (Exception $e) {}
}

// Unset all session variables
$_SESSION = [];

// Invalidate and delete session cookie from browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"] ?? '/',
        $params["domain"] ?? '',
        $params["secure"] ?? false,
        $params["httponly"] ?? true
    );
}

// Destroy session data on server
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>