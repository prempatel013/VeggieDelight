<?php
require_once 'config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$food_id = (int)($_POST['food_id'] ?? 0);

if ($food_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid food item']);
    exit;
}

// Remove item from cart
if (isset($_SESSION['cart'][$food_id])) {
    unset($_SESSION['cart'][$food_id]);
}

$cart_count = count($_SESSION['cart']);

echo json_encode([
    'success' => true,
    'message' => 'Item removed from cart',
    'cart_count' => $cart_count
]);
?> 