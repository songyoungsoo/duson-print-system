<?php
/**
 * 견적서 개정판 작성 페이지 (sent 상태 견적만 가능)
 * - 기존 견적 데이터 로드 (읽기 전용)
 * - 새 버전으로 복사 + 수정
 */

session_start();

// 캐시 방지
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/includes/QuoteManager.php';
require_once __DIR__ . '/includes/ProductSpecFormatter.php';

$manager = new QuoteManager($db);
$formatter = new ProductSpecFormatter($db);
$company = $manager->getCompanySettings();

// 견적서 ID 확인
$quoteId = intval($_GET['id'] ?? 0);
if (!$quoteId) {
    die('견적서 ID가 필요합니다.');
}

// 견적서 조회
$quote = $manager->getQuoteById($quoteId);
if (!$quote) {
    die('견적서를 찾을 수 없습니다.');
}

// sent 상태만 개정판 생성 가능
if ($quote['status'] !== 'sent') {
    die('발송된 견적서만 개정판을 생성할 수 있습니다. draft 견적은 직접 수정해주세요.');
}

// 품목 조회
$items = $manager->getQuoteItems($quoteId);

// 기존 버전 정보
$currentVersion = intval($quote['version'] ?? 1);
$newVersion = $currentVersion + 1;

$typeLabel = $quote['quote_type'] === 'transaction' ? '거래명세표' : '견적서';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $typeLabel; ?> 개정판 작성 - 두손기획인쇄</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans KR', sans-serif; background: #f0f0f0; font-size: 13px; }

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

        /* 알림 */
        .alert {
            padding: 10px 15px;
            margin-bottom: 12px;
            border: 1px solid;
            font-size: 13px;
        }
        .alert-warning { background: #fff3cd; border-color: #ffc107; color: #856404; }

        /* 섹션 */
        .section {
            background: #fff;
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 12px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }

        /* 폼 그룹 */
        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            font-size: 12px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #ccc;
            font-size: 13px;
        }

        /* 테이블 */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background: #f8f8f8;
            border: 1px solid #ccc;
            padding: 8px 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .items-table td {
            border: 1px solid #ccc;
            padding: 6px;
        }
        .items-table input {
            width: 100%;
            padding: 4px;
            border: 1px solid #ddd;
            font-size: 12px;
        }
        .items-table .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 11px;
        }

        /* 버튼 */
        .btn-group {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 15px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-add {
            background: #28a745;
            color: white;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            margin-top: 8px;
        }

        /* 로딩 */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .loading-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
        }

        /* 결과 모달 */
        .result-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }
        .result-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
        }
        .result-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .result-actions {
            margin-top: 20px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 헤더 -->
        <div class="header">
            <div>
                <a href="detail.php?id=<?php echo $quoteId; ?>" class="back-link">← 돌아가기</a>
                <span style="font-weight:bold; font-size:16px;"><?php echo $typeLabel; ?> 개정판 작성</span>
            </div>
            <div style="font-size:13px; color:#666;">
                원본: <strong><?php echo htmlspecialchars($quote['quote_no']); ?></strong> (v<?php echo $currentVersion; ?>)
            </div>
        </div>

        <!-- 알림 -->
        <div class="alert alert-warning">
            📝 원본 견적서를 기반으로 <strong>버전 <?php echo $newVersion; ?></strong> 개정판을 생성합니다.<br>
            원본 견적서는 그대로 유지되며, 새 견적번호가 발급됩니다.
        </div>

        <!-- 폼 -->
        <form id="quoteForm">
            <input type="hidden" name="original_quote_id" value="<?php echo $quoteId; ?>">
            <input type="hidden" name="quote_type" value="<?php echo htmlspecialchars($quote['quote_type']); ?>">

            <!-- 고객 정보 -->
            <div class="section">
                <div class="section-title">고객 정보</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>고객명/담당자명 *</label>
                        <input type="text" name="customer_name" required value="<?php echo htmlspecialchars($quote['customer_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label>회사명</label>
                        <input type="text" name="customer_company" value="<?php echo htmlspecialchars($quote['customer_company']); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>연락처</label>
                        <input type="tel" name="customer_phone" value="<?php echo htmlspecialchars($quote['customer_phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label>이메일 *</label>
                        <input type="email" name="customer_email" required value="<?php echo htmlspecialchars($quote['customer_email']); ?>">
                    </div>
                </div>
            </div>

            <!-- 배송 정보 -->
            <div class="section">
                <div class="section-title">배송 정보</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>배송방식</label>
                        <select name="delivery_type">
                            <option value="">선택</option>
                            <option value="택배" <?php echo $quote['delivery_type'] === '택배' ? 'selected' : ''; ?>>택배</option>
                            <option value="퀵서비스" <?php echo $quote['delivery_type'] === '퀵서비스' ? 'selected' : ''; ?>>퀵서비스</option>
                            <option value="직접수령" <?php echo $quote['delivery_type'] === '직접수령' ? 'selected' : ''; ?>>직접수령</option>
                            <option value="무료배송" <?php echo $quote['delivery_type'] === '무료배송' ? 'selected' : ''; ?>>무료배송</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>배송비 (공급가)</label>
                        <input type="number" name="delivery_price" id="deliveryPrice" value="<?php echo $quote['delivery_price']; ?>" min="0" onchange="calculateDeliveryVat()">
                    </div>
                    <div class="form-group">
                        <label>배송비 VAT</label>
                        <span id="deliveryVatDisplay"><?php echo number_format($quote['delivery_vat'] ?? round($quote['delivery_price'] * 0.1)); ?>원</span>
                        <input type="hidden" name="delivery_vat" id="deliveryVat" value="<?php echo $quote['delivery_vat'] ?? round($quote['delivery_price'] * 0.1); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>배송지 주소</label>
                    <textarea name="delivery_address" rows="2"><?php echo htmlspecialchars($quote['delivery_address']); ?></textarea>
                </div>
            </div>

            <!-- 품목 -->
            <div class="section">
                <div class="section-title">품목</div>
                <table class="items-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width:30px;">No</th>
                            <th style="width:180px;">품명 *</th>
                            <th>규격/사양</th>
                            <th style="width:70px;">수량 *</th>
                            <th style="width:60px;">단위</th>
                            <th style="width:100px;">단가 *</th>
                            <th style="width:120px;">공급가 *</th>
                            <th style="width:50px;">삭제</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php foreach ($items as $idx => $item):
                            $supplyPrice = intval($item['quantity'] * $item['unit_price']);
                        ?>
                        <tr class="item-row">
                            <td><?php echo $idx + 1; ?></td>
                            <td><input type="text" name="items[<?php echo $idx; ?>][product_name]" required value="<?php echo htmlspecialchars($item['product_name']); ?>"></td>
                            <td><input type="text" name="items[<?php echo $idx; ?>][specification]" value="<?php echo htmlspecialchars($item['specification']); ?>"></td>
                            <td><input type="number" name="items[<?php echo $idx; ?>][quantity]" class="quantity-input" required min="1" value="<?php echo $item['quantity']; ?>" onchange="calculateSupplyPrice(this)"></td>
                            <td><input type="text" name="items[<?php echo $idx; ?>][unit]" value="<?php echo htmlspecialchars($item['unit']); ?>"></td>
                            <td><input type="number" name="items[<?php echo $idx; ?>][unit_price]" class="unit-price-input" required min="0" step="0.01" value="<?php echo $item['unit_price']; ?>" onchange="calculateSupplyPrice(this)"></td>
                            <td><input type="number" name="items[<?php echo $idx; ?>][supply_price]" class="supply-price-input" required min="0" value="<?php echo $supplyPrice; ?>" onchange="calculateUnitPrice(this)"></td>
                            <td><button type="button" class="btn-remove" onclick="removeRow(this)">삭제</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" class="btn-add" onclick="addItemRow()">+ 품목 추가</button>
            </div>

            <!-- 금액 및 조건 -->
            <div class="section">
                <div class="section-title">금액 및 조건</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>할인금액</label>
                        <input type="number" name="discount_amount" value="<?php echo $quote['discount_amount']; ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label>할인사유</label>
                        <input type="text" name="discount_reason" value="<?php echo htmlspecialchars($quote['discount_reason']); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>결제조건</label>
                        <input type="text" name="payment_terms" value="<?php echo htmlspecialchars($quote['payment_terms']); ?>">
                    </div>
                    <div class="form-group">
                        <label>유효기간(일)</label>
                        <input type="number" name="valid_days" value="<?php echo $quote['valid_days']; ?>" min="1">
                    </div>
                </div>
                <div class="form-group">
                    <label>비고</label>
                    <textarea name="notes" rows="3"><?php echo htmlspecialchars($quote['notes']); ?></textarea>
                </div>
            </div>

            <!-- 버튼 -->
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="location.href='detail.php?id=<?php echo $quoteId; ?>'">취소</button>
                <button type="button" class="btn btn-primary" onclick="createRevision(false)">개정판 저장</button>
                <button type="button" class="btn btn-success" onclick="createRevision(true)">개정판 저장 및 이메일 발송</button>
            </div>
        </form>
    </div>

    <!-- 로딩 -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div style="font-size:18px; font-weight:bold; margin-bottom:10px;">처리 중...</div>
            <div id="loadingMessage">잠시만 기다려주세요.</div>
        </div>
    </div>

    <!-- 결과 모달 -->
    <div class="result-modal" id="resultModal">
        <div class="result-content">
            <div class="result-title" id="resultTitle">결과</div>
            <div id="resultMessage"></div>
            <div class="result-actions" id="resultActions"></div>
        </div>
    </div>

    <script>
    let itemCounter = <?php echo count($items); ?>;

    // 품목 추가
    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td>${itemCounter + 1}</td>
            <td><input type="text" name="items[${itemCounter}][product_name]" required></td>
            <td><input type="text" name="items[${itemCounter}][specification]"></td>
            <td><input type="number" name="items[${itemCounter}][quantity]" class="quantity-input" required min="1" value="1" onchange="calculateSupplyPrice(this)"></td>
            <td><input type="text" name="items[${itemCounter}][unit]" value="개"></td>
            <td><input type="number" name="items[${itemCounter}][unit_price]" class="unit-price-input" required min="0" step="0.01" value="0" onchange="calculateSupplyPrice(this)"></td>
            <td><input type="number" name="items[${itemCounter}][supply_price]" class="supply-price-input" required min="0" value="0" onchange="calculateUnitPrice(this)"></td>
            <td><button type="button" class="btn-remove" onclick="removeRow(this)">삭제</button></td>
        `;
        tbody.appendChild(row);
        itemCounter++;
        updateRowNumbers();
    }

    // 품목 삭제
    function removeRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        updateRowNumbers();
    }

    // 번호 재정렬
    function updateRowNumbers() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row, idx) => {
            row.querySelector('td:first-child').textContent = idx + 1;
        });
    }

    // 배송비 VAT 자동 계산
    function calculateDeliveryVat() {
        const deliveryPrice = parseInt(document.getElementById('deliveryPrice').value) || 0;
        const deliveryVat = Math.round(deliveryPrice * 0.1);
        document.getElementById('deliveryVat').value = deliveryVat;
        document.getElementById('deliveryVatDisplay').textContent = deliveryVat.toLocaleString() + '원';
    }

    // 공급가 자동 계산 (수량 또는 단가 변경 시)
    function calculateSupplyPrice(element) {
        const row = element.closest('tr');
        const supplyPriceInput = row.querySelector('.supply-price-input');

        // 공급가가 수동 수정되었으면 자동 계산 건너뜀
        if (supplyPriceInput.dataset.manualEdit === 'true') {
            return;
        }

        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');

        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const supplyPrice = Math.floor(quantity * unitPrice);

        supplyPriceInput.value = supplyPrice;
    }

    // 단가 역계산 (공급가 변경 시)
    function calculateUnitPrice(element) {
        const row = element.closest('tr');
        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const supplyPriceInput = row.querySelector('.supply-price-input');

        // 공급가가 수동으로 수정되었음을 표시
        supplyPriceInput.dataset.manualEdit = 'true';

        const quantity = parseFloat(quantityInput.value) || 0;
        const supplyPrice = parseFloat(supplyPriceInput.value) || 0;

        if (quantity > 0) {
            // 소수점 2자리까지 반올림
            const unitPrice = Math.round((supplyPrice / quantity) * 100) / 100;
            // 정수면 정수로, 소수면 불필요한 0 제거하여 표시
            unitPriceInput.value = unitPrice % 1 === 0 ? unitPrice : parseFloat(unitPrice.toFixed(2));
        }
    }

    // 로딩 표시
    function showLoading(message) {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    // 결과 표시
    function showResult(title, message, actions) {
        document.getElementById('resultTitle').textContent = title;
        document.getElementById('resultMessage').innerHTML = message;

        const actionsDiv = document.getElementById('resultActions');
        actionsDiv.innerHTML = '';
        actions.forEach(action => {
            const btn = document.createElement('a');
            btn.href = action.href || '#';
            btn.className = 'btn ' + (action.class || 'btn-primary');
            btn.textContent = action.text;
            if (action.target) btn.target = action.target;
            actionsDiv.appendChild(btn);
        });

        document.getElementById('resultModal').style.display = 'flex';
    }

    // 개정판 생성
    function createRevision(sendEmail) {
        const form = document.getElementById('quoteForm');
        const formData = new FormData();

        // 1. 기본 필드 추가 (items 제외)
        Array.from(form.elements).forEach(element => {
            if (element.name && !element.name.startsWith('items[')) {
                formData.append(element.name, element.value);
            }
        });

        // 2. 품목 데이터 재구성
        const itemRows = document.querySelectorAll('.item-row');
        const items = [];

        itemRows.forEach((row) => {
            const productName = row.querySelector('input[name*="[product_name]"]')?.value || '';
            const specification = row.querySelector('input[name*="[specification]"]')?.value || '';
            const quantity = row.querySelector('input[name*="[quantity]"]')?.value || '1';
            const unit = row.querySelector('input[name*="[unit]"]')?.value || '개';
            const unitPrice = row.querySelector('input[name*="[unit_price]"]')?.value || '0';
            const supplyPrice = row.querySelector('input[name*="[supply_price]"]')?.value || '0';

            if (productName.trim()) {
                items.push({
                    product_name: productName,
                    specification: specification,
                    quantity: quantity,
                    unit: unit,
                    unit_price: unitPrice,
                    supply_price: supplyPrice,
                    product_type: '',
                    source_type: 'manual'
                });
            }
        });

        // 3. 검증
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

        if (items.length === 0) {
            alert('최소 1개 이상의 품목을 추가해주세요.');
            return;
        }

        // 4. items를 JSON으로 전송
        formData.append('items_json', JSON.stringify(items));
        formData.append('send_email', sendEmail ? '1' : '0');
        formData.append('recipient_email', customerEmail);

        showLoading(sendEmail ? '개정판 생성 및 이메일 발송 중...' : '개정판 생성 중...');

        fetch('api/create_revision.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showResult('성공', `
                    <p>새 견적번호: <strong>${data.quote_no}</strong></p>
                    <p>${data.message}</p>
                    ${sendEmail ? '<p>이메일이 발송되었습니다.</p>' : ''}
                    <p style="font-size:11px; word-break:break-all;">공개 링크: <a href="${data.public_url}" target="_blank">${data.public_url}</a></p>
                `, [
                    { text: '새 견적서 보기', href: 'detail.php?id=' + data.quote_id, class: 'btn-primary' },
                    { text: '원본 견적서', href: 'detail.php?id=' + <?php echo $quoteId; ?>, class: 'btn' },
                    { text: '목록으로', href: 'index.php', class: 'btn' }
                ]);
            } else {
                alert('오류: ' + (data.message || '개정판 생성 실패'));
            }
        })
        .catch(err => {
            hideLoading();
            alert('오류가 발생했습니다: ' + err.message);
        });
    }
    </script>
</body>
</html>
