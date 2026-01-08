<?php
/**
 * 견적서 수정 페이지 (draft 상태만 수정 가능)
 * - 기존 견적 데이터 로드
 * - UPDATE 방식으로 저장
 */

session_start();

// 캐시 방지
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/includes/QuoteManager.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/ProductSpecFormatter.php';

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

// draft 상태만 수정 가능
if ($quote['status'] !== 'draft') {
    die('이미 발송된 견적서는 수정할 수 없습니다. 개정판을 작성해주세요.');
}

// 품목 조회
$items = $manager->getQuoteItems($quoteId);

$typeLabel = $quote['quote_type'] === 'transaction' ? '거래명세표' : '견적서';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $typeLabel; ?> 수정 - 두손기획인쇄</title>
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
        .alert-info { background: #cff4fc; border-color: #17a2b8; color: #055160; }

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
                <span style="font-weight:bold; font-size:16px;"><?php echo $typeLabel; ?> 수정</span>
            </div>
            <div style="font-size:13px; color:#666;">
                견적번호: <strong><?php echo htmlspecialchars($quote['quote_no']); ?></strong>
            </div>
        </div>

        <!-- 알림 -->
        <div class="alert alert-info">
            💡 이 견적서는 아직 발송되지 않았습니다. 수정 후 저장하면 기존 견적서가 업데이트됩니다.
        </div>

        <!-- 폼 -->
        <form id="quoteForm">
            <input type="hidden" name="quote_id" value="<?php echo $quoteId; ?>">
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
                        <label>배송비</label>
                        <input type="number" name="delivery_price" value="<?php echo $quote['delivery_price']; ?>" min="0">
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
                            <th style="width:200px;">품명 *</th>
                            <th>규격/사양</th>
                            <th style="width:80px;">수량 *</th>
                            <th style="width:70px;">단위</th>
                            <th style="width:130px;">공급가 *</th>
                            <th style="width:50px;">삭제</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php
                        /**
                         * ========================================
                         * 가격 필드 구조 (CRITICAL - 필드명 주의!)
                         * ========================================
                         *
                         * quote_items 테이블:
                         * - supply_price    = 공급가액 (부가세 제외)
                         * - unit_price      = 단가 (레거시, 수동 입력 품목만)
                         *
                         * ⚠️ 주의:
                         * - 품목 계산기에서 온 데이터는 supply_price 사용
                         * - 수동 입력 품목만 quantity * unit_price 계산
                         * - 역계산 금지: supply_price ÷ quantity = unit_price 계산 금지
                         */
                        foreach ($items as $idx => $item):
                            $supplyPrice = intval($item['supply_price'] ?? ($item['quantity'] * $item['unit_price']));
                        ?>
                        <tr class="item-row">
                            <td><?php echo $idx + 1; ?></td>
                            <td><input type="text" name="items[<?php echo $idx; ?>][product_name]" required value="<?php echo htmlspecialchars($item['product_name']); ?>"></td>
                            <td><input type="text" name="items[<?php echo $idx; ?>][specification]" value="<?php echo htmlspecialchars($item['specification']); ?>"></td>
                            <td><input type="number" name="items[<?php echo $idx; ?>][quantity]" class="quantity-input" required min="1" value="<?php echo $item['quantity']; ?>"></td>
                            <td><input type="text" name="items[<?php echo $idx; ?>][unit]" value="<?php echo htmlspecialchars($item['unit']); ?>"></td>
                            <td><input type="number" name="items[<?php echo $idx; ?>][supply_price]" class="supply-price-input" required min="0" value="<?php echo $supplyPrice; ?>"></td>
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
                <button type="button" class="btn btn-primary" onclick="updateQuote(false)">저장</button>
                <button type="button" class="btn btn-success" onclick="updateQuote(true)">저장 및 이메일 발송</button>
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
            <td><input type="number" name="items[${itemCounter}][quantity]" class="quantity-input" required min="1" value="1"></td>
            <td><input type="text" name="items[${itemCounter}][unit]" value="개"></td>
            <td><input type="number" name="items[${itemCounter}][supply_price]" class="supply-price-input" required min="0" value="0"></td>
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

    // 견적서 수정
    function updateQuote(sendEmail) {
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
            const supplyPrice = row.querySelector('input[name*="[supply_price]"]')?.value || '0';

            if (productName.trim()) {
                items.push({
                    product_name: productName,
                    specification: specification,
                    quantity: quantity,
                    unit: unit,
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

        // 품목별 필수 필드 검증
        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            const itemNo = i + 1;

            if (!item.product_name || !item.product_name.trim()) {
                alert(`${itemNo}번째 품목의 품명을 입력해주세요.`);
                return;
            }

            const qty = parseFloat(item.quantity);
            if (isNaN(qty) || qty <= 0) {
                alert(`${itemNo}번째 품목의 수량을 올바르게 입력해주세요.`);
                return;
            }

            const supplyPrice = parseFloat(item.supply_price);
            if (isNaN(supplyPrice) || supplyPrice < 0) {
                alert(`${itemNo}번째 품목의 공급가를 올바르게 입력해주세요.`);
                return;
            }
        }

        // 4. items를 JSON으로 전송
        formData.append('items_json', JSON.stringify(items));
        formData.append('send_email', sendEmail ? '1' : '0');
        formData.append('recipient_email', customerEmail);

        showLoading(sendEmail ? '견적서 수정 및 이메일 발송 중...' : '견적서 수정 중...');

        fetch('api/update.php', {
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
                    <p>견적서가 수정되었습니다.</p>
                `, [
                    { text: '견적서 상세', href: 'detail.php?id=' + data.quote_id, class: 'btn-primary' },
                    { text: '목록으로', href: 'index.php', class: 'btn' }
                ]);
            } else {
                alert('오류: ' + (data.message || '수정 실패'));
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
