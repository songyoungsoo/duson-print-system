<?php
// 공통 갤러리 컴포넌트 포함
include "../../includes/CommonGallery.php";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>두손기획인쇄 - 스티커 견적안내</title>

  <?php
  // 공통 갤러리 CSS 포함
  echo CommonGallery::renderCSS();
  ?>

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
      .inline-form-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
      }
      .inline-label {
        width: auto;
      }
      .inline-select, .inline-input {
        width: 100%;
        min-width: auto;
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

    /* 한 줄 레이아웃 폼 스타일 (index.php 기준) */
    .inline-form-container {
      margin: 15px 0;
      padding: 0;
    }
    .inline-form-row {
      display: flex;
      align-items: center;
      margin-bottom: 12px;
      gap: 10px;
      flex-wrap: nowrap;
    }
    .inline-label {
      width: 60px;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      text-align: center;
    }
    .inline-select, .inline-input {
      width: 200px;
      min-width: 200px;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
      box-sizing: border-box;
      text-align: center;
    }

    /* input 필드도 중앙 정렬 */
    .inline-input {
      text-align: center;
    }
    .inline-note {
      font-size: 12px;
      color: #666;
      margin-left: 8px;
    }
    /* 실시간 가격 표시 - 개선된 애니메이션 */
    .price-display {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 15px;
      text-align: center;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      transform: translateZ(0);
      will-change: background, border-color, transform;
    }
    .price-display.calculated {
      border-color: #28a745;
      background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
      transform: scale(1.02);
      box-shadow: 0 6px 20px rgba(40, 167, 69, 0.2);
    }
    .price-label {
      font-size: 1rem;
      color: #6c757d;
      margin-bottom: 8px;
      font-weight: 500;
    }
    .price-amount {
      font-size: 1rem;
      font-weight: 700;
      color: #28a745;
      margin-bottom: 10px;
      text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .price-details {
      font-size: 0.85rem;
      color: #6c757d;
      line-height: 1.5;

      /* 한 줄 표시 강제 - 모든 제품에서 통일 */
      display: flex !important;
      justify-content: center !important;
      align-items: center !important;
      gap: 15px !important;
      flex-wrap: nowrap !important;
      white-space: nowrap !important;
      overflow-x: auto !important;
    }
    /* 업로드 주문 버튼 - 프리미엄 스타일 */
    .upload-order-button {
      margin-top: 15px;
      text-align: center;
    }
    /* .btn-upload-order → common-styles.css SSOT 사용 */

    /* 도무송 선택 시 특수 스타일 */
    .domusong-selected {
      background-color: #ffe6e6 !important;
      border-color: #ff6b6b !important;
      color: #d63031 !important;
    }

    /* 디밍 효과 - 기본값 입력 필드 */
    .inline-input.dimmed {
      color: #999 !important;
      background-color: #f8f9fa !important;
      border-color: #e9ecef !important;
    }

    .inline-input.dimmed:focus {
      color: #333 !important;
      background-color: white !important;
      border-color: #3498db !important;
    }

    /* 툴팁 스타일 */
    .tooltip-container {
      position: relative;
      display: inline-block;
    }

    .tooltip {
      position: absolute;
      top: 50%;
      left: 100%;
      transform: translateY(-50%);
      background-color: #333;
      color: white;
      padding: 8px 12px;
      border-radius: 6px;
      font-size: 12px;
      white-space: nowrap;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      margin-left: 10px;
    }

    .tooltip::after {
      content: '';
      position: absolute;
      top: 50%;
      right: 100%;
      transform: translateY(-50%);
      border: 5px solid transparent;
      border-right-color: #333;
    }

    .tooltip.show {
      opacity: 1;
      visibility: visible;
    }

    /* 파일 업로드 모달 스타일 */
    .upload-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 2000;
    }

    .modal-content {
      background: white;
      border-radius: 12px;
      width: 90%;
      max-width: 600px;
      max-height: 90%;
      overflow-y: auto;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
      padding: 20px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h3 {
      margin: 0;
      color: #333;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #999;
    }

    .close-btn:hover {
      color: #333;
    }

    .modal-body {
      padding: 20px;
    }

    .modal-body h4 {
      margin: 0 0 15px 0;
      color: #333;
      font-size: 1.1rem;
    }

    /* 주문 정보 요약 */
    .order-summary {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .order-summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
      font-size: 0.9rem;
    }

    .order-summary-total {
      border-top: 1px solid #ddd;
      padding-top: 8px;
      font-weight: bold;
      color: #28a745;
    }

    /* 파일 업로드 섹션 */
    .upload-section {
      margin-bottom: 20px;
    }

    .upload-dropzone {
      border: 2px dashed #ddd;
      border-radius: 8px;
      padding: 40px 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .upload-dropzone:hover {
      border-color: #28a745;
      background: #f8fff9;
    }

    .upload-dropzone.dragover {
      border-color: #28a745;
      background: #e8f5e8;
    }

    .upload-icon {
      font-size: 48px;
      margin-bottom: 10px;
    }

    .dropzone-content p {
      margin: 10px 0;
      color: #666;
    }

    .dropzone-content small {
      color: #999;
    }

    /* 업로드된 파일 목록 */
    .uploaded-files {
      margin-top: 20px;
    }

    .file-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 6px;
      margin-bottom: 8px;
    }

    .file-info {
      display: flex;
      align-items: center;
    }

    .file-name {
      margin-left: 10px;
      font-size: 0.9rem;
    }

    .file-size {
      color: #666;
      font-size: 0.8rem;
    }

    .remove-file {
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 4px 8px;
      cursor: pointer;
      font-size: 0.8rem;
    }

    /* 작업 메모 */
    .memo-section {
      margin-bottom: 20px;
    }

    .memo-section textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 6px;
      resize: vertical;
      font-family: inherit;
    }

    /* 연락처 정보 */
    .contact-section {
      margin-bottom: 20px;
    }

    .contact-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 10px;
    }

    .contact-section input {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 0.9rem;
    }

    /* 모달 푸터 */
    .modal-footer {
      padding: 20px;
      border-top: 1px solid #eee;
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }

    .btn-cancel, .btn-order {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.9rem;
      font-weight: 600;
    }

    .btn-cancel {
      background: #6c757d;
      color: white;
    }

    .btn-cancel:hover {
      background: #5a6268;
    }

    .btn-order {
      background: #28a745;
      color: white;
    }

    .btn-order:hover {
      background: #218838;
    }

    .btn-order:disabled {
      background: #ccc;
      cursor: not-allowed;
    }

    @keyframes domusong-blink {
      0%, 100% { background-color: #ffe6e6; }
      50% { background-color: #ffb3b3; }
    }

    .domusong-blink {
      animation: domusong-blink 0.6s ease-in-out 3;
    }

    .price-breakdown {
      margin-top: 10px;
      font-size: 14px;
      color: #555;
      line-height: 1.4;
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
    <?php
    // 스티커 통합갤러리 렌더링
    echo CommonGallery::render([
        'category' => 'sticker',
        'categoryLabel' => '스티커',
        'brandColor' => '#ff5722',  // 스티커 전용 주황색
        'icon' => '🏷️',
        'containerId' => 'stickerGallery'
    ]);
    ?>

    <div class="calculator">
      <h2>스티커 견적안내</h2>

      <form id="stickerForm">
        <div class="inline-form-container">
          <!-- 종류 -->
          <div class="inline-form-row">
            <span class="inline-label">종류</span>
            <select name="jong" class="inline-select" required>
              <option value="">선택하세요</option>
              <option value="jil 아트유광코팅" selected>아트지유광</option>
              <option value="jil 아트무광코팅">아트지무광</option>
              <option value="jil 아트비코팅">아트지비코팅</option>
              <option value="jka 강접아트유광코팅">강접아트유광</option>
              <option value="cka 초강접아트코팅">초강접아트유광</option>
              <option value="cka 초강접아트비코팅">초강접아트비코팅</option>
              <option value="jsp 유포지">유포지</option>
              <option value="jsp 은데드롱">은데드롱</option>
              <option value="jsp 투명스티커">투명스티커</option>
              <option value="jil 모조비코팅">모조지비코팅</option>
              <option value="jsp 크라프트지">크라프트스티커</option>
              <option value="jsp 금지스티커">금지스티커-전화문의</option>
              <option value="jsp 금박스티커">금박스티커-전화문의</option>
              <option value="jsp 롤형스티커">롤스티커-전화문의</option>
            </select>
            <span class="inline-note">금지/금박/롤 전화문의</span>
          </div>

          <!-- 가로 -->
          <div class="inline-form-row">
            <span class="inline-label">가로</span>
            <div class="tooltip-container">
              <input type="number" name="garo" class="inline-input dimmed" min="10" max="500" placeholder="mm" value="100" required>
              <div class="tooltip" id="garoTooltip">mm단위로 입력하세요</div>
            </div>
            <span class="inline-note">※5mm단위 이하 도무송</span>
          </div>

          <!-- 세로 -->
          <div class="inline-form-row">
            <span class="inline-label">세로</span>
            <div class="tooltip-container">
              <input type="number" name="sero" class="inline-input dimmed" min="10" max="500" placeholder="mm" value="100" required>
              <div class="tooltip" id="seroTooltip">mm단위로 입력하세요</div>
            </div>
            <span class="inline-note">※50X60mm 이하 도무송</span>
          </div>

          <!-- 매수 -->
          <div class="inline-form-row">
            <span class="inline-label">매수</span>
            <select name="mesu" class="inline-select" required>
              <option value="">선택하세요</option>
              <option value="500">500매</option>
              <option value="1000" selected>1000매</option>
              <option value="2000">2000매</option>
              <option value="3000">3000매</option>
              <option value="4000">4000매</option>
              <option value="5000">5000매</option>
              <option value="6000">6000매</option>
              <option value="7000">7000매</option>
              <option value="8000">8000매</option>
              <option value="9000">9000매</option>
              <option value="10000">10000매</option>
              <option value="20000">20000매</option>
              <option value="30000">30000매</option>
              <option value="40000">40000매</option>
              <option value="50000">50000매</option>
              <option value="60000">60000매</option>
              <option value="70000">70000매</option>
              <option value="80000">80000매</option>
              <option value="90000">90000매</option>
              <option value="100000">100000매</option>
            </select>
            <span class="inline-note">10,000매이상 별도 견적</span>
          </div>

          <!-- 편집 -->
          <div class="inline-form-row">
            <span class="inline-label">편집</span>
            <select name="uhyung" class="inline-select" required>
              <option value="">선택하세요</option>
              <option value="0" selected>인쇄만</option>
              <option value="10000">기본 편집 (+10,000원)</option>
              <option value="30000">고급 편집 (+30,000원)</option>
            </select>
            <span class="inline-note">단순 작업 외 난이도에 따라 비용 협의</span>
          </div>

          <!-- 모양 -->
          <div class="inline-form-row">
            <span class="inline-label">모양</span>
            <select name="domusong" class="inline-select" required>
              <option value="">선택하세요</option>
              <option value="00000 사각" selected>기본사각</option>
              <option value="08000 사각도무송">사각도무송</option>
              <option value="08000 귀돌">귀돌이(라운드)</option>
              <option value="08000 원형">원형</option>
              <option value="08000 타원">타원형</option>
              <option value="19000 복잡">모양도무송</option>
            </select>
            <span class="inline-note">도무송 시 좌우상하밀림 현상 있습니다 (오차 1mm 이상)</span>
          </div>
        </div>
      </form>

      <!-- 명함 방식의 실시간 가격 표시 -->
      <div class="price-display" id="priceDisplay">
        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
        <div class="price-details" id="priceDetails">
          모든 옵션을 선택하면 자동으로 계산됩니다
        </div>
      </div>
      <!-- 명함 방식의 파일 업로드 및 주문 버튼 -->
      <div class="upload-order-button" id="uploadOrderButton" style="display: none;">
        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
          파일 업로드 및 주문하기
        </button>
      </div>
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

  <?php
  // 공통 업로드 모달 설정
  $modalTitle = "디자인 파일 업로드";
  $modalProductIcon = "🏷️";
  $modalProductName = "스티커";

  // 공통 업로드 모달 포함
  include "../../includes/upload_modal.php";
  ?>

  <!-- 공통 업로드 모달 JavaScript 포함 -->
  <script src="../../includes/upload_modal.js"></script>

  <!-- 공통 갤러리 JavaScript -->
  <script src="../../includes/js/CommonGalleryAPI.js"></script>
  <?php
  // 공통 갤러리 JavaScript 함수 포함
  echo CommonGallery::renderScript();
  ?>

  <script>
    // 전역 변수들
    let debounceTimer;

    // 현재 가격 데이터를 저장하는 전역 변수
    window.currentPriceData = null;

    // 디바운스 함수
    function debounce(func, wait) {
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(debounceTimer);
                func(...args);
            };
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(later, wait);
        };
    }

    // 디바운스된 가격 계산 함수
    const debouncedCalculatePrice = debounce(() => {
        autoCalculatePrice();
    }, 300);

    // 모든 옵션이 선택되었는지 확인하는 함수
    function areAllOptionsSelected() {
        const form = document.getElementById('stickerForm');
        const requiredSelects = form.querySelectorAll('select[required]');
        const requiredInputs = form.querySelectorAll('input[required]');

        for (let select of requiredSelects) {
            if (!select.value || select.value === '') {
                return false;
            }
        }

        for (let input of requiredInputs) {
            if (!input.value || input.value === '' || input.value <= 0) {
                return false;
            }
        }

        return true;
    }

    // 가격 표시 초기화 함수 (명함 방식)
    function resetPriceDisplay() {
        const priceDisplay = document.getElementById('priceDisplay');
        const priceAmount = document.getElementById('priceAmount');
        const priceDetails = document.getElementById('priceDetails');
        const uploadButton = document.getElementById('uploadOrderButton');

        if (priceDisplay) {
            priceDisplay.classList.remove('calculated');
        }
        if (priceAmount) {
            priceAmount.textContent = '견적 계산 필요';
        }
        if (priceDetails) {
            priceDetails.innerHTML = '모든 옵션을 선택하면 자동으로 계산됩니다';
        }
        if (uploadButton) {
            uploadButton.style.display = 'none';
        }

        window.currentPriceData = null;
    }

    // 가격 표시 업데이트 함수 (명함 방식 적용)
    function updatePriceDisplay(priceData) {
        console.log('Price data received:', priceData);

        const priceDisplay = document.getElementById('priceDisplay');
        const priceAmount = document.getElementById('priceAmount');
        const priceDetails = document.getElementById('priceDetails');
        const uploadButton = document.getElementById('uploadOrderButton');

        // DOM 요소 존재 확인
        if (!priceDisplay || !priceAmount || !priceDetails || !uploadButton) {
            console.error('Required DOM elements not found');
            return;
        }

        // 전역 변수에 저장 (raw_price 포함)
        window.currentPriceData = {
            ...priceData,
            raw_price: priceData.raw_price || parseInt(priceData.price.replace(/[^0-9]/g, '')),
            raw_price_vat: priceData.raw_price_vat || parseInt(priceData.price_vat.replace(/[^0-9]/g, ''))
        };

        // 가격 표시 업데이트
        priceAmount.innerHTML = priceData.price + '원';

        // 가격 상세 정보 표시 (새로운 형식)
        priceDetails.innerHTML = `
            인쇄비: ${priceData.price}원 공급가격: ${priceData.price}원 부가세 포함: <span style="color: #e74c3c; font-weight: bold;">${priceData.price_vat}원</span>
        `;

        // 계산 완료 스타일 적용
        priceDisplay.classList.add('calculated');

        // 업로드 버튼 표시
        uploadButton.style.display = 'block';
        console.log('가격 계산 완료, currentPriceData 설정됨:', window.currentPriceData);
    }

    // AJAX를 통한 자동 가격 계산 함수 (명함 방식)
    function autoCalculatePrice() {
        if (!areAllOptionsSelected()) {
            console.log('Not all options selected - checking details:');
            // 각 옵션 상태 확인
            const form = document.getElementById('stickerForm');
            const jong = form.querySelector('select[name="jong"]').value;
            const garo = parseInt(form.querySelector('input[name="garo"]').value) || 0;
            const sero = parseInt(form.querySelector('input[name="sero"]').value) || 0;
            const mesu = form.querySelector('select[name="mesu"]').value;
            const uhyung = form.querySelector('select[name="uhyung"]').value;
            const domusong = form.querySelector('select[name="domusong"]').value;

            console.log('Options status:', {jong, garo, sero, mesu, uhyung, domusong});

            // 옵션이 부족할 때만 가격 초기화 (명함 방식과 동일)
            resetPriceDisplay();
            return;
        }

        console.log('All options selected, calculating...');
        const formData = new FormData(document.getElementById('stickerForm'));

        // 디버깅: 전송되는 데이터 확인
        console.log('Sending form data:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
        }

        // 기존 계산 구조 사용
        formData.append('action', 'calculate');

        console.log('Fetching: ./calculate_price_ajax.php');

        fetch('./calculate_price_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.text(); // 먼저 text로 받아서 확인
        })
        .then(text => {
            console.log('Raw response:', text);

            try {
                const priceData = JSON.parse(text);
                console.log('Parsed response:', priceData);

                if (priceData.success) {
                    updatePriceDisplay(priceData);
                } else {
                    console.error('Calculation failed:', priceData.message || 'Unknown error');
                    resetPriceDisplay();
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response text:', text);
                resetPriceDisplay();
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            resetPriceDisplay();
        });
    }

    // 크기 검증 및 자동 사각도무송 선택 함수
    function checkSizeAndAutoSelect() {
        const garoInput = document.querySelector('input[name="garo"]');
        const seroInput = document.querySelector('input[name="sero"]');
        const domusongSelect = document.querySelector('select[name="domusong"]');

        if (!garoInput || !seroInput || !domusongSelect) return;

        const garo = parseInt(garoInput.value) || 0;
        const sero = parseInt(seroInput.value) || 0;

        // 49mm 이하 체크 (가로 또는 세로 중 하나라도) - 경고창 제거, 자동 선택만
        if (garo <= 49 || sero <= 49) {
            if (domusongSelect.value === "00000 사각") {
                domusongSelect.value = "08000 사각도무송";

                // 적색 클래스 추가
                domusongSelect.classList.add('domusong-selected');

                // 3번 반짝이는 효과 추가
                domusongSelect.classList.add('domusong-blink');
                setTimeout(() => {
                    domusongSelect.classList.remove('domusong-blink');
                }, 1800);

                // 시각적 하이라이트 효과
                domusongSelect.style.backgroundColor = '#fffbdd';
                domusongSelect.style.border = '2px solid #ff9800';
                setTimeout(() => {
                    domusongSelect.style.backgroundColor = '';
                    domusongSelect.style.border = '';
                }, 2000);
            }
            return;
        } else {
            // 49mm 초과일 때 자동으로 사각도무송에서 일반 사각형으로 되돌리기
            if (domusongSelect.value === "08000 사각도무송") {
                domusongSelect.value = "00000 사각";

                // 적색 클래스 제거
                domusongSelect.classList.remove('domusong-selected');

                // 초기화 시각적 효과
                domusongSelect.style.backgroundColor = '#e8f5e8';
                domusongSelect.style.border = '2px solid #28a745';
                setTimeout(() => {
                    domusongSelect.style.backgroundColor = '';
                    domusongSelect.style.border = '';
                }, 1500);
            }
        }
    }

    // 옵션 변경 시 자동 계산 이벤트 리스너 등록
    function initAutoCalculation() {
        const form = document.getElementById('stickerForm');

        // 가로/세로 입력 요소에 크기 검증 이벤트 추가
        const garoInput = form.querySelector('input[name="garo"]');
        const seroInput = form.querySelector('input[name="sero"]');

        if (garoInput) {
            const garoTooltip = document.getElementById('garoTooltip');

            // 초기 툴팁 표시
            if (garoTooltip) {
                setTimeout(() => {
                    garoTooltip.classList.add('show');
                }, 500);
            }

            garoInput.addEventListener('input', function() {
                // 디밍 해제 및 툴팁 숨김
                this.classList.remove('dimmed');
                if (garoTooltip) {
                    garoTooltip.classList.remove('show');
                }
                checkSizeAndAutoSelect();
                debouncedCalculatePrice();
            });
            garoInput.addEventListener('change', function() {
                this.classList.remove('dimmed');
                if (garoTooltip) {
                    garoTooltip.classList.remove('show');
                }
                checkSizeAndAutoSelect();
                debouncedCalculatePrice();
            });
            garoInput.addEventListener('focus', function() {
                this.classList.remove('dimmed');
                if (garoTooltip) {
                    garoTooltip.classList.remove('show');
                }
            });
        }

        if (seroInput) {
            const seroTooltip = document.getElementById('seroTooltip');

            // 초기 툴팁 표시
            if (seroTooltip) {
                setTimeout(() => {
                    seroTooltip.classList.add('show');
                }, 700);
            }

            seroInput.addEventListener('input', function() {
                // 디밍 해제 및 툴팁 숨김
                this.classList.remove('dimmed');
                if (seroTooltip) {
                    seroTooltip.classList.remove('show');
                }
                checkSizeAndAutoSelect();
                debouncedCalculatePrice();
            });
            seroInput.addEventListener('change', function() {
                this.classList.remove('dimmed');
                if (seroTooltip) {
                    seroTooltip.classList.remove('show');
                }
                checkSizeAndAutoSelect();
                debouncedCalculatePrice();
            });
            seroInput.addEventListener('focus', function() {
                this.classList.remove('dimmed');
                if (seroTooltip) {
                    seroTooltip.classList.remove('show');
                }
            });
        }

        // 나머지 입력 요소에 기본 이벤트 리스너 추가
        const otherInputs = form.querySelectorAll('select:not([name="domusong"]), input[type="number"]:not([name="garo"]):not([name="sero"])');
        otherInputs.forEach(input => {
            input.addEventListener('change', debouncedCalculatePrice);
            if (input.type === 'number') {
                input.addEventListener('input', debouncedCalculatePrice);
            }
        });

        // 모양 선택은 별도 처리 (자동 변경 방지)
        const domusongSelect = form.querySelector('select[name="domusong"]');
        if (domusongSelect) {
            domusongSelect.addEventListener('change', function() {
                // 사각도무송 선택 시 적색 클래스 추가/제거
                if (this.value === "08000 사각도무송") {
                    this.classList.add('domusong-selected');
                } else {
                    this.classList.remove('domusong-selected');
                }
                debouncedCalculatePrice();
            });
        }

        // 초기 계산을 지연 실행 (DOM 완전 로드 후) - 기본값으로 계산
        setTimeout(() => {
            console.log('Delayed initial calculation with default values');
            autoCalculatePrice();
        }, 100);
    }

    // 스티커 전용 장바구니 연결 함수 (공통 모달에서 호출됨)
    window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
        console.log('스티커 장바구니 추가 함수 호출됨', uploadedFiles);

        // 로딩 상태 표시
        const cartButton = document.querySelector('.btn-cart');
        if (cartButton) {
            cartButton.innerHTML = '🔄 저장 중...';
            cartButton.disabled = true;
            cartButton.style.opacity = '0.7';
        }

        // 폼 데이터 수집
        const formData = new FormData();

        // 스티커 옵션 데이터 수집
        const jongElement = document.querySelector('select[name="jong"]');
        const garoElement = document.querySelector('input[name="garo"]');
        const seroElement = document.querySelector('input[name="sero"]');
        const mesuElement = document.querySelector('select[name="mesu"]');
        const uhyungElement = document.querySelector('select[name="uhyung"]');
        const domusongElement = document.querySelector('select[name="domusong"]');

        if (jongElement) formData.append('jong', jongElement.value);
        if (garoElement) formData.append('garo', garoElement.value);
        if (seroElement) formData.append('sero', seroElement.value);
        if (mesuElement) formData.append('mesu', mesuElement.value);
        if (uhyungElement) formData.append('uhyung', uhyungElement.value);
        if (domusongElement) formData.append('domusong', domusongElement.value);

        // 가격 정보 추가
        if (window.currentPriceData) {
            console.log('currentPriceData 사용:', window.currentPriceData);
            // raw_price 사용 (콤마가 없는 숫자)
            const rawPrice = window.currentPriceData.raw_price || window.currentPriceData.price.replace(/[^0-9]/g, '');
            const rawPriceVat = window.currentPriceData.raw_price_vat || window.currentPriceData.price_vat.replace(/[^0-9]/g, '');
            formData.append('price', rawPrice);
            formData.append('st_price', rawPrice);
            formData.append('st_price_vat', rawPriceVat);
        } else {
            // 현재 표시된 가격에서 추출 (올바른 ID 사용)
            const priceElement = document.getElementById('priceAmount');
            if (priceElement) {
                const price = priceElement.textContent.replace(/[^0-9]/g, '') || '0';
                console.log('priceAmount에서 추출한 가격:', price);
                formData.append('price', price);
                formData.append('st_price', price);
                formData.append('st_price_vat', price);
            } else {
                console.error('가격 정보를 찾을 수 없습니다.');
                formData.append('price', '0');
                formData.append('st_price', '0');
                formData.append('st_price_vat', '0');
            }
        }

        // 작업 메모 추가
        const workMemo = document.getElementById('modalWorkMemo');
        if (workMemo) {
            formData.append('memo', workMemo.value);
            formData.append('work_memo', workMemo.value);
        }

        // 제품 타입 설정
        formData.append('product_type', 'sticker');
        formData.append('action', 'add_to_basket');

        // 업로드된 파일들 추가
        if (uploadedFiles && uploadedFiles.length > 0) {
            uploadedFiles.forEach((fileObj, index) => {
                formData.append(`files[${index}]`, fileObj.file);
                formData.append(`uploaded_files[${index}]`, fileObj.file);
            });
        }

        console.log('FormData 내용 확인:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        // 서버로 전송
        fetch('add_to_basket.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            console.log('서버 응답:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    console.log('장바구니 저장 성공:', data);
                    onSuccess();
                } else {
                    throw new Error(data.message || '알 수 없는 오류가 발생했습니다.');
                }
            } catch (parseError) {
                console.error('JSON 파싱 오류:', parseError);
                console.error('서버 응답 텍스트:', text);
                throw new Error('서버 응답을 처리할 수 없습니다.');
            }
        })
        .catch(error => {
            console.error('장바구니 저장 오류:', error);
            onError(error.message);
        });
    };

    // 페이지 로드 시 초기화
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing...');
        initAutoCalculation();
        setupDragAndDrop();
    });
  </script>
</body>
</html>
