<?php
// api.php
header('Content-Type: application/json');
session_start();

// --- Database Connection ---
$HomeDir = "../../";
include "$HomeDir/db.php";
$page = $_GET['page'] ?? "cadarok";
$Ttable = $page;
include "../ConDb.php";

global $db;
if (!$db) {
    error_log("Database connection error: " . mysqli_connect_error());
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

// --- Input Handling ---
$action = $_REQUEST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// --- API Router ---
switch ($action) {
    case 'getSizes':
        handleGetOptions('sizes');
        break;
    case 'getPapers':
        handleGetOptions('papers');
        break;
    case 'calculatePrice':
        if ($method === 'POST') {
            handleCalculatePrice();
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'POST method required.']);
        }
        break;
    default:
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

mysqli_close($db);

// --- Handler Functions ---

function handleGetOptions($option_type) {
    global $db, $Ttable;
    $category_type = isset($_GET['category_type']) ? (int)$_GET['category_type'] : 0;

    error_log("handleGetOptions called. option_type: " . $option_type . ", category_type: " . $category_type . ", Ttable: " . $Ttable);

    if ($category_type === 0) {
        error_log("category_type is 0. Returning empty array.");
        echo json_encode([]);
        return;
    }

    $query = "";
    $bind_params = [];
    $bind_types = "";

    if ($option_type === 'sizes') {
        // For sizes: Ttable = current, BigNo = category_type, TreeNo = ''
        $query = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE Ttable=? AND BigNo=? AND TreeNo=? ORDER BY no ASC";
        $bind_params = [$Ttable, $category_type, ''];
        $bind_types = "sis";
    } elseif ($option_type === 'papers') {
        // For papers: Ttable = current, BigNo = '', TreeNo = category_type
        $query = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE Ttable=? AND BigNo=? AND TreeNo=? ORDER BY no ASC";
        $bind_params = [$Ttable, '', $category_type];
        $bind_types = "ssi";
    } else {
        error_log("Invalid option type: " . $option_type);
        echo json_encode(['success' => false, 'message' => 'Invalid option type.']);
        return;
    }

    error_log("Query: " . $query);
    error_log("Bind Params: " . implode(", ", $bind_params));
    error_log("Bind Types: " . $bind_types);

    $stmt = mysqli_prepare($db, $query);
    
    if (!$stmt) {
        error_log("DB prepare failed: " . mysqli_error($db));
        echo json_encode(['success' => false, 'message' => 'DB prepare failed: ' . mysqli_error($db)]);
        return;
    }

    mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $options = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    error_log("Fetched options count: " . count($options));
    error_log("Fetched options: " . json_encode($options));

    echo json_encode($options);
    mysqli_stmt_close($stmt);
}

function handleCalculatePrice() {
    global $db;
    $data = json_decode(file_get_contents('php://input'), true);

    $my_type = $data['MY_type'] ?? null;
    $pn_type = $data['PN_type'] ?? null; // This will be mapped to TreeSelect
    $my_fsd = $data['MY_Fsd'] ?? null;
    $my_amount = $data['MY_amount'] ?? null;
    $ordertype = $data['ordertype'] ?? 'print';

    error_log("handleCalculatePrice called with: MY_type=" . $my_type . ", PN_type=" . $pn_type . ", MY_Fsd=" . $my_fsd . ", MY_amount=" . $my_amount);

    if (is_null($my_type) || is_null($pn_type) || is_null($my_fsd) || is_null($my_amount)) {
        error_log("Missing required parameters for price calculation.");
        echo json_encode(['success' => false, 'message' => 'Missing required parameters for price calculation.']);
        return;
    }

    // Query mlangprintauto_cadarok table
    // Mapping: MY_type -> style, MY_Fsd -> Section, MY_amount -> quantity, PN_type -> TreeSelect
    $query = "SELECT money, DesignMoney FROM mlangprintauto_cadarok WHERE style = ? AND Section = ? AND quantity = ? AND TreeSelect = ?";
    $stmt = mysqli_prepare($db, $query);

    if (!$stmt) {
        error_log("DB prepare failed for price calculation: " . mysqli_error($db));
        echo json_encode(['success' => false, 'message' => 'DB prepare failed for price calculation: ' . mysqli_error($db)]);
        return;
    }

    // All parameters appear to be integers based on provided INSERT statements.
    mysqli_stmt_bind_param($stmt, "iiii", $my_type, $my_fsd, $my_amount, $pn_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $price_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($price_data) {
        $print_price = (float)($price_data['money'] ?? 0);
        $design_price = (float)($price_data['DesignMoney'] ?? 0);

        if ($ordertype === 'print') {
            // Logic for 'print' order type. Adjust as per actual business logic.
        }

        $order_price = $print_price + $design_price;
        $vat = $order_price * 0.1; // Assuming 10% VAT
        $total_price = $order_price + $vat;

        $response = [
            'success' => true,
            'message' => 'Price calculated successfully.',
            'data' => [
                'Price' => number_format($print_price),
                'DS_Price' => number_format($design_price),
                'Order_Price' => number_format($order_price),
                'PriceForm' => $print_price,
                'DS_PriceForm' => $design_price,
                'Order_PriceForm' => $order_price,
                'VAT_PriceForm' => $vat,
                'Total_PriceForm' => $total_price,
                'StyleForm' => "카다록",
                'SectionForm' => "",
                'QuantityForm' => $my_amount,
                'DesignForm' => ($design_price > 0) ? "디자인 포함" : "디자인 미포함",
            ]
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'No price data found for the selected options. Please check the database.',
            'data' => [
                'Price' => "0원",
                'DS_Price' => "0원",
                'Order_Price' => "0원",
                'PriceForm' => 0,
                'DS_PriceForm' => 0,
                'Order_PriceForm' => 0,
                'VAT_PriceForm' => 0,
                'Total_PriceForm' => 0,
                'StyleForm' => "",
                'SectionForm' => "",
                'QuantityForm' => 0,
                'DesignForm' => "",
            ]
        ];
    }

    echo json_encode($response);
}