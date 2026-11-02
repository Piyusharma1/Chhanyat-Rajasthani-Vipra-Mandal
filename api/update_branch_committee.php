<?php
require_once 'db.php';
require_once 'require_auth.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['branchId']) || !isset($data['committee'])) {
    die(json_encode(['success' => false, 'error' => 'Branch ID and committee required']));
}

$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';

try {
    // If admin is restricted to a branch, verify target branch name
    if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
        $checkStmt = $pdo->prepare("SELECT branchName FROM branches WHERE id = :id");
        $checkStmt->execute([':id' => $data['branchId']]);
        $branchRow = $checkStmt->fetch();

        if (!$branchRow || $branchRow['branchName'] !== $adminBranch) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => '❌ आप केवल अपनी ही शाखा की कार्यकारिणी अपडेट कर सकते हैं।']));
        }
    }
    // 🔥 Delete photo file if provided
    if (isset($data['deletePhoto']) && !empty($data['deletePhoto'])) {
        $photoUrl = $data['deletePhoto'];
        
        // Convert URL to file path
        // Example: https://chhanyatvipralatur.com/uploads/committee/committee_xxx.jpeg
        // Becomes: /home/username/public_html/uploads/committee/committee_xxx.jpeg
        
        $baseUrl = 'https://chhanyatvipralatur.com/';
        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/';
        
        if (strpos($photoUrl, $baseUrl) === 0) {
            // Remove base URL to get relative path
            $relativePath = substr($photoUrl, strlen($baseUrl));
            $fullPath = $basePath . $relativePath;
            
            // Check if file exists and delete it
            if (file_exists($fullPath)) {
                if (unlink($fullPath)) {
                    // File deleted successfully
                    error_log("✅ Deleted photo file: $fullPath");
                } else {
                    error_log("❌ Failed to delete photo file: $fullPath");
                }
            } else {
                error_log("⚠️ Photo file not found: $fullPath");
            }
        }
    }
    
    // Update committee in database
    $committee = json_encode($data['committee'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    $stmt = $pdo->prepare("UPDATE branches SET committee = :committee WHERE id = :id");
    $stmt->execute([
        ':committee' => $committee,
        ':id' => $data['branchId']
    ]);
    
    if ($stmt->rowCount() === 0) {
        die(json_encode(['success' => false, 'error' => 'Branch not found or no changes']));
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An unexpected server error occurred.']);
}
?>