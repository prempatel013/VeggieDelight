<?php
// Backend Status Check
require_once 'config/config.php';

echo "<h2>🚀 FoodExpress Backend Status</h2>";

// Check PHP version
echo "<h3>📋 System Information</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Check database connection
echo "<h3>🗄️ Database Status</h3>";
try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check tables
    $tables = ['users', 'categories', 'foods', 'orders', 'order_items'];
    foreach ($tables as $table) {
        $stmt = $connection->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch();
        echo "<p><strong>$table:</strong> " . $result['count'] . " records</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Check file permissions
echo "<h3>📁 File System</h3>";
$upload_dir = '../uploads/';
if (is_dir($upload_dir)) {
    echo "<p style='color: green;'>✅ Uploads directory exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Uploads directory missing</p>";
}

// Check CSS file
$css_file = 'css/style.css';
if (file_exists($css_file)) {
    echo "<p style='color: green;'>✅ CSS file exists</p>";
} else {
    echo "<p style='color: red;'>❌ CSS file missing</p>";
}

// Check admin user
echo "<h3>👤 Admin User</h3>";
try {
    $stmt = $connection->prepare('SELECT id, name, email FROM users WHERE email = ?');
    $stmt->execute(['admin@fooddelivery.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color: green;'>✅ Admin user exists</p>";
        echo "<p><strong>Name:</strong> " . $admin['name'] . "</p>";
        echo "<p><strong>Email:</strong> " . $admin['email'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Admin user not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking admin user</p>";
}

echo "<h3>🔗 Quick Links</h3>";
echo "<ul>";
echo "<li><a href='index.php'>🏠 Main Page</a></li>";
echo "<li><a href='login.php'>🔐 User Login</a></li>";
echo "<li><a href='register.php'>📝 User Registration</a></li>";
echo "<li><a href='cart.php'>🛒 Shopping Cart</a></li>";
echo "<li><a href='admin/login.php'>⚙️ Admin Panel</a></li>";
echo "<li><a href='test_connection.php'>🧪 Database Test</a></li>";
echo "</ul>";

echo "<h3>🎯 Admin Login Credentials</h3>";
echo "<p><strong>Email:</strong> admin@fooddelivery.com</p>";
echo "<p><strong>Password:</strong> admin123</p>";

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>🎉 Your backend is ready to use!</p>";
?> 