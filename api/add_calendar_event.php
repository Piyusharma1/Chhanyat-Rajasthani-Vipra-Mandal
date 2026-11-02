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
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'अमान्य डेटा।']);
    exit;
}

$title = trim($input['title'] ?? '');
$eventDate = trim($input['event_date'] ?? '');
$branch = trim($input['branch'] ?? 'ALL') ?: 'ALL';
$tithi = trim($input['tithi'] ?? '') ?: null;
$description = trim($input['description'] ?? '') ?: null;
$isHighlight = !empty($input['is_highlight']) ? 1 : 0;

if (empty($title)) {
    echo json_encode(['success' => false, 'error' => 'पर्व / उत्सव का नाम (Title) अनिवार्य है।']);
    exit;
}

if (empty($eventDate)) {
    echo json_encode(['success' => false, 'error' => 'दिनांक (Date) अनिवार्य है।']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO calendar_events (title, event_date, branch, tithi, description, is_highlight) 
                           VALUES (:title, :event_date, :branch, :tithi, :description, :is_highlight)");
    $stmt->execute([
        ':title' => $title,
        ':event_date' => $eventDate,
        ':branch' => $branch,
        ':tithi' => $tithi,
        ':description' => $description,
        ':is_highlight' => $isHighlight
    ]);

    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'message' => 'नया पर्व / पंचांग तिथि सफलतापूर्वक जोड़ दी गई!'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
