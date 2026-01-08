/**
 * 견적서 작성 - 계산기 모달 시스템
 * 제품 선택 시 해당 제품의 계산기를 iframe 모달로 띄우고
 * 계산 완료 시 postMessage로 가격 데이터를 받아 견적서 폼에 자동 입력
 */

// ✅ Phase 5: 품목별 계산기 재사용 (iframe으로 로드)
// 각 제품의 기존 계산기를 견적서에서 활용 (품목 코드 영향 없음)

// 제품명 → 디렉토리명 매핑
const PRODUCT_DIR_MAP = {
    '전단지': 'inserted',
    '명함': 'namecard',
    '봉투': 'envelope',
    '스티커': 'sticker_new',
    '자석스티커': 'msticker',
    '카다록': 'cadarok',
    '포스터': 'littleprint',
    '상품권': 'merchandisebond',
    'NCR양식': 'ncrflambeau',
    '리플렛': 'leaflet'
};

// 제품별 product_type 매핑 (데이터베이스용)
const PRODUCT_TYPE_MAP = {
    '전단지': 'inserted',
    '명함': 'namecard',
    '봉투': 'envelope',
    '스티커': 'sticker',
    '자석스티커': 'msticker',
    '카다록': 'cadarok',
    '포스터': 'littleprint',
    '상품권': 'merchandisebond',
    'NCR양식': 'ncrflambeau',
    '리플렛': 'leaflet'
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

            // ✅ Phase 4: 통합 계산기 v2.0 메시지 처리
            // 계산기 v2에서는 quotation_temp에 직접 저장 후 CLOSE_CALCULATOR 전송
            if (event.data.type === 'QUOTATION_ITEM_ADDED') {
                console.log('✅ QUOTATION_ITEM_ADDED 수신 (calculator_v2.js)');
                // 데이터는 이미 quotation_temp에 저장됨, 페이지 새로고침 준비
            }

            if (event.data.type === 'CLOSE_CALCULATOR') {
                console.log('🚪 CLOSE_CALCULATOR 수신 → 모달 닫고 페이지 새로고침');
                this.close();

                // 페이지 새로고침하여 quotation_temp 데이터 표시
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            }

            // ⚠️ Backward Compatibility: 레거시 계산기 지원 (필요시)
            if (event.data.type === 'CALCULATOR_PRICE_DATA') {
                console.log('✅ CALCULATOR_PRICE_DATA 수신 (레거시), handlePriceData 호출');
                this.handlePriceData(event.data.payload);
            }

            if (event.data.type === 'CALCULATOR_CLOSE_MODAL') {
                console.log('🚪 CALCULATOR_CLOSE_MODAL 수신 (레거시) → 페이지 새로고침');
                this.close();

                setTimeout(() => {
                    window.location.reload();
                }, 300);
            }
        });
    }

    // 모달 열기
    open(productName, row) {
        console.log('🚀 [견적서 계산기] 모달 열기 시작:', {
            productName: productName,
            hasRow: !!row,
            rowType: row ? row.constructor.name : 'null'
        });

        this.productName = productName;
        this.currentRow = row;

        // 제품 디렉토리명 찾기
        const productDir = PRODUCT_DIR_MAP[productName];

        if (!productDir) {
            alert('해당 제품의 계산기를 찾을 수 없습니다.');
            console.error('❌ PRODUCT_DIR_MAP에 없는 제품:', productName);
            return;
        }

        // 모달 제목 설정
        document.getElementById('calcModalTitle').textContent = `${productName} 계산기`;

        // 제품명을 product_type으로 변환 (데이터베이스용)
        const productType = PRODUCT_TYPE_MAP[productName];

        if (!productType) {
            console.error('제품명에 해당하는 product_type을 찾을 수 없습니다:', productName);
            alert('제품 정보를 찾을 수 없습니다.');
            return;
        }

        // ✅ Phase 5: 품목별 계산기 직접 로드 (mode=quotation 파라미터 전달)
        // 예: /mlangprintauto/sticker_new/index.php?mode=quotation
        const calculatorUrl = `/mlangprintauto/${productDir}/index.php?mode=quotation`;

        // iframe URL 설정
        this.iframe.src = calculatorUrl;

        console.log('✅ [견적서 계산기] iframe 로드 시작:', {
            productName: productName,
            productDir: productDir,
            productType: productType,
            url: calculatorUrl
        });

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

            // 2. 규격 설정
            const specInput = row.querySelector('input[name*="[specification]"]');
            if (specInput) {
                specInput.value = data.specification || '';
                console.log('✅ 규격 설정:', data.specification);
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

            // ✅ 스티커의 경우, mesu를 displayQuantity로 사용 (quantity=1 무시)
            if (this.productName === '스티커' || this.productName === '자석스티커') {
                if (displayMesu > 0) {
                    displayQuantity = displayMesu;
                    console.log('🔧 스티커 수량 수정: mesu=' + displayMesu + ' 사용');
                } else if (data.mesu && parseInt(data.mesu) > 0) {
                    displayQuantity = parseInt(data.mesu);
                    console.log('🔧 스티커 수량 수정: data.mesu=' + data.mesu + ' 사용');
                }
            }
            // =================== 수정된 로직 끝 =====================

            // 3. 수량 설정
            // ✅ 수량 값 계산 (정수면 정수로, 소수면 소수로)
            const qtyValue = (displayQuantity == Math.floor(displayQuantity))
                ? parseInt(displayQuantity)
                : parseFloat(displayQuantity.toFixed(2)).toString().replace(/\.?0+$/, '');

            // ✅ hidden input 설정 (서버 전송용)
            const qtyHiddenInput = row.querySelector('input[name*="[quantity]"]');
            if (qtyHiddenInput) {
                qtyHiddenInput.value = qtyValue;
                console.log('✅ 수량 hidden input 설정:', qtyValue);
            }

            // ✅ 화면 표시 설정 (span.qty-display)
            const qtyDisplaySpan = row.querySelector('.qty-display');
            if (qtyDisplaySpan) {
                const unit = data.unit || '매';
                const displayText = qtyValue.toLocaleString() + unit;  // "1,000매"
                qtyDisplaySpan.textContent = displayText;
                console.log('✅ 수량 화면 표시:', displayText);
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
            let supply = 0;
            if (supplyInput) {
                supply = parseInt(data.supply_price) || 0;
                supplyInput.value = supply;
                console.log('✅ 공급가 설정:', supply);
            }

            // 6. 단가 계산 - ✅ 역계산 제거 (2026-01-08)
            // 품목마다 단가 개념이 다르므로 모든 품목에서 단가는 표시하지 않음
            // (전단지: 연당 가격, 명함: 매당 가격 등)
            // 견적서에서는 총 공급가만 표시하면 충분
            const priceInput = row.querySelector('.price-input');
            if (priceInput) {
                priceInput.value = '';
                priceInput.placeholder = '-';
                priceInput.disabled = true;  // 입력 불가 (역계산 방지)
                console.log('✅ 단가: 표시하지 않음 (역계산 금지)');
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

// DOM 로드 후 초기화 (이미 로드된 경우 즉시 실행)
function initCalculatorModal() {
    console.log('📱 calculator_modal.js 초기화 시작');
    calculatorModal = new CalculatorModal();
    console.log('✅ CalculatorModal 인스턴스 생성 완료:', calculatorModal);
}

// DOMContentLoaded 이벤트가 이미 발생했는지 체크
if (document.readyState === 'loading') {
    // 아직 로딩 중이면 이벤트 리스너 등록
    document.addEventListener('DOMContentLoaded', initCalculatorModal);
} else {
    // 이미 DOM이 로드되었으면 즉시 실행
    initCalculatorModal();
}

// 제품 선택 시 계산기 모달 오픈 함수 (외부에서 호출)
function openCalculatorModal(productName, row) {
    if (!calculatorModal) {
        console.error('CalculatorModal이 초기화되지 않았습니다.');
        return;
    }

    if (!PRODUCT_DIR_MAP[productName]) {
        // 계산기가 없는 제품 (배송비, 직접입력 등)
        console.log('ℹ️ 계산기가 없는 제품:', productName);
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
