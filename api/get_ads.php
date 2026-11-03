<?php
require_once __DIR__ . '/session_helper.php';
header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

try {
    $createTableSql = "CREATE TABLE IF NOT EXISTS local_ads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        business_name VARCHAR(255) DEFAULT NULL,
        image_url VARCHAR(500) NOT NULL,
        link_url VARCHAR(500) DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        position VARCHAR(50) DEFAULT 'above_calendar',
        status ENUM('active', 'inactive') DEFAULT 'active',
        display_order INT DEFAULT 0,
        start_date DATE DEFAULT NULL,
        end_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($createTableSql);

    // Self-healing migration for existing table: ensure `position` column exists
    try {
        $pdo->exec("ALTER TABLE local_ads ADD COLUMN position VARCHAR(50) DEFAULT 'above_calendar' AFTER phone");
    } catch (\Exception $e) {
        // Column already exists or table has it
    }

    // Auto-deactivate ads that have passed their end_date (do NOT delete, just mark inactive)
    try {
        $pdo->exec("UPDATE local_ads 
                    SET status = 'inactive' 
                    WHERE status = 'active' 
                      AND end_date IS NOT NULL 
                      AND end_date != '' 
                      AND end_date != '0000-00-00' 
                      AND end_date < CURDATE()");
    } catch (\Exception $e) {
        // Continue even if background auto-update fails
    }

    $isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    $adminView = isset($_GET['admin_view']) && ($_GET['admin_view'] === 'true' || $_GET['admin_view'] === '1');

    $whereClause = "";
    $params = [];

    if (!$isAdmin || !$adminView) {
        // Public view: only show active ads
        $whereClause = " WHERE status = 'active' 
                         AND (start_date IS NULL OR start_date = '' OR start_date = '0000-00-00' OR start_date <= CURDATE()) 
                         AND (end_date IS NULL OR end_date = '' OR end_date = '0000-00-00' OR end_date >= CURDATE()) ";
    }

    $orderBy = " ORDER BY display_order ASC, id DESC ";

    $sql = "SELECT * FROM local_ads" . $whereClause . $orderBy;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $ads,
        'total' => count($ads)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
