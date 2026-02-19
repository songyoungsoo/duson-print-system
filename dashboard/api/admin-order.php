<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST only']);
    exit;
}

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB 연결 실패']);
    exit;
}
mysqli_set_charset($db, 'utf8mb4');

$orderDataJson = $_POST['order_data'] ?? '';
if (empty($orderDataJson)) {
    echo json_encode(['success' => false, 'message' => '주문 데이터 없음']);
    exit;
}

$data = json_decode($orderDataJson, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'JSON 파싱 실패']);
    exit;
}

$customerName = trim($data['customer_name'] ?? '');
$customerPhone = trim($data['customer_phone'] ?? '');
$customerMobile = trim($data['customer_mobile'] ?? '');
$customerEmail = trim($data['customer_email'] ?? '');
$deliveryMethod = $data['delivery_method'] ?? '택배';
$paymentMethod = $data['payment_method'] ?? '계좌이체';
$bankname = trim($data['bankname'] ?? '');
$postcode = trim($data['postcode'] ?? '');
$address = trim($data['address'] ?? '');
$detailAddress = trim($data['detail_address'] ?? '');
$bizname = trim($data['bizname'] ?? '');
$bizText = $data['biz_text'] ?? '';
$logenFeeType = trim($data['logen_fee_type'] ?? '');
$logenDeliveryFee = intval($data['logen_delivery_fee'] ?? 0);
$customerMemo = trim($data['customer_memo'] ?? '');
$adminMemo = trim($data['admin_memo'] ?? '');
$orderStatus = $data['order_status'] ?? '2';
$items = $data['items'] ?? [];

if (empty($customerName)) {
    echo json_encode(['success' => false, 'message' => '주문자 성명 필수']);
    exit;
}
if (empty($customerPhone)) {
    echo json_encode(['success' => false, 'message' => '전화번호 필수']);
    exit;
}
if (empty($items)) {
    echo json_encode(['success' => false, 'message' => '품목 없음']);
    exit;
}

$cont = $customerMemo;
if (!empty($bizText)) {
    $cont .= $bizText;
}
if (!empty($adminMemo)) {
    $cont .= "\n\n[관리자 메모] " . $adminMemo;
}

$productTypeNames = [
    'sticker' => '스티커', 'namecard' => '명함', 'inserted' => '전단지',
    'envelope' => '봉투', 'cadarok' => '카다록', 'littleprint' => '포스터',
    'merchandisebond' => '상품권', 'ncrflambeau' => 'NCR양식지', 'msticker' => '자석스티커'
];

$date = date("Y-m-d H:i:s");
$orderNumbers = [];

try {
    foreach ($items as $item) {
        $maxResult = mysqli_query($db, "SELECT MAX(no) as max_no FROM mlangorder_printauto");
        $maxRow = mysqli_fetch_assoc($maxResult);
        $newNo = ($maxRow['max_no'] ?? 0) + 1;

        $productType = $item['product_type'] ?? '';
        $productName = $item['product_name'] ?? ($productTypeNames[$productType] ?? '기타');
        $typeName = $productTypeNames[$productType] ?? $productName;
        $specification = $item['specification'] ?? '';
        $quantity = floatval($item['quantity'] ?? 1);
        $unit = $item['unit'] ?? '매';
        $quantityDisplay = $item['quantity_display'] ?? '';
        $supplyPrice = intval($item['supply_price'] ?? 0);
        $vatPrice = intval(round($supplyPrice * 1.1));
        $calcData = $item['calculator_data'] ?? null;

        $productInfo = json_encode([
            'admin_order' => true,
            'source' => $item['source'] ?? 'manual',
            'product_type' => $productType,
            'product_name' => $productName,
            'specification' => $specification,
            'quantity' => $quantity,
            'unit' => $unit,
            'quantity_display' => $quantityDisplay,
            'supply_price' => $supplyPrice,
            'calculator_data' => $calcData
        ], JSON_UNESCAPED_UNICODE);

        $imgFolderPath = "uploads/orders/" . $newNo . "/";
        $thingCate = '';

        $stPrice = strval($supplyPrice);
        $stPriceVat = strval($vatPrice);

        $specType = $typeName;
        $specMaterial = '';
        $specSize = '';
        $specSides = '';
        $specDesign = '';
        $quantityValue = $quantity;
        $quantityUnit = $unit;
        $quantitySheets = 0;
        $priceSupply = $supplyPrice;
        $priceVat = $vatPrice;
        $priceVatAmount = intval(round($supplyPrice * 0.1));
        $dataVersion = 2;

        if ($calcData) {
            $specMaterial = $calcData['spec_material'] ?? $calcData['paper_type_text'] ?? '';
            $specSize = $calcData['spec_size'] ?? $calcData['paper_size_text'] ?? '';
            $specSides = $calcData['spec_sides'] ?? $calcData['sides_text'] ?? '';
            $specDesign = $calcData['spec_design'] ?? $calcData['design_text'] ?? '';
            if (!empty($calcData['quantity_value'])) $quantityValue = floatval($calcData['quantity_value']);
            if (!empty($calcData['quantity_unit'])) $quantityUnit = $calcData['quantity_unit'];
            if (!empty($calcData['quantity_sheets'])) $quantitySheets = intval($calcData['quantity_sheets']);
        }

        $uploadedFilesJson = null;
        $coatingEnabled = 0; $coatingType = ''; $coatingPrice = 0;
        $foldingEnabled = 0; $foldingType = ''; $foldingPrice = 0;
        $creasingEnabled = 0; $creasingLines = 0; $creasingPrice = 0;
        $additionalOptionsTotal = 0;
        $premiumOptions = ''; $premiumOptionsTotal = 0;
        $envelopeTapeEnabled = 0; $envelopeTapeQty = 0; $envelopeTapePrice = 0;
        $envelopeAdditionalTotal = 0;

        if ($calcData) {
            $coatingEnabled = intval($calcData['coating_enabled'] ?? 0);
            $coatingType = $calcData['coating_type'] ?? '';
            $coatingPrice = intval($calcData['coating_price'] ?? 0);
            $foldingEnabled = intval($calcData['folding_enabled'] ?? 0);
            $foldingType = $calcData['folding_type'] ?? '';
            $foldingPrice = intval($calcData['folding_price'] ?? 0);
            $creasingEnabled = intval($calcData['creasing_enabled'] ?? 0);
            $creasingLines = intval($calcData['creasing_lines'] ?? 0);
            $creasingPrice = intval($calcData['creasing_price'] ?? 0);
            $additionalOptionsTotal = intval($calcData['additional_options_total'] ?? 0);
            $premiumOptions = $calcData['premium_options'] ?? '';
            $premiumOptionsTotal = intval($calcData['premium_options_total'] ?? 0);
            $envelopeTapeEnabled = intval($calcData['envelope_tape_enabled'] ?? 0);
            $envelopeTapeQty = intval($calcData['envelope_tape_quantity'] ?? 0);
            $envelopeTapePrice = intval($calcData['envelope_tape_price'] ?? 0);
            $envelopeAdditionalTotal = intval($calcData['envelope_additional_options_total'] ?? 0);
        }

        $insertQuery = "INSERT INTO mlangorder_printauto (
            no, Type, product_type, ImgFolder, uploaded_files, Type_1, money_4, money_5, name, email, zip, zip1, zip2,
            phone, Hendphone, delivery, bizname, bank, bankname, cont, date, OrderStyle, ThingCate,
            coating_enabled, coating_type, coating_price,
            folding_enabled, folding_type, folding_price,
            creasing_enabled, creasing_lines, creasing_price,
            additional_options_total,
            premium_options, premium_options_total,
            envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price,
            envelope_additional_options_total, unit, quantity,
            spec_type, spec_material, spec_size, spec_sides, spec_design,
            quantity_value, quantity_unit, quantity_sheets, quantity_display,
            price_supply, price_vat, price_vat_amount, data_version,
            logen_fee_type, logen_delivery_fee
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($db, $insertQuery);
        if (!$stmt) {
            throw new Exception('쿼리 준비 실패: ' . mysqli_error($db));
        }

        // bind_param 3-step verification (AGENTS.md mandatory rule)
        $typeString = 'issssssssssssssssssssssisiisiiiiisiiiiisdsssssdsisiiiisi';
        $placeholderCount = substr_count($insertQuery, '?');
        $typeCount = strlen($typeString);
        $varCount = 56;

        if ($placeholderCount !== $typeCount || $typeCount !== $varCount) {
            throw new Exception("bind_param 불일치: placeholder=$placeholderCount, type=$typeCount, var=$varCount");
        }

        mysqli_stmt_bind_param($stmt, $typeString,
            $newNo, $typeName, $productType, $imgFolderPath, $uploadedFilesJson, $productInfo, $stPrice, $stPriceVat,
            $customerName, $customerEmail, $postcode, $address, $detailAddress,
            $customerPhone, $customerMobile, $deliveryMethod, $bizname, $paymentMethod, $bankname, $cont, $date, $orderStatus, $thingCate,
            $coatingEnabled, $coatingType, $coatingPrice,
            $foldingEnabled, $foldingType, $foldingPrice,
            $creasingEnabled, $creasingLines, $creasingPrice,
            $additionalOptionsTotal,
            $premiumOptions, $premiumOptionsTotal,
            $envelopeTapeEnabled, $envelopeTapeQty, $envelopeTapePrice,
            $envelopeAdditionalTotal,
            $unit, $quantity,
            $specType, $specMaterial, $specSize, $specSides, $specDesign,
            $quantityValue, $quantityUnit, $quantitySheets, $quantityDisplay,
            $priceSupply, $priceVat, $priceVatAmount, $dataVersion,
            $logenFeeType, $logenDeliveryFee
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('INSERT 실패 (no=' . $newNo . '): ' . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);

        // 교정 파일 업로드 처리
        if (!empty($_FILES['proof_files']) && !empty($_FILES['proof_files']['name'][0])) {
            $uploadDir = __DIR__ . '/../../mlangorder_printauto/upload/' . $newNo . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $savedFiles = [];
            $fileCount = count($_FILES['proof_files']['name']);
            for ($fi = 0; $fi < $fileCount; $fi++) {
                if ($_FILES['proof_files']['error'][$fi] !== UPLOAD_ERR_OK) continue;
                if ($_FILES['proof_files']['size'][$fi] > 20 * 1024 * 1024) continue;

                $originalName = $_FILES['proof_files']['name'][$fi];
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','pdf','ai','psd','zip'];
                if (!in_array($ext, $allowed)) continue;

                $savedName = date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
                $destPath = $uploadDir . $savedName;

                if (move_uploaded_file($_FILES['proof_files']['tmp_name'][$fi], $destPath)) {
                    $savedFiles[] = [
                        'original_name' => $originalName,
                        'saved_name' => $savedName,
                        'size' => $_FILES['proof_files']['size'][$fi],
                        'type' => $ext
                    ];
                }
            }

            if (!empty($savedFiles)) {
                $thingCateJson = json_encode($savedFiles, JSON_UNESCAPED_UNICODE);
                $updateStmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET ThingCate = ? WHERE no = ?");
                if ($updateStmt) {
                    mysqli_stmt_bind_param($updateStmt, 'si', $thingCateJson, $newNo);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                }
            }
        }

        $orderNumbers[] = $newNo;
    }

    echo json_encode([
        'success' => true,
        'message' => count($orderNumbers) . '건 주문 등록 완료',
        'order_numbers' => $orderNumbers
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($db) && $db) {
    mysqli_close($db);
}
