<?php
// api/add_admin.php
header('Content-Type: application/json; charset=utf-8');
require_once 'require_auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method Not Allowed']));
}

// Only Super Admins can add new admins
if (isset($_SESSION['admin_branch']) && $_SESSION['admin_branch'] !== 'ALL') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => '❌ केवल सुपर एडमिन ही नया एडमिन जोड़ सकते हैं।']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => '❌ अमान्य डेटा (Invalid JSON payload)']));
}

$fullName = trim($input['full_name'] ?? '');
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
$isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
$branch   = trim($input['branch'] ?? 'ALL');
if (empty($branch)) {
    $branch = 'ALL';
}

// Input validations
if (empty($fullName)) {
    die(json_encode(['success' => false, 'error' => '❌ कृपया एडमिन का पूरा नाम दर्ज करें।']));
}

if (empty($username) || strlen($username) < 3) {
    die(json_encode(['success' => false, 'error' => '❌ यूजरनेम कम से कम 3 अक्षरों का होना चाहिए।']));
}

if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
    die(json_encode(['success' => false, 'error' => '❌ यूजरनेम में केवल अंग्रेजी अक्षर, अंक, (.), (_) या (-) उपयोग करें।']));
}

if (empty($password) || strlen($password) < 6) {
    die(json_encode(['success' => false, 'error' => '❌ पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।']));
}

try {
    // 1. Auto-create table if not exists (Self-healing schema)
    $createTableQuery = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        branch VARCHAR(100) DEFAULT 'ALL',
        last_login DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($createTableQuery);

    try {
        $pdo->exec("ALTER TABLE admins ADD COLUMN branch VARCHAR(100) DEFAULT 'ALL'");
    } catch (Exception $e) {
        // Column already exists
    }

    // 2. Check if username already exists
    $checkStmt = $pdo->prepare("SELECT id FROM admins WHERE username = :username");
    $checkStmt->execute([':username' => $username]);
    if ($checkStmt->fetch()) {
        die(json_encode(['success' => false, 'error' => '❌ यह यूजरनेम पहले से मौजूद है। कृपया दूसरा यूजरनेम चुनें।']));
    }

    // 3. Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 4. Insert new admin with branch
    $insertStmt = $pdo->prepare("INSERT INTO admins (username, password, full_name, is_active, branch) VALUES (:username, :password, :full_name, :is_active, :branch)");
    $insertStmt->execute([
        ':username'  => $username,
        ':password'  => $hashedPassword,
        ':full_name' => $fullName,
        ':is_active' => $isActive,
        ':branch'    => $branch
    ]);

    echo json_encode([
        'success' => true,
        'message' => '✅ नया एडमिन सफलतापूर्वक जोड़ दिया गया!',
        'id'      => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'डाटाबेस त्रुटि: ' . $e->getMessage()
    ]);
}
?>
