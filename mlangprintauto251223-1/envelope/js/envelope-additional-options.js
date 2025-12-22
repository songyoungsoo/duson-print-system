/**
 * 봉투 양면테이프 추가 옵션 JavaScript
 * 전단지 additional-options.js와 동일한 패턴으로 구현
 *
 * @version 1.0
 * @date 2025-01-17
 */

// 봉투 양면테이프 가격 정보
const ENVELOPE_TAPE_PRICES = {
    500: 25000,
    1000: 40000,
    per_unit: 40  // 1000매 초과시 매당 가격
};

// DOM이 로드되면 초기화
document.addEventListener('DOMContentLoaded', function() {
    initializeEnvelopeOptions();
    syncWithMainEnvelopeQuantity();
});

/**
 * 봉투 추가 옵션 시스템 초기화
 */
function initializeEnvelopeOptions() {
    const envelopeTapeToggle = document.getElementById('envelope_tape_enabled');
    const envelopeTapeOptions = document.getElementById('envelope_tape_options');
    const envelopeTapeQuantity = document.getElementById('envelope_tape_quantity');
    const customQuantityInput = document.getElementById('custom_quantity_input');
    const customQtyField = document.getElementById('envelope_tape_custom_qty');

    if (!envelopeTapeToggle) {
        console.log('봉투 추가 옵션 요소를 찾을 수 없습니다.');
        return;
    }

    // 체크박스 토글 이벤트
    envelopeTapeToggle.addEventListener('change', function() {
        if (this.checked) {
            envelopeTapeOptions.style.display = 'block';
            calculateEnvelopeOptionPrice();
        } else {
            envelopeTapeOptions.style.display = 'none';
            updateEnvelopeOptionPrice(0);
        }
        updateMainPrice(); // 메인 가격 계산기 업데이트
    });

    // 수량 선택 변경 이벤트
    if (envelopeTapeQuantity) {
        envelopeTapeQuantity.addEventListener('change', function() {
            if (this.value === 'custom') {
                customQuantityInput.style.display = 'block';
                customQtyField.focus();
            } else {
                customQuantityInput.style.display = 'none';
                calculateEnvelopeOptionPrice();
            }
            updateMainPrice(); // 메인 가격 계산기 업데이트
        });
    }

    // 사용자 정의 수량 입력 이벤트
    if (customQtyField) {
        customQtyField.addEventListener('input', function() {
            calculateEnvelopeOptionPrice();
            updateMainPrice(); // 메인 가격 계산기 업데이트
        });
    }

    console.log('봉투 추가 옵션 시스템 초기화 완료');
}

/**
 * 봉투 메인 수량과 양면테이프 수량 동기화
 */
function syncWithMainEnvelopeQuantity() {
    // 봉투 메인 수량 필드 찾기 (일반적인 필드명들 확인)
    const quantityFields = ['MY_amount', 'quantity', 'envelope_quantity'];
    let mainQuantityField = null;

    for (let fieldName of quantityFields) {
        const field = document.getElementById(fieldName) || document.querySelector(`select[name="${fieldName}"]`);
        if (field) {
            mainQuantityField = field;
            break;
        }
    }

    if (mainQuantityField) {
        // 메인 수량 변경 시 양면테이프 수량도 동기화
        mainQuantityField.addEventListener('change', function() {
            updateTapeQuantityFromMain(this.value);
        });

        // 초기값 동기화
        updateTapeQuantityFromMain(mainQuantityField.value);

        console.log('봉투 수량 동기화 설정 완료:', mainQuantityField.id || mainQuantityField.name);
    } else {
        console.log('봉투 메인 수량 필드를 찾을 수 없습니다. 수동 설정 모드로 작동합니다.');
    }
}

/**
 * 메인 수량에 따라 양면테이프 수량 업데이트
 */
function updateTapeQuantityFromMain(mainQuantity) {
    const envelopeTapeToggle = document.getElementById('envelope_tape_enabled');
    const envelopeTapeQuantity = document.getElementById('envelope_tape_quantity');
    const customQtyField = document.getElementById('envelope_tape_custom_qty');

    if (!envelopeTapeToggle || !envelopeTapeToggle.checked) {
        return; // 양면테이프가 활성화되지 않은 경우 동기화하지 않음
    }

    // 수량을 숫자로 변환 (텍스트에서 숫자 추출)
    const numericQuantity = parseInt(String(mainQuantity).replace(/[^0-9]/g, '')) || 0;

    if (numericQuantity > 0) {
        // 기존 옵션에 맞는 수량인지 확인
        if (numericQuantity <= 500) {
            envelopeTapeQuantity.value = '500';
        } else if (numericQuantity <= 1000) {
            envelopeTapeQuantity.value = '1000';
        } else {
            // 1000매 초과인 경우 custom으로 설정하고 동일한 수량 입력
            envelopeTapeQuantity.value = 'custom';
            customQtyField.value = numericQuantity;
            document.getElementById('custom_quantity_input').style.display = 'block';
        }

        // 가격 재계산
        calculateEnvelopeOptionPrice();

        console.log(`양면테이프 수량을 봉투 수량(${numericQuantity}매)과 동기화했습니다.`);
    }
}

/**
 * 봉투 옵션 가격 계산
 */
function calculateEnvelopeOptionPrice() {
    const envelopeTapeToggle = document.getElementById('envelope_tape_enabled');
    const envelopeTapeQuantity = document.getElementById('envelope_tape_quantity');
    const customQtyField = document.getElementById('envelope_tape_custom_qty');

    if (!envelopeTapeToggle || !envelopeTapeToggle.checked) {
        updateEnvelopeOptionPrice(0);
        return;
    }

    let quantity = 0;
    let price = 0;

    if (envelopeTapeQuantity.value === 'custom') {
        quantity = parseInt(customQtyField.value) || 0;
        if (quantity > 0) {
            if (quantity <= 500) {
                price = ENVELOPE_TAPE_PRICES[500];
            } else if (quantity <= 1000) {
                price = ENVELOPE_TAPE_PRICES[1000];
            } else {
                price = quantity * ENVELOPE_TAPE_PRICES.per_unit;
            }
        }
    } else {
        quantity = parseInt(envelopeTapeQuantity.value);
        price = ENVELOPE_TAPE_PRICES[quantity] || 0;
    }

    updateEnvelopeOptionPrice(price);

    // 숨겨진 필드 업데이트
    document.getElementById('envelope_tape_price').value = price;
    document.getElementById('envelope_additional_options_total').value = price;

    console.log(`봉투 양면테이프 가격 계산: ${quantity}매 = ${price.toLocaleString()}원`);
}

/**
 * 봉투 옵션 가격 표시 업데이트
 */
function updateEnvelopeOptionPrice(price) {
    const priceDisplay = document.getElementById('envelopeOptionPriceTotal');
    if (priceDisplay) {
        if (price > 0) {
            priceDisplay.textContent = `(+${price.toLocaleString()}원)`;
            priceDisplay.style.color = '#e53e3e';
            priceDisplay.style.fontWeight = 'bold';
        } else {
            priceDisplay.textContent = '(+0원)';
            priceDisplay.style.color = '#666';
            priceDisplay.style.fontWeight = 'normal';
        }
    }
}

/**
 * 메인 가격 계산기 업데이트 (envelope.js의 함수 호출)
 */
function updateMainPrice() {
    // envelope.js에 있는 가격 계산 함수가 있다면 호출
    if (typeof calculatePrice === 'function') {
        calculatePrice();
    } else if (typeof updatePriceDisplay === 'function') {
        updatePriceDisplay();
    } else {
        console.log('메인 가격 계산 함수를 찾을 수 없습니다.');
    }
}

/**
 * 현재 선택된 봉투 옵션 정보 반환
 */
function getEnvelopeOptionsData() {
    const envelopeTapeToggle = document.getElementById('envelope_tape_enabled');
    const envelopeTapeQuantity = document.getElementById('envelope_tape_quantity');
    const customQtyField = document.getElementById('envelope_tape_custom_qty');

    if (!envelopeTapeToggle || !envelopeTapeToggle.checked) {
        return null;
    }

    let quantity = 0;
    if (envelopeTapeQuantity.value === 'custom') {
        quantity = parseInt(customQtyField.value) || 0;
    } else {
        quantity = parseInt(envelopeTapeQuantity.value);
    }

    return {
        envelope_tape_enabled: 1,
        envelope_tape_quantity: quantity,
        envelope_tape_price: parseInt(document.getElementById('envelope_tape_price').value) || 0
    };
}

/**
 * 폼 제출 전 검증
 */
function validateEnvelopeOptions() {
    const envelopeTapeToggle = document.getElementById('envelope_tape_enabled');
    const envelopeTapeQuantity = document.getElementById('envelope_tape_quantity');
    const customQtyField = document.getElementById('envelope_tape_custom_qty');

    if (envelopeTapeToggle && envelopeTapeToggle.checked) {
        if (envelopeTapeQuantity.value === 'custom') {
            const customQty = parseInt(customQtyField.value) || 0;
            if (customQty < 1) {
                alert('수량을 입력해주세요.');
                customQtyField.focus();
                return false;
            }
        }
    }

    return true;
}

// 전역 함수로 노출 (다른 스크립트에서 사용 가능)
window.envelopeOptions = {
    calculate: calculateEnvelopeOptionPrice,
    getData: getEnvelopeOptionsData,
    validate: validateEnvelopeOptions,
    updateMain: updateMainPrice,
    syncQuantity: updateTapeQuantityFromMain,
    init: syncWithMainEnvelopeQuantity
};