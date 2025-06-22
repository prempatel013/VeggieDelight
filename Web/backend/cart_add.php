<?php
// Suppress any warnings or notices that might output HTML
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$food_id = (int)($_POST['food_id'] ?? 0);

if ($food_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid food item']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get food details
    $stmt = $pdo->prepare('SELECT id, title, price FROM food_items WHERE id = ? AND is_available = 1');
    $stmt->execute([$food_id]);
    $food = $stmt->fetch();
    
    if (!$food) {
        echo json_encode(['success' => false, 'message' => 'Food item not found or unavailable']);
        exit;
    }
    
    // Check if item already exists in cart
    $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND food_id = ?');
    $stmt->execute([$user_id, $food_id]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $stmt = $pdo->prepare('UPDATE cart SET quantity = quantity + 1 WHERE id = ?');
        $stmt->execute([$existingItem['id']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare('INSERT INTO cart (user_id, food_id, quantity) VALUES (?, ?, 1)');
        $stmt->execute([$user_id, $food_id]);
    }
    
    // Get updated cart count
    $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $cartCount = $result['count'] ? intval($result['count']) : 0;
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart',
        'cart_count' => $cartCount
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 