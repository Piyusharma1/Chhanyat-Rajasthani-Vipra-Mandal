<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Log for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

$type = isset($_POST['type']) ? trim($_POST['type']) : (isset($_GET['type']) ? trim($_GET['type']) : '');
if ($type === 'family' || $type === 'families') {
    $folderName = 'families';
    $prefix = 'family_';
} else {
    $folderName = 'committee';
    $prefix = 'committee_';
}

$rawName = isset($_POST['name']) ? trim($_POST['name']) : 
          (isset($_POST['custom_name']) ? trim($_POST['custom_name']) : 
          (isset($_POST['head']) ? trim($_POST['head']) : ''));

// Clean custom name for filename: keep letters, vowel marks (\p{M}), numbers, and convert spaces/special chars to underscore
$cleanName = '';
if (!empty($rawName)) {
    $cleanName = strip_tags($rawName);
    $cleanName = preg_replace('/[^\p{L}\p{M}\p{N}\-_]+/u', '_', $cleanName);
    $cleanName = preg_replace('/_+/', '_', trim($cleanName, '_-'));
    if (mb_strlen($cleanName, 'UTF-8') > 60) {
        $cleanName = mb_substr($cleanName, 0, 60, 'UTF-8');
    }
}

$uploadDir = '../uploads/' . $folderName . '/';
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

require_once __DIR__ . '/webp_utils.php';

// Generate WebP filename using custom clean name if provided, else fallback to unique prefix
if (!empty($cleanName)) {
    $baseFilename = $cleanName;
    if (file_exists($uploadDir . $baseFilename . '.webp')) {
        $uniqueFilename = $baseFilename . '_' . date('Ymd_His') . '.webp';
    } else {
        $uniqueFilename = $baseFilename . '.webp';
    }
} else {
    $uniqueFilename = uniqid($prefix, true) . '.webp';
}

$uploadPath = $uploadDir . $uniqueFilename;

// Convert and save as WebP
if (convertToWebp($file['tmp_name'], $uploadPath)) {
    // Get full URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $encodedFilename = rawurlencode($uniqueFilename);
    $imageUrl = $protocol . '://' . $host . '/uploads/' . $folderName . '/' . $encodedFilename;
    $relativeUrl = 'uploads/' . $folderName . '/' . $encodedFilename;
    
    // Verify file exists
    if (!file_exists($uploadPath)) {
        echo json_encode([
            'success' => false,
            'message' => 'फ़ाइल सहेजी नहीं गई।'
        ]);
        exit;
    }
    
    $finalSize = filesize($uploadPath);
    echo json_encode([
        'success' => true,
        'message' => 'छवि सफलतापूर्वक अपलोड हुई और WebP में परिवर्तित की गई।',
        'url' => $imageUrl,
        'relative_url' => $relativeUrl,
        'path' => $uploadPath,
        'filename' => $uniqueFilename,
        'size' => $finalSize
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'फ़ाइल सहेजने में त्रुटि।',
        'debug' => error_get_last()
    ]);
}
?>