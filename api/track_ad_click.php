<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($id <= 0) {
    $rawInput = file_get_contents('php://input');
    if ($rawInput) {
        $json = json_decode($rawInput, true);
        if (isset($json['id'])) $id = (int)$json['id'];
    }
}

if ($id > 0 && $pdo) {
    try {
        $stmt = $pdo->prepare("UPDATE local_ads SET clicks = clicks + 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    } catch (\Exception $e) {
        // Silently return error
    }
}

echo json_encode(['success' => false]);
?>
