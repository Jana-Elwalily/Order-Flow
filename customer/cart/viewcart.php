<?php
header('Content-Type: application/json');
session_start();
require_once '../functions.php';

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

echo json_encode(viewCart($_SESSION['customer_id']));
?>