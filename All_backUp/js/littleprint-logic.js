/**
 * 포스터/리플렛 페이지 전용 스크립트
 * - poster.js
 * - littleprint-premium-options.js
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
// 3. 고급 이미지 갤러리 시스템 (적응형 이미지 분석 및 부드러운 애니메이션)
// =================================================================================

function initializeGallery() {
    const galleryContainer = document.getElementById('posterGallery');
    if (!galleryContainer) return;
    
    // GalleryLightbox 클래스 사용 (common-unified.js의 openGalleryPopup과 연동)
    if (typeof GalleryLightbox !== 'undefined') {
        const gallery = new GalleryLightbox('posterGallery', {
            dataSource: 'get_poster_images.php',
            productType: 'poster',
            autoLoad: true,
            zoomEnabled: true,
            animationSpeed: 0.15
        });
        gallery.init();
        setTimeout(() => { checkMoreButtonForLightbox(); }, 1000);
    } else {
        loadPosterImages();
    }
}

function loadPosterImages() {
    const galleryContainer = document.getElementById('posterGallery');
    if (!galleryContainer) return;
    galleryContainer.innerHTML = '<div class="loading">🖼️ 갤러리 로딩 중...</div>';
    fetch('get_poster_images.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                renderGallery(data.data, galleryContainer);
            } else {
                galleryContainer.innerHTML = '<div class="error">갤러리 이미지를 불러올 수 없습니다.</div>';
            }
        })
        .catch(error => {
            console.error('갤러리 로딩 오류:', error);
            galleryContainer.innerHTML = '<div class="error">갤러리 로딩 중 오류가 발생했습니다.</div>';
        });
}

function renderGallery(images, container) {
    const galleryHTML = `<div class="lightbox-viewer" id="zoomBox"></div><div class="thumbnail-strip" id="thumbnailStrip"></div>`;
    container.innerHTML = galleryHTML;
    const zoomBox = document.getElementById('zoomBox');
    const thumbnailStrip = document.getElementById('thumbnailStrip');
    images.forEach((image, index) => {
        const thumbnail = document.createElement('img');
        thumbnail.src = image.thumbnail || image.path;
        thumbnail.alt = image.title || `포스터 샘플 ${index + 1}`;
        thumbnail.className = 'thumbnail';
        thumbnail.dataset.fullImage = image.path;
        if (index === 0) {
            thumbnail.classList.add('active');
            loadImageToZoomBox(image.path, zoomBox);
        }
        thumbnail.addEventListener('click', function() {
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            loadImageToZoomBox(this.dataset.fullImage, zoomBox);
        });
        thumbnailStrip.appendChild(thumbnail);
    });
    initializeAdvancedZoom(zoomBox);
    checkMoreButtonVisibility(images.length);
}

function loadImageToZoomBox(imagePath, zoomBox) {
    analyzeImageSize(imagePath, function(backgroundSize) {
        zoomBox.style.backgroundImage = `url('${imagePath}')`;
        zoomBox.style.backgroundSize = backgroundSize;
        zoomBox.style.backgroundPosition = '50% 50%';
        currentX = targetX = 50;
        currentY = targetY = 50;
        currentSize = targetSize = 100;
        originalBackgroundSize = backgroundSize;
    });
}

function analyzeImageSize(imagePath, callback) {
    const img = new Image();
    img.onload = function() {
        const containerHeight = 300;
        const containerWidth = document.getElementById('zoomBox').getBoundingClientRect().width;
        let backgroundSize;
        if (this.naturalHeight <= containerHeight && this.naturalWidth <= containerWidth) {
            backgroundSize = `${this.naturalWidth}px ${this.naturalHeight}px`;
            currentImageType = 'small';
        } else {
            backgroundSize = 'contain';
            currentImageType = 'large';
        }
        callback(backgroundSize);
    };
    img.src = imagePath;
}

function initializeAdvancedZoom(zoomBox) {
    zoomBox.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        targetX = x;
        targetY = y;
        targetSize = (currentImageType === 'small') ? 140 : 160;
    });
    zoomBox.addEventListener('mouseleave', function() {
        targetX = 50;
        targetY = 50;
        targetSize = 100;
    });
    startSmoothAnimation();
}

function startSmoothAnimation() {
    if (animationId) cancelAnimationFrame(animationId);
    function animate() {
        const zoomBox = document.getElementById('zoomBox');
        if (!zoomBox) return;
        currentX += (targetX - currentX) * 0.08;
        currentY += (targetY - currentY) * 0.08;
        currentSize += (targetSize - currentSize) * 0.08;
        zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
        zoomBox.style.backgroundSize = currentSize > 100.1 ? `${currentSize}%` : originalBackgroundSize;
        animationId = requestAnimationFrame(animate);
    }
    animate();
}

function checkMoreButtonVisibility(imageCount) {
    const moreButton = document.querySelector('.gallery-more-button');
    if (moreButton) moreButton.style.display = 'block';
}

function checkMoreButtonForLightbox() {
    fetch('get_poster_images.php?all=true')
        .then(response => response.json())
        .then(data => { if (data.success && data.data) checkMoreButtonVisibility(data.data.length); })
        .catch(error => { console.error('더보기 버튼 확인 오류:', error); });
}

// =================================================================================
// 4. 포스터 가격 계산 함수
// =================================================================================

function initializeCalculator() {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sizeSelect = document.getElementById('PN_type');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');
    const ordertypeSelect = document.getElementById('ordertype');

    if (!typeSelect) return;

    typeSelect.addEventListener('change', function() {
        const style = this.value;
        resetSelectWithText(paperSelect, '용지 재질을 선택해주세요');
        resetSelectWithText(sizeSelect, '규격을 선택해주세요');
        resetSelectWithText(quantitySelect, '수량을 선택해주세요');
        resetPrice();
        if (style) loadPaperTypes(style);
    });

    if (paperSelect) {
        paperSelect.addEventListener('change', function() {
            const section = this.value;
            resetSelectWithText(sizeSelect, '규격을 선택해주세요');
            resetSelectWithText(quantitySelect, '수량을 선택해주세요');
            resetPrice();
            if (section) loadPaperSizes(section);
        });
    }
    
    if (sizeSelect) sizeSelect.addEventListener('change', loadQuantities);
    if (sideSelect) sideSelect.addEventListener('change', loadQuantities);
    
    [typeSelect, paperSelect, sizeSelect, sideSelect, quantitySelect, ordertypeSelect].forEach(select => {
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
    if (priceDetails) {
        priceDetails.innerHTML = '<span>모든 옵션을 선택하면 자동으로 계산됩니다</span>';
        priceDetails.style.display = 'flex';
        priceDetails.style.justifyContent = 'center';
        priceDetails.style.alignItems = 'center';
        priceDetails.style.gap = '15px';
        priceDetails.style.flexWrap = 'nowrap';
        priceDetails.style.whiteSpace = 'nowrap';
        priceDetails.style.flexDirection = 'row';
    }
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
                updateSelectWithOptions(paperSelect, data.data, '용지 재질을 선택해주세요');
                if (data.data.length > 0) {
                    const firstOption = data.data[0];
                    paperSelect.value = firstOption.no;
                    setTimeout(() => loadPaperSizes(firstOption.no), 100);
                }
            } else {
                showUserMessage('재질 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => { console.error('💥 용지 재질 로드 오류:', error); showUserMessage('재질 로드 중 오류가 발생했습니다.', 'error'); });
}

function loadPaperSizes(section) {
    if (!section) return;
    fetch(`get_paper_sizes.php?section=${section}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const sizeSelect = document.getElementById('PN_type');
                updateSelectWithOptions(sizeSelect, data.data, '규격을 선택해주세요');
                if (data.data.length > 0) {
                    const firstSize = data.data[0];
                    sizeSelect.value = firstSize.no;
                    const sideSelect = document.getElementById('POtype');
                    if (sideSelect && !sideSelect.value) sideSelect.value = '1';
                    loadQuantities();
                }
            } else {
                showUserMessage('규격 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => { console.error('💥 규격 로드 오류:', error); showUserMessage('규격 로드 중 오류가 발생했습니다.', 'error'); });
}

function loadQuantities() {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sizeSelect = document.getElementById('PN_type');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');

    if (!typeSelect || !paperSelect || !sizeSelect || !sideSelect || !quantitySelect) return;

    const style = typeSelect.value;
    const section = paperSelect.value;
    const size = sizeSelect.value;
    const potype = sideSelect.value;

    resetSelectWithText(quantitySelect, '수량을 선택해주세요');
    resetPrice();

    if (!style || !section || !size || !potype) return;
    
    const url = `get_quantities.php?style=${style}&section=${section}&pn_type=${size}&potype=${potype}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                let selectedValue = null;
                const option10 = data.data.find(opt => opt.value === '10');
                if (option10) selectedValue = '10';
                else if (data.data.length > 0) selectedValue = data.data[0].value;
                if (selectedValue) {
                    quantitySelect.value = selectedValue;
                    autoCalculatePrice();
                }
            } else {
                showUserMessage('수량 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => { console.error('💥 수량 로드 오류:', error); showUserMessage('수량 로드 중 오류가 발생했습니다.', 'error'); });
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
    const form = document.getElementById('posterForm');
    if (!form || !form.checkValidity()) return;
    calculatePrice(true);
}

function calculatePrice(isAuto = true) {
    const form = document.getElementById('posterForm');
    if (!form) return;
    const formData = new FormData(form);
    if (!formData.get('MY_type') || !formData.get('Section') || !formData.get('POtype') || !formData.get('MY_amount') || !formData.get('ordertype')) return;
    
    const params = new URLSearchParams(formData);
    params.append('additional_options_total', document.getElementById('additional_options_total')?.value || '0');

    fetch('calculate_price_ajax.php?' + params.toString())
    .then(response => { if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`); return response.json(); })
    .then(response => {
        if (response.success) {
            const priceData = response.data;
            currentPriceData = priceData;
            const additionalOptionsTotal = parseInt(document.getElementById('additional_options_total')?.value || 0);
            if (additionalOptionsTotal > 0) {
                priceData.total_price += additionalOptionsTotal;
                priceData.total_with_vat = Math.round(priceData.total_price * 1.1);
            }
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
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const priceDisplay = document.getElementById('priceDisplay');
    const uploadOrderButton = document.getElementById('uploadOrderButton');
    
    if (!priceAmount || !priceDetails || !priceDisplay || !uploadOrderButton) return;

    const supplyPrice = priceData.total_price || (priceData.base_price + priceData.design_price);
    priceAmount.textContent = formatNumber(supplyPrice) + '원';
    
    const additionalOptionsTotal = parseInt(document.getElementById('additional_options_total')?.value || 0);
    const printCost = Math.round(priceData.base_price);
    const designCost = Math.round(priceData.design_price);
    const total = Math.round(supplyPrice * 1.1);
    
    let optionHtml = '';
    if (additionalOptionsTotal > 0) {
        optionHtml = `<div class="price-divider"></div><div class="price-item"><span>추가옵션:</span><span>${formatNumber(additionalOptionsTotal)}원</span></div>`;
    }
    
    priceDetails.innerHTML = `
        <div class="price-breakdown">
            <div class="price-item"><span>인쇄비:</span><span>${formatNumber(printCost)}원</span></div>
            <div class="price-divider"></div>
            <div class="price-item"><span>디자인비:</span><span>${formatNumber(designCost)}원</span></div>
            ${optionHtml}
            <div class="price-divider"></div>
            <div class="price-item final"><span>부가세 포함:</span><span>${formatNumber(total)}원</span></div>
        </div>`;
    
    priceDisplay.classList.add('calculated');
    uploadOrderButton.style.display = 'block';
}

// =================================================================================
// 5. 장바구니 및 주문 기능
// =================================================================================

window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    if (!currentPriceData) return onError("먼저 가격을 계산해주세요.");

    const formData = new FormData(document.getElementById('posterForm'));
    formData.append("action", "add_to_basket");
    formData.append("product_type", "littleprint");
    formData.append("calculated_price", Math.round(currentPriceData.total_price));
    formData.append("calculated_vat_price", Math.round(currentPriceData.total_with_vat));

    const workMemo = document.getElementById("modalWorkMemo")?.value || '';
    if (workMemo) formData.append("work_memo", workMemo);
    formData.append("upload_method", window.selectedUploadMethod || "upload");

    if (uploadedFiles && uploadedFiles.length > 0) {
        uploadedFiles.forEach(file => formData.append("uploaded_files[]", file.file));
        formData.set('uploaded_files_info', JSON.stringify(uploadedFiles.map(f => ({ name: f.name, size: f.size, type: f.type }))));
    }

    fetch("add_to_basket.php", { method: "POST", body: formData })
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
// 6. 추가 옵션 관리자 (from littleprint-premium-options.js)
// =================================================================================

class LittleprintPremiumOptionsManager {
    constructor() {
        this.basePrices = {
            coating: { 'single': 80000, 'double': 160000, 'single_matte': 90000, 'double_matte': 180000 },
            folding: { '2fold': 40000, '3fold': 40000, 'accordion': 70000, 'gate': 100000 },
            creasing: { '1': 30000, '2': 30000, '3': 45000 }
        };
        this.currentQuantity = 1000;
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
        const quantityInput = document.getElementById('MY_amount');
        if (quantityInput) {
            quantityInput.addEventListener('change', (e) => this.updateQuantity(e.target.value));
        }
    }

    handleToggleChange(toggle) {
        const optionType = toggle.id.replace('_enabled', '');
        const detailsDiv = document.getElementById(`${optionType}_options`);
        if (toggle.checked) {
            if (detailsDiv) detailsDiv.style.display = 'block';
        } else {
            if (detailsDiv) {
                detailsDiv.style.display = 'none';
                const select = detailsDiv.querySelector('select');
                if (select) select.value = '';
            }
            const priceField = document.getElementById(`${optionType}_price`);
            if (priceField) priceField.value = '0';
        }
        this.calculateAndUpdatePrice();
    }

    updateQuantity(value) {
        this.currentQuantity = parseInt(value) || 1000;
        this.calculateAndUpdatePrice();
    }

    calculateAndUpdatePrice() {
        let totalPrice = 0;
        const multiplier = Math.max(this.currentQuantity / 1000, 1);

        ['coating', 'folding', 'creasing'].forEach(type => {
            const enabled = document.getElementById(`${type}_enabled`)?.checked;
            const priceField = document.getElementById(`${type}_price`);
            if (enabled) {
                const selected = document.getElementById(`${type}_${type === 'creasing' ? 'lines' : 'type'}`)?.value;
                if (selected && this.basePrices[type][selected]) {
                    const price = Math.round(this.basePrices[type][selected] * multiplier);
                    totalPrice += price;
                    if (priceField) priceField.value = price;
                } else if (priceField) {
                    priceField.value = '0';
                }
            } else if (priceField) {
                priceField.value = '0';
            }
        });

        const totalField = document.getElementById('additional_options_total');
        if (totalField) totalField.value = totalPrice;
        
        this.updatePriceDisplay(totalPrice);
        if (typeof autoCalculatePrice === 'function') autoCalculatePrice();
    }

    updatePriceDisplay(total = 0) {
        const priceElement = document.getElementById('premiumPriceTotal');
        if (priceElement) {
            priceElement.textContent = total > 0 ? `(+${total.toLocaleString()}원)` : '(+0원)';
            priceElement.style.color = total > 0 ? '#1E4E79' : '#999';
        }
    }

    generateAdditionalOptionsJSON() {
        return {
            coating_enabled: document.getElementById('coating_enabled')?.checked ? 1 : 0,
            coating_type: document.getElementById('coating_type')?.value || '',
            coating_price: parseInt(document.getElementById('coating_price')?.value || 0),
            folding_enabled: document.getElementById('folding_enabled')?.checked ? 1 : 0,
            folding_type: document.getElementById('folding_type')?.value || '',
            folding_price: parseInt(document.getElementById('folding_price')?.value || 0),
            creasing_enabled: document.getElementById('creasing_enabled')?.checked ? 1 : 0,
            creasing_lines: document.getElementById('creasing_lines')?.value || '',
            creasing_price: parseInt(document.getElementById('creasing_price')?.value || 0),
            additional_options_total: parseInt(document.getElementById('additional_options_total')?.value || 0)
        };
    }
}

// =================================================================================
// 7. 페이지 초기화
// =================================================================================

document.addEventListener('DOMContentLoaded', function() {
    // 계산기 초기화
    initializeCalculator();

    // 갤러리 초기화
    initializeGallery();

    // 추가 옵션 시스템 초기화
    if (document.getElementById('premiumOptionsSection')) {
        window.littleprintPremiumOptions = new LittleprintPremiumOptionsManager();
    }

    // 기본값이 설정되어 있으면 자동으로 하위 옵션들 로드
    const typeSelect = document.getElementById('MY_type');
    if (typeSelect && typeSelect.value) {
        loadPaperTypes(typeSelect.value);
    }
});
