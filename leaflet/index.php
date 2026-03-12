<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>전단지 팩토리 V2 — AI 자동 생성 시스템</title>
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

.cell-label {
  background-color: #f0f4f8;
  padding: var(--cell-padding);
  border-right: 1px solid var(--border-color);
  font-weight: 500;
  display: flex;
  align-items: center;
}

.cell-label .req {
  color: var(--error-color);
  margin-left: 4px;
}

.cell-input {
  padding: var(--cell-padding);
  border-right: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 10px;
}
.cell-input:last-child {
  border-right: none;
}

/* ── 입력 요소 (Excel style) ── */
input[type="text"],
input[type="tel"],
select,
textarea,
input[type="file"] {
  width: 100%;
  padding: 6px 10px;
  font-size: 13px;
  border: 1px solid #cccccc;
  border-radius: 2px;
  font-family: inherit;
  transition: border-color 0.2s;
  background-color: #ffffff;
}

input[type="text"]:focus,
input[type="tel"]:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: var(--brand-color);
  box-shadow: 0 0 0 1px var(--brand-color);
}

textarea {
  resize: vertical;
  min-height: 50px;
}

/* ── 데이터 테이블 (메뉴/품목) ── */
.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th, .data-table td {
  border: 1px solid var(--border-color);
  padding: var(--cell-padding);
  text-align: left;
}

.data-table th {
  background-color: var(--header-bg);
  font-weight: 500;
  font-size: 13px;
}

.data-table td input {
  border: none;
  border-bottom: 1px solid transparent;
  padding: 4px;
  width: 100%;
}
.data-table td input:focus {
  border-bottom-color: var(--brand-color);
  box-shadow: none;
  background-color: #f9fbfd;
}

.btn-sm {
  background: transparent;
  border: 1px solid #ccc;
  border-radius: 2px;
  padding: 4px 8px;
  cursor: pointer;
  font-size: 12px;
  color: var(--text-muted);
}
.btn-sm:hover {
  background: #eee;
}

/* ── 버튼 ── */
.actions {
  text-align: right;
  margin-top: 20px;
  border-top: 2px solid var(--brand-color);
  padding-top: 20px;
}

.btn-primary {
  background-color: var(--btn-bg);
  color: #fff;
  border: none;
  padding: 10px 25px;
  font-size: 15px;
  font-weight: 600;
  border-radius: var(--radius);
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover {
  background-color: var(--btn-hover);
}

.btn-primary:disabled {
  background-color: #a0c0d8;
  cursor: not-allowed;
}

/* 라디오 버튼 디자인 */
.radio-group {
  display: flex;
  gap: 15px;
  align-items: center;
}
.radio-label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 13px;
  cursor: pointer;
}

/* 에러 메시지 */
.error-msg {
  color: var(--error-color);
  font-size: 12px;
  display: none;
  margin-top: 4px;
}

/* 모달 */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  z-index: 1000;
  align-items: center;
  justify-content: center;
}
.modal-overlay.show {
  display: flex;
}
.modal-box {
  background: #fff;
  padding: 30px;
  border-radius: var(--radius);
  width: 400px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.modal-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--brand-color);
  margin-bottom: 20px;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 10px;
}
.status-log {
  font-family: monospace;
  font-size: 12px;
  color: var(--text-muted);
  background: #f4f6f9;
  padding: 10px;
  border: 1px solid var(--border-color);
  height: 150px;
  overflow-y: auto;
  margin-top: 15px;
  white-space: pre-wrap;
}
</style>
</head>
<body>

<div class="container">
  <div class="header">
    <h1>전단지(Leaflet) 팩토리 V2</h1>
    <div class="subtitle">Gemini AI 기반 에셋 주도형 전단지 제작 시스템</div>
  </div>

  <!-- 자동 완성 영역 (Magic Form) -->
  <div class="fieldset" style="background-color: #f4fae1; border-color: #cddda3; margin-bottom: 30px;">
    <div class="fieldset-title" style="background-color: #e8f5c8; color: #5a7b1b; border-color: #cddda3; border-bottom: none;">
      ✨ 매직 폼: 자료(사진/문서/텍스트)를 넣으면 정보 자동 완성
    </div>
    <div style="padding: 15px; padding-top: 5px;">
      <p style="font-size: 13px; color: #666; margin-bottom: 12px;">기존 전단지 사진, 문서 파일(txt, hwp 등)을 올리거나 아래에 메모를 붙여넣으시면 AI가 분석하여 폼을 자동으로 채워줍니다.</p>
      
      <div style="display: flex; flex-direction: column; gap: 10px;">
        <textarea id="magicTextInput" placeholder="여기에 행사 내용이나 메뉴판 텍스트를 자유롭게 복사해서 붙여넣으세요..." style="height: 80px; font-size: 13px;"></textarea>
        
        <div style="display: flex; gap: 10px; align-items: center;">
          <input type="file" id="ocrInput" accept="image/*,.txt,.hwp,.docx,.pdf" style="width: 250px; background: white; padding: 4px;">
          <button type="button" class="btn-primary" id="btnOcr" style="background-color: #8db596; padding: 8px 15px; font-size: 13px;">AI 자동 채우기 실행 🪄</button>
          <span id="ocrStatus" style="font-size: 13px; color: #5a7b1b; font-weight: 500; display: none;">AI가 자료를 분석하고 있습니다... ⏳</span>
        </div>
      </div>
    </div>
  </div>

  <form id="leafletForm" enctype="multipart/form-data">

    <!-- 1. 기본 정보 -->
    <div class="fieldset">
      <div class="fieldset-title">기본 정보 (Basic Information)</div>
      
      <div class="grid-table">
        <div class="cell-label">가게명/상호 <span class="req">*</span></div>
        <div class="cell-input">
          <input type="text" id="businessName" name="business_name" required placeholder="예: 맛나식당">
        </div>
        
        <div class="cell-label">업종 <span class="req">*</span></div>
        <div class="cell-input">
          <select name="category" required>
            <option value="">선택</option>
            <option value="음식점">음식점 (한식/중식/일식/양식)</option>
            <option value="카페/베이커리">카페/베이커리</option>
            <option value="학원/교육">학원/교육</option>
            <option value="미용/뷰티">미용/뷰티</option>
            <option value="피트니스">헬스/피트니스</option>
            <option value="기타">기타</option>
          </select>
        </div>
      </div>
      
      <div class="grid-table" style="grid-template-columns: 140px 1fr;">
        <div class="cell-label">특징/강점</div>
        <div class="cell-input">
          <input type="text" name="features" placeholder="예: 30년 전통, 역세권 1분, 친환경 재료 (쉼표로 구분)">
        </div>
      </div>
      
      <div class="grid-table" style="grid-template-columns: 140px 1fr 140px 1fr;">
        <div class="cell-label">전화번호</div>
        <div class="cell-input"><input type="tel" name="phone" placeholder="02-123-4567"></div>
        <div class="cell-label">영업시간</div>
        <div class="cell-input"><input type="text" name="hours" placeholder="매일 10:00 - 22:00"></div>
      </div>
      
      <div class="grid-table" style="grid-template-columns: 140px 1fr;">
        <div class="cell-label">주소</div>
        <div class="cell-input"><input type="text" name="address" placeholder="주소 입력"></div>
      </div>
    </div>

    <!-- 2. 이미지 에셋 (선택적) -->
    <div class="fieldset">
      <div class="fieldset-title">이미지 에셋 (Image Assets - 선택사항)</div>
      
      <div class="grid-table" style="grid-template-columns: 140px 1fr;">
        <div class="cell-label" style="flex-direction: column; align-items: flex-start;">
          <span>참조/사용 이미지</span>
          <span style="font-size:11px; color:#888; font-weight:normal; margin-top:4px;">최대 5장 첨부 가능<br>(JPG, PNG)</span>
        </div>
        <div class="cell-input" style="flex-direction: column; align-items: flex-start; gap: 10px;">
          <input type="file" name="assets[]" multiple accept="image/png, image/jpeg, image/webp" id="fileUpload">
          
          <div class="radio-group">
            <span style="font-size: 13px; font-weight: 500; margin-right: 10px;">이미지 활용 방식:</span>
            <label class="radio-label">
              <input type="radio" name="image_usage" value="ai_generate" checked>
              AI 이미지 자동 생성 (카피 분위기 기반)
            </label>
            <label class="radio-label">
              <input type="radio" name="image_usage" value="reference_only">
              첨부 이미지 참조 (AI가 새 이미지 생성)
            </label>
            <label class="radio-label">
              <input type="radio" name="image_usage" value="use_original">
              첨부 이미지 그대로 사용 (AI 누끼/보정 적용)
            </label>
          </div>
          <div style="font-size: 11px; color: #888;">
            * 이미지를 첨부하지 않으면 AI가 업종 및 카피에 맞춰 이미지를 자동 생성합니다.
          </div>
        </div>
      </div>
    </div>

    <!-- 3. 메뉴 및 품목 데이터 (엑셀 그리드형) -->
    <div class="fieldset">
      <div class="fieldset-title" style="display:flex; justify-content:space-between;">
        <span>메뉴 / 주요 품목 내역</span>
        <button type="button" class="btn-sm" onclick="addMenuRow()">+ 행 추가</button>
      </div>
      <table class="data-table" id="menuTable">
        <thead>
          <tr>
            <th style="width: 40px; text-align: center;">No</th>
            <th style="width: 30%;">품목/메뉴명</th>
            <th style="width: 45%;">상세 설명</th>
            <th style="width: 15%;">가격/조건</th>
            <th style="width: 10%; text-align: center;">삭제</th>
          </tr>
        </thead>
        <tbody id="menuTbody">
          <tr>
            <td style="text-align: center; color: #999;">1</td>
            <td><input type="text" name="item_name[]" placeholder="예: 프리미엄 도시락"></td>
            <td><input type="text" name="item_desc[]" placeholder="신선한 재료로 당일 제작"></td>
            <td><input type="text" name="item_price[]" placeholder="8,000원"></td>
            <td style="text-align: center;"><button type="button" class="btn-sm" onclick="removeRow(this)">X</button></td>
          </tr>
          <tr>
            <td style="text-align: center; color: #999;">2</td>
            <td><input type="text" name="item_name[]"></td>
            <td><input type="text" name="item_desc[]"></td>
            <td><input type="text" name="item_price[]"></td>
            <td style="text-align: center;"><button type="button" class="btn-sm" onclick="removeRow(this)">X</button></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 4. 기획 옵션 -->
    <div class="fieldset">
      <div class="fieldset-title">기획 방향 (Direction)</div>
      
      <div class="grid-table" style="grid-template-columns: 140px 1fr 140px 1fr;">
        <div class="cell-label">전단지 목적</div>
        <div class="cell-input">
          <select name="purpose">
            <option value="홍보/알림">일반 홍보/알림</option>
            <option value="신규오픈">신규 오픈(Grand Open)</option>
            <option value="할인/이벤트">할인/이벤트</option>
            <option value="메뉴소개">메뉴 소개</option>
          </select>
        </div>
        
        <div class="cell-label">타겟 고객 (선택)</div>
        <div class="cell-input"><input type="text" name="target_audience" placeholder="예: 3040 주부"></div>
      </div>
      
      <div class="grid-table" style="grid-template-columns: 140px 1fr;">
        <div class="cell-label">레이아웃 스타일</div>
        <div class="cell-input">
          <select name="layout_style" style="width: 100%;">
            <option value="auto">⭐ 자동 (AI 추천)</option>
            <option value="classic_grid">▦ 클래식 그리드 (안정적인 칸 나누기)</option>
            <option value="hero_focus">▣ 히어로 강조 (큰 메인 이미지 위주)</option>
            <option value="magazine_split">◧ 매거진 분할 (세련된 좌우/상하 분할)</option>
            <option value="bold_typo">𝐁 볼드 타이포 (텍스트와 카피를 압도적으로)</option>
            <option value="side_by_side">◫ 사이드 바이 (이미지와 텍스트의 균형)</option>
          </select>
        </div>
      </div>
    </div>

    <div class="actions">
      <button type="submit" class="btn-primary" id="btnSubmit">AI 전단지 팩토리 가동 🚀</button>
    </div>
  </form>
</div>

<!-- ── Progress Modal ── -->
<div class="modal-overlay" id="progressModal">
  <div class="modal-box">
    <div class="modal-title">전단지 제작 중...</div>
    <div style="font-size:13px; font-weight:500; margin-bottom: 5px;">현재 상태: <span id="statusText" style="color:var(--brand-color);">데이터 전송 중</span></div>
    
    <!-- 프로그레스 바 -->
    <div style="width: 100%; height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
      <div id="progressBar" style="width: 0%; height: 100%; background: var(--brand-color); transition: width 0.5s;"></div>
    </div>
    
    <!-- 실시간 로그 창 (엑셀/터미널 스타일) -->
    <div class="status-log" id="statusLog">요청을 준비하고 있습니다...</div>
  </div>
</div>

<script>
  // --- 매직 폼 (OCR 자동완성) ---
  document.getElementById('btnOcr').addEventListener('click', function() {
    var fileInput = document.getElementById('ocrInput');
    var textInput = document.getElementById('magicTextInput').value.trim();
    
    if (!fileInput.files[0] && textInput === '') {
      alert("전단지 사진, 문서 파일을 선택하거나 텍스트를 입력해주세요.");
      return;
    }
    
    var status = document.getElementById('ocrStatus');
    status.style.display = 'inline';
    this.disabled = true;

    var formData = new FormData();
    if (fileInput.files[0]) {
        formData.append('ocr_image', fileInput.files[0]);
    }
    if (textInput !== '') {
        formData.append('magic_text', textInput);
    }

    fetch('ocr_extract.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      status.style.display = 'none';
      document.getElementById('btnOcr').disabled = false;
      
      if (data.error) {
        alert("분석 실패: " + data.error);
        return;
      }
      
      // 폼 자동 채우기
      if(data.business_name) document.getElementById('businessName').value = data.business_name;
      if(data.features) document.querySelector('input[name="features"]').value = data.features;
      if(data.phone) document.querySelector('input[name="phone"]').value = data.phone;
      if(data.hours) document.querySelector('input[name="hours"]').value = data.hours;
      if(data.address) document.querySelector('input[name="address"]').value = data.address;
      
      // 업종 자동 선택
      if(data.category) {
        var catSelect = document.querySelector('select[name="category"]');
        var found = Array.from(catSelect.options).some(opt => opt.value.includes(data.category) || data.category.includes(opt.value));
        if(found) {
            Array.from(catSelect.options).forEach(opt => {
                if(opt.value.includes(data.category) || data.category.includes(opt.value)) {
                    catSelect.value = opt.value;
                }
            });
        } else {
            catSelect.value = "기타";
        }
      }
      
      // 메뉴(품목) 자동 채우기
      if(data.items && data.items.length > 0) {
        var tbody = document.getElementById('menuTbody');
        tbody.innerHTML = ''; // 기존 빈 행 초기화
        
        data.items.forEach((item, index) => {
          var tr = document.createElement('tr');
          tr.innerHTML = `
            <td style="text-align: center; color: #999;">${index + 1}</td>
            <td><input type="text" name="item_name[]" value="${item.name || ''}"></td>
            <td><input type="text" name="item_desc[]" value="${item.description || ''}"></td>
            <td><input type="text" name="item_price[]" value="${item.price || ''}"></td>
            <td style="text-align: center;"><button type="button" class="btn-sm" onclick="removeRow(this)">X</button></td>
          `;
          tbody.appendChild(tr);
        });
      }
      
      alert("✅ AI가 이미지에서 정보를 성공적으로 추출하여 폼을 채웠습니다!");
    })
    .catch(err => {
      status.style.display = 'none';
      document.getElementById('btnOcr').disabled = false;
      alert("서버 연결에 실패했습니다.");
    });
  });

  // 행 추가 기능
  function addMenuRow() {
    var tbody = document.getElementById('menuTbody');
    var idx = tbody.children.length + 1;
    var tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="text-align: center; color: #999;">${idx}</td>
      <td><input type="text" name="item_name[]"></td>
      <td><input type="text" name="item_desc[]"></td>
      <td><input type="text" name="item_price[]"></td>
      <td style="text-align: center;"><button type="button" class="btn-sm" onclick="removeRow(this)">X</button></td>
    `;
    tbody.appendChild(tr);
    updateRowNumbers();
  }

  // 행 삭제 기능
  function removeRow(btn) {
    var tbody = document.getElementById('menuTbody');
    if (tbody.children.length > 1) {
      btn.closest('tr').remove();
      updateRowNumbers();
    }
  }

  // 번호 업데이트
  function updateRowNumbers() {
    var rows = document.getElementById('menuTbody').children;
    for(var i=0; i<rows.length; i++) {
      rows[i].firstElementChild.textContent = i + 1;
    }
  }

  // 폼 전송
  var form = document.getElementById('leafletForm');
  var btnSubmit = document.getElementById('btnSubmit');
  var modal = document.getElementById('progressModal');
  var pollTimer = null;

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    btnSubmit.disabled = true;
    modal.classList.add('show');
    
    var formData = new FormData(form);

    fetch('generate.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if(data.success) {
        startPolling(data.job_id);
      } else {
        alert("오류: " + data.error);
        modal.classList.remove('show');
        btnSubmit.disabled = false;
      }
    })
    .catch(err => {
      alert("네트워크 오류 발생");
      modal.classList.remove('show');
      btnSubmit.disabled = false;
    });
  });

  function startPolling(jobId) {
    var statusText = document.getElementById('statusText');
    var statusLog = document.getElementById('statusLog');
    var progressBar = document.getElementById('progressBar');
    
    pollTimer = setInterval(function() {
      fetch('status.php?job_id=' + encodeURIComponent(jobId))
      .then(res => res.json())
      .then(data => {
        progressBar.style.width = data.progress + '%';
        statusText.textContent = data.current_step_name || '진행 중...';
        
        // 로그 텍스트 업데이트
        if(data.logs) {
          statusLog.textContent = data.logs.join('\\n');
          statusLog.scrollTop = statusLog.scrollHeight;
        }

        if(data.status === 'completed') {
          clearInterval(pollTimer);
          setTimeout(() => {
            window.location.href = 'result.php?job_id=' + encodeURIComponent(jobId);
          }, 1000);
        } else if(data.status === 'error') {
          clearInterval(pollTimer);
          alert("생성 중 오류 발생: " + data.error_message);
          modal.classList.remove('show');
          btnSubmit.disabled = false;
        }
      });
    }, 2000); // 2초마다 상태 체크
  }
</script>

</body>
</html>
