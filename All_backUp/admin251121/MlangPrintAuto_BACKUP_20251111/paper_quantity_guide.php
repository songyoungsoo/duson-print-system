<?php
declare(strict_types=1);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>종이 연수 매수 계산 가이드 | 두손기획인쇄</title>
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
            line-height: 1.7;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .guide-section {
            background: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .guide-section h2 {
            color: #2d3748;
            margin-bottom: 20px;
            border-bottom: 3px solid #4299e1;
            padding-bottom: 10px;
        }

        .formula-box {
            background: #ebf8ff;
            border: 2px solid #4299e1;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .formula-box .formula {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2b6cb0;
            margin-bottom: 10px;
        }

        .calculation-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .calculation-table th,
        .calculation-table td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: center;
        }

        .calculation-table th {
            background: #4299e1;
            color: white;
            font-weight: 600;
        }

        .calculation-table tr:nth-child(even) {
            background: #f7fafc;
        }

        .example-box {
            background: #f0fff4;
            border: 2px solid #48bb78;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .step-process {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .step-card {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .step-card .step-number {
            background: #4299e1;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px auto;
            font-weight: bold;
        }

        .calculator-section {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
        }

        .calculator-input {
            background: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            width: 100px;
            text-align: center;
            margin: 0 10px;
        }

        .calc-result {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .back-button {
            display: inline-block;
            background: #4299e1;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            margin-top: 20px;
        }

        .back-button:hover {
            background: #3182ce;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📏 종이 연수 매수 계산 가이드</h1>
            <p>인쇄업계 표준 연수 환산법을 쉽게 이해해보세요</p>
        </div>

        <!-- 기본 개념 -->
        <div class="guide-section">
            <h2>🔢 기본 개념</h2>
            <div class="step-process">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4>연(連)이란?</h4>
                    <p>큰 원본 종이 500매를 1연이라고 합니다</p>
                    <small>A형: 630×930mm<br>B형: 780×1080mm</small>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4>재단이란?</h4>
                    <p>큰 종이를 반으로 자르는 과정입니다</p>
                    <small>재단할 때마다<br>매수가 2배로 증가</small>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4>매수 환산</h4>
                    <p>재단 횟수에 따라 최종 매수가 결정됩니다</p>
                    <small>500 × 2^재단횟수</small>
                </div>
            </div>
        </div>

        <!-- 매수 환산 공식 -->
        <div class="guide-section">
            <h2>📊 매수 환산 공식</h2>
            <div class="formula-box">
                <div class="formula">매수 = 500매 × 2^(재단횟수)</div>
                <p>재단횟수 = 연수 - 1</p>
            </div>

            <table class="calculation-table">
                <thead>
                    <tr>
                        <th>연수</th>
                        <th>재단횟수</th>
                        <th>계산식</th>
                        <th>실제 매수</th>
                        <th>사용 예시</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>1연</strong></td>
                        <td>0회</td>
                        <td>500 × 2⁰</td>
                        <td><strong>500매</strong></td>
                        <td>대형 포스터</td>
                    </tr>
                    <tr>
                        <td><strong>2연</strong></td>
                        <td>1회</td>
                        <td>500 × 2¹</td>
                        <td><strong>1,000매</strong></td>
                        <td>A3 크기</td>
                    </tr>
                    <tr>
                        <td><strong>3연</strong></td>
                        <td>2회</td>
                        <td>500 × 2²</td>
                        <td><strong>2,000매</strong></td>
                        <td>A4 크기</td>
                    </tr>
                    <tr>
                        <td><strong>4연</strong></td>
                        <td>3회</td>
                        <td>500 × 2³</td>
                        <td><strong>4,000매</strong></td>
                        <td>B5, 16절</td>
                    </tr>
                    <tr>
                        <td><strong>5연</strong></td>
                        <td>4회</td>
                        <td>500 × 2⁴</td>
                        <td><strong>8,000매</strong></td>
                        <td>A5, 32절</td>
                    </tr>
                    <tr>
                        <td><strong>6연</strong></td>
                        <td>5회</td>
                        <td>500 × 2⁵</td>
                        <td><strong>16,000매</strong></td>
                        <td>소형 전단지</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 실무 예시 -->
        <div class="guide-section">
            <h2>💡 실무 예시</h2>

            <div class="example-box">
                <h4>📝 예시 1: A4 전단지 2,000매 주문</h4>
                <p><strong>필요한 연수:</strong> 3연</p>
                <p><strong>계산 과정:</strong></p>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>1단계: 1연 (500매) 원본 준비</li>
                    <li>2단계: 1회 재단 → 1,000매</li>
                    <li>3단계: 2회 재단 → 2,000매 완성</li>
                </ul>
            </div>

            <div class="example-box">
                <h4>📝 예시 2: 명함 크기 4,000매 주문</h4>
                <p><strong>필요한 연수:</strong> 4연</p>
                <p><strong>계산 과정:</strong></p>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>1단계: 1연 (500매) 원본 준비</li>
                    <li>2단계: 1회 재단 → 1,000매</li>
                    <li>3단계: 2회 재단 → 2,000매</li>
                    <li>4단계: 3회 재단 → 4,000매 완성</li>
                </ul>
            </div>
        </div>

        <!-- 간편 계산기 -->
        <div class="calculator-section">
            <h2 style="margin-bottom: 20px;">🔧 간편 계산기</h2>
            <div style="text-align: center;">
                <p style="margin-bottom: 15px;">필요한 매수를 입력하면 연수를 자동 계산합니다</p>

                <div>
                    <label style="font-size: 1.1rem;">필요한 매수:</label>
                    <input type="number" id="required-quantity" class="calculator-input" placeholder="2000" min="1">
                    <span style="font-size: 1.1rem;">매</span>
                </div>

                <div class="calc-result" id="calc-result">
                    매수를 입력하세요
                </div>
            </div>
        </div>

        <!-- 주의사항 -->
        <div class="guide-section">
            <h2>⚠️ 주의사항</h2>
            <ul style="margin-left: 20px; line-height: 2;">
                <li><strong>재단 우스리:</strong> 실제 크기는 재단 후 우스리를 제거한 크기입니다</li>
                <li><strong>최소 주문:</strong> 0.5연(250매)부터 주문 가능합니다</li>
                <li><strong>대량 주문:</strong> 10연 이상은 별도 문의 바랍니다</li>
                <li><strong>용지 종류:</strong> 용지에 따라 재단 방식이 다를 수 있습니다</li>
            </ul>
        </div>

        <div style="text-align: center;">
            <a href="flyer_order_step_by_step.php" class="back-button">← 주문하러 가기</a>
        </div>
    </div>

    <script>
        // 간편 계산기
        document.getElementById('required-quantity').addEventListener('input', function() {
            const quantity = parseInt(this.value);
            const resultDiv = document.getElementById('calc-result');

            if (!quantity || quantity < 1) {
                resultDiv.textContent = '매수를 입력하세요';
                return;
            }

            // 필요한 연수 계산
            let requiredRyeon = 1;
            let calculatedQuantity = 500;

            while (calculatedQuantity < quantity) {
                requiredRyeon++;
                calculatedQuantity = 500 * Math.pow(2, requiredRyeon - 1);
            }

            // 결과 표시
            const actualQuantity = calculatedQuantity.toLocaleString();
            const cutting = requiredRyeon - 1;

            if (calculatedQuantity === quantity) {
                resultDiv.innerHTML = `
                    <strong>${requiredRyeon}연</strong> 선택 → <strong>${actualQuantity}매</strong> 정확히 일치!<br>
                    <small>(${cutting}회 재단)</small>
                `;
            } else {
                resultDiv.innerHTML = `
                    <strong>${requiredRyeon}연</strong> 선택 → <strong>${actualQuantity}매</strong> 생산<br>
                    <small>(${cutting}회 재단, 여유분 ${(calculatedQuantity - quantity).toLocaleString()}매)</small>
                `;
            }
        });
    </script>
</body>
</html>