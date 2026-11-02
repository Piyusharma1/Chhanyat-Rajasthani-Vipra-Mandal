<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

// Only Super Admin can reorder Central Committee members
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
if ($adminBranch !== 'ALL') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => '❌ केवल सुपर एडमिन ही केंद्रीय कार्यकारिणी का क्रम बदल सकते हैं।']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['order']) || !is_array($data['order'])) {
    die(json_encode(['success' => false, 'error' => 'Array of ordered member IDs required']));
}

try {
    // Self-healing migration to ensure display_order column exists
    try {
        $pdo->exec("ALTER TABLE committee ADD COLUMN display_order INT DEFAULT 0");
    } catch (Exception $e) {
        // Column already exists
    }

    $stmt = $pdo->prepare("UPDATE committee SET display_order = :order WHERE id = :id");
    
    $pdo->beginTransaction();
    foreach ($data['order'] as $index => $id) {
        $stmt->execute([
            ':order' => (int)$index,
            ':id' => $id
        ]);
    }
    $pdo->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
