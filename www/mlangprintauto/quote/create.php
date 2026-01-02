<?php
/**
 * 견적서 작성 페이지 (엑셀 스타일)
 * - 장바구니 연동 (?from=cart)
 * - 빈 견적서 수동 입력 (기본)
 */

session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/includes/QuoteManager.php';

$manager = new QuoteManager($db);
$formatter = new ProductSpecFormatter($db);
$company = $manager->getCompanySettings();

// 장바구니에서 온 경우
$fromCart = ($_GET['from'] ?? '') === 'cart';
$cartItems = [];

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
}

// 금액 계산
$supplyTotal = 0;
$vatTotal = 0;
foreach ($cartItems as $item) {
    $supply = ProductSpecFormatter::getSupplyPrice($item);
    $total = ProductSpecFormatter::getPrice($item);
    $supplyTotal += $supply;
    $vatTotal += ($total - $supply);
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', '맑은 고딕', sans-serif; background: #f0f0f0; font-size: 13px; }

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
        .form-group { flex: 1; }
        .form-group label {
            display: block;
            font-size: 12px;
            color: #555;
            margin-bottom: 4px;
        }
        .form-group.required label::after { content: ' *'; color: #dc3545; }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #ccc;
            font-size: 13px;
            background: #fff;
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

        .excel-table td input {
            width: 100%;
            border: none;
            padding: 4px;
            font-size: 13px;
            background: transparent;
        }
        .excel-table td input:focus {
            outline: 1px solid #217346;
            background: #fff;
        }
        .excel-table td input[type="number"] { text-align: right; }
        .excel-table td input[readonly] { color: #555; background: #fafafa; }

        .col-no { width: 35px; text-align: center; }
        .col-name { width: 100px; }
        .col-spec { }
        .col-qty { width: 65px; }
        .col-unit { width: 50px; }
        .col-price { width: 85px; }
        .col-supply { width: 90px; text-align: right; font-family: 'Consolas', monospace; }
        .col-vat { width: 75px; text-align: right; font-family: 'Consolas', monospace; }
        .col-total { width: 95px; text-align: right; font-family: 'Consolas', monospace; }
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

        <form id="quoteForm">
            <input type="hidden" name="quote_type" value="<?php echo $quoteType; ?>">
            <input type="hidden" name="from_cart" value="<?php echo $fromCart ? '1' : '0'; ?>">

            <!-- 고객 정보 -->
            <div class="section">
                <div class="section-header">고객 정보</div>
                <div class="section-body">
                    <div class="form-row">
                        <div class="form-group required">
                            <label>고객명 (담당자)</label>
                            <input type="text" name="customer_name" required placeholder="홍길동">
                        </div>
                        <div class="form-group">
                            <label>회사명</label>
                            <input type="text" name="customer_company" placeholder="(주)회사명">
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
                                <th class="col-unit">단위</th>
                                <th class="col-price">단가</th>
                                <th class="col-supply">공급가액</th>
                                <th class="col-vat">VAT</th>
                                <th class="col-total">합계</th>
                                <th class="col-action"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php if ($fromCart && count($cartItems) > 0): ?>
                                <?php $no = 1; foreach ($cartItems as $item):
                                    $productType = $item['product_type'] ?? '';
                                    if (empty($productType) && !empty($item['jong'])) $productType = 'sticker';
                                    $productName = ProductSpecFormatter::getProductTypeName($productType);
                                    $spec = $formatter->format($item);
                                    $qty = ProductSpecFormatter::getQuantity($item);
                                    $unit = ProductSpecFormatter::getUnit($item);
                                    $supply = ProductSpecFormatter::getSupplyPrice($item);
                                    $total = ProductSpecFormatter::getPrice($item);
                                    $vat = $total - $supply;
                                    $unitPrice = $qty > 0 ? intval($supply / $qty) : 0;
                                ?>
                                <tr class="item-row" data-source="cart" data-source-id="<?php echo $item['no']; ?>">
                                    <td class="col-no"><?php echo $no++; ?></td>
                                    <td class="col-name"><input type="text" name="items[<?php echo $no-1; ?>][product_name]" value="<?php echo htmlspecialchars($productName); ?>" readonly></td>
                                    <td class="col-spec"><input type="text" name="items[<?php echo $no-1; ?>][specification]" value="<?php echo htmlspecialchars($spec); ?>" readonly></td>
                                    <td class="col-qty"><input type="number" name="items[<?php echo $no-1; ?>][quantity]" value="<?php echo $qty; ?>" class="qty-input" readonly></td>
                                    <td class="col-unit"><input type="text" name="items[<?php echo $no-1; ?>][unit]" value="<?php echo $unit; ?>" readonly></td>
                                    <td class="col-price"><input type="number" name="items[<?php echo $no-1; ?>][unit_price]" value="<?php echo $unitPrice; ?>" class="price-input" readonly></td>
                                    <td class="col-supply supply-cell"><?php echo number_format($supply); ?></td>
                                    <td class="col-vat vat-cell"><?php echo number_format($vat); ?></td>
                                    <td class="col-total total-cell"><?php echo number_format($total); ?></td>
                                    <td class="col-action"><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                                    <input type="hidden" name="items[<?php echo $no-1; ?>][source_type]" value="cart">
                                    <input type="hidden" name="items[<?php echo $no-1; ?>][source_id]" value="<?php echo $item['no']; ?>">
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div style="padding: 10px;">
                        <button type="button" class="btn" id="addItemBtn">+ 품목 추가</button>
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
                            <span>배송비</span>
                            <span><input type="number" name="delivery_price" value="0" id="deliveryPrice">원</span>
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

    <script>
    let itemIndex = <?php echo $fromCart ? count($cartItems) : 0; ?>;

    // 품목 추가
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const rowCount = tbody.querySelectorAll('tr').length + 1;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td class="col-no">${rowCount}</td>
            <td class="col-name"><input type="text" name="items[${itemIndex}][product_name]" placeholder="품명"></td>
            <td class="col-spec"><input type="text" name="items[${itemIndex}][specification]" placeholder="규격/사양"></td>
            <td class="col-qty"><input type="number" name="items[${itemIndex}][quantity]" value="1" class="qty-input" min="1"></td>
            <td class="col-unit"><input type="text" name="items[${itemIndex}][unit]" value="개"></td>
            <td class="col-price"><input type="number" name="items[${itemIndex}][unit_price]" value="0" class="price-input" min="0"></td>
            <td class="col-supply supply-cell">0</td>
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
        const removeBtn = row.querySelector('.remove-row');

        if (qtyInput && !qtyInput.readOnly) {
            qtyInput.addEventListener('input', () => calculateRow(row));
        }
        if (priceInput && !priceInput.readOnly) {
            priceInput.addEventListener('input', () => calculateRow(row));
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                row.remove();
                renumberRows();
                calculateTotals();
            });
        }
    }

    // 행 계산
    function calculateRow(row) {
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        const unitPrice = parseInt(row.querySelector('.price-input').value) || 0;
        const supply = qty * unitPrice;
        const vat = Math.round(supply * 0.1);
        const total = supply + vat;

        row.querySelector('.supply-cell').textContent = supply.toLocaleString();
        row.querySelector('.vat-cell').textContent = vat.toLocaleString();
        row.querySelector('.total-cell').textContent = total.toLocaleString();

        calculateTotals();
    }

    // 전체 합계 계산
    function calculateTotals() {
        let supplyTotal = 0;
        let vatTotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const supplyText = row.querySelector('.supply-cell').textContent.replace(/,/g, '');
            const vatText = row.querySelector('.vat-cell').textContent.replace(/,/g, '');
            supplyTotal += parseInt(supplyText) || 0;
            vatTotal += parseInt(vatText) || 0;
        });

        const delivery = parseInt(document.getElementById('deliveryPrice').value) || 0;
        const discount = parseInt(document.getElementById('discountAmount').value) || 0;
        const grandTotal = supplyTotal + vatTotal + delivery - discount;

        document.getElementById('supplyTotal').textContent = supplyTotal.toLocaleString() + '원';
        document.getElementById('vatTotal').textContent = vatTotal.toLocaleString() + '원';
        document.getElementById('grandTotal').textContent = grandTotal.toLocaleString() + '원';

        document.querySelector('input[name="supply_total"]').value = supplyTotal;
        document.querySelector('input[name="vat_total"]').value = vatTotal;
        document.querySelector('input[name="grand_total"]').value = grandTotal;
    }

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

        // 품목 확인
        const items = document.querySelectorAll('.item-row');
        if (items.length === 0) {
            alert('최소 1개 이상의 품목을 추가해주세요.');
            return;
        }

        // 이메일 발송 여부
        formData.append('send_email', sendEmail ? '1' : '0');
        formData.append('recipient_email', customerEmail);

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
                    <p>공개 링크: <a href="${data.public_url}" target="_blank">${data.public_url}</a></p>
                `, [
                    { text: '견적서 상세', href: 'detail.php?id=' + data.quote_id, class: 'btn-primary' },
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
</body>
</html>
