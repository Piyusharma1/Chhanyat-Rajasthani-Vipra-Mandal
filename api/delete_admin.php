<?php
// api/delete_admin.php
header('Content-Type: application/json; charset=utf-8');
require_once 'require_auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method Not Allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => '❌ अमान्य आईडी']));
}

$targetId = (int)$input['id'];
$currentAdminId = $_SESSION['admin_id'] ?? null;

if ($targetId === (int)$currentAdminId) {
    die(json_encode(['success' => false, 'error' => '❌ आप अपने स्वयं के अकाउंट को डिलीट नहीं कर सकते।']));
}

try {
    // Check total admin count to prevent deleting the last admin
    $countStmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $totalAdmins = (int)$countStmt->fetchColumn();

    if ($totalAdmins <= 1) {
        die(json_encode(['success' => false, 'error' => '❌ सिस्टम में कम से कम एक एडमिन होना अनिवार्य है।']));
    }

    $adminStmt = $pdo->prepare("SELECT username, full_name, branch FROM admins WHERE id = :id");
    $adminStmt->execute([':id' => $targetId]);
    $targetAdmin = $adminStmt->fetch();

    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = :id");
    $stmt->execute([':id' => $targetId]);

    log_activity('DELETE', 'admin', (string)$targetId, $targetAdmin['full_name'] ?? ($targetAdmin['username'] ?? ''), [
        'username' => $targetAdmin['username'] ?? '',
        'branch'   => $targetAdmin['branch'] ?? ''
    ]);

    echo json_encode([
        'success' => true,
        'message' => '✅ एडमिन यूजर सफलतापूर्वक हटा दिया गया है!'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'डाटाबेस त्रुटि: ' . $e->getMessage()
    ]);
}
?>
