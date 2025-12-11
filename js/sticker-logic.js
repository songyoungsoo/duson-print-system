/**
 * 스티커 페이지 전용 스크립트
 * @version 1.0
 * @date 2025-10-27
 */

// =================================================================================
// 1. 스티커 갤러리 및 줌 기능
// =================================================================================

function initializeStickerZoom() {
    const zoomBox = document.getElementById('stickerZoomBox');
    if (!zoomBox) return;

    const newZoomBox = zoomBox.cloneNode(true);
    zoomBox.parentNode.replaceChild(newZoomBox, zoomBox);

    newZoomBox.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        let x_ratio = (e.clientX - rect.left) / rect.width;
        let y_ratio = (e.clientY - rect.top) / rect.height;
        const margin = 0.1;
        let scaled_x = (x_ratio - margin) / (1 - 2 * margin);
        let scaled_y = (y_ratio - margin) / (1 - 2 * margin);
        let clamped_x = Math.max(0, Math.min(1, scaled_x));
        let clamped_y = Math.max(0, Math.min(1, scaled_y));
        this.style.backgroundSize = '200%';
        this.style.backgroundPosition = `${clamped_x * 100}% ${clamped_y * 100}%`;
    });

    newZoomBox.addEventListener('mouseleave', function() {
        this.style.backgroundSize = 'contain';
        this.style.backgroundPosition = 'center';
    });

    newZoomBox.addEventListener('click', function() {
        const bgImage = this.style.backgroundImage;
        if (bgImage) openStickerLightbox(bgImage.slice(5, -2));
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
// 2. 스티커 가격 계산 함수
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
    const editFee = parseInt(document.getElementById('stickerForm').uhyung.value) || 0;
    const printPrice = parseInt(priceData.price.replace(/,/g, '')) - editFee;
    
    let detailsHTML = `<div style="font-size: 0.8rem; margin-top: 6px; color: #6c757d; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <span>인쇄비: ${number_format(printPrice)}원</span>`;
    if (editFee > 0) {
        detailsHTML += `<span>편집비: ${number_format(editFee)}원</span>`;
    }
    detailsHTML += `<span>공급가격: ${priceData.price}원</span>
        <span>부가세 포함: <span style="color: #dc3545; font-size: 1rem;">${priceData.price_vat}원</span></span></div>`;
    
    detailsEl.innerHTML = detailsHTML;
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
// 3. 초기화
// =================================================================================

document.addEventListener('DOMContentLoaded', function() {
    // 가격 계산 초기화
    const calcForm = document.getElementById('stickerForm');
    if (calcForm) {
        const debouncedCalc = debounce(autoCalculatePrice, 300);
        calcForm.querySelectorAll('select, input[type="number"]').forEach(input => {
            input.addEventListener('change', autoCalculatePrice);
            if (input.type === 'number') input.addEventListener('input', debouncedCalc);
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
            })
            .catch(err => {
                console.error('갤러리 로딩 오류:', err);
                galleryContainer.innerHTML = '<p>갤러리 로딩 중 오류가 발생했습니다.</p>';
            });
    }
});
