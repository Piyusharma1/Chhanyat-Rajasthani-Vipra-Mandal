<?php
// api/check_auth.php
require_once __DIR__ . '/session_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Secure Origin handling (Credentials with wildcard '*' is invalid and insecure)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedHost = $_SERVER['HTTP_HOST'] ?? '';
if (!empty($origin)) {
    $parsedOrigin = parse_url($origin, PHP_URL_HOST);
    if ($parsedOrigin && ($parsedOrigin === $allowedHost || strpos($allowedHost, $parsedOrigin) !== false)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
    }
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';

// Self-healing schema check for admins.branch column
try {
    $pdo->exec("ALTER TABLE admins ADD COLUMN branch VARCHAR(100) DEFAULT 'ALL'");
} catch (Exception $e) {
    // Column already exists or error ignored
}

// Self-healing schema for login rate limiting / brute force tracking
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        username VARCHAR(100) NOT NULL,
        attempted_at DATETIME NOT NULL,
        is_successful TINYINT(1) DEFAULT 0,
        INDEX idx_ip_time (ip_address, attempted_at)
    )");
} catch (Exception $e) {}

// Get client IP safely
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $clientIp = trim($parts[0]);
}

// --- रास्ता 1: सिर्फ ऑथेंटिकेशन चेक करना (जब पेज नॉर्मल लोड होता है - GET Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        // चेक करें कि सेशन एक्सपायर तो नहीं हुआ (24 घंटे)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] < 86400)) {
            $branch = $_SESSION['admin_branch'] ?? 'ALL';
            echo json_encode([
                'isAdmin' => true,
                'username' => $_SESSION['admin_username'] ?? '',
                'full_name' => $_SESSION['admin_full_name'] ?? '',
                'branch' => $branch,
                'isSuperAdmin' => ($branch === 'ALL' || empty($branch))
            ]);
            exit;
        } else {
            // सेशन एक्सपायर हो गया, इसे सुरक्षित क्लियर करें
            $_SESSION = [];
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
            session_destroy();
        }
    }
    
    // अगर लॉग इन नहीं है या सेशन एक्सपायर हो चुका है
    echo json_encode(['isAdmin' => false]);
    exit;
}

// --- रास्ता 2: लॉगिन करने का प्रयास (जब फॉर्म सबमिट होता है - POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🛡️ Brute-Force Rate Limiting: Max 5 failed attempts in 15 minutes per IP
    try {
        $rateStmt = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts 
            WHERE ip_address = :ip 
              AND is_successful = 0 
              AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $rateStmt->execute([':ip' => $clientIp]);
        $failedAttempts = (int)$rateStmt->fetchColumn();

        if ($failedAttempts >= 5) {
            http_response_code(429);
            echo json_encode([
                'isAdmin' => false,
                'error' => '❌ बहुत अधिक गलत प्रयास। कृपया 15 मिनट बाद पुनः प्रयास करें।'
            ]);
            exit;
        }
    } catch (Exception $e) {
        // Continue if logging table check fails
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // वैलिडेट करें कि डेटा आया है या नहीं
    if (!$input || empty(trim($input['username'] ?? '')) || empty($input['password'] ?? '')) {
        echo json_encode([
            'isAdmin' => false,
            'error' => '❌ कृपया यूजरनेम और पासवर्ड दोनों दर्ज करें।'
        ]);
        exit;
    }

    $username = trim($input['username']);
    $password = $input['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username AND is_active = 1");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();
        $pwdHash = $admin['password'] ?? ($admin['password_hash'] ?? '');

        if ($admin && !empty($pwdHash) && password_verify($password, $pwdHash)) {
            // लॉगिन सफल
            @session_regenerate_id(true);
            $adminBranch = !empty($admin['branch']) ? $admin['branch'] : 'ALL';
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_full_name'] = $admin['full_name'];
            $_SESSION['admin_branch'] = $adminBranch;
            $_SESSION['login_time'] = time();
            
            // लास्ट लॉगिन टाइम व लॉग रिकॉर्ड करें
            try {
                $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
                $stmt->execute([':id' => $admin['id']]);

                $logStmt = $pdo->prepare("
                    INSERT INTO login_attempts (ip_address, username, attempted_at, is_successful)
                    VALUES (:ip, :user, NOW(), 1)
                ");
                $logStmt->execute([':ip' => $clientIp, ':user' => $username]);
            } catch (Exception $ex) {}

            session_write_close();
            
            echo json_encode([
                'isAdmin' => true,
                'username' => $admin['username'],
                'full_name' => $admin['full_name'],
                'branch' => $adminBranch,
                'isSuperAdmin' => ($adminBranch === 'ALL'),
                'message' => 'Login successful'
            ]);
            exit;
        } else {
            // गलत क्रेडेंशियल्स - लॉग रिकॉर्ड करें
            try {
                $logStmt = $pdo->prepare("
                    INSERT INTO login_attempts (ip_address, username, attempted_at, is_successful)
                    VALUES (:ip, :user, NOW(), 0)
                ");
                $logStmt->execute([':ip' => $clientIp, ':user' => $username]);
            } catch (Exception $ex) {}

            echo json_encode([
                'isAdmin' => false,
                'error' => '❌ गलत username या password'
            ]);
            exit;
        }
    } catch (Exception $e) {
        error_log('सर्वर त्रुटि: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'isAdmin' => false,
            'error' => 'An unexpected server error occurred.'
        ]);
        exit;
    }
}

// अगर कोई और ही रिक्वेस्ट मेथड यूज़ करने की कोशिश करे
http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']);
exit;
?>