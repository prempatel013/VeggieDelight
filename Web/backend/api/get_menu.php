<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// API endpoint to get menu items
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../config/database.php';
    // Get all available foods with their categories
    $stmt = $pdo->prepare("
        SELECT f.id, f.title, f.description, f.price, f.image_path, f.category
        FROM food_items f
        WHERE f.is_available = 1
        ORDER BY f.category, f.title
    ");
    $stmt->execute();
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group foods by category
    $menu = [];
    foreach ($foods as $food) {
        $category = $food['category'] ?: 'uncategorized';
        $categoryKey = strtolower(str_replace(' ', '_', $category));
        
        if (!isset($menu[$categoryKey])) {
            $menu[$categoryKey] = [];
        }
        
        $menu[$categoryKey][] = [
            'id' => $food['id'],
            'title' => $food['title'],
            'description' => $food['description'],
            'price' => floatval($food['price']),
            'category' => $food['category'],
            'image_path' => $food['image_path']
        ];
    }
    
    // Convert to indexed array
    $menuArray = [];
    foreach ($menu as $category => $items) {
        $menuArray = array_merge($menuArray, $items);
    }
    
    echo json_encode([
        'success' => true,
        'menu' => $menuArray,
        'categories' => array_keys($menu)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 