<!DOCTYPE html>
<html>
<head>
    <title>카다록 금액보기 직접 테스트</title>
</head>
<body>
    <h2>카다록 금액보기 직접 테스트</h2>
    
    <form name="choiceForm">
        <table border="1">
            <tr>
                <td>구분 (MY_type):</td>
                <td>
                    <select name="MY_type">
                        <option value="69361">카다록A4</option>
                        <option value="69461">카다록A5</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>규격 (MY_Fsd):</td>
                <td>
                    <select name="MY_Fsd">
                        <option value="693">210×297mm</option>
                        <option value="694">148×210mm</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>종이종류 (PN_type):</td>
                <td>
                    <select name="PN_type">
                        <option value="699">모조지</option>
                        <option value="700">아트지</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>수량 (MY_amount):</td>
                <td>
                    <select name="MY_amount">
                        <option value="1000">1000부</option>
                        <option value="2000">2000부</option>
                        <option value="3000">3000부</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>주문방법 (ordertype):</td>
                <td>
                    <select name="ordertype">
                        <option value="print">인쇄만 의뢰</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <br>
        <button type="button" onclick="testCalc()">금액보기 테스트</button>
        
        <br><br>
        <h3>결과:</h3>
        <div id="result"></div>
    </form>
    
    <iframe name="cal" width="100%" height="200" style="border: 1px solid #ccc;"></iframe>
    
    <script>
    function testCalc() {
        var form = document.forms["choiceForm"];
        
        console.log("=== 테스트 calc() 함수 ===");
        console.log("MY_type:", form.MY_type.value);
        console.log("PN_type:", form.PN_type.value);
        console.log("MY_Fsd:", form.MY_Fsd.value);
        console.log("MY_amount:", form.MY_amount.value);
        console.log("ordertype:", form.ordertype.value);
        
        // 필수 값들이 모두 있는지 확인
        if (!form.MY_type.value || !form.PN_type.value || !form.MY_Fsd.value || !form.MY_amount.value) {
            alert("모든 옵션을 선택해주세요.");
            return;
        }
        
        var url = 'price_cal.php?ordertype=' + form.ordertype.value + 
                  '&MY_type=' + form.MY_type.value + 
                  '&PN_type=' + form.PN_type.value + 
                  '&MY_Fsd=' + form.MY_Fsd.value + 
                  '&MY_amount=' + form.MY_amount.value;
        
        console.log("가격 계산 URL:", url);
        document.getElementById('result').innerHTML = "URL: " + url;
        
        // iframe으로 로드
        if (window.cal && window.cal.document) {
            window.cal.document.location.href = url;
        } else {
            window.open(url, 'cal', 'width=600,height=400');
        }
    }
    </script>
</body>
</html>