/**
 * 공통 가격 표시 함수 - 모든 MlangPrintAuto 품목 공통 사용
 * 스티커 스타일의 한 행 중앙정렬 + 부가세 적색 강조
 */

// 공통 가격 상세정보 표시 함수
function updatePriceDetailsCommon(priceData, options = {}) {
    const priceDetails = document.getElementById('priceDetails');
    if (!priceDetails) return;
    
    // 기본 옵션 설정
    const defaultOptions = {
        showEditFee: false,
        editFeeAmount: 0,
        printPriceField: 'base_price',
        supplyPriceField: 'total_price',
        vatPriceField: 'total_with_vat'
    };
    
    const opts = { ...defaultOptions, ...options };
    
    // 가격 데이터 추출
    const printPrice = priceData[opts.printPriceField] || 0;
    const editFee = opts.showEditFee ? opts.editFeeAmount : 0;
    const supplyPrice = priceData[opts.supplyPriceField] || (printPrice + editFee);
    const vatPrice = priceData[opts.vatPriceField] || 0;
    
    // 한 행 중앙정렬 레이아웃으로 표시 - .vat-amount 클래스 사용
    priceDetails.innerHTML = `
        <span>인쇄비: ${new Intl.NumberFormat('ko-KR').format(printPrice)}원</span>
        ${editFee > 0 ? `<span>편집비: ${new Intl.NumberFormat('ko-KR').format(editFee)}원</span>` : ''}
        <span>부가세 포함: <span class="vat-amount">${new Intl.NumberFormat('ko-KR').format(vatPrice)}원</span></span>
    `;
    
    // 강제로 한 줄 레이아웃 스타일 적용 - 모든 CSS 규칙 무시
    priceDetails.style.display = 'flex';
    priceDetails.style.justifyContent = 'center';
    priceDetails.style.alignItems = 'center';
    priceDetails.style.gap = '15px';
    priceDetails.style.flexWrap = 'nowrap';
    priceDetails.style.whiteSpace = 'nowrap';
    priceDetails.style.flexDirection = 'row';
}

// 포스터용 가격 표시 함수
function updatePosterPriceDetails(priceData) {
    updatePriceDetailsCommon(priceData, {
        printPriceField: 'base_price',
        supplyPriceField: null, // 계산으로 처리
        vatPriceField: 'total_with_vat'
    });
}

// 스티커용 가격 표시 함수  
function updateStickerPriceDetails(priceData, editFee = 0) {
    updatePriceDetailsCommon(priceData, {
        showEditFee: editFee > 0,
        editFeeAmount: editFee,
        printPriceField: 'base_price',
        supplyPriceField: 'price',
        vatPriceField: 'price_vat'
    });
}

// 다른 품목용 가격 표시 함수
function updateStandardPriceDetails(priceData) {
    updatePriceDetailsCommon(priceData, {
        printPriceField: 'base_price',
        supplyPriceField: 'total_price',
        vatPriceField: 'total_with_vat'
    });
}