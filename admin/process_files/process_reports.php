<?php
require_once '../db_connection.php';
require_once '../auth_functions.php';
require_once '../report_functions.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'order_flow');

$report_type = $_GET['report_type'] ?? '';

switch($report_type) {
    case 'sales_previous_month':
        $data = getTotalSalesPreviousMonth($conn);
        break;
        
    case 'sales_by_date':
        $date = $_GET['date'];
        $data = getTotalSalesByDate($date, $conn);
        break;
        
    case 'top_customers':
        $data = getTopCustomersLast3Months($conn);
        break;
        
    case 'top_products':
        $data = getTopSellingProductsLast3Months($conn);
        break;
        
    case 'product_reorders':
        $product_id = $_GET['product_id'];
        $data = getProductReorderCount($product_id, $conn);
        break;
        
    default:
        $data = ['error' => 'Invalid report type'];
}

echo json_encode($data);
?>