<?php
require_once 'db.php';
$table = $_GET['table'] ?? '';
$allowed = ['branches', 'families', 'announcements', 'committee'];
if (!in_array($table, $allowed)) { echo json_encode(['count' => 0]); exit; }
try {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo json_encode(['count' => $count]);
} catch (Exception $e) { echo json_encode(['count' => 0]); }
?>