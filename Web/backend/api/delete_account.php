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

// Get form data
$password = $_POST['password'] ?? '';

if (empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Password is required'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Verify password
    $stmt = $pdo->prepare("
        SELECT password FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Password is incorrect'
        ]);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Delete user's favorites
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Delete user's reviews
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Delete user's cart items
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Delete user's preferences
        $stmt = $pdo->prepare("DELETE FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Delete user's orders (and order items)
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($orders)) {
            $placeholders = str_repeat('?,', count($orders) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)");
            $stmt->execute($orders);
            
            $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
        
        // Finally, delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Commit transaction
        $pdo->commit();
        
        // Destroy session
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 