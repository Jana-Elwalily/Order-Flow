<?php

require_once '../product_fn.php';
header('Content-Type: application/json');

echo json_encode(getAllAvailableProducts());
