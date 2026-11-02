<?php
header('Content-Type: application/json; charset=utf-8');

// Production (Hostinger) settings
$prodHost = 'localhost';
$prodDb   = 'u581029428_websitedb';
$prodUser = 'u581029428_piyushdb';
$prodPass = '^O/t?>4NuUp9';
$charset  = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;

// 1. First attempt: Try Production Credentials
try {
    $dsn = "mysql:host=$prodHost;dbname=$prodDb;charset=$charset";
    $pdo = new PDO($dsn, $prodUser, $prodPass, $options);
} catch (\PDOException $e) {
    // 2. Fallback: Try Local Development MySQL (XAMPP / local service root)
    $localAttempts = [
        ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'db' => $prodDb],
        ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'db' => $prodDb],
        ['host' => 'localhost', 'user' => 'root', 'pass' => 'root', 'db' => $prodDb],
    ];

    foreach ($localAttempts as $attempt) {
        try {
            $dsn = "mysql:host=" . $attempt['host'] . ";dbname=" . $attempt['db'] . ";charset=$charset";
            $pdo = new PDO($dsn, $attempt['user'], $attempt['pass'], $options);
            break;
        } catch (\PDOException $localErr) {
            continue;
        }
    }
}

if (!$pdo) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Database connection failed',
        'hint' => 'Please verify your MySQL server is running and database credentials in api/db.php are correct.'
    ]));
}
?>