<?php
// customer/get_all_products.php  (replaces get_all_books.php)
require_once '../product_fn.php';
header('Content-Type: application/json');

echo json_encode(getAllAvailableProducts());