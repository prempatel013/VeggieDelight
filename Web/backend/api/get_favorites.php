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
    
    // Get user's favorite dishes
    $stmt = $pdo->prepare("
        SELECT f.id, f.food_id, f.created_at,
               fd.title, fd.description, fd.price, fd.category, fd.image_path
        FROM favorites f
        JOIN food_items fd ON f.food_id = fd.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    
    $stmt->execute([$user_id]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'favorites' => $favorites
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 