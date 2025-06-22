<?php
// API endpoint to get cart count
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
            'success' => true,
            'count' => 0
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get cart count from database
    $stmt = $pdo->prepare("
        SELECT SUM(quantity) as count
        FROM cart
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $cartCount = $result['count'] ? intval($result['count']) : 0;
    
    echo json_encode([
        'success' => true,
        'count' => $cartCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'count' => 0
    ]);
}
?> 