<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$galleryDir = __DIR__ . '/../uploads/gallery/';

// Self-healing table creation
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS gallery_albums (
        folder VARCHAR(255) PRIMARY KEY,
        title VARCHAR(255),
        branch VARCHAR(100) DEFAULT 'ALL',
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

// Fetch album branch mapping from DB
$branchMap = [];
try {
    $stmt = $pdo->query("SELECT folder, branch FROM gallery_albums");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $branchMap[$row['folder']] = $row['branch'];
    }
} catch (Exception $e) {}

$branchFilter = isset($_GET['branch']) ? trim($_GET['branch']) : null;
$adminBranch = $_SESSION['admin_branch'] ?? null;
$isAdminView = isset($_GET['admin_view']) && ($_GET['admin_view'] === 'true' || $_GET['admin_view'] === '1');

// If specific album requested - return images
if (isset($_GET['album'])) {
    $albumFolder = basename($_GET['album']); // security
    $albumPath = $galleryDir . $albumFolder;

    // Check branch permission for logged in branch admin (only in admin view)
    $albumBranch = $branchMap[$albumFolder] ?? 'ALL';
    if ($isAdminView && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $adminBranch && $adminBranch !== 'ALL') {
        if ($albumBranch !== $adminBranch) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => '❌ आप केवल अपनी ही शाखा के एल्बम देख सकते हैं।']);
            exit;
        }
    }

    if (!is_dir($albumPath)) {
        echo json_encode(['images' => []]);
        exit;
    }

    $files = glob($albumPath . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
    $images = [];

    foreach ($files as $file) {
        $images[] = [
            'url' => 'uploads/gallery/' . $albumFolder . '/' . basename($file),
            'name' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d', filemtime($file))
        ];
    }

    // Sort by date descending
    usort($images, fn($a, $b) => strcmp($b['date'], $a['date']));

    echo json_encode(['images' => $images, 'branch' => $albumBranch]);
    exit;
}

// Default: return all albums list (NO images - only metadata)
$albums = [];

if (is_dir($galleryDir)) {
    $dirs = array_filter(glob($galleryDir . '*'), 'is_dir');

    foreach ($dirs as $dir) {
        $folder = basename($dir);
        $albumBranch = $branchMap[$folder] ?? 'ALL';

        // Filter by branch if restricted
        if ($branchFilter && $branchFilter !== 'ALL') {
            if ($albumBranch !== $branchFilter && $albumBranch !== 'ALL') {
                continue;
            }
        } else if ($isAdminView && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $adminBranch && $adminBranch !== 'ALL') {
            if ($albumBranch !== $adminBranch) {
                continue;
            }
        }

        $files = glob($dir . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
        $count = count($files);

        // Get first image as cover
        $cover = '';
        if ($count > 0) {
            sort($files);
            $cover = 'uploads/gallery/' . $folder . '/' . basename($files[0]);
        }

        $albums[] = [
            'folder'  => $folder,
            'title'   => ucwords(str_replace(['-', '_'], ' ', $folder)),
            'count'   => $count,
            'cover'   => $cover,
            'branch'  => $albumBranch,
            'date'    => date('d M Y', filemtime($dir))
        ];
    }

    // Sort by date descending
    usort($albums, fn($a, $b) => strcmp($b['date'], $a['date']));
}

echo json_encode(['albums' => $albums]);
?>