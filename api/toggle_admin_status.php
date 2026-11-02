<?php
// api/toggle_admin_status.php
header('Content-Type: application/json; charset=utf-8');
require_once 'require_auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method Not Allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['is_active'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => '❌ अमान्य डेटा']));
}

$targetId = (int)$input['id'];
$isActive = (int)$input['is_active'] ? 1 : 0;
$currentAdminId = $_SESSION['admin_id'] ?? null;

if ($targetId === (int)$currentAdminId) {
    die(json_encode(['success' => false, 'error' => '❌ आप अपने स्वयं के अकाउंट का स्टेटस नहीं बदल सकते।']));
}

try {
    $stmt = $pdo->prepare("UPDATE admins SET is_active = :is_active WHERE id = :id");
    $stmt->execute([
        ':is_active' => $isActive,
        ':id'        => $targetId
    ]);

    $statusLabel = $isActive ? 'सक्रिय (Active)' : 'निष्क्रिय (Inactive)';

    echo json_encode([
        'success' => true,
        'message' => "✅ एडमिन स्टेटस बदल कर '{$statusLabel}' कर दिया गया है!"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'डाटाबेस त्रुटि: ' . $e->getMessage()
    ]);
}
?>
