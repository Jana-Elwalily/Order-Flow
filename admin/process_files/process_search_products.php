<?php
header('Content-Type: application/json');

require_once '../db_connection.php';
require_once '../auth_functions.php';
require_once '../product_functions.php';

$search_type = $_GET['type'] ?? $_POST['type'] ?? 'product_name'; 
$keyword = $_GET['keyword'] ?? $_POST['keyword'] ?? '';

if (empty($keyword)) {
    echo json_encode(['success' => false, 'error' => 'Search keyword is required']);
    exit();
}

$valid_types = ['product_id', 'product_name', 'category', 'seller_id'];
if (!in_array($search_type, $valid_types)) {
    echo json_encode(['success' => false, 'error' => 'Invalid search type']);
    exit();
}

$products = searchProducts($search_type, $keyword);

echo json_encode([
    'success' => true,
    'search_type' => $search_type,
    'keyword' => $keyword,
    'count' => count($products),
    'products' => $products
]);
?>