<?php
// API endpoint to get reviews for a food item
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';

// Suppress errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

try {
    $food_id = intval($_GET['food_id'] ?? 0);

    if ($food_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid food ID']);
        exit;
    }

    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    $stmt = $connection->prepare("
        SELECT id, user_name, rating, comment, created_at
        FROM reviews
        WHERE food_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$food_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'reviews' => $reviews]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 