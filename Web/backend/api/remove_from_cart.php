<?php
// API endpoint to remove item from cart
// Suppress any warnings or notices that might output HTML
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    
    $food_id = intval($_POST['food_id'] ?? 0);
    
    if ($food_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid food ID'
        ]);
        exit;
    }
    
    // Remove item from session-based cart
    if (isset($_SESSION['cart'][$food_id])) {
        unset($_SESSION['cart'][$food_id]);
        $cartCount = array_sum($_SESSION['cart']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => $cartCount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found in cart'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 