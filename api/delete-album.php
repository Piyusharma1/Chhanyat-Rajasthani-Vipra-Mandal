<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 🔐 Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['folder'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Folder parameter is required']);
    exit;
}

$albumFolder = basename($data['folder']);
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
            echo json_encode(['success' => false, 'error' => '❌ आप केवल अपनी ही शाखा का Album हटा सकते हैं।']);
            exit;
        }
    }

    $baseUploadDir = realpath(__DIR__ . '/../uploads/gallery/');
    $targetDir = realpath($baseUploadDir . '/' . $albumFolder);

    if ($targetDir && strpos($targetDir, $baseUploadDir) === 0 && is_dir($targetDir)) {
        // Delete all files inside folder
        $files = glob($targetDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        // Remove folder
        rmdir($targetDir);

        // Delete from DB table
        $stmtDel = $pdo->prepare("DELETE FROM gallery_albums WHERE folder = :folder");
        $stmtDel->execute([':folder' => $albumFolder]);

        echo json_encode(['success' => true, 'message' => 'Album deleted successfully']);
    } else {
        // Even if directory is missing, clean up DB record if permission allowed
        $stmtDel = $pdo->prepare("DELETE FROM gallery_albums WHERE folder = :folder");
        $stmtDel->execute([':folder' => $albumFolder]);
        echo json_encode(['success' => true, 'message' => 'Album record removed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
