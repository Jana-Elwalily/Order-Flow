<?php
header('Content-Type: application/json');

require_once '../db_connection.php';  

$conn = getDBConnection();

$query = "SELECT seller_id, name FROM seller ORDER BY name ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'error' => 'Database query failed: ' . mysqli_error($conn)
    ]);
    closeDBConnection($conn);
    exit();
}

$sellers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $sellers[] = $row;
}

echo json_encode([
    'success' => true,
    'sellers' => $sellers
]);

closeDBConnection($conn);
?>