<?php
/**
 * Gallery Image Loader API
 * Scans the /gallery folder and returns image list as JSON
 *
 * Usage:
 * https://yourdomain.com/api/get_gallery.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');

// Configuration
$BASE_DIR = __DIR__ . '/../';
// Read from URL if provided, otherwise default to gallerys. Sanitize to prevent path traversal.
$FOLDER = isset($_GET['folder']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['folder']) : 'gallerys';
$ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

// Gallery path
$GALLERY_PATH = realpath($BASE_DIR . $FOLDER);

// Validate path
if (!$GALLERY_PATH || !is_dir($GALLERY_PATH)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Gallery folder not found',
        'data' => []
    ]);
    exit;
}

// Scan directory
$files = scandir($GALLERY_PATH);
$images = [];

foreach ($files as $file) {

    // Skip hidden files
    if ($file === '.' || $file === '..' || strpos($file, '.') === 0) {
        continue;
    }

    $filePath = $GALLERY_PATH . '/' . $file;

    // Skip directories
    if (is_dir($filePath)) {
        continue;
    }

    // Check extension
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (!in_array($extension, $ALLOWED_EXTENSIONS)) {
        continue;
    }

    // File info
    $fileSize = filesize($filePath);
    $fileTime = filemtime($filePath);
    $fileName = pathinfo($file, PATHINFO_FILENAME);

    // Generate URLs
    $relativePath = $FOLDER . '/' . rawurlencode($file);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : 'http';

    $fullUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . $relativePath;

    $images[] = [
        'id' => md5($file),
        'name' => $fileName,
        'filename' => $file,
        'url' => $relativePath,
        'fullUrl' => $fullUrl,
        'size' => $fileSize,
        'sizeFormatted' => formatBytes($fileSize),
        'modified' => date('Y-m-d H:i:s', $fileTime),
        'extension' => $extension,
        'title' => ucfirst(str_replace(['-', '_', '%20'], ' ', $fileName)),
        'description' => ''
    ];
}

// Sort newest first
usort($images, function ($a, $b) {
    return strtotime($b['modified']) - strtotime($a['modified']);
});

// Response
echo json_encode([
    'success' => true,
    'folder' => $FOLDER,
    'count' => count($images),
    'data' => $images
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

/**
 * Convert bytes to readable format
 */
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}
?>