<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['branchName'])) {
    die(json_encode(['success' => false, 'error' => 'Branch name required']));
}

try {
    // Check if branch already exists
    $stmt = $pdo->prepare("SELECT id FROM branches WHERE branchName = :name");
    $stmt->execute([':name' => $data['branchName']]);
    
    if ($stmt->fetch()) {
        die(json_encode(['success' => false, 'error' => 'Branch already exists']));
    }
    
    $stmt = $pdo->prepare("INSERT INTO branches (branchName, committee) VALUES (:name, :committee)");
    $stmt->execute([
        ':name' => $data['branchName'],
        ':committee' => isset($data['committee']) ? json_encode($data['committee'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '[]'
    ]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>