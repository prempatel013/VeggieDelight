<?php
// Standalone Database Setup Script for VeggieDelight

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Configuration ---
$host = '127.0.0.1';
$db   = 'food_delivery';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
// ---------------------

echo "Setting up VeggieDelight database...\n";

try {
    // 1. Connect to MySQL server (without selecting a database)
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Successfully connected to MySQL server.\n";

    // 2. Create the database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    echo "Database '$db' created or already exists.\n";
    
    // 3. Select the database
    $pdo->exec("USE `$db`");
    echo "Switched to database '$db'.\n";

    // 4. Read the SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    if ($sql === false) {
        throw new Exception("Could not read database.sql file.");
    }
    echo "Successfully read database.sql file.\n";

    // 5. Execute the SQL script
    $pdo->exec($sql);
    echo "Successfully executed SQL script.\n";
    
    echo "\n----------------------------------------\n";
    echo "Database setup completed successfully!\n";
    echo "----------------------------------------\n";
    
} catch (PDOException $e) {
    echo "\n--- DATABASE ERROR ---\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "------------------------\n";
} catch (Exception $e) {
    echo "\n--- SCRIPT ERROR ---\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "----------------------\n";
}
?> 