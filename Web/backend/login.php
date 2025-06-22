<?php
// Suppress any warnings or notices that might output HTML
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'config/config.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // Authenticate user
    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();
            
            $stmt = $connection->prepare('SELECT id, name, email, password, address, phone FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_address'] = $user['address'];
                $_SESSION['user_phone'] = $user['phone'];
                
                // Check if admin (email: admin@fooddelivery.com)
                if ($user['email'] === 'admin@fooddelivery.com') {
                    $_SESSION['is_admin'] = true;
                }
                
                if ($isAjax) {
                    // Return JSON response for frontend
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email']
                        ]
                    ]);
                    exit;
                } else {
                    // Redirect for traditional form submission
                    $_SESSION['success_message'] = 'Welcome back, ' . $user['name'] . '!';
                    header('Location: index.php');
                    exit;
                }
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error occurred.';
        }
    }
    
    if ($isAjax) {
        // Return JSON response for frontend
        echo json_encode([
            'success' => false,
            'message' => implode(' ', $errors)
        ]);
        exit;
    }
}

// If not AJAX request, show the HTML form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FoodExpress</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <i class="fas fa-utensils"></i> FoodExpress
            </a>
        </nav>
    </header>
    
    <main class="container">
        <div class="form-container">
            <h2 class="form-title">Welcome Back!</h2>
            
            <?php if (isset($errors) && $errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem;">
                Don't have an account? 
                <a href="register.php" style="color: var(--primary-green); font-weight: bold;">Register</a>
            </p>
            
            <div style="text-align: center; margin-top: 2rem; padding: 1rem; background: var(--light-peach); border-radius: 10px;">
                <h4 style="color: var(--dark-green); margin-bottom: 0.5rem;">Demo Account</h4>
                <p style="color: var(--text-light); font-size: 0.9rem; margin: 0;">
                    Email: admin@fooddelivery.com<br>
                    Password: admin123
                </p>
            </div>
        </div>
    </main>
</body>
</html> 