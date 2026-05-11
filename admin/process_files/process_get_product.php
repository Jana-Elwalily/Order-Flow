<?php
header('Content-Type: application/json');

require_once '../db_connection.php';
require_once '../auth_functions.php';
require_once '../product_functions.php';

$product_id = $_GET['product_id'] ?? $_POST['product_id'] ?? '';

if (empty($product_id)) {
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit();
}

$result = getProductDetails($product_id);
echo json_encode($result);
?>