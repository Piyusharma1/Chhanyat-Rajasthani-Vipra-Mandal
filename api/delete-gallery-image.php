<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['folder']) || !isset($data['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Folder and image path required']);
    exit;
}

$albumFolder = basename($data['folder']);
$imagePath = $data['image'];
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';

try {
    // Check branch permissions
    if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
        $stmt = $pdo->prepare("SELECT branch FROM gallery_albums WHERE folder = :folder");
        $stmt->execute([':folder' => $albumFolder]);
        $albumRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $albumBranch = $albumRow['branch'] ?? 'ALL';
        if ($albumBranch !== $adminBranch) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => '❌ आप केवल अपनी ही शाखा की गैलरी फोटो हटा सकते हैं।']);
            exit;
        }
    }

    // Sanitize image path
    $baseUploadDir = realpath(__DIR__ . '/../uploads/gallery/');
    $targetFile = realpath(__DIR__ . '/../' . ltrim($imagePath, '/'));

    if ($targetFile && strpos($targetFile, $baseUploadDir) === 0 && file_exists($targetFile)) {
        if (unlink($targetFile)) {
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete file']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'File not found or invalid path']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
