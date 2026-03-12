


<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>포스터 팩토리 — AI 자동 포스터 생성</title>
<style>
/* ── Reset & Foundation ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

@font-face {
  font-family: 'Pretendard';
  src: url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.min.css');
}

:root {
  --brand: #2D3436;
  --accent: #D4A373;
  --accent-light: #E8C9A0;
  --bg: #FAFAF8;
  --bg-warm: #F5EDE3;
  --bg-card: #FFFFFF;
  --text: #2D3436;
  --text-muted: #636E72;
  --text-light: #B2BEC3;
  --border: #DFE6E9;
  --border-focus: #D4A373;
  --error: #D63031;
  --success: #00B894;
  --radius: 12px;
  --radius-sm: 8px;
  --shadow-sm: 0 1px 3px rgba(45,52,54,0.06);
  --shadow-md: 0 4px 16px rgba(45,52,54,0.08);
  --shadow-lg: 0 8px 32px rgba(45,52,54,0.12);
  --shadow-glow: 0 0 0 3px rgba(212,163,115,0.15);
  --transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

html {
  font-size: 15px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

body {
  font-family: 'Pretendard', 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
  background: var(--bg);
  color: var(--text);
  line-height: 1.6;
  min-height: 100vh;
}

/* ── Hero Header ── */
.hero-header {
  background: linear-gradient(135deg, var(--brand) 0%, #3D4A4D 50%, var(--brand) 100%);
  padding: 3.2rem 1.5rem 2.8rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-header::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 30% 40%, rgba(212,163,115,0.08) 0%, transparent 50%),
              radial-gradient(circle at 70% 60%, rgba(212,163,115,0.05) 0%, transparent 40%);
  animation: shimmer 12s ease-in-out infinite;
}

@keyframes shimmer {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(-3%, 2%); }
}

.hero-header .badge {
  display: inline-block;
  background: rgba(212,163,115,0.15);
  color: var(--accent-light);
  font-size: 0.73rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  padding: 0.35rem 1rem;
  border-radius: 100px;
  border: 1px solid rgba(212,163,115,0.2);
  margin-bottom: 1rem;
  position: relative;
}

.hero-header h1 {
  color: #FFFFFF;
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  margin-bottom: 0.5rem;
  position: relative;
}

.hero-header h1 span {
  background: linear-gradient(135deg, var(--accent-light) 0%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-header p {
  color: rgba(255,255,255,0.6);
  font-size: 0.93rem;
  font-weight: 400;
  position: relative;
}

/* ── Main Container ── */
.container {
  max-width: 720px;
  margin: 0 auto;
  padding: 2rem 1.25rem 4rem;
}

/* ── Section Cards ── */
.section-card {
  background: var(--bg-card);
  border-radius: var(--radius);
  border: 1px solid var(--border);
  padding: 1.75rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--shadow-sm);
  transition: box-shadow var(--transition), border-color var(--transition);
  animation: fadeSlideUp 0.5s ease-out both;
}

.section-card:hover {
  box-shadow: var(--shadow-md);
  border-color: rgba(212,163,115,0.3);
}

@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}

.section-card:nth-child(1) { animation-delay: 0.05s; }
.section-card:nth-child(2) { animation-delay: 0.10s; }
.section-card:nth-child(3) { animation-delay: 0.15s; }
.section-card:nth-child(4) { animation-delay: 0.20s; }
.section-card:nth-child(5) { animation-delay: 0.25s; }
.section-card:nth-child(6) { animation-delay: 0.30s; }

.section-title {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--brand);
  margin-bottom: 1.25rem;
  letter-spacing: -0.02em;
}

.section-title .icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  flex-shrink: 0;
}

.section-title .icon.orange { background: rgba(212,163,115,0.12); }
.section-title .icon.blue { background: rgba(9,132,227,0.08); }
.section-title .icon.green { background: rgba(0,184,148,0.08); }
.section-title .icon.purple { background: rgba(108,92,231,0.08); }
.section-title .icon.red { background: rgba(214,48,49,0.08); }
.section-title .icon.teal { background: rgba(0,206,209,0.08); }

.section-subtitle {
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 400;
  margin-left: auto;
}

/* ── Form Elements ── */
.form-group {
  margin-bottom: 1rem;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 0.35rem;
}

label .required {
  color: var(--error);
  margin-left: 2px;
}

label .optional {
  color: var(--text-light);
  font-weight: 400;
  font-size: 0.75rem;
  margin-left: 4px;
}

input[type="text"],
input[type="tel"],
select,
textarea {
  width: 100%;
  padding: 0.65rem 0.85rem;
  font-size: 0.88rem;
  font-family: inherit;
  color: var(--text);
  background: var(--bg);
  border: 1.5px solid var(--border);
  border-radius: var(--radius-sm);
  outline: none;
  transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
}

input[type="text"]:focus,
input[type="tel"]:focus,
select:focus,
textarea:focus {
  border-color: var(--border-focus);
  box-shadow: var(--shadow-glow);
  background: #FFFFFF;
}

input[type="text"]::placeholder,
input[type="tel"]::placeholder,
textarea::placeholder {
  color: var(--text-light);
}

select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23636E72' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding-right: 2.2rem;
}

textarea {
  resize: vertical;
  min-height: 72px;
}

/* ── Menu Items ── */
.menu-items-wrap {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.menu-row {
  display: grid;
  grid-template-columns: 1fr 1.4fr 100px 36px;
  gap: 0.5rem;
  align-items: start;
  animation: fadeSlideUp 0.3s ease-out both;
}

.menu-row input {
  font-size: 0.84rem;
  padding: 0.55rem 0.7rem;
}

.menu-row .row-num {
  display: none;
}

.btn-remove-row {
  width: 36px;
  height: 36px;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--bg);
  color: var(--text-muted);
  font-size: 1.1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition);
}

.btn-remove-row:hover {
  border-color: var(--error);
  color: var(--error);
  background: rgba(214,48,49,0.04);
}

.menu-labels {
  display: grid;
  grid-template-columns: 1fr 1.4fr 100px 36px;
  gap: 0.5rem;
  margin-bottom: 0.35rem;
}

.menu-labels span {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text-muted);
}

.btn-add-row {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1rem;
  font-size: 0.82rem;
  font-weight: 600;
  font-family: inherit;
  color: var(--accent);
  background: rgba(212,163,115,0.06);
  border: 1.5px dashed rgba(212,163,115,0.35);
  border-radius: var(--radius-sm);
  cursor: pointer;
  margin-top: 0.5rem;
  transition: all var(--transition);
}

.btn-add-row:hover {
  background: rgba(212,163,115,0.12);
  border-color: var(--accent);
}

/* ── Features ── */
.features-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 0.5rem;
}

/* ── Collapsible Promo ── */
.collapsible-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.6rem 0;
  font-size: 0.88rem;
  font-weight: 600;
  font-family: inherit;
  color: var(--accent);
  background: none;
  border: none;
  cursor: pointer;
  transition: color var(--transition);
}

.collapsible-toggle:hover { color: var(--brand); }

.collapsible-toggle .arrow {
  transition: transform var(--transition);
  font-size: 0.7rem;
}

.collapsible-toggle.open .arrow {
  transform: rotate(90deg);
}

.collapsible-body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s ease, opacity 0.3s ease;
  opacity: 0;
}

.collapsible-body.open {
  max-height: 200px;
  opacity: 1;
}

/* ── Radio Layout ── */
.layout-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.layout-option {
  position: relative;
}

.layout-option input[type="radio"] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.layout-option label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.3rem;
  padding: 0.75rem 0.5rem;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--transition);
  text-align: center;
  font-weight: 500;
  font-size: 0.78rem;
  color: var(--text-muted);
  margin-bottom: 0;
}

.layout-option label .layout-icon {
  font-size: 1.3rem;
  line-height: 1;
  margin-bottom: 2px;
}

.layout-option input[type="radio"]:checked + label {
  border-color: var(--accent);
  background: rgba(212,163,115,0.06);
  color: var(--brand);
  box-shadow: var(--shadow-glow);
}

.layout-option label:hover {
  border-color: rgba(212,163,115,0.4);
  background: rgba(212,163,115,0.03);
}

/* ── Submit ── */
.submit-wrap {
  margin-top: 1.5rem;
  text-align: center;
}

.btn-submit {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  width: 100%;
  max-width: 420px;
  padding: 1rem 2rem;
  font-size: 1.05rem;
  font-weight: 700;
  font-family: inherit;
  letter-spacing: -0.01em;
  color: #FFFFFF;
  background: linear-gradient(135deg, var(--brand) 0%, #3D4A4D 100%);
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  box-shadow: var(--shadow-md);
  transition: all var(--transition);
  position: relative;
  overflow: hidden;
}

.btn-submit::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(212,163,115,0.15), transparent);
  transition: left 0.6s ease;
}

.btn-submit:hover::before {
  left: 100%;
}

.btn-submit:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-1px);
}

.btn-submit:active {
  transform: translateY(0);
  box-shadow: var(--shadow-sm);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.btn-submit .spinner {
  display: none;
  width: 20px;
  height: 20px;
  border: 2.5px solid rgba(255,255,255,0.3);
  border-top-color: #FFFFFF;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

.btn-submit.loading .spinner { display: block; }
.btn-submit.loading .btn-text { display: none; }

@keyframes spin {
  to { transform: rotate(360deg); }
}

.submit-note {
  font-size: 0.78rem;
  color: var(--text-light);
  margin-top: 0.75rem;
}

/* ── Validation ── */
.field-error {
  border-color: var(--error) !important;
}

.error-msg {
  font-size: 0.75rem;
  color: var(--error);
  margin-top: 0.25rem;
  display: none;
}

.error-msg.show {
  display: block;
}

/* ── Progress Modal ── */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(45,52,54,0.6);
  backdrop-filter: blur(6px);
  z-index: 1000;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.modal-overlay.show {
  display: flex;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-box {
  background: var(--bg-card);
  border-radius: 16px;
  padding: 2.5rem 2rem;
  max-width: 440px;
  width: 100%;
  box-shadow: 0 24px 64px rgba(0,0,0,0.15);
  text-align: center;
  animation: modalSlideUp 0.4s cubic-bezier(0.34,1.56,0.64,1) both;
}

@keyframes modalSlideUp {
  from { opacity: 0; transform: translateY(30px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-title {
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--brand);
  margin-bottom: 0.4rem;
}

.modal-subtitle {
  font-size: 0.82rem;
  color: var(--text-muted);
  margin-bottom: 2rem;
}

/* ── Progress Steps ── */
.progress-steps {
  text-align: left;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.step-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border-radius: var(--radius-sm);
  transition: background var(--transition);
}

.step-row.active {
  background: rgba(212,163,115,0.06);
}

.step-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.75rem;
  transition: all var(--transition);
}

.step-icon.pending {
  background: var(--bg);
  border: 2px solid var(--border);
  color: var(--text-light);
}

.step-icon.running {
  background: rgba(212,163,115,0.12);
  border: 2px solid var(--accent);
  color: var(--accent);
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(212,163,115,0.3); }
  50% { box-shadow: 0 0 0 6px rgba(212,163,115,0); }
}

.step-icon.done {
  background: var(--success);
  border: 2px solid var(--success);
  color: #FFFFFF;
}

.step-icon.error {
  background: var(--error);
  border: 2px solid var(--error);
  color: #FFFFFF;
}

.step-label {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text);
  flex: 1;
}

.step-label .detail {
  font-weight: 400;
  color: var(--text-muted);
  font-size: 0.78rem;
  margin-left: 0.3rem;
}

.step-row.active .step-label {
  font-weight: 600;
}

.step-row.done-row .step-label {
  color: var(--text-muted);
}

/* ── Progress Bar ── */
.progress-bar-wrap {
  margin-top: 1.75rem;
  margin-bottom: 0.5rem;
}

.progress-bar-track {
  width: 100%;
  height: 6px;
  background: var(--bg);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, var(--accent) 0%, var(--accent-light) 100%);
  border-radius: 3px;
  transition: width 0.5s ease;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 0.5rem;
}

.modal-error-msg {
  display: none;
  margin-top: 1rem;
  padding: 0.75rem;
  background: rgba(214,48,49,0.06);
  border: 1px solid rgba(214,48,49,0.15);
  border-radius: var(--radius-sm);
  font-size: 0.82rem;
  color: var(--error);
}

/* ── Footer ── */
.footer {
  text-align: center;
  padding: 2rem 1rem;
  font-size: 0.78rem;
  color: var(--text-light);
}

.footer a {
  color: var(--accent);
  text-decoration: none;
}

/* ── Responsive ── */
@media (max-width: 600px) {
  .hero-header { padding: 2.2rem 1rem 2rem; }
  .hero-header h1 { font-size: 1.6rem; }
  .container { padding: 1.25rem 1rem 3rem; }
  .section-card { padding: 1.25rem; }
  .form-row { grid-template-columns: 1fr; }
  .menu-row { grid-template-columns: 1fr 1fr 80px 32px; }
  .menu-labels { grid-template-columns: 1fr 1fr 80px 32px; }
  .features-grid { grid-template-columns: 1fr; }
  .layout-grid { grid-template-columns: repeat(2, 1fr); }
  .btn-submit { padding: 0.85rem 1.5rem; font-size: 0.95rem; }
}

@media (max-width: 420px) {
  .menu-row { grid-template-columns: 1fr 1fr; }
  .menu-labels { grid-template-columns: 1fr 1fr; }
  .menu-row input:nth-child(3) { grid-column: 1; }
  .menu-row .btn-remove-row { grid-column: 2; justify-self: end; }
}
</style>
</head>
<body>

<!-- ── Hero Header ── -->
<header class="hero-header">
  <div class="badge">AI-Powered Design</div>
  <h1>포스터 <span>팩토리</span></h1>
  <p>사업 정보를 입력하면 AI가 인쇄용 포스터를 자동으로 만들어드립니다</p>
</header>

<!-- ── Main Form ── -->
<main class="container">
<form id="posterForm" autocomplete="off">

  <!-- Section 1: 업종 & 가게 -->
  <div class="section-card">
    <div class="section-title">
      <span class="icon orange">🏪</span>
      업종 & 가게 정보
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="category">업종/카테고리 <span class="required">*</span></label>
        <select id="category" name="category" required>
          <option value="">선택해주세요</option>
          <option value="분식">분식</option>
          <option value="치킨">치킨</option>
          <option value="카페/베이커리">카페/베이커리</option>
          <option value="음식점(한식)">음식점(한식)</option>
          <option value="음식점(중식)">음식점(중식)</option>
          <option value="음식점(일식)">음식점(일식)</option>
          <option value="음식점(양식)">음식점(양식)</option>
          <option value="미용실/네일">미용실/네일</option>
          <option value="학원/교습소">학원/교습소</option>
          <option value="병원/의원">병원/의원</option>
          <option value="기타">기타(직접입력)</option>
        </select>
      </div>

      <div class="form-group" id="customCategoryWrap" style="display:none;">
        <label for="customCategory">업종 직접입력 <span class="required">*</span></label>
        <input type="text" id="customCategory" name="custom_category" placeholder="예: 꽃집, 세탁소, 헬스장">
      </div>

      <div class="form-group">
        <label for="businessName">가게명/상호 <span class="required">*</span></label>
        <input type="text" id="businessName" name="business_name" placeholder="예: 맛나분식" required>
        <div class="error-msg" id="nameError">가게명을 입력해주세요</div>
      </div>
    </div>
  </div>

  <!-- Section 2: 메뉴/품목 -->
  <div class="section-card">
    <div class="section-title">
      <span class="icon blue">📋</span>
      메뉴 / 품목
      <span class="section-subtitle">최소 1개, 최대 8개</span>
    </div>

    <div class="menu-labels">
      <span>메뉴명 <span style="color:var(--error)">*</span></span>
      <span>설명</span>
      <span>가격</span>
      <span></span>
    </div>

    <div class="menu-items-wrap" id="menuItems">
      <div class="menu-row" data-idx="1">
        <input type="text" name="item_name[]" placeholder="떡볶이">
        <input type="text" name="item_desc[]" placeholder="매콤달콤 쌀떡볶이">
        <input type="text" name="item_price[]" placeholder="4,000원">
        <button type="button" class="btn-remove-row" onclick="removeMenuRow(this)" title="삭제">×</button>
      </div>
      <div class="menu-row" data-idx="2">
        <input type="text" name="item_name[]" placeholder="순대">
        <input type="text" name="item_desc[]" placeholder="속이 꽉 찬 찹쌀순대">
        <input type="text" name="item_price[]" placeholder="5,000원">
        <button type="button" class="btn-remove-row" onclick="removeMenuRow(this)" title="삭제">×</button>
      </div>
      <div class="menu-row" data-idx="3">
        <input type="text" name="item_name[]" placeholder="튀김">
        <input type="text" name="item_desc[]" placeholder="바삭한 모듬튀김">
        <input type="text" name="item_price[]" placeholder="3,500원">
        <button type="button" class="btn-remove-row" onclick="removeMenuRow(this)" title="삭제">×</button>
      </div>
      <div class="menu-row" data-idx="4">
        <input type="text" name="item_name[]" placeholder="김밥">
        <input type="text" name="item_desc[]" placeholder="정성 가득 수제김밥">
        <input type="text" name="item_price[]" placeholder="3,000원">
        <button type="button" class="btn-remove-row" onclick="removeMenuRow(this)" title="삭제">×</button>
      </div>
    </div>

    <button type="button" class="btn-add-row" id="btnAddMenu" onclick="addMenuRow()">
      <span>＋</span> 메뉴 추가
    </button>
    <div class="error-msg" id="menuError">최소 1개의 메뉴명을 입력해주세요</div>
  </div>

  <!-- Section 3: 연락처 -->
  <div class="section-card">
    <div class="section-title">
      <span class="icon green">📞</span>
      연락처 정보
      <span class="section-subtitle">선택사항</span>
    </div>

    <div class="form-group">
      <label for="phone">전화번호 <span class="optional">선택</span></label>
      <input type="tel" id="phone" name="phone" placeholder="02-1234-5678">
    </div>

    <div class="form-group">
      <label for="address">주소 <span class="optional">선택</span></label>
      <input type="text" id="address" name="address" placeholder="서울 영등포구 당산동 123-4">
    </div>

    <div class="form-group">
      <label for="hours">영업시간 <span class="optional">선택</span></label>
      <input type="text" id="hours" name="hours" placeholder="매일 10:00 - 22:00">
    </div>
  </div>

  <!-- Section 4: 특징 & 포스터 설정 -->
  <div class="section-card">
    <div class="section-title">
      <span class="icon purple">✨</span>
      특징 & 강점
      <span class="section-subtitle">선택사항</span>
    </div>

    <div class="features-grid">
      <div class="form-group">
        <input type="text" name="feature1" placeholder="예: 30년 전통">
      </div>
      <div class="form-group">
        <input type="text" name="feature2" placeholder="예: 직접 만든 양념">
      </div>
      <div class="form-group">
        <input type="text" name="feature3" placeholder="예: 역세권 도보 1분">
      </div>
    </div>

    <!-- Promo (collapsible) -->
    <div style="margin-top: 0.75rem; border-top: 1px solid var(--border); padding-top: 0.5rem;">
      <button type="button" class="collapsible-toggle" onclick="togglePromo(this)">
        <span class="arrow">▸</span>
        프로모션 / 이벤트 정보 추가
      </button>
      <div class="collapsible-body" id="promoBody">
        <div class="form-group" style="margin-top: 0.5rem;">
          <label for="promoTitle">프로모션 제목</label>
          <input type="text" id="promoTitle" name="promo_title" placeholder="예: GRAND OPEN 기념">
        </div>
        <div class="form-group">
          <label for="promoDetail">프로모션 내용</label>
          <input type="text" id="promoDetail" name="promo_detail" placeholder="예: 전 메뉴 20% 할인">
        </div>
      </div>
    </div>
  </div>

  <!-- Section 5: 포스터 옵션 -->
  <div class="section-card">
    <div class="section-title">
      <span class="icon teal">🎨</span>
      포스터 옵션
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="purpose">포스터 목적</label>
        <select id="purpose" name="purpose">
          <option value="메뉴 홍보">메뉴 홍보</option>
          <option value="신규 오픈">신규 오픈</option>
          <option value="이벤트/행사">이벤트/행사</option>
          <option value="시즌 프로모션">시즌 프로모션</option>
        </select>
      </div>

      <div class="form-group">
        <label for="targetAudience">타겟 고객 <span class="optional">선택</span></label>
        <input type="text" id="targetAudience" name="target_audience" placeholder="예: 20-40대 직장인">
      </div>
    </div>

    <div class="form-group">
      <label for="tone">분위기/톤 <span class="optional">선택</span></label>
      <input type="text" id="tone" name="tone" placeholder="예: 따뜻한, 활기찬, 고급스러운">
    </div>

    <div class="form-group" style="margin-top: 1rem;">
      <label>레이아웃</label>
      <div class="layout-grid">
        <div class="layout-option">
          <input type="radio" id="layout_auto" name="layout" value="auto" checked>
          <label for="layout_auto">
            <span class="layout-icon">⭐</span>
            자동(추천)
          </label>
        </div>
        <div class="layout-option">
          <input type="radio" id="layout_classic" name="layout" value="classic_grid">
          <label for="layout_classic">
            <span class="layout-icon">▦</span>
            클래식 그리드
          </label>
        </div>
        <div class="layout-option">
          <input type="radio" id="layout_hero" name="layout" value="hero_dominant">
          <label for="layout_hero">
            <span class="layout-icon">▣</span>
            히어로 강조
          </label>
        </div>
        <div class="layout-option">
          <input type="radio" id="layout_magazine" name="layout" value="magazine_split">
          <label for="layout_magazine">
            <span class="layout-icon">◧</span>
            매거진 분할
          </label>
        </div>
        <div class="layout-option">
          <input type="radio" id="layout_bold" name="layout" value="bold_typo">
          <label for="layout_bold">
            <span class="layout-icon">𝐁</span>
            볼드 타이포
          </label>
        </div>
        <div class="layout-option">
          <input type="radio" id="layout_side" name="layout" value="side_by_side">
          <label for="layout_side">
            <span class="layout-icon">◫</span>
            사이드 바이
          </label>
        </div>
      </div>
    </div>
  </div>

  <!-- Submit -->
  <div class="submit-wrap">
    <button type="submit" class="btn-submit" id="btnSubmit">
      <span class="btn-text">🚀 AI 포스터 생성하기</span>
      <span class="spinner"></span>
    </button>
    <p class="submit-note">생성에는 약 2~3분 소요됩니다. Gemini AI로 이미지를 생성합니다.</p>
  </div>

</form>
</main>

<!-- ── Progress Modal ── -->
<div class="modal-overlay" id="progressModal">
  <div class="modal-box">
    <div class="modal-title">포스터 생성 중</div>
    <div class="modal-subtitle" id="modalBizName">맛나분식</div>

    <div class="progress-steps" id="progressSteps">
      <div class="step-row" data-step="copy">
        <div class="step-icon pending">1</div>
        <div class="step-label">카피 생성</div>
      </div>
      <div class="step-row" data-step="design">
        <div class="step-icon pending">2</div>
        <div class="step-label">디자인 생성</div>
      </div>
      <div class="step-row" data-step="artdirect">
        <div class="step-icon pending">3</div>
        <div class="step-label">아트디렉팅</div>
      </div>
      <div class="step-row" data-step="images">
        <div class="step-icon pending">4</div>
        <div class="step-label">이미지 생성 <span class="detail" id="imageDetail"></span></div>
      </div>
      <div class="step-row" data-step="svg">
        <div class="step-icon pending">5</div>
        <div class="step-label">SVG 조립</div>
      </div>
    </div>

    <div class="progress-bar-wrap">
      <div class="progress-bar-track">
        <div class="progress-bar-fill" id="progressFill"></div>
      </div>
      <div class="progress-info">
        <span id="progressPercent">0%</span>
        <span id="progressElapsed">0초 경과</span>
      </div>
    </div>

    <div class="modal-error-msg" id="modalError"></div>
  </div>
</div>

<!-- ── Footer ── -->
<footer class="footer">
  <a href="/">두손기획인쇄</a> &middot; 포스터 팩토리 &middot; Powered by Gemini AI
</footer>

<script>
(function() {
  'use strict';

  // ── Menu Rows ──
  var menuWrap = document.getElementById('menuItems');
  var btnAdd = document.getElementById('btnAddMenu');

  function getMenuRowCount() {
    return menuWrap.querySelectorAll('.menu-row').length;
  }

  window.addMenuRow = function() {
    if (getMenuRowCount() >= 8) {
      btnAdd.style.display = 'none';
      return;
    }
    var idx = getMenuRowCount() + 1;
    var row = document.createElement('div');
    row.className = 'menu-row';
    row.setAttribute('data-idx', idx);
    row.innerHTML =
      '<input type="text" name="item_name[]" placeholder="메뉴명">' +
      '<input type="text" name="item_desc[]" placeholder="설명">' +
      '<input type="text" name="item_price[]" placeholder="가격">' +
      '<button type="button" class="btn-remove-row" onclick="removeMenuRow(this)" title="삭제">×</button>';
    menuWrap.appendChild(row);
    if (getMenuRowCount() >= 8) btnAdd.style.display = 'none';
  };

  window.removeMenuRow = function(btn) {
    if (getMenuRowCount() <= 1) return;
    btn.closest('.menu-row').remove();
    if (getMenuRowCount() < 8) btnAdd.style.display = '';
  };

  // ── Custom Category ──
  var catSelect = document.getElementById('category');
  var customWrap = document.getElementById('customCategoryWrap');
  catSelect.addEventListener('change', function() {
    if (this.value === '기타') {
      customWrap.style.display = '';
    } else {
      customWrap.style.display = 'none';
    }
  });

  // ── Collapsible Promo ──
  window.togglePromo = function(btn) {
    var body = document.getElementById('promoBody');
    btn.classList.toggle('open');
    body.classList.toggle('open');
  };

  // ── Form Validation ──
  function validateForm() {
    var valid = true;

    // Business name
    var nameInput = document.getElementById('businessName');
    var nameErr = document.getElementById('nameError');
    if (!nameInput.value.trim()) {
      nameInput.classList.add('field-error');
      nameErr.classList.add('show');
      valid = false;
    } else {
      nameInput.classList.remove('field-error');
      nameErr.classList.remove('show');
    }

    // Category
    var catInput = document.getElementById('category');
    if (!catInput.value) {
      catInput.classList.add('field-error');
      valid = false;
    } else {
      catInput.classList.remove('field-error');
      if (catInput.value === '기타') {
        var customCat = document.getElementById('customCategory');
        if (!customCat.value.trim()) {
          customCat.classList.add('field-error');
          valid = false;
        } else {
          customCat.classList.remove('field-error');
        }
      }
    }

    // At least 1 menu item name
    var menuNames = document.querySelectorAll('input[name="item_name[]"]');
    var hasMenu = false;
    menuNames.forEach(function(inp) {
      if (inp.value.trim()) hasMenu = true;
    });
    var menuErr = document.getElementById('menuError');
    if (!hasMenu) {
      menuErr.classList.add('show');
      valid = false;
    } else {
      menuErr.classList.remove('show');
    }

    return valid;
  }

  // ── Form Submit ──
  var form = document.getElementById('posterForm');
  var btnSubmit = document.getElementById('btnSubmit');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validateForm()) return;

    btnSubmit.classList.add('loading');
    btnSubmit.disabled = true;

    // Build form data
    var formData = new FormData(form);

    fetch('generate.php', {
      method: 'POST',
      body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.success) {
        showProgressModal(data.job_id, formData.get('business_name'));
      } else {
        alert('오류: ' + (data.error || '포스터 생성에 실패했습니다.'));
        btnSubmit.classList.remove('loading');
        btnSubmit.disabled = false;
      }
    })
    .catch(function(err) {
      alert('서버 연결에 실패했습니다. 다시 시도해주세요.');
      btnSubmit.classList.remove('loading');
      btnSubmit.disabled = false;
    });
  });

  // ── Progress Modal ──
  var modal = document.getElementById('progressModal');
  var pollTimer = null;
  var startTime = 0;
  var elapsedTimer = null;

  function showProgressModal(jobId, bizName) {
    modal.classList.add('show');
    document.getElementById('modalBizName').textContent = bizName || '';
    startTime = Date.now();

    // Reset steps
    document.querySelectorAll('.step-row').forEach(function(row) {
      row.classList.remove('active', 'done-row');
      var icon = row.querySelector('.step-icon');
      icon.className = 'step-icon pending';
    });
    document.getElementById('progressFill').style.width = '0%';
    document.getElementById('progressPercent').textContent = '0%';
    document.getElementById('modalError').style.display = 'none';
    document.getElementById('imageDetail').textContent = '';

    // Start polling
    pollTimer = setInterval(function() { pollStatus(jobId); }, 2000);

    // Elapsed timer
    elapsedTimer = setInterval(function() {
      var elapsed = Math.floor((Date.now() - startTime) / 1000);
      document.getElementById('progressElapsed').textContent = elapsed + '초 경과';
    }, 1000);
  }

  function pollStatus(jobId) {
    fetch('status.php?job_id=' + encodeURIComponent(jobId))
    .then(function(res) { return res.json(); })
    .then(function(data) {
      updateSteps(data.steps || []);
      document.getElementById('progressFill').style.width = (data.progress || 0) + '%';
      document.getElementById('progressPercent').textContent = (data.progress || 0) + '%';

      if (data.status === 'completed') {
        clearInterval(pollTimer);
        clearInterval(elapsedTimer);
        setTimeout(function() {
          window.location.href = 'result.php?job_id=' + encodeURIComponent(jobId);
        }, 800);
      } else if (data.status === 'error') {
        clearInterval(pollTimer);
        clearInterval(elapsedTimer);
        var errBox = document.getElementById('modalError');
        errBox.textContent = data.message || '포스터 생성 중 오류가 발생했습니다.';
        errBox.style.display = 'block';
        btnSubmit.classList.remove('loading');
        btnSubmit.disabled = false;
      }
    })
    .catch(function() {
      // silently retry
    });
  }

  function updateSteps(steps) {
    steps.forEach(function(step) {
      var stepName = step.name;
      var stepKey = '';
      if (stepName.indexOf('카피') !== -1) stepKey = 'copy';
      else if (stepName.indexOf('디자인') !== -1) stepKey = 'design';
      else if (stepName.indexOf('아트') !== -1) stepKey = 'artdirect';
      else if (stepName.indexOf('이미지') !== -1) stepKey = 'images';
      else if (stepName.indexOf('SVG') !== -1 || stepName.indexOf('조립') !== -1) stepKey = 'svg';

      var row = document.querySelector('.step-row[data-step="' + stepKey + '"]');
      if (!row) return;
      var icon = row.querySelector('.step-icon');

      if (step.status === 'done') {
        icon.className = 'step-icon done';
        icon.innerHTML = '✓';
        row.classList.remove('active');
        row.classList.add('done-row');
      } else if (step.status === 'running') {
        icon.className = 'step-icon running';
        icon.innerHTML = '';
        row.classList.add('active');
        row.classList.remove('done-row');
      } else if (step.status === 'error') {
        icon.className = 'step-icon error';
        icon.innerHTML = '!';
        row.classList.remove('active');
      }

      // Image detail
      if (stepKey === 'images' && step.detail) {
        document.getElementById('imageDetail').textContent = '(' + step.detail + ')';
      }
    });
  }

  // ── Clear field errors on input ──
  document.addEventListener('input', function(e) {
    if (e.target.matches('input, select')) {
      e.target.classList.remove('field-error');
    }
  });

})();
</script>

</body>
</html>
