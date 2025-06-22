<?php
// API endpoint to place a new order

header('Content-Type: application/json');
require_once '../config/config.php'; // Using main config

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure clean JSON output
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to place an order.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$delivery_address = trim($_POST['address'] ?? '');
$delivery_phone = trim($_POST['phone'] ?? '');

if (empty($delivery_address) || empty($delivery_phone)) {
    echo json_encode(['success' => false, 'message' => 'Address and phone number are required.']);
    exit;
}

$pdo = null;
try {
    $pdo = get_db();
    $pdo->beginTransaction();

    // 1. Get all items from the user's cart
    $stmt = $pdo->prepare("
        SELECT c.food_id, c.quantity, f.price
        FROM cart c
        JOIN food_items f ON c.food_id = f.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
        $pdo->rollBack();
        exit;
    }

    // 2. Calculate the total order amount
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    // Add tax
    $total_amount = $total_amount * 1.08;


    // 3. Create the order
    $stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, total, status, delivery_address, delivery_phone, created_at) 
         VALUES (?, ?, 'pending', ?, ?, NOW())"
    );
    $stmt->execute([$user_id, $total_amount, $delivery_address, $delivery_phone]);
    $order_id = $pdo->lastInsertId();

    // 4. Create the order items
    $stmt = $pdo->prepare(
        "INSERT INTO order_items (order_id, food_id, quantity, price) 
         VALUES (?, ?, ?, ?)"
    );
    foreach ($cart_items as $item) {
        $stmt->execute([$order_id, $item['food_id'], $item['quantity'], $item['price']]);
    }

    // 5. Clear the user's cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // 6. Commit the transaction
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully!',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log the detailed error to the server's error log instead of exposing it
    error_log("Order placement failed: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to place order due to a server error. Please try again later.'
    ]);
}
?> 