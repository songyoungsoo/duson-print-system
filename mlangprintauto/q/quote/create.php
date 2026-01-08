<?php
/**
 * 견적서 작성 페이지 (엑셀 스타일)
 * - 장바구니 연동 (?from=cart)
 * - 빈 견적서 수동 입력 (기본)
 */

session_start();

// 캐시 방지 - 삭제 후 즉시 반영
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once __DIR__ . '/includes/QuoteManager.php';

$manager = new QuoteManager($db);
$formatter = new ProductSpecFormatter($db);
$company = $manager->getCompanySettings();

// 장바구니에서 온 경우
$fromCart = ($_GET['from'] ?? '') === 'cart';
$cartItems = [];

error_log("create.php 장바구니 체크 - fromCart: " . ($fromCart ? 'true' : 'false') . ", session_id: " . session_id());

if ($fromCart) {
    $sessionId = session_id();
    $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no ASC";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $sessionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $cartItems[] = $row;
    }
    mysqli_stmt_close($stmt);
    error_log("create.php 장바구니 품목 로드: " . count($cartItems) . "개");
}

// quotation_temp에서 품목 로드 (계산기 모달에서 추가한 품목)
$quoteTempItems = [];
$sessionId = session_id();
$query = "SELECT * FROM quotation_temp WHERE session_id = ? ORDER BY regdate ASC";
$stmt = mysqli_prepare($db, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $sessionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $quoteTempItems[] = $row;
    }
    mysqli_stmt_close($stmt);
}
error_log("quotation_temp 품목 로드: " . count($quoteTempItems) . "개");

// 금액 계산 (장바구니 + quotation_temp)
$supplyTotal = 0;
$vatTotal = 0;

/**
 * ========================================
 * 가격 계산 (CRITICAL - 필드명 주의!)
 * ========================================
 *
 * 필드 구조:
 * - price_supply (또는 st_price)      = 공급가액 (부가세 제외)
 * - price_vat (또는 st_price_vat)     = 합계금액 (공급가 + 부가세) ⚠️ 필드명 오해!
 * - price_vat_amount                   = 부가세액
 *
 * ✅ 올바른 계산:
 * $vat = $total - $supply;             // 부가세액 = 합계 - 공급가
 *
 * ❌ 잘못된 계산 (절대 금지):
 * $total = $supply + $priceVat;        // price_vat는 부가세가 아님!
 * $unitPrice = $supply / $quantity;    // 역계산 금지!
 */

// 장바구니 품목
foreach ($cartItems as $item) {
    // ✅ 사용자 요구: DB 값 그대로 사용, 역계산 절대 금지!
    $supply = !empty($item['price_supply'])
        ? intval($item['price_supply'])  // Phase 3 필드 (우선)
        : intval($item['st_price'] ?? 0);  // 레거시 필드 (fallback, 역계산 안함!)

    $total = !empty($item['price_vat'])
        ? intval($item['price_vat'])  // Phase 3 필드 (우선, 합계금액!)
        : intval($item['st_price_vat'] ?? 0);  // 레거시 필드 (fallback)

    $supplyTotal += $supply;
    $vatTotal += ($total - $supply);  // 부가세 = 합계 - 공급가
}

// quotation_temp 품목
foreach ($quoteTempItems as $item) {
    // ✅ 사용자 요구: DB 값 그대로 사용, 역계산 절대 금지!
    $supply = !empty($item['price_supply'])
        ? intval($item['price_supply'])  // Phase 3 필드 (우선)
        : intval($item['st_price'] ?? 0);  // 레거시 필드 (fallback, 역계산 안함!)

    $total = !empty($item['price_vat'])
        ? intval($item['price_vat'])  // Phase 3 필드 (우선)
        : intval($item['st_price_vat'] ?? 0);  // 레거시 필드 (fallback)

    $vat = $total - $supply;
    $supplyTotal += $supply;
    $vatTotal += $vat;
}

$grandTotal = $supplyTotal + $vatTotal;

// 문서 유형
$quoteType = $_GET['type'] ?? 'quotation';
$typeLabel = $quoteType === 'transaction' ? '거래명세표' : '견적서';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $typeLabel; ?> 작성 - 두손기획인쇄</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="includes/calculator_modal.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans KR', sans-serif; background: #f0f0f0; font-size: 13px; }

        /* 채팅 모달 숨김 (관리자 견적서 작성 페이지) */
        .chat-widget,
        .chat-button,
        .chat-modal,
        #chat-widget,
        .tawk-widget,
        .crisp-client,
        #tidio-chat,
        [class*="chat-"],
        [id*="chat-"] {
            display: none !important;
            visibility: hidden !important;
        }

        .container { max-width: 900px; margin: 0 auto; padding: 12px; }

        /* 헤더 */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            background: #fff;
            padding: 10px 18px;
            border: 1px solid #ccc;
        }
        .header h1 { font-size: 16px; font-weight: bold; }
        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            margin-right: 10px;
        }
        .back-link:hover { color: #333; }

        /* 알림 박스 */
        .alert {
            padding: 10px 15px;
            margin-bottom: 12px;
            border: 1px solid;
            font-size: 13px;
        }
        .alert-info { background: #cff4fc; border-color: #17a2b8; color: #055160; }
        .alert-danger { background: #f8d7da; border-color: #dc3545; color: #842029; }
        .alert-success { background: #d1e7dd; border-color: #28a745; color: #0f5132; }

        /* 섹션 박스 */
        .section {
            background: #fff;
            border: 1px solid #8c8c8c;
            margin-bottom: 12px;
        }
        .section-header {
            background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 100%);
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #8c8c8c;
        }
        .section-body { padding: 12px; }

        /* 폼 스타일 */
        .form-row {
            display: flex;
            gap: 12px;
            margin-bottom: 10px;
        }
        .form-group {
            flex: 1;
            display: flex;
            align-items: center;
        }
        .form-group label {
            display: inline-block;
            width: 100px;
            font-size: 12px;
            color: #555;
            margin-right: 8px;
            flex-shrink: 0;
        }
        .form-group.required label::after { content: ' *'; color: #dc3545; }
        .form-group input,
        .form-group select,
        .form-group textarea {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid #ccc;
            font-size: 13px;
            background: #fff;
        }
        .form-group textarea {
            display: block;
            width: 100%;
            flex: none;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #217346;
            outline: none;
        }

        /* 엑셀 스타일 테이블 */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
        }
        .excel-table th {
            background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 100%);
            border: 1px solid #8c8c8c;
            padding: 7px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            color: #333;
            white-space: nowrap;
        }
        .excel-table td {
            border: 1px solid #c0c0c0;
            padding: 4px 6px;
            font-size: 13px;
            vertical-align: middle;
        }
        .excel-table tbody tr:hover { background: #e8f4fc; }

        .excel-table td input,
        .excel-table td select {
            width: 100%;
            border: none;
            padding: 4px;
            font-size: 13px;
            background: transparent;
        }
        .excel-table td input:focus,
        .excel-table td select:focus {
            outline: 1px solid #217346;
            background: #fff;
        }
        .excel-table td input[type="number"] { text-align: right; }
        .excel-table td input[readonly] { color: #555; background: #fafafa; }
        .excel-table .col-name { position: relative; }
        .excel-table .col-name input[name*="custom"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 4px 6px;
        }

        /* 직접입력 필드 스타일 */
        .direct-input-field {
            width: 100%;
            padding: 4px 6px;
            border: 1px solid #ccc;
            font-size: 12px;
            cursor: text;
        }
        .direct-input-field:focus {
            border-color: #4a90d9;
            outline: none;
        }
        .direct-input-field::placeholder {
            color: #888;
            font-size: 11px;
        }

        /* 제품 드롭다운 스타일 */
        .product-select {
            width: 100%;
            padding: 4px 6px;
            border: 1px solid #4a90d9;
            font-size: 12px;
            cursor: pointer;
            background: #fff;
        }

        .col-no { width: 35px; text-align: center; }
        .col-name { width: 110px; text-align: center; }
        .col-spec { text-align: center; }
        .col-qty { width: 80px; text-align: center; }
        .col-unit { width: 33px; text-align: center; }
        .col-price { width: 55px; text-align: center; }
        .col-supply { width: 85px; text-align: right; font-family: 'Consolas', monospace; }
        .col-vat { width: 70px; text-align: center; font-family: 'Consolas', monospace; }
        .col-total { width: 95px; text-align: center; font-family: 'Consolas', monospace; }
        .col-action { width: 40px; text-align: center; }

        /* 합계 영역 */
        .total-section {
            background: #fafafa;
            border: 1px solid #c0c0c0;
            padding: 12px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13px;
        }
        .total-row input {
            width: 120px;
            padding: 4px 8px;
            border: 1px solid #ccc;
            text-align: right;
            font-size: 13px;
        }
        .total-row.grand {
            font-size: 15px;
            font-weight: bold;
            color: #217346;
            border-top: 2px solid #217346;
            margin-top: 8px;
            padding-top: 12px;
        }

        /* 버튼 */
        .btn {
            padding: 6px 14px;
            border: 1px solid #ccc;
            background: #f8f8f8;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            color: #333;
        }
        .btn:hover { background: #e0e0e0; }
        .btn-primary { background: #217346; color: #fff; border-color: #217346; }
        .btn-primary:hover { background: #1a5c38; }
        .btn-success { background: #28a745; color: #fff; border-color: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: #fff; border-color: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-sm { padding: 3px 8px; font-size: 12px; }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 15px;
            padding: 15px;
            background: #fff;
            border: 1px solid #ccc;
        }

        /* 로딩/모달 */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: #fff;
            border: 1px solid #8c8c8c;
            padding: 20px;
            text-align: center;
            min-width: 300px;
        }
        .modal-content h3 { margin-bottom: 12px; font-size: 15px; }
        .modal-content p { margin-bottom: 12px; font-size: 13px; }
        .spinner {
            width: 36px; height: 36px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #217346;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 12px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <!-- CACHE TEST: <?php echo date('Y-m-d H:i:s'); ?> -->
    <div class="container">
        <!-- 헤더 -->
        <div class="header">
            <h1>
                <a href="index.php" class="back-link">←</a>
                <?php echo $typeLabel; ?> 작성
            </h1>
        </div>

        <?php if ($fromCart && count($cartItems) > 0): ?>
        <div class="alert alert-info">
            장바구니에서 <?php echo count($cartItems); ?>개 품목을 불러왔습니다.
        </div>
        <?php elseif ($fromCart && count($cartItems) === 0): ?>
        <div class="alert alert-danger">
            장바구니가 비어있습니다. 수동으로 품목을 추가해주세요.
        </div>
        <?php endif; ?>

        <?php if (count($quoteTempItems) > 0): ?>
        <div class="alert alert-success">
            💰 계산기 모달에서 <?php echo count($quoteTempItems); ?>개 품목이 임시 저장되었습니다.
        </div>
        <?php endif; ?>

        <form id="quoteForm">
            <input type="hidden" name="quote_type" value="<?php echo $quoteType; ?>">
            <input type="hidden" name="from_cart" value="<?php echo $fromCart ? '1' : '0'; ?>">

            <!-- 고객 정보 -->
            <div class="section">
                <div class="section-header">고객 정보</div>
                <div class="section-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>회사명</label>
                            <input type="text" name="customer_company" placeholder="(주)회사명">
                        </div>
                        <div class="form-group required">
                            <label>고객명 (담당자)</label>
                            <input type="text" name="customer_name" required placeholder="홍길동">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>연락처</label>
                            <input type="tel" name="customer_phone" placeholder="010-1234-5678">
                        </div>
                        <div class="form-group required">
                            <label>고객 이메일 (견적서 발송 대상)</label>
                            <input type="email" name="customer_email" required placeholder="customer@example.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 품목 목록 -->
            <div class="section">
                <div class="section-header">품목 목록</div>
                <div class="section-body" style="padding: 0;">
                    <table class="excel-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th class="col-no">NO</th>
                                <th class="col-name">품명</th>
                                <th class="col-spec">규격/사양</th>
                                <th class="col-qty">수량</th>
                                <th class="col-price">단가</th>
                                <th class="col-supply">공급가액</th>
                                <th class="col-vat">VAT</th>
                                <th class="col-total">합계</th>
                                <th class="col-action"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php
                            $index = 0;

                            // 1. 장바구니 품목 표시
                            if ($fromCart && count($cartItems) > 0):
                                foreach ($cartItems as $item):
                                    $productType = $item['product_type'] ?? '';
                                    if (empty($productType) && !empty($item['jong'])) $productType = 'sticker';
                                    $productName = ProductSpecFormatter::getProductTypeName($productType);
                                    $spec = $formatter->format($item);
                                    $qty = ProductSpecFormatter::getQuantity($item);

                                    // ✅ Phase 3: quantity_display 필드 우선 사용 (shop_temp도 Phase 3 지원)
                                    // ✅ 수정: 단위 체크 추가 (quotation_temp와 동일 패턴)
                                    if (!empty($item['quantity_display']) && preg_match('/[매연부권개장]/u', $item['quantity_display'])) {
                                        // Phase 3 표준 필드 사용 (단위 있는 경우만)
                                        $qtyDisplay = $item['quantity_display'];
                                    } else {
                                        // Fallback: 레거시 로직 (단위 없거나 빈값일 때)
                                        $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';
                                        if (!empty($item['mesu'])) {
                                            $qtyDisplay = number_format($item['mesu']) . $unit;
                                        } elseif (!empty($item['MY_amount'])) {
                                            $qtyDisplay = htmlspecialchars($item['MY_amount']) . $unit;
                                        } else {
                                            $qtyDisplay = ProductSpecFormatter::formatQuantity($item);  // ← 더 정확한 fallback
                                        }
                                    }
                                    // ✅ 사용자 요구: DB 값 그대로 사용, 계산 절대 금지!
                                    // Phase 3 필드 우선 사용, 없으면 st_price 직접 사용 (역계산 금지)
                                    $supply = !empty($item['price_supply'])
                                        ? intval($item['price_supply'])  // Phase 3 필드 (우선)
                                        : intval($item['st_price'] ?? 0);  // 레거시 필드 (fallback, 역계산 안함!)

                                    $total = !empty($item['price_vat'])
                                        ? intval($item['price_vat'])  // Phase 3 필드 (우선)
                                        : intval($item['st_price_vat'] ?? 0);  // 레거시 필드 (fallback)

                                    // ✅ 부가세: Phase 3 필드 우선, 없으면 계산 (단순 빼기만)
                                    $vat = !empty($item['price_vat_amount'])
                                        ? intval($item['price_vat_amount'])  // Phase 3 필드 (우선)
                                        : ($total - $supply);  // 계산 (fallback)

                                    // 단가 계산: 전단지 0.5연은 '-', 그 외는 공급가 ÷ 수량
                                    $unitPrice = 0;
                                    $unitPriceDisplay = '0';
                                    if (in_array($productType, ['inserted', 'leaflet']) && $qty == 0.5) {
                                        // 전단지 0.5연일 때만 '-' 표시
                                        $unitPriceDisplay = '-';
                                    } else {
                                        // 그 외 모든 경우: 단가 계산
                                        $unitPrice = $qty > 0 ? intval($supply / $qty) : 0;
                                        $unitPriceDisplay = $unitPrice;
                                    }
                                ?>
                                <tr class="item-row" data-source="cart" data-source-id="<?php echo $item['no']; ?>">
                                    <td class="col-no"><?php echo $index + 1; ?></td>
                                    <td class="col-name"><input type="text" name="items[<?php echo $index; ?>][product_name]" value="<?php echo htmlspecialchars($productName); ?>" readonly></td>
                                    <td class="col-spec"><span class="spec-display"><?php echo nl2br(htmlspecialchars($spec)); ?></span><input type="hidden" name="items[<?php echo $index; ?>][specification]" value="<?php echo htmlspecialchars($spec); ?>"></td>
                                    <td class="col-qty">
                                        <span class="qty-display"><?php echo nl2br(htmlspecialchars($qtyDisplay)); ?></span>
                                        <input type="hidden" name="items[<?php echo $index; ?>][quantity]" value="<?php echo $qty; ?>">
                                        <input type="hidden" name="items[<?php echo $index; ?>][unit]" value="<?php echo $unit; ?>">
                                    </td>
                                    <td class="col-price"><input type="text" name="items[<?php echo $index; ?>][unit_price]" value="<?php echo $unitPriceDisplay; ?>" class="price-input"></td>
                                    <td class="col-supply"><input type="number" name="items[<?php echo $index; ?>][supply_price]" value="<?php echo $supply; ?>" class="supply-input" min="0"></td>
                                    <td class="col-vat vat-cell"><?php echo number_format($vat); ?></td>
                                    <td class="col-total total-cell"><?php echo number_format($total); ?></td>
                                    <td class="col-action"><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                                    <input type="hidden" name="items[<?php echo $index; ?>][source_type]" value="cart">
                                    <input type="hidden" name="items[<?php echo $index; ?>][source_id]" value="<?php echo $item['no']; ?>">
                                    <input type="hidden" name="items[<?php echo $index; ?>][product_type]" value="<?php echo htmlspecialchars($productType); ?>">
                                </tr>
                                <?php $index++; endforeach; ?>
                            <?php endif; ?>

                            <?php
                            // 2. quotation_temp 품목 표시 (계산기 모달에서 추가된 품목)
                            if (count($quoteTempItems) > 0):
                                foreach ($quoteTempItems as $item):
                                    $productType = $item['product_type'] ?? '';
                                    $productName = $item['product_name'] ?? ProductSpecFormatter::getProductTypeName($productType);
                                    // formatter로 규격 생성 (라벨 포함 형식)
                                    $spec = $formatter->format($item);

                                    // ✅ Phase 3: quantity_display 필드 우선 사용 (DataAdapter 표준화)
                                    // ✅ 수정: 단위 체크 추가 (cart.php 수정과 동일 패턴)
                                    if (!empty($item['quantity_display']) && preg_match('/[매연부권개장]/u', $item['quantity_display'])) {
                                        // Phase 3 표준 필드 사용 (단위 있는 경우만)
                                        $qtyDisplay = $item['quantity_display'];
                                        $qty = floatval($item['quantity_value'] ?? $item['MY_amount'] ?? 1);
                                    } else {
                                        // Fallback: ProductSpecFormatter 사용 (단위 없거나 빈값일 때)
                                        $qty = ProductSpecFormatter::getQuantity($item);
                                        $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';

                                        if (!empty($item['mesu'])) {
                                            $qtyDisplay = number_format($item['mesu']) . $unit;
                                        } elseif (!empty($item['MY_amount'])) {
                                            $qtyDisplay = htmlspecialchars($item['MY_amount']) . $unit;
                                        } else {
                                            $qtyDisplay = ProductSpecFormatter::formatQuantity($item);  // ← 더 정확한 fallback
                                        }
                                    }
                                    // ✅ 사용자 요구: DB 값 그대로 사용, 계산 절대 금지!
                                    // Phase 3 필드 우선 사용, 없으면 st_price 직접 사용 (역계산 금지)
                                    $supply = !empty($item['price_supply'])
                                        ? intval($item['price_supply'])  // Phase 3 필드 (우선)
                                        : intval($item['st_price'] ?? 0);  // 레거시 필드 (fallback, 역계산 안함!)

                                    $total = !empty($item['price_vat'])
                                        ? intval($item['price_vat'])  // Phase 3 필드 (우선)
                                        : intval($item['st_price_vat'] ?? 0);  // 레거시 필드 (fallback)

                                    // ✅ 부가세: Phase 3 필드 우선, 없으면 계산 (단순 빼기만)
                                    $vat = !empty($item['price_vat_amount'])
                                        ? intval($item['price_vat_amount'])  // Phase 3 필드 (우선)
                                        : ($total - $supply);  // 계산 (fallback)

                                    // 단가 계산: 전단지 0.5연은 '-', 그 외는 공급가 ÷ 수량
                                    $unitPrice = 0;
                                    $unitPriceDisplay = '0';
                                    if (in_array($productType, ['inserted', 'leaflet']) && $qty == 0.5) {
                                        // 전단지 0.5연일 때만 '-' 표시
                                        $unitPriceDisplay = '-';
                                    } else {
                                        // 그 외 모든 경우: 단가 계산
                                        $unitPrice = $qty > 0 ? round($supply / $qty, 1) : 0;
                                        $unitPriceDisplay = $unitPrice;
                                    }
                                ?>
                                <tr class="item-row" data-source="quotation_temp" data-source-id="<?php echo $item['id']; ?>">
                                    <td class="col-no"><?php echo $index + 1; ?></td>
                                    <td class="col-name"><input type="text" name="items[<?php echo $index; ?>][product_name]" value="<?php echo htmlspecialchars($productName); ?>" readonly></td>
                                    <td class="col-spec"><span class="spec-display"><?php echo nl2br(htmlspecialchars($spec)); ?></span><input type="hidden" name="items[<?php echo $index; ?>][specification]" value="<?php echo htmlspecialchars($spec); ?>"></td>
                                    <td class="col-qty">
                                        <span class="qty-display"><?php echo nl2br(htmlspecialchars($qtyDisplay)); ?></span>
                                        <input type="hidden" name="items[<?php echo $index; ?>][quantity]" value="<?php echo $qty; ?>">
                                        <input type="hidden" name="items[<?php echo $index; ?>][unit]" value="<?php echo $unit; ?>">
                                    </td>
                                    <td class="col-price"><input type="text" name="items[<?php echo $index; ?>][unit_price]" value="<?php echo $unitPriceDisplay; ?>" class="price-input"></td>
                                    <td class="col-supply"><input type="number" name="items[<?php echo $index; ?>][supply_price]" value="<?php echo $supply; ?>" class="supply-input" min="0"></td>
                                    <td class="col-vat vat-cell"><?php echo number_format($vat); ?></td>
                                    <td class="col-total total-cell"><?php echo number_format($total); ?></td>
                                    <td class="col-action"><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                                    <input type="hidden" name="items[<?php echo $index; ?>][source_type]" value="quotation_temp">
                                    <input type="hidden" name="items[<?php echo $index; ?>][source_id]" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="items[<?php echo $index; ?>][product_type]" value="<?php echo htmlspecialchars($productType); ?>">
                                </tr>
                                <?php $index++; endforeach; ?>
                            <?php endif; ?>

                            <?php
                            // 3. 기본 빈 행 (장바구니/quotation_temp가 없을 때만 3개 표시)
                            $blankRowStartIndex = count($cartItems) + count($quoteTempItems);
                            $defaultRowCount = ($blankRowStartIndex == 0) ? 3 : 0;  // 품목 없으면 3개, 있으면 0개
                            ?>
                            <!-- 기본 빈 행 (품목이 없을 때만 3개 표시) -->
                            <?php for ($i = 0; $i < $defaultRowCount; $i++): ?>
                            <tr class="item-row">
                                <td class="col-no"><?php echo $blankRowStartIndex + $i + 1; ?></td>
                                <td class="col-name">
                                    <select name="items[<?php echo $blankRowStartIndex + $i; ?>][product_name]" class="product-select" style="display:none;">
                                        <option value="__direct__">직접입력</option>
                                        <option value="전단지">전단지</option>
                                        <option value="명함">명함</option>
                                        <option value="봉투">봉투</option>
                                        <option value="스티커">스티커</option>
                                        <option value="자석스티커">자석스티커</option>
                                        <option value="카다록">카다록</option>
                                        <option value="포스터">포스터</option>
                                        <option value="상품권">상품권</option>
                                        <option value="NCR양식">NCR양식</option>
                                        <option value="배송비">배송비</option>
                                    </select>
                                    <input type="text" name="items[<?php echo $blankRowStartIndex + $i; ?>][product_name_custom]" placeholder="품명을 직접 입력하세요" class="direct-input-field">
                                </td>
                                <td class="col-spec"><input type="text" name="items[<?php echo $blankRowStartIndex + $i; ?>][specification]" placeholder="규격/사양"></td>
                                <td class="col-qty">
                                    <input type="number" step="0.01" name="items[<?php echo $blankRowStartIndex + $i; ?>][quantity]" value="1" class="qty-input" min="0.01">
                                    <input type="hidden" name="items[<?php echo $blankRowStartIndex + $i; ?>][unit]" value="개">
                                </td>
                                <td class="col-price"><input type="number" name="items[<?php echo $blankRowStartIndex + $i; ?>][unit_price]" value="0" class="price-input" min="0"></td>
                                <td class="col-supply"><input type="number" name="items[<?php echo $blankRowStartIndex + $i; ?>][supply_price]" value="0" class="supply-input" min="0"></td>
                                <td class="col-vat vat-cell">0</td>
                                <td class="col-total total-cell">0</td>
                                <td class="col-action"><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                                <input type="hidden" name="items[<?php echo $blankRowStartIndex + $i; ?>][source_type]" value="manual">
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    <div style="padding: 10px;">
                        <button type="button" class="btn" id="addItemBtn">+ 품목 추가</button>
                        <button type="button" class="btn btn-success" id="addManualItemBtn" style="margin-left: 10px; background: #28a745; color: #fff;">+ 수기 품목</button>
                    </div>
                </div>
            </div>

            <!-- 금액 합계 -->
            <div class="section">
                <div class="section-header">금액 합계</div>
                <div class="section-body">
                    <div class="total-section">
                        <div class="total-row">
                            <span>공급가액</span>
                            <span id="supplyTotal"><?php echo number_format($supplyTotal); ?>원</span>
                            <input type="hidden" name="supply_total" value="<?php echo $supplyTotal; ?>">
                        </div>
                        <div class="total-row">
                            <span>부가세 (VAT)</span>
                            <span id="vatTotal"><?php echo number_format($vatTotal); ?>원</span>
                            <input type="hidden" name="vat_total" value="<?php echo $vatTotal; ?>">
                        </div>
                        <div class="total-row">
                            <span>배송비 (공급가액)</span>
                            <span><input type="number" name="delivery_price" value="0" id="deliveryPrice">원</span>
                        </div>
                        <div class="total-row" style="font-size:13px; color:#666;">
                            <span>배송비 부가세</span>
                            <span id="deliveryVat">0원</span>
                        </div>
                        <div class="total-row">
                            <span>할인</span>
                            <span><input type="number" name="discount_amount" value="0" id="discountAmount">원</span>
                        </div>
                        <div class="total-row grand">
                            <span>합계 (VAT 포함)</span>
                            <span id="grandTotal"><?php echo number_format($grandTotal); ?>원</span>
                            <input type="hidden" name="grand_total" value="<?php echo $grandTotal; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 추가 정보 -->
            <div class="section">
                <div class="section-header">추가 정보</div>
                <div class="section-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>결제조건</label>
                            <input type="text" name="payment_terms" value="발행일로부터 7일">
                        </div>
                        <div class="form-group">
                            <label>유효기간 (일)</label>
                            <input type="number" name="valid_days" value="7" min="1" max="90">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>배송방식</label>
                            <select name="delivery_type">
                                <option value="">선택</option>
                                <option value="택배">택배</option>
                                <option value="퀵서비스">퀵서비스</option>
                                <option value="직접수령">직접수령</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>배송지 주소</label>
                            <input type="text" name="delivery_address" placeholder="배송지 주소 입력">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>메모 (내부용)</label>
                        <textarea name="notes" rows="2" placeholder="관리자 메모"></textarea>
                    </div>
                </div>
            </div>

            <!-- 버튼 -->
            <div class="actions">
                <button type="button" class="btn" onclick="history.back()">취소</button>
                <button type="button" class="btn btn-primary" id="saveBtn">저장</button>
                <button type="button" class="btn btn-success" id="saveAndSendBtn">저장 후 이메일 발송</button>
            </div>
        </form>
    </div>

    <!-- 로딩 오버레이 -->
    <div class="modal-overlay" id="loadingOverlay">
        <div class="modal-content">
            <div class="spinner"></div>
            <p id="loadingText">처리 중...</p>
        </div>
    </div>

    <!-- 결과 모달 -->
    <div class="modal-overlay" id="resultModal">
        <div class="modal-content" style="max-width:450px;">
            <h3 id="resultTitle"></h3>
            <div id="resultMessage" style="margin-bottom:15px;"></div>
            <div id="resultActions"></div>
        </div>
    </div>

    <!-- 계산기 모달 시스템 (메인 스크립트 전에 로드) -->
    <script src="includes/calculator_modal.js"></script>

    <script>
    // 초기 품목 인덱스: 장바구니 + quotation_temp + 기본 빈 행
    let itemIndex = <?php echo count($cartItems) + count($quoteTempItems) + $defaultRowCount; ?>;

    // 품목 추가
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const rowCount = tbody.querySelectorAll('tr').length + 1;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td class="col-no">${rowCount}</td>
            <td class="col-name">
                <select name="items[${itemIndex}][product_name]" class="product-select">
                    <option value="" disabled selected>선택해주세요</option>
                    <option value="__direct__">✏️ 직접입력</option>
                    <option value="전단지">전단지</option>
                    <option value="명함">명함</option>
                    <option value="봉투">봉투</option>
                    <option value="스티커">스티커</option>
                    <option value="자석스티커">자석스티커</option>
                    <option value="카다록">카다록</option>
                    <option value="포스터">포스터</option>
                    <option value="상품권">상품권</option>
                    <option value="NCR양식">NCR양식</option>
                    <option value="배송비">배송비</option>
                </select>
                <input type="text" name="items[${itemIndex}][product_name_custom]" placeholder="품명을 직접 입력하세요" class="direct-input-field" style="display:none;">
            </td>
            <td class="col-spec"><input type="text" name="items[${itemIndex}][specification]" placeholder="규격/사양"></td>
            <td class="col-qty">
                <input type="number" step="0.01" name="items[${itemIndex}][quantity]" value="1" class="qty-input" min="0.01">
                <input type="hidden" name="items[${itemIndex}][unit]" value="개">
            </td>
            <td class="col-price"><input type="number" name="items[${itemIndex}][unit_price]" value="0" class="price-input" min="0"></td>
            <td class="col-supply"><input type="number" name="items[${itemIndex}][supply_price]" value="0" class="supply-input" min="0"></td>
            <td class="col-vat vat-cell">0</td>
            <td class="col-total total-cell">0</td>
            <td class="col-action"><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
            <input type="hidden" name="items[${itemIndex}][source_type]" value="manual">
        `;
        tbody.appendChild(tr);
        itemIndex++;

        attachRowEvents(tr);
        renumberRows();
    });

    // 행 이벤트 연결
    function attachRowEvents(row) {
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const supplyInput = row.querySelector('.supply-input');
        const removeBtn = row.querySelector('.remove-row');
        const productSelect = row.querySelector('.product-select');
        const customInput = row.querySelector('input[name*="[product_name_custom]"]');

        // 품명: 기본 직접입력 vs 품목추가 드롭다운 구분
        if (productSelect && customInput) {
            // 초기 상태 확인 (기본 빈 행: 입력 필드 보임, 품목추가: 드롭다운 보임)
            const isDefaultBlankRow = customInput.style.display !== 'none';

            // 기본 빈 행: 바로 입력 가능 (클릭 이벤트 없음 - 일반 견적서처럼)
            // 품목추가 행: 드롭다운이 이미 보이므로 별도 처리 불필요

            // 드롭다운에서 제품 선택 시 (공통)
            productSelect.addEventListener('change', function() {
                const selectedValue = this.value;

                if (selectedValue === '__direct__') {
                    // 직접입력 선택 → 입력 필드로 전환
                    productSelect.style.display = 'none';
                    customInput.style.display = 'block';
                    customInput.value = '';
                    customInput.placeholder = '품명을 직접 입력하세요';
                    customInput.focus();
                } else if (selectedValue !== '') {
                    // 제품 선택됨 → 입력 필드에 제품명 표시
                    productSelect.style.display = 'none';
                    customInput.style.display = 'block';
                    customInput.value = selectedValue;

                    // 자동으로 계산기 모달 열기 (배송비 제외)
                    if (selectedValue !== '배송비' && typeof openCalculatorModal === 'function') {
                        console.log('✅ 제품 선택됨 - 자동으로 계산기 모달 열기:', selectedValue);
                        openCalculatorModal(selectedValue, row);
                    }
                }
            });
        }

        // 수량 또는 단가 변경 시 공급가 자동 계산
        if (qtyInput) {
            qtyInput.addEventListener('input', () => calculateRow(row));
        }
        if (priceInput) {
            priceInput.addEventListener('input', () => calculateRow(row));
        }
        // 공급가 직접 입력 시 단가 역계산
        if (supplyInput) {
            supplyInput.addEventListener('input', () => calculateUnitPrice(row));
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                row.remove();
                renumberRows();
                calculateTotals();
            });
        }
    }

    // 행 계산 (수량 × 단가 → 공급가)
    function calculateRow(row) {
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const supplyInput = row.querySelector('.supply-input');
        const unitInput = row.querySelector('input[name*="[unit]"]');
        
        const qty = parseFloat(qtyInput.value) || 0;  // parseFloat로 소수점 지원
        const unitPrice = parseInt(priceInput.value) || 0;
        const unit = unitInput ? unitInput.value : '';

        // 전단지(연 단위)는 단가가 비어있으므로 공급가를 직접 사용
        let supply = 0;
        if (unit === '연') {
            // 전단지: 공급가를 그대로 사용 (단가 × 수량 계산 안 함)
            supply = parseInt(supplyInput.value) || 0;
        } else {
            // 다른 품목: 수량 × 단가
            supply = Math.round(qty * unitPrice);
            if (supplyInput) {
                supplyInput.value = supply;
            }
        }

        const vat = Math.round(supply * 0.1);
        const total = supply + vat;

        row.querySelector('.vat-cell').textContent = vat.toLocaleString();
        row.querySelector('.total-cell').textContent = total.toLocaleString();

        calculateTotals();
    }

    // 단가 역계산 (공급가 변경 시: 공급가 ÷ 수량 → 단가)
    function calculateUnitPrice(row) {
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const supplyInput = row.querySelector('.supply-input');
        const unitInput = row.querySelector('input[name*="[unit]"]');

        const quantity = parseFloat(qtyInput.value) || 0;
        const supplyPrice = parseFloat(supplyInput.value) || 0;
        const unit = unitInput ? unitInput.value : '';

        // 전단지(연 단위)는 단가 계산 안 함
        if (unit !== '연' && quantity > 0) {
            const unitPrice = Math.floor(supplyPrice / quantity);
            priceInput.value = unitPrice;
        }

        // VAT 및 합계 재계산
        const vat = Math.round(supplyPrice * 0.1);
        const total = supplyPrice + vat;

        row.querySelector('.vat-cell').textContent = vat.toLocaleString();
        row.querySelector('.total-cell').textContent = total.toLocaleString();

        calculateTotals();
    }

    // 전체 합계 계산
    function calculateTotals() {
        let supplyTotal = 0;
        let vatTotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const supplyInput = row.querySelector('.supply-input');
            const supplyValue = supplyInput ? parseInt(supplyInput.value) || 0 : 0;
            const vatText = row.querySelector('.vat-cell').textContent.replace(/,/g, '');
            supplyTotal += supplyValue;
            vatTotal += parseInt(vatText) || 0;
        });

        const deliverySupply = parseInt(document.getElementById('deliveryPrice').value) || 0;
        const deliveryVat = Math.round(deliverySupply * 0.1);
        const deliveryTotal = deliverySupply + deliveryVat;
        const discount = parseInt(document.getElementById('discountAmount').value) || 0;
        const grandTotal = supplyTotal + vatTotal + deliveryTotal - discount;

        document.getElementById('supplyTotal').textContent = supplyTotal.toLocaleString() + '원';
        document.getElementById('vatTotal').textContent = vatTotal.toLocaleString() + '원';
        document.getElementById('deliveryVat').textContent = deliveryVat.toLocaleString() + '원';
        document.getElementById('grandTotal').textContent = grandTotal.toLocaleString() + '원';

        document.querySelector('input[name="supply_total"]').value = supplyTotal;
        document.querySelector('input[name="vat_total"]').value = vatTotal;
        document.querySelector('input[name="grand_total"]').value = grandTotal;
    }

    // 계산기 모달에서 접근할 수 있도록 전역으로 노출
    window.calculateTotals = calculateTotals;

    // 행 번호 재정렬
    function renumberRows() {
        document.querySelectorAll('#itemsBody tr').forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    // 기존 행에 이벤트 연결
    document.querySelectorAll('.item-row').forEach(row => attachRowEvents(row));

    // 배송비, 할인 변경 시 합계 재계산
    document.getElementById('deliveryPrice').addEventListener('input', calculateTotals);
    document.getElementById('discountAmount').addEventListener('input', calculateTotals);

    // 저장
    document.getElementById('saveBtn').addEventListener('click', () => saveQuote(false));
    document.getElementById('saveAndSendBtn').addEventListener('click', () => saveQuote(true));

    function saveQuote(sendEmail) {
        const form = document.getElementById('quoteForm');
        const formData = new FormData(form);

        // 필수 입력 확인
        const customerName = formData.get('customer_name');
        const customerEmail = formData.get('customer_email');

        if (!customerName || !customerName.trim()) {
            alert('고객명을 입력해주세요.');
            return;
        }

        if (!customerEmail || !customerEmail.trim()) {
            alert('고객 이메일을 입력해주세요.');
            return;
        }

        // 품목 확인 - 품목명이 실제로 입력된 행만 카운트
        const items = document.querySelectorAll('.item-row');
        let validItemCount = 0;
        let emptyItemIndices = [];

        items.forEach((row, index) => {
            const isCartItem = row.getAttribute('data-source') === 'cart';

            // Select 또는 직접입력 필드 확인
            const productSelect = row.querySelector('select[name*="[product_name]"]');
            const customInput = row.querySelector('input[name*="[product_name_custom]"]');
            const cartInput = row.querySelector('input[name*="[product_name]"]');

            let hasProductName = false;

            // 장바구니 품목 (input readonly)
            if (isCartItem && cartInput && cartInput.value.trim()) {
                hasProductName = true;
            }
            // Select에서 선택한 경우 (빈 값이 아닌 경우)
            else if (productSelect && productSelect.value) {
                hasProductName = true;
            }
            // 직접입력한 경우
            else if (customInput && customInput.value.trim()) {
                hasProductName = true;
            }

            if (hasProductName) {
                validItemCount++;
            } else if (!isCartItem) {
                // 장바구니 품목이 아닌 경우에만 빈 품목으로 간주
                emptyItemIndices.push(index + 1);
            }
        });

        if (validItemCount === 0) {
            alert('최소 1개 이상의 품목을 추가해주세요.');
            return;
        }

        if (emptyItemIndices.length > 0) {
            const confirm = window.confirm(
                `${emptyItemIndices.join(', ')}번 행의 품목명이 비어있습니다.\n` +
                '빈 품목은 저장되지 않습니다.\n계속하시겠습니까?'
            );
            if (!confirm) {
                return;
            }
        }

        // 이메일 발송 여부
        formData.append('send_email', sendEmail ? '1' : '0');
        formData.append('recipient_email', customerEmail);

        // 디버깅: FormData 내용 출력
        console.log('=== FormData 전송 내용 ===');
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('items[')) {
                console.log(key, '=', value);
            }
        }
        console.log('========================');

        showLoading(sendEmail ? '견적서 저장 및 이메일 발송 중...' : '견적서 저장 중...');

        fetch('api/save.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showResult('성공', `
                    <p>견적번호: <strong>${data.quote_no}</strong></p>
                    ${sendEmail ? '<p>이메일이 발송되었습니다.</p>' : ''}
                    <p style="font-size:11px; word-break:break-all;">공개 링크: <a href="${data.public_url}" target="_blank">${data.public_url}</a></p>
                `, [
                    { text: '견적서 상세', href: 'detail.php?id=' + data.quote_id, class: 'btn' },
                    { text: '고객용 미리보기', href: 'public/view.php?token=' + data.public_token, class: 'btn', target: '_blank' },
                    { text: '목록으로', href: 'index.php', class: 'btn' }
                ]);
            } else {
                alert('오류: ' + (data.message || '저장 실패'));
            }
        })
        .catch(err => {
            hideLoading();
            alert('오류가 발생했습니다: ' + err.message);
        });
    }

    function showLoading(text) {
        document.getElementById('loadingText').textContent = text;
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    function showResult(title, message, actions) {
        document.getElementById('resultTitle').textContent = title;
        document.getElementById('resultMessage').innerHTML = message;

        const actionsDiv = document.getElementById('resultActions');
        actionsDiv.innerHTML = '';
        actions.forEach(action => {
            const btn = document.createElement('a');
            btn.href = action.href;
            btn.className = action.class;
            btn.textContent = action.text;
            btn.style.margin = '5px';
            btn.style.display = 'inline-block';
            if (action.target) {
                btn.target = action.target;
            }
            actionsDiv.appendChild(btn);
        });

        document.getElementById('resultModal').style.display = 'flex';
    }
    </script>

    <!-- 기존 행에도 계산기 이벤트 연결 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 페이지 로드 시 모든 기존 행에 이벤트 연결
        document.querySelectorAll('.item-row').forEach(row => {
            attachRowEvents(row);
        });
        // 초기 합계 계산
        calculateTotals();
    });
    </script>

    <!-- 수기 품목 추가 모달 -->
    <div id="manualItemModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: #fff; border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div style="padding: 16px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 16px;">수기 품목 추가</h3>
                <button type="button" id="closeManualModal" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #999;">&times;</button>
            </div>
            <div style="padding: 20px;">
                <form id="manualItemForm">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">품목명 <span style="color: #e74c3c;">*</span></label>
                        <input type="text" id="manualProductName" placeholder="예: 전단지, 명함, 기타인쇄물" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">규격/사양</label>
                        <input type="text" id="manualSpecification" placeholder="예: A4, 양면, 150g아트" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">수량 <span style="color: #e74c3c;">*</span></label>
                        <div style="display: flex; gap: 10px;">
                            <input type="number" id="manualQuantity" placeholder="1000" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" min="1" value="1" required>
                            <select id="manualUnit" style="width: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="매">매</option>
                                <option value="연">연</option>
                                <option value="부">부</option>
                                <option value="권">권</option>
                                <option value="개">개</option>
                                <option value="장">장</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">공급가액 <span style="color: #e74c3c;">*</span></label>
                        <div style="position: relative;">
                            <input type="number" id="manualPriceSupply" placeholder="50000" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" min="0" required>
                            <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #999;">원</span>
                        </div>
                        <div style="margin-top: 5px; font-size: 12px; color: #666;">
                            부가세: <span id="manualVatPreview">0</span>원 / 합계: <span id="manualTotalPreview">0</span>원
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">비고</label>
                        <textarea id="manualNotes" placeholder="추가 메모사항" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: vertical; min-height: 60px;"></textarea>
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" id="cancelManualItem" style="padding: 10px 20px; background: #f0f0f0; border: none; border-radius: 4px; cursor: pointer;">취소</button>
                        <button type="submit" style="padding: 10px 20px; background: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer;">품목 추가</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 수기 품목 추가 JavaScript -->
    <script>
    (function() {
        const modal = document.getElementById('manualItemModal');
        const form = document.getElementById('manualItemForm');
        const priceInput = document.getElementById('manualPriceSupply');
        const vatPreview = document.getElementById('manualVatPreview');
        const totalPreview = document.getElementById('manualTotalPreview');

        // 모달 열기
        document.getElementById('addManualItemBtn').addEventListener('click', function() {
            modal.style.display = 'flex';
            document.getElementById('manualProductName').focus();
        });

        // 모달 닫기
        function closeModal() {
            modal.style.display = 'none';
            form.reset();
            vatPreview.textContent = '0';
            totalPreview.textContent = '0';
        }

        document.getElementById('closeManualModal').addEventListener('click', closeModal);
        document.getElementById('cancelManualItem').addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });

        // 가격 미리보기
        priceInput.addEventListener('input', function() {
            const supply = parseInt(this.value) || 0;
            const vat = Math.round(supply * 0.1);
            const total = supply + vat;
            vatPreview.textContent = vat.toLocaleString();
            totalPreview.textContent = total.toLocaleString();
        });

        // 폼 제출
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const productName = document.getElementById('manualProductName').value.trim();
            const specification = document.getElementById('manualSpecification').value.trim();
            const quantity = document.getElementById('manualQuantity').value;
            const unit = document.getElementById('manualUnit').value;
            const priceSupply = document.getElementById('manualPriceSupply').value;
            const notes = document.getElementById('manualNotes').value.trim();

            // 수량 표시 생성 (천 단위 구분 + 단위)
            const quantityDisplay = parseInt(quantity).toLocaleString() + unit;

            // API 호출
            const formData = new FormData();
            formData.append('product_name', productName);
            formData.append('specification', specification);
            formData.append('quantity_display', quantityDisplay);
            formData.append('price_supply', priceSupply);
            formData.append('notes', notes);

            fetch('api/add_manual_item.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 테이블에 행 추가 (안전한 DOM 메서드 사용)
                    addManualRowToTableSafe(data.data);
                    closeModal();
                    calculateTotals();
                    alert('수기 품목이 추가되었습니다.');
                } else {
                    alert('오류: ' + (data.message || '품목 추가 실패'));
                }
            })
            .catch(err => {
                console.error('수기 품목 추가 오류:', err);
                alert('서버 오류가 발생했습니다.');
            });
        });

        // 테이블에 수기 품목 행 추가 (안전한 DOM 메서드 사용)
        function addManualRowToTableSafe(item) {
            const tbody = document.getElementById('itemsBody');
            const rowCount = tbody.querySelectorAll('tr').length + 1;

            const supply = parseInt(item.price_supply) || 0;
            const vat = parseInt(item.price_vat_amount) || 0;
            const total = parseInt(item.price_vat) || 0;

            // 행 생성
            const tr = document.createElement('tr');
            tr.className = 'item-row manual-item';
            tr.dataset.sourceId = item.id;
            tr.dataset.sourceType = 'quotation_temp';

            // 번호 셀
            const tdNo = document.createElement('td');
            tdNo.className = 'col-no';
            tdNo.textContent = rowCount;
            tr.appendChild(tdNo);

            // 품목명 셀
            const tdName = document.createElement('td');
            tdName.className = 'col-name';
            const nameInput = document.createElement('input');
            nameInput.type = 'text';
            nameInput.name = 'items[' + itemIndex + '][product_name_custom]';
            nameInput.value = item.product_name || '';
            nameInput.readOnly = true;
            nameInput.style.cssText = 'background: #f9f9f9; cursor: not-allowed;';
            tdName.appendChild(nameInput);
            const manualLabel = document.createElement('span');
            manualLabel.style.cssText = 'font-size: 10px; color: #28a745; display: block;';
            manualLabel.textContent = '✏️ 수기';
            tdName.appendChild(manualLabel);
            tr.appendChild(tdName);

            // 규격 셀
            const tdSpec = document.createElement('td');
            tdSpec.className = 'col-spec';
            const specInput = document.createElement('input');
            specInput.type = 'text';
            specInput.name = 'items[' + itemIndex + '][specification]';
            specInput.value = item.specification || '';
            specInput.readOnly = true;
            specInput.style.cssText = 'background: #f9f9f9; cursor: not-allowed;';
            tdSpec.appendChild(specInput);
            tr.appendChild(tdSpec);

            // 수량 셀
            const tdQty = document.createElement('td');
            tdQty.className = 'col-qty';
            const qtyDisplay = document.createElement('input');
            qtyDisplay.type = 'text';
            qtyDisplay.value = item.quantity_display || '';
            qtyDisplay.readOnly = true;
            qtyDisplay.style.cssText = 'background: #f9f9f9; cursor: not-allowed; width: 80px;';
            tdQty.appendChild(qtyDisplay);
            ['quantity', 'unit', 'quantity_display'].forEach((field, i) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'items[' + itemIndex + '][' + field + ']';
                hidden.value = field === 'quantity_display' ? (item.quantity_display || '') : (i === 0 ? '1' : '개');
                tdQty.appendChild(hidden);
            });
            tr.appendChild(tdQty);

            // 단가 셀
            const tdPrice = document.createElement('td');
            tdPrice.className = 'col-price';
            const priceInput = document.createElement('input');
            priceInput.type = 'number';
            priceInput.name = 'items[' + itemIndex + '][unit_price]';
            priceInput.value = supply;
            priceInput.className = 'price-input';
            priceInput.readOnly = true;
            priceInput.style.cssText = 'background: #f9f9f9;';
            tdPrice.appendChild(priceInput);
            tr.appendChild(tdPrice);

            // 공급가 셀
            const tdSupply = document.createElement('td');
            tdSupply.className = 'col-supply';
            const supplyInput = document.createElement('input');
            supplyInput.type = 'number';
            supplyInput.name = 'items[' + itemIndex + '][supply_price]';
            supplyInput.value = supply;
            supplyInput.className = 'supply-input';
            supplyInput.readOnly = true;
            supplyInput.style.cssText = 'background: #f9f9f9;';
            tdSupply.appendChild(supplyInput);
            tr.appendChild(tdSupply);

            // VAT 셀
            const tdVat = document.createElement('td');
            tdVat.className = 'col-vat vat-cell';
            tdVat.textContent = vat.toLocaleString();
            tr.appendChild(tdVat);

            // 합계 셀
            const tdTotal = document.createElement('td');
            tdTotal.className = 'col-total total-cell';
            tdTotal.textContent = total.toLocaleString();
            tr.appendChild(tdTotal);

            // 삭제 버튼 셀
            const tdAction = document.createElement('td');
            tdAction.className = 'col-action';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm remove-row';
            removeBtn.dataset.tempId = item.id;
            removeBtn.textContent = '×';
            removeBtn.addEventListener('click', function() {
                if (confirm('이 수기 품목을 삭제하시겠습니까?')) {
                    const tempId = this.dataset.tempId;
                    if (tempId) {
                        fetch('api/delete_temp_item.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'table=quotation_temp&id=' + tempId
                        }).catch(console.error);
                    }
                    tr.remove();
                    renumberRows();
                    calculateTotals();
                }
            });
            tdAction.appendChild(removeBtn);
            tr.appendChild(tdAction);

            // hidden 필드들
            ['source_type', 'source_id', 'is_manual_entry'].forEach((field, i) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'items[' + itemIndex + '][' + field + ']';
                hidden.value = field === 'source_type' ? 'quotation_temp' : (field === 'source_id' ? item.id : '1');
                tr.appendChild(hidden);
            });

            tbody.appendChild(tr);
            itemIndex++;
            renumberRows();
        }
    })();
    </script>
</body>
</html>
