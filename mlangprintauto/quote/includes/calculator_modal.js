/**
 * 견적서 작성 - 계산기 모달 시스템
 * 제품 선택 시 해당 제품의 계산기를 iframe 모달로 띄우고
 * 계산 완료 시 postMessage로 가격 데이터를 받아 견적서 폼에 자동 입력
 */

// 제품별 계산기 URL 매핑
const CALCULATOR_URLS = {
    '전단지': '/mlangprintauto/inserted/index.php',
    '명함': '/mlangprintauto/namecard/index.php',
    '봉투': '/mlangprintauto/envelope/index.php',
    '스티커': '/mlangprintauto/sticker_new/index.php',
    '자석스티커': '/mlangprintauto/msticker/index.php',
    '카다록': '/mlangprintauto/cadarok/index.php',
    '포스터': '/mlangprintauto/littleprint/index.php',
    '상품권': '/mlangprintauto/merchandisebond/index.php',
    'NCR양식': '/mlangprintauto/ncrflambeau/index.php'
};

// 제품별 product_type 매핑
const PRODUCT_TYPE_MAP = {
    '전단지': 'inserted',
    '명함': 'namecard',
    '봉투': 'envelope',
    '스티커': 'sticker',
    '자석스티커': 'msticker',
    '카다록': 'cadarok',
    '포스터': 'littleprint',
    '상품권': 'merchandisebond',
    'NCR양식': 'ncrflambeau'
};

class CalculatorModal {
    constructor() {
        this.modal = null;
        this.iframe = null;
        this.currentRow = null; // 현재 작업 중인 품목 행
        this.productName = null; // 현재 선택된 제품명

        this.init();
    }

    init() {
        this.createModal();
        this.setupMessageListener();
    }

    // 모달 HTML 생성
    createModal() {
        const modalHTML = `
            <div id="calculatorModal" class="calc-modal" style="display:none;">
                <div class="calc-modal-overlay"></div>
                <div class="calc-modal-content">
                    <div class="calc-modal-header">
                        <h3 id="calcModalTitle">제품 계산기</h3>
                        <button type="button" class="calc-modal-close" id="calcModalClose">&times;</button>
                    </div>
                    <div class="calc-modal-body">
                        <iframe id="calculatorIframe" frameborder="0"></iframe>
                    </div>
                    <div class="calc-modal-footer" style="background: #f8f9fa; text-align: center; border-top: 1px solid #dee2e6;">
                        <div style="color: #666; font-size: 14px;">
                            💡 <strong>계산기 내부</strong>에서 옵션을 선택한 후 <strong style="color: #217346;">"✅ 견적서에 적용"</strong> 버튼을 눌러주세요
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        this.modal = document.getElementById('calculatorModal');
        this.iframe = document.getElementById('calculatorIframe');

        // 이벤트 리스너 (닫기 버튼만)
        document.getElementById('calcModalClose').addEventListener('click', () => this.close());

        // 오버레이 클릭 시 닫기
        this.modal.querySelector('.calc-modal-overlay').addEventListener('click', () => this.close());
    }

    // postMessage 리스너 설정
    setupMessageListener() {
        window.addEventListener('message', (event) => {
            // 보안: origin 검증 (같은 도메인만 허용)
            if (event.origin !== window.location.origin) {
                return; // 다른 도메인 메시지 무시
            }

            // type이 없는 메시지 무시 (브라우저 확장 프로그램 등)
            if (!event.data || !event.data.type) {
                return;
            }

            // 우리가 관심있는 메시지만 로깅
            if (event.data.type.startsWith('CALCULATOR_')) {
                console.log('📨 계산기 메시지 수신:', {
                    type: event.data.type,
                    hasPayload: !!event.data.payload,
                    payload: event.data.payload
                });
            }

            // 계산기에서 전송한 가격 데이터 처리
            if (event.data.type === 'CALCULATOR_PRICE_DATA') {
                console.log('✅ CALCULATOR_PRICE_DATA 수신, handlePriceData 호출');
                this.handlePriceData(event.data.payload);
            }

            // 계산기에서 모달 닫기 요청 (전단지는 직접 AJAX 저장 후 모달 닫기)
            if (event.data.type === 'CALCULATOR_CLOSE_MODAL') {
                console.log('🚪 계산기에서 모달 닫기 요청 받음 → 페이지 새로고침');
                this.close();

                // 페이지 새로고침하여 quotation_temp 데이터 표시
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            }
        });
    }

    // 모달 열기
    open(productName, row) {
        console.log('🚀 [TUNNEL 1/5] 모달 열기 시작:', {
            productName: productName,
            hasRow: !!row,
            rowType: row ? row.constructor.name : 'null'
        });

        this.productName = productName;
        this.currentRow = row;

        const calculatorUrl = CALCULATOR_URLS[productName];

        if (!calculatorUrl) {
            alert('해당 제품의 계산기를 찾을 수 없습니다.');
            return;
        }

        // 모달 제목 설정
        document.getElementById('calcModalTitle').textContent = `${productName} 계산기`;

        // iframe URL 설정 (쿼리 파라미터로 견적서 모드 전달)
        this.iframe.src = calculatorUrl + '?mode=quotation';

        console.log('✅ [TUNNEL 1/5] iframe 로드 시작:', calculatorUrl + '?mode=quotation');

        // 모달 표시
        this.modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
    }

    // 모달 닫기
    close() {
        this.modal.style.display = 'none';
        document.body.style.overflow = ''; // 스크롤 복원

        // iframe 초기화
        this.iframe.src = 'about:blank';
        this.currentRow = null;
        this.productName = null;
    }

    // 계산기에서 전송한 가격 데이터 처리
    handlePriceData(data) {
        console.log('📨 [TUNNEL 3/5] 부모창에서 가격 데이터 수신:', {
            hasData: !!data,
            product_name: data?.product_name,
            specification: data?.specification,
            quantity: data?.quantity,
            supply_price: data?.supply_price
        });

        // 임시 저장
        this.calculatedData = data;

        // ✅ 자동으로 견적서에 적용 (UX 개선)
        console.log('🔄 [TUNNEL 4/5] 견적서 폼에 데이터 적용 시작');
        this.applyToQuote();
    }

    // 견적서 폼에 가격 데이터 적용 (현재 행의 입력 필드에 직접 입력)
    applyToQuote() {
        console.log('📝 [TUNNEL 4/5] applyToQuote 시작 - 폼 필드에 직접 입력:', {
            hasCalculatedData: !!this.calculatedData,
            hasCurrentRow: !!this.currentRow,
            productName: this.productName
        });

        if (!this.calculatedData || !this.currentRow) {
            console.error('❌ [TUNNEL 실패] 데이터 누락:', {
                calculatedData: this.calculatedData,
                currentRow: this.currentRow
            });
            alert('계산된 가격 데이터가 없습니다.');
            return;
        }

        // 현재 행의 입력 필드에 데이터 채우기
        this.fillCurrentRow(this.calculatedData);

        // 모달 닫기
        this.close();

        // 성공 메시지
        console.log('✅ [TUNNEL 5/5] 견적서 폼에 데이터 입력 완료:', this.productName);
    }

    // 현재 행의 입력 필드에 계산 데이터 채우기
    fillCurrentRow(data) {
        const row = this.currentRow;
        console.log('📝 fillCurrentRow 시작:', {
            product: this.productName,
            specification: data.specification,
            quantity: data.quantity,
            supply_price: data.supply_price
        });

        try {
            // 1. 제품명 설정
            const productSelect = row.querySelector('.product-select');
            if (productSelect && !productSelect.readOnly) {
                productSelect.value = this.productName;
                console.log('✅ 제품명 설정:', this.productName);
            }

            // 2. 규격 설정 (span 표시 + hidden input 저장)
            const specDisplay = row.querySelector('.spec-display');
            const specInput = row.querySelector('input[name*="[specification]"]');

            if (specDisplay && specInput) {
                const specText = data.specification || '';
                specDisplay.textContent = specText;  // span에 표시 (white-space: pre-line으로 줄바꿈 처리)
                specInput.value = specText;          // hidden input에 저장
                console.log('✅ 규격 설정:', specText);
            } else if (specInput) {
                // Fallback: 기존 input 방식 (하위 호환성)
                specInput.value = data.specification || '';
                console.log('✅ 규격 설정 (legacy):', data.specification);
            }

            // =================== 수정된 로직 시작 ===================
            let displayQuantity = data.quantity || 1;
            let displayMesu = parseInt(data.mesu) || 0;

            // 전단지(inserted)의 경우, 규격 문자열에서 직접 파싱하여 값을 재정의
            if ((this.productName === '전단지' || (data.product_type && data.product_type.includes('inserted'))) && data.specification) {
                const reamMatch = data.specification.match(/([0-9.]+)연/);
                if (reamMatch && reamMatch[1]) {
                    displayQuantity = parseFloat(reamMatch[1]);
                }

                const mesuMatch = data.specification.match(/\(([0-9,]+)매\)/);
                if (mesuMatch && mesuMatch[1]) {
                    displayMesu = parseInt(mesuMatch[1].replace(/,/g, ''));
                }
            }
            // =================== 수정된 로직 끝 =====================

            // 3. 수량 설정
            const qtyInput = row.querySelector('.qty-input');
            if (qtyInput) {
                // ✅ create.php와 동일한 스마트 포맷팅 적용
                const qtyDisplay = (displayQuantity == Math.floor(displayQuantity))
                    ? parseInt(displayQuantity)
                    : parseFloat(displayQuantity.toFixed(2)).toString().replace(/\.?0+$/, '');

                qtyInput.value = qtyDisplay;
                console.log('✅ 수량 설정:', qtyDisplay, data.unit === '연' ? '(연 단위)' : '');
            }

            // 4. 단위 설정
            const unitInput = row.querySelector('input[name*="[unit]"]');
            if (unitInput) {
                const unit = data.unit || '개';
                unitInput.value = unit;
                console.log('✅ 단위 설정:', unit);

                const existingMesuDiv = unitInput.parentNode.querySelector('.mesu-info');
                if (existingMesuDiv) {
                    existingMesuDiv.remove();
                }

                if (displayMesu > 0) {
                    const mesuDiv = document.createElement('div');
                    mesuDiv.className = 'mesu-info';
                    mesuDiv.style.cssText = 'color:#666; font-size:11px; margin-top:2px;';
                    mesuDiv.textContent = '(' + displayMesu.toLocaleString() + '매)';
                    unitInput.parentNode.appendChild(mesuDiv);
                    console.log('✅ 매수 표시:', displayMesu);
                }
            }

            // 5. 공급가 설정
            const supplyInput = row.querySelector('.supply-input');
            if (supplyInput) {
                const supply = parseInt(data.supply_price) || 0;
                supplyInput.value = supply;
                console.log('✅ 공급가 설정:', supply);
            }

            // 6. 단가 계산 (전단지는 단가를 비움, 다른 품목은 공급가 ÷ 수량)
            const supply = parseInt(supplyInput.value) || 0;
            const qty = parseFloat(qtyInput.value) || 1;
            
            const priceInput = row.querySelector('.price-input');
            if (priceInput) {
                // 전단지(inserted)는 단가를 비움
                if (this.productName === '전단지' || (data.product_type && data.product_type.includes('inserted'))) {
                    priceInput.value = '';
                    priceInput.placeholder = '-';
                    console.log('✅ 전단지 단가: 비움 (공급가액만 표시)');
                } else {
                    // 다른 품목은 단가 계산
                    const unitPrice = qty > 0 ? Math.round(supply / qty) : 0;
                    priceInput.value = unitPrice;
                    console.log('✅ 단가 계산:', unitPrice, '(공급가', supply, '÷ 수량', qty, ')');
                }
            }

            // 7. VAT와 총액 계산
            const vat = Math.round(supply * 0.1);
            const total = supply + vat;

            row.querySelector('.vat-cell').textContent = vat.toLocaleString();
            row.querySelector('.total-cell').textContent = total.toLocaleString();
            console.log('✅ VAT 및 총액 계산:', {vat: vat, total: total});

            // 8. 전체 합계 재계산 (create.php의 calculateTotals() 함수 호출)
            if (typeof window.calculateTotals === 'function') {
                window.calculateTotals();
                console.log('✅ 전체 합계 재계산 완료');
            } else {
                console.warn('⚠️ calculateTotals() 함수를 찾을 수 없습니다');
            }

            console.log('✅ fillCurrentRow 완료');
        } catch (error) {
            console.error('❌ fillCurrentRow 오류:', error);
            alert('데이터 입력 중 오류가 발생했습니다: ' + error.message);
        }
    }
}

// 전역 인스턴스 생성
let calculatorModal = null;

// DOM 로드 후 초기화
document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 calculator_modal.js DOMContentLoaded 이벤트 발생');
    calculatorModal = new CalculatorModal();
    console.log('✅ CalculatorModal 인스턴스 생성 완료:', calculatorModal);
});

// 제품 선택 시 계산기 모달 오픈 함수 (외부에서 호출)
function openCalculatorModal(productName, row) {
    if (!calculatorModal) {
        console.error('CalculatorModal이 초기화되지 않았습니다.');
        return;
    }

    if (!CALCULATOR_URLS[productName]) {
        // 계산기가 없는 제품 (배송비, 직접입력 등)
        return;
    }

    calculatorModal.open(productName, row);
}

// 계산기 내부에서 사용할 헬퍼 함수 (계산기 페이지에서 호출)
// window.parent.postCalculatorData(data) 형태로 호출
window.postCalculatorData = function(data) {
    // 부모 창으로 postMessage 전송
    window.parent.postMessage({
        type: 'CALCULATOR_PRICE_DATA',
        payload: data
    }, window.location.origin);
};
