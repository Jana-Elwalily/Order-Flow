<?php
require_once 'db_connection.php';

function addNewProduct($ProductData) {
    $conn = getDBConnection();
    
    $product_id = mysqli_real_escape_string($conn, $ProductData['product_id']);
    $product_name = mysqli_real_escape_string($conn, $ProductData['product_name']);
    $description = mysqli_real_escape_string($conn, $ProductData['description'] ?? '');

    $price = (float)$ProductData['price'];
    $category = mysqli_real_escape_string($conn, $ProductData['category']);
    $stock = (int)$ProductData['stock'];
    $threshold = (int)$ProductData['threshold'];
    $seller_id = (int)$ProductData['seller_id'];

    $query = "INSERT INTO product 
    (product_id, product_name, description, price, category, stock_quantity, threshold, seller_id)
    VALUES 
    ('$product_id', '$product_name', '$description', $price, '$category', $stock, $threshold, $seller_id)";

    if (mysqli_query($conn, $query)) {
        closeDBConnection($conn);
        return ['success' => true, 'message' => 'Product added successfully'];
    } else {
        $error = mysqli_error($conn);
        closeDBConnection($conn);
        return ['success' => false, 'error' => $error];
    }
}

function updateProductStock($product_id, $newQuantity, $conn = null) {
    if ($conn === null) {
        $conn = getDBConnection();
        $closeConnection = true;
    } else {
        $closeConnection = false;
    }
    
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $newQuantity = (int)$newQuantity;
    
    $query = "UPDATE product SET stock_quantity = $newQuantity WHERE product_id = '$product_id'";
    
    try {
        if (mysqli_query($conn, $query)) {
            if ($closeConnection) {
                closeDBConnection($conn);
            }
            return ['success' => true, 'message' => 'Stock updated successfully'];
        } else {
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        if ($closeConnection) {
            closeDBConnection($conn);
        }
        
        if (strpos($error, 'cannot be negative') !== false) {
            return ['success' => false, 'error' => 'Stock quantity cannot be negative (trigger prevented it)'];
        }
        
        return ['success' => false, 'error' => 'Update failed: ' . $error];
    }
}

function searchProducts($keyword) {
    $conn = getDBConnection();
    $keyword = mysqli_real_escape_string($conn, $keyword);

    $query = "SELECT * FROM product
              WHERE product_name LIKE '%$keyword%'
              OR description LIKE '%$keyword%'
              OR category = '$keyword'";

    $result = mysqli_query($conn, $query);
    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    closeDBConnection($conn);
    return $products;
}

function getProductDetails($id) {
    $conn = getDBConnection();

    $id = mysqli_real_escape_string($conn, $id);

    $query = "SELECT * FROM product WHERE product_id = '$id'";

    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        closeDBConnection($conn);
        return ['success' => false, 'error' => 'Product not found'];
    }

    $product = mysqli_fetch_assoc($result);

    closeDBConnection($conn);
    return ['success' => true, 'product' => $product];
}



?>