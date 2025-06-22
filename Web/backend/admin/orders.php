<?php
require_once '../config/config.php';

// Only allow admin
if (!is_admin()) {
    redirect('login.php');
}

$errors = [];
$success = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'update_status') {
            $order_id = (int)($_POST['order_id'] ?? 0);
            $new_status = $_POST['status'] ?? '';
            $allowed_statuses = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
            if ($order_id <= 0 || !in_array($new_status, $allowed_statuses)) {
                $errors[] = 'Invalid order data.';
            } else {
                $stmt = get_db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
                if ($stmt->execute([$new_status, $order_id])) {
                    $success = 'Order status updated successfully!';
                } else {
                    $errors[] = 'Failed to update order status.';
                }
            }
        }
    }
}

// Fetch all orders with user information
$stmt = get_db()->prepare('
    SELECT o.*, u.name as customer_name, u.email as customer_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
');
$stmt->execute();
$orders = $stmt->fetchAll();

// Get status colors
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

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-shield-alt"></i> <?php echo SITE_NAME; ?> Admin</h1>
            <nav class="admin-nav">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="categories.php"><i class="fas fa-list"></i> Categories</a>
                <a href="foods.php"><i class="fas fa-hamburger"></i> Foods</a>
                <a href="orders.php"><i class="fas fa-receipt"></i> Orders</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <h2 class="section-title">Manage Orders</h2>
        
        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Orders List -->
        <div class="admin-card">
            <h3><i class="fas fa-list"></i> All Orders</h3>
            <?php if (empty($orders)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">No orders found.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $order['id']; ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                        <small style="color: var(--text-light);"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                    </td>
                                    <td>
                                        <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span style="padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; font-weight: bold; <?php echo getStatusColor($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.5rem; margin-right: 0.5rem;" 
                                                onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn" style="padding: 0.5rem;" 
                                                onclick="updateOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Order Details Modal -->
        <div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 600px; max-height: 90vh; overflow-y: auto;">
                <h3>Order Details</h3>
                <div id="orderDetails"></div>
                <div style="text-align: right; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeOrderModal()">Close</button>
                </div>
            </div>
        </div>
        
        <!-- Update Status Modal -->
        <div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 400px;">
                <h3>Update Order Status</h3>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" id="statusOrderId">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="statusSelect" class="form-input" required>
                            <option value="Pending">Pending</option>
                            <option value="Preparing">Preparing</option>
                            <option value="Out for Delivery">Out for Delivery</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div style="text-align: right; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
                        <button type="submit" class="btn">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
        function viewOrderDetails(orderId) {
            // Fetch order details via AJAX
            fetch('order_details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('orderDetails').innerHTML = html;
                    document.getElementById('orderModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load order details.');
                });
        }
        
        function updateOrderStatus(orderId, currentStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }
        
        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
        
        document.getElementById('statusModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeStatusModal();
            }
        });
    </script>
</body>
</html> 