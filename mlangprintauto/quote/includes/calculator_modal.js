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

            // 2. 규격 설정 (span 표시 + hidden input)
            const specInput = row.querySelector('input[name*="[specification]"]');
            const specCell = row.querySelector('.col-spec');
            if (specInput && specCell && data.specification) {
                // hidden input으로 변환
                specInput.type = 'hidden';
                specInput.value = data.specification;

                // 기존 spec-display가 있으면 제거
                const existingDisplay = specCell.querySelector('.spec-display');
                if (existingDisplay) {
                    existingDisplay.remove();
                }

                // 새로운 표시용 span 추가 (줄바꿈을 <br>로 변환)
                const specDisplaySpan = document.createElement('span');
                specDisplaySpan.className = 'spec-display';
                specDisplaySpan.innerHTML = data.specification.replace(/\n/g, '<br>');
                specCell.insertBefore(specDisplaySpan, specInput);

                console.log('✅ 규격 설정:', data.specification);
            }

            // 수량과 매수 직접 사용 (specification에서 파싱하지 않음)
            let displayQuantity = parseFloat(data.quantity) || 1;
            // flyer_mesu 우선 사용 (전단지/리플렛 전용), 없으면 mesu 폴백 (레거시 호환)
            let displayMesu = parseInt(data.flyer_mesu) || parseInt(data.mesu) || 0;
            let quantityDisplayText = data.quantity_display || '';  // "0.5연\n(2,000매)" 형식

            // 3. 수량 설정
            const qtyInput = row.querySelector('.qty-input');
            const qtyCell = row.querySelector('.col-qty');
            if (qtyInput && qtyCell) {
                // 수량 입력 필드 값 설정
                qtyInput.value = displayQuantity;

                // 전단지/리플렛인 경우 수량 표시 형식 변경
                if (displayMesu > 0 && quantityDisplayText) {
                    // 기존 입력 필드를 숨기고 표시용 span 추가
                    qtyInput.type = 'hidden';

                    // 기존 qty-display가 있으면 제거
                    const existingDisplay = qtyCell.querySelector('.qty-display');
                    if (existingDisplay) {
                        existingDisplay.remove();
                    }

                    // 새로운 표시용 span 추가
                    const qtyDisplaySpan = document.createElement('span');
                    qtyDisplaySpan.className = 'qty-display';
                    qtyDisplaySpan.innerHTML = quantityDisplayText.replace(/\n/g, '<br>');
                    qtyCell.insertBefore(qtyDisplaySpan, qtyInput);
                }

                console.log('✅ 수량 설정:', displayQuantity, data.unit === '연' ? '(연 단위)' : '');
            }

            // 4. 단위 설정 (hidden)
            const unitInput = row.querySelector('input[name*="[unit]"]');
            if (unitInput) {
                const unit = data.unit || '개';
                unitInput.value = unit;
                console.log('✅ 단위 설정:', unit);
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

            // 8. 추가 hidden 필드 설정 (flyer_mesu, mesu, product_type, source_type)
            // 폼에 이미 있으면 업데이트, 없으면 추가
            const itemIndex = Array.from(row.parentElement.children).indexOf(row);

            // flyer_mesu hidden field (전단지/리플렛 전용 - 스티커용 mesu와 분리)
            let flyerMesuInput = row.querySelector('input[name*="[flyer_mesu]"]');
            if (!flyerMesuInput && displayMesu > 0) {
                flyerMesuInput = document.createElement('input');
                flyerMesuInput.type = 'hidden';
                flyerMesuInput.name = `items[${itemIndex}][flyer_mesu]`;
                row.appendChild(flyerMesuInput);
            }
            if (flyerMesuInput) {
                flyerMesuInput.value = displayMesu;
                console.log('✅ flyer_mesu hidden 필드 설정:', displayMesu);
            }

            // mesu hidden field (레거시 호환용)
            let mesuInput = row.querySelector('input[name*="[mesu]"]');
            if (!mesuInput && displayMesu > 0) {
                mesuInput = document.createElement('input');
                mesuInput.type = 'hidden';
                mesuInput.name = `items[${itemIndex}][mesu]`;
                row.appendChild(mesuInput);
            }
            if (mesuInput) {
                mesuInput.value = displayMesu;
                console.log('✅ mesu hidden 필드 설정 (레거시):', displayMesu);
            }

            // product_type hidden field
            let productTypeInput = row.querySelector('input[name*="[product_type]"]');
            if (!productTypeInput && data.product_type) {
                productTypeInput = document.createElement('input');
                productTypeInput.type = 'hidden';
                productTypeInput.name = `items[${itemIndex}][product_type]`;
                row.appendChild(productTypeInput);
            }
            if (productTypeInput && data.product_type) {
                productTypeInput.value = data.product_type;
                console.log('✅ product_type hidden 필드 설정:', data.product_type);
            }

            // source_type hidden field (calculator로 설정)
            let sourceTypeInput = row.querySelector('input[name*="[source_type]"]');
            if (!sourceTypeInput) {
                sourceTypeInput = document.createElement('input');
                sourceTypeInput.type = 'hidden';
                sourceTypeInput.name = `items[${itemIndex}][source_type]`;
                row.appendChild(sourceTypeInput);
            }
            sourceTypeInput.value = 'calculator';
            console.log('✅ source_type hidden 필드 설정: calculator');

            // 9. 전체 합계 재계산 (create.php의 calculateTotals() 함수 호출)
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
