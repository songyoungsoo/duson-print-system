<!DOCTYPE html>
<html>
<head>
    <title>쿠폰 시스템 실시간 디버깅</title>
    <style>
        .debug-box { 
            border: 1px solid #ccc; 
            padding: 10px; 
            margin: 10px 0; 
            background: #f9f9f9; 
        }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h2>쿠폰 시스템 실시간 디버깅</h2>
    
    <div class="debug-box">
        <h3>1. 실제 쿠폰 시스템 페이지 테스트</h3>
        <p>이 버튼들을 클릭해서 실제 쿠폰 시스템 페이지의 상태를 확인하세요:</p>
        <button onclick="testParentPage()">상위 페이지 폼 테스트</button>
        <button onclick="testPriceFields()">가격 필드 테스트</button>
        <button onclick="testManualPriceSet()">수동 가격 설정 테스트</button>
        <button onclick="testCalcOk()">calc_ok() 함수 테스트</button>
    </div>
    
    <div class="debug-box">
        <h3>2. 테스트 결과</h3>
        <div id="testResult"></div>
    </div>
    
    <div class="debug-box">
        <h3>3. 실시간 AJAX 모니터링</h3>
        <button onclick="startAjaxMonitoring()">AJAX 요청 모니터링 시작</button>
        <div id="ajaxResult"></div>
    </div>

<script>
function log(message, type = 'info') {
    var result = document.getElementById('testResult');
    var className = type === 'success' ? 'success' : type === 'error' ? 'error' : type === 'warning' ? 'warning' : '';
    result.innerHTML += '<div class="' + className + '">' + new Date().toLocaleTimeString() + ': ' + message + '</div>';
}

function testParentPage() {
    log("=== 상위 페이지 폼 테스트 시작 ===");
    
    try {
        // 상위 페이지 접근 시도
        if (window.parent && window.parent !== window) {
            var parentForm = window.parent.document.forms["choiceForm"];
            if (parentForm) {
                log("✅ 상위 페이지에서 choiceForm 찾음", 'success');
                
                // 옵션 필드들 확인
                var optionFields = ['MY_type', 'MY_amount', 'POtype', 'PN_type', 'ordertype'];
                optionFields.forEach(function(fieldName) {
                    var field = parentForm[fieldName];
                    if (field) {
                        log("✅ " + fieldName + " 필드 존재, 값: " + field.value, 'success');
                    } else {
                        log("❌ " + fieldName + " 필드 없음", 'error');
                    }
                });
                
            } else {
                log("❌ 상위 페이지에서 choiceForm을 찾을 수 없음", 'error');
            }
        } else {
            log("❌ 상위 페이지에 접근할 수 없음", 'error');
        }
    } catch (e) {
        log("❌ 상위 페이지 테스트 오류: " + e.message, 'error');
    }
}

function testPriceFields() {
    log("=== 가격 필드 테스트 시작 ===");
    
    try {
        var parentForm = window.parent.document.forms["choiceForm"];
        if (parentForm) {
            var priceFields = ['Price', 'DS_Price', 'Order_Price'];
            priceFields.forEach(function(fieldName) {
                var field = parentForm[fieldName];
                if (field) {
                    log("✅ " + fieldName + " 필드 존재", 'success');
                    log("   - 타입: " + field.type, 'info');
                    log("   - 현재 값: '" + field.value + "'", 'info');
                    log("   - readonly: " + field.readOnly, 'info');
                    log("   - 스타일: " + field.style.cssText, 'info');
                } else {
                    log("❌ " + fieldName + " 필드 없음", 'error');
                }
            });
            
            // 숨겨진 필드들도 확인
            var hiddenFields = ['PriceForm', 'DS_PriceForm', 'Order_PriceForm'];
            hiddenFields.forEach(function(fieldName) {
                var field = parentForm[fieldName];
                if (field) {
                    log("✅ " + fieldName + " 숨겨진 필드 존재, 값: '" + field.value + "'", 'success');
                } else {
                    log("❌ " + fieldName + " 숨겨진 필드 없음", 'error');
                }
            });
        }
    } catch (e) {
        log("❌ 가격 필드 테스트 오류: " + e.message, 'error');
    }
}

function testManualPriceSet() {
    log("=== 수동 가격 설정 테스트 시작 ===");
    
    try {
        var parentForm = window.parent.document.forms["choiceForm"];
        if (parentForm) {
            // 테스트 가격 데이터
            var testPrices = {
                Price: "70,000",
                DS_Price: "15,000", 
                Order_Price: "85,000"
            };
            
            Object.keys(testPrices).forEach(function(fieldName) {
                var field = parentForm[fieldName];
                if (field) {
                    var oldValue = field.value;
                    field.value = testPrices[fieldName];
                    log("✅ " + fieldName + " 수동 설정: '" + oldValue + "' → '" + field.value + "'", 'success');
                } else {
                    log("❌ " + fieldName + " 필드가 없어서 설정 불가", 'error');
                }
            });
        }
    } catch (e) {
        log("❌ 수동 가격 설정 오류: " + e.message, 'error');
    }
}

function testCalcOk() {
    log("=== calc_ok() 함수 테스트 시작 ===");
    
    try {
        if (window.parent && window.parent.calc_ok) {
            log("✅ 상위 페이지에서 calc_ok 함수 찾음", 'success');
            log("calc_ok() 함수 실행 중...", 'info');
            window.parent.calc_ok();
            log("✅ calc_ok() 함수 실행 완료", 'success');
        } else {
            log("❌ 상위 페이지에서 calc_ok 함수를 찾을 수 없음", 'error');
        }
    } catch (e) {
        log("❌ calc_ok() 함수 테스트 오류: " + e.message, 'error');
    }
}

function startAjaxMonitoring() {
    log("=== AJAX 모니터링 시작 ===");
    
    // XMLHttpRequest 모니터링
    var originalXHR = window.parent.XMLHttpRequest;
    if (originalXHR) {
        window.parent.XMLHttpRequest = function() {
            var xhr = new originalXHR();
            var originalOpen = xhr.open;
            var originalSend = xhr.send;
            
            xhr.open = function(method, url, async) {
                log("🌐 AJAX 요청 시작: " + method + " " + url, 'info');
                return originalOpen.apply(this, arguments);
            };
            
            xhr.send = function(data) {
                log("📤 AJAX 데이터 전송: " + (data || '없음'), 'info');
                
                var originalOnReadyStateChange = xhr.onreadystatechange;
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        log("📥 AJAX 응답 받음 (상태: " + xhr.status + ")", xhr.status === 200 ? 'success' : 'error');
                        log("📄 응답 내용: " + xhr.responseText.substring(0, 200) + "...", 'info');
                    }
                    if (originalOnReadyStateChange) {
                        return originalOnReadyStateChange.apply(this, arguments);
                    }
                };
                
                return originalSend.apply(this, arguments);
            };
            
            return xhr;
        };
        
        log("✅ AJAX 모니터링 설정 완료", 'success');
    } else {
        log("❌ XMLHttpRequest를 찾을 수 없음", 'error');
    }
}

// 페이지 로드 시 자동 테스트
window.onload = function() {
    setTimeout(function() {
        testParentPage();
        testPriceFields();
    }, 1000);
};
</script>

</body>
</html>