<?php
// 간단한 디버그 페이지
include "../../db.php";
$connect = $db;
mysqli_set_charset($connect, "utf8");

$page = "envelope";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 로그 정보
$log_url = preg_replace("/\//", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y");
$log_md = date("md");
$log_ip = "127.0.0.1";
$log_time = time();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>봉투 페이지 디버그</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        select { padding: 10px; margin: 10px; width: 300px; }
        .form-group { margin: 15px 0; }
        .price-display { background: #f0f0f0; padding: 15px; margin: 15px 0; }
        button { padding: 10px 20px; margin: 5px; }
    </style>
</head>
<body>
    <h1>봉투 주문 시스템 디버그</h1>
    
    <form name="envelopeForm">
        <div class="form-group">
            <label>봉투 구분:</label>
            <select name="MY_type" onchange="change_Envelope_Field(this.value)">
                <?php
                $Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC");
                while ($Cate_row = mysqli_fetch_array($Cate_result)) {
                    echo "<option value='" . htmlspecialchars($Cate_row['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($Cate_row['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>봉투 종류:</label>
            <select name="PN_type" onchange="env_calc_re();">
                <option value="">종류를 선택하세요</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>인쇄색상:</label>
            <select name="POtype" onchange="env_calc_ok();">
                <option value='2'>마스터2도</option>
                <option value='1'>마스터1도</option>
                <option value='3'>칼라4도(옵셋)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>수량:</label>
            <select name="MY_amount" onchange="env_calc_ok();">
                <option value='1000'>1000매</option>
                <option value='2000'>2000매</option>
                <option value='3000'>3000매</option>
                <option value='5000'>5000매</option>
                <option value='10000'>10000매</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>주문방법:</label>
            <select name="ordertype" onchange="env_calc_ok();">
                <option value='total'>디자인+인쇄</option>
                <option value='print'>인쇄만 의뢰</option>
            </select>
        </div>
        
        <div class="price-display">
            <div>인쇄비: <span id="print_price">0원</span></div>
            <div>디자인비: <span id="design_price">0원</span></div>
            <div>총 금액: <span id="total_price">0원</span></div>
        </div>
        
        <!-- Hidden Fields -->
        <input type='hidden' name='PriceForm'>
        <input type='hidden' name='DS_PriceForm'>
        <input type='hidden' name='Order_PriceForm'>
        <input type='hidden' name='VAT_PriceForm'>
        <input type='hidden' name='Total_PriceForm'>
        <input type='hidden' name='StyleForm'>
        <input type='hidden' name='SectionForm'>
        <input type='hidden' name='QuantityForm'>
        <input type='hidden' name='DesignForm'>
    </form>
    
    <div id="debug-info" style="background: #ffffcc; padding: 15px; margin: 15px 0;">
        <h3>디버그 정보</h3>
        <div id="debug-log"></div>
    </div>
    
    <script>
        // 디버그 로그 함수
        function debugLog(message) {
            var debugDiv = document.getElementById('debug-log');
            var time = new Date().toLocaleTimeString();
            debugDiv.innerHTML += '[' + time + '] ' + message + '<br>';
            console.log(message);
        }
        
        // 봉투 종류 변경 함수
        function change_Envelope_Field(val) {
            debugLog("change_Envelope_Field 호출됨, 값: " + val);
            
            var form = document.envelopeForm;
            var PN_type = form.PN_type;
            
            if (!val || val === "") {
                PN_type.innerHTML = '<option value="">구분을 먼저 선택하세요</option>';
                return;
            }
            
            PN_type.innerHTML = '<option value="">로딩중...</option>';
            
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = xhr.responseText.trim();
                            debugLog("서버 응답: " + response);
                            
                            var options = JSON.parse(response);
                            PN_type.innerHTML = '<option value="">종류를 선택하세요</option>';
                            
                            if (options && options.length > 0) {
                                for (var i = 0; i < options.length; i++) {
                                    var option = document.createElement('option');
                                    option.value = options[i].no;
                                    option.text = options[i].title;
                                    PN_type.appendChild(option);
                                }
                                debugLog("종류 로딩 완료: " + options.length + "개");
                            } else {
                                debugLog("해당 구분에 종류가 없습니다");
                            }
                        } catch (e) {
                            debugLog("JSON 파싱 오류: " + e.message);
                            PN_type.innerHTML = '<option value="">오류 발생</option>';
                        }
                    } else {
                        debugLog("HTTP 오류: " + xhr.status);
                        PN_type.innerHTML = '<option value="">로딩 실패</option>';
                    }
                }
            };
            
            var url = "get_envelope_types.php?category_type=" + encodeURIComponent(val);
            debugLog("AJAX 요청: " + url);
            xhr.open("GET", url, true);
            xhr.send();
        }
        
        // 가격 계산 함수
        function env_calc_ok() {
            var form = document.envelopeForm;
            
            if (!form.MY_type.value || !form.PN_type.value || !form.MY_amount.value) {
                debugLog("가격 계산에 필요한 값들이 비어있음");
                return;
            }
            
            var url = "price_cal_ajax.php?" +
                     "MY_type=" + encodeURIComponent(form.MY_type.value) +
                     "&PN_type=" + encodeURIComponent(form.PN_type.value) +
                     "&MY_amount=" + encodeURIComponent(form.MY_amount.value) +
                     "&POtype=" + encodeURIComponent(form.POtype.value) +
                     "&ordertype=" + encodeURIComponent(form.ordertype.value);
            
            debugLog("가격 계산 요청: " + url);
            
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            debugLog("가격 계산 응답: " + JSON.stringify(response));
                            
                            if (response.success && response.data) {
                                var data = response.data;
                                document.getElementById('print_price').textContent = data.formatted.Price + "원";
                                document.getElementById('design_price').textContent = data.formatted.DesignMoneyOk + "원";
                                document.getElementById('total_price').textContent = data.formatted.Total_PriceOk + "원";
                                debugLog("가격 계산 완료");
                            } else {
                                debugLog("가격 계산 실패: " + (response.message || "알 수 없는 오류"));
                            }
                        } catch (e) {
                            debugLog("가격 계산 응답 파싱 오류: " + e.message);
                        }
                    } else {
                        debugLog("가격 계산 HTTP 오류: " + xhr.status);
                    }
                }
            };
            xhr.open("GET", url, true);
            xhr.send();
        }
        
        function env_calc_re() {
            setTimeout(env_calc_ok, 100);
        }
        
        // 페이지 로드 시 초기화
        window.onload = function() {
            debugLog("페이지 로드 완료");
            setTimeout(function() {
                var form = document.envelopeForm;
                if (form && form.MY_type && form.MY_type.value) {
                    change_Envelope_Field(form.MY_type.value);
                }
            }, 300);
        };
    </script>
</body>
</html>