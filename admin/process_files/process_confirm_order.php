<?php
require_once '../db_connection.php';
require_once '../auth_functions.php';
require_once '../order_functions.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'order_flow');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reorder_id = (int)$_POST['reorder_id'];
    
    $result = confirmReplenishmentOrder($reorder_id,$conn);
    echo json_encode($result);
}
?>