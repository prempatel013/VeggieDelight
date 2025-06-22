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
    
    // Get user preferences
    $stmt = $pdo->prepare("
        SELECT email_notifications, sms_notifications, newsletter,
               profile_visible, order_history, analytics
        FROM user_preferences 
        WHERE user_id = ?
    ");
    
    $stmt->execute([$user_id]);
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no preferences exist, return defaults
    if (!$preferences) {
        $preferences = [
            'email_notifications' => 0,
            'sms_notifications' => 0,
            'newsletter' => 0,
            'profile_visible' => 1,
            'order_history' => 1,
            'analytics' => 1
        ];
    }
    
    echo json_encode([
        'success' => true,
        'settings' => $preferences
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 