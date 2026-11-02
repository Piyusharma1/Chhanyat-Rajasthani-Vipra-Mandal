<?php
require_once 'db.php';

$isAll = isset($_GET['all']) && $_GET['all'] === 'true';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
$offset = ($page - 1) * $limit;

try {
    if ($isAll) {
        $stmt = $pdo->query("SELECT * FROM branches ORDER BY id ASC");
        $data = $stmt->fetchAll();
        $total = count($data);
        $hasMore = false;
    } else {
        $stmt = $pdo->prepare("SELECT * FROM branches ORDER BY id ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $total = $pdo->query("SELECT COUNT(*) FROM branches")->fetchColumn();
        $hasMore = ($offset + $limit) < $total;
    }

    // 🔥 CRITICAL: Decode committee column (longtext → array)
    foreach ($data as &$row) {
        // Convert ID to string for frontend consistency
        $row['id'] = (string)$row['id'];
        
        // Handle committee column (longtext containing JSON)
        if (isset($row['committee'])) {
            // If it's a string, decode it
            if (is_string($row['committee']) && !empty($row['committee'])) {
                $decoded = json_decode($row['committee'], true);
                
                // Check if decode was successful
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $row['committee'] = $decoded;
                } else {
                    // If decode failed, try to fix common issues
                    $row['committee'] = [];
                }
            } 
            // If it's already an array (shouldn't happen with longtext, but just in case)
            elseif (is_array($row['committee'])) {
                // Keep as is
            } 
            // If null or empty
            else {
                $row['committee'] = [];
            }
        } else {
            $row['committee'] = [];
        }
    }

    // Return response
    if ($isAll) {
        echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([
            'data' => $data,
            'hasMore' => $hasMore,
            'total' => $total,
            'currentPage' => $page
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>