<?php
require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/db.php';

// Only Super Admin can delete Central Committee members
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
if ($adminBranch !== 'ALL') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => '❌ केवल सुपर एडमिन ही केंद्रीय कार्यकारिणी सदस्य को हटा सकते हैं।']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    die(json_encode(['success' => false, 'error' => 'ID required']));
}

try {
    $infoStmt = $pdo->prepare("SELECT name, designation FROM committee WHERE id = :id");
    $infoStmt->execute([':id' => $data['id']]);
    $existing = $infoStmt->fetch();

    $stmt = $pdo->prepare("DELETE FROM committee WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    
    if ($stmt->rowCount() === 0) {
        die(json_encode(['success' => false, 'error' => 'Member not found']));
    }

    log_activity('DELETE', 'committee', (string)$data['id'], $existing['name'] ?? '', [
        'designation' => $existing['designation'] ?? ''
    ]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>