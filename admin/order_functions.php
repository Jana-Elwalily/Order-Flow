<?php
require_once 'db_connection.php';
function getPendingOrders() {
    $conn = getDBConnection();
   $query = "SELECT 
            ro.reorder_id,
            ro.order_date,
            ro.quantity,
            ro.status,
            b.product_name AS product_name,
            s.name AS seller_name
          FROM Replenishment_Order ro
          JOIN Product b ON ro.product_id = b.product_id
          JOIN Seller s ON ro.seller_id = s.seller_id
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