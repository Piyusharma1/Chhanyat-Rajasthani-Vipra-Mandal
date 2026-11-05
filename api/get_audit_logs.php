<?php
// api/get_audit_logs.php
// Returns paginated audit logs for Super Admins
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit_helper.php';

// Only Super Admins can view audit logs
$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
if ($adminBranch !== 'ALL' && !empty($adminBranch)) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => '❌ केवल सुपर एडमिन ही ऑडिट लॉग्स देख सकते हैं।']));
}

try {
    ensure_audit_logs_table($pdo);

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;

    $category = trim($_GET['category'] ?? '');
    $action   = trim($_GET['action'] ?? '');
    $admin    = trim($_GET['admin'] ?? '');
    $search   = trim($_GET['search'] ?? '');

    $where = [];
    $params = [];

    if (!empty($category)) {
        $where[] = "category = :category";
        $params[':category'] = $category;
    }

    if (!empty($action)) {
        $where[] = "action = :action";
        $params[':action'] = $action;
    }

    if (!empty($admin)) {
        $where[] = "admin_username = :admin";
        $params[':admin'] = $admin;
    }

    if (!empty($search)) {
        $where[] = "(target_name LIKE :search OR target_id LIKE :search OR details LIKE :search OR ip_address LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs $whereSql");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Data query
    $dataStmt = $pdo->prepare("
        SELECT id, admin_id, admin_username, admin_name, action, category, target_id, target_name, details, ip_address, created_at 
        FROM audit_logs 
        $whereSql 
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset
    ");

    foreach ($params as $k => $v) {
        $dataStmt->bindValue($k, $v);
    }
    $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();

    $logs = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'     => true,
        'data'        => $logs,
        'total'       => $total,
        'page'        => $page,
        'limit'       => $limit,
        'totalPages'  => ceil($total / $limit)
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
