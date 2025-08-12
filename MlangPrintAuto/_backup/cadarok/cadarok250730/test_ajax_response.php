<!DOCTYPE html>
<html>
<head>
    <title>카다록 AJAX 응답 테스트</title>
</head>
<body>
    <h2>카다록 AJAX 응답 테스트</h2>
    
    <button onclick="testGetSizes()">규격 옵션 테스트</button>
    <button onclick="testGetPapers()">종이종류 옵션 테스트</button>
    <button onclick="testPriceCalculation()">가격 계산 테스트</button>
    
    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>

<script>
function testGetSizes() {
    var result = document.getElementById('result');
    result.innerHTML = "<h3>규격 옵션 테스트</h3>요청 중...";
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            result.innerHTML += "<br><strong>응답 상태:</strong> " + xhr.status;
            result.innerHTML += "<br><strong>원본 응답:</strong><br><pre>" + xhr.responseText + "</pre>";
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    result.innerHTML += "<br><strong>✅ JSON 파싱 성공!</strong>";
                    result.innerHTML += "<br><strong>파싱된 데이터:</strong><br><pre>" + JSON.stringify(response, null, 2) + "</pre>";
                } catch (e) {
                    result.innerHTML += "<br><strong>❌ JSON 파싱 실패:</strong> " + e.message;
                }
            }
        }
    };
    xhr.open("GET", "get_cadarok_sizes.php?category_type=61461", true);
    xhr.send();
}

function testGetPapers() {
    var result = document.getElementById('result');
    result.innerHTML = "<h3>종이종류 옵션 테스트</h3>요청 중...";
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            result.innerHTML += "<br><strong>응답 상태:</strong> " + xhr.status;
            result.innerHTML += "<br><strong>원본 응답:</strong><br><pre>" + xhr.responseText + "</pre>";
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    result.innerHTML += "<br><strong>✅ JSON 파싱 성공!</strong>";
                    result.innerHTML += "<br><strong>파싱된 데이터:</strong><br><pre>" + JSON.stringify(response, null, 2) + "</pre>";
                } catch (e) {
                    result.innerHTML += "<br><strong>❌ JSON 파싱 실패:</strong> " + e.message;
                }
            }
        }
    };
    xhr.open("GET", "get_cadarok_papers.php?category_type=61461", true);
    xhr.send();
}

function testPriceCalculation() {
    var result = document.getElementById('result');
    result.innerHTML = "<h3>가격 계산 테스트</h3>요청 중...";
    
    var params = {
        MY_type: "61461",
        PN_type: "61462", 
        MY_Fsd: "61463",
        MY_amount: "1000",
        ordertype: "total"
    };
    
    result.innerHTML += "<br><strong>요청 파라미터:</strong><br><pre>" + JSON.stringify(params, null, 2) + "</pre>";
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            result.innerHTML += "<br><strong>응답 상태:</strong> " + xhr.status;
            result.innerHTML += "<br><strong>원본 응답:</strong><br><pre>" + xhr.responseText + "</pre>";
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    result.innerHTML += "<br><strong>✅ JSON 파싱 성공!</strong>";
                    result.innerHTML += "<br><strong>파싱된 데이터:</strong><br><pre>" + JSON.stringify(response, null, 2) + "</pre>";
                } catch (e) {
                    result.innerHTML += "<br><strong>❌ JSON 파싱 실패:</strong> " + e.message;
                }
            }
        }
    };
    xhr.open("POST", "price_cal.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(params));
}
</script>

</body>
</html>