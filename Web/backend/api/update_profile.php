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
$name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($name) || empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Name and email are required'
    ]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("
        SELECT id FROM users 
        WHERE email = ? AND id != ?
    ");
    $stmt->execute([$email, $user_id]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Email is already taken'
        ]);
        exit;
    }
    
    // Update user profile
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, phone = ?
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$name, $email, $phone, $user_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update profile'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 