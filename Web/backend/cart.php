<?php
require_once 'config/config.php';

// Redirect if not logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please log in to view your cart.';
    redirect('login.php');
}

$cart_items = $_SESSION['cart'] ?? [];
$total = 0;

// Calculate total
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - <?php echo SITE_NAME; ?></title>
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
                <li><a href="index.php#menu">Menu</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
            <a href="cart.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <?php if (count($cart_items) > 0): ?>
                    <span class="cart-count"><?php echo count($cart_items); ?></span>
                <?php endif; ?>
            </a>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <h1 class="section-title">Your Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="cart-container" style="text-align: center; padding: 3rem;">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                <h2 style="color: var(--text-light); margin-bottom: 1rem;">Your cart is empty</h2>
                <p style="color: var(--text-light); margin-bottom: 2rem;">Add some delicious food to get started!</p>
                <a href="index.php#menu" class="btn">Browse Menu</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <?php foreach ($cart_items as $food_id => $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <h3 class="cart-item-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="cart-item-price">$<?php echo number_format($item['price'], 2); ?> each</p>
                        </div>
                        
                        <div class="cart-quantity">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $food_id; ?>, -1)">-</button>
                            <span id="quantity-<?php echo $food_id; ?>"><?php echo $item['quantity']; ?></span>
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $food_id; ?>, 1)">+</button>
                        </div>
                        
                        <div class="cart-item-price">
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                        
                        <button class="quantity-btn" onclick="removeFromCart(<?php echo $food_id; ?>)" 
                                style="background: #D32F2F; margin-left: 1rem;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-total" id="cart-total">
                    Total: $<?php echo number_format($total, 2); ?>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="checkout.php" class="btn" style="margin-right: 1rem;">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </a>
                    <a href="index.php#menu" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Add More Items
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="js/cart.js"></script>
</body>
</html> 