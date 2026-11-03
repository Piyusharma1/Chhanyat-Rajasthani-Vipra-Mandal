<?php
require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Ad ID']);
    exit;
}

try {
    // 1. Fetch ad details to remove physical image file if local
    $stmt = $pdo->prepare("SELECT image_url FROM local_ads WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) {
        echo json_encode(['success' => false, 'error' => 'Advertisement not found']);
        exit;
    }

    // 2. Delete database record
    $delStmt = $pdo->prepare("DELETE FROM local_ads WHERE id = :id");
    $delStmt->execute([':id' => $id]);

    // 3. Delete image file if stored locally in uploads/ads/
    if (!empty($ad['image_url']) && strpos($ad['image_url'], 'uploads/ads/') !== false) {
        $filePath = '../' . $ad['image_url'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Advertisement deleted successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
