/**
 * 상품권/쿠폰 페이지 전용 스크립트
 * - GalleryLightbox.js
 * - merchandisebond.js
 * - merchandisebond-premium-options.js
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
        this.container.innerHTML = `
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
        `;
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
// 5. 상품권 가격 계산 함수 (from merchandisebond.js)
// =================================================================================

function initializeCalculator() {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');
    const ordertypeSelect = document.getElementById('ordertype');

    if (!typeSelect) return;

    typeSelect.addEventListener('change', function() {
        const style = this.value;
        resetSelectWithText(quantitySelect, '수량을 선택해주세요');
        resetSelectWithText(paperSelect, '후가공을 선택해주세요');
        resetPrice();
        if (style) {
            loadQuantities(style);
            loadPaperTypes(style);
        }
    });

    if (quantitySelect) {
        quantitySelect.addEventListener('change', function() {
            resetAllPremiumOptions();
            calculatePremiumOptions();
            if (currentPriceData) updatePriceDisplayWithPremium(currentPriceData);
        });
    }
    if (sideSelect) sideSelect.addEventListener('change', autoCalculatePrice);
    if (paperSelect) paperSelect.addEventListener('change', autoCalculatePrice);
    
    [typeSelect, quantitySelect, sideSelect, paperSelect, ordertypeSelect].forEach(select => {
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

function loadPaperTypes(style) {
    if (!style) return;
    fetch(`get_paper_types.php?style=${style}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const paperSelect = document.getElementById('Section');
                updateSelectWithOptions(paperSelect, data.data, '후가공을 선택해주세요');
                const defaultSection = paperSelect.dataset.defaultValue;
                if (defaultSection) {
                    paperSelect.value = defaultSection;
                    if (paperSelect.value) autoCalculatePrice();
                }
            } else {
                showUserMessage('재질 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => { console.error('재질 로드 오류:', error); showUserMessage('재질 로드 중 오류가 발생했습니다.', 'error'); });
}

function loadQuantities(styleParam = null) {
    const typeSelect = document.getElementById('MY_type');
    const quantitySelect = document.getElementById('MY_amount');

    if (!typeSelect || !quantitySelect) return;

    const style = styleParam || typeSelect.value;

    resetSelectWithText(quantitySelect, '수량을 선택해주세요');
    resetPrice();

    if (!style) return;

    fetch(`get_quantities.php?style=${style}`)
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
    const form = document.getElementById('merchandisebondForm');
    if (!form || !form.checkValidity()) return;
    calculatePrice(true);
}

function calculatePrice(isAuto = true) {
    const form = document.getElementById('merchandisebondForm');
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
            updatePriceDisplayWithPremium(priceData);
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
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const priceDisplay = document.getElementById('priceDisplay');
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

function updatePriceDisplayWithPremium(priceData) {
    updatePriceDisplay(priceData);
    const premiumTotal = calculatePremiumOptions();
    if (premiumTotal > 0) {
        const priceAmount = document.getElementById('priceAmount');
        const priceDetails = document.getElementById('priceDetails');
        if (priceAmount && priceDetails) {
            const printCost = Math.round(priceData.base_price);
            const designCost = Math.round(priceData.design_price);
            const originalSupplyPrice = printCost + designCost;
            const newSupplyPrice = originalSupplyPrice + premiumTotal;
            const newTotal = Math.round(newSupplyPrice * 1.1);
            priceAmount.textContent = formatNumber(newSupplyPrice) + '원';
            priceDetails.innerHTML = `
                <div class="price-breakdown">
                    <div class="price-item"><span>인쇄비:</span><span>${formatNumber(printCost)}원</span></div>
                    <div class="price-divider"></div>
                    <div class="price-item"><span>디자인비:</span><span>${formatNumber(designCost)}원</span></div>
                    <div class="price-divider"></div>
                    <div class="price-item"><span>프리미엄 옵션:</span><span>${formatNumber(premiumTotal)}원</span></div>
                    <div class="price-divider"></div>
                    <div class="price-item final"><span>부가세 포함:</span><span>${formatNumber(newTotal)}원</span></div>
                </div>`;
        }
    }
}

// =================================================================================
// 6. 장바구니 및 주문 기능
// =================================================================================

window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    if (!currentPriceData) return onError("먼저 가격을 계산해주세요.");

    const formData = new FormData(document.getElementById('merchandisebondForm'));
    formData.append("action", "add_to_basket");
    formData.append("product_type", "merchandisebond");
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
// 7. 상품권 프리미엄 옵션 관리 시스템 (from merchandisebond-premium-options.js)
// =================================================================================

class MerchandiseBondPremiumOptionsManager {
    constructor() {
        this.basePrices = {
            foil: { base_500: 30000, per_unit: 12, types: { 'gold_matte': '금박무광', 'gold_gloss': '금박유광', 'silver_matte': '은박무광', 'silver_gloss': '은박유광', 'blue_gloss': '청박유광', 'red_gloss': '적박유광', 'green_gloss': '녹박유광', 'black_gloss': '먹박유광' } },
            numbering: { single: { base_500: 60000, per_unit: 12 }, double: { base_500: 75000, per_unit: 12, additional_fee: 15000 } },
            perforation: { single: { base_500: 20000, per_unit: 25 }, double: { base_500: 35000, per_unit: 25, additional_fee: 15000 } },
            rounding: { base_500: 6000, per_unit: 12 },
            creasing: { '1line': { base_500: 20000, per_unit: 25 }, '2line': { base_500: 20000, per_unit: 25 }, '3line': { base_500: 35000, per_unit: 25, additional_fee: 15000 } }
        };
        this.currentQuantity = 500;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updatePriceDisplay();
    }

    setupEventListeners() {
        document.querySelectorAll('.option-toggle').forEach(toggle => {
            toggle.addEventListener('change', (e) => this.handleToggleChange(e.target));
        });
        document.querySelectorAll('.option-details select').forEach(select => {
            select.addEventListener('change', () => this.calculateAndUpdatePrice());
        });
        const quantitySelect = document.getElementById('MY_amount');
        if (quantitySelect) {
            quantitySelect.addEventListener('change', (e) => this.updateQuantity(e.target.value));
        }
    }

    handleToggleChange(toggle) {
        const optionType = toggle.id.replace('_enabled', '');
        const detailsDiv = document.getElementById(`${optionType}_options`);
        if (toggle.checked) {
            if (detailsDiv) { detailsDiv.style.display = 'block'; detailsDiv.classList.add('show'); detailsDiv.classList.remove('hide'); }
        } else {
            if (detailsDiv) { detailsDiv.style.display = 'none'; detailsDiv.classList.add('hide'); detailsDiv.classList.remove('show'); }
            this.resetOptionFields(optionType);
        }
        this.calculateAndUpdatePrice();
    }

    resetOptionFields(optionType) {
        const priceField = document.getElementById(`${optionType}_price`);
        if (priceField) priceField.value = '0';
    }

    updateQuantity(quantityValue) {
        this.currentQuantity = parseInt(quantityValue) || 500;
        this.calculateAndUpdatePrice();
    }

    calculateOptionPrice(optionType, subType, quantity) {
        const config = this.basePrices[optionType];
        if (!config) return 0;
        quantity = quantity || this.currentQuantity;
        if (config.base_500 !== undefined) {
            if (quantity <= 500) return config.base_500;
            else return config.base_500 + ((quantity - 500) * config.per_unit);
        }
        if (config[subType]) {
            const subConfig = config[subType];
            if (quantity <= 500) return subConfig.base_500;
            else {
                const basePrice = subConfig.base_500;
                const additionalUnits = quantity - 500;
                const additionalFee = subConfig.additional_fee || 0;
                return basePrice + (additionalUnits * subConfig.per_unit) + additionalFee;
            }
        }
        return 0;
    }

    calculateAndUpdatePrice() {
        let totalOptionsPrice = 0;
        
        // 박 옵션 계산
        if (document.getElementById('foil_enabled')?.checked) {
            const price = this.calculateOptionPrice('foil', null, this.currentQuantity);
            totalOptionsPrice += price;
            document.getElementById('foil_price').value = price;
        } else { document.getElementById('foil_price').value = '0'; }

        // 넘버링 옵션 계산
        if (document.getElementById('numbering_enabled')?.checked) {
            const numberingType = document.getElementById('numbering_type')?.value || 'single';
            const price = this.calculateOptionPrice('numbering', numberingType, this.currentQuantity);
            totalOptionsPrice += price;
            document.getElementById('numbering_price').value = price;
        } else { document.getElementById('numbering_price').value = '0'; }

        // 미싱(절취선) 옵션 계산
        if (document.getElementById('perforation_enabled')?.checked) {
            const perforationType = document.getElementById('perforation_type')?.value || 'single';
            const price = this.calculateOptionPrice('perforation', perforationType, this.currentQuantity);
            totalOptionsPrice += price;
            document.getElementById('perforation_price').value = price;
        } else { document.getElementById('perforation_price').value = '0'; }

        // 귀돌이 옵션 계산
        if (document.getElementById('rounding_enabled')?.checked) {
            const price = this.calculateOptionPrice('rounding', null, this.currentQuantity);
            totalOptionsPrice += price;
            document.getElementById('rounding_price').value = price;
        } else { document.getElementById('rounding_price').value = '0'; }

        // 오시 옵션 계산
        if (document.getElementById('creasing_enabled')?.checked) {
            const creasingType = document.getElementById('creasing_type')?.value || '1line';
            const price = this.calculateOptionPrice('creasing', creasingType, this.currentQuantity);
            totalOptionsPrice += price;
            document.getElementById('creasing_price').value = price;
        } else { document.getElementById('creasing_price').value = '0'; }

        document.getElementById('premium_options_total').value = totalOptionsPrice;
        this.updatePriceDisplay(totalOptionsPrice);
        if (typeof calculatePrice === 'function') calculatePrice();
    }

    updatePriceDisplay(totalOptionsPrice = 0) {
        const optionPriceTotal = document.getElementById('premiumPriceTotal');
        if (optionPriceTotal) {
            optionPriceTotal.textContent = totalOptionsPrice > 0 ? `(+${totalOptionsPrice.toLocaleString()}원)` : '(+0원)';
            optionPriceTotal.style.color = totalOptionsPrice > 0 ? '#d4af37' : '#718096';
        }
    }

    getCurrentPremiumOptions() {
        const options = {};
        if (document.getElementById('foil_enabled')?.checked) { options.foil_enabled = 1; options.foil_type = document.getElementById('foil_type')?.value; options.foil_price = document.getElementById('foil_price')?.value; }
        if (document.getElementById('numbering_enabled')?.checked) { options.numbering_enabled = 1; options.numbering_type = document.getElementById('numbering_type')?.value; options.numbering_price = document.getElementById('numbering_price')?.value; }
        if (document.getElementById('perforation_enabled')?.checked) { options.perforation_enabled = 1; options.perforation_type = document.getElementById('perforation_type')?.value; options.perforation_price = document.getElementById('perforation_price')?.value; }
        if (document.getElementById('rounding_enabled')?.checked) { options.rounding_enabled = 1; options.rounding_price = document.getElementById('rounding_price')?.value; }
        if (document.getElementById('creasing_enabled')?.checked) { options.creasing_enabled = 1; options.creasing_type = document.getElementById('creasing_type')?.value; options.creasing_price = document.getElementById('creasing_price')?.value; }
        options.premium_options_total = document.getElementById('premium_options_total')?.value;
        return options;
    }

    getPremiumOptionsTotal() {
        return parseInt(document.getElementById('premium_options_total')?.value) || 0;
    }
}

let premiumOptionsManager = null;

function initMerchandiseBondPremiumOptions() {
    if (!premiumOptionsManager) premiumOptionsManager = new MerchandiseBondPremiumOptionsManager();
    return premiumOptionsManager;
}

function getPremiumOptionsTotal() {
    if (premiumOptionsManager) return premiumOptionsManager.getPremiumOptionsTotal();
    const totalField = document.getElementById('premium_options_total');
    return totalField ? parseInt(totalField.value) || 0 : 0;
}

function updatePremiumOptionsQuantity(quantity) {
    if (premiumOptionsManager) premiumOptionsManager.updateQuantity(quantity);
}

function recalculatePremiumOptions() {
    if (premiumOptionsManager) premiumOptionsManager.calculateAndUpdatePrice();
}

// =================================================================================
// 8. 페이지 초기화
// =================================================================================

document.addEventListener('DOMContentLoaded', function() {
    // 계산기 초기화
    initializeCalculator();

    // 갤러리 초기화
    initializeGallery();

    // 프리미엄 옵션 시스템 초기화
    if (document.getElementById('premiumOptionsSection')) {
        initMerchandiseBondPremiumOptions();
        // 프리미엄 옵션 이벤트 리스너 초기화
        const toggles = document.querySelectorAll('.option-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function(e) {
                const optionType = e.target.id.replace('_enabled', '');
                const detailsDiv = document.getElementById(`${optionType}_options`);
                if (e.target.checked) {
                    detailsDiv.style.display = 'block';
                } else {
                    detailsDiv.style.display = 'none';
                    const priceField = document.getElementById(`${optionType}_price`);
                    if (priceField) priceField.value = '0';
                }
                calculatePrice();
            });
        });
        const selects = document.querySelectorAll('.option-select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                calculatePrice();
            });
        });
    }

    // 기본값이 설정되어 있으면 자동으로 하위 옵션들 로드
    const typeSelect = document.getElementById('MY_type');
    if (typeSelect && typeSelect.value) {
        loadPaperTypes(typeSelect.value);
    }
});
