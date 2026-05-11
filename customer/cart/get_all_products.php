<?php

require_once '../order_fn.php';
header('Content-Type: application/json');

echo json_encode(getAllAvailableProducts());
