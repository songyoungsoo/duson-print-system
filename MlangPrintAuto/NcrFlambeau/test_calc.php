<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>양식지 가격 계산 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 10px 0; }
        label { display: inline-block; width: 100px; }
        select, input { padding: 5px; margin: 5px; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        .result { margin: 20px 0; padding: 15px; background: #f0f0f0; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>🧪 양식지 가격 계산 테스트</h2>
    
    <form id="testForm">
        <div class="form-group">
            <label>구분:</label>
            <select name="MY_type" id="MY_type" required>
                <option value="">선택하세요</option>
                <option value="475">양식(100매철)</option>
                <option value="476">NCR 2매(100매철)</option>
                <option value="477">NCR 3매(150매철)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>규격:</label>
            <select name="MY_Fsd" id="MY_Fsd" required>
                <option value="">먼저 구분을 선택하세요</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>색상:</label>
            <select name="PN_type" id="PN_type" required>
                <option value="">먼저 구분을 선택하세요</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>수량:</label>
            <select name="MY_amount" id="MY_amount" required>
                <option value="">수량을 선택하세요</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>편집디자인:</label>
            <select name="ordertype" required>
                <option value="total">디자인+인쇄</option>
                <option value="print">인쇄만 의뢰</option>
            </select>
        </div>
        
        <button type="button" onclick="calculatePrice()">💰 가격 계산</button>
    </form>
    
    <div id="result" class="result" style="display: none;">
        <h3>계산 결과:</h3>
        <div id="resultContent"></div>
    </div>

    <script>
    // 구분 변경 시 규격 로드
    document.getElementById('MY_type').addEventListener('change', function() {
        const style = this.value;
        if (!style) return;
        
        fetch(`get_sizes.php?style=${style}`)
        .then(response => response.json())
        .then(response => {
            const sizeSelect = document.getElementById('MY_Fsd');
            sizeSelect.innerHTML = '<option value="">규격을 선택하세요</option>';
            
            if (response.success && response.data) {
                response.data.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.no;
                    optionElement.textContent = option.title;
                    sizeSelect.appendChild(optionElement);
                });
            }
        })
        .catch(error => console.error('규격 로드 오류:', error));
        
        // 색상도 로드
        fetch(`get_colors.php?style=${style}`)
        .then(response => response.json())
        .then(response => {
            const colorSelect = document.getElementById('PN_type');
            colorSelect.innerHTML = '<option value="">색상을 선택하세요</option>';
            
            if (response.success && response.data) {
                response.data.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.no;
                    optionElement.textContent = option.title;
                    colorSelect.appendChild(optionElement);
                });
            }
        })
        .catch(error => console.error('색상 로드 오류:', error));
    });
    
    // 규격과 색상 선택 시 수량 로드
    function loadQuantities() {
        const style = document.getElementById('MY_type').value;
        const section = document.getElementById('MY_Fsd').value;
        const treeSelect = document.getElementById('PN_type').value;
        
        if (!style || !section || !treeSelect) return;
        
        fetch(`get_quantities.php?style=${style}&Section=${section}&TreeSelect=${treeSelect}`)
        .then(response => response.json())
        .then(response => {
            const quantitySelect = document.getElementById('MY_amount');
            quantitySelect.innerHTML = '<option value="">수량을 선택하세요</option>';
            
            if (response.success && response.data) {
                response.data.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.text;
                    quantitySelect.appendChild(optionElement);
                });
            }
        })
        .catch(error => console.error('수량 로드 오류:', error));
    }
    
    document.getElementById('MY_Fsd').addEventListener('change', loadQuantities);
    document.getElementById('PN_type').addEventListener('change', loadQuantities);
    
    // 가격 계산
    function calculatePrice() {
        const form = document.getElementById('testForm');
        const formData = new FormData(form);
        
        // 필수 필드 검증
        if (!formData.get('MY_type') || !formData.get('MY_Fsd') || !formData.get('PN_type') || 
            !formData.get('MY_amount') || !formData.get('ordertype')) {
            alert('모든 옵션을 선택해주세요.');
            return;
        }
        
        const params = new URLSearchParams({
            MY_type: formData.get('MY_type'),
            MY_Fsd: formData.get('MY_Fsd'),
            PN_type: formData.get('PN_type'),
            MY_amount: formData.get('MY_amount'),
            ordertype: formData.get('ordertype')
        });
        
        console.log('가격 계산 요청:', params.toString());
        
        fetch('calculate_price_ajax.php?' + params.toString())
        .then(response => response.json())
        .then(response => {
            console.log('가격 계산 응답:', response);
            
            const resultDiv = document.getElementById('result');
            const contentDiv = document.getElementById('resultContent');
            
            if (response.success) {
                const data = response.data;
                contentDiv.innerHTML = `
                    <p><strong>기본 가격:</strong> ${Number(data.base_price).toLocaleString()}원</p>
                    <p><strong>디자인 비용:</strong> ${Number(data.design_price).toLocaleString()}원</p>
                    <p><strong>총 가격:</strong> ${Number(data.total_price).toLocaleString()}원</p>
                    <p><strong>부가세 포함:</strong> ${Number(data.total_with_vat).toLocaleString()}원</p>
                `;
                resultDiv.style.display = 'block';
            } else {
                contentDiv.innerHTML = `<p style="color: red;">오류: ${response.message}</p>`;
                resultDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('가격 계산 오류:', error);
            const resultDiv = document.getElementById('result');
            const contentDiv = document.getElementById('resultContent');
            contentDiv.innerHTML = `<p style="color: red;">네트워크 오류: ${error.message}</p>`;
            resultDiv.style.display = 'block';
        });
    }
    </script>
</body>
</html>