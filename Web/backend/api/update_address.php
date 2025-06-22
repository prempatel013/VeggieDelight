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
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$zipcode = $_POST['zipcode'] ?? '';

if (empty($address) || empty($city) || empty($zipcode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Address, city, and ZIP code are required'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Update user address
    $stmt = $pdo->prepare("
        UPDATE users 
        SET address = ?, city = ?, zipcode = ?
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$address, $city, $zipcode, $user_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Address updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update address'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 