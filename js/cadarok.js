/**
 * 카다록/리플렛 견적안내 컴팩트 시스템 - 템플릿 기반 생성
 * Envelope/Namecard 시스템 구조를 카다록에 적용
 */

// 전역 변수
window.currentPriceData = null;

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    initializeCalculator();
    
    const typeSelect = document.getElementById('MY_type');
    if (typeSelect && typeSelect.value) {
        loadPaperTypes(typeSelect.value);
    }
});

function initializeCalculator() {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');
    const ordertypeSelect = document.getElementById('ordertype');

    if (!typeSelect) return;

    typeSelect.addEventListener('change', function() {
        const style = this.value;
        resetSelectWithText(paperSelect, '재질을 선택해주세요');
        resetSelectWithText(quantitySelect, '수량을 선택해주세요');
        resetPrice();
        if (style) {
            loadPaperTypes(style);
        }
    });

    if (paperSelect) {
        paperSelect.addEventListener('change', loadQuantities);
    }
    if (sideSelect) {
        sideSelect.addEventListener('change', loadQuantities);
    }
    
    [typeSelect, paperSelect, sideSelect, quantitySelect, ordertypeSelect].forEach(select => {
        if (select) {
            select.addEventListener('change', autoCalculatePrice);
        }
    });
}

function resetSelectWithText(selectElement, defaultText) {
    if (selectElement) {
        selectElement.innerHTML = `<option value="">${defaultText}</option>`;
    }
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
    
    window.currentPriceData = null;
}

function loadPaperTypes(style) {
    if (!style) return;

    fetch(`/mlangprintauto/cadarok/get_options.php?type=paper&style=${style}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const paperSelect = document.getElementById('Section');
                updateSelectWithOptions(paperSelect, data.data, '재질을 선택해주세요');
                
                const defaultSection = paperSelect.dataset.defaultValue;
                if (defaultSection && Array.from(paperSelect.options).some(opt => opt.value == defaultSection)) {
                    paperSelect.value = defaultSection;
                } else if (data.data.length > 0) {
                    paperSelect.value = data.data[0].no;
                }

                if (paperSelect.value) {
                    loadQuantities();
                }

            } else {
                showUserMessage('재질 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('재질 로드 오류:', error);
            showUserMessage('재질 로드 중 오류가 발생했습니다.', 'error');
        });
}

function loadQuantities() {
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

    fetch(`/mlangprintauto/cadarok/get_options.php?type=quantity&style=${style}&section=${section}&potype=${potype}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                
                const defaultQuantity = quantitySelect.dataset.defaultValue;
                if (defaultQuantity && Array.from(quantitySelect.options).some(opt => opt.value == defaultQuantity)) {
                    quantitySelect.value = defaultQuantity;
                } else if (data.data.length > 0) {
                    quantitySelect.value = data.data[0].value;
                }
                if (quantitySelect.value) {
                    autoCalculatePrice();
                }

            } else {
                showUserMessage('수량 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('수량 로드 오류:', error);
            showUserMessage('수량 로드 중 오류가 발생했습니다.', 'error');
        });
}

function updateSelectWithOptions(selectElement, options, defaultOptionText) {
    if (!selectElement) return;
    
    selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
    if (options) {
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
    if (!form) return;
    
    const formData = new FormData(form);
    
    if (!formData.get('MY_type') || !formData.get('Section') || 
        !formData.get('POtype') || !formData.get('MY_amount') || 
        !formData.get('ordertype')) {
        return;
    }
    
    calculatePrice(true);
}

function calculatePrice(isAuto = true) {
    const form = document.getElementById('cadarokForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    if (!formData.get('MY_type') || !formData.get('Section') || 
        !formData.get('POtype') || !formData.get('MY_amount') || 
        !formData.get('ordertype')) {
        return;
    }
    
    const params = new URLSearchParams(formData);
    
    fetch('/mlangprintauto/cadarok/calculate_price_ajax.php?' + params.toString())
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(response => {
        if (response.success) {
            updatePriceDisplay(response.data);
        } else {
            resetPrice();
            if (!isAuto) {
                showUserMessage('가격 계산 실패: ' + (response.message || '알 수 없는 오류'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('가격 계산 오류:', error);
        if (!isAuto) {
            showUserMessage('가격 계산 중 오류가 발생했습니다.', 'error');
        }
    });
}

function updatePriceDisplay(priceData) {
    const priceDisplay = document.getElementById('priceDisplay');
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const uploadOrderButton = document.getElementById('uploadOrderButton');
    
    const supplyPrice = priceData.total_price || (priceData.base_price + priceData.design_price);
    const totalWithVat = Math.round(priceData.total_with_vat);

    if (priceAmount) {
        priceAmount.textContent = formatNumber(supplyPrice) + '원';
    }
    
    if (priceDetails) {
        priceDetails.innerHTML = `
            <span>인쇄비: ${formatNumber(priceData.base_price)}원</span>
            <span>디자인비: ${formatNumber(priceData.design_price)}원</span>
            <span>부가세 포함: <span class="vat-amount">${formatNumber(totalWithVat)}원</span></span>
        `;
    }
    
    if (priceDisplay) {
        priceDisplay.classList.add('calculated');
    }

    window.currentPriceData = {
        Order_PriceForm: supplyPrice,
        Total_PriceForm: totalWithVat,
        ...priceData
    };
    
    if (uploadOrderButton) {
        uploadOrderButton.style.display = 'block';
    }
}

function showUserMessage(message, type = 'info') {
    alert(message);
}

function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}