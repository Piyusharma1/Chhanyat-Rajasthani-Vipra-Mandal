<?php
header('Content-Type: application/json; charset=utf-8');

// Load credentials from config file (never hardcoded here)
$configFile = __DIR__ . '/../config/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Database configuration file not found.',
        'hint'  => 'Copy config/config.example.php to config/config.php and fill in your credentials.'
    ]));
}
require_once $configFile;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;

// 1. First attempt: Production / configured credentials
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // 2. Fallback: Local development (XAMPP / local MySQL)
    $localAttempts = [
        ['host' => 'localhost',  'user' => 'root', 'pass' => '',     'db' => DB_NAME],
        ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '',     'db' => DB_NAME],
        ['host' => 'localhost',  'user' => 'root', 'pass' => 'root', 'db' => DB_NAME],
    ];

    foreach ($localAttempts as $attempt) {
        try {
            $dsn = 'mysql:host=' . $attempt['host'] . ';dbname=' . $attempt['db'] . ';charset=' . DB_CHARSET;
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
        'hint'  => 'Verify your MySQL server is running and credentials in config/config.php are correct.'
    ]));
}
?>
