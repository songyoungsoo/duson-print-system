<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>두손기획인쇄 - 스티커 견적안내</title>
  <style>
    body {
      margin: 0;
      font-family: "Noto Sans KR", sans-serif;
      background: #f5f7fa;
    }

    /* Header */
    header {
      background: #2c3e50;
      color: #fff;
    }
    .header-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header h1 {
      font-size: 18px;
      margin: 0;
    }
    header nav a {
      color: #fff;
      margin-left: 20px;
      text-decoration: none;
      font-size: 14px;
    }

    /* Menu buttons */
    .menu-bar {
      background: #37495a;
      padding: 10px 0;
    }
    .menu-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: center;
      gap: 10px;
    }
    .menu-bar button {
      background: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      cursor: pointer;
      font-weight: bold;
    }

    /* Main content */
    .container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      gap: 20px;
      padding: 30px 20px;
    }

    /* 반응형: 화면이 좁아지면 세로 배치 */
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        padding: 15px 20px;
      }
      .header-inner,
      .menu-inner,
      .footer-inner {
        padding: 0 15px;
      }
    }
    .samples {
      flex: 1;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
    }
    .calculator {
      flex: 1;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
    }
    .calculator h2 {
      margin-top: 0;
      color: #6c2ca7;
    }
    .calculator label {
      display: block;
      margin: 10px 0 4px;
    }
    .calculator input, .calculator select {
      width: 100%;
      padding: 8px;
      margin-bottom: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
    }
    .price-box {
      padding: 15px;
      background: #eafaf1;
      border: 2px solid #27ae60;
      border-radius: 8px;
      margin: 15px 0;
      font-size: 18px;
      color: #27ae60;
    }
    .sub-price {
      font-size: 14px;
      color: #e74c3c;
      margin-top: 5px;
    }
    .order-btn {
      display: block;
      width: 100%;
      padding: 12px;
      background: #27ae60;
      color: white;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 120px;
      right: 20px;
      width: 220px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
    }
    .sidebar img {
      width: 100%;
    }

    /* Footer */
    footer {
      background: #2c3e50;
      color: #fff;
      margin-top: 40px;
      padding: 30px 0;
    }
    .footer-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      font-size: 14px;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="header-inner">
      <h1>두손기획인쇄</h1>
      <nav>
        <a href="#">로그인</a>
        <a href="#">회원가입</a>
        <a href="#">고객센터</a>
      </nav>
    </div>
  </header>

  <!-- Menu -->
  <div class="menu-bar">
    <div class="menu-inner">
      <button>스티커</button>
      <button>전단지</button>
      <button>명함</button>
      <button>봉투</button>
      <button>카다로그</button>
    </div>
  </div>

  <!-- Main content -->
  <div class="container">
    <div class="samples">
      <h2>샘플 스티커</h2>
      <img src="https://via.placeholder.com/400x250" alt="샘플 이미지">
    </div>

    <div class="calculator">
      <h2>스티커 견적안내</h2>

      <label>재질</label>
      <select id="material">
        <option value="1">아트지유광</option>
        <option value="1.1">아트지무광 (+10%)</option>
        <option value="1.2">방수유광 (+20%)</option>
      </select>

      <label>가로 (mm)</label>
      <input type="number" id="width" value="100" min="10">

      <label>세로 (mm)</label>
      <input type="number" id="height" value="100" min="10">

      <label>매수</label>
      <input type="number" id="quantity" value="1000" min="100">

      <label>편집</label>
      <select id="design">
        <option value="0">인쇄만</option>
        <option value="5000">인쇄 + 편집 (+5,000원)</option>
      </select>

      <div class="price-box">
        예상 금액: <span id="price">26,000</span>원
        <div class="sub-price">부가세 포함: <span id="price-vat">28,600</span>원</div>
      </div>

      <button class="order-btn">파일 업로드 및 주문하기</button>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar">
    <img src="https://via.placeholder.com/200x200?text=KakaoTalk+상담">
    <p>고객센터<br>1688-2384</p>
  </div>

  <!-- Footer -->
  <footer>
    <div class="footer-inner">
      <div>
        <h3>두손기획인쇄</h3>
        <p>서울 영등포구 영등포로 36길 9</p>
        <p>02-2632-1830</p>
      </div>
      <div>
        <h3>입금계좌</h3>
        <p>국민은행 999-1688-2384</p>
      </div>
    </div>
  </footer>

  <script>
    const widthEl = document.getElementById("width");
    const heightEl = document.getElementById("height");
    const qtyEl = document.getElementById("quantity");
    const materialEl = document.getElementById("material");
    const designEl = document.getElementById("design");
    const priceEl = document.getElementById("price");
    const priceVatEl = document.getElementById("price-vat");

    function calculatePrice() {
      let width = parseFloat(widthEl.value);
      let height = parseFloat(heightEl.value);
      let qty = parseInt(qtyEl.value);
      let materialRate = parseFloat(materialEl.value);
      let designCost = parseInt(designEl.value);

      // 기준 값 (100x100mm, 1000매 = 26,000원)
      let basePrice = 26000;
      let baseArea = 100 * 100;
      let baseQty = 1000;

      // 면적 및 매수 비례 가격 계산
      let area = width * height;
      let price = (basePrice * (area / baseArea) * (qty / baseQty)) * materialRate;

      // 편집비 추가
      price += designCost;

      // 최소 금액 보정
      if (price < 10000) price = 10000;

      // 표시 업데이트
      priceEl.textContent = price.toLocaleString();
      priceVatEl.textContent = Math.round(price * 1.1).toLocaleString();
    }

    // 이벤트 연결
    [widthEl, heightEl, qtyEl, materialEl, designEl].forEach(el => {
      el.addEventListener("input", calculatePrice);
    });

    // 초기 계산
    calculatePrice();
  </script>
</body>
</html>
