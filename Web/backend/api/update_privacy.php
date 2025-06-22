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
$profile_visible = isset($_POST['profile_visible']) ? 1 : 0;
$order_history = isset($_POST['order_history']) ? 1 : 0;
$analytics = isset($_POST['analytics']) ? 1 : 0;

try {
    $user_id = $_SESSION['user_id'];
    
    // Check if user preferences exist
    $stmt = $pdo->prepare("
        SELECT id FROM user_preferences 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        // Update existing preferences
        $stmt = $pdo->prepare("
            UPDATE user_preferences 
            SET profile_visible = ?, order_history = ?, analytics = ?
            WHERE user_id = ?
        ");
        $result = $stmt->execute([$profile_visible, $order_history, $analytics, $user_id]);
    } else {
        // Insert new preferences
        $stmt = $pdo->prepare("
            INSERT INTO user_preferences 
            (user_id, profile_visible, order_history, analytics) 
            VALUES (?, ?, ?, ?)
        ");
        $result = $stmt->execute([$user_id, $profile_visible, $order_history, $analytics]);
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Privacy settings updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update privacy settings'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 