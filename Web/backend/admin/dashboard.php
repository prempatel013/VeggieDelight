<?php
require_once '../config/config.php';

// Only allow admin
if (!is_admin()) {
    redirect('login.php');
}

// Fetch stats
$db = get_db();
$total_users = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$total_orders = $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$total_revenue = $db->query('SELECT IFNULL(SUM(total_amount),0) FROM orders')->fetchColumn();
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-shield-alt"></i> <?php echo SITE_NAME; ?> Admin Dashboard</h1>
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
        <h2 class="section-title">Quick Stats</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <div class="admin-card">
                <h3><i class="fas fa-users" style="color: var(--primary-green);"></i> Users</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-green);">
                    <?php echo $total_users; ?>
                </div>
            </div>
            <div class="admin-card">
                <h3><i class="fas fa-receipt" style="color: var(--accent-orange);"></i> Orders</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: var(--accent-orange);">
                    <?php echo $total_orders; ?>
                </div>
            </div>
            <div class="admin-card">
                <h3><i class="fas fa-dollar-sign" style="color: var(--dark-green);"></i> Revenue</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: var(--dark-green);">
                    $<?php echo number_format($total_revenue, 2); ?>
                </div>
            </div>
            <div class="admin-card">
                <h3><i class="fas fa-clock" style="color: var(--primary-peach);"></i> Pending Orders</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-peach);">
                    <?php echo $pending_orders; ?>
                </div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="categories.php" class="btn" style="margin-right: 1rem;"><i class="fas fa-list"></i> Manage Categories</a>
            <a href="foods.php" class="btn btn-secondary" style="margin-right: 1rem;"><i class="fas fa-hamburger"></i> Manage Foods</a>
            <a href="orders.php" class="btn"><i class="fas fa-receipt"></i> Manage Orders</a>
        </div>
    </main>
</body>
</html> 