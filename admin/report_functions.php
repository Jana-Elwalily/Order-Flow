<?php
require_once 'db_connection.php';
function getTotalSalesPreviousMonth() {
    $conn = getDBConnection();

    $query = "SELECT COALESCE(SUM(total_amount), 0) as total_sales
              FROM `order`
              WHERE YEAR(order_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
                AND MONTH(order_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
    
    $result = mysqli_query($conn, $query);
    closeDBConnection($conn);
    return mysqli_fetch_assoc($result);
}

function getTotalSalesByDate($date) {
    $conn = getDBConnection();
    $date = mysqli_real_escape_string($conn, $date);
    
    $query = "SELECT COALESCE(SUM(total_amount), 0) as total_sales
              FROM `order`
              WHERE DATE(order_date) = '$date'";
    
    $result = mysqli_query($conn, $query);
    closeDBConnection($conn);
    return mysqli_fetch_assoc($result);
}

function getTopCustomersLast3Months() {
    $conn = getDBConnection();
    $query = "SELECT c.customer_id, c.first_name, c.last_name, 
                     COALESCE(SUM(o.total_amount), 0) as total_spent
              FROM `order` o
              JOIN customer c ON o.customer_id = c.customer_id
              WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
              GROUP BY c.customer_id
              ORDER BY total_spent DESC
              LIMIT 5";
    
    $result = mysqli_query($conn, $query);
    $customers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row;
    }
    closeDBConnection($conn);
    return $customers;
}

function getTopSellingProductsLast3Months() {
    $conn = getDBConnection();
    $query = "SELECT b.product_id, b.product_name, COALESCE(SUM(oi.quantity), 0) as total_sold
              FROM order_item oi
              JOIN `order` o ON oi.order_id = o.order_id
              JOIN product b ON oi.product_id = b.product_id
              WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
              GROUP BY b.product_id
              ORDER BY total_sold DESC
              LIMIT 10";
    
    $result = mysqli_query($conn, $query);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    closeDBConnection($conn);
    return $products;
}


function getProductReorderCount($product_id) {
    $conn = getDBConnection();
    $product_id = mysqli_real_escape_string($conn, $product_id);
    
    $query = "SELECT COUNT(*) as times_reordered
              FROM replenishment_order
              WHERE product_id = '$product_id'";
    
    $result = mysqli_query($conn, $query);
    closeDBConnection($conn);
    return mysqli_fetch_assoc($result);
}
?>