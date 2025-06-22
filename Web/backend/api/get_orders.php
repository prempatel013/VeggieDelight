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
    
    // Get user's orders with items
    $stmt = $pdo->prepare("
        SELECT o.id as order_id, o.total, o.status, o.created_at,
               oi.food_id, oi.quantity, oi.price,
               fd.title, fd.description
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN food_items fd ON oi.food_id = fd.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    
    $stmt->execute([$user_id]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group items by order
    $orders = [];
    foreach ($orderItems as $item) {
        $orderId = $item['order_id'];
        
        if (!isset($orders[$orderId])) {
            $orders[$orderId] = [
                'order_id' => $orderId,
                'total' => $item['total'],
                'status' => $item['status'],
                'created_at' => $item['created_at'],
                'items' => []
            ];
        }
        
        $orders[$orderId]['items'][] = [
            'food_id' => $item['food_id'],
            'title' => $item['title'],
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'orders' => array_values($orders)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 