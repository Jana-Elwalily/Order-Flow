<?php
require_once 'db_connection.php';
function getPendingOrders() {
    $conn = getDBConnection();
    $query = "SELECT ro.*, b.product_name, p.name as seller_name 
              FROM Replenishment_Order ro 
              JOIN product b ON ro.product_id = b.product_id
              JOIN Seller p ON ro.seller_id = p.seller_id 
              WHERE ro.status = 'Pending' 
              ORDER BY ro.order_date DESC";
    
    $result = mysqli_query($conn, $query);
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    closeDBConnection($conn);
    return $orders;
}

function confirmReplenishmentOrder($reorder_id) {
    $conn = getDBConnection();
    $reorder_id = (int)$reorder_id;
    
    $query = "UPDATE Replenishment_Order SET status = 'Confirmed' 
              WHERE reorder_id = $reorder_id AND status = 'Pending'";
    
    if (mysqli_query($conn, $query)) {
        closeDBConnection($conn);
        return ['success' => true];
    }
    closeDBConnection($conn);
    return ['success' => false, 'error' => mysqli_error($conn)];
}
?>