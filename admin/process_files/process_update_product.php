<?php
require_once '../db_connection.php';
require_once '../auth_functions.php';
require_once '../product_functions.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$required = ['product_id', 'product_name', 'seller_id', 'price', 'stock'];

foreach ($required as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        echo json_encode(['success' => false, 'error' => "$field is required"]);
        exit();
    }
}

$conn = getDBConnection();

$product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
$product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
$seller_id = (int)$_POST['seller_id'];
$price = (float)$_POST['price'];
$stock = (int)$_POST['stock'];

try {
    $stmt = $conn->prepare("
        UPDATE product 
        SET product_name=?, seller_id=?, price=?, stock_quantity=? 
        WHERE product_id=?
    ");

    $stmt->bind_param("sidis", $product_name, $seller_id, $price, $stock, $product_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

closeDBConnection($conn);
?>