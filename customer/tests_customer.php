<?php
require_once 'functions.php';
require_once 'order_fn.php';

// Test counter
$tests_run = 0;
$tests_passed = 0;
$tests_failed = 0;

function assert_test($condition, $test_name, $expected, $actual) {
    global $tests_run, $tests_passed, $tests_failed;
    $tests_run++;
    if ($condition) {
        echo "✓ PASS: $test_name\n";
        $tests_passed++;
    } else {
        echo "FAIL: $test_name\n";
        echo "  Expected: " . json_encode($expected) . "\n";
        echo "  Actual: " . json_encode($actual) . "\n";
        $tests_failed++;
    }
}

echo "--- UNIT TESTS ---\n\n";

// Test 1: validateCreditCardSimple
echo "Testing validateCreditCardSimple():\n";

$valid_card = validateCreditCardSimple("4111111111111111", "12/30");
assert_test($valid_card === true, "Valid card (16 digits, future expiry)", true, $valid_card);

$invalid_length = validateCreditCardSimple("1234", "12/30");
assert_test($invalid_length === false, "Invalid card length (<16 digits)", false, $invalid_length);

$invalid_expiry_format = validateCreditCardSimple("4111111111111111", "13/25"); // month 13 doesnot exist
assert_test($invalid_expiry_format === false, "Invalid expiry format (invalid month)", false, $invalid_expiry_format);

$expired_card = validateCreditCardSimple("4111111111111111", "01/20");
assert_test($expired_card === false, "Expired card", false, $expired_card);

echo "\n--- INTEGRATION TESTS ---\n\n";
echo "Testing registration and login flow:\n";

$test_username = "testuser_" . time();

$register_result = registerCustomer(
    $test_username,
    "TestPass123",
    "Test",
    "User",
    "test_" . time() . "@example.com",
    "1234567890",
    "123 Test St"
);

assert_test(
    $register_result['success'] === true,
    "Register new customer",
    true,
    $register_result['success']
);

if ($register_result['success']) {
    $customer_id = $register_result['customer_id'];
    
    // Test login
    $login_result = loginCustomer($test_username, "TestPass123");
    assert_test(
        $login_result['success'] === true,
        "Login with correct credentials",
        true,
        $login_result['success']
    );
    
    // Test get profile
    $profile = getCustomerProfile($customer_id);
    assert_test(
        $profile !== null && $profile['username'] === $test_username,
        "Get customer profile",
        $test_username,
        $profile['username'] ?? null
    );
}

echo "\n--- CART INTEGRATION TESTS ---\n\n";
if (isset($customer_id)) {
    // Get available products
    $products = getAllAvailableProducts();
    
    if ($products['success'] && !empty($products['products'])) {
        $test_product = $products['products'][0];
        $product_id = $test_product['product_id'];
        
        echo "Testing with product: " . $test_product['product_name'] . "\n";
        
        // Test add to cart
        $add_result = addToCart($customer_id, $product_id, 1);
        assert_test(
            $add_result['success'] === true,
            "Add item to cart",
            true,
            $add_result['success']
        );
        
        // Test view cart
        $cart = viewCart($customer_id);
        assert_test(
            $cart['success'] === true,
            "View cart",
            true,
            $cart['success']
        );
        
        // Test update quantity
        $update_result = updateCartQuantity($customer_id, $product_id, 3);
        assert_test(
            $update_result['success'] === true,
            "Update cart quantity",
            true,
            $update_result['success']
        );
        
        // Test checkout with invalid card (should fail)
        $checkout_fail = checkoutSimplified($customer_id, "1234", "12/25");
        assert_test(
            $checkout_fail['success'] === false,
            "Checkout with invalid card",
            false,
            $checkout_fail['success']
        );
        
        // Test checkout with valid card
        $checkout_success = checkoutSimplified($customer_id, "4111111111111111", "12/30");
        assert_test(
            $checkout_success['success'] === true,
            "Checkout with valid card",
            true,
            $checkout_success['success']
        );
        
        if ($checkout_success['success']) {
            // Test order history
            $orders = getOrderHistory($customer_id);
            assert_test(
                !empty($orders),
                "Order history contains new order",
                true,
                !empty($orders)
            );
        }
    } else {
        echo "No products available for testing\n";
    }
}

echo "\n--- PROFILE MANAGEMENT TESTS ---\n\n";

if (isset($customer_id)) {
    // Test update profile
    $update_result = updateCustomerProfile(
        $customer_id,
        $test_username . "_updated",
        "Updated",
        "Name",
        "updated_" . time() . "@example.com",
        "0987654321",
        "456 New Address"
    );
    
    assert_test(
        $update_result['success'] === true,
        "Update customer profile",
        true,
        $update_result['success']
    );
    
    // Test change password
    $password_result = changePassword($customer_id, "TestPass123", "NewPass456");
    assert_test(
        $password_result['success'] === true,
        "Change password with correct old password",
        true,
        $password_result['success']
    );
    
    $wrong_password_result = changePassword($customer_id, "WrongPass", "NewPass456");
    assert_test(
        $wrong_password_result['success'] === false,
        "Change password with wrong old password (should fail)",
        false,
        $wrong_password_result['success']
    );
}

echo "\n--- TEST SUMMARY ---\n";
echo "Total tests run: $tests_run\n";
echo "Tests passed: $tests_passed\n";
echo "Tests failed: $tests_failed\n";
if ($tests_failed === 0) {
    echo "\n ALL TESTS PASSED\n";
} else {
    echo "\n SOME TESTS FAILED\n";
}
?>
