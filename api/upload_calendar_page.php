<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please login as admin.']);
    exit;
}

$month = isset($_POST['month']) ? (int)$_POST['month'] : 0;
if ($month < 1 || $month > 12) {
    echo json_encode(['success' => false, 'error' => 'Invalid month number (1-12 required).']);
    exit;
}

if (!isset($_FILES['page_image']) || $_FILES['page_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No image file uploaded or upload error.']);
    exit;
}

$file = $_FILES['page_image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, and WEBP images are allowed.']);
    exit;
}

$targetDir = '../uploads/calendar/pages/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Remove any existing extension for this month
foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
    $existing = $targetDir . $month . '.' . $ext;
    if (file_exists($existing)) {
        @unlink($existing);
    }
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($extension === 'jpeg') $extension = 'jpg';
$targetFile = $targetDir . $month . '.' . $extension;

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode([
        'success' => true,
        'message' => "माह {$month} का कैलेंडर पृष्ठ सफलतापूर्वक अपलोड हुआ!",
        'url' => "uploads/calendar/pages/{$month}.{$extension}"
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded image.']);
}
?>
