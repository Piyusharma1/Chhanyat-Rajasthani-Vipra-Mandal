<?php
require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['id'])) {
    echo json_encode(['success' => false, 'error' => 'इवेंट आईडी अनिवार्य है।']);
    exit;
}

$id = (int)$input['id'];
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
    $stmt = $pdo->prepare("UPDATE calendar_events 
                           SET title = :title, 
                               event_date = :event_date, 
                               branch = :branch, 
                               tithi = :tithi, 
                               description = :description, 
                               is_highlight = :is_highlight 
                           WHERE id = :id");
    $stmt->execute([
        ':title' => $title,
        ':event_date' => $eventDate,
        ':branch' => $branch,
        ':tithi' => $tithi,
        ':description' => $description,
        ':is_highlight' => $isHighlight,
        ':id' => $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'पर्व विवरण सफलतापूर्वक अपडेट किया गया!'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
