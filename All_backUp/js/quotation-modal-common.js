/**
 * Quotation Modal Common JavaScript
 * 견적서 모달에서 계산기를 사용할 때 공통으로 사용하는 기능
 * Created: 2025-12-18
 */

/**
 * 견적서에 적용 버튼 클릭 시 실행
 * 현재 계산된 가격 정보를 부모 창(견적서 페이지)으로 전달
 */
function applyToQuotation() {
    try {
        // 현재 가격 데이터 확인
        if (!window.currentPriceData) {
            alert('먼저 옵션을 선택하여 가격을 계산해주세요.');
            return;
        }

        const priceData = window.currentPriceData;

        // 필수 데이터 검증
        if (!priceData.total_price || priceData.total_price <= 0) {
            alert('가격이 계산되지 않았습니다. 모든 옵션을 선택해주세요.');
            return;
        }

        // 제품 정보 수집
        const productData = {
            // 가격 정보
            supply_price: priceData.total_price || 0,
            vat_price: priceData.vat_price || 0,
            vat_amount: Math.round((priceData.total_price || 0) * 0.1),
            total_price: (priceData.total_price || 0) + Math.round((priceData.total_price || 0) * 0.1),

            // 제품 정보 (각 제품별로 커스터마이징 필요)
            product_type: getProductType(),
            product_name: getProductName(),
            specification: getProductSpecification(),
            quantity: getProductQuantity(),
            unit: getProductUnit(),

            // 추가 정보
            options: getSelectedOptions(),
            notes: getProductNotes()
        };

        console.log('견적서에 적용할 데이터:', productData);

        // 부모 창(견적서 페이지)으로 데이터 전달
        if (window.parent && window.parent !== window) {
            window.parent.postMessage({
                type: 'QUOTATION_APPLY',
                data: productData
            }, '*');

            // 성공 메시지 표시 후 모달 닫기
            alert('견적서에 적용되었습니다!');

            // 모달 닫기 시도
            if (window.parent.closeCalculatorModal) {
                window.parent.closeCalculatorModal();
            }
        } else {
            alert('견적서 페이지에서만 사용 가능합니다.');
        }

    } catch (error) {
        console.error('견적서 적용 오류:', error);
        alert('견적서 적용 중 오류가 발생했습니다.');
    }
}

/**
 * 제품 타입 반환 (각 제품별로 오버라이드 필요)
 */
function getProductType() {
    // body 클래스에서 제품 타입 추출
    const bodyClasses = document.body.className;
    if (bodyClasses.includes('namecard-page')) return 'namecard';
    if (bodyClasses.includes('envelope-page')) return 'envelope';
    if (bodyClasses.includes('msticker-page')) return 'msticker';
    if (bodyClasses.includes('cadarok-page')) return 'cadarok';
    if (bodyClasses.includes('littleprint-page')) return 'littleprint';
    if (bodyClasses.includes('merchandisebond-page')) return 'merchandisebond';
    if (bodyClasses.includes('ncrflambeau-page')) return 'ncrflambeau';
    if (bodyClasses.includes('inserted-page')) return 'inserted';
    if (bodyClasses.includes('sticker-page')) return 'sticker';
    return 'unknown';
}

/**
 * 제품명 반환
 */
function getProductName() {
    const h1 = document.querySelector('.page-title h1, h1');
    if (h1) {
        return h1.textContent.trim().replace(/📋|📄|🎁|📝|🏷️|📰|✨/g, '').trim();
    }
    return '제품명';
}

/**
 * 제품 규격 반환 (각 제품별로 커스터마이징 필요)
 */
function getProductSpecification() {
    const specs = [];

    // 공통 규격 필드 수집
    const form = document.querySelector('form');
    if (form) {
        // 사이즈/규격
        const sizeSelect = form.querySelector('[name="MY_type"], [name="size"], [name="규격"]');
        if (sizeSelect && sizeSelect.selectedOptions[0]) {
            specs.push(sizeSelect.selectedOptions[0].text);
        }

        // 용지/재질
        const paperSelect = form.querySelector('[name="Section"], [name="paper"], [name="용지"]');
        if (paperSelect && paperSelect.selectedOptions[0]) {
            specs.push(paperSelect.selectedOptions[0].text);
        }

        // 수량
        const quantitySelect = form.querySelector('[name="MY_amount"], [name="quantity"], [name="수량"]');
        if (quantitySelect && quantitySelect.selectedOptions[0]) {
            specs.push(quantitySelect.selectedOptions[0].text);
        }
    }

    return specs.join(' / ') || '규격 정보';
}

/**
 * 제품 수량 반환
 */
function getProductQuantity() {
    const form = document.querySelector('form');
    if (form) {
        const quantitySelect = form.querySelector('[name="MY_amount"], [name="quantity"], [name="수량"]');
        if (quantitySelect) {
            // 숫자만 추출
            const value = quantitySelect.value;
            const numericValue = parseFloat(value);
            return isNaN(numericValue) ? 1 : numericValue;
        }
    }
    return 1;
}

/**
 * 제품 단위 반환
 */
function getProductUnit() {
    const productType = getProductType();

    // 제품별 기본 단위
    const unitMap = {
        'namecard': '매',
        'envelope': '매',
        'msticker': '매',
        'cadarok': '부',
        'littleprint': '매',
        'merchandisebond': '매',
        'ncrflambeau': '권',
        'inserted': '연',
        'sticker': '매'
    };

    return unitMap[productType] || '개';
}

/**
 * 선택된 옵션 정보 반환
 */
function getSelectedOptions() {
    const options = [];

    // 추가 옵션 체크박스 수집
    const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
    checkboxes.forEach(cb => {
        const label = cb.closest('label') || document.querySelector(`label[for="${cb.id}"]`);
        if (label) {
            options.push(label.textContent.trim());
        }
    });

    return options.join(', ');
}

/**
 * 제품 비고사항 반환
 */
function getProductNotes() {
    // 작업메모 필드가 있으면 반환
    const memoField = document.querySelector('[name="work_memo"], [name="memo"], [name="비고"]');
    if (memoField) {
        return memoField.value || '';
    }
    return '';
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    console.log('Quotation Modal Common JS loaded');

    // 견적서 모달 모드인지 확인
    const isQuotationMode = document.body.classList.contains('quotation-modal-mode');
    if (isQuotationMode) {
        console.log('견적서 모달 모드 활성화');
    }
});
