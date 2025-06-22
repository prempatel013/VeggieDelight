<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get user profile information
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone, address, city, zipcode, created_at
        FROM users 
        WHERE id = ?
    ");
    
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    // Get user statistics
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_orders 
        FROM orders 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as favorites 
        FROM favorites 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $favorites = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as reviews 
        FROM reviews 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Combine user data with statistics
    $user['total_orders'] = $orders['total_orders'];
    $user['favorites'] = $favorites['favorites'];
    $user['reviews'] = $reviews['reviews'];
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 