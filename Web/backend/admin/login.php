<?php
require_once '../config/config.php';

// Redirect if already logged in as admin
if (is_admin()) {
    redirect('dashboard.php');
}

$email = $password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (!validate_email($email)) {
        $errors[] = 'Invalid email address.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // Authenticate admin
    if (empty($errors)) {
        $stmt = get_db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Check if admin (email: admin@fooddelivery.com)
            if ($user['email'] === 'admin@fooddelivery.com') {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set admin session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = true;
                
                $_SESSION['success_message'] = 'Welcome to Admin Panel, ' . $user['name'] . '!';
                redirect('dashboard.php');
            } else {
                $errors[] = 'Access denied. Admin privileges required.';
            }
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-shield-alt"></i> <?php echo SITE_NAME; ?> Admin</h1>
        </div>
    </header>
    
    <main class="container">
        <div class="form-container">
            <h2 class="form-title">Admin Login</h2>
            
            <?php if ($errors): ?>
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
                           value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="btn">Login to Admin Panel</button>
            </form>
            
            <div style="text-align: center; margin-top: 2rem; padding: 1rem; background: var(--light-peach); border-radius: 10px;">
                <h4 style="color: var(--dark-green); margin-bottom: 0.5rem;">Admin Credentials</h4>
                <p style="color: var(--text-light); font-size: 0.9rem; margin: 0;">
                    Email: admin@fooddelivery.com<br>
                    Password: admin123
                </p>
            </div>
            
            <p style="text-align: center; margin-top: 1rem;">
                <a href="../index.php" style="color: var(--primary-green); font-weight: bold;">
                    <i class="fas fa-arrow-left"></i> Back to Website
                </a>
            </p>
        </div>
    </main>
</body>
</html> 