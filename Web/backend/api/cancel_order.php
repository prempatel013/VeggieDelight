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
$order_id = $input['order_id'] ?? null;

if (!$order_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID is required'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Check if order belongs to user and is pending
    $stmt = $pdo->prepare("
        SELECT id, status FROM orders 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found or cannot be cancelled'
        ]);
        exit;
    }
    
    // Update order status to cancelled
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'cancelled' 
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$order_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to cancel order'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 