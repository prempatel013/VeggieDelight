<?php
// API endpoint to get current user details
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';

// Suppress errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    $stmt = $connection->prepare("SELECT name, email, address, phone FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'A server error occurred.'
    ]);
}
?> 