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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$food_id = $input['food_id'] ?? null;

if (!$food_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Food ID is required'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Remove from favorites
    $stmt = $pdo->prepare("
        DELETE FROM favorites 
        WHERE user_id = ? AND food_id = ?
    ");
    
    $result = $stmt->execute([$user_id, $food_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Removed from favorites'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove from favorites'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 