<?php
  // 불필요한 <!DOCTYPE>, <html>, <head>, <body> 태그 제거
  // 세션이나 include 경로 조정
?>
<div class="calculator-fragment">
  <!-- 실제 견적 계산 폼과 스크립트만 남깁니다 -->
  <form id="littleCalc">
    <label>수량: <input type="number" name="qty"></label><br/>
    <label>단가: <input type="number" name="price"></label><br/>
    <button type="button" id="littleCalcBtn">계산</button>
  </form>
  <p>총 금액: <span id="littleTotal">0</span>원</p>
  <script>
    document.getElementById('littleCalcBtn').addEventListener('click', () => {
      const f = document.getElementById('littleCalc');
      const total = (Number(f.qty.value)||0)*(Number(f.price.value)||0);
      document.getElementById('littleTotal').textContent = total.toLocaleString();
    });
  </script>