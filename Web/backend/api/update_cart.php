<?php
// API endpoint to update cart item quantity
// Suppress any warnings or notices that might output HTML
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $food_id = intval($_POST['food_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    
    if ($food_id <= 0 || $quantity <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid food ID or quantity'
        ]);
        exit;
    }
    
    // Check if food exists and is available
    $stmt = $connection->prepare("SELECT id FROM foods WHERE id = ? AND is_available = 1");
    $stmt->execute([$food_id]);
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Food item not found or not available'
        ]);
        exit;
    }
    
    // For now, we'll use session-based cart since the database doesn't have a cart table
    // This is a simplified implementation
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if ($quantity > 0) {
        $_SESSION['cart'][$food_id] = $quantity;
    } else {
        unset($_SESSION['cart'][$food_id]);
    }
    
    $cartCount = array_sum($_SESSION['cart']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'cart_count' => $cartCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 