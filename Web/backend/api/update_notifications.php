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
$email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
$sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
$push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
$newsletter = isset($_POST['newsletter']) ? 1 : 0;

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
            SET email_notifications = ?, sms_notifications = ?, 
                push_notifications = ?, newsletter = ?
            WHERE user_id = ?
        ");
        $result = $stmt->execute([$email_notifications, $sms_notifications, $push_notifications, $newsletter, $user_id]);
    } else {
        // Insert new preferences
        $stmt = $pdo->prepare("
            INSERT INTO user_preferences 
            (user_id, email_notifications, sms_notifications, push_notifications, newsletter) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$user_id, $email_notifications, $sms_notifications, $push_notifications, $newsletter]);
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification preferences updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update notification preferences'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 