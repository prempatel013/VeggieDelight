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
$quantity = (int)($_POST['quantity'] ?? 0);

if ($food_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Update quantity in cart
if (isset($_SESSION['cart'][$food_id])) {
    $_SESSION['cart'][$food_id]['quantity'] = $quantity;
}

// Calculate new total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

$cart_count = count($_SESSION['cart']);

echo json_encode([
    'success' => true,
    'message' => 'Quantity updated',
    'total' => $total,
    'cart_count' => $cart_count
]);
?> 