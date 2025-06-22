<?php
require_once 'config/config.php';

// Fetch categories
$stmt = get_db()->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Fetch foods with category information
$stmt = get_db()->prepare("
    SELECT f.*, c.name as category_name 
    FROM foods f 
    JOIN categories c ON f.category_id = c.id 
    WHERE f.is_available = 1 
    ORDER BY c.name, f.title
");
$stmt->execute();
$foods = $stmt->fetchAll();

// Group foods by category
$foods_by_category = [];
foreach ($foods as $food) {
    $foods_by_category[$food['category_name']][] = $food;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Delicious Food Delivered</title>
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
                <li><a href="#menu">Menu</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
            <a href="cart.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                <?php endif; ?>
            </a>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Hero Section -->
        <section class="hero-section">
            <h1 class="hero-title">Delicious Food Delivered to Your Door</h1>
            <p class="hero-subtitle">Fresh ingredients, amazing flavors, and fast delivery - all in one place!</p>
            <a href="#menu" class="btn">Explore Menu</a>
        </section>

        <!-- Categories Section -->
        <section class="categories-section" id="menu">
            <h2 class="section-title">Our Menu Categories</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card" onclick="scrollToCategory('<?php echo $category['name']; ?>')">
                        <div class="category-icon">
                            <?php
                            $icons = [
                                'Pizza' => 'fas fa-pizza-slice',
                                'Burgers' => 'fas fa-hamburger',
                                'Pasta' => 'fas fa-utensils',
                                'Salads' => 'fas fa-leaf',
                                'Desserts' => 'fas fa-ice-cream',
                                'Beverages' => 'fas fa-coffee'
                            ];
                            $icon = $icons[$category['name']] ?? 'fas fa-utensils';
                            ?>
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Food Items by Category -->
        <?php foreach ($foods_by_category as $category_name => $category_foods): ?>
            <section class="foods-section" id="category-<?php echo strtolower(str_replace(' ', '-', $category_name)); ?>">
                <h2 class="section-title"><?php echo htmlspecialchars($category_name); ?></h2>
                <div class="foods-grid">
                    <?php foreach ($category_foods as $food): ?>
                        <div class="food-card">
                            <div class="food-image">
                                <?php if ($food['image_path']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($food['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($food['title']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                    <span>No Image</span>
                                <?php endif; ?>
                            </div>
                            <div class="food-content">
                                <h3 class="food-title"><?php echo htmlspecialchars($food['title']); ?></h3>
                                <p class="food-description"><?php echo htmlspecialchars($food['description']); ?></p>
                                <div class="food-price">$<?php echo number_format($food['price'], 2); ?></div>
                                <button class="add-to-cart-btn" onclick="addToCart(<?php echo $food['id']; ?>)">
                                    <i class="fas fa-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>

    <!-- Footer -->
    <footer style="background: var(--dark-green); color: var(--white); text-align: center; padding: 2rem; margin-top: 3rem;">
        <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        <p>Delicious food delivered with love ❤️</p>
    </footer>

    <script src="js/cart.js"></script>
    <script>
        function scrollToCategory(categoryName) {
            const categoryId = 'category-' + categoryName.toLowerCase().replace(' ', '-');
            const element = document.getElementById(categoryId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Show success message if redirected from login/register
        <?php if (isset($_SESSION['success_message'])): ?>
            alert('<?php echo $_SESSION['success_message']; ?>');
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html> 