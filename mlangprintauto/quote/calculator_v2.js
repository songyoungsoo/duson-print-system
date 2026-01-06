/**
 * 견적서 통합 계산기 JavaScript v2.0
 *
 * 11개 품목의 계산기를 단일 페이지에서 처리
 * - 동적 폼 로드
 * - 실시간 가격 계산
 * - 부모 창 데이터 전달
 *
 * @author Claude Code
 * @version 2.0
 * @date 2026-01-06
 */

class UnifiedCalculator {
    constructor() {
        this.currentProduct = null;
        this.currentPrice = null;
        this.formData = {};
        this.init();
    }

    /**
     * 초기화
     */
    init() {
        console.log('✅ UnifiedCalculator initialized');

        // 제품 선택 이벤트
        const productSelector = document.getElementById('productSelector');
        if (productSelector) {
            productSelector.addEventListener('change', (e) => {
                this.onProductChange(e.target.value);
            });
        }

        // 버튼 이벤트
        const btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', () => this.resetCalculator());
        }

        const btnAddToQuote = document.getElementById('btnAddToQuote');
        if (btnAddToQuote) {
            btnAddToQuote.addEventListener('click', () => this.addToQuotation());
        }
    }

    /**
     * HTML 이스케이프 (XSS 방지)
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * 제품 선택 변경
     */
    async onProductChange(productType) {
        if (!productType) {
            this.showEmptyState();
            return;
        }

        console.log('📦 Product selected:', productType);
        this.currentProduct = productType;

        try {
            await this.loadProductForm(productType);
        } catch (error) {
            console.error('❌ Failed to load product form:', error);
            this.showError('폼을 불러오는데 실패했습니다: ' + error.message);
        }
    }

    /**
     * 제품별 폼 로드 (AJAX)
     */
    async loadProductForm(productType) {
        const formContainer = document.getElementById('calculatorForm');
        formContainer.textContent = '폼을 불러오는 중...';
        formContainer.className = 'calc-form loading';

        try {
            const response = await fetch(`api/get_form.php?product=${encodeURIComponent(productType)}`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const html = await response.text();
            // API에서 받은 HTML은 서버측에서 검증됨 (내부 API)
            formContainer.innerHTML = html;
            formContainer.className = 'calc-form';

            // 폼 로드 후 이벤트 연결
            this.attachFormListeners();

            // Footer 표시
            document.getElementById('calcFooter').style.display = 'block';

            console.log('✅ Form loaded for:', productType);
        } catch (error) {
            console.error('❌ loadProductForm error:', error);
            this.showError('폼을 불러오지 못했습니다: ' + error.message);
            throw error;
        }
    }

    /**
     * 폼 입력 이벤트 연결
     */
    attachFormListeners() {
        const form = document.querySelector('#calculatorForm form');
        if (!form) {
            console.warn('⚠️ No form found in calculatorForm');
            return;
        }

        // 모든 input/select 요소에 change 이벤트 연결
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                this.onFormChange();
            });
        });

        console.log('✅ Form listeners attached:', inputs.length, 'inputs');
    }

    /**
     * 폼 입력 변경 시 가격 계산
     */
    async onFormChange() {
        console.log('🔄 Form changed, calculating price...');

        // 필수 필드 검증
        if (!this.validateForm()) {
            this.disableAddButton();
            return;
        }

        try {
            await this.calculatePrice();
            this.enableAddButton();
        } catch (error) {
            console.error('❌ Price calculation failed:', error);
            this.showError('가격 계산 실패: ' + error.message);
            this.disableAddButton();
        }
    }

    /**
     * 폼 유효성 검사
     */
    validateForm() {
        const form = document.querySelector('#calculatorForm form');
        if (!form) return false;

        const formData = new FormData(form);
        let hasRequiredFields = true;

        // 제품별 필수 필드 확인
        switch (this.currentProduct) {
            case 'sticker':
            case 'msticker':
                hasRequiredFields = formData.get('jong') &&
                                   formData.get('domusong') &&
                                   formData.get('garo') &&
                                   formData.get('sero') &&
                                   formData.get('mesu');
                break;

            case 'namecard':
                hasRequiredFields = formData.get('MY_type') &&
                                   formData.get('Section') &&
                                   formData.get('POtype') &&
                                   formData.get('MY_amount');
                break;

            case 'inserted':
                hasRequiredFields = formData.get('MY_type') &&
                                   formData.get('PN_type') &&
                                   formData.get('MY_Fsd') &&
                                   formData.get('MY_amount') &&
                                   formData.get('POtype');
                break;

            default:
                // 기타 제품: MY_type과 MY_amount만 체크
                hasRequiredFields = formData.get('MY_type') &&
                                   formData.get('MY_amount');
                break;
        }

        return hasRequiredFields;
    }

    /**
     * 가격 계산 (AJAX)
     */
    async calculatePrice() {
        const form = document.querySelector('#calculatorForm form');
        if (!form) {
            throw new Error('Form not found');
        }

        const formData = new FormData(form);
        formData.append('product_type', this.currentProduct);

        try {
            const response = await fetch('api/calculate_price.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || '가격 계산 실패');
            }

            // 가격 표시 업데이트
            this.updatePriceDisplay(result.data);

            // 폼 데이터 저장 (나중에 quotation_temp에 저장할 데이터)
            this.formData = result.data.form_data || {};

            console.log('✅ Price calculated:', result.data);
        } catch (error) {
            console.error('❌ calculatePrice error:', error);
            throw error;
        }
    }

    /**
     * 가격 표시 업데이트
     */
    updatePriceDisplay(priceData) {
        const supplyPrice = priceData.supply_price || 0;
        const totalPrice = priceData.total_price || 0;

        document.getElementById('supplyPrice').textContent =
            supplyPrice.toLocaleString() + '원';
        document.getElementById('totalPrice').textContent =
            totalPrice.toLocaleString() + '원';

        this.currentPrice = {
            supply: supplyPrice,
            total: totalPrice
        };
    }

    /**
     * 견적서에 추가
     */
    async addToQuotation() {
        console.log('📋 Adding to quotation...');

        if (!this.formData || !this.currentPrice) {
            alert('가격을 먼저 계산해주세요.');
            return;
        }

        try {
            // quotation_temp에 저장
            const response = await fetch('add_to_quotation_temp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(this.formData)
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || '저장 실패');
            }

            console.log('✅ Saved to quotation_temp:', result.data);

            // 부모 창에 알림 (create.php 새로고침)
            if (window.isInModal && window.parent) {
                window.parent.postMessage({
                    type: 'QUOTATION_ITEM_ADDED',
                    data: result.data
                }, '*');

                // 모달 닫기 (부모가 처리)
                window.parent.postMessage({
                    type: 'CLOSE_CALCULATOR'
                }, '*');
            } else {
                // 모달이 아닌 경우 페이지 새로고침
                alert('견적서에 추가되었습니다!');
                this.resetCalculator();
            }
        } catch (error) {
            console.error('❌ addToQuotation error:', error);
            alert('견적서에 추가하지 못했습니다: ' + error.message);
        }
    }

    /**
     * 계산기 초기화
     */
    resetCalculator() {
        document.getElementById('productSelector').value = '';
        this.showEmptyState();
        this.currentProduct = null;
        this.currentPrice = null;
        this.formData = {};
    }

    /**
     * 빈 상태 표시 (안전한 정적 HTML)
     */
    showEmptyState() {
        const formContainer = document.getElementById('calculatorForm');
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'empty-state';
        emptyDiv.innerHTML = `
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <p>위에서 제품을 선택하면 계산기가 표시됩니다</p>
        `;
        formContainer.innerHTML = '';
        formContainer.appendChild(emptyDiv);
        document.getElementById('calcFooter').style.display = 'none';
    }

    /**
     * 에러 표시 (XSS 방지)
     */
    showError(message) {
        const formContainer = document.getElementById('calculatorForm');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-state';

        const errorIcon = document.createElement('p');
        errorIcon.textContent = '⚠️';
        errorDiv.appendChild(errorIcon);

        const errorMsg = document.createElement('p');
        errorMsg.className = 'error-detail';
        errorMsg.textContent = message; // textContent로 안전하게 설정
        errorDiv.appendChild(errorMsg);

        formContainer.innerHTML = '';
        formContainer.appendChild(errorDiv);
    }

    /**
     * 추가 버튼 활성화
     */
    enableAddButton() {
        const btn = document.getElementById('btnAddToQuote');
        if (btn) {
            btn.disabled = false;
        }
    }

    /**
     * 추가 버튼 비활성화
     */
    disableAddButton() {
        const btn = document.getElementById('btnAddToQuote');
        if (btn) {
            btn.disabled = true;
        }
    }
}

// DOM 로드 완료 시 초기화
document.addEventListener('DOMContentLoaded', () => {
    window.calculator = new UnifiedCalculator();
});
