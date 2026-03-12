<?php
session_start();
$session_id = session_id();

// 출력 버퍼 관리 및 에러 설정
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// 보안 상수 정의 후 데이터베이스 연결
include "../db.php";
$connect = $db;

// 방문자 추적 시스템
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';

// 페이지 설정
$page_title = '두손기획인쇄 - 전단지 팩토리 V2 — AI 자동 생성 시스템';
$current_page = 'leaflet_ai';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
}

// 공통 함수 및 설정
if (file_exists('../includes/functions.php')) {
    include "../includes/functions.php";
}

// 공통 인증 시스템 사용
include "../includes/auth.php";
$is_logged_in = isLoggedIn() || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

// 사용자 정보 설정
if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_id'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
} elseif (isset($_COOKIE['id_login_ok'])) {
    $user_name = $_COOKIE['id_login_ok'];
} else {
    $user_name = '';
}

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($page_title); ?></title>

<!-- SEO 메타 태그 -->
<meta name="description" content="두손기획인쇄 AI 전단지 자동 채우기. 이미지/텍스트 입력으로 전단지 내용 자동 생성. 빠르고 쉽게 홍보물 제작.">
<meta name="keywords" content="AI전단지, 전단지제작, 전단지자동생성, 인쇄, 두손기획인쇄, AI카피라이터, AI디자인">
<meta name="author" content="두손기획인쇄">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
<meta property="og:description" content="두손기획인쇄 AI 전단지 자동 채우기. 이미지/텍스트 입력으로 전단지 내용 자동 생성.">
<meta property="og:image" content="https://dsp114.com/ImgFolder/og-leaflet-ai.png">
<meta property="og:url" content="https://dsp114.com/leaflet/index.php">
<meta property="og:site_name" content="두손기획인쇄">
<meta property="og:locale" content="ko_KR">

<!-- 브랜드 디자인 시스템 (최우선 로드) -->
<link rel="stylesheet" href="../css/brand-design-system.css?v=<?php echo time(); ?>">

<!-- 홈페이지 전용 CSS -->
<link rel="stylesheet" href="../assets/css/layout.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="./leaflet-style.css?v=<?php echo time(); ?>">

<!-- 브랜드 폰트 - Pretendard & Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Tailwind CSS (옵션) -->
<script src="https://cdn.tailwindcss.com"></script>

<style>
/* ── Reset & Foundation ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

@font-face {
  font-family: 'Pretendard';
  src: url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.min.css');
}

:root {
  /* 파스텔톤 컬러 팔레트 (Excel-like 감성) */
  --brand-color: #5b8fb9; /* 파스텔 블루 */
  --accent-color: #8db596; /* 파스텔 그린 */
  --bg-color: #f7f9fc;
  --panel-bg: #ffffff;
  --text-main: #333333;
  --text-muted: #666666;
  --border-color: #d1d9e6;
  --header-bg: #e3ebf3; /* 그리드 헤더용 파스텔톤 */
  --error-color: #e57373; /* 파스텔 레드 */
  --btn-bg: #5b8fb9;
  --btn-hover: #4a7a9e;
  
  --radius: 4px; /* 엑셀처럼 각진 느낌을 살림 */
  --shadow: 0 2px 5px rgba(0,0,0,0.05);
  --cell-padding: 8px 12px;
}

body {
  font-family: 'Pretendard', 'Noto Sans KR', sans-serif;
  background-color: var(--bg-color);
  color: var(--text-main);
  font-size: 14px;
  line-height: 1.5;
  padding: 20px;
}

.container {
  max-width: 960px;
  margin: 0 auto;
  background: var(--panel-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px 30px;
}

.header {
  border-bottom: 2px solid var(--brand-color);
  padding-bottom: 15px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.header h1 {
  font-size: 24px;
  color: var(--brand-color);
  margin: 0;
}

.header .subtitle {
  font-size: 13px;
  color: var(--text-muted);
}

/* ── 엑셀 스타일 폼 컨테이너 ── */
.fieldset {
  margin-bottom: 25px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  overflow: hidden;
}

.fieldset-title {
  background-color: var(--header-bg);
  padding: var(--cell-padding);
  font-weight: 600;
  color: var(--brand-color);
  border-bottom: 1px solid var(--border-color);
  font-size: 15px;
}

.grid-table {
  display: grid;
  grid-template-columns: 140px 1fr 140px 1fr;
  border-bottom: 1px solid var(--border-color);
}

.grid-table:last-child {
  border-bottom: none;
}

.grid-label,
.grid-input {
  padding: var(--cell-padding);
  border-right: 1px solid var(--border-color);
}

.grid-table .grid-label:last-child,
.grid-table .grid-input:last-child {
  border-right: none;
}

.grid-label {
  background-color: #f0f4f7;
  font-weight: 500;
  color: var(--text-main);
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 20px; /* 라벨 우측 여백 */
}

.grid-input {
  display: flex;
  align-items: center;
}

.grid-input input[type="text"],
.grid-input input[type="tel"],
.grid-input select,
.grid-input textarea {
  width: 100%;
  border: 1px solid #ccc; /* 엑셀 셀처럼 보이기 */
  border-radius: var(--radius);
  padding: 6px 10px;
  font-size: 14px;
  color: var(--text-main);
  box-sizing: border-box;
}

.grid-input input[type="text"]:focus,
.grid-input input[type="tel"]:focus,
.grid-input select:focus,
.grid-input textarea:focus {
  border-color: var(--brand-color);
  box-shadow: 0 0 0 1px var(--brand-color);
  outline: none;
}

.grid-input select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23666666' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  padding-right: 30px;
  cursor: pointer;
}

.grid-input textarea {
  resize: vertical;
  min-height: 80px;
}

/* ── 메뉴 테이블 ── */
.menu-grid-table {
  display: grid;
  grid-template-columns: 140px 1fr 140px 1fr;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
}

.menu-grid-table.menu-header {
  background-color: #f0f4f7;
  font-weight: 600;
  color: var(--text-main);
}

.menu-grid-table .grid-cell {
  padding: var(--cell-padding);
  border-right: 1px solid var(--border-color);
}

.menu-grid-table .grid-cell:last-child {
  border-right: none;
}

.menu-grid-table input[type="text"] {
  width: 100%;
  border: 1px solid #ccc;
  border-radius: var(--radius);
  padding: 6px 10px;
  font-size: 14px;
  box-sizing: border-box;
}

.btn-add-menu,
.btn-remove-menu {
  background-color: var(--brand-color);
  color: #fff;
  border: none;
  padding: 8px 15px;
  border-radius: var(--radius);
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.btn-add-menu:hover,
.btn-remove-menu:hover {
  background-color: var(--btn-hover);
}

.btn-remove-menu {
  background-color: var(--error-color);
}

.btn-remove-menu:hover {
  background-color: #c65959;
}

/* ── 버튼 영역 ── */
.button-group {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-primary {
  background-color: var(--brand-color);
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background-color: var(--btn-hover);
}

.btn-secondary {
  background-color: #e0e0e0;
  color: var(--text-main);
  padding: 10px 20px;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background-color: #cccccc;
}

/* ── 매직 폼 영역 (AI 자동 채우기) ── */
.magic-form {
  background-color: #eaf4ed; /* 파스텔 그린 계열 */
  border: 1px dashed var(--accent-color);
  border-radius: var(--radius);
  padding: 20px;
  margin-bottom: 25px;
}

.magic-form-title {
  color: var(--accent-color);
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 10px;
}

.magic-form-description {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 15px;
}

.magic-form-input-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.magic-form-input-group textarea {
  width: 100%;
  min-height: 100px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 10px;
  font-size: 14px;
}

.magic-form-controls {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.magic-form-controls input[type="file"] {
  flex-grow: 1; /* 남은 공간을 채우도록 */
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 6px 10px;
  background-color: #fff;
}

.btn-magic-fill {
  background-color: var(--accent-color);
  color: #fff;
  border: none;
  padding: 8px 15px;
  border-radius: var(--radius);
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.btn-magic-fill:hover {
  background-color: #799e81;
}

.magic-status {
  font-size: 13px;
  color: var(--accent-color);
  font-weight: 600;
  display: none;
}

/* ── 상태 메시지 (로딩, 오류) ── */
.status-message {
  background-color: #fef3c7; /* 노란색 배경 */
  color: #92400e; /* 주황색 텍스트 */
  border: 1px solid #fcd34d;
  border-radius: var(--radius);
  padding: 10px 15px;
  margin-top: 20px;
  font-size: 14px;
  text-align: center;
  display: none;
}

.status-message.error {
  background-color: #fee2e2; /* 빨간색 배경 */
  color: #991b1b; /* 짙은 빨간색 텍스트 */
  border-color: #ef4444;
}

/* ── 필드 오류 스타일 ── */
.field-error {
  border-color: var(--error-color) !important;
}

.error-message {
  font-size: 12px;
  color: var(--error-color);
  margin-top: 5px;
  display: none;
}

.error-message.show {
  display: block;
}

</style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>전단지 팩토리 V2</h1>
        <p class="subtitle">AI 자동 생성 시스템</p>
      </div>
      <button class="btn-secondary" onclick="location.href='../'">홈으로</button>
    </div>

    <!-- Magic Form (AI 자동 채우기) -->
    <div class="magic-form">
      <div class="magic-form-title">✨ AI 매직 폼: 자료(사진/텍스트) 기반 자동 채우기</div>
      <p class="magic-form-description">기존 전단지/메뉴판 사진을 올리거나 아래에 텍스트를 붙여넣으시면 AI가 분석하여 폼을 자동으로 채워줍니다.</p>
      <div class="magic-form-input-group">
        <textarea id="magicTextInput" placeholder="여기에 홍보 문구나 메뉴판 텍스트를 자유롭게 붙여넣으세요..."></textarea>
        <div class="magic-form-controls">
          <input type="file" id="ocrInput" accept="image/*,.txt,.hwp,.docx,.pdf">
          <button type="button" id="btnOcr" class="btn-magic-fill">AI 자동 채우기 실행 🪄</button>
          <span id="ocrStatus" class="magic-status">AI가 자료를 분석하고 있습니다... ⏳</span>
        </div>
      </div>
    </div>

    <form id="leafletForm">
      <div class="fieldset">
        <div class="fieldset-title">기본 정보</div>
        <div class="grid-table">
          <div class="grid-label">가게명/상호 <span style="color: var(--error-color);">*</span></div>
          <div class="grid-input"><input type="text" id="businessName" name="business_name" placeholder="예: 맛나분식" required></div>
          <div class="grid-label">업종/카테고리 <span style="color: var(--error-color);">*</span></div>
          <div class="grid-input">
            <select id="category" name="category" required>
              <option value="">선택</option>
              <option value="음식점">음식점</option>
              <option value="카페">카페</option>
              <option value="미용실">미용실</option>
              <option value="학원">학원</option>
              <option value="병원">병원</option>
              <option value="기타">기타</option>
            </select>
          </div>
        </div>
        <div class="grid-table" id="customCategoryRow" style="display:none;">
          <div class="grid-label">업종 직접입력 <span style="color: var(--error-color);">*</span></div>
          <div class="grid-input full-width"><input type="text" id="customCategory" name="custom_category" placeholder="예: 꽃집, 세탁소"></div>
        </div>
        <div class="grid-table">
          <div class="grid-label">전화번호</div>
          <div class="grid-input"><input type="tel" id="phone" name="phone" placeholder="예: 02-1234-5678"></div>
          <div class="grid-label">영업시간</div>
          <div class="grid-input"><input type="text" id="hours" name="hours" placeholder="예: 매일 10:00 - 22:00"></div>
        </div>
        <div class="grid-table">
          <div class="grid-label">주소</div>
          <div class="grid-input full-width"><input type="text" id="address" name="address" placeholder="예: 서울시 영등포구 당산동 123-4"></div>
        </div>
      </div>

      <div class="fieldset">
        <div class="fieldset-title">강점 / 특징 (최대 3개)</div>
        <div class="grid-table">
          <div class="grid-label">강점 1</div>
          <div class="grid-input"><input type="text" id="feature1" name="feature1" placeholder="예: 30년 전통"></div>
          <div class="grid-label">강점 2</div>
          <div class="grid-input"><input type="text" id="feature2" name="feature2" placeholder="예: 직접 만든 양념"></div>
        </div>
        <div class="grid-table">
          <div class="grid-label">강점 3</div>
          <div class="grid-input full-width"><input type="text" id="feature3" name="feature3" placeholder="예: 역세권 도보 1분"></div>
        </div>
      </div>

      <div class="fieldset">
        <div class="fieldset-title">메뉴 / 품목 (최대 8개)</div>
        <div class="menu-grid-table menu-header">
          <div class="grid-cell">메뉴명</div>
          <div class="grid-cell">설명</div>
          <div class="grid-cell">가격</div>
          <div class="grid-cell"></div>
        </div>
        <div id="menuItems">
          <!-- 메뉴 항목들이 여기에 동적으로 추가됩니다 -->
          <div class="menu-grid-table">
            <div class="grid-cell"><input type="text" name="item_name[]" placeholder="예: 떡볶이"></div>
            <div class="grid-cell"><input type="text" name="item_desc[]" placeholder="예: 매콤달콤 쌀떡볶이"></div>
            <div class="grid-cell"><input type="text" name="item_price[]" placeholder="예: 4,000원"></div>
            <div class="grid-cell"><button type="button" class="btn-remove-menu" onclick="removeMenuItem(this)">삭제</button></div>
          </div>
          <div class="menu-grid-table">
            <div class="grid-cell"><input type="text" name="item_name[]" placeholder="예: 순대"></div>
            <div class="grid-cell"><input type="text" name="item_desc[]" placeholder="예: 속이 꽉 찬 찹쌀순대"></div>
            <div class="grid-cell"><input type="text" name="item_price[]" placeholder="예: 5,000원"></div>
            <div class="grid-cell"><button type="button" class="btn-remove-menu" onclick="removeMenuItem(this)">삭제</button></div>
          </div>
        </div>
        <button type="button" id="addMenuItemBtn" class="btn-add-menu" style="margin-top: 15px;">메뉴 항목 추가</button>
      </div>

      <div class="fieldset">
        <div class="fieldset-title">프로모션 / 이벤트 (선택 사항)</div>
        <div class="grid-table">
          <div class="grid-label">제목</div>
          <div class="grid-input"><input type="text" id="promoTitle" name="promo_title" placeholder="예: GRAND OPEN 기념"></div>
          <div class="grid-label">내용</div>
          <div class="grid-input"><input type="text" id="promoDetail" name="promo_detail" placeholder="예: 전 메뉴 20% 할인"></div>
        </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn-primary">전단지 생성</button>
        <button type="button" class="btn-secondary" onclick="location.reload();">초기화</button>
      </div>
    </form>

    <div id="statusMessage" class="status-message"></div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const leafletForm = document.getElementById('leafletForm');
    const businessNameInput = document.getElementById('businessName');
    const categorySelect = document.getElementById('category');
    const customCategoryRow = document.getElementById('customCategoryRow');
    const customCategoryInput = document.getElementById('customCategory');
    const addMenuItemBtn = document.getElementById('addMenuItemBtn');
    const menuItemsContainer = document.getElementById('menuItems');
    const statusMessageDiv = document.getElementById('statusMessage');
    const btnOcr = document.getElementById('btnOcr');
    const ocrInput = document.getElementById('ocrInput');
    const magicTextInput = document.getElementById('magicTextInput');
    const ocrStatus = document.getElementById('ocrStatus');

    // ── AI 자동 채우기 (OCR) 기능 ──
    if (btnOcr) {
      btnOcr.addEventListener('click', async function() {
        if (!ocrInput.files[0] && magicTextInput.value.trim() === '') {
          alert("전단지 사진, 문서 파일을 선택하거나 텍스트를 입력해주세요.");
          return;
        }

        ocrStatus.style.display = 'inline-block';
        this.disabled = true;
        this.style.opacity = '0.7';
        statusMessageDiv.style.display = 'none'; // 기존 메시지 숨김

        const formData = new FormData();
        if (ocrInput.files[0]) {
            formData.append('ocr_image', ocrInput.files[0]);
        }
        if (magicTextInput.value.trim() !== '') {
            formData.append('magic_text', magicTextInput.value.trim());
        }

        try {
          const response = await fetch('ocr_extract.php', {
            method: 'POST',
            body: formData
          });
          const data = await response.json();

          if (data.error) {
            showStatusMessage("분석 실패: " + data.error, true);
            return;
          }

          // 폼 필드 채우기
          if (data.business_name) businessNameInput.value = data.business_name;
          if (data.category) {
            let found = false;
            for (let i = 0; i < categorySelect.options.length; i++) {
              if (categorySelect.options[i].value === data.category) {
                categorySelect.selectedIndex = i;
                found = true;
                break;
              }
            }
            if (!found && data.category) { // 일치하는 카테고리가 없으면 '기타'로 선택하고 직접 입력 필드 채움
              categorySelect.value = '기타';
              customCategoryRow.style.display = '';
              customCategoryInput.value = data.category;
            }
          }
          if (data.phone) document.getElementById('phone').value = data.phone;
          if (data.hours) document.getElementById('hours').value = data.hours;
          if (data.address) document.getElementById('address').value = data.address;
          
          if (data.features && data.features.length > 0) {
            document.getElementById('feature1').value = data.features[0] || '';
            document.getElementById('feature2').value = data.features[1] || '';
            document.getElementById('feature3').value = data.features[2] || '';
          }

          // 메뉴 항목 채우기
          if (data.items && data.items.length > 0) {
            menuItemsContainer.innerHTML = ''; // 기존 항목 초기화
            data.items.forEach(item => {
              const newRow = document.createElement('div');
              newRow.classList.add('menu-grid-table');
              newRow.innerHTML = `
                <div class="grid-cell"><input type="text" name="item_name[]" value="${item.name || ''}" placeholder="메뉴명"></div>
                <div class="grid-cell"><input type="text" name="item_desc[]" value="${item.description || ''}" placeholder="설명"></div>
                <div class="grid-cell"><input type="text" name="item_price[]" value="${item.price || ''}" placeholder="가격"></div>
                <div class="grid-cell"><button type="button" class="btn-remove-menu" onclick="removeMenuItem(this)">삭제</button></div>
              `;
              menuItemsContainer.appendChild(newRow);
            });
            // 최소 2개 메뉴는 항상 보이도록
            while (menuItemsContainer.children.length < 2) {
              addMenuItem();
            }
          }

          if (data.promo_title) document.getElementById('promoTitle').value = data.promo_title;
          if (data.promo_detail) document.getElementById('promoDetail').value = data.promo_detail;

          showStatusMessage("✅ AI가 자료에서 정보를 성공적으로 추출하여 폼을 채웠습니다!", false);

        } catch (error) {
          console.error('Error during OCR fetch:', error);
          showStatusMessage("요청 중 오류가 발생했습니다. 개발자에게 문의해주세요.", true);
        } finally {
          ocrStatus.style.display = 'none';
          btnOcr.disabled = false;
          btnOcr.style.opacity = '1';
        }
      });
    }

    // ── 이벤트 리스너 ──

    // '기타' 카테고리 선택 시 직접 입력 필드 표시/숨김
    categorySelect.addEventListener('change', function() {
      if (categorySelect.value === '기타') {
        customCategoryRow.style.display = '';
      } else {
        customCategoryRow.style.display = 'none';
        customCategoryInput.value = ''; // 숨길 때 값 초기화
      }
    });

    // 메뉴 항목 추가
    addMenuItemBtn.addEventListener('click', addMenuItem);

    function addMenuItem() {
      if (menuItemsContainer.children.length >= 8) {
        alert("메뉴 항목은 최대 8개까지만 추가할 수 있습니다.");
        return;
      }
      const newRow = document.createElement('div');
      newRow.classList.add('menu-grid-table');
      newRow.innerHTML = `
        <div class="grid-cell"><input type="text" name="item_name[]" placeholder="메뉴명"></div>
        <div class="grid-cell"><input type="text" name="item_desc[]" placeholder="설명"></div>
        <div class="grid-cell"><input type="text" name="item_price[]" placeholder="가격"></div>
        <div class="grid-cell"><button type="button" class="btn-remove-menu" onclick="removeMenuItem(this)">삭제</button></div>
      `;
      menuItemsContainer.appendChild(newRow);
    }

    // 메뉴 항목 삭제
    window.removeMenuItem = function(button) {
      if (menuItemsContainer.children.length > 1) { // 최소 1개는 유지
        button.closest('.menu-grid-table').remove();
      } else {
        alert("메뉴 항목은 최소 1개 이상 있어야 합니다.");
      }
    };

    // ── 폼 제출 처리 ──
    leafletForm.addEventListener('submit', async function(event) {
      event.preventDefault();

      // 간단한 필수 필드 유효성 검사
      if (!businessNameInput.value.trim()) {
        showStatusMessage("가게명을 입력해주세요.", true);
        businessNameInput.classList.add('field-error');
        return;
      } else {
        businessNameInput.classList.remove('field-error');
      }
      if (categorySelect.value === '') {
        showStatusMessage("업종/카테고리를 선택해주세요.", true);
        categorySelect.classList.add('field-error');
        return;
      } else {
        categorySelect.classList.remove('field-error');
      }
      if (categorySelect.value === '기타' && !customCategoryInput.value.trim()) {
        showStatusMessage("업종을 직접 입력해주세요.", true);
        customCategoryInput.classList.add('field-error');
        return;
      } else {
        customCategoryInput.classList.remove('field-error');
      }

      // 메뉴 항목 최소 1개 검사
      let hasMenuItem = false;
      document.querySelectorAll('input[name="item_name[]"]').forEach(input => {
        if (input.value.trim() !== '') {
          hasMenuItem = true;
        }
      });
      if (!hasMenuItem) {
        showStatusMessage("메뉴 항목은 최소 1개 이상 입력해주세요.", true);
        menuItemsContainer.querySelector('input[name="item_name[]"]').classList.add('field-error');
        return;
      } else {
        document.querySelectorAll('input[name="item_name[]"]').forEach(input => input.classList.remove('field-error'));
      }

      const submitBtn = leafletForm.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.textContent = '생성 중...';
      statusMessageDiv.style.display = 'none';

      const formData = new FormData(leafletForm);

      try {
        const response = await fetch('generate.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          window.location.href = `result.php?job_id=${data.job_id}`;
        } else {
          showStatusMessage("생성 실패: " + (data.error || '알 수 없는 오류가 발생했습니다.'), true);
        }
      } catch (error) {
        console.error('Error during form submission:', error);
        showStatusMessage("서버 연결 오류. 다시 시도해주세요.", true);
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = '전단지 생성';
      }
    });

    // 상태 메시지 표시 함수
    function showStatusMessage(message, isError) {
      statusMessageDiv.textContent = message;
      statusMessageDiv.className = 'status-message';
      if (isError) {
        statusMessageDiv.classList.add('error');
      }
      statusMessageDiv.style.display = 'block';
    }

  });
  </script>
</body>
</html>
