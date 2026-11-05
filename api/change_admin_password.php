<?php
// api/change_admin_password.php
header('Content-Type: application/json; charset=utf-8');
require_once 'require_auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method Not Allowed']));
}

// Only Super Admins can change other admins' passwords
if (isset($_SESSION['admin_branch']) && $_SESSION['admin_branch'] !== 'ALL') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => '❌ केवल सुपर एडमिन ही अन्य एडमिन का पासवर्ड बदल सकते हैं।']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['id']) || empty($input['new_password'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => '❌ अमान्य डेटा।']));
}

$targetId = (int)$input['id'];
$newPassword = $input['new_password'];

if (strlen($newPassword) < 6) {
    die(json_encode(['success' => false, 'error' => '❌ नया पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।']));
}

try {
    // Check if admin exists
    $checkStmt = $pdo->prepare("SELECT username, full_name FROM admins WHERE id = :id");
    $checkStmt->execute([':id' => $targetId]);
    $admin = $checkStmt->fetch();

    if (!$admin) {
        die(json_encode(['success' => false, 'error' => '❌ एडमिन यूजर नहीं मिला।']));
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE admins SET password = :password WHERE id = :id");
    $stmt->execute([
        ':password' => $hashedPassword,
        ':id'       => $targetId
    ]);

    log_activity('PASSWORD_CHANGE', 'admin', (string)$targetId, $admin['full_name'], [
        'username' => $admin['username']
    ]);

    echo json_encode([
        'success' => true,
        'message' => "✅ '{$admin['full_name']}' (code: {$admin['username']}) का पासवर्ड सफलतापूर्वक बदल दिया गया है!"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'डाटाबेस त्रुटि: ' . $e->getMessage()
    ]);
}
?>
