<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

// Only Super Admin can add Central Committee members
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
if ($adminBranch !== 'ALL') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => '❌ केवल सुपर एडमिन ही केंद्रीय कार्यकारिणी में सदस्य जोड़ सकते हैं।']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['name']) || !isset($data['designation'])) {
    die(json_encode(['success' => false, 'error' => 'Name and designation required']));
}

try {
    // Get next display_order
    $orderStmt = $pdo->query("SELECT COALESCE(MAX(display_order), 0) + 1 AS next_order FROM committee");
    $nextOrder = (int)$orderStmt->fetchColumn();

    $stmt = $pdo->prepare("
        INSERT INTO committee (name, designation, photo, mobile, display_order) 
        VALUES (:name, :designation, :photo, :mobile, :display_order)
    ");
    
    $stmt->execute([
        ':name' => $data['name'],
        ':designation' => $data['designation'],
        ':photo' => $data['photo'] ?? '',
        ':mobile' => $data['mobile'] ?? '',
        ':display_order' => $nextOrder
    ]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>