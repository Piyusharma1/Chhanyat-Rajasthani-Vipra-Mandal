<?php
require_once 'db.php';
require_once 'require_auth.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    die(json_encode(['success' => false, 'error' => 'Invalid data']));
}

// Enforce branch restriction if admin is assigned to a specific branch
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
    $data['branch'] = $adminBranch;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO families (
            id, branch, head, mobile, photo, address, village, district, pincode,
            nativePlace, nativeTaluka, nativeDistrict,
            kuldaivat, kuldaivatPlace,
            businessName, businessType, businessAddress, businessMobile, businessYears,
            members
        ) VALUES (
            :id, :branch, :head, :mobile, :photo, :address, :village, :district, :pincode,
            :nativePlace, :nativeTaluka, :nativeDistrict,
            :kuldaivat, :kuldaivatPlace,
            :businessName, :businessType, :businessAddress, :businessMobile, :businessYears,
            :members
        )
    ");
    
    $stmt->execute([
        ':id' => $data['id'],
        ':branch' => $data['branch'] ?? '',
        ':head' => $data['head'] ?? '',
        ':mobile' => $data['mobile'] ?? '',
        ':photo' => $data['photo'] ?? '',
        ':address' => $data['address'] ?? '',
        ':village' => $data['village'] ?? '',
        ':district' => $data['district'] ?? '',
        ':pincode' => $data['pincode'] ?? '',
        ':nativePlace' => $data['nativePlace'] ?? '',
        ':nativeTaluka' => $data['nativeTaluka'] ?? '',
        ':nativeDistrict' => $data['nativeDistrict'] ?? '',
        ':kuldaivat' => $data['kuldaivat'] ?? '',
        ':kuldaivatPlace' => $data['kuldaivatPlace'] ?? '',
        ':businessName' => $data['businessName'] ?? '',
        ':businessType' => $data['businessType'] ?? '',
        ':businessAddress' => $data['businessAddress'] ?? '',
        ':businessMobile' => $data['businessMobile'] ?? '',
        ':businessYears' => $data['businessYears'] ?? '',
        ':members' => isset($data['members']) ? json_encode($data['members'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '[]'
    ]);
    
    echo json_encode(['success' => true, 'id' => $data['id']]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An unexpected server error occurred.']);
}
?>