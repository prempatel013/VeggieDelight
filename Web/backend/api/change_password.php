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
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Current password and new password are required'
    ]);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'New password must be at least 6 characters long'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Verify current password
    $stmt = $pdo->prepare("
        SELECT password FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        exit;
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password = ?
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$hashed_password, $user_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update password'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 