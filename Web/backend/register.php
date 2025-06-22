<?php
// Suppress any warnings or notices that might output HTML
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'config/config.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $errors = [];

    // Validation
    if (empty($first_name) || strlen($first_name) < 2) {
        $errors[] = 'First name must be at least 2 characters.';
    }
    if (empty($last_name) || strlen($last_name) < 2) {
        $errors[] = 'Last name must be at least 2 characters.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (empty($address)) {
        $errors[] = 'Address is required.';
    }
    if (empty($phone) || !preg_match('/^[0-9\-\+ ]{7,20}$/', $phone)) {
        $errors[] = 'Valid phone number is required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();
            
            $stmt = $connection->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email is already registered.';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error occurred.';
        }
    }

    // Register user
    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $name = $first_name . ' ' . $last_name;
            
            $stmt = $connection->prepare('INSERT INTO users (name, email, password, address, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            
            if ($stmt->execute([$name, $email, $password_hash, $address, $phone])) {
                if ($isAjax) {
                    // Return JSON response for frontend
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! Please log in.'
                    ]);
                    exit;
                } else {
                    // Redirect for traditional form submission
                    $_SESSION['success_message'] = 'Registration successful! Please log in.';
                    header('Location: login.php');
                    exit;
                }
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        } catch (Exception $e) {
            $errors[] = 'Registration failed. Please try again.';
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
    <title>Register - FoodExpress</title>
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
            <h2 class="form-title">Create Your Account</h2>
            
            <?php if (isset($errors) && $errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-input" 
                           value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input" 
                           value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-input form-textarea" required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input" 
                           value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-input" required>
                </div>
                
                <button type="submit" class="btn">Register</button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem;">
                Already have an account? 
                <a href="login.php" style="color: var(--primary-green); font-weight: bold;">Login</a>
            </p>
        </div>
    </main>
</body>
</html> 