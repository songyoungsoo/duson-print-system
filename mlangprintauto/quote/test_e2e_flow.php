<?php
/**
 * E2E Test Script: Cart → Quotation → Order Flow
 * Tests the complete data flow for leaflet products with 연/매수 structure
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/includes/QuoteManager.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== E2E Test: Leaflet Quotation → Order Flow ===\n\n";

try {
    $manager = new QuoteManager($db);

    // Step 1: Create test quotation directly in database
    echo "Step 1: Creating test quotation\n";
    echo "-----------------------------------------------\n";

    $quoteNo = $manager->generateQuoteNo('quotation');
    $publicToken = $manager->generatePublicToken();

    $insertQuery = "INSERT INTO quotes (
        quote_no, status, customer_name, customer_email, customer_company,
        customer_phone, delivery_type, delivery_address, notes, public_token
    ) VALUES (?, 'draft', ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $insertQuery);
    $customerName = 'E2E 테스트';
    $customerEmail = 'test@example.com';
    $customerCompany = '테스트 회사';
    $customerPhone = '010-1234-5678';
    $deliveryType = '퀵서비스';
    $deliveryAddress = '서울시 테스트구';
    $notes = 'E2E 테스트 견적서';

    mysqli_stmt_bind_param($stmt, "sssssssss",
        $quoteNo, $customerName, $customerEmail, $customerCompany,
        $customerPhone, $deliveryType, $deliveryAddress, $notes, $publicToken
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to create quote: " . mysqli_error($db));
    }

    $quoteId = mysqli_insert_id($db);
    echo "✅ Quote created: ID = $quoteId, No = $quoteNo\n\n";

    // Step 2: Add cart item #947 to quote using reflection to access private method
    echo "Step 2: Adding cart item #947 to quotation\n";
    echo "-----------------------------------------------\n";

    // Get cart item data
    $cartQuery = "SELECT * FROM shop_temp WHERE no = 947";
    $cartResult = mysqli_query($db, $cartQuery);
    $cartItem = mysqli_fetch_assoc($cartResult);

    if (!$cartItem) {
        throw new Exception("Cart item #947 not found");
    }

    // Use reflection to call private addItemFromCart method
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('addItemFromCart');
    $method->setAccessible(true);

    try {
        $result = $method->invoke($manager, $quoteId, 1, $cartItem);
        echo "✅ Cart item added to quote\n";
        echo "Result: " . var_export($result, true) . "\n\n";
    } catch (Exception $e) {
        echo "❌ Exception during addItemFromCart: " . $e->getMessage() . "\n";
        echo "MySQL Error: " . mysqli_error($db) . "\n";
        throw new Exception("Failed to add cart item: " . $e->getMessage());
    }

    // Check if item was actually inserted
    $checkQuery = "SELECT COUNT(*) as count FROM quote_items WHERE quote_id = ?";
    $checkStmt = mysqli_prepare($db, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "i", $quoteId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $checkRow = mysqli_fetch_assoc($checkResult);
    echo "Items in quote: {$checkRow['count']}\n\n";

    // Step 3: Verify quote_items data structure
    echo "Step 3: Verifying quote_items data structure\n";
    echo "-----------------------------------------------\n";

    $query = "SELECT id, product_type, MY_type, PN_type, MY_amount, mesu, unit,
                     ordertype, supply_price, vat_amount, total_price,
                     product_data, formatted_display, additional_options, additional_options_total
              FROM quote_items
              WHERE quote_id = ?
              ORDER BY id DESC LIMIT 1";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $quoteId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $quoteItem = mysqli_fetch_assoc($result);

    if ($quoteItem) {
        echo "Quote Item ID: {$quoteItem['id']}\n";
        echo "Product Type: {$quoteItem['product_type']}\n";
        echo "MY_type (규격): {$quoteItem['MY_type']}\n";
        echo "PN_type (용지): {$quoteItem['PN_type']}\n";
        echo "MY_amount (연수): {$quoteItem['MY_amount']}\n";
        echo "mesu (매수): {$quoteItem['mesu']}\n";
        echo "unit (단위): {$quoteItem['unit']}\n";
        echo "ordertype (인쇄방식): {$quoteItem['ordertype']}\n";
        echo "supply_price (공급가): {$quoteItem['supply_price']}\n";
        echo "vat_amount (VAT): {$quoteItem['vat_amount']}\n";
        echo "total_price (합계): {$quoteItem['total_price']}\n";
        echo "\nproduct_data (JSON):\n";
        if ($quoteItem['product_data']) {
            $pd = json_decode($quoteItem['product_data'], true);
            echo json_encode($pd, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "(NULL)\n";
        }
        echo "\nformatted_display:\n{$quoteItem['formatted_display']}\n";

        // Verify critical fields
        $errors = [];
        if (empty($quoteItem['product_type'])) $errors[] = "product_type is empty";
        if ($quoteItem['MY_amount'] === null || $quoteItem['MY_amount'] == 0) {
            $errors[] = "MY_amount is NULL or 0 (should be 1.00)";
        }
        if ($quoteItem['mesu'] == 0) {
            $errors[] = "mesu is 0 (should be 8000)";
        }
        if (empty($quoteItem['unit'])) $errors[] = "unit is empty";

        if (empty($errors)) {
            echo "\n✅ All critical fields validated successfully!\n\n";
        } else {
            echo "\n❌ Validation errors:\n";
            foreach ($errors as $error) {
                echo "  - $error\n";
            }
            echo "\n";
        }
    } else {
        throw new Exception("Quote item not found");
    }

    // Step 4: Update quote status to 'sent' for conversion eligibility
    echo "Step 4: Updating quote status to 'sent'\n";
    echo "-----------------------------------------------\n";

    $updateQuery = "UPDATE quotes SET status = 'sent' WHERE id = ?";
    $stmt = mysqli_prepare($db, $updateQuery);
    mysqli_stmt_bind_param($stmt, "i", $quoteId);
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Quote status updated to 'sent'\n\n";
    }

    // Step 5: Get quote details for conversion
    echo "Step 5: Testing convert_to_order.php data preparation\n";
    echo "-----------------------------------------------\n";

    $quote = $manager->getQuoteById($quoteId);
    $items = $manager->getQuoteItems($quoteId);

    echo "Quote No: {$quote['quote_no']}\n";
    echo "Items count: " . count($items) . "\n\n";

    if (count($items) > 0) {
        $item = $items[0];
        echo "Testing Type_1 JSON generation:\n";

        // Simulate convert_to_order.php Type_1 JSON generation
        $type1Data = [
            'product_type' => $item['product_type'],
            'product_name' => $item['product_name'],
            'specification' => $item['specification'] ?? '',
            'formatted_display' => $item['formatted_display'] ?? '',
            'source' => 'quote',
            'quote_no' => $quote['quote_no'],
            'quantity' => floatval($item['quantity']),
            'unit' => $item['unit'] ?? '개',
            'supply_price' => intval($item['supply_price']),
            'vat_amount' => intval($item['vat_amount']),
            'total_price' => intval($item['total_price']),
            // 11 new fields
            'MY_type' => $item['MY_type'] ?? '',
            'PN_type' => $item['PN_type'] ?? '',
            'MY_Fsd' => $item['MY_Fsd'] ?? '',
            'POtype' => $item['POtype'] ?? '',
            'MY_amount' => floatval($item['MY_amount'] ?? 0),
            'mesu' => intval($item['mesu'] ?? 0),
            'ordertype' => $item['ordertype'] ?? ''
        ];

        if (!empty($item['product_data'])) {
            $productData = json_decode($item['product_data'], true);
            if ($productData) {
                $type1Data['product_data'] = $productData;
            }
        }

        if (!empty($item['additional_options'])) {
            $additionalOptions = json_decode($item['additional_options'], true);
            if ($additionalOptions) {
                $type1Data['additional_options'] = $additionalOptions;
            }
        }
        $type1Data['additional_options_total'] = intval($item['additional_options_total'] ?? 0);

        echo json_encode($type1Data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        // Verify Type_1 JSON contains all critical fields
        $type1Errors = [];
        if ($type1Data['MY_amount'] == 0) {
            $type1Errors[] = "MY_amount is 0 in Type_1 JSON";
        }
        if ($type1Data['mesu'] == 0) {
            $type1Errors[] = "mesu is 0 in Type_1 JSON";
        }
        if (empty($type1Data['unit'])) {
            $type1Errors[] = "unit is empty in Type_1 JSON";
        }

        if (empty($type1Errors)) {
            echo "✅ Type_1 JSON structure validated successfully!\n\n";
        } else {
            echo "❌ Type_1 JSON validation errors:\n";
            foreach ($type1Errors as $error) {
                echo "  - $error\n";
            }
            echo "\n";
        }
    }

    echo "=== E2E Test Complete ===\n";
    echo "Quote ID: $quoteId\n";
    echo "Quote No: {$quote['quote_no']}\n";
    echo "\nTo complete the test:\n";
    echo "1. Open: http://localhost/mlangprintauto/quote/detail.php?id=$quoteId\n";
    echo "2. Click '주문으로 변환' button\n";
    echo "3. Verify order creation\n";
    echo "4. Check order display in admin panel\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
