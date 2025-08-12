<?php
/**
 * AJAX 시스템 테스트 파일
 * 새로운 AJAX 기반 시스템이 올바르게 작동하는지 확인
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJAX 시스템 테스트</title>
    <style>
        body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .test-result { margin: 10px 0; padding: 10px; background: #f5f5f5; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>AJAX 시스템 테스트</h1>
    
    <div class="test-section">
        <h2>1. 종이종류 옵션 테스트</h2>
        <button onclick="testPaperTypes()">종이종류 옵션 가져오기 테스트</button>
        <div id="paperTypesResult" class="test-result"></div>
    </div>
    
    <div class="test-section">
        <h2>2. 종이규격 옵션 테스트</h2>
        <button onclick="testPaperSizes()">종이규격 옵션 가져오기 테스트</button>
        <div id="paperSizesResult" class="test-result"></div>
    </div>
    
    <div class="test-section">
        <h2>3. 가격 계산 테스트</h2>
        <button onclick="testPriceCalculation()">가격 계산 테스트</button>
        <div id="priceCalculationResult" class="test-result"></div>
    </div>
    
    <div class="test-section">
        <h2>4. 통합 테스트</h2>
        <button onclick="testIntegration()">전체 시스템 통합 테스트</button>
        <div id="integrationResult" class="test-result"></div>
    </div>

    <script>
        function testPaperTypes() {
            const resultDiv = document.getElementById('paperTypesResult');
            resultDiv.innerHTML = '테스트 진행 중...';
            
            // 첫 번째 인쇄색상 ID로 테스트 (일반적으로 1)
            const testCV_no = '1';
            
            fetch(`get_paper_types.php?CV_no=${testCV_no}&page=inserted`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        resultDiv.innerHTML = `<div class="error">❌ 오류: ${data.message}</div>`;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="success">✅ 성공: ${data.length}개의 종이종류 옵션을 가져왔습니다.</div>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">❌ 네트워크 오류: ${error.message}</div>`;
                });
        }
        
        function testPaperSizes() {
            const resultDiv = document.getElementById('paperSizesResult');
            resultDiv.innerHTML = '테스트 진행 중...';
            
            // 첫 번째 인쇄색상 ID로 테스트 (일반적으로 1)
            const testCV_no = '1';
            
            fetch(`get_paper_sizes.php?CV_no=${testCV_no}&page=inserted`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        resultDiv.innerHTML = `<div class="error">❌ 오류: ${data.message}</div>`;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="success">✅ 성공: ${data.length}개의 종이규격 옵션을 가져왔습니다.</div>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">❌ 네트워크 오류: ${error.message}</div>`;
                });
        }
        
        function testPriceCalculation() {
            const resultDiv = document.getElementById('priceCalculationResult');
            resultDiv.innerHTML = '테스트 진행 중...';
            
            // 테스트용 파라미터
            const params = new URLSearchParams({
                MY_type: '1',
                PN_type: '1', 
                MY_Fsd: '1',
                MY_amount: '1',
                ordertype: 'total',
                POtype: '1'
            });
            
            fetch(`calculate_price_ajax.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="success">✅ 가격 계산 성공</div>
                            <pre>${JSON.stringify(data.data, null, 2)}</pre>
                        `;
                    } else {
                        resultDiv.innerHTML = `<div class="error">❌ 가격 계산 실패: ${data.error.message}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">❌ 네트워크 오류: ${error.message}</div>`;
                });
        }
        
        function testIntegration() {
            const resultDiv = document.getElementById('integrationResult');
            resultDiv.innerHTML = '통합 테스트 진행 중...';
            
            let testResults = [];
            
            // 1단계: 종이종류 옵션 테스트
            fetch('get_paper_types.php?CV_no=1&page=inserted')
                .then(response => response.json())
                .then(paperTypes => {
                    if (paperTypes.error) {
                        throw new Error('종이종류 옵션 가져오기 실패');
                    }
                    testResults.push('✅ 종이종류 옵션 가져오기 성공');
                    
                    // 2단계: 종이규격 옵션 테스트
                    return fetch('get_paper_sizes.php?CV_no=1&page=inserted');
                })
                .then(response => response.json())
                .then(paperSizes => {
                    if (paperSizes.error) {
                        throw new Error('종이규격 옵션 가져오기 실패');
                    }
                    testResults.push('✅ 종이규격 옵션 가져오기 성공');
                    
                    // 3단계: 가격 계산 테스트
                    const params = new URLSearchParams({
                        MY_type: '1',
                        PN_type: '1',
                        MY_Fsd: '1', 
                        MY_amount: '1',
                        ordertype: 'total',
                        POtype: '1'
                    });
                    
                    return fetch(`calculate_price_ajax.php?${params.toString()}`);
                })
                .then(response => response.json())
                .then(priceData => {
                    if (!priceData.success) {
                        throw new Error('가격 계산 실패');
                    }
                    testResults.push('✅ 가격 계산 성공');
                    
                    // 모든 테스트 성공
                    resultDiv.innerHTML = `
                        <div class="success">
                            <h3>🎉 통합 테스트 성공!</h3>
                            ${testResults.map(result => `<div>${result}</div>`).join('')}
                            <p><strong>새로운 AJAX 시스템이 정상적으로 작동합니다.</strong></p>
                        </div>
                    `;
                })
                .catch(error => {
                    testResults.push(`❌ ${error.message}`);
                    resultDiv.innerHTML = `
                        <div class="error">
                            <h3>❌ 통합 테스트 실패</h3>
                            ${testResults.map(result => `<div>${result}</div>`).join('')}
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>