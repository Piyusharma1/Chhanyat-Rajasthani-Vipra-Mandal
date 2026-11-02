<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    die(json_encode(['success' => false, 'error' => 'ID required']));
}

$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';

try {
    if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
        $checkStmt = $pdo->prepare("SELECT branch FROM announcements WHERE id = :id");
        $checkStmt->execute([':id' => $data['id']]);
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            die(json_encode(['success' => false, 'error' => 'Announcement not found']));
        }
        
        if ($row['branch'] !== $adminBranch) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => '❌ आप केवल अपनी ही शाखा की घोषणा हटा सकते हैं।']));
        }
    }

    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    
    if ($stmt->rowCount() === 0) {
        die(json_encode(['success' => false, 'error' => 'Announcement not found']));
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>