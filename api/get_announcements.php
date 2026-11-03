<?php
require_once __DIR__ . '/session_helper.php';
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    // Self-healing migration for branch column
    try {
        $pdo->exec("ALTER TABLE announcements ADD COLUMN branch VARCHAR(100) DEFAULT 'ALL'");
    } catch (Exception $e) {}

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
    $all = isset($_GET['all']) && $_GET['all'] === 'true';
    if ($all) {
        $limit = 1000;
    }
    $offset = ($page - 1) * $limit;

    $branchFilter = isset($_GET['branch']) ? trim($_GET['branch']) : null;
    $adminBranch = $_SESSION['admin_branch'] ?? null;
    $isAdminView = isset($_GET['admin_view']) && ($_GET['admin_view'] === 'true' || $_GET['admin_view'] === '1');

    $whereClause = "";
    $params = [];

    if ($branchFilter) {
        if ($branchFilter !== 'ALL') {
            $whereClause = " WHERE (branch = :branch OR branch = 'ALL' OR branch IS NULL OR branch = '') ";
            $params[':branch'] = $branchFilter;
        }
    } else if ($isAdminView && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $adminBranch && $adminBranch !== 'ALL') {
        $whereClause = " WHERE (branch = :admin_branch OR branch = 'ALL' OR branch IS NULL OR branch = '') ";
        $params[':admin_branch'] = $adminBranch;
    }

    // 1. Get total count
    $countSql = "SELECT COUNT(*) FROM announcements" . $whereClause;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // 2. Fetch paginated data
    $dataSql = "SELECT * FROM announcements" . $whereClause . " ORDER BY createdAt DESC, id DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($dataSql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Normalize fields
    $data = array_map(function($row) {
        if (!isset($row['priority']) || empty($row['priority'])) {
            $row['priority'] = 'normal';
        }
        if (!isset($row['branch']) || empty($row['branch'])) {
            $row['branch'] = 'ALL';
        }
        return $row;
    }, $data);
    
    $hasMore = ($page * $limit) < $total;

    echo json_encode([
        'success' => true, 
        'data' => $data,
        'currentPage' => $page,
        'hasMore' => $hasMore,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>