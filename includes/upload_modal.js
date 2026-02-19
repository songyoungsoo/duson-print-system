// upload_modal.js v2.0 — 완성파일 업로드 / 디자인 의뢰 두 모드 지원

window.uploadedFiles = [];
window.selectedUploadMethod = 'upload'; // 'upload' | 'design'
window.designPhase = 'intro'; // 'intro' | 'upload' (design mode only)

var UPLOAD_MODES = {
    upload: {
        label: '인쇄용 파일 첨부',
        memoLabel: '작업메모',
        dropzoneIcon: '📁',
        dropzoneText: '파일을 여기에 드래그하거나 클릭하세요',
        dropzoneFormats: 'JPG, PNG, PDF, AI, EPS, PSD, ZIP (15MB 이하)',
        accept: '.jpg,.jpeg,.png,.pdf,.ai,.eps,.psd,.zip',
        memoPlaceholder: '특별한 요청사항이 있으시면 입력해주세요...\n\n예: 색상 조정, 크기 변경, 레이아웃 수정 등'
    },
    design: {
        label: '원고·참고자료 첨부',
        memoLabel: '디자인 요청사항',
        dropzoneIcon: '📋',
        dropzoneText: '원고파일을 드래그하거나 클릭하여 업로드',
        dropzoneFormats: 'HWP, 엑셀, PPT, 워드, 이미지, PDF, AI, PSD, TXT, ZIP 등',
        accept: '.jpg,.jpeg,.png,.pdf,.ai,.eps,.psd,.zip,.hwp,.xlsx,.xls,.pptx,.ppt,.doc,.docx,.txt,.rtf,.csv',
        memoPlaceholder: '디자인 요청사항을 상세히 적어주세요\n\n' +
            '■ 디자인 컨셉 (예: 깔끔하고 모던한 느낌)\n' +
            '■ 주요 내용 (예: 회사명, 연락처, 주소)\n' +
            '■ 색상 선호 (예: 파란색 계열, 색상코드 #1E4E79)\n' +
            '■ 참고 디자인 URL이나 이미지\n' +
            '■ 기타 요청사항 (예: 뒷면에 약도 배치)'
    }
};

// === 모달 열기/닫기 ===

window.openUploadModal = function() {
    if (!isLoggedIn()) {
        openLoginModal();
        return;
    }

    var modal = document.getElementById('uploadModal');
    if (!modal) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    switchMode('upload');
    window.initializeModalFileUpload();

    if (typeof updateModalPrice === 'function') {
        updateModalPrice();
    }
};

window.closeUploadModal = function() {
    var modal = document.getElementById('uploadModal');
    if (!modal) return;

    modal.style.display = 'none';
    document.body.style.overflow = 'auto';

    window.uploadedFiles = [];
    window.updateModalFileList();

    var workMemo = document.getElementById('modalWorkMemo');
    if (workMemo) workMemo.value = '';

    var introMemo = document.getElementById('designIntroMemo');
    if (introMemo) introMemo.value = '';
};

// === 핵심: 모드 전환 ===

function switchMode(mode, phase) {
    window.selectedUploadMethod = mode;
    var cfg = UPLOAD_MODES[mode];
    if (!cfg) return;

    var buttons = document.querySelectorAll('.btn-upload-method');
    buttons.forEach(function(btn) { btn.classList.remove('active'); });
    var activeIndex = (mode === 'upload') ? 0 : 1;
    if (buttons[activeIndex]) buttons[activeIndex].classList.add('active');

    var introPanel = document.getElementById('designIntroPanel');
    var uploadContainer = document.getElementById('uploadContainer');
    var footer = document.querySelector('#uploadModal .modal-footer');

    if (mode === 'design' && phase === 'intro') {
        window.designPhase = 'intro';
        if (introPanel) introPanel.style.display = '';
        if (uploadContainer) uploadContainer.style.display = 'none';
        if (footer) footer.style.display = 'none';
        return;
    }

    window.designPhase = (mode === 'design') ? 'upload' : 'intro';
    if (introPanel) introPanel.style.display = 'none';
    if (uploadContainer) uploadContainer.style.display = '';
    if (footer) footer.style.display = '';

    setText('uploadLeftLabel', cfg.label);
    setText('dropzoneIcon', cfg.dropzoneIcon);
    setText('dropzoneText', cfg.dropzoneText);
    setText('dropzoneFormats', cfg.dropzoneFormats);

    var fileInput = document.getElementById('modalFileInput');
    if (fileInput) fileInput.setAttribute('accept', cfg.accept);

    setText('uploadRightLabel', cfg.memoLabel);
    var memo = document.getElementById('modalWorkMemo');
    if (memo) memo.placeholder = cfg.memoPlaceholder;

    toggle('guideUpload', mode === 'upload');
    toggle('guideDesign', mode === 'design');
    toggle('noticeUpload', mode === 'upload');
    toggle('noticeDesign', mode === 'design');
    toggle('uploadInfoDesign', mode === 'design');

    var dropzone = document.getElementById('modalUploadDropzone');
    if (dropzone) {
        dropzone.classList.toggle('dropzone-design', mode === 'design');
    }
}

window.selectUploadMethod = function(method) {
    if (method === 'design') {
        switchMode('design', 'intro');
    } else {
        switchMode('upload');
        var fileInput = document.getElementById('modalFileInput');
        if (fileInput) fileInput.click();
    }
};

window.proceedToDesignUpload = function() {
    var introMemo = document.getElementById('designIntroMemo');
    var workMemo = document.getElementById('modalWorkMemo');
    if (introMemo && workMemo && introMemo.value.trim()) {
        workMemo.value = introMemo.value;
    }
    switchMode('design', 'upload');
    window.initializeModalFileUpload();
};

// === 파일 업로드 초기화 ===

window.initializeModalFileUpload = function() {
    var dropzone = document.getElementById('modalUploadDropzone');
    var fileInput = document.getElementById('modalFileInput');
    if (!dropzone || !fileInput) return;

    if (dropzone._uploadInitialized) return;
    dropzone._uploadInitialized = true;

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
        window.processFiles(Array.from(e.dataTransfer.files));
    });

    dropzone.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function(e) {
        window.processFiles(Array.from(e.target.files));
        e.target.value = '';
    });
};

// === 파일 처리 ===

window.processFiles = function(files) {
    var mode = window.selectedUploadMethod;
    var allowedUpload = ['.jpg','.jpeg','.png','.pdf','.ai','.eps','.psd','.zip'];
    var allowedDesign = ['.jpg','.jpeg','.png','.pdf','.ai','.eps','.psd','.zip',
                         '.hwp','.xlsx','.xls','.pptx','.ppt','.doc','.docx','.txt','.rtf','.csv'];
    var allowed = (mode === 'design') ? allowedDesign : allowedUpload;
    var maxSize = 15 * 1024 * 1024;

    files.forEach(function(file) {
        if (file.size > maxSize) {
            alert('파일 "' + file.name + '"이 너무 큽니다. 15MB 이하의 파일을 선택해주세요.');
            return;
        }

        var ext = '.' + file.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            var typeMsg = (mode === 'design')
                ? 'HWP, 엑셀, PPT, 워드, 이미지, PDF, AI, PSD, TXT, ZIP'
                : 'JPG, PNG, PDF, AI, EPS, PSD, ZIP';
            alert('파일 "' + file.name + '"은 지원하지 않는 형식입니다.\n허용: ' + typeMsg);
            return;
        }

        if (window.uploadedFiles.find(function(f) { return f.name === file.name && f.size === file.size; })) {
            alert('파일 "' + file.name + '"은 이미 업로드되었습니다.');
            return;
        }

        window.uploadedFiles.push({
            id: Date.now() + '_' + Math.random().toString(36).substr(2, 9),
            file: file,
            name: file.name,
            size: formatFileSize(file.size),
            type: ext
        });
    });

    window.updateModalFileList();
};

// === 파일 목록 렌더링 ===

window.updateModalFileList = function() {
    var fileList = document.getElementById('modalFileList');
    var container = document.getElementById('modalUploadedFiles');
    if (!fileList || !container) return;

    if (window.uploadedFiles.length === 0) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';
    fileList.innerHTML = '';

    var imageExts = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

    window.uploadedFiles.forEach(function(fileObj) {
        var item = document.createElement('div');
        var isImage = imageExts.includes(fileObj.type);

        if (isImage) {
            item.className = 'file-item file-item-image';
            var thumbUrl = URL.createObjectURL(fileObj.file);
            item.innerHTML =
                '<div class="file-thumb-wrap">' +
                    '<img src="' + thumbUrl + '" alt="' + escapeHtml(fileObj.name) + '" class="file-thumb" ' +
                        'onclick="window.previewUploadedImage(this.src, \'' + escapeHtml(fileObj.name) + '\')">' +
                '</div>' +
                '<div class="file-info">' +
                    '<div class="file-details">' +
                        '<div class="file-name">' + escapeHtml(fileObj.name) + '</div>' +
                        '<div class="file-size">' + fileObj.size + '</div>' +
                    '</div>' +
                '</div>' +
                '<button class="file-remove" onclick="removeFile(\'' + fileObj.id + '\')">삭제</button>';
        } else {
            item.className = 'file-item';
            item.innerHTML =
                '<div class="file-info">' +
                    '<span class="file-icon">' + getFileIcon(fileObj.type) + '</span>' +
                    '<div class="file-details">' +
                        '<div class="file-name">' + escapeHtml(fileObj.name) + '</div>' +
                        '<div class="file-size">' + fileObj.size + '</div>' +
                    '</div>' +
                '</div>' +
                '<button class="file-remove" onclick="removeFile(\'' + fileObj.id + '\')">삭제</button>';
        }

        fileList.appendChild(item);
    });
};

window.previewUploadedImage = function(src, name) {
    var overlay = document.createElement('div');
    overlay.className = 'file-preview-overlay';
    overlay.onclick = function() { overlay.remove(); };
    overlay.innerHTML =
        '<div class="file-preview-container">' +
            '<div class="file-preview-header">' +
                '<span>' + escapeHtml(name) + '</span>' +
                '<button onclick="this.closest(\'.file-preview-overlay\').remove()">✕</button>' +
            '</div>' +
            '<img src="' + src + '" alt="' + escapeHtml(name) + '">' +
        '</div>';
    document.body.appendChild(overlay);
};

window.removeFile = function(fileId) {
    window.uploadedFiles = window.uploadedFiles.filter(function(f) { return f.id !== fileId; });
    window.updateModalFileList();
};

// === 장바구니 추가 ===

window.addToBasketFromModal = function() {
    if (typeof showDusonLoading === 'function') {
        showDusonLoading('장바구니에 담는 중...');
    }

    if (typeof handleModalBasketAdd === 'function') {
        var onSuccess = function() {
            if (typeof hideDusonLoading === 'function') hideDusonLoading();
            window.location.href = window.location.origin + '/mlangprintauto/shop/cart.php';
        };

        var onError = function(errorMessage) {
            if (typeof hideDusonLoading === 'function') hideDusonLoading();
            alert('장바구니 저장에 실패했습니다: ' + (errorMessage || '알 수 없는 오류'));

            var cartButton = document.querySelector('.btn-cart');
            if (cartButton) {
                cartButton.innerHTML = '🛒 장바구니에 저장';
                cartButton.disabled = false;
                cartButton.style.opacity = '1';
                cartButton.style.backgroundColor = '';
            }
        };

        handleModalBasketAdd(window.uploadedFiles, onSuccess, onError);
    } else {
        if (typeof hideDusonLoading === 'function') hideDusonLoading();
        alert('장바구니 추가 기능을 사용할 수 없습니다.');
    }
};

// === 유틸리티 ===

function setText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text;
}

function toggle(id, show) {
    var el = document.getElementById(id);
    if (el) el.style.display = show ? '' : 'none';
}

function getFileIcon(fileType) {
    var icons = {
        '.jpg': '🖼️', '.jpeg': '🖼️', '.png': '🖼️', '.gif': '🖼️',
        '.pdf': '📄', '.ai': '🎨', '.eps': '🎨', '.psd': '🖌️',
        '.zip': '📦', '.hwp': '📝', '.doc': '📝', '.docx': '📝',
        '.xls': '📊', '.xlsx': '📊', '.csv': '📊',
        '.ppt': '📰', '.pptx': '📰', '.txt': '📃', '.rtf': '📃'
    };
    return icons[fileType] || '📄';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function isLoggedIn() {
    if (typeof checkLoginStatus === 'function') return checkLoginStatus();
    return document.cookie.includes('PHPSESSID');
}

function openLoginModal() {
    if (typeof showLoginModal === 'function') {
        showLoginModal();
    } else {
        alert('로그인이 필요합니다.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var modal = document.getElementById('uploadModal');
            if (modal && modal.style.display === 'flex') {
                window.closeUploadModal();
            }
        }
    });

    window.selectUploadMethod = function(method) {
        if (method === 'design') {
            switchMode('design', 'intro');
        } else {
            switchMode('upload');
            var fileInput = document.getElementById('modalFileInput');
            if (fileInput) fileInput.click();
        }
    };
});
