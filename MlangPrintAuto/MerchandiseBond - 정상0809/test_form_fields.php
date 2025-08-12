<!DOCTYPE html>
<html>
<head>
    <title>쿠폰 시스템 폼 필드 테스트</title>
</head>
<body>
    <h2>쿠폰 시스템 폼 필드 실시간 테스트</h2>
    
    <!-- 실제 쿠폰 시스템과 동일한 폼 구조 -->
    <form name='choiceForm' method='post'>
        <h3>가격 표시 필드들</h3>
        <table border="1">
            <tr>
                <td>인쇄비:</td>
                <td><input type="text" name='Price' readonly style='width:150px; font-weight:bold; text-align:right;'></td>
            </tr>
            <tr>
                <td>디자인비:</td>
                <td><input type="text" name='DS_Price' readonly style='width:150px; font-weight:bold; text-align:right;'></td>
            </tr>
            <tr>
                <td>합계:</td>
                <td><input type="text" name='Order_Price' readonly style='width:150px; font-weight:bold; text-align:right;'></td>
            </tr>
        </table>
        
        <h3>숨겨진 필드들</h3>
        <input type='hidden' name='PriceForm'>
        <input type='hidden' name='DS_PriceForm'>
        <input type='hidden' name='Order_PriceForm'>
        <input type='hidden' name='VAT_PriceForm'>
        <input type='hidden' name='Total_PriceForm'>
        <input type='hidden' name='StyleForm'>
        <input type='hidden' name='SectionForm'>
        <input type='hidden' name='QuantityForm'>
        <input type='hidden' name='DesignForm'>
        
        <h3>옵션 필드들</h3>
        <select name="MY_type">
            <option value="61461">상품권 A</option>
            <option value="61462">상품권 B</option>
        </select>
        
        <select name="MY_amount">
            <option value="1000">1000매</option>
            <option value="2000">2000매</option>
        </select>
        
        <select name="POtype">
            <option value="1">단면</option>
            <option value="2">양면</option>
        </select>
        
        <select name="PN_type">
            <option value="61461">후가공 없음</option>
        </select>
        
        <select name="ordertype">
            <option value="total">디자인+인쇄</option>
            <option value="print">인쇄만 의뢰</option>
        </select>
        
        <br><br>
        <button type="button" onclick="testFormFields()">폼 필드 테스트</button>
        <button type="button" onclick="testPriceCalculation()">가격 계산 테스트</button>
    </form>
    
    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>

<script>
function testFormFields() {
    var result = document.getElementById('result');
    var form = document.forms["choiceForm"];
    
    result.innerHTML = "<h3>폼 필드 존재 여부 확인</h3>";
    
    if (!form) {
        result.innerHTML += "❌ choiceForm을 찾을 수 없습니다!<br>";
        return;
    }
    
    result.innerHTML += "✅ choiceForm 찾음<br><br>";
    
    // 가격 표시 필드들 확인
    var priceFields = ['Price', 'DS_Price', 'Order_Price'];
    result.innerHTML += "<strong>가격 표시 필드들:</strong><br>";
    
    priceFields.forEach(function(fieldName) {
        var field = form[fieldName];
        if (field) {
            result.innerHTML += "✅ " + fieldName + " 필드 존재 (타입: " + field.type + ")<br>";
        } else {
            result.innerHTML += "❌ " + fieldName + " 필드 없음<br>";
        }
    });
    
    // 숨겨진 필드들 확인
    var hiddenFields = ['PriceForm', 'DS_PriceForm', 'Order_PriceForm', 'VAT_PriceForm', 'Total_PriceForm'];
    result.innerHTML += "<br><strong>숨겨진 필드들:</strong><br>";
    
    hiddenFields.forEach(function(fieldName) {
        var field = form[fieldName];
        if (field) {
            result.innerHTML += "✅ " + fieldName + " 필드 존재 (타입: " + field.type + ")<br>";
        } else {
            result.innerHTML += "❌ " + fieldName + " 필드 없음<br>";
        }
    });
    
    // 옵션 필드들 확인
    var optionFields = ['MY_type', 'MY_amount', 'POtype', 'PN_type', 'ordertype'];
    result.innerHTML += "<br><strong>옵션 필드들:</strong><br>";
    
    optionFields.forEach(function(fieldName) {
        var field = form[fieldName];
        if (field) {
            result.innerHTML += "✅ " + fieldName + " 필드 존재 (값: " + field.value + ")<br>";
        } else {
            result.innerHTML += "❌ " + fieldName + " 필드 없음<br>";
        }
    });
}

function testPriceCalculation() {
    var result = document.getElementById('result');
    var form = document.forms["choiceForm"];
    
    result.innerHTML = "<h3>가격 계산 테스트</h3>";
    result.innerHTML += "AJAX 요청 시작...<br>";
    
    // 실제 쿠폰 시스템과 동일한 AJAX 요청
    var params = {
        MY_type: form.MY_type.value,
        PN_type: form.PN_type.value,
        MY_amount: form.MY_amount.value,
        ordertype: form.ordertype.value,
        POtype: form.POtype.value
    };
    
    result.innerHTML += "요청 파라미터: " + JSON.stringify(params) + "<br><br>";
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            result.innerHTML += "AJAX 응답 상태: " + xhr.status + "<br>";
            result.innerHTML += "AJAX 응답 내용: " + xhr.responseText + "<br><br>";
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        result.innerHTML += "<strong>✅ 가격 계산 성공!</strong><br>";
                        result.innerHTML += "응답 데이터: " + JSON.stringify(response.data, null, 2) + "<br><br>";
                        
                        // 실제 필드 업데이트 시도
                        result.innerHTML += "<strong>필드 업데이트 시도:</strong><br>";
                        
                        if (form.Price) {
                            form.Price.value = response.data.Price;
                            result.innerHTML += "✅ Price 업데이트: " + response.data.Price + "<br>";
                        } else {
                            result.innerHTML += "❌ Price 필드를 찾을 수 없음<br>";
                        }
                        
                        if (form.DS_Price) {
                            form.DS_Price.value = response.data.DS_Price;
                            result.innerHTML += "✅ DS_Price 업데이트: " + response.data.DS_Price + "<br>";
                        } else {
                            result.innerHTML += "❌ DS_Price 필드를 찾을 수 없음<br>";
                        }
                        
                        if (form.Order_Price) {
                            form.Order_Price.value = response.data.Order_Price;
                            result.innerHTML += "✅ Order_Price 업데이트: " + response.data.Order_Price + "<br>";
                        } else {
                            result.innerHTML += "❌ Order_Price 필드를 찾을 수 없음<br>";
                        }
                        
                    } else {
                        result.innerHTML += "❌ 가격 계산 실패: " + response.message + "<br>";
                    }
                } catch (e) {
                    result.innerHTML += "❌ 응답 파싱 오류: " + e.message + "<br>";
                }
            } else {
                result.innerHTML += "❌ AJAX 요청 실패<br>";
            }
        }
    };
    
    xhr.open("POST", "price_cal_ajax.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(params));
}
</script>

</body>
</html>