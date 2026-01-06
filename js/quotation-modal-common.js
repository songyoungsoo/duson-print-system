/**
 * 견적서 모달 공통 JavaScript
 * 모든 제품 계산기에서 공유하는 견적서 적용 기능
 */

/**
 * 견적서에 적용 - 계산기에서 견적서 생성 페이지로 데이터 전송
 *
 * 이 함수는:
 * 1. 필수 옵션 선택 여부 확인
 * 2. 가격 계산 (필요시 자동 실행)
 * 3. 제품 상세 정보 수집
 * 4. postMessage로 부모 창(create.php)에 전송
 */
function applyToQuotation() {
    console.log('🚀 [견적서 적용] applyToQuotation() 호출됨');

    // 1. 필수 옵션 선택 여부 확인
    if (!validateRequiredFields()) {
        alert('모든 필수 옵션을 선택해주세요.');
        console.error('❌ 필수 옵션 미선택');
        return;
    }

    // 2. 가격 계산 여부 확인 - 없으면 자동 계산
    // 전단지: Order_PriceForm / 일반: total_price / 명함: total_supply_price / 스티커: price / 상품권: PriceForm 체크
    const hasPriceData = window.currentPriceData &&
        (window.currentPriceData.Order_PriceForm || window.currentPriceData.total_price || window.currentPriceData.total_supply_price || window.currentPriceData.price || window.currentPriceData.PriceForm);

    if (!hasPriceData) {
        console.log('⚠️ [견적서 적용] 가격 데이터 없음 - 자동 계산 시도');

        // 제품별 가격 계산 함수 시도 (우선순위 순)
        let calcFunction = null;

        if (typeof window.autoCalculatePrice === 'function') {
            calcFunction = window.autoCalculatePrice;
            console.log('📞 autoCalculatePrice() 사용');
        } else if (typeof window.calculatePrice === 'function') {
            calcFunction = window.calculatePrice;
            console.log('📞 calculatePrice() 사용');
        } else if (typeof window.calc_ajax === 'function') {
            calcFunction = window.calc_ajax;
            console.log('📞 calc_ajax() 사용');
        } else if (typeof window.calculatePriceAjax === 'function') {
            calcFunction = window.calculatePriceAjax;
            console.log('📞 calculatePriceAjax() 사용');
        }

        if (calcFunction) {
            // 가격 계산 함수 호출
            calcFunction();

            // 계산 완료 대기 (최대 3초)
            let attempts = 0;
            const maxAttempts = 30; // 30 * 100ms = 3초

            const waitForPrice = setInterval(() => {
                attempts++;

                const hasPriceNow = window.currentPriceData &&
                    (window.currentPriceData.Order_PriceForm || window.currentPriceData.total_price || window.currentPriceData.total_supply_price || window.currentPriceData.price || window.currentPriceData.PriceForm);

                if (hasPriceNow) {
                    // 가격 계산 완료
                    clearInterval(waitForPrice);
                    console.log('✅ [견적서 적용] 가격 계산 완료:', window.currentPriceData);
                    proceedWithApply();
                } else if (attempts >= maxAttempts) {
                    // 타임아웃 - DOM에서 가격 읽기 시도
                    clearInterval(waitForPrice);
                    console.warn('⚠️ [견적서 적용] 가격 계산 타임아웃 - DOM 읽기 시도');
                    proceedWithApply(); // DOM 읽기 로직 시도
                }
            }, 100);

            return; // 비동기 처리 대기
        } else {
            // 가격 계산 함수가 없음 - DOM에서 직접 읽기 시도
            console.warn('⚠️ [견적서 적용] 가격 계산 함수 없음 - DOM 읽기 시도');
            console.log('   시도한 함수: autoCalculatePrice, calculatePrice, calc_ajax, calculatePriceAjax');
            // return 대신 proceedWithApply() 호출하여 DOM 읽기 시도
        }
    }

    console.log('✅ [견적서 적용] 가격 데이터 확인:', window.currentPriceData);
    proceedWithApply();
}

/**
 * 필수 필드 검증
 */
function validateRequiredFields() {
    // 현재 페이지 경로로 제품 타입 판단
    const currentPath = window.location.pathname;

    if (currentPath.includes('/inserted/') || currentPath.includes('/leaflet/')) {
        // 전단지/리플렛: 색상, 종류, 규격, 인쇄면, 수량 필수
        const required = ['MY_type', 'MY_Fsd', 'PN_type', 'POtype', 'MY_amount'];
        for (const fieldId of required) {
            const field = document.getElementById(fieldId);
            if (!field || !field.value) {
                console.error('필수 필드 누락:', fieldId);
                return false;
            }
        }
        return true;
    } else if (currentPath.includes('/namecard/')) {
        // 명함: MY_type, Section, POtype, MY_amount 필수
        const required = ['MY_type', 'Section', 'POtype', 'MY_amount'];
        for (const fieldId of required) {
            const field = document.getElementById(fieldId);
            if (!field || !field.value) {
                console.error('필수 필드 누락:', fieldId);
                return false;
            }
        }
        return true;
    }

    // 기타 제품: 기본 검증 (최소한 가격 계산이 가능한지만 확인)
    return true;
}

/**
 * 견적서 적용 진행 (가격 계산 완료 후)
 */
function proceedWithApply() {
    console.log('📋 [견적서 적용] 데이터 전송 시작');

    // 2. 현재 제품 타입 감지 (URL 기반)
    const currentPath = window.location.pathname;
    let productType = '';
    let productName = '';

    if (currentPath.includes('/inserted/')) {
        productType = 'inserted';
        productName = '전단지';
    } else if (currentPath.includes('/namecard/')) {
        productType = 'namecard';
        productName = '명함';
    } else if (currentPath.includes('/envelope/')) {
        productType = 'envelope';
        productName = '봉투';
    } else if (currentPath.includes('/sticker')) {
        productType = 'sticker';
        productName = '스티커';
    } else if (currentPath.includes('/msticker/')) {
        productType = 'msticker';
        productName = '자석스티커';
    } else if (currentPath.includes('/cadarok/')) {
        productType = 'cadarok';
        productName = '카다록';
    } else if (currentPath.includes('/littleprint/')) {
        productType = 'littleprint';
        productName = '포스터';
    } else if (currentPath.includes('/merchandisebond/')) {
        productType = 'merchandisebond';
        productName = '상품권';
    } else if (currentPath.includes('/ncrflambeau/')) {
        productType = 'ncrflambeau';
        productName = 'NCR양식';
    } else if (currentPath.includes('/leaflet/')) {
        productType = 'leaflet';
        productName = '리플렛';
    }

    console.log('🏷️ [견적서 적용] 제품 타입:', productType, productName);

    // 3. 제품별 규격/옵션 정보 수집
    let specification = '';
    let quantity = 1;
    let unit = '매';
    let flyer_mesu = 0;
    let quantity_display = '';

    try {
        if (productType === 'inserted' || productType === 'leaflet') {
            // 전단지/리플렛 전용 로직
            specification = buildInsertedSpecification();

            // 수량 (연수)
            const myAmount = document.getElementById('MY_amount');
            if (myAmount && myAmount.value) {
                quantity = parseFloat(myAmount.value) || 0.5;
            }
            unit = '연';

            // 매수 정보 (MY_amountRight hidden field)
            const myAmountRight = document.getElementById('MY_amountRight');
            if (myAmountRight && myAmountRight.value) {
                // "2000장" → 2000
                flyer_mesu = parseInt(myAmountRight.value.replace(/[^0-9]/g, '')) || 0;
            }

            // 수량 표시 형식 ("0.5연\n(2,000매)")
            if (flyer_mesu > 0) {
                const yeonDisplay = (Math.floor(quantity) === quantity)
                    ? quantity.toFixed(0)
                    : quantity.toFixed(1);
                quantity_display = `${yeonDisplay}연\n(${flyer_mesu.toLocaleString()}매)`;
            }

            console.log('📋 [전단지] 수량 정보:', {
                quantity: quantity,
                unit: unit,
                flyer_mesu: flyer_mesu,
                quantity_display: quantity_display
            });

        } else if (productType === 'namecard') {
            // 명함
            specification = buildNamecardSpecification();
            const myAmount = document.getElementById('MY_amount');
            if (myAmount) {
                quantity = parseFloat(myAmount.value) || 1;
            }
            unit = '매';

        } else if (productType === 'envelope') {
            // 봉투
            specification = buildEnvelopeSpecification();
            const myAmount = document.getElementById('MY_amount');
            if (myAmount) {
                quantity = parseFloat(myAmount.value) || 1;
            }
            unit = '매';

        } else if (productType === 'sticker') {
            // 스티커: mesu 선택 박스에서 수량 가져오기
            specification = buildStickerSpecification();
            const mesuSelect = document.getElementById('mesu');
            if (mesuSelect && mesuSelect.value) {
                quantity = parseInt(mesuSelect.value) || 1000;
            } else {
                // 폴백: MY_amount 필드 확인 (수동입력 모드)
                const myAmount = document.getElementById('MY_amount');
                if (myAmount) {
                    quantity = parseFloat(myAmount.value) || 1;
                }
            }
            unit = '매';

        } else if (productType === 'msticker') {
            // 자석스티커: mesu 선택 박스에서 수량 가져오기
            specification = buildMstickerSpecification();
            const mesuSelect = document.getElementById('mesu');
            if (mesuSelect && mesuSelect.value) {
                quantity = parseInt(mesuSelect.value) || 1000;
            } else {
                // 폴백: MY_amount 필드 확인 (수동입력 모드)
                const myAmount = document.getElementById('MY_amount');
                if (myAmount) {
                    quantity = parseFloat(myAmount.value) || 1;
                }
            }
            unit = '매';

        } else if (productType === 'cadarok') {
            // 카다록
            specification = buildCadarokSpecification();
            const myAmount = document.getElementById('MY_amount');
            if (myAmount) {
                quantity = parseFloat(myAmount.value) || 1;
            }
            unit = '권';

        } else if (productType === 'littleprint') {
            // 포스터
            specification = buildLittleprintSpecification();
            const myAmount = document.getElementById('MY_amount');
            if (myAmount) {
                quantity = parseFloat(myAmount.value) || 1;
            }
            unit = '매';

        } else if (productType === 'merchandisebond') {
            // 상품권
            specification = buildMerchandisebondSpecification();
            const myAmount = document.getElementById('MY_amount');
            if (myAmount) {
                quantity = parseFloat(myAmount.value) || 1;
            }
            unit = '매';

        } else if (productType === 'ncrflambeau') {
            // NCR양식
            specification = buildNcrflambeauSpecification();
            const myAmount = document.getElementById('MY_amount');
            if (myAmount) {
                quantity = parseFloat(myAmount.value) || 1;
            }
            unit = '권';

        } else {
            // 기타 제품 - 기본 로직
            specification = '제품 옵션 정보';
            console.warn('⚠️ [견적서 적용] 제품별 규격 생성 함수 미구현:', productType);
        }

    } catch (error) {
        console.error('❌ [견적서 적용] 규격 정보 생성 실패:', error);
        alert('규격 정보를 생성할 수 없습니다. 모든 옵션을 선택했는지 확인해주세요.');
        return;
    }

    // 4. 가격 데이터 준비
    let supplyPrice = 0;
    let totalPrice = 0;

    // 🔧 각 제품별 가격 데이터 읽기 (window.currentPriceData 또는 DOM에서)
    if (window.currentPriceData) {
        if (window.currentPriceData.Order_PriceForm) {
            // 방법 1A: 전단지 형식 (Order_PriceForm, Total_PriceForm)
            supplyPrice = Math.round(window.currentPriceData.Order_PriceForm) || 0;
            totalPrice = Math.round(window.currentPriceData.Total_PriceForm) || 0;
            console.log('✅ [가격 읽기] Order_PriceForm 사용:', { supplyPrice, totalPrice });
        } else if (window.currentPriceData.PriceForm) {
            // 방법 1B: 상품권 형식 (PriceForm, Total_PriceForm)
            supplyPrice = Math.round(window.currentPriceData.PriceForm) || 0;
            totalPrice = Math.round(window.currentPriceData.Total_PriceForm) || 0;
            console.log('✅ [가격 읽기] PriceForm 사용 (상품권):', { supplyPrice, totalPrice });
        } else if (window.currentPriceData.total_price) {
            // 방법 1C: 기타 품목 형식 (total_price, vat_price)
            supplyPrice = Math.round(window.currentPriceData.total_price) || 0;
            totalPrice = Math.round(window.currentPriceData.vat_price) || 0;
            console.log('✅ [가격 읽기] total_price 사용:', { supplyPrice, totalPrice });
        } else if (window.currentPriceData.total_supply_price) {
            // 방법 1D: 명함 형식 (total_supply_price, final_total_with_vat)
            supplyPrice = Math.round(window.currentPriceData.total_supply_price) || 0;
            totalPrice = Math.round(window.currentPriceData.final_total_with_vat) || 0;
            console.log('✅ [가격 읽기] total_supply_price 사용:', { supplyPrice, totalPrice });
        } else if (window.currentPriceData.price) {
            // 방법 1E: 스티커 형식 (price, price_vat)
            // price가 문자열인 경우 콤마 제거 후 변환
            const priceStr = window.currentPriceData.price.toString().replace(/,/g, '');
            const priceVatStr = window.currentPriceData.price_vat.toString().replace(/,/g, '');
            supplyPrice = Math.round(parseFloat(priceStr)) || 0;
            totalPrice = Math.round(parseFloat(priceVatStr)) || 0;
            console.log('✅ [가격 읽기] price 사용 (스티커 형식):', { supplyPrice, totalPrice });
        }

    } else {
        // 방법 2: DOM 요소에서 가격 읽기 시도 (기타 제품)
        console.warn('⚠️ [가격 읽기] currentPriceData 없음, DOM에서 읽기 시도');

        // 가격 표시 요소 찾기 (여러 패턴 시도)
        const priceElements = [
            // 명함 등의 가격 표시
            document.querySelector('.price-item.final .price-item-value'),
            document.querySelector('.price-item-value'),
            // 일반적인 가격 표시
            document.getElementById('priceAmount'),
            document.querySelector('.price-amount'),
            document.querySelector('.final-price'),
            document.querySelector('#finalPrice'),
            // 포괄적 검색
            document.querySelector('[class*="price-item-value"]'),
            document.querySelector('[class*="price-amount"]'),
            document.querySelector('[class*="final"]'),
            document.querySelector('[id*="price"]')
        ];

        for (const elem of priceElements) {
            if (elem && elem.textContent) {
                const text = elem.textContent.trim();
                // "123,000원" 또는 "123,000" 형식에서 숫자 추출
                const match = text.match(/([0-9,]+)/);
                if (match) {
                    const price = parseInt(match[1].replace(/,/g, ''));
                    if (price > 0) {
                        // VAT 포함 가격으로 간주
                        totalPrice = price;
                        // 공급가 = VAT 포함가 ÷ 1.1 (역산)
                        supplyPrice = Math.round(price / 1.1);
                        console.log('✅ [가격 읽기] DOM 파싱 성공:', { totalPrice, supplyPrice, source: elem.id || elem.className });
                        break;
                    }
                }
            }
        }
    }

    // 가격 데이터 유효성 최종 검증
    if (totalPrice === 0 || supplyPrice === 0) {
        console.error('❌ [가격 읽기] 가격 데이터를 찾을 수 없음. currentPriceData:', window.currentPriceData);
        alert('가격 정보를 확인할 수 없습니다. 먼저 "견적 계산" 버튼을 눌러주세요.');
        return;
    }

    // 5. postMessage 페이로드 구성
    const payload = {
        product_name: productName,
        product_type: productType,
        specification: specification,
        quantity: quantity,
        unit: unit,
        supply_price: supplyPrice,
        flyer_mesu: flyer_mesu,  // 전단지/리플렛 전용
        quantity_display: quantity_display  // 전단지/리플렛 전용
    };

    // 스티커/자석스티커: mesu 필드도 함께 전송 (quotation_temp에 저장용)
    if (productType === 'sticker' || productType === 'msticker') {
        payload.mesu = quantity;  // quantity에 이미 mesu 값이 들어있음
    }

    // 가격 필드는 출처에 따라 적절한 이름 사용
    if (window.currentPriceData) {
        // currentPriceData가 있으면 원본 필드명 유지
        if (window.currentPriceData.total_with_vat) {
            payload.total_price = supplyPrice;  // 공급가액 (VAT 미포함)
            payload.total_with_vat = totalPrice;  // 총액 (VAT 포함)
        } else if (window.currentPriceData.Total_PriceForm) {
            payload.supply_price = supplyPrice;  // 전단지/상품권은 supply_price가 없을 수 있음
            payload.Total_PriceForm = totalPrice;
        } else {
            payload.total_price = totalPrice;  // 일반 형식
        }
    } else {
        // DOM 파싱: totalPrice는 VAT 포함 총액, supplyPrice는 역산
        payload.total_price = totalPrice;  // VAT 포함 총액
        payload.vat_price = totalPrice - supplyPrice;  // VAT
    }

    console.log('📤 [견적서 적용] 전송할 데이터:', payload);

    // 6. 부모 창으로 postMessage 전송
    if (window.parent && window.parent !== window) {
        window.parent.postMessage({
            type: 'CALCULATOR_PRICE_DATA',
            payload: payload
        }, window.location.origin);

        console.log('✅ [견적서 적용] postMessage 전송 완료');

        // 성공 메시지
        alert('견적서에 추가되었습니다.');

    } else {
        console.error('❌ [견적서 적용] 부모 창이 없습니다 (iframe이 아님)');
        alert('견적서 모달에서만 사용 가능합니다.');
    }
}

/**
 * 전단지 규격 정보 생성
 */
function buildInsertedSpecification() {
    const parts = [];

    // 색상
    const myType = document.getElementById('MY_type');
    if (myType && myType.selectedOptions[0]) {
        parts.push(myType.selectedOptions[0].text);
    }

    // 용지 종류
    const myFsd = document.getElementById('MY_Fsd');
    if (myFsd && myFsd.selectedOptions[0]) {
        parts.push(myFsd.selectedOptions[0].text);
    }

    // 규격
    const pnType = document.getElementById('PN_type');
    if (pnType && pnType.selectedOptions[0]) {
        parts.push(pnType.selectedOptions[0].text);
    }

    // 인쇄면
    const poType = document.getElementById('POtype');
    if (poType && poType.selectedOptions[0]) {
        parts.push(poType.selectedOptions[0].text);
    }

    // 편집비
    const orderType = document.getElementById('ordertype');
    if (orderType && orderType.selectedOptions[0]) {
        parts.push(orderType.selectedOptions[0].text);
    }

    // 추가 옵션 (코팅, 접지, 오시)
    const coatingEnabled = document.getElementById('coating_enabled');
    if (coatingEnabled && coatingEnabled.checked) {
        const coatingType = document.getElementById('coating_type');
        if (coatingType && coatingType.selectedOptions[0]) {
            parts.push('코팅: ' + coatingType.selectedOptions[0].text);
        }
    }

    const foldingEnabled = document.getElementById('folding_enabled');
    if (foldingEnabled && foldingEnabled.checked) {
        const foldingType = document.getElementById('folding_type');
        if (foldingType && foldingType.selectedOptions[0]) {
            parts.push('접지: ' + foldingType.selectedOptions[0].text);
        }
    }

    const creasingEnabled = document.getElementById('creasing_enabled');
    if (creasingEnabled && creasingEnabled.checked) {
        const creasingLines = document.getElementById('creasing_lines');
        if (creasingLines && creasingLines.selectedOptions[0]) {
            parts.push('오시: ' + creasingLines.selectedOptions[0].text);
        }
    }

    return parts.join('\n');
}

/**
 * 명함 규격 정보 생성
 */
function buildNamecardSpecification() {
    const parts = [];

    // 명함 전용 필드들 (실제 ID는 제품 페이지 확인 필요)
    const myType = document.getElementById('MY_type');
    if (myType && myType.selectedOptions[0]) {
        parts.push(myType.selectedOptions[0].text);
    }

    const section = document.getElementById('Section');
    if (section && section.selectedOptions[0]) {
        parts.push(section.selectedOptions[0].text);
    }

    const poType = document.getElementById('POtype');
    if (poType && poType.selectedOptions[0]) {
        parts.push(poType.selectedOptions[0].text);
    }

    return parts.join('\n');
}

/**
 * 봉투 규격 정보 생성
 */
function buildEnvelopeSpecification() {
    const parts = [];

    // 봉투 전용 필드들
    const myType = document.getElementById('MY_type');
    if (myType && myType.selectedOptions[0]) {
        parts.push(myType.selectedOptions[0].text);
    }

    const section = document.getElementById('Section');
    if (section && section.selectedOptions[0]) {
        parts.push(section.selectedOptions[0].text);
    }

    return parts.join('\n');
}

/**
 * 스티커 규격 정보 생성
 */
function buildStickerSpecification() {
    const parts = [];

    // 용지 종류
    const jong = document.getElementById('jong');
    if (jong && jong.selectedOptions[0]) {
        parts.push(jong.selectedOptions[0].text);
    }

    // 재단 형태
    const domusong = document.getElementById('domusong');
    if (domusong && domusong.selectedOptions[0]) {
        const domusongText = domusong.selectedOptions[0].text;
        parts.push(domusongText);
    }

    // 편집비 (인쇄만이 아닌 경우만 표시)
    const uhyung = document.getElementById('uhyung');
    if (uhyung && uhyung.selectedOptions[0]) {
        const uhyungText = uhyung.selectedOptions[0].text;
        if (!uhyungText.includes('인쇄만')) {
            parts.push(uhyungText);
        }
    }

    // 가로/세로 사이즈
    const garo = document.getElementById('garo');
    const sero = document.getElementById('sero');
    if (garo && sero && garo.value && sero.value) {
        parts.push(`${garo.value}mm x ${sero.value}mm`);
    }

    return parts.join('\n');
}

/**
 * 자석스티커 규격 정보 생성
 */
function buildMstickerSpecification() {
    const parts = [];

    const myType = document.getElementById('MY_type');
    if (myType && myType.selectedOptions[0]) {
        parts.push(myType.selectedOptions[0].text);
    }

    const section = document.getElementById('Section');
    if (section && section.selectedOptions[0]) {
        parts.push(section.selectedOptions[0].text);
    }

    const poType = document.getElementById('POtype');
    if (poType && poType.selectedOptions[0]) {
        parts.push(poType.selectedOptions[0].text);
    }

    return parts.join('\n');
}

/**
 * 카다록 규격 정보 생성
 */
function buildCadarokSpecification() {
    const parts = [];

    const myType = document.getElementById('MY_type');
    if (myType && myType.selectedOptions[0]) {
        parts.push(myType.selectedOptions[0].text);
    }

    // 종이 재질 (Section)
    const section = document.getElementById('Section');
    if (section && section.selectedOptions[0]) {
        parts.push(section.selectedOptions[0].text);
    }

    const myFsd = document.getElementById('MY_Fsd');
    if (myFsd && myFsd.selectedOptions[0]) {
        parts.push(myFsd.selectedOptions[0].text);
    }

    const pnType = document.getElementById('PN_type');
    if (pnType && pnType.selectedOptions[0]) {
        parts.push(pnType.selectedOptions[0].text);
    }

    const poType = document.getElementById('POtype');
    if (poType && poType.selectedOptions[0]) {
        parts.push(poType.selectedOptions[0].text);
    }

    return parts.join('\n');
}

/**
 * 포스터 규격 정보 생성
 */
function buildLittleprintSpecification() {
    const parts = [];

    const myType = document.getElementById('MY_type');
    if (myType && myType.selectedOptions[0]) {
        parts.push(myType.selectedOptions[0].text);
    }

    // 종이 재질 (Section)
    const section = document.getElementById('Section');
    if (section && section.selectedOptions[0]) {
        parts.push(section.selectedOptions[0].text);
    }

    const myFsd = document.getElementById('MY_Fsd');
    if (myFsd && myFsd.selectedOptions[0]) {
        parts.push(myFsd.selectedOptions[0].text);
    }

    const pnType = document.getElementById('PN_type');
    if (pnType && pnType.selectedOptions[0]) {
        parts.push(pnType.selectedOptions[0].text);
    }

    const poType = document.getElementById('POtype');
    if (poType && poType.selectedOptions[0]) {
        parts.push(poType.selectedOptions[0].text);
    }

    return parts.join('\n');
}

/**
 * 상품권 규격 정보 생성
 */
function buildMerchandisebondSpecification() {
    const parts = [];

    const myType = document.getElementById('MY_type');
    if (myType && myType.selectedOptions[0]) {
        parts.push(myType.selectedOptions[0].text);
    }

    const section = document.getElementById('Section');
    if (section && section.selectedOptions[0]) {
        parts.push(section.selectedOptions[0].text);
    }

    const poType = document.getElementById('POtype');
    if (poType && poType.selectedOptions[0]) {
        parts.push(poType.selectedOptions[0].text);
    }

    return parts.join('\n');
}

/**
 * NCR양식 규격 정보 생성
 */
function buildNcrflambeauSpecification() {
    const parts = [];

    const myType = document.getElementById('MY_type');
    if (myType && myType.selectedOptions[0]) {
        parts.push(myType.selectedOptions[0].text);
    }

    const myFsd = document.getElementById('MY_Fsd');
    if (myFsd && myFsd.selectedOptions[0]) {
        parts.push(myFsd.selectedOptions[0].text);
    }

    const pnType = document.getElementById('PN_type');
    if (pnType && pnType.selectedOptions[0]) {
        parts.push(pnType.selectedOptions[0].text);
    }

    const poType = document.getElementById('POtype');
    if (poType && poType.selectedOptions[0]) {
        parts.push(poType.selectedOptions[0].text);
    }

    return parts.join('\n');
}

console.log('✅ quotation-modal-common.js 로드 완료');
