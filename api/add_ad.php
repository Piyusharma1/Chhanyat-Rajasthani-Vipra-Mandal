<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

try {
    // 1. Ensure table exists
    $createTableSql = "CREATE TABLE IF NOT EXISTS local_ads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        business_name VARCHAR(255) DEFAULT NULL,
        image_url VARCHAR(500) NOT NULL,
        link_url VARCHAR(500) DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        display_order INT DEFAULT 0,
        start_date DATE DEFAULT NULL,
        end_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($createTableSql);

    // Detect if content is JSON or Form-data
    $isFormData = !empty($_POST) || !empty($_FILES);
    $data = [];

    if ($isFormData) {
        $data = $_POST;
    } else {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
    }

    $title = trim($data['title'] ?? '');
    $businessName = trim($data['business_name'] ?? '') ?: null;
    $linkUrl = trim($data['link_url'] ?? '') ?: null;
    $phone = trim($data['phone'] ?? '') ?: null;
    $status = in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : 'active';
    $displayOrder = isset($data['display_order']) ? (int)$data['display_order'] : 0;
    $startDate = !empty($data['start_date']) ? $data['start_date'] : null;
    $endDate = !empty($data['end_date']) ? $data['end_date'] : null;

    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'Advertisement title is required']);
        exit;
    }

    $imageUrl = trim($data['image_url'] ?? '');

    // Handle File Upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/ads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['image'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

        if ($file['size'] > $maxFileSize) {
            echo json_encode(['success' => false, 'error' => 'Image size must be less than 5MB']);
            exit;
        }

        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Allowed image formats: JPG, PNG, WEBP']);
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('ad_', true) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $imageUrl = 'uploads/ads/' . $filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save uploaded image']);
            exit;
        }
    }

    if (empty($imageUrl)) {
        echo json_encode(['success' => false, 'error' => 'Banner image is required (upload a file or provide URL)']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO local_ads (title, business_name, image_url, link_url, phone, status, display_order, start_date, end_date) 
                           VALUES (:title, :business_name, :image_url, :link_url, :phone, :status, :display_order, :start_date, :end_date)");
    $stmt->execute([
        ':title' => $title,
        ':business_name' => $businessName,
        ':image_url' => $imageUrl,
        ':link_url' => $linkUrl,
        ':phone' => $phone,
        ':status' => $status,
        ':display_order' => $displayOrder,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);

    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'message' => 'Advertisement added successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
