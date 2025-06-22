<?php
require_once '../config/config.php';

// Only allow admin
if (!is_admin()) {
    http_response_code(403);
    exit('Access denied');
}

$order_id = (int)($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    echo '<p style="color: #D32F2F;">Invalid order ID.</p>';
    exit;
}

try {
    // Get order details with customer info
    $stmt = get_db()->prepare('
        SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ');
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo '<p style="color: #D32F2F;">Order not found.</p>';
        exit;
    }
    
    // Get order items
    $stmt = get_db()->prepare('
        SELECT oi.*, f.title as food_title, f.image_path
        FROM order_items oi 
        JOIN foods f ON oi.food_id = f.id 
        WHERE oi.order_id = ?
    ');
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
    // Get status color
    function getStatusColor($status) {
        switch ($status) {
            case 'Pending': return 'background: #FFF3E0; color: #F57C00;';
            case 'Preparing': return 'background: #E3F2FD; color: #1976D2;';
            case 'Out for Delivery': return 'background: #E8F5E8; color: #388E3C;';
            case 'Delivered': return 'background: var(--secondary-green); color: var(--dark-green);';
            case 'Cancelled': return 'background: #FFE6E6; color: #D32F2F;';
            default: return 'background: var(--light-peach); color: var(--text-dark);';
        }
    }
    
    ?>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Order Info -->
        <div>
            <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Order Information</h4>
            <div style="background: var(--light-peach); padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> 
                    <span style="padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; font-weight: bold; <?php echo getStatusColor($order['status']); ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            </div>
            
            <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Customer Information</h4>
            <div style="background: var(--light-peach); padding: 1rem; border-radius: 10px;">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
            </div>
        </div>
        
        <!-- Delivery Info -->
        <div>
            <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Delivery Information</h4>
            <div style="background: var(--light-peach); padding: 1rem; border-radius: 10px;">
                <p><strong>Delivery Address:</strong></p>
                <p style="margin-left: 1rem;"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                <p><strong>Delivery Phone:</strong> <?php echo htmlspecialchars($order['delivery_phone']); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div style="margin-top: 2rem;">
        <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Order Items</h4>
        <?php if (empty($order_items)): ?>
            <p style="text-align: center; color: var(--text-light); padding: 1rem;">No items found.</p>
        <?php else: ?>
            <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--light-peach);">
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--secondary-green);">Item</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--secondary-green);">Quantity</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 1px solid var(--secondary-green);">Price Each</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 1px solid var(--secondary-green);">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--secondary-green);">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <?php if ($item['image_path']): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($item['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['food_title']); ?>"
                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; background: var(--light-peach); border-radius: 5px; display: flex; align-items: center; justify-content: center; color: var(--text-light);">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['food_title']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--secondary-green);">
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td style="padding: 1rem; text-align: right; border-bottom: 1px solid var(--secondary-green);">
                                    $<?php echo number_format($item['price_each'], 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: right; border-bottom: 1px solid var(--secondary-green);">
                                    <strong>$<?php echo number_format($item['price_each'] * $item['quantity'], 2); ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
    
} catch (Exception $e) {
    echo '<p style="color: #D32F2F;">Error loading order details.</p>';
}
?> 