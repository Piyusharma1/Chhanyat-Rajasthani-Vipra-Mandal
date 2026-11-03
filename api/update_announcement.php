<?php
require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['id'])) {
    echo json_encode(['success' => false, 'error' => 'घोषणा आईडी (ID) अनिवार्य है।']);
    exit;
}

$id = (int)$input['id'];
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$date = !empty($input['date']) ? $input['date'] : null;
$link = trim($input['link'] ?? '') ?: null;
$priority = in_array($input['priority'] ?? '', ['urgent', 'event', 'normal']) ? $input['priority'] : 'normal';

if (empty($title) || empty($description)) {
    echo json_encode(['success' => false, 'error' => 'शीर्षक और विवरण दोनों अनिवार्य हैं।']);
    exit;
}

$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';

try {
    // Check if announcement exists
    $checkStmt = $pdo->prepare("SELECT branch FROM announcements WHERE id = :id");
    $checkStmt->execute([':id' => $id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        echo json_encode(['success' => false, 'error' => 'घोषणा नहीं मिली।']);
        exit;
    }

    // Branch admin permission check
    if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
        if ($existing['branch'] !== $adminBranch) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => '❌ आप केवल अपनी शाखा की घोषणा संपादित कर सकते हैं।']));
        }
        $branch = $adminBranch;
    } else {
        $branch = trim($input['branch'] ?? 'ALL') ?: 'ALL';
    }

    $updateSql = "UPDATE announcements 
                  SET title = :title, 
                      description = :description, 
                      date = :date, 
                      link = :link, 
                      priority = :priority, 
                      branch = :branch 
                  WHERE id = :id";
    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':date' => $date,
        ':link' => $link,
        ':priority' => $priority,
        ':branch' => $branch,
        ':id' => $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'घोषणा सफलतापूर्वक अपडेट की गई!'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
