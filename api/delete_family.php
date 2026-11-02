<?php
require_once 'db.php';
require_once 'require_auth.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    die(json_encode(['success' => false, 'error' => 'Invalid data']));
}

$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';

try {
    $checkStmt = $pdo->prepare("SELECT branch, photo FROM families WHERE id = :id");
    $checkStmt->execute([':id' => $data['id']]);
    $existing = $checkStmt->fetch();

    if (!$existing) {
        die(json_encode(['success' => false, 'error' => 'Family not found']));
    }

    if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
        if ($existing['branch'] !== $adminBranch) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => '❌ आप केवल अपनी ही शाखा के परिवार का डेटा हटा सकते हैं।']));
        }
    }

    // Delete associated photo file from server
    if (!empty($existing['photo'])) {
        $photoUrl = $existing['photo'];
        $parsedUrl = parse_url($photoUrl, PHP_URL_PATH);
        if ($parsedUrl) {
            $uploadsPos = strpos($parsedUrl, '/uploads/');
            if ($uploadsPos !== false) {
                $relativePath = substr($parsedUrl, $uploadsPos); // e.g. /uploads/committee/file.jpg
                $absolutePath = realpath(__DIR__ . '/..') . $relativePath;
                if (file_exists($absolutePath) && is_file($absolutePath)) {
                    unlink($absolutePath);
                }
            }
        }
    }

    $stmt = $pdo->prepare("DELETE FROM families WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An unexpected server error occurred.']);
}
?>