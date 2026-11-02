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
    $isFormData = !empty($_POST) || !empty($_FILES);
    $data = $isFormData ? $_POST : (json_decode(file_get_contents('php://input'), true) ?: []);

    $id = isset($data['id']) ? (int)$data['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid Ad ID']);
        exit;
    }

    // Fetch existing ad
    $stmt = $pdo->prepare("SELECT * FROM local_ads WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        echo json_encode(['success' => false, 'error' => 'Advertisement not found']);
        exit;
    }

    // Quick status toggle
    if (isset($data['action']) && $data['action'] === 'toggle_status') {
        $newStatus = ($existing['status'] === 'active') ? 'inactive' : 'active';
        $updateStmt = $pdo->prepare("UPDATE local_ads SET status = :status WHERE id = :id");
        $updateStmt->execute([':status' => $newStatus, ':id' => $id]);
        echo json_encode([
            'success' => true,
            'message' => 'Status updated to ' . $newStatus,
            'new_status' => $newStatus
        ]);
        exit;
    }

    // Full update
    $title = isset($data['title']) ? trim($data['title']) : $existing['title'];
    $businessName = isset($data['business_name']) ? (trim($data['business_name']) ?: null) : $existing['business_name'];
    $linkUrl = isset($data['link_url']) ? (trim($data['link_url']) ?: null) : $existing['link_url'];
    $phone = isset($data['phone']) ? (trim($data['phone']) ?: null) : $existing['phone'];
    $placement = isset($data['placement']) && in_array($data['placement'], ['top', 'feed', 'bottom']) ? $data['placement'] : ($existing['placement'] ?? 'top');
    $status = isset($data['status']) && in_array($data['status'], ['active', 'inactive']) ? $data['status'] : $existing['status'];
    $displayOrder = isset($data['display_order']) ? (int)$data['display_order'] : (int)$existing['display_order'];
    $startDate = isset($data['start_date']) ? ($data['start_date'] ?: null) : $existing['start_date'];
    $endDate = isset($data['end_date']) ? ($data['end_date'] ?: null) : $existing['end_date'];
    $imageUrl = $existing['image_url'];

    // Handle new image upload if present
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/ads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['image'];
        $maxFileSize = 5 * 1024 * 1024;
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
            // Delete old image if it's in uploads/ads/
            if (!empty($existing['image_url']) && strpos($existing['image_url'], 'uploads/ads/') !== false) {
                $oldFile = '../' . $existing['image_url'];
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            $imageUrl = 'uploads/ads/' . $filename;
        }
    } elseif (!empty($data['image_url'])) {
        $imageUrl = trim($data['image_url']);
    }

    $updateSql = "UPDATE local_ads SET 
                    title = :title, 
                    business_name = :business_name, 
                    image_url = :image_url, 
                    link_url = :link_url, 
                    phone = :phone, 
                    placement = :placement, 
                    status = :status, 
                    display_order = :display_order, 
                    start_date = :start_date, 
                    end_date = :end_date 
                  WHERE id = :id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':title' => $title,
        ':business_name' => $businessName,
        ':image_url' => $imageUrl,
        ':link_url' => $linkUrl,
        ':phone' => $phone,
        ':placement' => $placement,
        ':status' => $status,
        ':display_order' => $displayOrder,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':id' => $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Advertisement updated successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
