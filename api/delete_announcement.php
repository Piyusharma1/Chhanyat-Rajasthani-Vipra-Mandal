<?php
require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    die(json_encode(['success' => false, 'error' => 'ID required']));
}

$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';

try {
    $checkStmt = $pdo->prepare("SELECT title, branch FROM announcements WHERE id = :id");
    $checkStmt->execute([':id' => $data['id']]);
    $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        die(json_encode(['success' => false, 'error' => 'Announcement not found']));
    }
    
    if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
        if ($row['branch'] !== $adminBranch) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => '❌ आप केवल अपनी ही शाखा की घोषणा हटा सकते हैं।']));
        }
    }

    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    
    log_activity('DELETE', 'announcement', (string)$data['id'], $row['title'] ?? '', [
        'branch' => $row['branch'] ?? ''
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>