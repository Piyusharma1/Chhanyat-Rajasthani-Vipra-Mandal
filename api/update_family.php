<?php
require_once 'db.php';
require_once 'require_auth.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    die(json_encode(['success' => false, 'error' => 'Invalid data']));
}

$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';

try {
    // If admin is restricted to a branch, verify target family belongs to that branch
    if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
        $checkStmt = $pdo->prepare("SELECT branch FROM families WHERE id = :id");
        $checkStmt->execute([':id' => $data['id']]);
        $existing = $checkStmt->fetch();

        if (!$existing || $existing['branch'] !== $adminBranch) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => '❌ आप केवल अपनी ही शाखा के परिवार का डेटा अपडेट कर सकते हैं।']));
        }
        
        // Lock branch field to admin's branch
        $data['branch'] = $adminBranch;
    }

    $sets = [];
    $params = [':id' => $data['id']];
    
    foreach ($data as $key => $value) {
        if ($key === 'id') continue;
        
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        $sets[] = "`$key` = :$key";
        $params[":$key"] = $value;
    }
    
    $sql = "UPDATE families SET " . implode(', ', $sets) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An unexpected server error occurred.']);
}
?>