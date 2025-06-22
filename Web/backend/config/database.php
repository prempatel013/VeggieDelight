<?php
// Database configuration
// Please update with your local database credentials

$host = '127.0.0.1'; // Use 127.0.0.1 instead of localhost to avoid potential DNS lookup issues
$db   = 'food_delivery'; // Change this to your database name
$user = 'root'; // Change this to your database username
$pass = ''; // Change this to your database password
$charset = 'utf8mb4';

// Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If connection fails, throw an exception that can be caught by the calling script
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?> 