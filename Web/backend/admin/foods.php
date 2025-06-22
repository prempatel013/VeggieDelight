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
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $category_id = (int)($_POST['category_id'] ?? 0);
            $title = sanitize_input($_POST['title'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            
            // Validation
            if (empty($title) || $category_id <= 0 || $price <= 0) {
                $errors[] = 'Please fill all required fields correctly.';
            } else {
                $image_path = '';
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = MAX_FILE_SIZE;
                    
                    if (!in_array($file['type'], $allowed_types)) {
                        $errors[] = 'Only JPG, PNG, and GIF images are allowed.';
                    } elseif ($file['size'] > $max_size) {
                        $errors[] = 'Image size must be less than 5MB.';
                    } else {
                        $upload_dir = '../uploads/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = uniqid() . '_' . time() . '.' . $extension;
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $image_path = $filename;
                        } else {
                            $errors[] = 'Failed to upload image.';
                        }
                    }
                }
                
                if (empty($errors)) {
                    $stmt = get_db()->prepare('INSERT INTO foods (category_id, title, description, price, image_path, is_available) VALUES (?, ?, ?, ?, ?, ?)');
                    if ($stmt->execute([$category_id, $title, $description, $price, $image_path, $is_available])) {
                        $success = 'Food item added successfully!';
                    } else {
                        $errors[] = 'Failed to add food item.';
                    }
                }
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $category_id = (int)($_POST['category_id'] ?? 0);
            $title = sanitize_input($_POST['title'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            
            if (empty($title) || $category_id <= 0 || $price <= 0 || $id <= 0) {
                $errors[] = 'Please fill all required fields correctly.';
            } else {
                // Get current image path
                $stmt = get_db()->prepare('SELECT image_path FROM foods WHERE id = ?');
                $stmt->execute([$id]);
                $current_image = $stmt->fetchColumn();
                $image_path = $current_image;
                
                // Handle new image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = MAX_FILE_SIZE;
                    
                    if (!in_array($file['type'], $allowed_types)) {
                        $errors[] = 'Only JPG, PNG, and GIF images are allowed.';
                    } elseif ($file['size'] > $max_size) {
                        $errors[] = 'Image size must be less than 5MB.';
                    } else {
                        $upload_dir = '../uploads/';
                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = uniqid() . '_' . time() . '.' . $extension;
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            // Delete old image if exists
                            if ($current_image && file_exists($upload_dir . $current_image)) {
                                unlink($upload_dir . $current_image);
                            }
                            $image_path = $filename;
                        } else {
                            $errors[] = 'Failed to upload image.';
                        }
                    }
                }
                
                if (empty($errors)) {
                    $stmt = get_db()->prepare('UPDATE foods SET category_id = ?, title = ?, description = ?, price = ?, image_path = ?, is_available = ? WHERE id = ?');
                    if ($stmt->execute([$category_id, $title, $description, $price, $image_path, $is_available, $id])) {
                        $success = 'Food item updated successfully!';
                    } else {
                        $errors[] = 'Failed to update food item.';
                    }
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                $errors[] = 'Invalid food ID.';
            } else {
                // Get image path before deletion
                $stmt = get_db()->prepare('SELECT image_path FROM foods WHERE id = ?');
                $stmt->execute([$id]);
                $image_path = $stmt->fetchColumn();
                
                $stmt = get_db()->prepare('DELETE FROM foods WHERE id = ?');
                if ($stmt->execute([$id])) {
                    // Delete image file if exists
                    if ($image_path && file_exists('../uploads/' . $image_path)) {
                        unlink('../uploads/' . $image_path);
                    }
                    $success = 'Food item deleted successfully!';
                } else {
                    $errors[] = 'Failed to delete food item.';
                }
            }
        }
    }
}

// Fetch all categories for dropdown
$stmt = get_db()->prepare('SELECT * FROM categories ORDER BY name');
$stmt->execute();
$categories = $stmt->fetchAll();

// Fetch all foods with category information
$stmt = get_db()->prepare('
    SELECT f.*, c.name as category_name 
    FROM foods f 
    JOIN categories c ON f.category_id = c.id 
    ORDER BY c.name, f.title
');
$stmt->execute();
$foods = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Foods - <?php echo SITE_NAME; ?> Admin</title>
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
        <h2 class="section-title">Manage Foods</h2>
        
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
        
        <!-- Add Food Form -->
        <div class="admin-card">
            <h3><i class="fas fa-plus"></i> Add New Food Item</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="add">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-input" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price *</label>
                        <input type="number" name="price" class="form-input" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Available</label>
                        <input type="checkbox" name="is_available" checked style="margin-left: 0.5rem;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input form-textarea" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-input" accept="image/*">
                    <small style="color: var(--text-light);">Max size: 5MB. Allowed: JPG, PNG, GIF</small>
                </div>
                <button type="submit" class="btn">Add Food Item</button>
            </form>
        </div>
        
        <!-- Foods List -->
        <div class="admin-card">
            <h3><i class="fas fa-list"></i> All Food Items</h3>
            <?php if (empty($foods)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">No food items found.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($foods as $food): ?>
                                <tr>
                                    <td>
                                        <?php if ($food['image_path']): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($food['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($food['title']); ?>"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: var(--light-peach); border-radius: 5px; display: flex; align-items: center; justify-content: center; color: var(--text-light);">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($food['title']); ?></strong><br>
                                        <small style="color: var(--text-light);"><?php echo htmlspecialchars($food['description']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($food['category_name']); ?></td>
                                    <td>$<?php echo number_format($food['price'], 2); ?></td>
                                    <td>
                                        <span style="padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; font-weight: bold; 
                                                   <?php echo $food['is_available'] ? 'background: var(--secondary-green); color: var(--dark-green);' : 'background: #FFE6E6; color: #D32F2F;'; ?>">
                                            <?php echo $food['is_available'] ? 'Available' : 'Unavailable'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.5rem; margin-right: 0.5rem;" 
                                                onclick="editFood(<?php echo htmlspecialchars(json_encode($food)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" style="display: inline;" onsubmit="return confirm('Delete this food item?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $food['id']; ?>">
                                            <button type="submit" class="btn" style="padding: 0.5rem; background: #D32F2F;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Edit Food Modal -->
        <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 500px; max-height: 90vh; overflow-y: auto;">
                <h3>Edit Food Item</h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" id="editCategoryId" class="form-input" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" id="editTitle" class="form-input" required>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" id="editPrice" class="form-input" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Available</label>
                            <input type="checkbox" name="is_available" id="editAvailable" style="margin-left: 0.5rem;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editDescription" class="form-input form-textarea" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Current Image</label>
                        <div id="currentImage" style="margin-bottom: 1rem;"></div>
                        <label class="form-label">New Image (optional)</label>
                        <input type="file" name="image" class="form-input" accept="image/*">
                        <small style="color: var(--text-light);">Max size: 5MB. Allowed: JPG, PNG, GIF</small>
                    </div>
                    
                    <div style="text-align: right; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn">Update Food Item</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
        function editFood(food) {
            document.getElementById('editId').value = food.id;
            document.getElementById('editCategoryId').value = food.category_id;
            document.getElementById('editTitle').value = food.title;
            document.getElementById('editDescription').value = food.description;
            document.getElementById('editPrice').value = food.price;
            document.getElementById('editAvailable').checked = food.is_available == 1;
            
            // Show current image
            const currentImageDiv = document.getElementById('currentImage');
            if (food.image_path) {
                currentImageDiv.innerHTML = `<img src="../uploads/${food.image_path}" alt="${food.title}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;">`;
            } else {
                currentImageDiv.innerHTML = '<div style="width: 100px; height: 100px; background: var(--light-peach); border-radius: 5px; display: flex; align-items: center; justify-content: center; color: var(--text-light);"><i class="fas fa-image"></i></div>';
            }
            
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