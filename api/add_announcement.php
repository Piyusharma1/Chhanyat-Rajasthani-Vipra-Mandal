<?php
require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$date = $input['date'] ?? null;
$link = trim($input['link'] ?? '') ?: null;
$priority = in_array($input['priority'] ?? '', ['urgent', 'event', 'normal']) ? $input['priority'] : 'normal';

// Branch handling
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
    // Branch admin can ONLY save announcements for their assigned branch
    $branch = $adminBranch;
} else {
    // Super admin can specify branch or default to 'ALL'
    $branch = trim($input['branch'] ?? 'ALL') ?: 'ALL';
}

if (empty($title) || empty($description)) {
    echo json_encode(['success' => false, 'error' => 'Title and description are required']);
    exit;
}

try {
    // Self-healing migration for priority and branch columns
    try {
        $pdo->exec("ALTER TABLE announcements ADD COLUMN priority VARCHAR(20) DEFAULT 'normal'");
    } catch (Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE announcements ADD COLUMN branch VARCHAR(100) DEFAULT 'ALL'");
    } catch (Exception $e) {}

    $stmt = $pdo->prepare("INSERT INTO announcements (title, description, date, link, priority, branch, createdAt) VALUES (:title, :desc, :date, :link, :priority, :branch, NOW())");
    $stmt->execute([
        ':title' => $title,
        ':desc' => $description,
        ':date' => $date ?: null,
        ':link' => $link,
        ':priority' => $priority,
        ':branch' => $branch
    ]);

    $newAnnId = (string)$pdo->lastInsertId();

    log_activity('CREATE', 'announcement', $newAnnId, $title, [
        'branch' => $branch,
        'priority' => $priority
    ]);

    echo json_encode([
        'success' => true,
        'id' => $newAnnId,
        'message' => 'Announcement added successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>