/**
 * 전단지 페이지 전용 스크립트
 * - leaflet-compact.js
 * - leaflet-premium-options.js
 * - index.php의 인라인 스크립트 통합
 * @version 1.0
 * @date 2025-10-27
 */

// =================================================================================
// 1. 추가 옵션 관리자 (from leaflet-premium-options.js)
// =================================================================================
class LeafletPremiumOptionsManager {
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

        // 각 옵션 가격 계산
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
}

// =================================================================================
// 2. 가격 계산 및 옵션 로딩 (from leaflet-compact.js)
// =================================================================================

function initDropdownEvents() {
    const selectors = {
        'MY_type': () => { loadPaperTypes(getSelectValue('MY_type')); loadPaperSizes(getSelectValue('MY_type')); resetDownstreamSelects(); resetPriceDisplay(); },
        'MY_Fsd': () => { resetDownstreamSelects(); resetPriceDisplay(); updateQuantities(); },
        'PN_type': () => { resetDownstreamSelects(); resetPriceDisplay(); updateQuantities(); },
        'POtype': () => { resetDownstreamSelects(); resetPriceDisplay(); updateQuantities(); },
        'MY_amount': () => { if (typeof autoCalculatePrice === 'function') autoCalculatePrice(); },
        'ordertype': () => { if (typeof autoCalculatePrice === 'function') autoCalculatePrice(); }
    };
    for (const id in selectors) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', selectors[id]);
    }
}

function getSelectValue(id) {
    const el = document.getElementById(id);
    return el ? el.value : '';
}

function resetDownstreamSelects() {
    const quantitySelect = document.getElementById('MY_amount');
    if (quantitySelect) quantitySelect.innerHTML = '<option value="">수량을 선택해주세요</option>';
}

function resetPriceDisplay() {
    const amountEl = document.getElementById('priceAmount');
    const detailsEl = document.getElementById('priceDetails');
    const displayEl = document.getElementById('priceDisplay');
    if (displayEl) displayEl.classList.remove('calculated');
    if (amountEl) amountEl.textContent = '견적 계산 필요';
    if (detailsEl) detailsEl.innerHTML = '옵션을 선택하면<br>실시간으로 가격이 계산됩니다';
    window.currentPriceData = null;
}

function loadPaperTypes(colorNo) {
    fetch(`get_paper_types.php?CV_no=${colorNo}&page=inserted`).then(r => r.json()).then(data => {
        const select = document.getElementById('MY_Fsd');
        if (!select) return;
        select.innerHTML = '<option value="">종이종류를 선택해주세요</option>';
        data.forEach((opt, i) => select.add(new Option(opt.title, opt.no, i === 0, i === 0)));
    });
}

function loadPaperSizes(colorNo) {
    fetch(`get_paper_sizes.php?CV_no=${colorNo}&page=inserted`).then(r => r.json()).then(data => {
        const select = document.getElementById('PN_type');
        if (!select) return;
        select.innerHTML = '<option value="">종이규격을 선택해주세요</option>';
        data.forEach(opt => {
            const isA4 = opt.title.includes('A4') && opt.title.includes('210') && opt.title.includes('297');
            select.add(new Option(opt.title, opt.no, isA4, isA4));
        });
        if (document.querySelector('#PN_type option[selected]')) updateQuantities();
    });
}

function updateQuantities() {
    const params = new URLSearchParams({ MY_type: getSelectValue('MY_type'), PN_type: getSelectValue('PN_type'), MY_Fsd: getSelectValue('MY_Fsd'), POtype: getSelectValue('POtype') });
    if (!params.get('MY_type') || !params.get('PN_type') || !params.get('MY_Fsd')) return;

    fetch(`get_quantities.php?${params.toString()}`).then(r => r.json()).then(data => {
        const select = document.getElementById('MY_amount');
        if (!select) return;
        select.innerHTML = '<option value="">수량을 선택해주세요</option>';
        if (data.length === 0) return;
        data.forEach((opt, i) => select.add(new Option(opt.text, opt.value, i === 0, i === 0)));
        if (data.length > 0) setTimeout(autoCalculatePrice, 100);
    });
}

function autoCalculatePrice() {
    const form = document.getElementById('orderForm');
    if (!form || !form.checkValidity()) return;
    calculatePrice();
}

function calculatePrice() {
    const form = document.getElementById('orderForm');
    if (!form.checkValidity()) return alert('모든 옵션을 선택해주세요.');

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('additional_options_total', document.getElementById('additional_options_total')?.value || '0');

    fetch('calculate_price_ajax.php?' + params.toString()).then(r => r.json()).then(data => {
        if (data.success) {
            window.currentPriceData = data.data;
            updatePriceDisplay(data.data);
        } else {
            resetPriceDisplay();
        }
    }).catch(resetPriceDisplay);
}

function updatePriceDisplay(priceData) {
    const amountEl = document.getElementById('priceAmount');
    const detailsEl = document.getElementById('priceDetails');
    const displayEl = document.getElementById('priceDisplay');
    const uploadBtn = document.getElementById('uploadOrderButton');
    if (!amountEl || !detailsEl || !displayEl || !uploadBtn) return;

    const additionalPrice = parseInt(document.getElementById('additional_options_total')?.value || '0');
    const printCost = Math.round(priceData.PriceForm);
    const designCost = Math.round(priceData.DS_PriceForm);
    const supplyPrice = printCost + designCost + additionalPrice;
    const totalWithVat = Math.round(supplyPrice * 1.1);

    amountEl.textContent = `${supplyPrice.toLocaleString()}원`;
    let detailsHTML = `<div class="price-breakdown"><div class="price-item"><span>인쇄비:</span><span>${printCost.toLocaleString()}원</span></div><div class="price-divider"></div><div class="price-item"><span>디자인비:</span><span>${designCost.toLocaleString()}원</span></div>`;
    if (additionalPrice > 0) {
        detailsHTML += `<div class="price-divider"></div><div class="price-item"><span>추가옵션:</span><span>${additionalPrice.toLocaleString()}원</span></div>`;
    }
    detailsHTML += `<div class="price-divider"></div><div class="price-item final"><span>부가세 포함:</span><span>${totalWithVat.toLocaleString()}원</span></div></div>`;
    detailsEl.innerHTML = detailsHTML;

    displayEl.classList.add('calculated');
    uploadBtn.style.display = 'block';
    
    const priceInput = document.getElementById('calculated_price');
    const vatPriceInput = document.getElementById('calculated_vat_price');
    if (priceInput) priceInput.value = supplyPrice;
    if (vatPriceInput) vatPriceInput.value = totalWithVat;
}

// =================================================================================
// 3. 장바구니 연동 (from inline script)
// =================================================================================

window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    if (!window.currentPriceData) {
        return onError("먼저 견적 계산을 해주세요.");
    }
    const formData = new FormData(document.getElementById('orderForm'));
    formData.append("action", "add_to_basket");
    formData.append("product_type", "inserted");
    formData.append("calculated_price", document.getElementById('calculated_price').value);
    formData.append("calculated_vat_price", document.getElementById('calculated_vat_price').value);
    formData.append("work_memo", document.getElementById("modalWorkMemo")?.value || '');
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

// =================================================================================
// 4. 페이지 초기화
// =================================================================================

document.addEventListener('DOMContentLoaded', () => {
    initDropdownEvents();
    const colorSelect = document.getElementById('MY_type');
    if (colorSelect && colorSelect.value) {
        loadPaperTypes(colorSelect.value);
        loadPaperSizes(colorSelect.value);
    }
    setTimeout(updateQuantities, 1000);

    if (document.getElementById('premiumOptionsSection')) {
        window.leafletPremiumOptions = new LeafletPremiumOptionsManager();
    }
});
