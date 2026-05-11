<?php
require_once '../db_connection.php';
require_once '../auth_functions.php';
require_once '../product_functions.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$required_fields = ['product_id', 'product_name', 'price', 'category', 'stock', 'threshold', 'seller_id'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        echo json_encode(['success' => false, 'error' => "Required field '$field' is missing"]);
        exit();
    }
}

$product_data = [
    'product_id' => trim($_POST['product_id']),
    'product_name' => trim($_POST['product_name']),
    'price' => (float)$_POST['price'],
    'category' => trim($_POST['category']),
    'stock' => (int)$_POST['stock'],
    'threshold' => (int)$_POST['threshold'],
    'seller_id' => (int)$_POST['seller_id']
];

if ($product_data['price'] <= 0) {
    echo json_encode(['success' => false, 'error' => 'Price must be greater than 0']);
    exit();
}

if ($product_data['stock'] < 0) {
    echo json_encode(['success' => false, 'error' => 'Stock cannot be negative']);
    exit();
}

if ($product_data['threshold'] < 0) {
    echo json_encode(['success' => false, 'error' => 'Threshold cannot be negative']);
    exit();
}

// Create product
$result = addNewProduct($product_data);

echo json_encode($result);
?>