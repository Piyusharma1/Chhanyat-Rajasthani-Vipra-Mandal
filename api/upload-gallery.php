<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 🔐 Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Please login again']);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once 'db.php';

// Get album name
$albumName = isset($_POST['album']) ? trim($_POST['album']) : '';
if (empty($albumName)) {
    echo json_encode(['success' => false, 'error' => 'Album name is required']);
    exit;
}

// Branch handling
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
    $branch = $adminBranch;
} else {
    $branch = trim($_POST['branch'] ?? 'ALL') ?: 'ALL';
}

// Sanitize album folder name (remove special characters, keep Hindi + alphanumeric)
$albumFolder = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $albumName);
$albumFolder = preg_replace('/\s+/', '_', $albumFolder);
$albumFolder = trim($albumFolder, '_');

if (empty($albumFolder)) {
    $albumFolder = 'album_' . time();
}

// Save album branch metadata in database
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS gallery_albums (
        folder VARCHAR(255) PRIMARY KEY,
        title VARCHAR(255),
        branch VARCHAR(100) DEFAULT 'ALL',
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $stmt = $pdo->prepare("INSERT INTO gallery_albums (folder, title, branch, createdAt) 
                           VALUES (:folder, :title, :branch, NOW()) 
                           ON DUPLICATE KEY UPDATE title = VALUES(title), branch = IF(VALUES(branch) != 'ALL', VALUES(branch), branch)");
    $stmt->execute([
        ':folder' => $albumFolder,
        ':title' => $albumName,
        ':branch' => $branch
    ]);
} catch (Exception $e) {
    // Continue even if database log fails
}

// Create uploads directory
$uploadBaseDir = __DIR__ . '/../uploads/gallery/';
$albumDir = $uploadBaseDir . $albumFolder . '/';

if (!is_dir($uploadBaseDir)) {
    if (!mkdir($uploadBaseDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        exit;
    }
}

if (!is_dir($albumDir)) {
    if (!mkdir($albumDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create album directory']);
        exit;
    }
}

// Check if files were uploaded
if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    echo json_encode(['success' => false, 'error' => 'No files uploaded']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$maxSize = 5 * 1024 * 1024; // 5MB
$uploadedCount = 0;
$errors = [];
$uploadedFiles = [];

// Process each file
$files = $_FILES['images'];
$fileCount = count($files['name']);

for ($i = 0; $i < $fileCount; $i++) {
    $fileName = $files['name'][$i];
    $fileTmpName = $files['tmp_name'][$i];
    $fileSize = $files['size'][$i];
    $fileError = $files['error'][$i];
    $fileType = $files['type'][$i];
    
    // Check for upload errors
    if ($fileError !== UPLOAD_ERR_OK) {
        $errors[] = "$fileName: Upload error code $fileError";
        continue;
    }
    
    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
        $errors[] = "$fileName: Invalid file type ($fileType)";
        continue;
    }
    
    // Validate file size
    if ($fileSize > $maxSize) {
        $errors[] = "$fileName: File too large (" . round($fileSize / 1024 / 1024, 2) . " MB)";
        continue;
    }
    
    // Generate unique filename
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('img_', true) . '.' . strtolower($ext);
    $destination = $albumDir . $newFileName;
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $destination)) {
        $uploadedCount++;
        $uploadedFiles[] = [
            'name' => $fileName,
            'saved_as' => $newFileName,
            'url' => 'uploads/gallery/' . $albumFolder . '/' . $newFileName,
            'size' => $fileSize
        ];
    } else {
        $errors[] = "$fileName: Failed to move file";
    }
}

// Return response
$response = [
    'success' => $uploadedCount > 0,
    'uploaded_count' => $uploadedCount,
    'total_files' => $fileCount,
    'album' => $albumName,
    'album_folder' => $albumFolder,
    'files' => $uploadedFiles
];

if (!empty($errors)) {
    $response['errors'] = $errors;
}

if ($uploadedCount === 0) {
    $response['error'] = 'No files were uploaded successfully. ' . implode(', ', $errors);
    http_response_code(400);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>