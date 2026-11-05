<?php
require_once __DIR__ . '/require_auth.php';

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
$maxSize = 15 * 1024 * 1024; // 15MB
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
    
    require_once __DIR__ . '/webp_utils.php';

    // Generate unique WebP filename
    $newFileName = uniqid('img_', true) . '.webp';
    $destination = $albumDir . $newFileName;
    
    // Convert and save as WebP
    if (convertToWebp($fileTmpName, $destination)) {
        $uploadedCount++;
        $uploadedFiles[] = [
            'name' => $fileName,
            'saved_as' => $newFileName,
            'url' => 'uploads/gallery/' . $albumFolder . '/' . $newFileName,
            'size' => file_exists($destination) ? filesize($destination) : $fileSize
        ];
    } else {
        $errors[] = "$fileName: Failed to convert and save image as WebP";
    }
}

// Automatically create announcement if requested and at least one image uploaded
$announcementCreated = false;
$autoAnnounce = isset($_POST['create_announcement']) 
    ? filter_var($_POST['create_announcement'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false 
    : true; // Default true

if ($uploadedCount > 0 && $autoAnnounce) {
    try {
        // Ensure announcements table exists with priority and branch columns
        $pdo->exec("CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            date DATE NULL,
            link VARCHAR(255) NULL,
            priority VARCHAR(20) DEFAULT 'normal',
            branch VARCHAR(100) DEFAULT 'ALL',
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        try { $pdo->exec("ALTER TABLE announcements ADD COLUMN priority VARCHAR(20) DEFAULT 'normal'"); } catch (Exception $e) {}
        try { $pdo->exec("ALTER TABLE announcements ADD COLUMN branch VARCHAR(100) DEFAULT 'ALL'"); } catch (Exception $e) {}

        $announcementTitle = "गैलरी: " . $albumName;
        $announcementDesc = "फोटो गैलरी में '{$albumName}' एल्बम में {$uploadedCount} नई तस्वीरें जोड़ी गई हैं। सभी तस्वीरें देखने के लिए गैलरी पर जाएं।";
        $announcementLink = "gallery.php";

        // Check if an announcement for this album was already made today to avoid spamming multiple announcements in quick succession
        $checkStmt = $pdo->prepare("SELECT id FROM announcements WHERE title = :title AND date = CURDATE() ORDER BY id DESC LIMIT 1");
        $checkStmt->execute([':title' => $announcementTitle]);
        $existingAnnounce = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingAnnounce) {
            // Update existing announcement's description and timestamp
            $upStmt = $pdo->prepare("UPDATE announcements SET description = :desc, branch = :branch, createdAt = NOW() WHERE id = :id");
            $upStmt->execute([
                ':desc' => $announcementDesc,
                ':branch' => $branch,
                ':id' => $existingAnnounce['id']
            ]);
            $announcementCreated = true;
        } else {
            // Insert new announcement
            $insStmt = $pdo->prepare("INSERT INTO announcements (title, description, date, link, priority, branch, createdAt) 
                                      VALUES (:title, :desc, CURDATE(), :link, 'normal', :branch, NOW())");
            $insStmt->execute([
                ':title' => $announcementTitle,
                ':desc' => $announcementDesc,
                ':link' => $announcementLink,
                ':branch' => $branch
            ]);
            $announcementCreated = true;
        }
    } catch (Exception $e) {
        // Log/record announcement failure without blocking the image upload response
        $errors[] = "घोषणा बनाने में त्रुटि: " . $e->getMessage();
    }
}

// Return response
$response = [
    'success' => $uploadedCount > 0,
    'uploaded_count' => $uploadedCount,
    'total_files' => $fileCount,
    'album' => $albumName,
    'album_folder' => $albumFolder,
    'announcement_created' => $announcementCreated,
    'files' => $uploadedFiles
];

if (!empty($errors)) {
    $response['errors'] = $errors;
}

if ($uploadedCount === 0) {
    $response['error'] = 'No files were uploaded successfully. ' . implode(', ', $errors);
    http_response_code(400);
} else {
    log_activity('UPLOAD', 'gallery', $albumFolder, $albumName, [
        'uploaded_count' => $uploadedCount,
        'branch' => $branch,
        'announcement_created' => $announcementCreated
    ]);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>