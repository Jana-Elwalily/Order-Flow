<?php
require_once 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function searchProducts($search_type, $search_value) {
    global $conn;

     $query = "
        SELECT
            p.product_id,
            p.product_name,
            p.description,
            p.price,
            p.category,
            p.stock_quantity,
            s.name AS seller_name

        FROM Product p
        LEFT JOIN Seller s
        ON p.seller_id = s.seller_id
    ";
    $params = [];
     switch ($search_type) {

        case 'product_name':
            $query .= " WHERE p.product_name LIKE ?";
            $params[] = "%$search_value%";
            break;

        case 'category':
            $query .= " WHERE p.category = ?";
            $params[] = $search_value;
            break;

        case 'seller':
            $query .= " WHERE s.name LIKE ?";
            $params[] = "%$search_value%";
            break;
    }
    $stmt = $conn->prepare($query);
    if (!empty($params)) $stmt->bind_param(str_repeat("s", count($params)), ...$params);

    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    return ['success' => true, 'products' => $products];
}

function getProductDetails($product_id) {
    global $conn;
    $stmt = $conn->prepare("
SELECT p.product_name,p.description,p.price,p.category,
    p.stock_quantity,p.threshold,s.name AS seller_name
FROM Product p
LEFT JOIN Seller s
ON p.seller_id = s.seller_id
WHERE p.product_id = ?"
    );
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Product not found'];
    }
    return ['success' => true, 'product' => $result->fetch_assoc()];
}
function getAllAvailableProducts(): array {
    global $conn;
 
    $result = $conn->query(
        "SELECT p.product_id, p.product_name, p.description, p.price,
                p.category, p.stock_quantity,
                s.name AS seller_name
         FROM product p
         LEFT JOIN seller s ON p.seller_id = s.seller_id
         WHERE p.stock_quantity > 0
         ORDER BY p.product_name ASC"
    );
 
    if (!$result) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
 
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
 
    return ['success' => true, 'products' => $products];
}
#SHOPPING CART

function getOrCreateCart($customer_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT cart_id FROM Shopping_Cart WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0){
         return $result->fetch_assoc()['cart_id'];
    }

    $stmt = $conn->prepare("INSERT INTO Shopping_Cart (customer_id) VALUES (?)");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $conn->insert_id;
}

function addToCart($customer_id, $product_id, $quantity) {
    global $conn;
    $stmt = $conn->prepare("SELECT stock_quantity FROM Product WHERE product_id = ?");
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) 
        {
            return ['success' => false, 'message' => 'product not found'];
        }
//Check stock availability
    if ($product['stock_quantity'] < $quantity) return ['success' => false, 'message' => 'Insufficient stock'];

    $cart_id = getOrCreateCart($customer_id);
//Check if item already exists in cart
    $stmt = $conn->prepare("SELECT quantity FROM Cart_Item WHERE cart_id = ? AND product_id = ?");
    $stmt->bind_param("is", $cart_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
//Update or insert
    if ($result->num_rows > 0) {
        $new_qty = $result->fetch_assoc()['quantity'] + $quantity;
        if ($product['stock_quantity'] < $new_qty){
             return ['success' => false, 'message' => 'Not enough stock'];
        }
        $stmt = $conn->prepare("UPDATE Cart_Item SET quantity = ? WHERE cart_id = ? AND product_id = ?");
        $stmt->bind_param("iis", $new_qty, $cart_id, $product_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO Cart_Item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $cart_id, $product_id, $quantity);
    }
    $stmt->execute();
    return ['success' => true];
}

function viewCart($customer_id) {
    global $conn;
    $cart_id = getOrCreateCart($customer_id);
    $stmt = $conn->prepare("
        SELECT
            ci.product_id,p.product_name,p.price,ci.quantity,p.category,p.stock_quantity,
            (p.price * ci.quantity) AS item_total,
            s.name AS seller_name
        FROM Cart_Item ci
        JOIN Product p ON ci.product_id = p.product_id
        LEFT JOIN Seller s ON p.seller_id = s.seller_id
        WHERE ci.cart_id = ?
    ");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
        $total += $row['item_total'];
    }
    return [
        'success' => true,
        'items' => $items,
        'total' => $total
    ];
}

function updateCartQuantity($customer_id, $product_id, $quantity) {
    global $conn;
    if ($quantity <= 0){
         return removeFromCart($customer_id, $product_id);
    }
    $stmt = $conn->prepare("SELECT stock_quantity FROM Product WHERE product_id = ?");
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $stock = $stmt->get_result()->fetch_assoc()['stock_quantity'];
    if ($stock < $quantity){ 
        return ['success' => false, 'message' => 'Insufficient stock'];
    }
    $cart_id = getOrCreateCart($customer_id);
    $stmt = $conn->prepare("UPDATE Cart_Item SET quantity = ? WHERE cart_id = ? AND product_id= ?");
    $stmt->bind_param("iis", $quantity, $cart_id, $product_id);
    $stmt->execute();
    return ['success' => true];
}

function removeFromCart($customer_id, $product_id) {
    global $conn;
    $cart_id = getOrCreateCart($customer_id);
    $stmt = $conn->prepare("DELETE FROM Cart_Item WHERE cart_id = ? AND product_id = ?");
    $stmt->bind_param("is", $cart_id, $product_id);
    $stmt->execute();
    return ['success' => true];
}

function clearCart($customer_id) {
    global $conn;
    $cart_id = getOrCreateCart($customer_id);
    $stmt = $conn->prepare("DELETE FROM Cart_Item WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    return ['success' => true];
}
#CHECKOUT
function validateCreditCardSimple($card_number, $expiry_date) {
    // Remove all non-digits
    $card_number = preg_replace('/\D/', '', $card_number);
    
    // Basic length check (most cards are 16 digits)
    if (strlen($card_number) != 16) {
        return false;
    }
    
    // Check all characters are digits
    if (!ctype_digit($card_number)) {
        return false;
    }
    
    // Check expiry date format (MM/YY)
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry_date)) {
        return false;
    }
    
    // Parse expiry date
    list($month, $year) = explode('/', $expiry_date);
    $expiry_year = '20' . $year; // Convert YY to YYYY
    $expiry_month = (int)$month;
    
    // Check if expiry date is in the future
    $current_year = (int)date('Y');
    $current_month = (int)date('m');
    
    if ($expiry_year < $current_year) {
        return false;
    }
    
    if ($expiry_year == $current_year && $expiry_month < $current_month) {
        return false;
    }
    
    return true;
}

function checkoutSimplified($customer_id, $card_number, $expiry_date) {
    global $conn;
    
    // SIMPLE VALIDATION - Perfect for student project
    $card_number = preg_replace('/\s+/', '', $card_number);
    $clean_card = preg_replace('/\D/', '', $card_number);
    
    // 1. Basic card validation
    if (strlen($clean_card) !== 16 || !is_numeric($clean_card)) {
        return ['success' => false, 'message' => 'Invalid card number (must be 16 digits)'];
    }
    
    // 2. Basic expiry validation
    if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry_date, $matches)) {
        return ['success' => false, 'message' => 'Invalid expiry date (use MM/YY format)'];
    }
    
    $month = (int)$matches[1];
    $year = (int)('20' . $matches[2]);
    $current_year = (int)date('Y');
    $current_month = (int)date('m');
    
    if ($year < $current_year || ($year == $current_year && $month < $current_month)) {
        return ['success' => false, 'message' => 'Card has expired'];
    }
    
    // 3. Check cart
    $cart_data = viewCart($customer_id);
    if (empty($cart_data['items'])) {
        return ['success' => false, 'message' => 'Cart is empty'];
    }
    
    // 4. Process transaction
    $conn->begin_transaction();
    
    try {
        $total = $cart_data['total'];
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO `Order` (customer_id, total_amount) VALUES (?, ?)");
        $stmt->bind_param("id", $customer_id, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Insert order items (stock deducted by trigger)
        $stmt = $conn->prepare("INSERT INTO Order_Item (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        foreach ($cart_data['items'] as $item) {
            $stmt->bind_param("isid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();      
        }
        
        // Clear cart
        clearCart($customer_id);
        
        $conn->commit();
        
        return [
            'success' => true, 
            'order_id' => $order_id,
            'total' => $total,
            'message' => 'Order placed successfully!'
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()];
    }
}

#ORDER HISTORY 
function getOrderHistory($customer_id) {
    global $conn;

    // Fixed GROUP BY to include o.order_date and o.total_amount
    $stmt = $conn->prepare("
         SELECT
            o.order_id,
            o.order_date,
            o.total_amount,

            oi.product_id,
            p.product_name,
            oi.quantity,
            oi.price_at_purchase,
            (oi.quantity * oi.price_at_purchase) AS item_total,

            s.name AS seller_name
        FROM `Order` o
        JOIN Order_Item oi ON o.order_id = oi.order_id
        JOIN Product p ON oi.product_id = p.product_id
        LEFT JOIN Seller s ON p.seller_id = s.seller_id
        WHERE o.customer_id = ?
        ORDER BY o.order_date DESC
    ");

    if (!$stmt) {
        error_log('SQL prepare error: ' . $conn->error);
        return [];
    }

    $stmt->bind_param("i", $customer_id);
    
    if (!$stmt->execute()) {
        error_log('SQL execute error: ' . $stmt->error);
        return [];
    }
    
    $result = $stmt->get_result();
    $orders = [];

    while ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];

        // Create order entry if it doesn't exist
        if (!isset($orders[$order_id])) {
            $orders[$order_id] = [
                'order_id' => $order_id,
                'order_date' => $row['order_date'],
                'total_amount' => $row['total_amount'],
                'items' => []
            ];
        }

        // Add item to order
        $orders[$order_id]['items'][] = [
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'seller_name' => $row['seller_name'],
            'quantity' => $row['quantity'],
            'price_at_purchase' => $row['price_at_purchase'],
            'item_total' => $row['item_total']
        ];
    }

    $stmt->close();
    
    // Return orders as indexed array
    return array_values($orders);
}

?>
