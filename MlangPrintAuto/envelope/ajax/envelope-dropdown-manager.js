/**
 * Envelope 드롭다운 관리자
 * envelope 시스템의 2단계 드롭다운 (구분 → 종류)을 관리합니다.
 */

class EnvelopeDropdownManager {
    constructor() {
        // API 엔드포인트 설정
        this.endpoints = {
            envelopeTypes: 'ajax/get_envelope_types.php',
            calculate: 'ajax/calculate_envelope_price.php'
        };
        
        // 로딩 상태 관리
        this.loadingStates = new Map();
        
        // 2단계 드롭다운 체인 구조 (구분 → 종류)
        this.dropdownChain = ['MY_type', 'PN_type'];
        
        // 필수 드롭다운 목록 (가격 계산을 위해)
        this.requiredDropdowns = ['MY_type', 'PN_type', 'MY_amount', 'POtype'];
        
        // 로딩 인디케이터 및 에러 핸들러 초기화
        this.loadingIndicator = new LoadingIndicator();
        this.errorHandler = new ErrorHandler();
        
        // 이벤트 리스너 초기화
        this.initializeEventListeners();
    }
    
    /**
     * 이벤트 리스너 초기화
     */
    initializeEventListeners() {
        // 구분(MY_type) 변경 시 종류(PN_type) 업데이트
        const myTypeDropdown = document.getElementById('MY_type');
        if (myTypeDropdown) {
            myTypeDropdown.addEventListener('change', (e) => {
                this.handleDropdownChange('MY_type', 'PN_type');
            });
        }
        
        // 모든 필수 드롭다운에 가격 계산 트리거 추가
        this.requiredDropdowns.forEach(dropdownId => {
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.addEventListener('change', () => {
                    this.triggerPriceCalculation();
                });
            }
        });
    }
    
    /**
     * 드롭다운 변경 이벤트 처리
     * @param {string} sourceDropdown - 변경된 드롭다운 ID
     * @param {string} targetDropdown - 업데이트할 대상 드롭다운 ID
     */
    handleDropdownChange(sourceDropdown, targetDropdown) {
        const sourceElement = document.getElementById(sourceDropdown);
        const targetElement = document.getElementById(targetDropdown);
        
        if (!sourceElement || !targetElement) {
            console.error('드롭다운 요소를 찾을 수 없습니다:', sourceDropdown, targetDropdown);
            return;
        }
        
        const selectedValue = sourceElement.value;
        
        // 대상 드롭다운 초기화
        this.resetDropdown(targetElement);
        
        // 선택값이 없으면 종료
        if (!selectedValue || selectedValue === '') {
            return;
        }
        
        // Ajax 요청으로 옵션 업데이트
        this.updateDropdownOptions(sourceDropdown, targetDropdown, selectedValue);
    }
    
    /**
     * Ajax 요청으로 드롭다운 옵션 업데이트
     * @param {string} sourceDropdown - 소스 드롭다운 ID
     * @param {string} targetDropdown - 대상 드롭다운 ID
     * @param {string} selectedValue - 선택된 값
     */
    updateDropdownOptions(sourceDropdown, targetDropdown, selectedValue) {
        const targetElement = document.getElementById(targetDropdown);
        
        // 로딩 상태 시작
        this.setLoadingState(targetDropdown, true);
        
        // Ajax 요청 데이터 준비
        const requestData = {
            category_type: selectedValue
        };
        
        // Ajax 요청 실행
        this.makeAjaxRequest(
            this.endpoints.envelopeTypes,
            requestData,
            (response) => {
                // 성공 콜백
                this.handleOptionsUpdateSuccess(targetDropdown, response);
            },
            (error) => {
                // 에러 콜백
                this.handleOptionsUpdateError(targetDropdown, error);
            }
        );
    }
    
    /**
     * Ajax 요청 실행
     * @param {string} url - 요청 URL
     * @param {Object} data - 요청 데이터
     * @param {Function} successCallback - 성공 콜백
     * @param {Function} errorCallback - 에러 콜백
     */
    makeAjaxRequest(url, data, successCallback, errorCallback) {
        const xhr = new XMLHttpRequest();
        
        // GET 요청을 위한 URL 파라미터 생성
        const params = new URLSearchParams(data).toString();
        const requestUrl = `${url}?${params}`;
        
        xhr.open('GET', requestUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        // 타임아웃 설정 (10초)
        xhr.timeout = 10000;
        
        xhr.onreadystatechange = () => {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            successCallback(response);
                        } else {
                            errorCallback(response.error || '서버에서 에러가 발생했습니다.');
                        }
                    } catch (e) {
                        errorCallback('응답 데이터 파싱 중 에러가 발생했습니다.');
                    }
                } else {
                    errorCallback(`HTTP 에러: ${xhr.status}`);
                }
            }
        };
        
        xhr.ontimeout = () => {
            errorCallback('요청 시간이 초과되었습니다.');
        };
        
        xhr.onerror = () => {
            errorCallback('네트워크 에러가 발생했습니다.');
        };
        
        xhr.send();
    }
    
    /**
     * 옵션 업데이트 성공 처리
     * @param {string} targetDropdown - 대상 드롭다운 ID
     * @param {Object} response - 서버 응답
     */
    handleOptionsUpdateSuccess(targetDropdown, response) {
        const targetElement = document.getElementById(targetDropdown);
        
        // 로딩 상태 종료
        this.setLoadingState(targetDropdown, false);
        
        // 옵션 추가
        if (response.data && Array.isArray(response.data)) {
            response.data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.id;
                optionElement.textContent = option.title;
                targetElement.appendChild(optionElement);
            });
            
            // 옵션이 없는 경우 안내 메시지
            if (response.data.length === 0) {
                const optionElement = document.createElement('option');
                optionElement.value = '';
                optionElement.textContent = '선택 가능한 옵션이 없습니다';
                optionElement.disabled = true;
                targetElement.appendChild(optionElement);
            }
        }
    }
    
    /**
     * 옵션 업데이트 에러 처리
     * @param {string} targetDropdown - 대상 드롭다운 ID
     * @param {string} error - 에러 메시지
     */
    handleOptionsUpdateError(targetDropdown, error) {
        // 로딩 상태 종료
        this.setLoadingState(targetDropdown, false);
        
        // 에러 처리
        this.errorHandler.handleAjaxError(error, `${targetDropdown} 옵션 로드 실패`);
        
        // 에러 옵션 추가
        const targetElement = document.getElementById(targetDropdown);
        const optionElement = document.createElement('option');
        optionElement.value = '';
        optionElement.textContent = '옵션 로드 실패 - 다시 시도해주세요';
        optionElement.disabled = true;
        targetElement.appendChild(optionElement);
    }
    
    /**
     * 드롭다운 초기화
     * @param {HTMLElement} dropdown - 드롭다운 요소
     */
    resetDropdown(dropdown) {
        // 기본 옵션 제외하고 모든 옵션 제거
        const options = dropdown.querySelectorAll('option');
        options.forEach((option, index) => {
            if (index > 0) { // 첫 번째 옵션(기본 선택 옵션)은 유지
                option.remove();
            }
        });
        
        // 선택값 초기화
        dropdown.selectedIndex = 0;
    }
    
    /**
     * 로딩 상태 설정
     * @param {string} dropdownId - 드롭다운 ID
     * @param {boolean} isLoading - 로딩 상태
     */
    setLoadingState(dropdownId, isLoading) {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;
        
        this.loadingStates.set(dropdownId, isLoading);
        
        if (isLoading) {
            // 로딩 상태 표시
            dropdown.disabled = true;
            this.loadingIndicator.show(dropdown);
            
            // 로딩 옵션 추가
            const loadingOption = document.createElement('option');
            loadingOption.value = '';
            loadingOption.textContent = '로딩 중...';
            loadingOption.disabled = true;
            loadingOption.className = 'loading-option';
            dropdown.appendChild(loadingOption);
        } else {
            // 로딩 상태 해제
            dropdown.disabled = false;
            this.loadingIndicator.hide(dropdown);
            
            // 로딩 옵션 제거
            const loadingOptions = dropdown.querySelectorAll('.loading-option');
            loadingOptions.forEach(option => option.remove());
        }
    }
    
    /**
     * 가격 계산 트리거
     * 모든 필수 드롭다운이 선택되었을 때 자동으로 가격 계산을 실행합니다.
     */
    triggerPriceCalculation() {
        // 모든 필수 드롭다운 값 확인
        const allSelected = this.requiredDropdowns.every(dropdownId => {
            const dropdown = document.getElementById(dropdownId);
            return dropdown && dropdown.value && dropdown.value !== '';
        });
        
        if (!allSelected) {
            // 필수값이 누락된 경우 가격 필드 초기화
            this.clearPriceFields();
            return;
        }
        
        // 가격 계산 실행
        this.calculatePrice();
    }
    
    /**
     * 가격 계산 실행
     */
    calculatePrice() {
        // 모든 드롭다운 값 수집
        const formData = {};
        this.requiredDropdowns.forEach(dropdownId => {
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                formData[dropdownId] = dropdown.value;
            }
        });
        
        // 가격 계산 Ajax 요청
        this.makeAjaxRequest(
            this.endpoints.calculate,
            formData,
            (response) => {
                this.handlePriceCalculationSuccess(response);
            },
            (error) => {
                this.handlePriceCalculationError(error);
            }
        );
    }
    
    /**
     * 가격 계산 성공 처리
     * @param {Object} response - 서버 응답
     */
    handlePriceCalculationSuccess(response) {
        if (response.data) {
            const data = response.data;
            
            // 가격 필드 업데이트
            this.updatePriceField('print_price', data.print_price);
            this.updatePriceField('design_price', data.design_price);
            this.updatePriceField('total_price', data.total_price);
            
            // 수량 표시 업데이트
            if (data.quantity_display) {
                this.updateQuantityDisplay(data.quantity_display);
            }
        }
    }
    
    /**
     * 가격 계산 에러 처리
     * @param {string} error - 에러 메시지
     */
    handlePriceCalculationError(error) {
        this.errorHandler.handleAjaxError(error, '가격 계산 실패');
        this.clearPriceFields();
    }
    
    /**
     * 가격 필드 업데이트
     * @param {string} fieldId - 필드 ID
     * @param {number} value - 가격 값
     */
    updatePriceField(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            // 숫자 포맷팅 (천 단위 콤마)
            const formattedValue = new Intl.NumberFormat('ko-KR').format(value);
            field.textContent = formattedValue + '원';
        }
    }
    
    /**
     * 수량 표시 업데이트
     * @param {string} quantityDisplay - 수량 표시 텍스트
     */
    updateQuantityDisplay(quantityDisplay) {
        const field = document.getElementById('quantity_display');
        if (field) {
            field.textContent = quantityDisplay;
        }
    }
    
    /**
     * 가격 필드 초기화
     */
    clearPriceFields() {
        const priceFields = ['print_price', 'design_price', 'total_price'];
        priceFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.textContent = '-';
            }
        });
        
        // 수량 표시도 초기화
        const quantityField = document.getElementById('quantity_display');
        if (quantityField) {
            quantityField.textContent = '';
        }
    }
    
    /**
     * 현재 로딩 상태 확인
     * @param {string} dropdownId - 드롭다운 ID
     * @returns {boolean} 로딩 상태
     */
    isLoading(dropdownId) {
        return this.loadingStates.get(dropdownId) || false;
    }
    
    /**
     * 모든 드롭다운 초기화
     */
    resetAllDropdowns() {
        this.dropdownChain.forEach(dropdownId => {
            const dropdown = document.getElementById(dropdownId);
            if (dropdown && dropdownId !== 'MY_type') { // 첫 번째 드롭다운은 제외
                this.resetDropdown(dropdown);
            }
        });
        
        this.clearPriceFields();
    }
}

/**
 * 로딩 인디케이터 클래스
 * Ajax 요청 중 로딩 상태를 시각적으로 표시합니다.
 */
class LoadingIndicator {
    constructor() {
        this.spinners = new Map();
    }
    
    /**
     * 로딩 인디케이터 표시
     * @param {HTMLElement} element - 대상 요소
     */
    show(element) {
        if (!element) return;
        
        // 기존 스피너가 있으면 제거
        this.hide(element);
        
        // 스피너 생성
        const spinner = document.createElement('div');
        spinner.className = 'loading-spinner';
        spinner.innerHTML = '⟳';
        spinner.style.cssText = `
            display: inline-block;
            margin-left: 5px;
            animation: spin 1s linear infinite;
            color: #666;
        `;
        
        // CSS 애니메이션 추가 (한 번만)
        if (!document.getElementById('spinner-style')) {
            const style = document.createElement('style');
            style.id = 'spinner-style';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
        
        // 스피너를 드롭다운 옆에 추가
        element.parentNode.insertBefore(spinner, element.nextSibling);
        this.spinners.set(element, spinner);
    }
    
    /**
     * 로딩 인디케이터 숨김
     * @param {HTMLElement} element - 대상 요소
     */
    hide(element) {
        if (!element) return;
        
        const spinner = this.spinners.get(element);
        if (spinner && spinner.parentNode) {
            spinner.parentNode.removeChild(spinner);
            this.spinners.delete(element);
        }
    }
}

/**
 * 에러 핸들러 클래스
 * Ajax 요청 에러 및 사용자 메시지를 처리합니다.
 */
class ErrorHandler {
    constructor() {
        this.messageContainer = null;
        this.createMessageContainer();
    }
    
    /**
     * 메시지 컨테이너 생성
     */
    createMessageContainer() {
        // 기존 컨테이너가 있으면 사용
        this.messageContainer = document.getElementById('ajax-message-container');
        
        if (!this.messageContainer) {
            this.messageContainer = document.createElement('div');
            this.messageContainer.id = 'ajax-message-container';
            this.messageContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(this.messageContainer);
        }
    }
    
    /**
     * Ajax 에러 처리
     * @param {string} error - 에러 메시지
     * @param {string} context - 에러 컨텍스트
     */
    handleAjaxError(error, context = '') {
        console.error('Ajax 에러:', error, context);
        
        let userMessage = '요청 처리 중 오류가 발생했습니다.';
        
        if (typeof error === 'string') {
            if (error.includes('timeout') || error.includes('시간')) {
                userMessage = '요청 시간이 초과되었습니다. 다시 시도해주세요.';
            } else if (error.includes('network') || error.includes('네트워크')) {
                userMessage = '네트워크 연결을 확인해주세요.';
            } else if (error.includes('HTTP')) {
                userMessage = '서버 연결에 문제가 있습니다.';
            }
        }
        
        this.showUserMessage(userMessage, 'error');
    }
    
    /**
     * 사용자 메시지 표시
     * @param {string} message - 메시지 내용
     * @param {string} type - 메시지 타입 (success, error, warning, info)
     */
    showUserMessage(message, type = 'info') {
        const messageElement = document.createElement('div');
        messageElement.className = `ajax-message ajax-message-${type}`;
        
        // 메시지 스타일
        const colors = {
            success: { bg: '#d4edda', border: '#c3e6cb', text: '#155724' },
            error: { bg: '#f8d7da', border: '#f5c6cb', text: '#721c24' },
            warning: { bg: '#fff3cd', border: '#ffeaa7', text: '#856404' },
            info: { bg: '#d1ecf1', border: '#bee5eb', text: '#0c5460' }
        };
        
        const color = colors[type] || colors.info;
        
        messageElement.style.cssText = `
            background-color: ${color.bg};
            border: 1px solid ${color.border};
            color: ${color.text};
            padding: 12px 16px;
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            animation: slideIn 0.3s ease-out;
        `;
        
        messageElement.innerHTML = `
            ${message}
            <button onclick="this.parentNode.remove()" style="
                position: absolute;
                right: 8px;
                top: 8px;
                background: none;
                border: none;
                font-size: 16px;
                cursor: pointer;
                color: ${color.text};
                opacity: 0.7;
            ">&times;</button>
        `;
        
        // 슬라이드 인 애니메이션 CSS 추가 (한 번만)
        if (!document.getElementById('message-style')) {
            const style = document.createElement('style');
            style.id = 'message-style';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }
        
        this.messageContainer.appendChild(messageElement);
        
        // 5초 후 자동 제거
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.remove();
            }
        }, 5000);
    }
    
    /**
     * 에러 로그 기록
     * @param {Error} error - 에러 객체
     * @param {string} context - 에러 컨텍스트
     */
    logError(error, context = '') {
        const logData = {
            timestamp: new Date().toISOString(),
            error: error.toString(),
            context: context,
            userAgent: navigator.userAgent,
            url: window.location.href
        };
        
        console.error('에러 로그:', logData);
        
        // 필요시 서버로 에러 로그 전송
        // this.sendErrorToServer(logData);
    }
}

// 전역 인스턴스 생성 (페이지 로드 후)
let envelopeDropdownManager;

document.addEventListener('DOMContentLoaded', function() {
    envelopeDropdownManager = new EnvelopeDropdownManager();
    console.log('EnvelopeDropdownManager 초기화 완료');
});