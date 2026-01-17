/**
 * 공통 파일 업로드 모달 JavaScript
 * 모든 MlangPrintAuto 품목에서 공통으로 사용
 * 
 * @version 1.0
 * @date 2025-01-08
 */

// 전역 변수 (window 객체에 명시적으로 할당)
window.uploadedFiles = [];
window.selectedUploadMethod = 'upload';

/**
 * 업로드 모달 열기 (전역 함수)
 */
window.openUploadModal = function() {
    console.log('openUploadModal 호출됨');
    
    // 로그인 체크
    if (!isLoggedIn()) {
        openLoginModal();
        return;
    }
    
    const modal = document.getElementById('uploadModal');
    if (!modal) {
        console.error('uploadModal을 찾을 수 없습니다.');
        return;
    }
    
    console.log('모달 열기:', modal);
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // 모달 내 파일 업로드 초기화
    console.log('파일 업로드 초기화 시작');
    window.initializeModalFileUpload();
    
    // 가격 정보 업데이트 (각 제품별 함수가 있다면 호출)
    if (typeof updateModalPrice === 'function') {
        updateModalPrice();
    }
    
    console.log('모달 열기 완료');
}

/**
 * 업로드 모달 닫기 (전역 함수)
 */
window.closeUploadModal = function() {
    const modal = document.getElementById('uploadModal');
    if (!modal) return;
    
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // 업로드된 파일 초기화
    window.uploadedFiles = [];
    window.updateModalFileList();
    
    // 작업메모 초기화
    const workMemo = document.getElementById('modalWorkMemo');
    if (workMemo) {
        workMemo.value = '';
    }
}

/**
 * 업로드 방법 선택 (전역 함수)
 */
window.selectUploadMethod = function(method) {
    window.selectedUploadMethod = method;
    const buttons = document.querySelectorAll('.btn-upload-method');
    buttons.forEach(btn => btn.classList.remove('active'));

    if (event && event.target) {
        event.target.classList.add('active');
    }

    console.log('selectUploadMethod 호출됨:', method);

    // 파일업로드 버튼 클릭 시 파일 선택 다이얼로그 열기
    if (method === 'upload') {
        const fileInput = document.getElementById('modalFileInput');
        if (fileInput) {
            console.log('파일 선택 다이얼로그 열기');
            fileInput.click();
        }
    }
}

/**
 * 모달 파일 업로드 초기화 (전역 함수)
 */
window.initializeModalFileUpload = function() {
    console.log('initializeModalFileUpload 시작');
    
    const dropzone = document.getElementById('modalUploadDropzone');
    const fileInput = document.getElementById('modalFileInput');
    
    console.log('찾은 요소들:', {dropzone, fileInput});
    
    if (!dropzone || !fileInput) {
        console.error('업로드 요소를 찾을 수 없습니다:', {
            dropzone: dropzone ? '있음' : '없음',
            fileInput: fileInput ? '있음' : '없음'
        });
        return;
    }
    
    // 기존 이벤트 리스너 제거 (중복 방지)
    if (dropzone._uploadInitialized) {
        console.log('이미 초기화된 드롭존, 건너뛰기');
        return;
    }
    dropzone._uploadInitialized = true;
    
    // 드래그 앤 드롭 이벤트
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    
    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        
        const files = Array.from(e.dataTransfer.files);
        window.processFiles(files);
    });
    
    // 클릭으로 파일 선택
    dropzone.addEventListener('click', function() {
        console.log('드롭존 클릭됨, 파일 입력 클릭 실행');
        fileInput.click();
    });
    
    // 파일 입력 변경 이벤트
    fileInput.addEventListener('change', function(e) {
        console.log('파일 선택됨:', e.target.files);
        const files = Array.from(e.target.files);
        window.processFiles(files);
    });
    
    console.log('파일 업로드 초기화 완료 - 이벤트 리스너 설정됨');
}

/**
 * 파일 처리 (전역 함수)
 */
window.processFiles = function(files) {
    console.log('📁 processFiles 호출됨, 파일 수:', files.length);
    console.log('📁 파일 목록:', files);
    
    const allowedTypes = ['.jpg', '.jpeg', '.png', '.pdf', '.ai', '.eps', '.psd', '.zip'];
    
    files.forEach(file => {
        console.log('📄 파일 처리 중:', file.name, file.size);
        // 파일 크기 체크 (15MB)
        if (file.size > 15 * 1024 * 1024) {
            alert(`파일 "${file.name}"이 너무 큽니다. 15MB 이하의 파일을 선택해주세요.`);
            return;
        }
        
        // 파일 타입 체크
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(fileExtension)) {
            alert(`파일 "${file.name}"은 지원하지 않는 형식입니다. JPG, PNG, PDF, AI, EPS, PSD, ZIP 파일만 업로드 가능합니다.`);
            return;
        }
        
        // 중복 파일 체크
        const existingFile = window.uploadedFiles.find(f => f.name === file.name && f.size === file.size);
        if (existingFile) {
            alert(`파일 "${file.name}"은 이미 업로드되었습니다.`);
            return;
        }
        
        // 파일 객체 생성
        const fileObj = {
            id: Date.now() + '_' + Math.random().toString(36).substr(2, 9),
            file: file,
            name: file.name,
            size: formatFileSize(file.size),
            type: fileExtension
        };
        
        window.uploadedFiles.push(fileObj);
        console.log('✅ 파일 추가됨:', fileObj.name, '현재 총', window.uploadedFiles.length, '개');
    });
    
    console.log('📦 최종 uploadedFiles:', window.uploadedFiles);
    window.updateModalFileList();
}

/**
 * 파일 목록 업데이트 (전역 함수)
 */
window.updateModalFileList = function() {
    const fileList = document.getElementById('modalFileList');
    const uploadedFilesContainer = document.getElementById('modalUploadedFiles');
    
    if (!fileList || !uploadedFilesContainer) return;
    
    if (window.uploadedFiles.length === 0) {
        uploadedFilesContainer.style.display = 'none';
        return;
    }
    
    uploadedFilesContainer.style.display = 'block';
    fileList.innerHTML = '';
    
    window.uploadedFiles.forEach(fileObj => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <div class="file-info">
                <span class="file-icon">${getFileIcon(fileObj.type)}</span>
                <div class="file-details">
                    <div class="file-name">${escapeHtml(fileObj.name)}</div>
                    <div class="file-size">${fileObj.size}</div>
                </div>
            </div>
            <button class="file-remove" onclick="removeFile('${fileObj.id}')">삭제</button>
        `;
        fileList.appendChild(fileItem);
    });
}

/**
 * 파일 제거 (전역 함수)
 */
window.removeFile = function(fileId) {
    window.uploadedFiles = window.uploadedFiles.filter(f => f.id !== fileId);
    window.updateModalFileList();
}

/**
 * 파일 아이콘 반환
 */
function getFileIcon(fileType) {
    const icons = {
        '.jpg': '🖼️', '.jpeg': '🖼️', '.png': '🖼️',
        '.pdf': '📄', '.ai': '🎨', '.eps': '🎨', 
        '.psd': '🖌️', '.zip': '📦'
    };
    return icons[fileType] || '📄';
}

/**
 * 파일 크기 포맷
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * HTML 이스케이프
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * 로그인 상태 체크 (각 제품별로 구현되어 있을 수 있음)
 */
function isLoggedIn() {
    // 기본 구현 - 각 제품에서 오버라이드 가능
    if (typeof checkLoginStatus === 'function') {
        return checkLoginStatus();
    }
    
    // 기본적으로 세션 체크
    return document.cookie.includes('PHPSESSID');
}

/**
 * 로그인 모달 열기 (각 제품별로 구현되어 있을 수 있음)
 */
function openLoginModal() {
    if (typeof showLoginModal === 'function') {
        showLoginModal();
    } else {
        alert('로그인이 필요합니다.');
    }
}

/**
 * 모달에서 장바구니에 추가 (전역 함수)
 */
window.addToBasketFromModal = function() {
    console.log('addToBasketFromModal 호출됨');
    console.log('handleModalBasketAdd 타입:', typeof handleModalBasketAdd);

    // 로딩 스피너 표시
    if (typeof showDusonLoading === 'function') {
        showDusonLoading('장바구니에 담는 중...');
    }

    // 각 제품별 장바구니 추가 함수 호출 (파일 업로드는 선택사항)
    if (typeof handleModalBasketAdd === 'function') {
        console.log('handleModalBasketAdd 함수 발견, 호출 시작');
        // 성공 콜백 함수 정의
        const onSuccess = function() {
            console.log('장바구니 저장 성공 - 장바구니 페이지로 이동');

            // 로딩 스피너 숨김
            if (typeof hideDusonLoading === 'function') {
                hideDusonLoading();
            }

            // 짧은 성공 메시지 표시
            const cartButton = document.querySelector('.btn-cart');
            if (cartButton) {
                cartButton.innerHTML = '✅ 저장완료';
                cartButton.style.backgroundColor = '#28a745';
            }

            // 1초 후 장바구니 페이지로 이동
            setTimeout(function() {
                window.location.href = window.location.origin + '/mlangprintauto/shop/cart.php';
            }, 1000);
        };

        const onError = function(errorMessage) {
            console.error('장바구니 저장 실패:', errorMessage);

            // 로딩 스피너 숨김
            if (typeof hideDusonLoading === 'function') {
                hideDusonLoading();
            }

            alert('장바구니 저장에 실패했습니다: ' + (errorMessage || '알 수 없는 오류'));

            // 버튼 상태 복원
            const cartButton = document.querySelector('.btn-cart');
            if (cartButton) {
                cartButton.innerHTML = '🛒 장바구니에 저장';
                cartButton.disabled = false;
                cartButton.style.opacity = '1';
                cartButton.style.backgroundColor = '';
            }
        };

        // 제품별 함수 호출 (성공/실패 콜백 전달)
        handleModalBasketAdd(window.uploadedFiles, onSuccess, onError);
    } else {
        // 로딩 스피너 숨김
        if (typeof hideDusonLoading === 'function') {
            hideDusonLoading();
        }
        console.error('handleModalBasketAdd 함수가 정의되지 않았습니다.');
        alert('장바구니 추가 기능을 사용할 수 없습니다.');
    }
}

// 페이지 로드 완료 후 초기화
document.addEventListener('DOMContentLoaded', function() {
    // ESC 키로 모달 닫기
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('uploadModal');
            if (modal && modal.style.display === 'flex') {
                window.closeUploadModal();
            }
        }
    });
});/* Cache buster: 1759617001 */
