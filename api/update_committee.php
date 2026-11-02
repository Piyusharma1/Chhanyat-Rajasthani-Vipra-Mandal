<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['branchId']) || !isset($data['committee'])) {
    die(json_encode(['success' => false, 'error' => 'Branch ID and committee required']));
}

try {
    $committee = json_encode($data['committee'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    $stmt = $pdo->prepare("UPDATE branches SET committee = :committee WHERE id = :id");
    $stmt->execute([
        ':committee' => $committee,
        ':id' => $data['branchId']
    ]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>