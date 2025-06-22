<?php
require_once '../config/config.php';

// Only allow admin
if (!is_admin()) {
    redirect('login.php');
}

$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        
        if (empty($name)) {
            $errors[] = 'Category name is required.';
        } else {
            $stmt = get_db()->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            if ($stmt->execute([$name, $description])) {
                $success = 'Category added successfully!';
            } else {
                $errors[] = 'Failed to add category.';
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        
        if (empty($name) || $id <= 0) {
            $errors[] = 'Invalid category data.';
        } else {
            $stmt = get_db()->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
            if ($stmt->execute([$name, $description, $id])) {
                $success = 'Category updated successfully!';
            } else {
                $errors[] = 'Failed to update category.';
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            $errors[] = 'Invalid category ID.';
        } else {
            // Check if category has foods
            $stmt = get_db()->prepare('SELECT COUNT(*) FROM foods WHERE category_id = ?');
            $stmt->execute([$id]);
            $food_count = $stmt->fetchColumn();
            
            if ($food_count > 0) {
                $errors[] = 'Cannot delete category with existing foods.';
            } else {
                $stmt = get_db()->prepare('DELETE FROM categories WHERE id = ?');
                if ($stmt->execute([$id])) {
                    $success = 'Category deleted successfully!';
                } else {
                    $errors[] = 'Failed to delete category.';
                }
            }
        }
    }
}

// Fetch all categories
$stmt = get_db()->prepare('SELECT * FROM categories ORDER BY name');
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - <?php echo SITE_NAME; ?> Admin</title>
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
        <h2 class="section-title">Manage Categories</h2>
        
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
        
        <!-- Add Category Form -->
        <div class="admin-card">
            <h3><i class="fas fa-plus"></i> Add New Category</h3>
            <form method="post" style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 1rem; align-items: end;">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-input">
                </div>
                <button type="submit" class="btn">Add Category</button>
            </form>
        </div>
        
        <!-- Categories List -->
        <div class="admin-card">
            <h3><i class="fas fa-list"></i> All Categories</h3>
            <?php if (empty($categories)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">No categories found.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 0.5rem; margin-right: 0.5rem;" 
                                            onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['description']); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Delete this category?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" class="btn" style="padding: 0.5rem; background: #D32F2F;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Edit Category Modal -->
        <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 400px;">
                <h3>Edit Category</h3>
                <form method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="editName" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" id="editDescription" class="form-input">
                    </div>
                    <div style="text-align: right; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
        function editCategory(id, name, description) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html> 