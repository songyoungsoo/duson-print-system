<?php
// 카다록 가격 계산 디버깅
echo "<h2>🔍 카다록 가격 계산 디버깅</h2>";

// 1. 직접 price_cal.php 호출 테스트
echo "<h3>1. price_cal.php 직접 호출 테스트</h3>";

// 테스트 파라미터
$test_params = [
    'ordertype' => 'print',
    'MY_type' => '69361',
    'PN_type' => '69961', 
    'MY_Fsd' => '69361',
    'MY_amount' => '1000'
];

$url = "price_cal.php?" . http_build_query($test_params);
echo "<p><strong>테스트 URL:</strong> <a href='$url' target='_blank'>$url</a></p>";

// 2. iframe 테스트
echo "<h3>2. iframe 방식 테스트</h3>";
echo "<iframe src='$url' width='100%' height='200' style='border: 1px solid #ccc;'></iframe>";

// 3. JavaScript 함수 테스트
echo "<h3>3. JavaScript 함수 테스트</h3>";
?>

<script>
// 테스트용 폼 생성
document.write('<form name="testForm">');
document.write('<input type="hidden" name="ordertype" value="print">');
document.write('<input type="hidden" name="MY_type" value="69361">');
document.write('<input type="hidden" name="PN_type" value="69961">');
document.write('<input type="hidden" name="MY_Fsd" value="69361">');
document.write('<input type="hidden" name="MY_amount" value="1000">');
document.write('<input type="text" name="Price" placeholder="인쇄비" readonly>');
document.write('<input type="text" name="DS_Price" placeholder="디자인비" readonly>');
document.write('<input type="text" name="Order_Price" placeholder="총액" readonly>');
document.write('</form>');

// calc_ok 함수 복사 (실제 페이지에서 사용하는 것과 동일)
function test_calc_ok() {
  var form = document.forms["testForm"];
  
  console.log("=== 카다록 가격 계산 테스트 시작 ===");
  
  // 필수 값들이 모두 있는지 확인
  if (!form.MY_type.value || !form.PN_type.value || !form.MY_Fsd.value || !form.MY_amount.value) {
    console.log("❌ 가격 계산에 필요한 값들이 비어있음");
    return;
  }
  
  // 원래 카다록 시스템 방식: GET 방식으로 iframe에 로드
  var url = 'price_cal.php?ordertype=' + form.ordertype.value + 
            '&MY_type=' + form.MY_type.value + 
            '&PN_type=' + form.PN_type.value + 
            '&MY_Fsd=' + form.MY_Fsd.value + 
            '&MY_amount=' + form.MY_amount.value;
  
  console.log("✅ 카다록 가격 계산 URL:", url);
  
  // AJAX로 직접 테스트
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      console.log("AJAX 응답 상태:", xhr.status);
      if (xhr.status === 200) {
        console.log("✅ AJAX 응답 성공");
        console.log("응답 내용:", xhr.responseText);
        
        // 응답을 iframe에 삽입해서 JavaScript 실행
        var testIframe = document.getElementById('testIframe');
        if (testIframe) {
          testIframe.contentDocument.open();
          testIframe.contentDocument.write(xhr.responseText);
          testIframe.contentDocument.close();
        }
      } else {
        console.error("❌ AJAX 요청 실패:", xhr.status, xhr.statusText);
      }
    }
  };
  xhr.open("GET", url, true);
  xhr.send();
}

// 테스트 실행
console.log("🔄 카다록 가격 계산 테스트 실행");
test_calc_ok();
</script>

<h3>4. 테스트 결과 확인</h3>
<p>브라우저 개발자 도구(F12) → Console 탭에서 로그를 확인하세요.</p>

<h3>5. iframe 응답 확인</h3>
<iframe id="testIframe" width="100%" height="200" style="border: 1px solid #ccc;"></iframe>

<h3>6. 테스트 폼</h3>
<button onclick="test_calc_ok()">가격 계산 테스트 실행</button>

<?php
// 4. 실제 페이지 JavaScript 함수 확인
echo "<h3>7. 실제 페이지 함수 확인</h3>";
echo "<p>실제 index.php에서 사용하는 함수들이 제대로 정의되어 있는지 확인:</p>";
?>

<script>
// 실제 페이지에서 사용하는 함수들 확인
setTimeout(function() {
  console.log("=== 실제 페이지 함수 확인 ===");
  
  // calc 함수 확인
  if (typeof calc === 'function') {
    console.log("✅ calc 함수 정의됨");
  } else {
    console.log("❌ calc 함수 정의되지 않음");
  }
  
  // calc_ok 함수 확인
  if (typeof calc_ok === 'function') {
    console.log("✅ calc_ok 함수 정의됨");
  } else {
    console.log("❌ calc_ok 함수 정의되지 않음");
  }
  
  // change_Field 함수 확인
  if (typeof change_Field === 'function') {
    console.log("✅ change_Field 함수 정의됨");
  } else {
    console.log("❌ change_Field 함수 정의되지 않음");
  }
  
  // choiceForm 확인
  if (document.forms["choiceForm"]) {
    console.log("✅ choiceForm 폼 존재");
    var form = document.forms["choiceForm"];
    console.log("MY_type 값:", form.MY_type ? form.MY_type.value : "필드 없음");
    console.log("PN_type 값:", form.PN_type ? form.PN_type.value : "필드 없음");
    console.log("MY_Fsd 값:", form.MY_Fsd ? form.MY_Fsd.value : "필드 없음");
    console.log("MY_amount 값:", form.MY_amount ? form.MY_amount.value : "필드 없음");
  } else {
    console.log("❌ choiceForm 폼 존재하지 않음");
  }
}, 1000);
</script>
