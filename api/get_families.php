<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAll = isset($_GET['all']) && $_GET['all'] === 'true';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 100;
$offset = ($page - 1) * $limit;
$branch = isset($_GET['branch']) ? $_GET['branch'] : null;

// Enforce branch restriction ONLY for admin panel requests (admin_view=true)
$isAdminView = isset($_GET['admin_view']) && ($_GET['admin_view'] === 'true' || $_GET['admin_view'] === '1');
if ($isAdminView && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
    if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
        $branch = $adminBranch;
    }
}

    $conditions = [];
    $params = [];

    if ($branch) {
        $conditions[] = "branch = :branch";
        $params[':branch'] = $branch;
    }

    $head = isset($_GET['head']) ? trim($_GET['head']) : '';
    if ($head !== '') {
        $conditions[] = "head LIKE :head";
        $params[':head'] = "%$head%";
    }

    $city = isset($_GET['city']) ? trim($_GET['city']) : '';
    if ($city !== '') {
        $conditions[] = "(village LIKE :city OR district LIKE :city)";
        $params[':city'] = "%$city%";
    }

    $member = isset($_GET['member']) ? trim($_GET['member']) : '';
    if ($member !== '') {
        $conditions[] = "members LIKE :member";
        $params[':member'] = "%$member%";
    }

    $whereClause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

    try {
        if ($isAll) {
            $sql = "SELECT * FROM families $whereClause ORDER BY id ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $families = $stmt->fetchAll();
            $total = count($families);
            $hasMore = false;
        } else {
            $sql = "SELECT * FROM families $whereClause ORDER BY id ASC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $families = $stmt->fetchAll();
            
            $countSql = "SELECT COUNT(*) FROM families $whereClause";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            $hasMore = ($offset + $limit) < $total;
        }

    // 🔥 Decode JSON members column
    foreach ($families as &$family) {
        if (isset($family['members']) && is_string($family['members'])) {
            $family['members'] = json_decode($family['members'], true);
        }
    }

    echo json_encode([
        'data' => $families,
        'hasMore' => $hasMore,
        'total' => $total,
        'currentPage' => $page
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['error' => 'An unexpected server error occurred.']);
}
?>