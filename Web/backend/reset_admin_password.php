<?php
// Reset admin password with fresh hash
require_once 'config/config.php';

echo "<h2>Reset Admin Password</h2>";

$admin_email = 'admin@fooddelivery.com';
$new_password = 'admin123';

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    // Generate fresh password hash
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    echo "<p><strong>New password:</strong> $new_password</p>";
    echo "<p><strong>New hash:</strong> $new_hash</p>";
    
    // Update admin password in database
    $stmt = $connection->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
    $result = $stmt->execute([$new_hash, $admin_email]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Admin password updated successfully!</p>";
        
        // Verify the update worked
        $stmt = $connection->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
        $stmt->execute([$admin_email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p><strong>Updated user:</strong></p>";
            echo "<p>ID: " . $user['id'] . "</p>";
            echo "<p>Name: " . $user['name'] . "</p>";
            echo "<p>Email: " . $user['email'] . "</p>";
            echo "<p>New Hash: " . $user['password_hash'] . "</p>";
            
            // Test password verification
            if (password_verify($new_password, $user['password_hash'])) {
                echo "<p style='color: green;'>✅ Password verification successful!</p>";
                echo "<p style='color: green; font-weight: bold;'>🎉 Admin login should now work!</p>";
            } else {
                echo "<p style='color: red;'>❌ Password verification failed</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to update admin password</p>";
    }
    
    echo "<hr>";
    echo "<h3>Admin Login Credentials:</h3>";
    echo "<p><strong>Email:</strong> $admin_email</p>";
    echo "<p><strong>Password:</strong> $new_password</p>";
    
    echo "<hr>";
    echo "<h3>Try the admin login now:</h3>";
    echo "<p><a href='admin/login.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 