<?php
// api/get_admins.php
header('Content-Type: application/json; charset=utf-8');
require_once 'require_auth.php';
require_once 'db.php';

try {
    // Self-healing table creation
    $createTableQuery = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        branch VARCHAR(100) DEFAULT 'ALL',
        last_login DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($createTableQuery);

    try {
        $pdo->exec("ALTER TABLE admins ADD COLUMN branch VARCHAR(100) DEFAULT 'ALL'");
    } catch (Exception $e) {
        // Column already exists
    }

    $stmt = $pdo->query("SELECT id, username, full_name, is_active, branch, last_login, created_at FROM admins ORDER BY id DESC");
    $admins = $stmt->fetchAll();

    $currentAdminId = $_SESSION['admin_id'] ?? null;

    foreach ($admins as &$admin) {
        $admin['is_current'] = ($admin['id'] == $currentAdminId);
        $admin['is_active'] = (int)$admin['is_active'];
        $admin['branch'] = !empty($admin['branch']) ? $admin['branch'] : 'ALL';
    }

    echo json_encode([
        'success' => true,
        'admins'  => $admins
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'डाटाबेस त्रुटि: ' . $e->getMessage()
    ]);
}
?>
