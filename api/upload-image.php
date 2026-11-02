<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Log for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

$uploadDir = '../uploads/committee/';
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

// Create upload directory if it doesn't exist
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'अपलोड फ़ोल्डर बनाने में त्रुटि।',
            'debug' => 'Cannot create directory: ' . $uploadDir
        ]);
        exit;
    }
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'फ़ाइल बहुत बड़ी है।',
        UPLOAD_ERR_FORM_SIZE => 'फ़ाइल बहुत बड़ी है।',
        UPLOAD_ERR_PARTIAL => 'फ़ाइल आंशिक रूप से अपलोड हुई।',
        UPLOAD_ERR_NO_FILE => 'कोई फ़ाइल नहीं चुनी गई।',
        UPLOAD_ERR_NO_TMP_DIR => 'अस्थायी फ़ोल्डर नहीं मिला।',
        UPLOAD_ERR_CANT_WRITE => 'फ़ाइल लिखने में त्रुटि।',
    ];
    
    $errorCode = $_FILES['image']['error'] ?? 'unknown';
    $errorMsg = $errorMessages[$errorCode] ?? 'फ़ाइल अपलोड नहीं हुई।';
    
    echo json_encode([
        'success' => false,
        'message' => $errorMsg,
        'debug' => [
            'error_code' => $errorCode,
            'files' => $_FILES
        ]
    ]);
    exit;
}

$file = $_FILES['image'];

// Validate file size
if ($file['size'] > $maxFileSize) {
    echo json_encode([
        'success' => false,
        'message' => 'फ़ाइल का आकार 5MB से अधिक नहीं होना चाहिए।'
    ]);
    exit;
}

// Validate file type
$fileType = mime_content_type($file['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'message' => 'केवल JPG, PNG और WEBP फ़ाइलें अनुमत हैं।',
        'debug' => [
            'file_type' => $fileType,
            'allowed' => $allowedTypes
        ]
    ]);
    exit;
}

// Generate unique filename
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$uniqueFilename = uniqid('committee_', true) . '.' . $fileExtension;
$uploadPath = $uploadDir . $uniqueFilename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Get full URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $imageUrl = $protocol . '://' . $host . '/uploads/committee/' . $uniqueFilename;
    
    // Verify file exists
    if (!file_exists($uploadPath)) {
        echo json_encode([
            'success' => false,
            'message' => 'फ़ाइल सहेजी नहीं गई।'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'छवि सफलतापूर्वक अपलोड हुई।',
        'url' => $imageUrl,
        'path' => $uploadPath,
        'filename' => $uniqueFilename,
        'size' => $file['size']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'फ़ाइल सहेजने में त्रुटि।',
        'debug' => error_get_last()
    ]);
}
?>