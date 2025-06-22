<?php
// API endpoint to get a single food item
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

// Suppress errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

try {
    $food_id = intval($_GET['id'] ?? 0);

    if ($food_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid food ID']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        SELECT f.id, f.title, f.description, f.price, f.image_path, f.category
        FROM food_items f
        WHERE f.id = ? AND f.is_available = 1
    ");
    $stmt->execute([$food_id]);
    $food = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($food) {
        echo json_encode([
            'success' => true,
            'food' => $food
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Food item not found']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 