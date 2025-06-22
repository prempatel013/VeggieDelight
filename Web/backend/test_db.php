<?php
// Temporary database diagnostic script

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Attempting to connect to MySQL server...\n";

// Use credentials from our config, but connect WITHOUT specifying a database initially
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Assuming default XAMPP password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connection to MySQL server successful!\n\n";

    echo "Listing available databases:\n";
    $databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);

    echo "---------------------------------\n";
    foreach ($databases as $database) {
        echo "- " . $database . "\n";
    }
    echo "---------------------------------\n\n";

    if (in_array('veggiedelight', $databases)) {
        echo "SUCCESS: The 'veggiedelight' database was found.\n";
        echo "You can now try running the main setup script again.\n";
    } else {
        echo "ERROR: The 'veggiedelight' database was NOT found.\n";
        echo "Please go to phpMyAdmin and create a database with that exact name.\n";
    }

} catch (\PDOException $e) {
    echo "\n----------------------------------------\n";
    echo "CRITICAL ERROR: Could not connect to the MySQL server.\n";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "----------------------------------------\n";
    echo "Please check:\n";
    echo "1. Is MySQL running in the XAMPP control panel?\n";
    echo "2. Are the username ('root') and password (empty) correct?\n";
}
?> 