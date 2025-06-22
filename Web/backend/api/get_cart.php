<?php
// API endpoint to get cart items
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

try {
    require_once '../config/database.php';
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in',
            'cart' => [],
            'total' => 0
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get cart items from database
    $stmt = $pdo->prepare("
        SELECT c.id, c.quantity, 
               f.id as food_id, f.title, f.description, f.price, f.image_path
        FROM cart c
        JOIN food_items f ON c.food_id = f.id
        WHERE c.user_id = ? AND f.is_available = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $cart = [];
    $total = 0;
    
    foreach ($cartItems as $item) {
        $itemTotal = floatval($item['price']) * intval($item['quantity']);
        $total += $itemTotal;
        
        $cart[] = [
            'id' => intval($item['food_id']),
            'title' => $item['title'],
            'description' => $item['description'],
            'price' => floatval($item['price']),
            'quantity' => intval($item['quantity']),
            'image_path' => $item['image_path']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'cart' => $cart,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'cart' => [],
        'total' => 0
    ]);
}
?> 