/**
 * 추가 옵션 시스템 JavaScript 모듈
 * 
 * 목적: 체크박스 기반 추가 옵션의 동적 처리
 * 특징: 실시간 가격 계산 및 UI 업데이트
 * 
 * @version 1.0
 * @date 2025-01-08
 * @author SuperClaude Architecture System
 */

class AdditionalOptionsManager {
    constructor() {
        this.basePrices = {
            coating: {
                single: 80000,
                double: 160000,
                single_matte: 90000,
                double_matte: 180000
            },
            folding: {
                '2fold': 40000,
                '3fold': 40000,
                'accordion': 70000,
                'gate': 100000
            },
            creasing: {
                1: 30000,
                2: 30000,
                3: 45000
            }
        };
        
        this.currentQuantity = 1000; // 기본 수량 (1연)
        this.init();
    }
    
    /**
     * 초기화
     */
    init() {
        console.log('🔧 추가 옵션 시스템 초기화');
        this.setupEventListeners();
        this.updatePriceDisplay();
    }
    
    /**
     * 이벤트 리스너 설정
     */
    setupEventListeners() {
        // 체크박스 토글 이벤트
        const toggles = document.querySelectorAll('.option-toggle');
        console.log('🔍 체크박스 개수:', toggles.length);
        if (toggles.length === 0) {
            console.error('❌ 추가옵션 체크박스를 찾을 수 없습니다! (.option-toggle)');
        }
        toggles.forEach((toggle, index) => {
            console.log(`  [${index}] 체크박스 ID:`, toggle.id, 'name:', toggle.name);
            toggle.addEventListener('change', (e) => {
                console.log('🎯 체크박스 변경 이벤트 발생:', e.target.id, 'checked:', e.target.checked);
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
        const quantitySelect = document.getElementById('MY_amount');
        if (quantitySelect) {
            // 중복 이벤트 방지를 위한 네임스페이스 사용
            quantitySelect.removeEventListener('change', this.quantityChangeHandler);
            this.quantityChangeHandler = (e) => {
                console.log('🔧 추가옵션: 수량 변경 감지:', e.target.value);
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
            detailsDiv.style.display = 'block';
            detailsDiv.classList.add('show');
            detailsDiv.classList.remove('hide');
            
            console.log(`✅ ${optionType} 옵션 활성화`);
        } else {
            detailsDiv.style.display = 'none';
            detailsDiv.classList.add('hide');
            detailsDiv.classList.remove('show');
            
            // 숨겨진 필드 초기화
            this.resetOptionFields(optionType);
            console.log(`❌ ${optionType} 옵션 비활성화`);
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
        this.currentQuantity = parseInt(quantityValue) || 1000;
        console.log('📊 수량 업데이트:', this.currentQuantity);
        this.calculateAndUpdatePrice();
    }
    
    /**
     * 수량 기준 배수 계산
     */
    calculateQuantityMultiplier(quantity) {
        const yeon = quantity / 1000; // 1000매 = 1연 기준
        return yeon <= 0.5 ? 1.0 : yeon; // 0.5연 이하는 1연 가격
    }
    
    /**
     * 가격 계산 및 UI 업데이트
     */
    calculateAndUpdatePrice() {
        const multiplier = this.calculateQuantityMultiplier(this.currentQuantity);
        let totalOptionsPrice = 0;
        const optionDetails = [];
        
        // 코팅 옵션 계산
        if (document.getElementById('coating_enabled')?.checked) {
            const coatingType = document.getElementById('coating_type')?.value;
            if (coatingType && this.basePrices.coating[coatingType]) {
                const price = Math.round(this.basePrices.coating[coatingType] * multiplier);
                totalOptionsPrice += price;

                document.getElementById('coating_price').value = price;
                const coatingPriceDisplay = document.getElementById('coating_price_display');
                if (coatingPriceDisplay) {
                    coatingPriceDisplay.textContent = price.toLocaleString();
                    coatingPriceDisplay.closest('.option-price').style.display = 'block';
                }
                optionDetails.push({
                    name: this.getOptionName('coating', coatingType),
                    price: price
                });

                console.log('🎨 코팅 가격:', price);
            }
        } else {
            document.getElementById('coating_price').value = '0';
        }
        
        // 접지 옵션 계산
        if (document.getElementById('folding_enabled')?.checked) {
            const foldingType = document.getElementById('folding_type')?.value;
            if (foldingType && this.basePrices.folding[foldingType]) {
                const price = Math.round(this.basePrices.folding[foldingType] * multiplier);
                totalOptionsPrice += price;

                document.getElementById('folding_price').value = price;
                const foldingPriceDisplay = document.getElementById('folding_price_display');
                if (foldingPriceDisplay) {
                    foldingPriceDisplay.textContent = price.toLocaleString();
                    foldingPriceDisplay.closest('.option-price').style.display = 'block';
                }
                optionDetails.push({
                    name: this.getOptionName('folding', foldingType),
                    price: price
                });

                console.log('📄 접지 가격:', price);
            }
        } else {
            document.getElementById('folding_price').value = '0';
        }
        
        // 오시 옵션 계산
        if (document.getElementById('creasing_enabled')?.checked) {
            const creasingLines = document.getElementById('creasing_lines')?.value;
            if (creasingLines && this.basePrices.creasing[parseInt(creasingLines)]) {
                const price = Math.round(this.basePrices.creasing[parseInt(creasingLines)] * multiplier);
                totalOptionsPrice += price;

                document.getElementById('creasing_price').value = price;
                const creasingPriceDisplay = document.getElementById('creasing_price_display');
                if (creasingPriceDisplay) {
                    creasingPriceDisplay.textContent = price.toLocaleString();
                    creasingPriceDisplay.closest('.option-price').style.display = 'block';
                }
                optionDetails.push({
                    name: this.getOptionName('creasing', creasingLines),
                    price: price
                });

                console.log('📏 오시 가격:', price);
            }
        } else {
            document.getElementById('creasing_price').value = '0';
        }
        
        // 총 옵션 가격 업데이트
        document.getElementById('additional_options_total').value = totalOptionsPrice;

        // 총 옵션 가격 표시 업데이트
        const totalText = document.getElementById('additional_options_total_text');
        const totalDisplay = document.getElementById('options_total_display');
        if (totalText && totalDisplay) {
            if (totalOptionsPrice > 0) {
                totalText.textContent = totalOptionsPrice.toLocaleString();
                totalDisplay.style.display = 'block';
            } else {
                totalDisplay.style.display = 'none';
            }
        }

        // UI 업데이트
        this.updatePriceDisplay(totalOptionsPrice, optionDetails);
        
        // 메인 가격 계산 함수 호출 (전단지 전용) - 자동 모드로 호출
        if (typeof calculatePrice === 'function') {
            calculatePrice(true); // isAuto = true로 alert 방지
        }
        
        console.log('💰 총 추가 옵션 가격:', totalOptionsPrice, '배수:', multiplier);
    }
    
    /**
     * 옵션 이름 가져오기
     */
    getOptionName(category, type) {
        const names = {
            coating: {
                single: '단면유광코팅',
                double: '양면유광코팅',
                single_matte: '단면무광코팅',
                double_matte: '양면무광코팅'
            },
            folding: {
                '2fold': '2단접지',
                '3fold': '3단접지',
                'accordion': '병풍접지',
                'gate': '대문접지'
            },
            creasing: {
                1: '1줄 오시',
                2: '2줄 오시',
                3: '3줄 오시'
            }
        };
        
        return names[category] && names[category][type] ? names[category][type] : type;
    }
    
    /**
     * 가격 표시 UI 업데이트
     */
    updatePriceDisplay(totalOptionsPrice = 0, optionDetails = []) {
        // 옵션 총액 표시 업데이트
        const optionPriceTotal = document.getElementById('optionPriceTotal');
        if (optionPriceTotal) {
            if (totalOptionsPrice > 0) {
                optionPriceTotal.textContent = `(+${totalOptionsPrice.toLocaleString()}원)`;
                optionPriceTotal.style.color = '#38a169';
            } else {
                optionPriceTotal.textContent = '(+0원)';
                optionPriceTotal.style.color = '#718096';
            }
        }
        
        // 메인 가격 표시에 옵션 정보 추가
        const priceDisplay = document.getElementById('priceDisplay');
        if (priceDisplay && totalOptionsPrice > 0) {
            priceDisplay.classList.add('has-options');
            
            // 기존 추가 옵션 행 제거
            const existingOptions = priceDisplay.querySelectorAll('.additional-options-row');
            existingOptions.forEach(row => row.remove());
            
            // 새로운 추가 옵션 행 추가
            if (optionDetails.length > 0) {
                const priceBreakdown = priceDisplay.querySelector('.price-breakdown');
                if (priceBreakdown) {
                    optionDetails.forEach(option => {
                        const optionRow = document.createElement('div');
                        optionRow.className = 'additional-options-row price-item';
                        optionRow.innerHTML = `
                            <span class="price-item-label">${option.name}:</span>
                            <span class="price-item-value">${option.price.toLocaleString()}원</span>
                        `;
                        priceBreakdown.appendChild(optionRow);
                    });
                }
            }
        } else if (priceDisplay) {
            priceDisplay.classList.remove('has-options');
            
            // 추가 옵션 행 제거
            const existingOptions = priceDisplay.querySelectorAll('.additional-options-row');
            existingOptions.forEach(row => row.remove());
        }
    }
    
    /**
     * 현재 선택된 옵션 정보 반환
     */
    getCurrentOptions() {
        const options = {};
        
        // 코팅 옵션
        if (document.getElementById('coating_enabled')?.checked) {
            options.coating_enabled = 1;
            options.coating_type = document.getElementById('coating_type')?.value;
            options.coating_price = document.getElementById('coating_price')?.value;
        }
        
        // 접지 옵션
        if (document.getElementById('folding_enabled')?.checked) {
            options.folding_enabled = 1;
            options.folding_type = document.getElementById('folding_type')?.value;
            options.folding_price = document.getElementById('folding_price')?.value;
        }
        
        // 오시 옵션
        if (document.getElementById('creasing_enabled')?.checked) {
            options.creasing_enabled = 1;
            options.creasing_lines = document.getElementById('creasing_lines')?.value;
            options.creasing_price = document.getElementById('creasing_price')?.value;
        }
        
        options.additional_options_total = document.getElementById('additional_options_total')?.value;
        
        return options;
    }
}

// 전역 인스턴스
let additionalOptionsManager = null;

/**
 * 추가 옵션 시스템 초기화
 */
function initAdditionalOptions() {
    if (!additionalOptionsManager) {
        additionalOptionsManager = new AdditionalOptionsManager();
    }
    return additionalOptionsManager;
}

/**
 * 추가 옵션 총액 가져오기
 */
function getAdditionalOptionsTotal() {
    const totalField = document.getElementById('additional_options_total');
    return totalField ? parseInt(totalField.value) || 0 : 0;
}

/**
 * 외부에서 수량 업데이트 시 호출
 */
function updateAdditionalOptionsQuantity(quantity) {
    if (additionalOptionsManager) {
        additionalOptionsManager.updateQuantity(quantity);
    }
}

/**
 * DOM 로드 완료 시 자동 초기화 (메인 시스템 이후에 실행)
 */
document.addEventListener('DOMContentLoaded', function() {
    // 추가 옵션 섹션이 있을 때만 초기화 (약간의 지연으로 메인 시스템 초기화 대기)
    const optionsSection = document.getElementById('additionalOptionsSection');
    console.log('🔍 DOMContentLoaded - additionalOptionsSection 존재:', !!optionsSection);
    if (optionsSection) {
        setTimeout(() => {
            console.log('⏰ 100ms 지연 후 초기화 시작...');
            initAdditionalOptions();
            console.log('✅ 추가 옵션 시스템 초기화 완료 (메인 시스템 이후)');
        }, 100); // 100ms 지연으로 초기화 순서 보장
    } else {
        console.error('❌ additionalOptionsSection을 찾을 수 없습니다!');
    }
});

// 전역 함수로 내보내기 (다른 스크립트에서 사용 가능)
window.AdditionalOptionsManager = AdditionalOptionsManager;
window.initAdditionalOptions = initAdditionalOptions;
window.getAdditionalOptionsTotal = getAdditionalOptionsTotal;
window.updateAdditionalOptionsQuantity = updateAdditionalOptionsQuantity;