<!DOCTYPE html>
<html>
<head>
    <title>카다록 AJAX 상세 디버깅</title>
</head>
<body>
    <h2>카다록 AJAX 상세 디버깅</h2>
    
    <h3>1. 개별 AJAX 파일 테스트</h3>
    <button onclick="testPriceCalAjax()">price_cal.php 직접 테스트</button>
    <button onclick="testGetSizesAjax()">get_cadarok_sizes.php 직접 테스트</button>
    <button onclick="testGetPapersAjax()">get_cadarok_papers.php 직접 테스트</button>
    
    <h3>2. 실제 카다록 시스템 시뮬레이션</h3>
    <button onclick="simulateRealSystem()">실제 시스템과 동일한 요청</button>
    
    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; white-space: pre-wrap;"></div>

<script>
function logResult(message) {
    var result = document.getElementById('result');
    result.innerHTML += new Date().toLocaleTimeString() + ": " + message + "\n";
}

function testPriceCalAjax() {
    var result = document.getElementById('result');
    result.innerHTML = "=== price_cal.php 테스트 ===\n";
    
    var params = {
        MY_type: "61461",
        PN_type: "61462", 
        MY_Fsd: "61463",
        MY_amount: "1000",
        ordertype: "total"
    };
    
    logResult("요청 파라미터: " + JSON.stringify(params));
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            logResult("응답 상태: " + xhr.status);
            logResult("응답 헤더: " + xhr.getAllResponseHeaders());
            logResult("원본 응답 (처음 200자): " + xhr.responseText.substring(0, 200));
            
            // 응답의 첫 번째 문자 확인
            if (xhr.responseText.length > 0) {
                var firstChar = xhr.responseText.charAt(0);
                logResult("첫 번째 문자: '" + firstChar + "' (코드: " + firstChar.charCodeAt(0) + ")");
                
                if (firstChar === '<') {
                    logResult("❌ HTML 출력 감지! JSON 파싱 실패 예상");
                } else if (firstChar === '{') {
                    logResult("✅ JSON 형식으로 시작");
                } else {
                    logResult("⚠️ 예상치 못한 첫 문자");
                }
            }
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    logResult("✅ JSON 파싱 성공!");
                    logResult("파싱된 데이터: " + JSON.stringify(response, null, 2));
                } catch (e) {
                    logResult("❌ JSON 파싱 실패: " + e.message);
                    logResult("전체 응답: " + xhr.responseText);
                }
            }
        }
    };
    xhr.open("POST", "price_cal.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(params));
}

function testGetSizesAjax() {
    var result = document.getElementById('result');
    result.innerHTML = "=== get_cadarok_sizes.php 테스트 ===\n";
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            logResult("응답 상태: " + xhr.status);
            logResult("원본 응답 (처음 200자): " + xhr.responseText.substring(0, 200));
            
            if (xhr.responseText.length > 0) {
                var firstChar = xhr.responseText.charAt(0);
                logResult("첫 번째 문자: '" + firstChar + "' (코드: " + firstChar.charCodeAt(0) + ")");
            }
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    logResult("✅ JSON 파싱 성공!");
                    logResult("파싱된 데이터: " + JSON.stringify(response, null, 2));
                } catch (e) {
                    logResult("❌ JSON 파싱 실패: " + e.message);
                }
            }
        }
    };
    xhr.open("GET", "get_cadarok_sizes.php?category_type=61461", true);
    xhr.send();
}

function testGetPapersAjax() {
    var result = document.getElementById('result');
    result.innerHTML = "=== get_cadarok_papers.php 테스트 ===\n";
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            logResult("응답 상태: " + xhr.status);
            logResult("원본 응답 (처음 200자): " + xhr.responseText.substring(0, 200));
            
            if (xhr.responseText.length > 0) {
                var firstChar = xhr.responseText.charAt(0);
                logResult("첫 번째 문자: '" + firstChar + "' (코드: " + firstChar.charCodeAt(0) + ")");
            }
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    logResult("✅ JSON 파싱 성공!");
                    logResult("파싱된 데이터: " + JSON.stringify(response, null, 2));
                } catch (e) {
                    logResult("❌ JSON 파싱 실패: " + e.message);
                }
            }
        }
    };
    xhr.open("GET", "get_cadarok_papers.php?category_type=61461", true);
    xhr.send();
}

function simulateRealSystem() {
    var result = document.getElementById('result');
    result.innerHTML = "=== 실제 시스템 시뮬레이션 ===\n";
    
    // 1단계: 규격 옵션 로드
    logResult("1단계: 규격 옵션 로드 중...");
    var xhr1 = new XMLHttpRequest();
    xhr1.onreadystatechange = function() {
        if (xhr1.readyState === 4) {
            if (xhr1.status === 200) {
                try {
                    var sizes = JSON.parse(xhr1.responseText);
                    logResult("✅ 규격 옵션 로드 성공: " + sizes.length + "개");
                    
                    // 2단계: 종이종류 옵션 로드
                    loadPapers();
                } catch (e) {
                    logResult("❌ 규격 옵션 JSON 파싱 실패: " + e.message);
                    logResult("규격 응답: " + xhr1.responseText.substring(0, 100));
                }
            } else {
                logResult("❌ 규격 옵션 로드 실패: " + xhr1.status);
            }
        }
    };
    xhr1.open("GET", "get_cadarok_sizes.php?category_type=61461", true);
    xhr1.send();
    
    function loadPapers() {
        logResult("2단계: 종이종류 옵션 로드 중...");
        var xhr2 = new XMLHttpRequest();
        xhr2.onreadystatechange = function() {
            if (xhr2.readyState === 4) {
                if (xhr2.status === 200) {
                    try {
                        var papers = JSON.parse(xhr2.responseText);
                        logResult("✅ 종이종류 옵션 로드 성공: " + papers.length + "개");
                        
                        // 3단계: 가격 계산
                        calculatePrice();
                    } catch (e) {
                        logResult("❌ 종이종류 옵션 JSON 파싱 실패: " + e.message);
                        logResult("종이종류 응답: " + xhr2.responseText.substring(0, 100));
                    }
                } else {
                    logResult("❌ 종이종류 옵션 로드 실패: " + xhr2.status);
                }
            }
        };
        xhr2.open("GET", "get_cadarok_papers.php?category_type=61461", true);
        xhr2.send();
    }
    
    function calculatePrice() {
        logResult("3단계: 가격 계산 중...");
        var params = {
            MY_type: "61461",
            PN_type: "61462", 
            MY_Fsd: "61463",
            MY_amount: "1000",
            ordertype: "total"
        };
        
        var xhr3 = new XMLHttpRequest();
        xhr3.onreadystatechange = function() {
            if (xhr3.readyState === 4) {
                if (xhr3.status === 200) {
                    try {
                        var priceData = JSON.parse(xhr3.responseText);
                        logResult("✅ 가격 계산 성공!");
                        logResult("가격 데이터: " + JSON.stringify(priceData, null, 2));
                    } catch (e) {
                        logResult("❌ 가격 계산 JSON 파싱 실패: " + e.message);
                        logResult("가격 응답: " + xhr3.responseText.substring(0, 100));
                    }
                } else {
                    logResult("❌ 가격 계산 실패: " + xhr3.status);
                }
            }
        };
        xhr3.open("POST", "price_cal.php", true);
        xhr3.setRequestHeader("Content-Type", "application/json");
        xhr3.send(JSON.stringify(params));
    }
}
</script>

</body>
</html>