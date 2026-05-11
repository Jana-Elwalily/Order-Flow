<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'order_flow');

function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    
    //mysqli_set_charset($conn, "utf8"); 
    mysqli_set_charset($conn, "utf8mb4");  //change
    return $conn;
}

function closeDBConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}
?>