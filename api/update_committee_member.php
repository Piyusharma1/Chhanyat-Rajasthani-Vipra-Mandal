<?php
require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/db.php';

// Only Super Admin can edit Central Committee members
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
if ($adminBranch !== 'ALL') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => '❌ केवल सुपर एडमिन ही केंद्रीय कार्यकारिणी सदस्य संपादित कर सकते हैं।']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['name']) || !isset($data['designation'])) {
    die(json_encode(['success' => false, 'error' => 'ID, name and designation required']));
}

try {
    // If an old committee photo needs deletion when replaced (never delete family photos)
    if (isset($data['deletePhoto']) && !empty($data['deletePhoto'])) {
        $photoUrl = $data['deletePhoto'];
        if (strpos($photoUrl, 'uploads/families/') === false && (strpos($photoUrl, 'uploads/committee/') !== false || strpos($photoUrl, 'committee_') !== false)) {
            $baseUrl = 'https://chhanyatvipralatur.com/';
            $basePath = $_SERVER['DOCUMENT_ROOT'] . '/';
            $relativePath = $photoUrl;
            if (strpos($photoUrl, $baseUrl) === 0) {
                $relativePath = substr($photoUrl, strlen($baseUrl));
            } elseif (strpos($photoUrl, 'uploads/') !== false) {
                $relativePath = substr($photoUrl, strpos($photoUrl, 'uploads/'));
            }
            $fullPath = $basePath . ltrim($relativePath, '/');
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    // Ensure family_id column exists
    try {
        $pdo->exec("ALTER TABLE committee ADD COLUMN family_id VARCHAR(100) DEFAULT NULL");
    } catch (Exception $e) { }

    $stmt = $pdo->prepare("
        UPDATE committee 
        SET name = :name, 
            designation = :designation, 
            photo = :photo, 
            mobile = :mobile,
            family_id = :family_id
        WHERE id = :id
    ");
    
    $stmt->execute([
        ':name' => $data['name'],
        ':designation' => $data['designation'],
        ':photo' => $data['photo'] ?? '',
        ':mobile' => $data['mobile'] ?? '',
        ':family_id' => !empty($data['family_id']) ? $data['family_id'] : null,
        ':id' => $data['id']
    ]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
