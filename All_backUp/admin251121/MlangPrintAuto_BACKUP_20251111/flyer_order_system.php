<?php
declare(strict_types=1);

// 전단지 주문 시스템
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
    'design_money' => [
        '30000' => '기본 디자인 (30,000원)',
        '40000' => '표준 디자인 (40,000원)',
        '50000' => '고급 디자인 (50,000원)',
        '60000' => '프리미엄 디자인 (60,000원)',
        '65000' => '디럭스 디자인 (65,000원)',
        '70000' => '스페셜 디자인 (70,000원)',
        '80000' => '마스터 디자인 (80,000원)',
        '90000' => '엘리트 디자인 (90,000원)',
        '100000' => '최고급 디자인 (100,000원)'
    ],
    'po_type' => [
        '1' => '단면 인쇄',
        '2' => '양면 인쇄'
    ]
];

// 가격 데이터 로드 함수
function loadPriceData($csv_file) {
    $price_data = [];
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        $header = fgetcsv($handle); // 헤더 스킵
        while (($data = fgetcsv($handle)) !== FALSE) {
            $key = $data[1] . '_' . $data[2] . '_' . $data[3] . '_' . $data[5] . '_' . $data[7]; // style_section_quantity_treeselect_potype
            $price_data[$key] = [
                'money' => (int)str_replace('"', '', $data[4]),
                'design_money' => (int)str_replace('"', '', $data[6]),
                'quantity_two' => (int)str_replace('"', '', $data[8])
            ];
        }
        fclose($handle);
    }
    return $price_data;
}

// 가격 계산 함수
function calculatePrice($style, $section, $quantity, $treeselect, $po_type, $price_data) {
    $key = $style . '_' . $section . '_' . $quantity . '_' . $treeselect . '_' . $po_type;

    if (isset($price_data[$key])) {
        $data = $price_data[$key];
        return [
            'printing_cost' => $data['money'],
            'design_cost' => $data['design_money'],
            'total_cost' => $data['money'] + $data['design_money'],
            'found' => true
        ];
    }

    return ['found' => false, 'error' => '해당 옵션 조합의 가격을 찾을 수 없습니다.'];
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>전단지 주문 시스템</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #2d3748;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .order-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .form-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3748;
        }

        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            background-color: white;
            transition: border-color 0.3s;
        }

        .form-group select:focus {
            outline: none;
            border-color: #4299e1;
        }

        .price-section {
            background: #edf2f7;
            padding: 25px;
            border-radius: 8px;
            border-left: 5px solid #4299e1;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .price-item.total {
            border-top: 2px solid #4299e1;
            padding-top: 15px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 1.2rem;
            color: #2d3748;
        }

        .order-button {
            width: 100%;
            padding: 15px;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }

        .order-button:hover {
            background: #3182ce;
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            display: none;
        }

        .preview-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .preview-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .order-form {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 전단지 주문 시스템</h1>
            <p>원하시는 옵션을 선택하시면 실시간으로 가격이 계산됩니다.</p>
        </div>

        <form id="orderForm" class="order-form">
            <div class="form-section">
                <h3>📋 주문 옵션 선택</h3>

                <div class="form-group">
                    <label for="style">전단지 타입</label>
                    <select id="style" name="style" required>
                        <option value="">타입을 선택하세요</option>
                        <?php foreach ($flyer_options['style'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="section">용지 크기</label>
                    <select id="section" name="section" required>
                        <option value="">크기를 선택하세요</option>
                        <?php foreach ($flyer_options['section'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">주문 수량</label>
                    <select id="quantity" name="quantity" required>
                        <option value="">수량을 선택하세요</option>
                        <?php foreach ($flyer_options['quantity'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="treeselect">용지 종류</label>
                    <select id="treeselect" name="treeselect" required>
                        <option value="">용지를 선택하세요</option>
                        <?php foreach ($flyer_options['treeselect'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="po_type">인쇄 방식</label>
                    <select id="po_type" name="po_type" required>
                        <option value="">인쇄 방식을 선택하세요</option>
                        <?php foreach ($flyer_options['po_type'] as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <div class="price-section">
                    <h3>💰 가격 계산</h3>
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

                <button type="submit" class="order-button">주문하기</button>
            </div>
        </form>

        <div class="preview-section">
            <h3>📝 주문 미리보기</h3>
            <div id="order-preview">
                <p style="text-align: center; color: #718096;">옵션을 선택하시면 주문 내역이 표시됩니다.</p>
            </div>
        </div>
    </div>

    <script>
        // 가격 데이터 (PHP에서 JSON으로 전달)
        const priceData = <?= json_encode(loadPriceData('f:\데이터엑셀화\mlangprintauto_inserted.csv')) ?>;
        const optionLabels = <?= json_encode($flyer_options) ?>;

        // 폼 요소들
        const form = document.getElementById('orderForm');
        const selects = form.querySelectorAll('select');
        const printingCostEl = document.getElementById('printing-cost');
        const designCostEl = document.getElementById('design-cost');
        const totalCostEl = document.getElementById('total-cost');
        const errorMessageEl = document.getElementById('error-message');
        const previewEl = document.getElementById('order-preview');

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
        }

        // 미리보기 업데이트
        function updatePreview(style, section, quantity, treeselect, poType, totalCost) {
            if (!style || !section || !quantity || !treeselect || !poType) {
                previewEl.innerHTML = '<p style="text-align: center; color: #718096;">옵션을 선택하시면 주문 내역이 표시됩니다.</p>';
                return;
            }

            const preview = `
                <div class="preview-item">
                    <span>전단지 타입:</span>
                    <span>${optionLabels.style[style]}</span>
                </div>
                <div class="preview-item">
                    <span>용지 크기:</span>
                    <span>${optionLabels.section[section]}</span>
                </div>
                <div class="preview-item">
                    <span>주문 수량:</span>
                    <span>${optionLabels.quantity[quantity]}</span>
                </div>
                <div class="preview-item">
                    <span>용지 종류:</span>
                    <span>${optionLabels.treeselect[treeselect]}</span>
                </div>
                <div class="preview-item">
                    <span>인쇄 방식:</span>
                    <span>${optionLabels.po_type[poType]}</span>
                </div>
                <div class="preview-item" style="font-weight: bold; color: #2d3748; border-top: 2px solid #4299e1; padding-top: 10px; margin-top: 10px;">
                    <span>총 결제 금액:</span>
                    <span>${totalCost ? totalCost.toLocaleString() + '원' : '계산 중...'}</span>
                </div>
            `;
            previewEl.innerHTML = preview;
        }

        // 이벤트 리스너 추가
        selects.forEach(select => {
            select.addEventListener('change', calculatePrice);
        });

        // 폼 제출 처리
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const style = document.getElementById('style').value;
            const section = document.getElementById('section').value;
            const quantity = document.getElementById('quantity').value;
            const treeselect = document.getElementById('treeselect').value;
            const poType = document.getElementById('po_type').value;

            if (!style || !section || !quantity || !treeselect || !poType) {
                alert('모든 옵션을 선택해주세요.');
                return;
            }

            const totalCost = totalCostEl.textContent;
            if (totalCost === '0원') {
                alert('가격을 확인할 수 없습니다. 옵션을 다시 확인해주세요.');
                return;
            }

            if (confirm(`총 ${totalCost}의 전단지를 주문하시겠습니까?`)) {
                // 실제 주문 처리 로직
                alert('주문이 접수되었습니다. 담당자가 곧 연락드리겠습니다.');

                // 주문 데이터를 서버로 전송
                const orderData = {
                    style: style,
                    section: section,
                    quantity: quantity,
                    treeselect: treeselect,
                    po_type: poType,
                    total_cost: totalCost
                };

                console.log('주문 데이터:', orderData);
                // 실제로는 fetch API나 AJAX를 사용해서 서버로 전송
            }
        });

        // 초기 로드시 가격 계산
        calculatePrice();
    </script>
</body>
</html>