<?php
// Debug admin login process
require_once 'config/config.php';

echo "<h2>Admin Login Debug</h2>";

// Clear any existing session
session_start();
session_destroy();
session_start();

$email = 'admin@fooddelivery.com';
$password = 'admin123';

echo "<p><strong>Testing with:</strong></p>";
echo "<p>Email: $email</p>";
echo "<p>Password: $password</p>";

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    // Step 1: Check if user exists
    $stmt = $connection->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p style='color: green;'>✅ Step 1: User found in database</p>";
        echo "<p>User ID: " . $user['id'] . "</p>";
        echo "<p>User Name: " . $user['name'] . "</p>";
        
        // Step 2: Verify password
        if (password_verify($password, $user['password_hash'])) {
            echo "<p style='color: green;'>✅ Step 2: Password verification successful</p>";
            
            // Step 3: Check if it's admin email
            if ($user['email'] === 'admin@fooddelivery.com') {
                echo "<p style='color: green;'>✅ Step 3: Admin email verified</p>";
                
                // Step 4: Set session variables (simulating login)
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = true;
                
                echo "<p style='color: green;'>✅ Step 4: Session variables set</p>";
                echo "<p>Session user_id: " . $_SESSION['user_id'] . "</p>";
                echo "<p>Session is_admin: " . ($_SESSION['is_admin'] ? 'true' : 'false') . "</p>";
                
                // Step 5: Test admin functions
                if (is_admin()) {
                    echo "<p style='color: green;'>✅ Step 5: is_admin() function returns true</p>";
                } else {
                    echo "<p style='color: red;'>❌ Step 5: is_admin() function returns false</p>";
                }
                
                if (is_logged_in()) {
                    echo "<p style='color: green;'>✅ Step 6: is_logged_in() function returns true</p>";
                } else {
                    echo "<p style='color: red;'>❌ Step 6: is_logged_in() function returns false</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ Step 3: Not admin email</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Step 2: Password verification failed</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Step 1: User not found in database</p>";
    }
    
    echo "<hr>";
    echo "<h3>Session Information:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>Try the admin login now:</h3>";
    echo "<p><a href='admin/login.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 