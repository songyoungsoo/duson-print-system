<?php
declare(strict_types=1);

// 전단지 주문 시스템 - 완전한 버전
include "../../db.php";

// CSV 데이터를 기반으로 선택 옵션 정의
$flyer_options = [
    'style' => [
        '802' => 'B형 전단지 (780×1080 원판)',
        '625' => 'A형 전단지 (630×930 원판)'
    ],
    'section' => [
        '818' => '8절 (250×353mm)',
        '820' => '4절 (353×500mm)',
        '821' => 'A4 (210×297mm)',
        '822' => '32절 (148×210mm)',
        '823' => '국2절 (297×420mm)',
        '824' => '16절 (176×250mm)',
        '826' => '정사각형 (200×200mm)',
        '628' => '기타규격',
        '629' => '기타규격',
        '630' => '기타규격',
        '631' => '기타규격',
        '632' => '기타규격',
        '944' => '소형 (100×150mm)'
    ],
    'quantity' => [
        '0.5' => '250매 (0.5연분)',
        '1' => '500매 (1연 원본)',
        '2' => '1,000매 (1연을 1회재단)',
        '3' => '2,000매 (1연을 2회재단)',
        '4' => '4,000매 (1연을 3회재단)',
        '5' => '8,000매 (1연을 4회재단)',
        '6' => '16,000매 (1연을 5회재단)',
        '7' => '32,000매 (1연을 6회재단)',
        '8' => '64,000매 (1연을 7회재단)',
        '9' => '128,000매 (1연을 8회재단)',
        '10' => '256,000매 (1연을 9회재단)',
        '20' => '524,288,000매 (1연을 19회재단)',
        '4000' => '대량주문 (특별계산)'
    ],
    'treeselect' => [
        '626' => '일반지 (80g)',
        '714' => '고급지 (120g)',
        '715' => '아트지 (150g)',
        '716' => '무광코팅지 (180g)',
        '717' => '유광코팅지 (200g)',
        '806' => '재생지 (80g)',
        '807' => '크라프트지 (120g)',
        '808' => '색상지 (80g)',
        '809' => '펄지 (150g)',
        '924' => '특수지 (200g)',
        '943' => '투명지 (120g)',
        '773' => '반투명지 (100g)'
    ],
    'po_type' => [
        '1' => '단면 인쇄',
        '2' => '양면 인쇄'
    ]
];

// 가격 데이터 로드 함수
function loadPriceData($csv_file) {
    $price_data = [];
    if (file_exists($csv_file) && ($handle = fopen($csv_file, "r")) !== FALSE) {
        $header = fgetcsv($handle); // 헤더 스킵
        while (($data = fgetcsv($handle)) !== FALSE) {
            $key = $data[1] . '_' . $data[2] . '_' . $data[3] . '_' . $data[5] . '_' . $data[7]; // style_section_quantity_treeselect_potype
            $price_data[$key] = [
                'money' => (int)str_replace(['"', '.'], '', $data[4]),
                'design_money' => (int)str_replace(['"', '.'], '', $data[6]),
                'quantity_two' => (int)str_replace(['"', '.'], '', $data[8])
            ];
        }
        fclose($handle);
    }
    return $price_data;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>전단지 주문 시스템 | 두손기획인쇄</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', Arial, sans-serif;
            background-color: #f7fafc;
            color: #2d3748;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .order-layout {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748;
            border-bottom: 3px solid #4299e1;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2d3748;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background-color: white;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .price-section {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 8px 0;
            font-size: 1rem;
        }

        .price-item.total {
            border-top: 2px solid rgba(255,255,255,0.3);
            padding-top: 15px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 1.4rem;
        }

        .order-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .order-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
        }

        .order-button:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
            border-left: 4px solid #e53e3e;
        }

        .success-message {
            background: #c6f6d5;
            color: #22543d;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
            border-left: 4px solid #38a169;
        }

        .preview-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            grid-column: span 3;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .preview-item {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #4299e1;
        }

        .preview-label {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 5px;
        }

        .preview-value {
            font-weight: 600;
            color: #2d3748;
            font-size: 1.1rem;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .required {
            color: #e53e3e;
        }

        @media (max-width: 1200px) {
            .order-layout {
                grid-template-columns: 1fr 1fr;
            }
            .preview-section {
                grid-column: span 2;
            }
        }

        @media (max-width: 768px) {
            .order-layout {
                grid-template-columns: 1fr;
            }
            .preview-section {
                grid-column: span 1;
            }
            .container {
                padding: 10px;
            }
            .header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 전단지 주문 시스템</h1>
            <p>두손기획인쇄 - 전문적이고 신속한 전단지 제작 서비스</p>
        </div>

        <div class="order-layout">
            <!-- 주문 옵션 선택 -->
            <div class="form-section">
                <h3 class="section-title">📋 주문 옵션</h3>

                <div class="form-group">
                    <label for="style">전단지 타입 <span class="required">*</span></label>
                    <select id="style" name="style" required>
                        <option value="">타입을 선택하세요</option>
                        <?php foreach ($flyer_options['style'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="section">용지 크기 <span class="required">*</span></label>
                    <select id="section" name="section" required>
                        <option value="">크기를 선택하세요</option>
                        <?php foreach ($flyer_options['section'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">주문 수량 <span class="required">*</span></label>
                    <select id="quantity" name="quantity" required>
                        <option value="">수량을 선택하세요</option>
                        <?php foreach ($flyer_options['quantity'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="treeselect">용지 종류 <span class="required">*</span></label>
                    <select id="treeselect" name="treeselect" required>
                        <option value="">용지를 선택하세요</option>
                        <?php foreach ($flyer_options['treeselect'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="po_type">인쇄 방식 <span class="required">*</span></label>
                    <select id="po_type" name="po_type" required>
                        <option value="">인쇄 방식을 선택하세요</option>
                        <?php foreach ($flyer_options['po_type'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- 고객 정보 -->
            <div class="form-section">
                <h3 class="section-title">👤 고객 정보</h3>

                <div class="form-group">
                    <label for="customer_name">성함 <span class="required">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" placeholder="성함을 입력하세요" required>
                </div>

                <div class="form-group">
                    <label for="customer_phone">연락처 <span class="required">*</span></label>
                    <input type="tel" id="customer_phone" name="customer_phone" placeholder="010-0000-0000" required>
                </div>

                <div class="form-group">
                    <label for="customer_email">이메일</label>
                    <input type="email" id="customer_email" name="customer_email" placeholder="example@email.com">
                </div>

                <div class="form-group">
                    <label for="order_memo">주문 메모</label>
                    <textarea id="order_memo" name="order_memo" rows="4" placeholder="특별한 요청사항이나 메모를 입력하세요"></textarea>
                </div>
            </div>

            <!-- 가격 계산 -->
            <div class="form-section">
                <div class="price-section">
                    <h3 class="section-title" style="color: white; border-color: rgba(255,255,255,0.3);">💰 가격 계산</h3>
                    <div class="price-item">
                        <span>인쇄비:</span>
                        <span id="printing-cost">0원</span>
                    </div>
                    <div class="price-item">
                        <span>디자인비:</span>
                        <span id="design-cost">0원</span>
                    </div>
                    <div class="price-item total">
                        <span>총 금액:</span>
                        <span id="total-cost">0원</span>
                    </div>
                </div>

                <div class="error-message" id="error-message"></div>
                <div class="success-message" id="success-message"></div>

                <button type="button" class="order-button" id="order-btn">
                    <span id="btn-text">주문하기</span>
                </button>
            </div>
        </div>

        <!-- 주문 미리보기 -->
        <div class="preview-section">
            <h3 class="section-title">📝 주문 미리보기</h3>
            <div id="order-preview">
                <p style="text-align: center; color: #718096; padding: 40px;">옵션을 선택하시면 주문 내역이 표시됩니다.</p>
            </div>
        </div>
    </div>

    <script>
        // 가격 데이터 (PHP에서 JSON으로 전달)
        const priceData = <?= json_encode(loadPriceData('f:\데이터엑셀화\mlangprintauto_inserted.csv')) ?>;
        const optionLabels = <?= json_encode($flyer_options) ?>;

        // 폼 요소들
        const selects = document.querySelectorAll('select');
        const printingCostEl = document.getElementById('printing-cost');
        const designCostEl = document.getElementById('design-cost');
        const totalCostEl = document.getElementById('total-cost');
        const errorMessageEl = document.getElementById('error-message');
        const successMessageEl = document.getElementById('success-message');
        const previewEl = document.getElementById('order-preview');
        const orderBtn = document.getElementById('order-btn');
        const btnText = document.getElementById('btn-text');

        let currentTotalCost = 0;

        // 가격 계산 함수
        function calculatePrice() {
            const style = document.getElementById('style').value;
            const section = document.getElementById('section').value;
            const quantity = document.getElementById('quantity').value;
            const treeselect = document.getElementById('treeselect').value;
            const poType = document.getElementById('po_type').value;

            // 모든 필드가 선택되었는지 확인
            if (!style || !section || !quantity || !treeselect || !poType) {
                resetPrice();
                updatePreview();
                return;
            }

            const key = `${style}_${section}_${quantity}_${treeselect}_${poType}`;

            if (priceData[key]) {
                const data = priceData[key];
                const printingCost = data.money;
                const designCost = data.design_money;
                const totalCost = printingCost + designCost;

                printingCostEl.textContent = printingCost.toLocaleString() + '원';
                designCostEl.textContent = designCost.toLocaleString() + '원';
                totalCostEl.textContent = totalCost.toLocaleString() + '원';

                currentTotalCost = totalCost;

                errorMessageEl.style.display = 'none';
                updatePreview(style, section, quantity, treeselect, poType, totalCost);
            } else {
                resetPrice();
                errorMessageEl.textContent = '선택하신 옵션 조합의 가격을 찾을 수 없습니다. 다른 옵션을 선택해주세요.';
                errorMessageEl.style.display = 'block';
                updatePreview();
            }
        }

        // 가격 초기화
        function resetPrice() {
            printingCostEl.textContent = '0원';
            designCostEl.textContent = '0원';
            totalCostEl.textContent = '0원';
            currentTotalCost = 0;
        }

        // 미리보기 업데이트
        function updatePreview(style, section, quantity, treeselect, poType, totalCost) {
            if (!style || !section || !quantity || !treeselect || !poType) {
                previewEl.innerHTML = '<p style="text-align: center; color: #718096; padding: 40px;">옵션을 선택하시면 주문 내역이 표시됩니다.</p>';
                return;
            }

            const customerName = document.getElementById('customer_name').value;
            const customerPhone = document.getElementById('customer_phone').value;

            previewEl.innerHTML = `
                <div class="preview-grid">
                    <div class="preview-item">
                        <div class="preview-label">전단지 타입</div>
                        <div class="preview-value">${optionLabels.style[style]}</div>
                    </div>
                    <div class="preview-item">
                        <div class="preview-label">용지 크기</div>
                        <div class="preview-value">${optionLabels.section[section]}</div>
                    </div>
                    <div class="preview-item">
                        <div class="preview-label">주문 수량</div>
                        <div class="preview-value">${optionLabels.quantity[quantity]}</div>
                    </div>
                    <div class="preview-item">
                        <div class="preview-label">용지 종류</div>
                        <div class="preview-value">${optionLabels.treeselect[treeselect]}</div>
                    </div>
                    <div class="preview-item">
                        <div class="preview-label">인쇄 방식</div>
                        <div class="preview-value">${optionLabels.po_type[poType]}</div>
                    </div>
                    <div class="preview-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="preview-label" style="color: rgba(255,255,255,0.8);">총 결제 금액</div>
                        <div class="preview-value" style="color: white; font-size: 1.3rem;">${totalCost ? totalCost.toLocaleString() + '원' : '계산 중...'}</div>
                    </div>
                    ${customerName ? `<div class="preview-item">
                        <div class="preview-label">주문자</div>
                        <div class="preview-value">${customerName}</div>
                    </div>` : ''}
                    ${customerPhone ? `<div class="preview-item">
                        <div class="preview-label">연락처</div>
                        <div class="preview-value">${customerPhone}</div>
                    </div>` : ''}
                </div>
            `;
        }

        // 이벤트 리스너 추가
        selects.forEach(select => {
            select.addEventListener('change', calculatePrice);
        });

        // 고객 정보 입력시 미리보기 업데이트
        document.getElementById('customer_name').addEventListener('input', () => {
            if (currentTotalCost > 0) {
                const style = document.getElementById('style').value;
                const section = document.getElementById('section').value;
                const quantity = document.getElementById('quantity').value;
                const treeselect = document.getElementById('treeselect').value;
                const poType = document.getElementById('po_type').value;
                updatePreview(style, section, quantity, treeselect, poType, currentTotalCost);
            }
        });

        document.getElementById('customer_phone').addEventListener('input', () => {
            if (currentTotalCost > 0) {
                const style = document.getElementById('style').value;
                const section = document.getElementById('section').value;
                const quantity = document.getElementById('quantity').value;
                const treeselect = document.getElementById('treeselect').value;
                const poType = document.getElementById('po_type').value;
                updatePreview(style, section, quantity, treeselect, poType, currentTotalCost);
            }
        });

        // 주문 버튼 클릭 처리
        orderBtn.addEventListener('click', async function() {
            // 필수 필드 검증
            const style = document.getElementById('style').value;
            const section = document.getElementById('section').value;
            const quantity = document.getElementById('quantity').value;
            const treeselect = document.getElementById('treeselect').value;
            const poType = document.getElementById('po_type').value;
            const customerName = document.getElementById('customer_name').value.trim();
            const customerPhone = document.getElementById('customer_phone').value.trim();

            if (!style || !section || !quantity || !treeselect || !poType) {
                errorMessageEl.textContent = '모든 주문 옵션을 선택해주세요.';
                errorMessageEl.style.display = 'block';
                successMessageEl.style.display = 'none';
                return;
            }

            if (!customerName) {
                errorMessageEl.textContent = '성함을 입력해주세요.';
                errorMessageEl.style.display = 'block';
                successMessageEl.style.display = 'none';
                return;
            }

            if (!customerPhone) {
                errorMessageEl.textContent = '연락처를 입력해주세요.';
                errorMessageEl.style.display = 'block';
                successMessageEl.style.display = 'none';
                return;
            }

            if (currentTotalCost === 0) {
                errorMessageEl.textContent = '가격을 확인할 수 없습니다. 옵션을 다시 확인해주세요.';
                errorMessageEl.style.display = 'block';
                successMessageEl.style.display = 'none';
                return;
            }

            // 주문 확인
            if (!confirm(`총 ${currentTotalCost.toLocaleString()}원의 전단지를 주문하시겠습니까?`)) {
                return;
            }

            // 로딩 상태
            btnText.innerHTML = '<div class="loading"></div>주문 처리중...';
            orderBtn.disabled = true;
            errorMessageEl.style.display = 'none';
            successMessageEl.style.display = 'none';

            try {
                // 주문 데이터 준비
                const orderData = {
                    style: style,
                    section: section,
                    quantity: quantity,
                    treeselect: treeselect,
                    po_type: poType,
                    total_cost: currentTotalCost.toLocaleString() + '원',
                    customer_name: customerName,
                    customer_phone: customerPhone,
                    customer_email: document.getElementById('customer_email').value.trim(),
                    order_memo: document.getElementById('order_memo').value.trim()
                };

                // 서버로 주문 데이터 전송
                const response = await fetch('process_flyer_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (result.success) {
                    successMessageEl.textContent = `주문이 성공적으로 접수되었습니다! 주문번호: ${result.data.order_no}`;
                    successMessageEl.style.display = 'block';
                    errorMessageEl.style.display = 'none';

                    // 폼 초기화
                    document.querySelectorAll('select, input, textarea').forEach(el => el.value = '');
                    resetPrice();
                    updatePreview();

                    // 성공 알림
                    setTimeout(() => {
                        alert(`주문이 완료되었습니다!\n주문번호: ${result.data.order_no}\n담당자가 곧 연락드리겠습니다.`);
                    }, 500);
                } else {
                    throw new Error(result.message || '주문 처리 중 오류가 발생했습니다.');
                }

            } catch (error) {
                console.error('주문 처리 오류:', error);
                errorMessageEl.textContent = '주문 처리 중 오류가 발생했습니다: ' + error.message;
                errorMessageEl.style.display = 'block';
                successMessageEl.style.display = 'none';
            } finally {
                // 버튼 상태 복원
                btnText.textContent = '주문하기';
                orderBtn.disabled = false;
            }
        });

        // 초기 로드시 가격 계산
        calculatePrice();
    </script>
</body>
</html>