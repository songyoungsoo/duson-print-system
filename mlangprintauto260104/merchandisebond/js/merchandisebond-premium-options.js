/**
 * 상품권 프리미엄 옵션 JavaScript 모듈 (명함 방식 적용)
 *
 * 목적: 박, 넘버링, 미싱, 귀돌이, 오시 등 프리미엄 옵션의 동적 처리
 * 특징: 실시간 가격 계산 및 UI 업데이트
 *
 * @version 1.0
 * @date 2025-10-09
 * @author SuperClaude Premium Options System (MerchandiseBond Edition)
 */

class MerchandiseBondPremiumOptionsManager {
    constructor() {
        this.basePrices = {
            foil: {
                base_500: 30000,  // 500매 이하 기본 가격
                per_unit: 12,     // 500매 초과시 매당 가격
                types: {
                    'gold_matte': '금박무광',
                    'gold_gloss': '금박유광',
                    'silver_matte': '은박무광',
                    'silver_gloss': '은박유광',
                    'blue_gloss': '청박유광',
                    'red_gloss': '적박유광',
                    'green_gloss': '녹박유광',
                    'black_gloss': '먹박유광'
                }
            },
            numbering: {
                single: { base_500: 60000, per_unit: 12 },
                double: { base_500: 75000, per_unit: 12, additional_fee: 15000 }
            },
            perforation: {
                single: { base_500: 20000, per_unit: 25 },
                double: { base_500: 35000, per_unit: 25, additional_fee: 15000 }
            },
            rounding: {
                base_500: 6000, per_unit: 12
            },
            creasing: {
                '1line': { base_500: 20000, per_unit: 25 },
                '2line': { base_500: 20000, per_unit: 25 },
                '3line': { base_500: 35000, per_unit: 25, additional_fee: 15000 }
            }
        };

        this.currentQuantity = 500; // 기본 수량
        this.init();
    }

    /**
     * 초기화
     */
    init() {
        console.log('상품권 프리미엄 옵션 시스템 초기화');
        this.setupEventListeners();
        this.updatePriceDisplay();
    }

    /**
     * 이벤트 리스너 설정
     */
    setupEventListeners() {
        // 체크박스 토글 이벤트
        const toggles = document.querySelectorAll('.option-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                this.handleToggleChange(e.target);
            });
        });

        // 옵션 선택 변경 이벤트
        const selects = document.querySelectorAll('.option-details select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                this.calculateAndUpdatePrice();
            });
        });

        // 메인 수량 변경 감지 (상품권 전용)
        const quantitySelect = document.getElementById('MY_amount');
        if (quantitySelect) {
            // 중복 이벤트 방지를 위한 네임스페이스 사용
            quantitySelect.removeEventListener('change', this.quantityChangeHandler);
            this.quantityChangeHandler = (e) => {
                console.log('프리미엄옵션: 수량 변경 감지:', e.target.value);
                this.updateQuantity(e.target.value);
            };
            quantitySelect.addEventListener('change', this.quantityChangeHandler);
        }
    }

    /**
     * 체크박스 토글 처리
     */
    handleToggleChange(toggle) {
        const optionType = toggle.id.replace('_enabled', '');
        const detailsDiv = document.getElementById(`${optionType}_options`);

        if (toggle.checked) {
            if (detailsDiv) {
                detailsDiv.style.display = 'block';
                detailsDiv.classList.add('show');
                detailsDiv.classList.remove('hide');
            }
            console.log(`${optionType} 옵션 활성화`);
        } else {
            if (detailsDiv) {
                detailsDiv.style.display = 'none';
                detailsDiv.classList.add('hide');
                detailsDiv.classList.remove('show');
            }

            // 숨겨진 필드 초기화
            this.resetOptionFields(optionType);
            console.log(`${optionType} 옵션 비활성화`);
        }

        this.calculateAndUpdatePrice();
    }

    /**
     * 옵션 필드 초기화
     */
    resetOptionFields(optionType) {
        const priceField = document.getElementById(`${optionType}_price`);
        if (priceField) {
            priceField.value = '0';
        }
    }

    /**
     * 수량 업데이트
     */
    updateQuantity(quantityValue) {
        this.currentQuantity = parseInt(quantityValue) || 500;
        console.log('프리미엄 옵션 수량 업데이트:', this.currentQuantity);
        this.calculateAndUpdatePrice();
    }

    /**
     * 개별 옵션 가격 계산
     */
    calculateOptionPrice(optionType, subType, quantity) {
        const config = this.basePrices[optionType];
        if (!config) return 0;

        quantity = quantity || this.currentQuantity;

        // 단순 가격 구조 (박, 귀돌이)
        if (config.base_500 !== undefined) {
            if (quantity <= 500) {
                return config.base_500;
            } else {
                const additionalUnits = quantity - 500;
                return config.base_500 + (additionalUnits * config.per_unit);
            }
        }

        // 복합 가격 구조 (넘버링, 미싱, 오시)
        if (config[subType]) {
            const subConfig = config[subType];
            if (quantity <= 500) {
                return subConfig.base_500;
            } else {
                const basePrice = subConfig.base_500;
                const additionalUnits = quantity - 500;
                const additionalFee = subConfig.additional_fee || 0;
                return basePrice + (additionalUnits * subConfig.per_unit) + additionalFee;
            }
        }

        return 0;
    }

    /**
     * 가격 계산 및 UI 업데이트
     */
    calculateAndUpdatePrice() {
        let totalOptionsPrice = 0;
        const optionDetails = [];

        // 박 옵션 계산
        if (document.getElementById('foil_enabled')?.checked) {
            const foilType = document.getElementById('foil_type')?.value || 'gold_matte';
            const price = this.calculateOptionPrice('foil', null, this.currentQuantity);
            totalOptionsPrice += price;

            document.getElementById('foil_price').value = price;
            optionDetails.push({
                name: `박(${this.basePrices.foil.types[foilType] || foilType})`,
                price: price
            });

            console.log('박 가격:', price);
        } else {
            document.getElementById('foil_price').value = '0';
        }

        // 넘버링 옵션 계산
        if (document.getElementById('numbering_enabled')?.checked) {
            const numberingType = document.getElementById('numbering_type')?.value || 'single';
            const price = this.calculateOptionPrice('numbering', numberingType, this.currentQuantity);
            totalOptionsPrice += price;

            document.getElementById('numbering_price').value = price;
            optionDetails.push({
                name: `넘버링(${numberingType === 'double' ? '2개' : '1개'})`,
                price: price
            });

            console.log('넘버링 가격:', price);
        } else {
            document.getElementById('numbering_price').value = '0';
        }

        // 미싱(절취선) 옵션 계산
        if (document.getElementById('perforation_enabled')?.checked) {
            const perforationType = document.getElementById('perforation_type')?.value || 'single';
            const price = this.calculateOptionPrice('perforation', perforationType, this.currentQuantity);
            totalOptionsPrice += price;

            document.getElementById('perforation_price').value = price;
            optionDetails.push({
                name: `미싱(${perforationType === 'double' ? '2개' : '1개'})`,
                price: price
            });

            console.log('미싱 가격:', price);
        } else {
            document.getElementById('perforation_price').value = '0';
        }

        // 귀돌이 옵션 계산
        if (document.getElementById('rounding_enabled')?.checked) {
            const price = this.calculateOptionPrice('rounding', null, this.currentQuantity);
            totalOptionsPrice += price;

            document.getElementById('rounding_price').value = price;
            optionDetails.push({
                name: '귀돌이',
                price: price
            });

            console.log('귀돌이 가격:', price);
        } else {
            document.getElementById('rounding_price').value = '0';
        }

        // 오시 옵션 계산
        if (document.getElementById('creasing_enabled')?.checked) {
            const creasingType = document.getElementById('creasing_type')?.value || '1line';
            const price = this.calculateOptionPrice('creasing', creasingType, this.currentQuantity);
            totalOptionsPrice += price;

            document.getElementById('creasing_price').value = price;
            const lineCount = creasingType.replace('line', '') + '줄';
            optionDetails.push({
                name: `오시(${lineCount})`,
                price: price
            });

            console.log('오시 가격:', price);
        } else {
            document.getElementById('creasing_price').value = '0';
        }

        // 총 프리미엄 옵션 가격 업데이트
        document.getElementById('premium_options_total').value = totalOptionsPrice;

        // UI 업데이트
        this.updatePriceDisplay(totalOptionsPrice, optionDetails);

        // 메인 가격 계산 함수 호출 (상품권 전용) - 자동 모드로 호출
        if (typeof calculatePrice === 'function') {
            calculatePrice(); // 메인 계산 함수에서 프리미엄 옵션 가격을 포함하여 재계산
        }

        console.log('총 프리미엄 옵션 가격:', totalOptionsPrice, '수량:', this.currentQuantity);
    }

    /**
     * 가격 표시 UI 업데이트
     */
    updatePriceDisplay(totalOptionsPrice = 0, optionDetails = []) {
        // 프리미엄 옵션 총액 표시 업데이트
        const optionPriceTotal = document.getElementById('premiumPriceTotal');
        if (optionPriceTotal) {
            if (totalOptionsPrice > 0) {
                optionPriceTotal.textContent = `(+${totalOptionsPrice.toLocaleString()}원)`;
                optionPriceTotal.style.color = '#d4af37'; // 골드 색상
            } else {
                optionPriceTotal.textContent = '(+0원)';
                optionPriceTotal.style.color = '#718096';
            }
        }

        // 메인 가격 표시에 프리미엄 옵션 정보 추가는 메인 updatePriceDisplay 함수에서 처리
    }

    /**
     * 현재 선택된 프리미엄 옵션 정보 반환
     */
    getCurrentPremiumOptions() {
        const options = {};

        // 박 옵션
        if (document.getElementById('foil_enabled')?.checked) {
            options.foil_enabled = 1;
            options.foil_type = document.getElementById('foil_type')?.value;
            options.foil_price = document.getElementById('foil_price')?.value;
        }

        // 넘버링 옵션
        if (document.getElementById('numbering_enabled')?.checked) {
            options.numbering_enabled = 1;
            options.numbering_type = document.getElementById('numbering_type')?.value;
            options.numbering_price = document.getElementById('numbering_price')?.value;
        }

        // 미싱 옵션
        if (document.getElementById('perforation_enabled')?.checked) {
            options.perforation_enabled = 1;
            options.perforation_type = document.getElementById('perforation_type')?.value;
            options.perforation_price = document.getElementById('perforation_price')?.value;
        }

        // 귀돌이 옵션
        if (document.getElementById('rounding_enabled')?.checked) {
            options.rounding_enabled = 1;
            options.rounding_price = document.getElementById('rounding_price')?.value;
        }

        // 오시 옵션
        if (document.getElementById('creasing_enabled')?.checked) {
            options.creasing_enabled = 1;
            options.creasing_type = document.getElementById('creasing_type')?.value;
            options.creasing_price = document.getElementById('creasing_price')?.value;
        }

        options.premium_options_total = document.getElementById('premium_options_total')?.value;

        return options;
    }

    /**
     * 프리미엄 옵션 총액 반환
     */
    getPremiumOptionsTotal() {
        return parseInt(document.getElementById('premium_options_total')?.value) || 0;
    }
}

// 전역 인스턴스
let premiumOptionsManager = null;

/**
 * 프리미엄 옵션 시스템 초기화
 */
function initMerchandiseBondPremiumOptions() {
    if (!premiumOptionsManager) {
        premiumOptionsManager = new MerchandiseBondPremiumOptionsManager();
    }
    return premiumOptionsManager;
}

/**
 * 프리미엄 옵션 총액 가져오기 (외부 호출용)
 */
function getPremiumOptionsTotal() {
    if (premiumOptionsManager) {
        return premiumOptionsManager.getPremiumOptionsTotal();
    }
    const totalField = document.getElementById('premium_options_total');
    return totalField ? parseInt(totalField.value) || 0 : 0;
}

/**
 * 외부에서 수량 업데이트 시 호출
 */
function updatePremiumOptionsQuantity(quantity) {
    if (premiumOptionsManager) {
        premiumOptionsManager.updateQuantity(quantity);
    }
}

/**
 * 프리미엄 옵션 강제 재계산 (외부 호출용)
 */
function recalculatePremiumOptions() {
    if (premiumOptionsManager) {
        premiumOptionsManager.calculateAndUpdatePrice();
    }
}

/**
 * DOM 로드 완료 시 자동 초기화 (메인 시스템 이후에 실행)
 */
document.addEventListener('DOMContentLoaded', function() {
    // 프리미엄 옵션 섹션이 있을 때만 초기화 (약간의 지연으로 메인 시스템 초기화 대기)
    if (document.getElementById('premiumOptionsSection')) {
        setTimeout(() => {
            initMerchandiseBondPremiumOptions();
            console.log('상품권 프리미엄 옵션 시스템 초기화 완료 (메인 시스템 이후)');
        }, 200); // 200ms 지연으로 초기화 순서 보장
    }
});

// 전역 함수로 내보내기 (다른 스크립트에서 사용 가능)
window.MerchandiseBondPremiumOptionsManager = MerchandiseBondPremiumOptionsManager;
window.initMerchandiseBondPremiumOptions = initMerchandiseBondPremiumOptions;
window.getPremiumOptionsTotal = getPremiumOptionsTotal;
window.updatePremiumOptionsQuantity = updatePremiumOptionsQuantity;
window.recalculatePremiumOptions = recalculatePremiumOptions;
