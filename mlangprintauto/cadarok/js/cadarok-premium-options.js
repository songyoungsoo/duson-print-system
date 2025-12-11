/**
 * 카다록 추가 옵션 JavaScript 모듈
 *
 * 목적: 코팅, 접지, 오시 등 추가 옵션의 동적 처리
 * 특징: 실시간 가격 계산 및 UI 업데이트 (명함 스타일 적용)
 *
 * @version 1.0
 * @date 2025-10-09
 * @author SuperClaude - Based on Leaflet Premium Options System
 */

class CadarokPremiumOptionsManager {
    constructor() {
        this.basePrices = {
            coating: {
                'single': 80000,       // 단면유광코팅
                'double': 160000,      // 양면유광코팅
                'single_matte': 90000, // 단면무광코팅
                'double_matte': 180000 // 양면무광코팅
            },
            folding: {
                '2fold': 40000,        // 2단접지
                '3fold': 40000,        // 3단접지
                'accordion': 70000,    // 병풍접지
                'gate': 100000         // 대문접지
            },
            creasing: {
                '1': 30000,            // 1줄 오시
                '2': 30000,            // 2줄 오시
                '3': 45000             // 3줄 오시
            }
        };

        this.currentQuantity = 1000; // 기본 수량 (전단지는 1000매 기준)
        this.init();
    }

    /**
     * 초기화
     */
    init() {
        console.log('🔧 카다록 추가 옵션 시스템 초기화');
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

        // 메인 수량 변경 감지 (전단지 전용)
        const quantityInput = document.getElementById('MY_amount');
        if (quantityInput) {
            quantityInput.removeEventListener('change', this.quantityChangeHandler);
            this.quantityChangeHandler = (e) => {
                console.log('🔧 추가옵션: 수량 변경 감지:', e.target.value);
                this.updateQuantity(e.target.value);
            };
            quantityInput.addEventListener('change', this.quantityChangeHandler);
        }
    }

    /**
     * 체크박스 토글 처리
     */
    handleToggleChange(toggle) {
        const optionType = toggle.id.replace('_enabled', '');
        const detailsDiv = document.getElementById(`${optionType}_options`);

        if (toggle.checked) {
            // 옵션 활성화 - 셀렉트 박스 표시
            if (detailsDiv) {
                detailsDiv.style.display = 'block';
            }
            console.log(`✅ ${optionType} 옵션 활성화`);
        } else {
            // 옵션 비활성화 - 셀렉트 박스 숨김 및 초기화
            if (detailsDiv) {
                detailsDiv.style.display = 'none';
                const select = detailsDiv.querySelector('select');
                if (select) {
                    select.value = '';
                }
            }
            // 가격 숨겨진 필드 초기화
            const priceField = document.getElementById(`${optionType}_price`);
            if (priceField) {
                priceField.value = '0';
            }
            console.log(`❌ ${optionType} 옵션 비활성화`);
        }

        this.calculateAndUpdatePrice();
    }

    /**
     * 수량 업데이트
     */
    updateQuantity(value) {
        const quantity = parseInt(value) || 1000;
        this.currentQuantity = quantity;
        console.log('📊 수량 업데이트:', quantity);
        this.calculateAndUpdatePrice();
    }

    /**
     * 가격 계산 및 업데이트
     */
    calculateAndUpdatePrice() {
        let totalPrice = 0;
        const quantity = this.currentQuantity;
        const multiplier = Math.max(quantity / 1000, 1); // 1000매 기준 배수 계산

        console.log(`💰 가격 계산 시작 (수량: ${quantity}매, 배수: ${multiplier})`);

        // 코팅 옵션
        const coatingEnabled = document.getElementById('coating_enabled')?.checked;
        if (coatingEnabled) {
            const coatingType = document.getElementById('coating_type')?.value;
            if (coatingType && this.basePrices.coating[coatingType]) {
                const price = Math.round(this.basePrices.coating[coatingType] * multiplier);
                totalPrice += price;
                document.getElementById('coating_price').value = price;
                console.log(`🎨 코팅 (${coatingType}): ${price.toLocaleString()}원`);
            } else {
                document.getElementById('coating_price').value = '0';
            }
        } else {
            document.getElementById('coating_price').value = '0';
        }

        // 접지 옵션
        const foldingEnabled = document.getElementById('folding_enabled')?.checked;
        if (foldingEnabled) {
            const foldingType = document.getElementById('folding_type')?.value;
            if (foldingType && this.basePrices.folding[foldingType]) {
                const price = Math.round(this.basePrices.folding[foldingType] * multiplier);
                totalPrice += price;
                document.getElementById('folding_price').value = price;
                console.log(`📄 접지 (${foldingType}): ${price.toLocaleString()}원`);
            } else {
                document.getElementById('folding_price').value = '0';
            }
        } else {
            document.getElementById('folding_price').value = '0';
        }

        // 오시 옵션
        const creasingEnabled = document.getElementById('creasing_enabled')?.checked;
        if (creasingEnabled) {
            const creasingLines = document.getElementById('creasing_lines')?.value;
            if (creasingLines && this.basePrices.creasing[creasingLines]) {
                const price = Math.round(this.basePrices.creasing[creasingLines] * multiplier);
                totalPrice += price;
                document.getElementById('creasing_price').value = price;
                console.log(`📏 오시 (${creasingLines}줄): ${price.toLocaleString()}원`);
            } else {
                document.getElementById('creasing_price').value = '0';
            }
        } else {
            document.getElementById('creasing_price').value = '0';
        }

        // 총액 업데이트
        document.getElementById('additional_options_total').value = totalPrice;

        console.log(`🎯 추가 옵션 총액: ${totalPrice.toLocaleString()}원`);

        // UI 업데이트
        this.updatePriceDisplay(totalPrice);

        // 메인 가격 계산 함수 호출 (전단지 전용)
        if (typeof window.calculatePrice === 'function') {
            window.calculatePrice(true); // isAuto = true로 alert 방지
        }
    }

    /**
     * 가격 표시 업데이트
     */
    updatePriceDisplay(total = 0) {
        const priceElement = document.getElementById('premiumPriceTotal');
        if (priceElement) {
            if (total > 0) {
                priceElement.textContent = `(+${total.toLocaleString()}원)`;
                priceElement.style.color = '#1E4E79'; // Deep Navy
            } else {
                priceElement.textContent = '(+0원)';
                priceElement.style.color = '#999'; // 회색
            }
        }
    }

    /**
     * additional_options JSON 생성 (컬럼 기반 형식)
     */
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

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', () => {
    // premiumOptionsSection이 존재하면 초기화
    if (document.getElementById('premiumOptionsSection')) {
        window.cadarokPremiumOptions = new CadarokPremiumOptionsManager();
        console.log('✅ 카다록 추가 옵션 시스템 준비 완료');
    }
});
