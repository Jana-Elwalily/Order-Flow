<?php
header('Content-Type: application/json');
session_start();
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$card_number  = $_POST['card_number']  ?? '';
$expiry_date  = $_POST['expiry_date']  ?? '';

if (!$card_number || !$expiry_date) {
    echo json_encode(['success' => false, 'message' => 'Card number and expiry date are required']);
    exit;
}

echo json_encode(checkoutSimplified($_SESSION['customer_id'], $card_number, $expiry_date));
?>