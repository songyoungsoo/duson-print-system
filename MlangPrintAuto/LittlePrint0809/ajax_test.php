<!DOCTYPE html>
<html>
<head>
    <title>LittlePrint Ajax 연결 테스트</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        button { padding: 10px 15px; margin: 5px; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>LittlePrint Ajax 연결 테스트</h1>
    
    <div class="test-section">
        <h2>1. 연결 테스트</h2>
        <button onclick="testConnection()">연결 테스트</button>
        <div id="connection-result"></div>
    </div>
    
    <div class="test-section">
        <h2>2. 메인 카테고리 조회</h2>
        <button onclick="testMainCategories()">종류 목록 조회</button>
        <div id="categories-result"></div>
    </div>
    
    <div class="test-section">
        <h2>3. 종이종류 조회</h2>
        <input type="number" id="category-id" placeholder="종류 ID 입력" value="1">
        <button onclick="testPaperTypes()">종이종류 조회</button>
        <div id="paper-types-result"></div>
    </div>
    
    <div class="test-section">
        <h2>4. 종이규격 조회</h2>
        <input type="number" id="category-id-2" placeholder="종류 ID 입력" value="1">
        <button onclick="testPaperSizes()">종이규격 조회</button>
        <div id="paper-sizes-result"></div>
    </div>
    
    <div class="test-section">
        <h2>5. 가격 계산</h2>
        <button onclick="testPriceCalculation()">가격 계산 테스트</button>
        <div id="price-result"></div>
    </div>

    <script>
        function testConnection() {
            const resultDiv = document.getElementById('connection-result');
            resultDiv.innerHTML = '테스트 중...';
            
            fetch('ajax/test_connection.php', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `<div class="success">✅ 연결 성공!</div><pre>${JSON.stringify(data, null, 2)}</pre>`;
                    } else {
                        resultDiv.innerHTML = `<div class="error">❌ 연결 실패: ${data.error.message}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">❌ 네트워크 오류: ${error.message}</div>`;
                });
        }
        
        function testMainCategories() {
            const resultDiv = document.getElementById('categories-result');
            resultDiv.innerHTML = '조회 중...';
            
            fetch('ajax/get_main_categories.php', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `<div class="success">✅ 조회 성공!</div><pre>${JSON.stringify(data, null, 2)}</pre>`;
                    } else {
                        resultDiv.innerHTML = `<div class="error">❌ 조회 실패: ${data.error.message}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">❌ 네트워크 오류: ${error.message}</div>`;
                });
        }
        
        function testPaperTypes() {
            const categoryId = document.getElementById('category-id').value;
            const resultDiv = document.getElementById('paper-types-result');
            
            if (!categoryId) {
                resultDiv.innerHTML = '<div class="error">❌ 종류 ID를 입력해주세요.</div>';
                return;
            }
            
            resultDiv.innerHTML = '조회 중...';
            
            fetch(`ajax/get_paper_types.php?category_id=${categoryId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `<div class="success">✅ 조회 성공!</div><pre>${JSON.stringify(data, null, 2)}</pre>`;
                    } else {
                        resultDiv.innerHTML = `<div class="error">❌ 조회 실패: ${data.error.message}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">❌ 네트워크 오류: ${error.message}</div>`;
                });
        }
        
        function testPaperSizes() {
            const categoryId = document.getElementById('category-id-2').value;
            const resultDiv = document.getElementById('paper-sizes-result');
            
            if (!categoryId) {
                resultDiv.innerHTML = '<div class="error">❌ 종류 ID를 입력해주세요.</div>';
                return;
            }
            
            resultDiv.innerHTML = '조회 중...';
            
            fetch(`ajax/get_paper_sizes.php?category_id=${categoryId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `<div class="success">✅ 조회 성공!</div><pre>${JSON.stringify(data, null, 2)}</pre>`;
                    } else {
                        resultDiv.innerHTML = `<div class="error">❌ 조회 실패: ${data.error.message}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">❌ 네트워크 오류: ${error.message}</div>`;
                });
        }
        
        function testPriceCalculation() {
            const resultDiv = document.getElementById('price-result');
            resultDiv.innerHTML = '계산 중...';
            
            // 테스트용 파라미터
            const params = new URLSearchParams({
                MY_type: '1',
                MY_Fsd: '2', 
                PN_type: '3',
                MY_amount: '100',
                POtype: '2',
                ordertype: 'total'
            });
            
            fetch(`ajax/calculate_price.php?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `<div class="success">✅ 계산 성공!</div><pre>${JSON.stringify(data, null, 2)}</pre>`;
                    } else {
                        resultDiv.innerHTML = `<div class="error">❌ 계산 실패: ${data.error.message}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">❌ 네트워크 오류: ${error.message}</div>`;
                });
        }
    </script>
</body>
</html>