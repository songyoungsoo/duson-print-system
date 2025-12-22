/**
 * 공통 스크립트: 파일 업로드, 갤러리 팝업, 공통 유틸리티
 * @version 1.0
 * @date 2025-10-27
 */

// =================================================================================
// 1. 전역 변수
// =================================================================================
window.uploadedFiles = [];
window.selectedUploadMethod = 'upload';
window.currentPriceData = null;
let modalFileUploadInitialized = false;

// =================================================================================
// 2. 유틸리티 함수
// =================================================================================

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getFileIcon(fileType) {
    const icons = { '.jpg': '🖼️', '.jpeg': '🖼️', '.png': '🖼️', '.pdf': '📄', '.ai': '🎨', '.eps': '🎨', '.psd': '🖌️', '.zip': '📦' };
    return icons[fileType] || '📄';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024, sizes = ['Bytes', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function isLoggedIn() {
    if (typeof checkLoginStatus === 'function') return checkLoginStatus();
    return document.cookie.includes('PHPSESSID');
}

function showUserMessage(message, type = 'info') {
    alert(message);
}

// =================================================================================
// 3. 공통 갤러리 팝업 함수
// =================================================================================

window.openGalleryPopup = function(category) {
    if (!category) return;
    const width = 1200, height = 800;
    const left = Math.floor((screen.width - width) / 2), top = Math.floor((screen.height - height) / 2);
    const popup = window.open(`/popup/proof_gallery.php?cate=${encodeURIComponent(category)}`, `proof_popup_${category}`, `width=${width},height=${height},scrollbars=yes,resizable=yes,top=${top},left=${left}`);
    if (popup) popup.focus();
    else alert('팝업이 차단되었습니다. 팝업 차단을 해제해주세요.');
}

// =================================================================================
// 4. 공통 파일 업로드 모달 함수
// =================================================================================

window.openUploadModal = function() {
    if (!isLoggedIn()) {
        if(typeof openLoginModal === 'function') openLoginModal();
        return;
    }
    if (!window.currentPriceData) {
        showUserMessage('먼저 가격을 계산해주세요.', 'warning');
        return;
    }
    const modal = document.getElementById('uploadModal');
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    if (!modalFileUploadInitialized) {
        initializeModalFileUpload();
        modalFileUploadInitialized = true;
    }
}

window.closeUploadModal = function() {
    const modal = document.getElementById('uploadModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    window.uploadedFiles = [];
    updateModalFileList();
    const workMemo = document.getElementById('modalWorkMemo');
    if (workMemo) workMemo.value = '';
}

function initializeModalFileUpload() {
    const dropzone = document.getElementById('modalUploadDropzone');
    const fileInput = document.getElementById('modalFileInput');
    if (!dropzone || !fileInput || dropzone._uploadInitialized) return;
    dropzone._uploadInitialized = true;
    
    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
    dropzone.addEventListener('dragleave', e => { e.preventDefault(); dropzone.classList.remove('drag-over'); });
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        processFiles(Array.from(e.dataTransfer.files));
    });
    
    dropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', e => {
        processFiles(Array.from(e.target.files));
        e.target.value = '';
    });
}

function processFiles(files) {
    const allowedTypes = ['.jpg', '.jpeg', '.png', '.pdf', '.ai', '.eps', '.psd', '.zip'];
    files.forEach(file => {
        if (file.size > 15 * 1024 * 1024) return alert(`파일 "${file.name}"이 너무 큽니다. 15MB 이하만 가능합니다.`);
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExtension)) return alert(`"${file.name}"은(는) 지원하지 않는 형식입니다.`);
        if (window.uploadedFiles.find(f => f.name === file.name && f.size === file.size)) return alert(`"${file.name}"은(는) 이미 추가된 파일입니다.`);
        
        window.uploadedFiles.push({ id: Date.now(), file: file, name: file.name, size: formatFileSize(file.size), type: fileExtension });
    });
    updateModalFileList();
}

function updateModalFileList() {
    const fileList = document.getElementById('modalFileList');
    const container = document.getElementById('modalUploadedFiles');
    if (!fileList || !container) return;
    
    container.style.display = window.uploadedFiles.length > 0 ? 'block' : 'none';
    fileList.innerHTML = window.uploadedFiles.map(fileObj => `
        <div class="file-item" id="file-${fileObj.id}">
            <div class="file-info">
                <span class="file-icon">${getFileIcon(fileObj.type)}</span>
                <div class="file-details">
                    <div class="file-name">${escapeHtml(fileObj.name)}</div>
                    <div class="file-size">${fileObj.size}</div>
                </div>
            </div>
            <button type="button" class="file-remove" onclick="removeFile(${fileObj.id})">삭제</button>
        </div>`).join('');
}

window.removeFile = function(fileId) {
    window.uploadedFiles = window.uploadedFiles.filter(f => f.id !== fileId);
    updateModalFileList();
}

// =================================================================================
// 5. DOMContentLoaded 이벤트 리스너
// =================================================================================

document.addEventListener('DOMContentLoaded', function() {
    // ESC 키로 업로드 모달 닫기
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && document.getElementById('uploadModal')?.style.display === 'flex') {
            closeUploadModal();
        }
    });

    // "샘플 더보기" 버튼에 갤러리 팝업 자동 바인딩
    const categoryMap = {
        'namecard': '명함', 'sticker': '스티커', 'sticker_new': '스티커', 'envelope': '봉투',
        'inserted': '전단지', 'littleprint': '포스터', 'cadarok': '카탈로그', 
        'merchandisebond': '상품권', 'msticker': '자석스티커', 'ncrflambeau': '양식지'
    };
    document.querySelectorAll('.gallery-more-thumb').forEach(button => {
        if (!button.onclick) {
            const product = button.getAttribute('data-product');
            if (product) {
                const category = categoryMap[product] || product;
                button.onclick = e => {
                    e.preventDefault();
                    e.stopPropagation();
                    window.openGalleryPopup(category);
                };
            }
        }
    });
});
