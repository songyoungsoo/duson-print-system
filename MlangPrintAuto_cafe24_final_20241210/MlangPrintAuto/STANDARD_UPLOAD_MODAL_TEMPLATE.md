# 📎 표준 파일 업로드 모달 템플릿

## 🎯 개요
명함 프로젝트에서 성공한 파일 업로드 모달 시스템을 모든 품목에 표준으로 적용하기 위한 템플릿입니다.

---

## 📋 HTML 템플릿

### 기본 구조
```html
<!-- 파일 업로드 모달 (표준 템플릿) -->
<div id="uploadModal" class="upload-modal" style="display: none;">
    <div class="modal-overlay" onclick="closeUploadModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">📎 [품목명] 디자인 파일 업로드</h3>
            <button type="button" class="modal-close" onclick="closeUploadModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="upload-section">
                <div class="upload-title">📎 파일 업로드</div>
                <div class="upload-container">
                    <div class="upload-left">
                        <label class="upload-label">업로드 방법</label>
                        <div class="upload-buttons">
                            <button type="button" class="btn-upload-method active" data-method="upload" onclick="selectUploadMethod('upload')">
                                📁 파일 업로드
                            </button>
                            <button type="button" class="btn-upload-method" data-method="email" onclick="selectUploadMethod('email')">
                                📧 이메일 전송
                            </button>
                        </div>
                        <div class="upload-area" id="modalUploadArea">
                            <div class="upload-dropzone" id="modalUploadDropzone">
                                <span class="upload-icon">📁</span>
                                <span class="upload-text">파일을 여기에 드래그하거나 클릭하세요</span>
                                <input type="file" id="modalFileInput" accept="[품목별_파일_형식]" multiple hidden>
                            </div>
                            <div class="upload-info">
                                파일첨부 특수문자(#,&,'&',*,%, 등) 사용은 불가능하며 파일명이 길면 오류가 발생하니 되도록 짧고 간단하게 작성해 주세요!<br>
                                지원 형식: [품목별_지원_형식] (최대 [크기]MB)
                            </div>
                        </div>
                    </div>
                    
                    <div class="upload-right">
                        <label class="upload-label">작업메모</label>
                        <textarea id="modalWorkMemo" class="memo-textarea" placeholder="작업 관련 요청사항이나 특별한 지시사항을 입력해주세요.&#10;&#10;예시:&#10;- 색상을 더 진하게 해주세요&#10;- 글자 크기를 조금 더 크게&#10;- 배경색을 파란색으로 변경"></textarea>
                        
                        <div class="upload-notice">
                            <div class="notice-item">📋 택배 무료배송은 결제금액 총 3만원 이상시에 한함</div>
                            <div class="notice-item">📋 당일(익일)주문 전날 주문 제품과 동일 불가</div>
                        </div>
                    </div>
                </div>
                
                <div class="uploaded-files" id="modalUploadedFiles" style="display: none;">
                    <h5>📂 업로드된 파일</h5>
                    <div class="file-list" id="modalFileList"></div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="modal-btn btn-cart" onclick="addToBasketFromModal()" style="max-width: none;">
                🛒 장바구니에 저장
            </button>
        </div>
    </div>
</div>
```

---

## 🎨 CSS 템플릿

### 필수 CSS 클래스들
```css
/* 파일 업로드 모달 시스템 (표준 템플릿) */
.upload-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    animation: fadeInOverlay 0.3s ease-out;
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 20px;
    max-width: 900px;
    max-height: 90vh;
    width: 95%;
    overflow-y: auto;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.upload-section {
    margin-bottom: 20px;
}

.upload-title {
    color: #495057;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 20px;
    border-bottom: 2px solid #28a745;
    padding-bottom: 8px;
}

.upload-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.upload-buttons {
    display: flex;
    gap: 8px;
    margin-bottom: 15px;
}

.btn-upload-method {
    padding: 8px 16px;
    border: 2px solid #28a745;
    background: white;
    color: #28a745;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-upload-method.active {
    background: #28a745;
    color: white;
}

.upload-dropzone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.upload-dropzone:hover,
.upload-dropzone.dragover {
    border-color: #28a745;
    background: #f8fff8;
    transform: scale(1.02);
}

.memo-textarea {
    width: 100%;
    height: 120px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9rem;
    resize: vertical;
    font-family: inherit;
    line-height: 1.4;
}

.uploaded-files {
    margin-top: 15px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    margin-bottom: 8px;
}

.file-remove {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 0.8rem;
    cursor: pointer;
}

/* 반응형 */
@media (max-width: 768px) {
    .upload-container {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}
```

---

## 💻 JavaScript 템플릿

### 필수 함수들
```javascript
// 전역 변수
let uploadedFiles = [];
let selectedUploadMethod = 'upload';

// 모달 열기/닫기
function openUploadModal() {
    if (!currentPriceData) {
        alert('먼저 가격을 계산해주세요.');
        return;
    }
    
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        initializeFileUpload();
    }
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// 업로드 방법 선택
function selectUploadMethod(method) {
    selectedUploadMethod = method;
    
    document.querySelectorAll('.btn-upload-method').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-method="${method}"]`).classList.add('active');
    
    const uploadArea = document.getElementById('modalUploadArea');
    uploadArea.style.display = method === 'upload' ? 'block' : 'none';
}

// 파일 업로드 초기화
function initializeFileUpload() {
    const dropzone = document.getElementById('modalUploadDropzone');
    const fileInput = document.getElementById('modalFileInput');
    
    if (!dropzone || !fileInput) return;
    
    // 드래그 앤 드롭 이벤트
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    
    dropzone.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
}

// 파일 처리
function handleFiles(files) {
    const maxSize = [품목별_최대크기] * 1024 * 1024; // MB
    const allowedTypes = [품목별_허용타입_배열];
    
    Array.from(files).forEach(file => {
        if (file.size > maxSize) {
            alert(`파일 "${file.name}"이 너무 큽니다. 최대 [크기]MB까지 업로드 가능합니다.`);
            return;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert(`파일 "${file.name}"은 지원하지 않는 형식입니다.`);
            return;
        }
        
        uploadedFiles.push({
            file: file,
            name: file.name,
            size: file.size,
            type: file.type
        });
    });
    
    updateFileList();
}

// 파일 목록 업데이트
function updateFileList() {
    const fileList = document.getElementById('modalFileList');
    const uploadedFilesContainer = document.getElementById('modalUploadedFiles');
    
    if (uploadedFiles.length === 0) {
        uploadedFilesContainer.style.display = 'none';
        return;
    }
    
    uploadedFilesContainer.style.display = 'block';
    fileList.innerHTML = '';
    
    uploadedFiles.forEach((fileInfo, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        
        fileItem.innerHTML = `
            <div class="file-info">
                <span class="file-icon">📄</span>
                <div class="file-details">
                    <div class="file-name">${escapeHtml(fileInfo.name)}</div>
                    <div class="file-size">${formatFileSize(fileInfo.size)}</div>
                </div>
            </div>
            <button type="button" class="file-remove" onclick="removeFile(${index})">삭제</button>
        `;
        
        fileList.appendChild(fileItem);
    });
}

// 장바구니 추가
function addToBasketFromModal() {
    if (!currentPriceData) {
        alert('가격 정보가 없습니다. 다시 계산해주세요.');
        return;
    }
    
    const cartButton = document.querySelector('.btn-cart');
    const originalText = cartButton.innerHTML;
    cartButton.innerHTML = '🔄 저장 중...';
    cartButton.disabled = true;
    cartButton.style.opacity = '0.7';
    
    const form = document.getElementById('[품목별_폼_ID]');
    const formData = new FormData(form);
    
    // 가격 정보 추가
    formData.append('calculated_price', currentPriceData.total_price);
    formData.append('calculated_vat_price', currentPriceData.vat_price);
    formData.append('product_type', '[품목명]');
    formData.append('upload_method', selectedUploadMethod);
    formData.append('work_memo', document.getElementById('modalWorkMemo').value);
    
    // 업로드된 파일들 추가
    uploadedFiles.forEach((fileInfo, index) => {
        formData.append(`uploaded_files[${index}]`, fileInfo.file);
    });
    
    fetch('add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const response = JSON.parse(text);
            if (response.success) {
                alert('장바구니에 저장되었습니다! 🛒');
                closeUploadModal();
                if (confirm('장바구니 페이지로 이동하시겠습니까?')) {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                }
            } else {
                throw new Error(response.message || '알 수 없는 오류');
            }
        } catch (parseError) {
            throw new Error('서버 응답 처리 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        alert('장바구니 저장 중 오류가 발생했습니다: ' + error.message);
    })
    .finally(() => {
        cartButton.innerHTML = originalText;
        cartButton.disabled = false;
        cartButton.style.opacity = '1';
    });
}

// 유틸리티 함수들
function removeFile(index) {
    uploadedFiles.splice(index, 1);
    updateFileList();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
```

---

## 🔧 품목별 커스터마이징 가이드

### 1. HTML 커스터마이징
```html
<!-- 품목별 변경 사항 -->
<h3 class="modal-title">📎 [카다록] 디자인 파일 업로드</h3>
<input type="file" accept=".jpg,.jpeg,.png,.pdf,.zip" multiple hidden>
<div class="upload-info">
    지원 형식: JPG, PNG, PDF, ZIP (최대 25MB)
</div>
```

### 2. JavaScript 커스터마이징
```javascript
// 품목별 설정
const maxSize = 25 * 1024 * 1024; // 카다록: 25MB
const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'];

// 폼 ID 변경
const form = document.getElementById('cadarok-quote-form');

// 제품 타입 변경
formData.append('product_type', 'cadarok');
```

### 3. CSS 커스터마이징
```css
/* 품목별 색상 테마 변경 */
.upload-title {
    border-bottom: 2px solid #667eea; /* 카다록: 보라색 */
}

.btn-upload-method {
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-upload-method.active {
    background: #667eea;
}
```

---

## 📋 품목별 적용 체크리스트

### 기본 설정
- [ ] 모달 제목 변경 (품목명)
- [ ] 파일 형식 설정 (accept 속성)
- [ ] 최대 파일 크기 설정
- [ ] 허용 파일 타입 배열 설정
- [ ] 폼 ID 변경
- [ ] 제품 타입 변경

### 스타일링
- [ ] 품목별 색상 테마 적용
- [ ] 업로드 안내 메시지 수정
- [ ] 공지사항 내용 수정

### 기능 테스트
- [ ] 드래그 앤 드롭 동작 확인
- [ ] 파일 크기 제한 테스트
- [ ] 파일 형식 검증 테스트
- [ ] 장바구니 저장 테스트
- [ ] 모바일 반응형 테스트

---

## 🎯 적용 예시

### 카다록 적용
```javascript
// 카다록 전용 설정
const maxSize = 25 * 1024 * 1024; // 25MB
const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'];
formData.append('product_type', 'cadarok');
```

### 전단지 적용
```javascript
// 전단지 전용 설정
const maxSize = 15 * 1024 * 1024; // 15MB
const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'];
formData.append('product_type', 'leaflet');
```

### 포스터 적용
```javascript
// 포스터 전용 설정
const maxSize = 20 * 1024 * 1024; // 20MB
const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'];
formData.append('product_type', 'poster');
```

---

**작성일**: 2025년 8월 14일  
**기반**: 명함 프로젝트 성공 패턴  
**적용 대상**: 모든 품목 페이지  
**유지보수**: 이 템플릿을 기준으로 일관성 유지