<?php
require_once 'config/config.php';

// Redirect if not logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please log in to checkout.';
    redirect('login.php');
}

$cart_items = $_SESSION['cart'] ?? [];
$total = 0;

// Calculate total
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Redirect if cart is empty
if (empty($cart_items)) {
    redirect('cart.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = sanitize_input($_POST['delivery_address'] ?? '');
    $delivery_phone = sanitize_input($_POST['delivery_phone'] ?? '');
    
    // Validation
    if (empty($delivery_address)) {
        $errors[] = 'Delivery address is required.';
    }
    if (empty($delivery_phone) || !preg_match('/^[0-9\-\+ ]{7,20}$/', $delivery_phone)) {
        $errors[] = 'Valid delivery phone number is required.';
    }
    
    // Process order
    if (empty($errors)) {
        try {
            get_db()->beginTransaction();
            
            // Create order
            $stmt = get_db()->prepare('INSERT INTO orders (user_id, total_amount, delivery_address, delivery_phone) VALUES (?, ?, ?, ?)');
            $stmt->execute([$_SESSION['user_id'], $total, $delivery_address, $delivery_phone]);
            $order_id = get_db()->lastInsertId();
            
            // Add order items
            $stmt = get_db()->prepare('INSERT INTO order_items (order_id, food_id, quantity, price_each) VALUES (?, ?, ?, ?)');
            foreach ($cart_items as $food_id => $item) {
                $stmt->execute([$order_id, $food_id, $item['quantity'], $item['price']]);
            }
            
            get_db()->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            
            $_SESSION['success_message'] = 'Order placed successfully! Order #' . $order_id;
            redirect('profile.php');
            
        } catch (Exception $e) {
            get_db()->rollBack();
            $errors[] = 'Failed to place order. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <i class="fas fa-utensils"></i> <?php echo SITE_NAME; ?>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <h1 class="section-title">Checkout</h1>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 1000px; margin: 0 auto;">
            <!-- Order Summary -->
            <div class="cart-container">
                <h2 style="color: var(--text-dark); margin-bottom: 1.5rem;">
                    <i class="fas fa-shopping-cart"></i> Order Summary
                </h2>
                
                <?php foreach ($cart_items as $food_id => $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <h3 class="cart-item-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="cart-item-price">Qty: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="cart-item-price">
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-total">
                    Total: $<?php echo number_format($total, 2); ?>
                </div>
            </div>
            
            <!-- Delivery Details -->
            <div class="form-container">
                <h2 class="form-title">Delivery Details</h2>
                
                <?php if ($errors): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="form-group">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="delivery_address" class="form-input form-textarea" required 
                                  placeholder="Enter your full delivery address"><?php echo htmlspecialchars($_POST['delivery_address'] ?? $_SESSION['user_address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Delivery Phone</label>
                        <input type="text" name="delivery_phone" class="form-input" required
                               value="<?php echo htmlspecialchars($_POST['delivery_phone'] ?? $_SESSION['user_phone'] ?? ''); ?>"
                               placeholder="Enter delivery phone number">
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%;">
                        <i class="fas fa-credit-card"></i> Place Order
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="cart.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html> 