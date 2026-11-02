<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

$table = 'committee';
try {
    // Self-healing migration to ensure display_order column exists
    try {
        $pdo->exec("ALTER TABLE committee ADD COLUMN display_order INT DEFAULT 0");
    } catch (Exception $e) {
        // Column already exists or ignored
    }

    $stmt = $pdo->query("SELECT * FROM $table ORDER BY display_order ASC, id ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $data]);
} catch (Exception $e) { 
    http_response_code(500); 
    echo json_encode(['error' => $e->getMessage()]); 
}
?>