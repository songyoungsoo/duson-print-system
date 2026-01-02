/**
 * 통합 갤러리, 업로드, 계산기 스크립트 for sticker_new
 * @version 3.0
 * @date 2025-10-27
 * @description 여러 JS 파일과 인라인 스크립트를 하나로 통합하고 충돌을 해결함.
 */

// =================================================================================
// 1. 전역 변수
// =================================================================================
window.uploadedFiles = [];
window.selectedUploadMethod = 'upload';
window.currentPriceData = null;
let isCalculating = false;
let calculationTimeout = null;
let modalFileUploadInitialized = false;

// =================================================================================
// 2. 유틸리티 함수
// =================================================================================

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function number_format(number) {
    if (isNaN(number)) return '0';
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

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
// 3. 갤러리 관련 함수
// =================================================================================

window.openGalleryPopup = function(category) {
    if (!category) return;
    const width = 1200, height = 800;
    const left = Math.floor((screen.width - width) / 2), top = Math.floor((screen.height - height) / 2);
    const popup = window.open(`/popup/proof_gallery.php?cate=${encodeURIComponent(category)}`, `proof_popup_${category}`, `width=${width},height=${height},scrollbars=yes,resizable=yes,top=${top},left=${left}`);
    if (popup) popup.focus();
    else alert('팝업이 차단되었습니다. 팝업 차단을 해제해주세요.');
}

function initializeStickerZoom() {
    const zoomBox = document.getElementById('stickerZoomBox');
    if (!zoomBox) return;

    const newZoomBox = zoomBox.cloneNode(true);
    zoomBox.parentNode.replaceChild(newZoomBox, zoomBox);

    newZoomBox.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        
        // 마우스 위치를 0과 1 사이의 비율로 계산
        let x_ratio = (e.clientX - rect.left) / rect.width;
        let y_ratio = (e.clientY - rect.top) / rect.height;

        // 컨트롤 영역에 "여유"를 주어 가장자리에 더 쉽게 도달하도록 함
        const margin = 0.1; // 10% 여유
        
        // 비율 재계산
        let scaled_x = (x_ratio - margin) / (1 - 2 * margin);
        let scaled_y = (y_ratio - margin) / (1 - 2 * margin);

        // 값을 0과 1 사이로 유지
        let clamped_x = Math.max(0, Math.min(1, scaled_x));
        let clamped_y = Math.max(0, Math.min(1, scaled_y));

        // 퍼센트로 변환
        const x_pos = clamped_x * 100;
        const y_pos = clamped_y * 100;

        this.style.backgroundSize = '200%';
        this.style.backgroundPosition = `${x_pos}% ${y_pos}%`;
    });

    newZoomBox.addEventListener('mouseleave', function() {
        this.style.backgroundSize = 'contain';
        this.style.backgroundPosition = 'center';
    });

    newZoomBox.addEventListener('click', function() {
        const bgImage = this.style.backgroundImage;
        if (bgImage) {
            openStickerLightbox(bgImage.slice(5, -2));
        }
    });
}

function openStickerLightbox(imagePath) {
    if (typeof EnhancedImageLightbox !== 'undefined') {
        new EnhancedImageLightbox().open([{ src: imagePath, title: '🏷️ 스티커 작품 확대보기' }]);
    } else {
        window.open(imagePath, '_blank');
    }
}

function renderStickerGallery(images, container) {
    container.innerHTML = `<div class="lightbox-viewer zoom-box" id="stickerZoomBox"></div><div class="thumbnail-grid" id="stickerThumbnailGrid"></div>`;
    const thumbnailGrid = document.getElementById('stickerThumbnailGrid');
    if (!thumbnailGrid) return;

    images.forEach((image, index) => {
        const thumbnail = document.createElement('img');
        thumbnail.src = image.image_path;
        thumbnail.alt = image.title || `스티커 작품 ${index + 1}`;
        thumbnail.className = index === 0 ? 'active' : '';
        thumbnail.onclick = () => selectStickerImage(thumbnail, image.image_path);
        thumbnailGrid.appendChild(thumbnail);
    });

    if (images.length > 0) {
        setStickerMainImage(images[0].image_path);
        initializeStickerZoom();
    }
}

function selectStickerImage(thumb, imagePath) {
    document.querySelectorAll('#stickerThumbnailGrid img').forEach(img => img.classList.remove('active'));
    thumb.classList.add('active');
    setStickerMainImage(imagePath);
}

function setStickerMainImage(imagePath) {
    const zoomBox = document.getElementById('stickerZoomBox');
    if (!zoomBox) return;
    zoomBox.style.backgroundImage = `url('${imagePath}')`;
    zoomBox.style.backgroundSize = 'contain';
    zoomBox.style.backgroundPosition = 'center';
    zoomBox.style.backgroundRepeat = 'no-repeat';
    zoomBox.style.cursor = 'zoom-in';
}

// =================================================================================
// 4. 파일 업로드 모달 함수
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
// 5. 스티커 가격 계산 함수
// =================================================================================

function areAllOptionsSelected() {
    const form = document.getElementById('stickerForm');
    return form && form.jong.value && (parseInt(form.garo.value) || 0) > 0 && (parseInt(form.sero.value) || 0) > 0 && form.mesu.value && form.uhyung.value !== '' && form.domusong.value;
}

function autoCalculatePrice() {
    if (!areAllOptionsSelected()) {
        resetPriceDisplay();
        return;
    }
    const formData = new FormData(document.getElementById('stickerForm'));
    fetch('./calculate_price.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) updatePriceDisplay(data);
            else resetPriceDisplay();
        })
        .catch(() => resetPriceDisplay());
}

function updatePriceDisplay(priceData) {
    const amountEl = document.getElementById('priceAmount');
    const detailsEl = document.getElementById('priceDetails');
    const displayEl = document.getElementById('priceDisplay');
    const uploadBtn = document.getElementById('uploadOrderButton');

    if (!amountEl || !detailsEl || !displayEl || !uploadBtn) return;

    amountEl.textContent = priceData.price + '원';
    const printPrice = parseInt(priceData.price.replace(/,/g, '')) - (parseInt(document.getElementById('stickerForm').uhyung.value) || 0);
    detailsEl.innerHTML = `
        <div style="font-size: 0.8rem; margin-top: 6px; color: #6c757d; display: flex; gap: 15px; justify-content: center;">
            <span>인쇄비: ${number_format(printPrice)}원</span>
            <span>공급가격: ${priceData.price}원</span>
            <span>부가세 포함: <span style="color: #dc3545; font-size: 1rem;">${priceData.price_vat}원</span></span>
        </div>`;
    displayEl.classList.add('calculated');
    uploadBtn.style.display = 'block';
    window.currentPriceData = priceData;
}

function resetPriceDisplay() {
    const amountEl = document.getElementById('priceAmount');
    const detailsEl = document.getElementById('priceDetails');
    const displayEl = document.getElementById('priceDisplay');
    const uploadBtn = document.getElementById('uploadOrderButton');

    if(amountEl) amountEl.textContent = '견적 계산 필요';
    if(detailsEl) detailsEl.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
    if(displayEl) displayEl.classList.remove('calculated');
    if(uploadBtn) uploadBtn.style.display = 'none';
    window.currentPriceData = null;
}

// =================================================================================
// 6. 초기화 및 이벤트 리스너
// =================================================================================

document.addEventListener('DOMContentLoaded', function() {
    // 가격 계산 초기화
    const calcForm = document.getElementById('stickerForm');
    if (calcForm) {
        calcForm.querySelectorAll('select, input[type="number"]').forEach(input => {
            input.addEventListener('change', autoCalculatePrice);
            if (input.type === 'number') input.addEventListener('input', debounce(autoCalculatePrice, 300));
        });
    }

    // 갤러리 초기화
    const galleryContainer = document.querySelector('.product-gallery');
    if (galleryContainer) {
        fetch('/api/get_sticker_gallery.php')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    renderStickerGallery(data.data, galleryContainer);
                } else {
                    galleryContainer.innerHTML = '<p>갤러리 이미지를 불러올 수 없습니다.</p>';
                }
            });
    }

    // ESC 키로 모달 닫기
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && document.getElementById('uploadModal').style.display === 'flex') {
            closeUploadModal();
        }
    });
});
