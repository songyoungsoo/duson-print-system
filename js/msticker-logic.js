/**
 * 자석스티커 페이지 전용 스크립트
 * - GalleryLightbox.js
 * - msticker.js
 * - UniversalFileUpload.js
 * - unified-gallery-popup.js
 * - index.php의 인라인 스크립트 통합
 * @version 1.0
 * @date 2025-10-27
 */

// =================================================================================
// 1. 전역 변수 (common-unified.js와 중복되지 않는 고유 변수)
// =================================================================================
let currentPriceData = null; // common-unified.js의 window.currentPriceData와 별개로 관리

// 갤러리 관련 변수들
let currentX = 50;
let currentY = 50;
let currentSize = 100;
let targetX = 50;
let targetY = 50; 
let targetSize = 100;
let originalBackgroundSize = 'contain';
let currentImageType = 'large'; // 'small' or 'large'
let animationId = null;

let mstickerCurrentPage = 1;
let mstickerPaginationData = null;

// =================================================================================
// 2. 유틸리티 함수 (common-unified.js와 중복되지 않는 고유 유틸리티)
// =================================================================================

function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function restoreButton(button, originalText) {
    if(button) {
        button.innerHTML = originalText;
        button.disabled = false;
        button.style.opacity = '1';
    }
}

// =================================================================================
// 3. 고급 이미지 갤러리 시스템 (적응형 이미지 분석 및 부드러운 애니메이션) - GalleryLightbox Class
// =================================================================================

class GalleryLightbox {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = {
            dataSource: options.dataSource || null,
            productType: options.productType || 'default',
            autoLoad: options.autoLoad !== false,
            zoomEnabled: options.zoomEnabled !== false,
            animationSpeed: options.animationSpeed || 0.2,
            ...options
        };
        
        this.images = [];
        this.currentIndex = 0;
        this.isInitialized = false;
        
        this.targetX = 50;
        this.targetY = 50;
        this.currentX = 50;
        this.currentY = 50;
        this.targetSize = 100;
        this.currentSize = 100;
        this.animationFrame = null;
    }

    init() {
        if (this.isInitialized) return;
        this.createHTML();
        this.bindEvents();
        if (this.options.autoLoad && this.options.dataSource) {
            this.loadImages();
        }
        this.isInitialized = true;
    }

    createHTML() {
        if (!this.container) return;
        this.container.innerHTML = "`
            <div class="gallery-container">
                <div class="zoom-box" id="zoomBox_${this.options.productType}">
                </div>
                <div class="thumbnail-grid" id="thumbnailGrid_${this.options.productType}">
                </div>
            </div>
            <div id="galleryLoading_${this.options.productType}" class="gallery-loading">
                <p>이미지를 불러오는 중...</p>
            </div>
            <div id="galleryError_${this.options.productType}" class="gallery-error" style="display: none;">
                <p>이미지를 불러올 수 없습니다.</p>
            </div>
        ";
    }

    bindEvents() {
        const zoomBox = document.getElementById(`zoomBox_${this.options.productType}`);
        if (!zoomBox || !this.options.zoomEnabled) return;

        zoomBox.addEventListener('mousemove', (e) => {
            const rect = zoomBox.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            this.targetX = (x / rect.width) * 100;
            this.targetY = (y / rect.height) * 100;
            this.targetSize = 200;
            
            if (!this.animationFrame) this.animate();
        });

        zoomBox.addEventListener('mouseleave', () => {
            this.targetX = 50;
            this.targetY = 50;
            this.targetSize = 100;
        });
    }

    animate() {
        const zoomBox = document.getElementById(`zoomBox_${this.options.productType}`);
        if (!zoomBox) return;

        const ease = 0.1;
        this.currentX += (this.targetX - this.currentX) * ease;
        this.currentY += (this.targetY - this.currentY) * ease;
        this.currentSize += (this.targetSize - this.currentSize) * ease;

        zoomBox.style.backgroundSize = `${this.currentSize}%`;
        zoomBox.style.backgroundPosition = `${this.currentX}% ${this.currentY}%`;

        const threshold = 0.1;
        if (Math.abs(this.targetX - this.currentX) > threshold ||
            Math.abs(this.targetY - this.currentY) > threshold ||
            Math.abs(this.targetSize - this.currentSize) > threshold) {
            this.animationFrame = requestAnimationFrame(() => this.animate());
        } else {
            this.animationFrame = null;
        }
    }

    async loadImages() {
        if (!this.options.dataSource) return;
        this.showLoading(true);
        try {
            const response = await fetch(this.options.dataSource);
            const data = await response.json();
            if (data.success && data.data.length > 0) {
                this.images = data.data;
                this.createThumbnails();
            } else {
                this.showError(`${this.options.productType} 샘플 이미지가 없습니다.`);
            }
        } catch (error) {
            this.showError('이미지를 불러오는 중 오류가 발생했습니다: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    createThumbnails() {
        const thumbnailGrid = document.getElementById(`thumbnailGrid_${this.options.productType}`);
        if (!thumbnailGrid) return;
        thumbnailGrid.innerHTML = '';
        this.images.forEach((image, index) => {
            const thumbnail = document.createElement('img');
            thumbnail.src = image.thumbnail;
            thumbnail.alt = image.title;
            thumbnail.className = index === 0 ? 'active' : '';
            thumbnail.title = image.title;
            thumbnail.addEventListener('click', () => {
                this.updateMainImage(index);
                this.updateThumbnailActive(index);
            });
            thumbnailGrid.appendChild(thumbnail);
        });
        if (this.images.length > 0) this.updateMainImage(0);
    }

    updateMainImage(index) {
        if (this.images.length === 0 || index >= this.images.length) return;
        const zoomBox = document.getElementById(`zoomBox_${this.options.productType}`);
        const image = this.images[index];
        if (zoomBox) {
            zoomBox.style.backgroundImage = `url('${image.path}')`;
            zoomBox.style.backgroundSize = '100%';
            zoomBox.style.backgroundPosition = 'center center';
            this.currentSize = 100; this.currentX = 50; this.currentY = 50;
            this.targetSize = 100; this.targetX = 50; this.targetY = 50;
        }
        this.currentIndex = index;
    }

    updateThumbnailActive(activeIndex) {
        const thumbnails = document.querySelectorAll(`#thumbnailGrid_${this.options.productType} img`);
        thumbnails.forEach((thumb, index) => {
            if (index === activeIndex) thumb.classList.add('active');
            else thumb.classList.remove('active');
        });
    }

    showLoading(show) {
        const loadingElement = document.getElementById(`galleryLoading_${this.options.productType}`);
        if (loadingElement) loadingElement.style.display = show ? 'block' : 'none';
    }

    showError(message) {
        const errorElement = document.getElementById(`galleryError_${this.options.productType}`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    setImages(images) {
        this.images = images;
        this.createThumbnails();
    }

    nextImage() {
        if (this.images.length === 0) return;
        const nextIndex = (this.currentIndex + 1) % this.images.length;
        this.updateMainImage(nextIndex);
        this.updateThumbnailActive(nextIndex);
    }

    prevImage() {
        if (this.images.length === 0) return;
        const prevIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.updateMainImage(prevIndex);
        this.updateThumbnailActive(prevIndex);
    }

    destroy() {
        if (this.animationFrame) cancelAnimationFrame(this.animationFrame);
        if (this.container) this.container.innerHTML = '';
        this.isInitialized = false;
    }
}
window.GalleryLightbox = GalleryLightbox;

// =================================================================================
// 4. 범용 파일 업로드 JavaScript 클래스 (UniversalFileUpload Class)
// =================================================================================

class UniversalFileUpload {
    constructor(containerId, config = {}) {
        this.container = document.getElementById(containerId);
        this.config = {
            product_type: 'general',
            max_file_size: 10 * 1024 * 1024,
            allowed_types: ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
            allowed_extensions: ['jpg', 'jpeg', 'png', 'pdf'],
            upload_url: '../../includes/upload_handler.php',
            get_files_url: '../../includes/get_files_handler.php',
            delete_file_url: '../../includes/delete_file_handler.php',
            multiple: true,
            drag_drop: true,
            show_progress: true,
            auto_upload: true,
            delete_enabled: true,
            preview_enabled: true,
            ...config
        };
        
        this.uploadedFiles = [];
        this.init();
    }
    
    init() {
        this.dropZone = this.container.querySelector('.drop-zone');
        this.fileInput = this.container.querySelector('.file-input');
        this.fileList = this.container.querySelector('.file-list');
        this.uploadProgress = this.container.querySelector('.upload-progress');
        this.progressFill = this.container.querySelector('.progress-fill');
        this.progressText = this.container.querySelector('.progress-text');
        this.btnFileSelect = this.container.querySelector('.btn-file-select');
        
        this.setupEvents();
        this.loadExistingFiles();
    }
    
    setupEvents() {
        if (this.config.drag_drop && this.dropZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                this.dropZone.addEventListener(eventName, this.preventDefaults, false);
                document.body.addEventListener(eventName, this.preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                this.dropZone.addEventListener(eventName, this.highlight.bind(this), false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                this.dropZone.addEventListener(eventName, this.unhighlight.bind(this), false);
            });
            
            this.dropZone.addEventListener('drop', this.handleDrop.bind(this), false);
            this.dropZone.addEventListener('click', () => this.fileInput.click());
        }
        
        if (this.btnFileSelect) this.btnFileSelect.addEventListener('click', () => this.fileInput.click());
        if (this.fileInput) this.fileInput.addEventListener('change', this.handleFileSelect.bind(this));
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    highlight() {
        if (this.dropZone) { this.dropZone.style.borderColor = '#007bff'; this.dropZone.style.backgroundColor = '#f8f9ff'; this.dropZone.style.transform = 'scale(1.02)'; } 
    }
    
    unhighlight() {
        if (this.dropZone) { this.dropZone.style.borderColor = '#007bff'; this.dropZone.style.backgroundColor = 'white'; this.dropZone.style.transform = 'scale(1)'; } 
    }
    
    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        this.handleFiles(files);
    }
    
    handleFileSelect(e) {
        const files = e.target.files;
        this.handleFiles(files);
    }
    
    handleFiles(files) {
        [...files].forEach(file => this.processFile(file));
    }
    
    processFile(file) {
        if (!this.validateFile(file)) return;
        if (this.config.auto_upload) this.uploadFile(file);
        else this.addFileToQueue(file);
    }
    
    validateFile(file) {
        if (file.size > this.config.max_file_size) { this.showError(`파일 크기가 ${this.config.max_file_size / 1024 / 1024}MB를 초과합니다: ${file.name}`); return false; }
        if (!this.config.allowed_types.includes(file.type)) { this.showError(`지원하지 않는 파일 형식입니다: ${file.name}\n지원 형식: ${this.config.allowed_extensions.join(', ').toUpperCase()}`); return false; }
        return true;
    }
    
    uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('product_type', this.config.product_type);
        formData.append('session_id', this.getSessionId());
        const fileItem = this.addFileToList(file, 'uploading');
        if (this.config.show_progress) this.showProgress();
        fetch(this.config.upload_url, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) { this.updateFileStatus(fileItem, 'success', data.file_info); this.uploadedFiles.push(data.file_info); this.onFileUploaded(data.file_info); } 
            else { this.updateFileStatus(fileItem, 'error', null, data.message); this.onUploadError(data.message); }
        })
        .catch(error => { console.error('업로드 오류:', error); this.updateFileStatus(fileItem, 'error', null, '업로드 중 오류가 발생했습니다.'); this.onUploadError(error.message); })
        .finally(() => { if (this.config.show_progress) this.hideProgress(); });
    }
    
    addFileToList(file, status) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.style.cssText = `display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: white; border: 1px solid #dee2e6; border-radius: 5px; font-size: 0.9rem;`;
        const fileName = file.name.length > 30 ? file.name.substring(0, 30) + '...' : file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2) + 'MB';
        fileItem.innerHTML = `
            <div class="file-info">
                <span class="file-name" style="font-weight: 600;">${fileName}</span>
                <span class="file-size" style="color: #6c757d; margin-left: 0.5rem;">(${fileSize})</span>
            </div>
            <div class="file-status">
                ${this.getStatusIcon(status)}
            </div>
        `;
        this.fileList.appendChild(fileItem);
        return fileItem;
    }
    
    updateFileStatus(fileItem, status, fileInfo = null, errorMessage = null) {
        const statusElement = fileItem.querySelector('.file-status');
        statusElement.innerHTML = this.getStatusIcon(status, fileInfo);
        if (status === 'error' && errorMessage) { fileItem.style.borderColor = '#dc3545'; fileItem.title = errorMessage; }
        else if (status === 'success') { fileItem.style.borderColor = '#28a745'; }
    }
    
    getStatusIcon(status, fileInfo = null) {
        switch (status) {
            case 'uploading': return '<span style="color: #007bff;">⏳ 업로드 중...</span>';
            case 'success':
                let successHtml = '<span style="color: #28a745;">✅ 완료</span>';
                if (this.config.delete_enabled && fileInfo) {
                    successHtml += `<button onclick="window.deleteUploadedFile(${fileInfo.id}, this)" style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 0.8rem; margin-left: 0.5rem;">🗑️ 삭제</button>`;
                }
                return successHtml;
            case 'error': return '<span style="color: #dc3545;">❌ 실패</span>';
            default: return '';
        }
    }
    
    showProgress() {
        if (this.uploadProgress) {
            this.uploadProgress.style.display = 'block';
            this.progressText.textContent = '파일 업로드 중...';
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                this.progressFill.style.width = progress + '%';
            }, 200);
            this.progressInterval = interval;
        }
    }
    
    hideProgress() {
        if (this.progressInterval) clearInterval(this.progressInterval);
        if (this.progressFill && this.progressText && this.uploadProgress) {
            this.progressFill.style.width = '100%';
            this.progressText.textContent = '업로드 완료!';
            setTimeout(() => { this.uploadProgress.style.display = 'none'; this.progressFill.style.width = '0%'; }, 1000);
        }
    }
    
    loadExistingFiles() {
        fetch(`${this.config.get_files_url}?product_type=${this.config.product_type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.files.length > 0) {
                data.files.forEach(file => {
                    const fileItem = this.createExistingFileItem(file);
                    this.fileList.appendChild(fileItem);
                    this.uploadedFiles.push(file);
                });
            }
        })
        .catch(error => { console.error('기존 파일 로드 오류:', error); });
    }
    
    createExistingFileItem(file) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.style.cssText = `display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: white; border: 1px solid #28a745; border-radius: 5px; font-size: 0.9rem;`;
        const fileName = file.original_name.length > 30 ? file.original_name.substring(0, 30) + '...' : file.original_name;
        const fileSize = (file.file_size / 1024 / 1024).toFixed(2) + 'MB';
        fileItem.innerHTML = `
            <div class="file-info">
                <span class="file-name" style="font-weight: 600;">${fileName}</span>
                <span class="file-size" style="color: #6c757d; margin-left: 0.5rem;">(${fileSize})</span>
                <span class="upload-date" style="color: #868e96; margin-left: 0.5rem; font-size: 0.8rem;">${new Date(file.upload_date).toLocaleString()}</span>
            </div>
            <div class="file-actions">
                <span style="color: #28a745; margin-right: 0.5rem;">✅ 업로드 완료</span>
                ${this.config.delete_enabled ? `<button onclick="window.deleteUploadedFile(${file.id}, this)" style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 0.8rem;">🗑️ 삭제</button>` : ''}
            </div>
        `;
        return fileItem;
    }
    
    getSessionId() {
        return document.querySelector('meta[name="session-id"]')?.content || '';
    }
    
    showError(message) {
        alert(message);
    }
    
    onFileUploaded(fileInfo) { console.log('파일 업로드 완료:', fileInfo); }
    onUploadError(error) { console.error('업로드 오류:', error); }
    onFileDeleted(fileId) { console.log('파일 삭제 완료:', fileId); }
    
    getUploadedFiles() { return this.uploadedFiles; }
    clearAllFiles() { this.fileList.innerHTML = ''; this.uploadedFiles = []; }
}
window.deleteUploadedFile = function(fileId, buttonElement) {
    if (!confirm('이 파일을 삭제하시겠습니까?')) return;
    const formData = new FormData();
    formData.append('file_id', fileId);
    fetch('../../includes/delete_file_handler.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) { buttonElement.closest('.file-item').remove(); alert('파일이 삭제되었습니다.'); } 
        else alert('파일 삭제에 실패했습니다: ' + data.message);
    })
    .catch(error => { console.error('파일 삭제 오류:', error); alert('파일 삭제 중 오류가 발생했습니다.'); });
};

// =================================================================================
// 5. 자석스티커 가격 계산 함수 (from msticker.js)
// =================================================================================

function initializeCalculator() {
    const typeSelect = document.getElementById('MY_type');
    const sizeSelect = document.getElementById('Section');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');
    const ordertypeSelect = document.getElementById('ordertype');

    if (!typeSelect) return;

    typeSelect.addEventListener('change', function() {
        const style = this.value;
        resetSelectWithText(sizeSelect, '자석스티커 규격을 선택해주세요');
        resetSelectWithText(quantitySelect, '수량을 선택해주세요');
        resetPrice();
        if (style) loadSizes(style);
    });

    if (sizeSelect) sizeSelect.addEventListener('change', loadQuantities);
    if (sideSelect) sideSelect.addEventListener('change', loadQuantities);
    
    [typeSelect, sizeSelect, sideSelect, quantitySelect, ordertypeSelect].forEach(select => {
        if (select) select.addEventListener('change', autoCalculatePrice);
    });
}

function resetSelectWithText(selectElement, defaultText) {
    if (selectElement) selectElement.innerHTML = `<option value="">${defaultText}</option>`;
}

function resetPrice() {
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const priceDisplay = document.getElementById('priceDisplay');
    const uploadOrderButton = document.getElementById('uploadOrderButton');
    
    if (priceAmount) priceAmount.textContent = '견적 계산 필요';
    if (priceDetails) priceDetails.innerHTML = '<span>모든 옵션을 선택하면 자동으로 계산됩니다</span>';
    if (priceDisplay) priceDisplay.classList.remove('calculated');
    if (uploadOrderButton) uploadOrderButton.style.display = 'none';
    currentPriceData = null;
}

function loadSizes(style) {
    if (!style) return;
    fetch(`get_sizes.php?CV_no=${style}&page=msticker`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const sizeSelect = document.getElementById('Section');
                updateSelectWithOptions(sizeSelect, data.data, '자석스티커 규격을 선택해주세요');
                const defaultSection = sizeSelect.dataset.defaultValue;
                if (defaultSection) {
                    sizeSelect.value = defaultSection;
                    if (sizeSelect.value) loadQuantities();
                }
            } else {
                showUserMessage('규격 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => { console.error('규격 로드 오류:', error); showUserMessage('규격 로드 중 오류가 발생했습니다.', 'error'); });
}

function loadQuantities() {
    const typeSelect = document.getElementById('MY_type');
    const sizeSelect = document.getElementById('Section');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');

    if (!typeSelect || !sizeSelect || !sideSelect || !quantitySelect) return;

    const style = typeSelect.value;
    const section = sizeSelect.value;
    const potype = sideSelect.value;

    resetSelectWithText(quantitySelect, '수량을 선택해주세요');
    resetPrice();

    if (!style || !section || !potype) return;

    fetch(`get_quantities.php?style=${style}&Section=${section}&POtype=${potype}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                const defaultQuantity = quantitySelect.dataset.defaultValue;
                if (defaultQuantity) {
                    quantitySelect.value = defaultQuantity;
                    if (quantitySelect.value) autoCalculatePrice();
                }
            } else {
                showUserMessage('수량 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => { console.error('수량 로드 오류:', error); showUserMessage('수량 로드 중 오류가 발생했습니다.', 'error'); });
}

function updateSelectWithOptions(selectElement, options, defaultOptionText) {
    if (!selectElement) return;
    selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
    if (options && options.length > 0) {
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option.value || option.no;
            optionElement.textContent = option.text || option.title;
            selectElement.appendChild(optionElement);
        });
    }
}

function autoCalculatePrice() {
    const form = document.getElementById('mstickerForm');
    if (!form || !form.checkValidity()) return;
    calculatePrice(true);
}

function calculatePrice(isAuto = true) {
    const form = document.getElementById('mstickerForm');
    if (!form) return;
    const formData = new FormData(form);
    if (!formData.get('MY_type') || !formData.get('Section') || !formData.get('POtype') || !formData.get('MY_amount') || !formData.get('ordertype')) return;
    
    const params = new URLSearchParams(formData);
    
    fetch('calculate_price_ajax.php?' + params.toString())
    .then(response => { if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`); return response.json(); })
    .then(response => {
        if (response.success) {
            const priceData = response.data;
            currentPriceData = priceData;
            updatePriceDisplay(priceData);
        } else {
            resetPrice();
            if (!isAuto) showUserMessage('가격 계산 실패: ' + (response.message || '알 수 없는 오류'), 'error');
        }
    })
    .catch(error => {
        console.error('가격 계산 오류:', error);
        if (!isAuto) showUserMessage('가격 계산 중 오류가 발생했습니다.', 'error');
    });
}

function updatePriceDisplay(priceData) {
    const priceDisplay = document.getElementById('priceDisplay');
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const uploadOrderButton = document.getElementById('uploadOrderButton');
    
    if (!priceAmount || !priceDetails || !priceDisplay || !uploadOrderButton) return;

    const supplyPrice = priceData.total_price || (priceData.base_price + priceData.design_price);
    priceAmount.textContent = formatNumber(supplyPrice) + '원';
    
    const printCost = Math.round(priceData.base_price);
    const designCost = Math.round(priceData.design_price);
    const total = Math.round(priceData.total_with_vat);
    
    priceDetails.innerHTML = `
        <div class="price-breakdown">
            <div class="price-item"><span>인쇄비:</span><span>${formatNumber(printCost)}원</span></div>
            <div class="price-divider"></div>
            <div class="price-item"><span>디자인비:</span><span>${formatNumber(designCost)}원</span></div>
            <div class="price-divider"></div>
            <div class="price-item final"><span>부가세 포함:</span><span>${formatNumber(total)}원</span></div>
        </div>`;
    
    priceDisplay.classList.add('calculated');
    uploadOrderButton.style.display = 'block';
}

// =================================================================================
// 6. 장바구니 및 주문 기능
// =================================================================================

window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    if (!currentPriceData) return onError("먼저 가격을 계산해주세요.");

    const formData = new FormData(document.getElementById('mstickerForm'));
    formData.append("action", "add_to_basket");
    formData.append("product_type", "msticker");
    formData.append("calculated_price", Math.round(currentPriceData.total_price));
    formData.append("calculated_vat_price", Math.round(currentPriceData.total_with_vat));

    const workMemo = document.getElementById("modalWorkMemo")?.value || '';
    if (workMemo) formData.append("work_memo", workMemo);
    formData.append("upload_method", window.selectedUploadMethod || "upload");

    if (uploadedFiles && uploadedFiles.length > 0) {
        uploadedFiles.forEach(file => formData.append("uploaded_files[]", file.file));
        formData.set('uploaded_files_info', JSON.stringify(uploadedFiles.map(f => ({ name: f.name, size: f.size, type: f.type }))));
    }

    fetch('add_to_basket.php', { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => data.success ? onSuccess(data) : onError(data.message))
        .catch(err => onError("네트워크 오류가 발생했습니다."));
};

function openUploadModal() {
    if (!currentPriceData) {
        showUserMessage('먼저 가격을 계산해주세요.', 'warning');
        return;
    }
    if (typeof window.openUploadModal_Common === 'function') {
        window.openUploadModal_Common();
    } else {
        const modal = document.getElementById('uploadModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof initializeModalFileUpload === 'function') initializeModalFileUpload();
        }
    }
}

// =================================================================================
// 7. 통일된 갤러리 팝업 시스템 (UnifiedGalleryPopup Class)
// =================================================================================

class UnifiedGalleryPopup {
    constructor(options = {}) {
        this.options = {
            category: options.category || 'default',
            apiUrl: options.apiUrl || '/api/get_real_orders_portfolio.php',
            perPage: 18,
            title: options.title || '갤러리',
            icon: options.icon || '📸',
            ...options
        };
        
        this.currentPage = 1;
        this.totalPages = 1;
        this.isLoading = false;
        this.data = [];
        
        this.init();
    }
    
    init() {
        const existingPopup = document.getElementById(`unified-gallery-popup-${this.options.category}`);
        if (existingPopup) existingPopup.remove();
        
        const popupHTML = `
            <div id="unified-gallery-popup-${this.options.category}" class="unified-gallery-popup">
                <div class="unified-popup-container">
                    <div class="unified-popup-header">
                        <h3 class="unified-popup-title">
                            <span>${this.options.icon}</span>
                            <span>${this.options.title}</span>
                        </h3>
                        <button class="unified-popup-close" type="button">✕</button>
                    </div>
                    
                    <div class="unified-popup-body">
                        <div class="unified-gallery-grid" id="unified-gallery-grid-${this.options.category}">
                        </div>
                        
                        <div class="unified-pagination" id="unified-pagination-${this.options.category}">
                            <div class="unified-page-info" id="unified-page-info-${this.options.category}">
                                페이지 1 / 1 (총 0개)
                            </div>
                            
                            <div class="unified-page-controls">
                                <button class="unified-page-btn" id="unified-prev-btn-${this.options.category}" disabled>
                                    ← 이전
                                </button>
                                
                                <div class="unified-page-numbers" id="unified-page-numbers-${this.options.category}">
                                </div>
                                
                                <button class="unified-page-btn" id="unified-next-btn-${this.options.category}" disabled>
                                    다음 →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', popupHTML);
        this.bindEvents();
    }
    
    bindEvents() {
        const popup = document.getElementById(`unified-gallery-popup-${this.options.category}`);
        const closeBtn = popup.querySelector('.unified-popup-close');
        const prevBtn = document.getElementById(`unified-prev-btn-${this.options.category}`);
        const nextBtn = document.getElementById(`unified-next-btn-${this.options.category}`);
        
        closeBtn.addEventListener('click', () => this.close());
        popup.addEventListener('click', (e) => { if (e.target === popup) this.close(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && popup.classList.contains('active')) this.close(); });
        
        prevBtn.addEventListener('click', () => this.goToPage(this.currentPage - 1));
        nextBtn.addEventListener('click', () => this.goToPage(this.currentPage + 1));
    }
    
    async open() {
        const popup = document.getElementById(`unified-gallery-popup-${this.options.category}`);
        popup.classList.add('active');
        document.body.style.overflow = 'hidden';
        await this.loadPage(1);
    }
    
    close() {
        const popup = document.getElementById(`unified-gallery-popup-${this.options.category}`);
        popup.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    async loadPage(page) {
        if (this.isLoading) return;
        this.isLoading = true;
        this.currentPage = page;
        const galleryGrid = document.getElementById(`unified-gallery-grid-${this.options.category}`);
        galleryGrid.innerHTML = '<div class="unified-gallery-loading">갤러리 로딩 중...</div>';
        try {
            const url = `${this.options.apiUrl}?category=${this.options.category}&page=${page}&per_page=${this.options.perPage}&all=true`;
            const response = await fetch(url);
            const data = await response.json();
            if (data.success && data.data && data.data.length > 0) {
                this.data = data.data;
                this.renderGallery(data.data);
                this.updatePagination(data.pagination);
            } else {
                galleryGrid.innerHTML = '<div class="unified-gallery-error">갤러리 이미지를 불러올 수 없습니다.</div>';
            }
        } catch (error) {
            console.error(`🚨 ${this.options.category} 갤러리 로딩 오류:`, error);
            galleryGrid.innerHTML = '<div class="unified-gallery-error">갤러리 로딩 중 오류가 발생했습니다.</div>';
        }
        this.isLoading = false;
    }
    
    renderGallery(images) {
        const galleryGrid = document.getElementById(`unified-gallery-grid-${this.options.category}`);
        galleryGrid.innerHTML = '';
        images.forEach((image, index) => {
            const cardHTML = `
                <div class="unified-gallery-card" onclick="UnifiedGalleryPopup.viewImage('${image.image_path}', '${image.title}')">
                    <img 
                        class="unified-card-image" 
                        src="${image.image_path}" 
                        alt="${image.title}"
                        onerror="this.src='/images/placeholder.jpg'; this.style.background='#f0f0f0';"
                    />
                    <div class="unified-card-title">${image.title}</div>
                </div>
            `;
            galleryGrid.insertAdjacentHTML('beforeend', cardHTML);
        });
    }
    
    updatePagination(pagination) {
        this.totalPages = pagination.total_pages;
        const pageInfo = document.getElementById(`unified-page-info-${this.options.category}`);
        pageInfo.textContent = `페이지 ${pagination.current_page} / ${pagination.total_pages} (총 ${pagination.total_count.toLocaleString()}개)`;
        const prevBtn = document.getElementById(`unified-prev-btn-${this.options.category}`);
        const nextBtn = document.getElementById(`unified-next-btn-${this.options.category}`);
        prevBtn.disabled = !pagination.has_prev;
        nextBtn.disabled = !pagination.has_next;
        this.renderPageNumbers(pagination);
    }
    
    renderPageNumbers(pagination) {
        const pageNumbers = document.getElementById(`unified-page-numbers-${this.options.category}`);
        pageNumbers.innerHTML = '';
        const currentPage = pagination.current_page;
        const totalPages = pagination.total_pages;
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        if (endPage - startPage < 4) {
            if (startPage === 1) endPage = Math.min(totalPages, startPage + 4);
            else startPage = Math.max(1, endPage - 4);
        }
        if (startPage > 1) {
            pageNumbers.insertAdjacentHTML('beforeend', `<button class="unified-page-btn" onclick="window.unifiedGalleryPopup_${this.options.category}.goToPage(1)">1</button>`);
            if (startPage > 2) pageNumbers.insertAdjacentHTML('beforeend', `<span style="padding: 0 5px; color: #999;">...</span>`);
        }
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === currentPage ? ' active' : '';
            pageNumbers.insertAdjacentHTML('beforeend', `<button class="unified-page-btn${isActive}" onclick="window.unifiedGalleryPopup_${this.options.category}.goToPage(${i})">${i}</button>`);
        }
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) pageNumbers.insertAdjacentHTML('beforeend', `<span style="padding: 0 5px; color: #999;">...</span>`);
            pageNumbers.insertAdjacentHTML('beforeend', `<button class="unified-page-btn" onclick="window.unifiedGalleryPopup_${this.options.category}.goToPage(${totalPages})">${totalPages}</button>`);
        }
    }
    
    goToPage(page) {
        if (page < 1 || page > this.totalPages || page === this.currentPage || this.isLoading) return;
        this.loadPage(page);
    }
    
    static viewImage(imagePath, title) {
        if (typeof EnhancedImageLightbox !== 'undefined') {
            const lightbox = new EnhancedImageLightbox({
                closeOnImageClick: true, showNavigation: false, showCaption: true,
                enableKeyboard: true, zoomEnabled: true
            });
            lightbox.open([{ src: imagePath, title: title, description: '실제 고객 주문으로 제작된 제품입니다. 클릭하면 닫힙니다.' }]);
        } else {
            window.open(imagePath, '_blank');
        }
    }
}
window.UnifiedGalleryPopup = UnifiedGalleryPopup;

// =================================================================================
// 8. 페이지 초기화
// =================================================================================

document.addEventListener('DOMContentLoaded', function() {
    // 계산기 초기화
    initializeCalculator();

    // 갤러리 초기화
    initializeGallery();

    // 기본값이 설정되어 있으면 자동으로 하위 옵션들 로드
    const typeSelect = document.getElementById('MY_type');
    if (typeSelect && typeSelect.value) {
        loadSizes(typeSelect.value);
    }

    // 갤러리 모달 제어 함수들 (페이지네이션 지원)
    let unifiedMstickerGallery;
    unifiedMstickerGallery = new UnifiedGalleryPopup({
        category: 'msticker',
        apiUrl: '/api/get_real_orders_portfolio.php',
        title: '자석스티커 전체 갤러리',
        icon: '🧲',
        perPage: 18
    });
    window.openMstickerGalleryModal = function() {
        if (unifiedMstickerGallery) unifiedMstickerGallery.open();
    };
    window.closeMstickerGalleryModal = function() {
        const modal = document.getElementById('mstickerGalleryModal');
        if (modal) { modal.style.display = 'none'; document.body.style.overflow = 'auto'; }
    };
    window.loadMStickerPage = function(pageOrDirection) {
        let targetPage;
        if (pageOrDirection === 'prev') targetPage = Math.max(1, mstickerCurrentPage - 1);
        else if (pageOrDirection === 'next') targetPage = mstickerCurrentPage + 1;
        else targetPage = parseInt(pageOrDirection);
        if (targetPage !== mstickerCurrentPage) loadMStickerFullGallery(targetPage);
    };
    window.loadMStickerFullGallery = function(page = 1) {
        const galleryGrid = document.getElementById('mstickerGalleryModalGrid');
        if (!galleryGrid) return;
        galleryGrid.innerHTML = '<div class="gallery-loading">갤러리를 불러오는 중...</div>';
        fetch(`/api/get_real_orders_portfolio.php?category=msticker&all=true&page=${page}&per_page=12`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    mstickerCurrentPage = page;
                    mstickerPaginationData = data.pagination;
                    if (data.data.length > 0) {
                        renderMStickerFullGallery(data.data, galleryGrid);
                        updateMStickerPagination(data.pagination);
                    } else {
                        galleryGrid.innerHTML = '<div class="gallery-empty">표시할 이미지가 없습니다.</div>';
                        hideMStickerPagination();
                    }
                } else {
                    galleryGrid.innerHTML = '<div class="gallery-empty">표시할 이미지가 없습니다.</div>';
                    hideMStickerPagination();
                }
            })
            .catch(error => {
                console.error('Gallery loading error:', error);
                galleryGrid.innerHTML = '<div class="gallery-error">갤러리를 불러오는 중 오류가 발생했습니다.</div>';
                hideMStickerPagination();
            });
    };
    window.updateMStickerPagination = function(pagination) {
        if (!pagination || pagination.total_pages <= 1) { hideMStickerPagination(); return; }
        const paginationContainer = document.getElementById('mstickerPagination');
        const pageInfo = document.getElementById('mstickerPageInfo');
        const pageNumbers = document.getElementById('mstickerPageNumbers');
        const prevBtn = document.getElementById('mstickerPrevBtn');
        const nextBtn = document.getElementById('mstickerNextBtn');
        if (!paginationContainer || !pageInfo || !pageNumbers || !prevBtn || !nextBtn) return;
        pageInfo.textContent = `페이지 ${pagination.current_page} / ${pagination.total_pages} (총 ${pagination.total_count.toLocaleString()}개)`;
        prevBtn.disabled = !pagination.has_prev;
        nextBtn.disabled = !pagination.has_next;
        pageNumbers.innerHTML = generateMStickerPageNumbers(pagination);
        paginationContainer.style.display = 'block';
    };
    window.generateMStickerPageNumbers = function(pagination) {
        let html = '';
        const current = pagination.current_page;
        const total = pagination.total_pages;
        const startPage = Math.max(1, current - 2);
        const endPage = Math.min(total, current + 2);
        if (startPage > 1) {
            html += `<span class="pagination-number" onclick="loadMStickerPage(1)">1</span>`;
            if (startPage > 2) html += `<span class="pagination-ellipsis">...</span>`;
        }
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === current ? 'active' : '';
            html += `<span class="pagination-number ${activeClass}" onclick="loadMStickerPage(${i})">${i}</span>`;
        }
        if (endPage < total) {
            if (endPage < total - 1) html += `<span class="pagination-ellipsis">...</span>`;
            html += `<span class="pagination-number" onclick="loadMStickerPage(${total})">${total}</span>`;
        }
        return html;
    };
    window.hideMStickerPagination = function() {
        const paginationContainer = document.getElementById('mstickerPagination');
        if (paginationContainer) paginationContainer.style.display = 'none';
    };
    window.renderMStickerFullGallery = function(images, container) {
        let html = '';
        images.forEach((image, index) => {
            html += `
                <div class="gallery-item" onclick="openLightbox('${image.path}', '${image.title}')">
                    <img src="${image.path}" alt="${image.title}" loading="lazy" 
                         onerror="this.parentElement.style.display='none'">
                    <div class="gallery-item-title">${image.title}</div>
                </div>
            `;
        });
        container.innerHTML = html;
    };
    window.openLightbox = function(imagePath, title) {
        if (window.lightboxViewer && window.lightboxViewer.showLightbox) {
            window.lightboxViewer.showLightbox(imagePath, title);
        } else {
            window.open(imagePath, '_blank');
        }
    };

    // 메인 갤러리 API 방식으로 로딩 (전단지와 동일한 방법)
    loadMstickerImagesAPI();
});

async function loadMstickerImagesAPI() {
    const galleryContainer = document.getElementById('mstickerGallery');
    if (!galleryContainer) return;
    galleryContainer.innerHTML = '<div class="loading">🧲 갤러리 로딩 중...</div>';
    try {
        const response = await fetch('/api/get_real_orders_portfolio.php?category=msticker&per_page=4');
        const data = await response.json();
        if (data.success && data.data && data.data.length > 0) {
            renderMstickerGalleryAPI(data.data, galleryContainer);
        } else {
            galleryContainer.innerHTML = '<div class="error">표시할 이미지가 없습니다.</div>';
        }
    } catch (error) {
        console.error('❌ 자석스티커 갤러리 로딩 오류:', error);
        galleryContainer.innerHTML = '<div class="error">갤러리를 불러오는 중 오류가 발생했습니다.</div>';
    }
}

function renderMstickerGalleryAPI(images, container) {
    const viewerHtml = `
        <div class="lightbox-viewer" id="mstickerLightboxViewer">
            <img id="mstickerMainImage" src="${images[0].path}" alt="${images[0].title}" 
                 style="width: 100%; height: 100%; object-fit: cover; cursor: zoom-in;"
                 onclick="openFullScreenImage('${images[0].path}', '${images[0].title}')">
        </div>
        <div class="thumbnail-strip">
            ${images.map((img, index) => 
                `<img src="${img.path}" alt="${img.title}" class="${index === 0 ? 'active' : ''}"
                     onclick="changeMstickerMainImage('${img.path}', '${img.title}', this)">` 
            ).join('')}
        </div>
    `;
    container.innerHTML = viewerHtml;
    initializeMstickerZoomEffect();
}

function changeMstickerMainImage(imagePath, title, thumbnail) {
    const mainImage = document.getElementById('mstickerMainImage');
    if (mainImage) {
        mainImage.src = imagePath;
        mainImage.alt = title;
        mainImage.onclick = () => openFullScreenImage(imagePath, title);
    }
    const thumbnails = document.querySelectorAll('.thumbnail-strip img');
    thumbnails.forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
    initializeMstickerZoomEffect();
}

function initializeMstickerZoomEffect() {
    const viewer = document.getElementById('mstickerLightboxViewer');
    const mainImage = document.getElementById('mstickerMainImage');
    if (!viewer || !mainImage) return;
    const newViewer = viewer.cloneNode(true);
    viewer.parentNode.replaceChild(newViewer, viewer);
    const newMainImage = document.getElementById('mstickerMainImage');
    if (!newMainImage) return;
    let isZoomed = false;
    newViewer.addEventListener('mousemove', function(e) {
        if (isZoomed) return;
        const rect = newViewer.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        newMainImage.style.transform = `scale(1.5)`;
        newMainImage.style.transformOrigin = `${x}% ${y}%`;
        newMainImage.style.transition = 'transform 0.3s ease';
    });
    newViewer.addEventListener('mouseleave', function() {
        if (isZoomed) return;
        newMainImage.style.transform = 'scale(1)';
        newMainImage.style.transformOrigin = 'center center';
    });
    newViewer.addEventListener('click', function(e) {
        if (e.target === newMainImage) {
            const imagePath = newMainImage.src;
            const title = newMainImage.alt;
            openFullScreenImage(imagePath, title);
        }
    });
}

function openFullScreenImage(imagePath, title) {
    if (window.lightboxViewer && window.lightboxViewer.showLightbox) {
        window.lightboxViewer.showLightbox(imagePath, title);
    } else {
        window.open(imagePath, '_blank');
    }
}
