<?php

declare(strict_types=1);

// 단계별 전단지 주문 시스템
include "../../db.php";

// 편집디자인 가격 계산 함수
function calculateDesignPrice($section, $po_type)
{
    // A4 이하 크기 정의 (면적 기준)
    $a4_area = 210 * 297; // 62,370

    $size_areas = [
        '821' => 210 * 297, // A4
        '822' => 148 * 210, // A5 ()
        '944' => 100 * 150, // 소형
        '824' => 176 * 250, // B5(16절 )
        '818' => 250 * 353, // B4
        '820' => 353 * 500, // B3 (4절)
        '823' => 297 * 420, // A3 (A3)
        '826' => 200 * 200, // 정사각형
    ];

    // 기타규격은 기본가격 적용
    if (!isset($size_areas[$section])) {
        return $po_type == '1' ? 30000 : 60000;
    }

    $section_area = $size_areas[$section];
    $base_price = $po_type == '1' ? 30000 : 60000; // 단면/양면 기본가격

    // A4 이하면 기본가격, 이상이면 1.5배
    if ($section_area <= $a4_area) {
        return $base_price;
    } else {
        return intval($base_price * 1.5);
    }
}

// 선택 옵션 정의
$flyer_options = [
    'style' => [
        '802' => 'B형 전단지 (780×1080 원판)',
        '625' => 'A형 전단지 (630×930 원판)'
    ],
    'section' => [
        '818' => '8절 (257×367mm)',
        '820' => '4절 (367×517mm)',
        '821' => 'A4 (210×297mm)',
        '822' => 'B6 32절 (127×182mm)',
        '823' => 'A2 국2절 (420x594mm)',
        '824' => 'B5 16절 (182×257mm)',
        '826' => '정사각형 (200×200mm)',
        '628' => '기타규격',
        '629' => '기타규격',
        '630' => '기타규격',
        '631' => '기타규격',
        '632' => '기타규격',
        '944' => '소형 (100×150mm)'
    ],
    'po_type' => [
        '1' => '단면 인쇄',
        '2' => '양면 인쇄'
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
        '10' => '256,000매 (1연을 9회재단)'
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
    ]
];

// 가격 데이터 로드
function loadPriceData($csv_file)
{
    $price_data = [];
    if (file_exists($csv_file) && ($handle = fopen($csv_file, "r")) !== FALSE) {
        $header = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== FALSE) {
            $key = $data[1] . '_' . $data[2] . '_' . $data[3] . '_' . $data[5] . '_' . $data[7];
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
    <title>단계별 전단지 주문 시스템</title>
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
            max-width: 1000px;
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .step-indicators {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #718096;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s;
        }

        .step.active .step-number {
            background: #4299e1;
            color: white;
        }

        .step.completed .step-number {
            background: #48bb78;
            color: white;
        }

        .step-title {
            font-size: 0.9rem;
            color: #718096;
            text-align: center;
        }

        .step.active .step-title {
            color: #4299e1;
            font-weight: 600;
        }

        .step-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .step-content.hidden {
            display: none;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: #2d3748;
            font-size: 1.1rem;
        }

        .option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .option-card {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .option-card:hover {
            border-color: #4299e1;
            background: #ebf8ff;
        }

        .option-card.selected {
            border-color: #4299e1;
            background: #4299e1;
            color: white;
        }

        .price-summary {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .price-item.total {
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            padding-top: 15px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 1.3rem;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
        }

        .btn-prev {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-prev:hover {
            background: #cbd5e0;
        }

        .btn-next {
            background: #4299e1;
            color: white;
        }

        .btn-next:hover {
            background: #3182ce;
        }

        .btn-order {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }

        .btn-order:hover {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .step-indicators {
                flex-wrap: wrap;
            }

            .step {
                margin-bottom: 15px;
            }

            .option-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎯 단계별 전단지 주문</h1>
            <p>순서대로 선택하시면 정확한 견적을 확인할 수 있습니다</p>
        </div>

        <!-- 진행 상황 표시 -->
        <div class="progress-bar">
            <div class="step-indicators">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-title">종이종류</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-title">종이규격</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-title">인쇄면</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-title">수량</div>
                </div>
                <div class="step" data-step="5">
                    <div class="step-number">5</div>
                    <div class="step-title">매수(연)</div>
                </div>
                <div class="step" data-step="6">
                    <div class="step-number">6</div>
                    <div class="step-title">편집디자인</div>
                </div>
            </div>
        </div>

        <!-- 1단계: 종이종류 선택 -->
        <div class="step-content" id="step-1">
            <div class="form-group">
                <label>1단계: 종이종류를 선택하세요</label>
                <div class="option-grid">
                    <?php foreach ($flyer_options['treeselect'] as $key => $value): ?>
                        <div class="option-card" data-value="<?= $key ?>" data-step="treeselect">
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 2단계: 종이규격 선택 -->
        <div class="step-content hidden" id="step-2">
            <div class="form-group">
                <label>2단계: 종이규격(크기)을 선택하세요</label>
                <div class="option-grid">
                    <?php foreach ($flyer_options['section'] as $key => $value): ?>
                        <div class="option-card" data-value="<?= $key ?>" data-step="section">
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 3단계: 인쇄면 선택 -->
        <div class="step-content hidden" id="step-3">
            <div class="form-group">
                <label>3단계: 인쇄면을 선택하세요</label>
                <div class="option-grid">
                    <?php foreach ($flyer_options['po_type'] as $key => $value): ?>
                        <div class="option-card" data-value="<?= $key ?>" data-step="po_type">
                            <strong><?= $value ?></strong>
                            <br><small><?= $key == '1' ? '한쪽면만 인쇄' : '양쪽면 모두 인쇄' ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 4단계: 수량 선택 -->
        <div class="step-content hidden" id="step-4">
            <div class="form-group">
                <label>4단계: 수량(연 기준)을 선택하세요</label>

                <!-- 연수 매수 계산법 가이드 -->
                <div style="background: #ebf8ff; border: 2px solid #4299e1; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                    <h4 style="color: #2b6cb0; margin-bottom: 15px;">📏 연수에 대한 매수 계산 방법</h4>

                    <div style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                        <h5 style="color: #2d3748; margin-bottom: 10px;">🔢 기본 원리:</h5>
                        <ul style="margin-left: 20px; line-height: 1.8;">
                            <li><strong>1연</strong> = 원본 크기 500매 (A형: 630×930mm, B형: 780×1080mm)</li>
                            <li><strong>재단</strong> = 큰 종이를 반으로 자르면 매수가 2배로 증가</li>
                            <li><strong>매수 환산법</strong> = 500매 × 2^(재단횟수)</li>
                        </ul>
                    </div>

                    <div style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                        <h5 style="color: #2d3748; margin-bottom: 10px;">📊 매수 환산표:</h5>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; font-size: 0.9rem;">
                            <div><strong>1연</strong> → 500매 (원본)</div>
                            <div><strong>2연</strong> → 1,000매 (1회재단)</div>
                            <div><strong>3연</strong> → 2,000매 (2회재단)</div>
                            <div><strong>4연</strong> → 4,000매 (3회재단)</div>
                            <div><strong>5연</strong> → 8,000매 (4회재단)</div>
                            <div><strong>6연</strong> → 16,000매 (5회재단)</div>
                        </div>
                    </div>

                    <div style="background: #f0fff4; border: 1px solid #68d391; padding: 12px; border-radius: 6px;">
                        <strong style="color: #22543d;">💡 예시:</strong>
                        <span style="color: #2f855a;">
                            A4 크기 2,000매가 필요하다면 → "3연" 선택
                            (1연 500매를 2회 재단하여 2,000매 완성)
                        </span>
                    </div>
                </div>

                <div class="option-grid">
                    <?php foreach ($flyer_options['quantity'] as $key => $value): ?>
                        <div class="option-card" data-value="<?= $key ?>" data-step="quantity">
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 5단계: 매수(연) 확인 -->
        <div class="step-content hidden" id="step-5">
            <div class="form-group">
                <label>5단계: 매수(연) 확인</label>
                <div id="quantity-summary">
                    <p>선택하신 수량에 따른 매수를 확인해주세요.</p>
                </div>
            </div>
        </div>

        <!-- 6단계: 편집디자인 -->
        <div class="step-content hidden" id="step-6">
            <div class="form-group">
                <label>6단계: 편집디자인을 선택하세요</label>
                <div class="option-grid">
                    <div class="option-card" data-value="yes" data-step="design">
                        <strong>편집디자인 필요</strong>
                        <br><small id="design-price-text">가격은 크기에 따라 산정됩니다</small>
                    </div>
                    <div class="option-card" data-value="no" data-step="design">
                        <strong>편집디자인 불필요</strong>
                        <br><small>디자인 완료된 파일 보유</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 가격 요약 -->
        <div class="price-summary">
            <h3 style="margin-bottom: 15px;">💰 견적 요약</h3>
            <div class="price-item">
                <span>인쇄비:</span>
                <span id="printing-cost">0원</span>
            </div>
            <div class="price-item">
                <span>디자인비:</span>
                <span id="design-cost">0원</span>
            </div>
            <div class="price-item total">
                <span>총 견적:</span>
                <span id="total-cost">0원</span>
            </div>
        </div>

        <!-- 네비게이션 버튼 -->
        <div class="navigation-buttons">
            <button class="btn btn-prev" id="prev-btn" onclick="previousStep()" disabled>이전 단계</button>
            <button class="btn btn-next" id="next-btn" onclick="nextStep()" disabled>다음 단계</button>
            <button class="btn btn-order hidden" id="order-btn" onclick="submitOrder()">주문하기</button>
        </div>
    </div>

    <script>
        // 전역 변수
        let currentStep = 1;
        const totalSteps = 6;
        const selections = {};
        const priceData = <?= json_encode(loadPriceData('f:\데이터엑셀화\mlangprintauto_inserted.csv')) ?>;
        const optionLabels = <?= json_encode($flyer_options) ?>;

        // 옵션 카드 클릭 이벤트
        document.addEventListener('click', function(e) {
            if (e.target.closest('.option-card')) {
                const card = e.target.closest('.option-card');
                const step = card.dataset.step;
                const value = card.dataset.value;

                // 같은 단계의 다른 카드들 선택 해제
                document.querySelectorAll(`[data-step="${step}"]`).forEach(c => c.classList.remove('selected'));

                // 현재 카드 선택
                card.classList.add('selected');

                // 선택값 저장
                selections[step] = value;

                // 다음 버튼 활성화
                document.getElementById('next-btn').disabled = false;

                // 가격 업데이트
                updatePrice();

                console.log('선택됨:', step, value, selections);
            }
        });

        // 다음 단계로 이동
        function nextStep() {
            if (currentStep < totalSteps) {
                // 현재 단계 완료 표시
                document.querySelector(`[data-step="${currentStep}"]`).classList.add('completed');
                document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');

                currentStep++;

                // 다음 단계 활성화
                document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');

                // 화면 전환
                document.querySelectorAll('.step-content').forEach(content => content.classList.add('hidden'));
                document.getElementById(`step-${currentStep}`).classList.remove('hidden');

                // 특수 처리
                if (currentStep === 5) {
                    updateQuantitySummary();
                }
                if (currentStep === 6) {
                    updateDesignPriceText();
                }

                // 버튼 상태 업데이트
                updateButtons();
            }
        }

        // 이전 단계로 이동
        function previousStep() {
            if (currentStep > 1) {
                // 현재 단계 비활성화
                document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');

                currentStep--;

                // 이전 단계 활성화
                document.querySelector(`[data-step="${currentStep}"]`).classList.remove('completed');
                document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');

                // 화면 전환
                document.querySelectorAll('.step-content').forEach(content => content.classList.add('hidden'));
                document.getElementById(`step-${currentStep}`).classList.remove('hidden');

                // 버튼 상태 업데이트
                updateButtons();
            }
        }

        // 버튼 상태 업데이트
        function updateButtons() {
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const orderBtn = document.getElementById('order-btn');

            prevBtn.disabled = currentStep === 1;

            if (currentStep === totalSteps) {
                nextBtn.classList.add('hidden');
                orderBtn.classList.remove('hidden');
                orderBtn.disabled = !selections.design;
            } else {
                nextBtn.classList.remove('hidden');
                orderBtn.classList.add('hidden');

                // 현재 단계의 선택이 있는지 확인
                const stepKeys = {
                    1: 'treeselect',
                    2: 'section',
                    3: 'po_type',
                    4: 'quantity',
                    5: 'quantity', // 확인 단계
                    6: 'design'
                };

                nextBtn.disabled = !selections[stepKeys[currentStep]];
            }
        }

        // 수량 요약 업데이트
        function updateQuantitySummary() {
            const quantityKey = selections.quantity;
            if (quantityKey && optionLabels.quantity[quantityKey]) {
                document.getElementById('quantity-summary').innerHTML = `
                    <div style="background: #f7fafc; padding: 20px; border-radius: 8px; text-align: center;">
                        <h3>선택하신 수량</h3>
                        <p style="font-size: 1.2rem; font-weight: bold; color: #4299e1; margin: 10px 0;">
                            ${optionLabels.quantity[quantityKey]}
                        </p>
                        <p style="color: #718096;">매수 환산법에 의한 실제 매수입니다</p>
                    </div>
                `;
            }
        }

        // 디자인 가격 텍스트 업데이트
        function updateDesignPriceText() {
            const section = selections.section;
            const poType = selections.po_type;

            if (section && poType) {
                // PHP 함수와 동일한 로직으로 계산
                const a4Area = 210 * 297;
                const sizeAreas = {
                    '821': 210 * 297, // A4
                    '822': 148 * 210, // 32절
                    '944': 100 * 150, // 소형
                    '824': 176 * 250, // 16절
                    '818': 250 * 353, // 8절
                    '820': 353 * 500, // 4절
                    '823': 297 * 420, // 국2절
                    '826': 200 * 200, // 정사각형
                };

                const sectionArea = sizeAreas[section] || a4Area;
                const basePrice = poType == '1' ? 30000 : 60000;
                const designPrice = sectionArea <= a4Area ? basePrice : Math.floor(basePrice * 1.5);

                document.getElementById('design-price-text').textContent =
                    `${poType == '1' ? '단면' : '양면'} ${designPrice.toLocaleString()}원`;
            }
        }

        // 가격 업데이트
        function updatePrice() {
            if (!selections.treeselect || !selections.section || !selections.quantity || !selections.po_type) {
                return;
            }

            // 스타일은 임시로 802 사용 (B형)
            const style = '802';
            const key = `${style}_${selections.section}_${selections.quantity}_${selections.treeselect}_${selections.po_type}`;

            if (priceData[key]) {
                const printingCost = priceData[key].money;

                // 디자인 비용 계산 (JavaScript에서도 동일한 로직)
                const a4Area = 210 * 297;
                const sizeAreas = {
                    '821': 210 * 297,
                    '822': 148 * 210,
                    '944': 100 * 150,
                    '824': 176 * 250,
                    '818': 250 * 353,
                    '820': 353 * 500,
                    '823': 297 * 420,
                    '826': 200 * 200
                };

                const sectionArea = sizeAreas[selections.section] || a4Area;
                const basePrice = selections.po_type == '1' ? 30000 : 60000;
                const designCost = selections.design === 'yes' ?
                    (sectionArea <= a4Area ? basePrice : Math.floor(basePrice * 1.5)) : 0;

                const totalCost = printingCost + designCost;

                document.getElementById('printing-cost').textContent = printingCost.toLocaleString() + '원';
                document.getElementById('design-cost').textContent = designCost.toLocaleString() + '원';
                document.getElementById('total-cost').textContent = totalCost.toLocaleString() + '원';
            }
        }

        // 주문 제출
        function submitOrder() {
            // 고객 정보 입력받기
            const customerName = prompt('성함을 입력하세요:');
            const customerPhone = prompt('연락처를 입력하세요:');

            if (!customerName || !customerPhone) {
                alert('성함과 연락처를 모두 입력해주세요.');
                return;
            }

            // 주문 데이터 준비
            const orderData = {
                selections: selections,
                customer_name: customerName,
                customer_phone: customerPhone,
                total_cost: document.getElementById('total-cost').textContent
            };

            console.log('주문 데이터:', orderData);
            alert(`주문이 접수되었습니다!\n담당자가 곧 연락드리겠습니다.\n총 견적: ${orderData.total_cost}`);
        }

        // 초기화
        updateButtons();
    </script>
</body>

</html>