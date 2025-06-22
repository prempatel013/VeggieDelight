<?php
// API endpoint to add a new review
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Suppress errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to post a review.']);
    exit;
}

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();

    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $food_id = intval($_POST['food_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($food_id <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided. Please check your input.']);
        exit;
    }

    $stmt = $connection->prepare(
        "INSERT INTO reviews (food_id, user_id, user_name, rating, comment) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$food_id, $user_id, $user_name, $rating, $comment]);

    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your review!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to submit review. Please try again later.'
    ]);
}
?> 