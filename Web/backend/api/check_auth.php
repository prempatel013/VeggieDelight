<?php
// API endpoint to check authentication status
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

$logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

echo json_encode([
    'success' => true,
    'logged_in' => $logged_in,
    'user_id' => $_SESSION['user_id'] ?? null,
    'user_name' => $_SESSION['user_name'] ?? null
]);
?> 