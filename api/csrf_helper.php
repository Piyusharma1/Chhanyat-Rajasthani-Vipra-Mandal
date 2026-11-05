<?php
// api/csrf_helper.php
// Centralized CSRF Token Generation & Verification
// Protects state-changing requests (POST, PUT, DELETE, PATCH)

require_once __DIR__ . '/session_helper.php';

if (!function_exists('get_csrf_token')) {
    /**
     * Get or generate a cryptographically secure CSRF token bound to the current session.
     */
    function get_csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * Verify CSRF token from HTTP headers or payload.
     * Looks for X-CSRF-Token / X-XSRF-Token header or 'csrf_token' field in input.
     */
    function verify_csrf_token(?string $tokenToTest = null): bool {
        $validToken = $_SESSION['csrf_token'] ?? '';
        if (empty($validToken)) {
            return false;
        }

        if ($tokenToTest !== null && !empty($tokenToTest)) {
            return hash_equals($validToken, $tokenToTest);
        }

        // Check Headers
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? $_SERVER['HTTP_X_XSRF_TOKEN'] 
            ?? $_SERVER['HTTP_CSRF_TOKEN'] 
            ?? null;

        if (!empty($headerToken) && hash_equals($validToken, $headerToken)) {
            return true;
        }

        // Check POST form field
        if (!empty($_POST['csrf_token']) && hash_equals($validToken, $_POST['csrf_token'])) {
            return true;
        }

        return false;
    }
}

if (!function_exists('enforce_csrf')) {
    /**
     * Enforce CSRF token verification on state-changing requests.
     * Fails with HTTP 403 Forbidden if invalid.
     */
    function enforce_csrf(?string $customToken = null): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Only enforce on state-changing methods
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            if (!verify_csrf_token($customToken)) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => '❌ सुरक्षा सत्यापन विफल (CSRF Token Invalid or Missing)। कृपया पेज रीफ्रेश करें।'
                ]);
                exit;
            }
        }
    }
}
?>
