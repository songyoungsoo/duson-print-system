/**
 * 🧮 UniversalPriceHandler - 통합 가격 처리 시스템
 * 
 * 모든 제품의 가격 계산 응답을 처리하는 통합 JavaScript 클래스
 * 분석 결과, 모든 제품의 가격 응답 구조가 이미 완벽하게 통일되어 있음을 확인
 * 
 * 작성일: 2025년 8월 9일
 * 상태: 스마트 컴포넌트 시스템 구현 - 3단계
 */

class UniversalPriceHandler {
    
    /**
     * 생성자 - 전역 가격 핸들러 초기화
     */
    constructor() {
        this.debug = false; // 디버깅 모드
        this.priceEndpoints = this.initPriceEndpoints();
        this.init();
    }

    /**
     * 초기화 - 이벤트 리스너 등록
     */
    init() {
        console.log('🧮 UniversalPriceHandler 초기화 완료');
        
        // 스마트 필드 변경 시 자동 가격 계산
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('smart-field')) {
                this.handleFieldChange(e.target);
            }
        });

        // 수량 입력 필드 실시간 계산
        document.addEventListener('input', (e) => {
            if (e.target.name === 'MY_amount' && e.target.classList.contains('smart-field')) {
                this.debounce(() => {
                    this.calculatePrice();
                }, 500)();
            }
        });
    }

    /**
     * 제품별 가격 계산 엔드포인트 매핑
     */
    initPriceEndpoints() {
        return {
            'leaflet': '/mlangprintauto/inserted/calculate_price.php',
            'poster': '/mlangprintauto/LittlePrint/calculate_price.php', 
            'namecard': '/mlangprintauto/NameCard/calculate_price.php',
            'coupon': '/mlangprintauto/MerchandiseBond/calculate_price.php',
            'envelope': '/mlangprintauto/envelope/calculate_price.php',
            'form': '/mlangprintauto/NcrFlambeau/calculate_price.php',
            'magnetic_sticker': '/mlangprintauto/msticker/calculate_price.php',
            'catalog': '/mlangprintauto/cadarok/calculate_price.php',
            'sticker': '/mlangprintauto/shop/calculate_price.php' // 일반 스티커 (공식 기반)
        };
    }

    /**
     * 필드 변경 시 처리
     */
    handleFieldChange(fieldElement) {
        const productType = this.detectProductType(fieldElement);
        
        if (this.debug) {
            console.log(`🔄 필드 변경 감지: ${fieldElement.name} = ${fieldElement.value} (제품: ${productType})`);
        }

        // 연관 필드 업데이트 (AJAX로 옵션 목록 갱신)
        this.updateDependentFields(fieldElement, productType);
        
        // 가격 계산 실행
        this.calculatePrice(productType);
    }

    /**
     * 현재 페이지에서 제품 타입 감지
     */
    detectProductType(element) {
        // 1. 엘리먼트의 data-product 속성 확인
        if (element && element.dataset.product) {
            return element.dataset.product;
        }

        // 2. 부모 컨테이너의 data-product 확인
        const container = element ? element.closest('[data-product]') : null;
        if (container && container.dataset.product) {
            return container.dataset.product;
        }

        // 3. URL 기반 감지
        const path = window.location.pathname;
        if (path.includes('/inserted/')) return 'leaflet';
        if (path.includes('/LittlePrint/')) return 'poster';
        if (path.includes('/NameCard/')) return 'namecard';
        if (path.includes('/MerchandiseBond/')) return 'coupon';
        if (path.includes('/envelope/')) return 'envelope';
        if (path.includes('/NcrFlambeau/')) return 'form';
        if (path.includes('/msticker/')) return 'magnetic_sticker';
        if (path.includes('/cadarok/')) return 'catalog';
        if (path.includes('/shop/') && path.includes('view_modern')) return 'sticker';

        // 4. 폼 이름 기반 감지
        const choiceForm = document.forms['choiceForm'];
        if (choiceForm && choiceForm.dataset.product) {
            return choiceForm.dataset.product;
        }

        console.warn('⚠️ 제품 타입을 감지할 수 없습니다. 기본값(leaflet) 사용');
        return 'leaflet';
    }

    /**
     * 연관 필드 업데이트 (예: MY_type 변경시 PN_type 옵션 갱신)
     */
    async updateDependentFields(changedField, productType) {
        // SmartFieldComponent PHP 클래스의 AJAX 엔드포인트 호출
        const updateUrl = '/includes/ajax/update_dependent_fields.php';
        
        try {
            const response = await fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_type: productType,
                    field_name: changedField.name,
                    field_value: changedField.value
                })
            });

            const result = await response.json();
            
            if (result.success && result.updates) {
                Object.keys(result.updates).forEach(fieldName => {
                    this.updateSelectOptions(fieldName, result.updates[fieldName]);
                });
            }
        } catch (error) {
            if (this.debug) {
                console.warn('연관 필드 업데이트 실패:', error);
            }
        }
    }

    /**
     * 셀렉트 박스 옵션 업데이트
     */
    updateSelectOptions(fieldName, options) {
        const selectElement = document.querySelector(`select[name="${fieldName}"]`);
        if (!selectElement) return;

        // 기존 옵션 제거 (첫 번째 기본 옵션 제외)
        const firstOption = selectElement.options[0];
        selectElement.innerHTML = '';
        selectElement.appendChild(firstOption);

        // 새 옵션 추가
        options.forEach(option => {
            const optElement = document.createElement('option');
            optElement.value = option.value;
            optElement.textContent = option.text;
            selectElement.appendChild(optElement);
        });
    }

    /**
     * 메인 가격 계산 함수
     */
    async calculatePrice(productType = null) {
        if (!productType) {
            productType = this.detectProductType();
        }

        const endpoint = this.priceEndpoints[productType];
        if (!endpoint) {
            console.error(`❌ 알 수 없는 제품 타입: ${productType}`);
            return;
        }

        // 폼 데이터 수집
        const formData = this.collectFormData();
        if (!this.validateFormData(formData, productType)) {
            if (this.debug) {
                console.warn('⚠️ 폼 데이터가 불완전합니다. 가격 계산 건너뜀');
            }
            return;
        }

        try {
            if (this.debug) {
                console.log(`🧮 가격 계산 시작: ${productType}`, formData);
            }

            // 로딩 표시
            this.showPriceLoading();

            let response;
            
            // 스티커 제품은 다른 방식 (공식 계산)
            if (productType === 'sticker') {
                response = await this.calculateStickerPrice(formData);
            } else {
                response = await this.callPriceAPI(endpoint, formData);
            }

            // 응답 처리
            if (response) {
                this.updatePriceDisplay(response);
                
                if (this.debug) {
                    console.log('✅ 가격 계산 완료:', response);
                }
            }

        } catch (error) {
            console.error('❌ 가격 계산 오류:', error);
            this.showPriceError();
        } finally {
            this.hidePriceLoading();
        }
    }

    /**
     * 폼 데이터 수집
     */
    collectFormData() {
        const form = document.forms['choiceForm'] || document.querySelector('form');
        if (!form) return {};

        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        return data;
    }

    /**
     * 폼 데이터 유효성 검사
     */
    validateFormData(formData, productType) {
        const requiredFields = {
            'leaflet': ['MY_type', 'MY_Fsd', 'PN_type', 'POtype', 'MY_amount'],
            'poster': ['MY_type', 'MY_Fsd', 'PN_type', 'POtype', 'MY_amount'],
            'namecard': ['MY_type', 'PN_type', 'POtype', 'MY_amount'],
            'coupon': ['MY_type', 'PN_type', 'MY_amount'],
            'sticker': ['jong', 'garo', 'sero', 'mesu'] // 스티커는 다른 필드 구조
        };

        const required = requiredFields[productType] || ['MY_type', 'MY_amount'];
        
        return required.every(field => {
            const hasValue = formData[field] && formData[field].trim() !== '';
            if (!hasValue && this.debug) {
                console.warn(`⚠️ 필수 필드 누락: ${field}`);
            }
            return hasValue;
        });
    }

    /**
     * 일반 제품 가격 API 호출 (테이블 기반 계산)
     */
    async callPriceAPI(endpoint, formData) {
        const queryString = new URLSearchParams(formData).toString();
        const url = `${endpoint}?${queryString}`;

        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const text = await response.text();
        
        // 응답이 JavaScript 코드 형태인 경우 파싱
        if (text.includes('parent.document.forms')) {
            return this.parseJavaScriptResponse(text);
        }
        
        // JSON 응답인 경우
        try {
            return JSON.parse(text);
        } catch {
            throw new Error('응답 형식을 인식할 수 없습니다');
        }
    }

    /**
     * 스티커 전용 가격 계산 (공식 기반)
     */
    async calculateStickerPrice(formData) {
        const { jong, garo, sero, mesu, uhyung = 'none', domusong = 'square' } = formData;

        // 스티커 계산은 기존 view_modern.php의 JavaScript 공식을 사용
        // 이 부분은 기존 스티커 시스템의 calculate_price() 함수를 호출
        if (typeof window.calculate_price === 'function') {
            // 기존 스티커 계산 함수 호출
            return new Promise((resolve) => {
                // 기존 함수가 폼에 결과를 설정하므로, 설정 후 값을 읽어서 반환
                window.calculate_price();
                
                setTimeout(() => {
                    const form = document.forms['choiceForm'];
                    if (form) {
                        resolve({
                            Price: form.Price ? form.Price.value : '0',
                            DS_Price: form.DS_Price ? form.DS_Price.value : '0',
                            Order_Price: form.Order_Price ? form.Order_Price.value : '0',
                            PriceForm: form.PriceForm ? parseInt(form.PriceForm.value) : 0,
                            DS_PriceForm: form.DS_PriceForm ? parseInt(form.DS_PriceForm.value) : 0,
                            Order_PriceForm: form.Order_PriceForm ? parseInt(form.Order_PriceForm.value) : 0,
                            VAT_PriceForm: form.VAT_PriceForm ? parseInt(form.VAT_PriceForm.value) : 0,
                            Total_PriceForm: form.Total_PriceForm ? parseInt(form.Total_PriceForm.value) : 0
                        });
                    } else {
                        resolve(null);
                    }
                }, 100);
            });
        }
        
        // 기존 함수가 없으면 API 호출
        return this.callPriceAPI('/mlangprintauto/shop/calculate_price.php', formData);
    }

    /**
     * JavaScript 응답 파싱 (parent.document.forms 형태)
     */
    parseJavaScriptResponse(jsCode) {
        const patterns = {
            Price: /Price\.value\s*=\s*"([^"]+)"/,
            DS_Price: /DS_Price\.value\s*=\s*"([^"]+)"/,
            Order_Price: /Order_Price\.value\s*=\s*"([^"]+)"/,
            PriceForm: /PriceForm\.value\s*=\s*([^;]+)/,
            DS_PriceForm: /DS_PriceForm\.value\s*=\s*([^;]+)/,
            Order_PriceForm: /Order_PriceForm\.value\s*=\s*([^;]+)/,
            VAT_PriceForm: /VAT_PriceForm\.value\s*=\s*([^;]+)/,
            Total_PriceForm: /Total_PriceForm\.value\s*=\s*([^;]+)/,
            StyleForm: /StyleForm\.value\s*=\s*"([^"]+)"/,
            SectionForm: /SectionForm\.value\s*=\s*"([^"]+)"/,
            QuantityForm: /QuantityForm\.value\s*=\s*"([^"]+)"/,
            DesignForm: /DesignForm\.value\s*=\s*"([^"]+)"/
        };

        const result = {};
        
        Object.keys(patterns).forEach(key => {
            const match = jsCode.match(patterns[key]);
            if (match) {
                result[key] = match[1].replace(/"/g, '');
            }
        });

        return result;
    }

    /**
     * 📋 통합 가격 표시 업데이트 (모든 제품 공통)
     * 
     * 분석에서 확인한 바와 같이 모든 제품의 응답 구조가 동일하므로
     * 이 함수 하나로 모든 제품의 가격 표시를 처리할 수 있습니다.
     */
    updatePriceDisplay(priceData) {
        const form = parent.document.forms["choiceForm"] || document.forms["choiceForm"];
        
        if (!form) {
            console.error('❌ choiceForm을 찾을 수 없습니다');
            return;
        }

        // ✅ 표시용 가격 (콤마 포함 문자열)
        if (form.Price && priceData.Price) {
            form.Price.value = priceData.Price;
        }
        if (form.DS_Price && priceData.DS_Price) {
            form.DS_Price.value = priceData.DS_Price;
        }
        if (form.Order_Price && priceData.Order_Price) {
            form.Order_Price.value = priceData.Order_Price;
        }

        // ✅ 계산용 가격 (숫자)
        if (form.PriceForm && priceData.PriceForm !== undefined) {
            form.PriceForm.value = priceData.PriceForm;
        }
        if (form.DS_PriceForm && priceData.DS_PriceForm !== undefined) {
            form.DS_PriceForm.value = priceData.DS_PriceForm;
        }
        if (form.Order_PriceForm && priceData.Order_PriceForm !== undefined) {
            form.Order_PriceForm.value = priceData.Order_PriceForm;
        }
        if (form.VAT_PriceForm && priceData.VAT_PriceForm !== undefined) {
            form.VAT_PriceForm.value = priceData.VAT_PriceForm;
        }
        if (form.Total_PriceForm && priceData.Total_PriceForm !== undefined) {
            form.Total_PriceForm.value = priceData.Total_PriceForm;
        }

        // ✅ 선택 옵션 정보
        if (form.StyleForm && priceData.StyleForm) {
            form.StyleForm.value = priceData.StyleForm;
        }
        if (form.SectionForm && priceData.SectionForm) {
            form.SectionForm.value = priceData.SectionForm;
        }
        if (form.QuantityForm && priceData.QuantityForm) {
            form.QuantityForm.value = priceData.QuantityForm;
        }
        if (form.DesignForm && priceData.DesignForm) {
            form.DesignForm.value = priceData.DesignForm;
        }

        // 화면 표시 업데이트
        this.updateScreenDisplay(priceData);
        
        // 커스텀 이벤트 발생 (다른 컴포넌트에서 감지 가능)
        this.dispatchPriceUpdateEvent(priceData);
    }

    /**
     * 화면의 가격 표시 요소 업데이트
     */
    updateScreenDisplay(priceData) {
        // 일반적인 가격 표시 요소들
        const displayElements = {
            '.price-display': priceData.Order_Price || priceData.Price,
            '.vat-display': priceData.VAT_PriceForm ? this.formatPrice(priceData.VAT_PriceForm) : '',
            '.total-display': priceData.Total_PriceForm ? this.formatPrice(priceData.Total_PriceForm) : '',
            '.print-cost': priceData.Price,
            '.design-cost': priceData.DS_Price
        };

        Object.keys(displayElements).forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                if (displayElements[selector]) {
                    el.textContent = displayElements[selector];
                }
            });
        });
    }

    /**
     * 가격 업데이트 커스텀 이벤트 발생
     */
    dispatchPriceUpdateEvent(priceData) {
        const event = new CustomEvent('priceUpdated', {
            detail: {
                priceData,
                timestamp: new Date(),
                productType: this.detectProductType()
            }
        });
        document.dispatchEvent(event);
    }

    /**
     * 로딩 표시
     */
    showPriceLoading() {
        const loadingElements = document.querySelectorAll('.price-loading');
        loadingElements.forEach(el => el.style.display = 'inline');

        // 가격 표시 요소들에 로딩 클래스 추가
        const priceElements = document.querySelectorAll('.price-display, .total-display');
        priceElements.forEach(el => el.classList.add('loading'));
    }

    /**
     * 로딩 숨기기
     */
    hidePriceLoading() {
        const loadingElements = document.querySelectorAll('.price-loading');
        loadingElements.forEach(el => el.style.display = 'none');

        const priceElements = document.querySelectorAll('.price-display, .total-display');
        priceElements.forEach(el => el.classList.remove('loading'));
    }

    /**
     * 가격 오류 표시
     */
    showPriceError() {
        const errorElements = document.querySelectorAll('.price-error');
        errorElements.forEach(el => {
            el.style.display = 'block';
            el.textContent = '가격 계산 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
        });
    }

    /**
     * 숫자를 가격 형태로 포맷팅
     */
    formatPrice(price) {
        if (typeof price === 'string') {
            price = parseInt(price.replace(/,/g, ''));
        }
        return price.toLocaleString('ko-KR') + '원';
    }

    /**
     * 디바운스 함수 (연속 호출 방지)
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * 디버그 모드 토글
     */
    toggleDebug() {
        this.debug = !this.debug;
        console.log(`🔧 디버그 모드: ${this.debug ? 'ON' : 'OFF'}`);
    }
}

// 전역 인스턴스 생성 및 노출
window.universalPriceHandler = new UniversalPriceHandler();

// 하위 호환성을 위한 전역 함수들
window.calculate_price = function() {
    window.universalPriceHandler.calculatePrice();
};

window.smart_field_change = function(element) {
    window.universalPriceHandler.handleFieldChange(element);
};

console.log('🧮 UniversalPriceHandler 로드 완료');