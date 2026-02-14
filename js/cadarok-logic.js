/**
 * 카다록/리플렛 페이지 전용 스크립트
 * - GalleryLightbox.js
 * - cadarok.js
 * - cadarok-premium-options.js
 * - index.php의 인라인 스크립트 통합
 * @version 1.2
 * @date 2025-10-28
 */

// =================================================================================
// 1. 전역 변수 (common-unified.js와 중복되지 않는 고유 변수)
// =================================================================================
let currentPriceData = null; // common-unified.js의 window.currentPriceData와 별개로 관리

// =================================================================================
// 2. 유틸리티 함수 (common-unified.js와 중복되지 않는 고유 유틸리티)
// =================================================================================

function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// =================================================================================
// 3. 카다록 가격 계산 함수 (from cadarok.js)
// =================================================================================

function initializeCalculator() {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');
    const ordertypeSelect = document.getElementById('ordertype');

    if (!typeSelect) return;

    // URL 파라미터에서 type/section 읽기 (네비 드롭다운에서 진입 시)
    const urlParams = new URLSearchParams(window.location.search);
    const urlType = urlParams.get('type');
    const urlSection = urlParams.get('section');

    if (urlType) {
        typeSelect.value = urlType;
        console.log('🎯 URL 파라미터로 카다록 종류 선택:', urlType);
    }
    if (urlSection && paperSelect) {
        paperSelect.dataset.defaultValue = urlSection;
        console.log('🎯 URL 파라미터로 카다록 재질 예약:', urlSection);
    }

    typeSelect.addEventListener('change', function() {
        const style = this.value;
        resetSelectWithText(paperSelect, '재질을 선택해주세요');
        resetSelectWithText(quantitySelect, '수량을 선택해주세요');
        resetPrice();
        if (style) loadPaperTypes(style, loadQuantities);
    });

    if (paperSelect) paperSelect.addEventListener('change', function() { loadQuantities(autoCalculatePrice); });
    if (sideSelect) sideSelect.addEventListener('change', function() { loadQuantities(autoCalculatePrice); });
    
    [quantitySelect, ordertypeSelect].forEach(select => {
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
    if (priceDetails) priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
    if (priceDisplay) priceDisplay.classList.remove('calculated');
    if (uploadOrderButton) uploadOrderButton.style.display = 'none';
    currentPriceData = null;
}

function loadPaperTypes(style, callback) {
    if (!style) return;
    fetch(`/mlangprintauto/cadarok/get_paper_types.php?style=${style}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const paperSelect = document.getElementById('Section');
                updateSelectWithOptions(paperSelect, data.data, '재질을 선택해주세요');
                const defaultSection = paperSelect.dataset.defaultValue;
                if (defaultSection) {
                    paperSelect.value = defaultSection;
                }
                if (callback) callback();
            } else {
                showUserMessage('재질 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => { console.error('재질 로드 오류:', error); showUserMessage('재질 로드 중 오류가 발생했습니다.', 'error'); });
}

function loadQuantities(callback) {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');

    if (!typeSelect || !paperSelect || !sideSelect || !quantitySelect) return;

    const style = typeSelect.value;
    const section = paperSelect.value;
    const potype = sideSelect.value;

    resetSelectWithText(quantitySelect, '수량을 선택해주세요');
    resetPrice();

    if (!style || !section || !potype) return;

    fetch(`/mlangprintauto/cadarok/get_quantities.php?style=${style}&section=${section}&potype=${potype}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                const defaultQuantity = quantitySelect.dataset.defaultValue;
                if (defaultQuantity) {
                    quantitySelect.value = defaultQuantity;
                }
                if (callback) callback();
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
    const form = document.getElementById('cadarokForm');
    if (!form || !form.checkValidity()) return;
    calculatePrice(true);
}

function calculatePrice(isAuto = true) {
    const form = document.getElementById('cadarokForm');
    if (!form) return;
    const formData = new FormData(form);
    if (!formData.get('MY_type') || !formData.get('Section') || !formData.get('POtype') || !formData.get('MY_amount') || !formData.get('ordertype')) return;
    
    const params = new URLSearchParams(formData);
    params.append('additional_options_total', document.getElementById('additional_options_total')?.value || '0');

    fetch('/mlangprintauto/cadarok/calculate_price_ajax.php?' + params.toString())
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
// 4. 장바구니 및 주문 기능
// =================================================================================

window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    if (!currentPriceData) return onError("먼저 가격을 계산해주세요.");

    const formData = new FormData(document.getElementById('cadarokForm'));
    formData.append("action", "add_to_basket");
    formData.append("product_type", "cadarok");
    formData.append("calculated_price", Math.round(currentPriceData.total_price));
    formData.append("calculated_vat_price", Math.round(currentPriceData.total_with_vat));

    const workMemo = document.getElementById("modalWorkMemo")?.value || '';
    if (workMemo) formData.append("work_memo", workMemo);
    formData.append("upload_method", window.selectedUploadMethod || "upload");

    if (uploadedFiles && uploadedFiles.length > 0) {
        uploadedFiles.forEach(file => formData.append("uploaded_files[]", file.file));
        formData.set('uploaded_files_info', JSON.stringify(uploadedFiles.map(f => ({ name: f.name, size: f.size, type: f.type }))));
    }

    fetch('/mlangprintauto/cadarok/add_to_basket.php', { method: "POST", body: formData })
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
// 5. 추가 옵션 관리자 (from cadarok-premium-options.js)
// =================================================================================

class CadarokPremiumOptionsManager {
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
// 6. 페이지 초기화
// =================================================================================

document.addEventListener('DOMContentLoaded', function() {
    // 계산기 초기화
    initializeCalculator();

    // 추가 옵션 시스템 초기화
    if (document.getElementById('premiumOptionsSection')) {
        window.cadarokPremiumOptions = new CadarokPremiumOptionsManager();
    }

    // 페이지 로드 시 자동 계산 체인 시작
    setTimeout(function() {
        console.log("✅ cadarok-logic.js 로드 및 자동 계산 시작");
        const typeSelect = document.getElementById('MY_type');
        if (typeSelect && typeSelect.value) {
            console.log('-> 1단계: 종류 자동 선택됨:', typeSelect.value);
            // 1. 재질 로드 시작
            loadPaperTypes(typeSelect.value, function() {
                console.log('-> 2단계: 재질 로드 완료, 수량 로드 시작');
                // 2. 수량 로드 시작 (재질 로드 콜백에서 실행)
                loadQuantities(function() {
                    console.log('-> 3단계: 수량 로드 완료, 최종 가격 계산 시작');
                    // 3. 최종 가격 계산 (수량 로드 콜백에서 실행)
                    autoCalculatePrice();
                });
            });
        }
    }, 100);
});

window.calculatePrice = calculatePrice;