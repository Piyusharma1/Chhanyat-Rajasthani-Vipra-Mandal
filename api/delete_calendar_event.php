<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'अनधिकृत पहुंच। कृपया एडमिन लॉगिन करें।']);
    exit;
}

require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['id'])) {
    echo json_encode(['success' => false, 'error' => 'इवेंट आईडी अनिवार्य है।']);
    exit;
}

$id = (int)$input['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'पर्व सफलतापूर्वक हटा दिया गया!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'इवेंट नहीं मिला या पहले ही हटा दिया गया है।'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
