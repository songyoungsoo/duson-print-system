
document.addEventListener('DOMContentLoaded', function() {
    console.log('리플렛 계산기 스크립트 초기화');

    // DOM 요소 캐싱
    const form = document.getElementById('orderForm');
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');

    // 옵션 선택 요소
    const myType = document.getElementById('MY_type');
    const myFsd = document.getElementById('MY_Fsd');
    const pnType = document.getElementById('PN_type');
    const poType = document.getElementById('POtype');
    const myAmount = document.getElementById('MY_amount');
    const orderType = document.getElementById('ordertype');

    // 추가 옵션 요소
    const coatingEnabled = document.getElementById('coating_enabled');
    const foldingEnabled = document.getElementById('folding_enabled');
    const creasingEnabled = document.getElementById('creasing_enabled');

    const coatingOptionsDiv = document.getElementById('coating_options');
    const foldingOptionsDiv = document.getElementById('folding_options');
    const creasingOptionsDiv = document.getElementById('creasing_options');

    const coatingTypeSelect = document.getElementById('coating_type');
    const foldingTypeSelect = document.getElementById('folding_type');
    const creasingTypeSelect = document.getElementById('creasing_lines'); // ID가 creasing_lines 임

    const premiumPriceTotal = document.getElementById('premiumPriceTotal');

    // =================================================================
    // 이벤트 리스너 바인딩
    // =================================================================

    // 기본 옵션 변경 시
    [myType, myFsd, pnType, poType, orderType].forEach(el => {
        if (el) el.addEventListener('change', () => {
            if (el === myType || el === myFsd || el === pnType) {
                loadQuantityOptions(); // 규격, 종류, 색상 변경 시 수량 다시 로드
            } else {
                calculatePriceAjax();
            }
        });
    });
    
    myAmount.addEventListener('change', calculatePriceAjax);


    // 추가 옵션 활성화/비활성화 시
    [coatingEnabled, foldingEnabled, creasingEnabled].forEach(el => {
        if (el) el.addEventListener('change', toggleAdditionalOptions);
    });
    
    // 추가 옵션 상세 선택 시 가격 재계산
    [coatingTypeSelect, foldingTypeSelect, creasingTypeSelect].forEach(el => {
        if (el) el.addEventListener('change', calculatePriceAjax);
    });

    // =================================================================
    // 초기화 함수
    // =================================================================

    // 추가 옵션(코팅, 접지, 오시) 로드
    function loadAdditionalOptions() {
        console.log('추가 옵션 로드 시작');
        // 코팅 옵션 로드
        fetch('get_coating_types.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateSelect(coatingTypeSelect, data.data);
                }
            })
            .catch(error => console.error('코팅 옵션 로드 오류:', error));

        // 접지 옵션 로드
        fetch('get_fold_types.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateSelect(foldingTypeSelect, data.data);
                }
            })
            .catch(error => console.error('접지 옵션 로드 오류:', error));

        // 오시 옵션 로드
        fetch('get_creasing_types.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateSelect(creasingTypeSelect, data.data);
                }
            })
            .catch(error => console.error('오시 옵션 로드 오류:', error));
    }

    // Select 엘리먼트 옵션 채우기
    function populateSelect(selectElement, options) {
        if (!selectElement) return;
        selectElement.innerHTML = '<option value="">선택하세요</option>';
        options.forEach(option => {
            const optionElement = new Option(option.label, option.value);
            optionElement.dataset.price = option.price;
            selectElement.add(optionElement);
        });
    }
    
    // 추가 옵션 UI 토글
    function toggleAdditionalOptions() {
        coatingOptionsDiv.style.display = coatingEnabled.checked ? 'block' : 'none';
        foldingOptionsDiv.style.display = foldingEnabled.checked ? 'block' : 'none';
        creasingOptionsDiv.style.display = creasingEnabled.checked ? 'block' : 'none';
        
        if (!coatingEnabled.checked) coatingTypeSelect.value = '';
        if (!foldingEnabled.checked) foldingTypeSelect.value = '';
        if (!creasingEnabled.checked) creasingTypeSelect.value = '';

        calculatePriceAjax();
    }

    // =================================================================
    // 가격 계산 함수
    // =================================================================

    function calculatePriceAjax() {
        console.log("AJAX 가격 계산 시작");

        if (!myType.value || !pnType.value || !myFsd.value || !myAmount.value) {
            console.log("필수 옵션 미선택, 계산 중단");
            priceAmount.textContent = '견적 계산 필요';
            priceDetails.innerHTML = createPriceDetailHtml();
            return;
        }

        let params = new URLSearchParams({
            MY_type: myType.value,
            PN_type: pnType.value,
            MY_Fsd: myFsd.value,
            MY_amount: myAmount.value,
            ordertype: orderType.value,
            POtype: poType.value
        });

        if (coatingEnabled.checked && coatingTypeSelect.value) {
            params.append('coating_type', coatingTypeSelect.value);
        }
        if (foldingEnabled.checked && foldingTypeSelect.value) {
            params.append('fold_type', foldingTypeSelect.value);
        }
        if (creasingEnabled.checked && creasingTypeSelect.value) {
            params.append('creasing_type', creasingTypeSelect.value);
        }

        console.log("요청 파라미터:", params.toString());
        priceAmount.innerHTML = '계산 중...';

        fetch(`calculate_price_ajax.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                console.log("가격 계산 응답:", data);
                if (data.success) {
                    updatePriceUI(data.data);
                    window.currentPriceData = data.data;
                } else {
                    alert(data.error.message || '가격 정보를 가져올 수 없습니다.');
                    priceAmount.textContent = '계산 오류';
                    priceDetails.innerHTML = createPriceDetailHtml();
                    window.currentPriceData = null;
                }
            })
            .catch(error => {
                console.error('가격 계산 fetch 오류:', error);
                priceAmount.textContent = '통신 오류';
                priceDetails.innerHTML = createPriceDetailHtml();
                window.currentPriceData = null;
            });
    }

    function updatePriceUI(data) {
        const total = data.Total_PriceForm || 0;
        priceAmount.innerHTML = `${total.toLocaleString()}원 <span class="vat-info">(VAT 포함)</span>`;
        priceDetails.innerHTML = createPriceDetailHtml(data);
        
        const additionalTotal = (data.Coating_PriceForm || 0) + (data.Fold_PriceForm || 0) + (data.Creasing_PriceForm || 0);
        premiumPriceTotal.textContent = `(+${additionalTotal.toLocaleString()}원)`;
    }

    function createPriceDetailHtml(data = {}) {
        const price = data.Price || '0';
        const dsPrice = data.DS_Price || '0';
        const foldPrice = data.Fold_PriceForm ? `
            <div class="price-divider"></div>
            <div class="price-item">
                <span class="price-item-label">접지:</span>
                <span class="price-item-value">${data.Fold_Price}원</span>
            </div>` : '';
        const coatingPrice = data.Coating_PriceForm ? `
            <div class="price-divider"></div>
            <div class="price-item">
                <span class="price-item-label">코팅:</span>
                <span class="price-item-value">${data.Coating_Price}원</span>
            </div>` : '';
        const creasingPrice = data.Creasing_PriceForm ? `
            <div class="price-divider"></div>
            <div class="price-item">
                <span class="price-item-label">오시:</span>
                <span class="price-item-value">${data.Creasing_Price}원</span>
            </div>` : '';
        const orderPrice = data.Order_Price || '0';
        const totalPrice = data.Total_PriceForm ? data.Total_PriceForm.toLocaleString() : '0';

        return `
            <div class="price-breakdown">
                <div class="price-item">
                    <span class="price-item-label">인쇄비:</span>
                    <span class="price-item-value">${price}원</span>
                </div>
                <div class="price-divider"></div>
                <div class="price-item">
                    <span class="price-item-label">디자인비:</span>
                    <span class="price-item-value">${dsPrice}원</span>
                </div>
                ${coatingPrice}
                ${foldPrice}
                ${creasingPrice}
                <div class="price-divider-bold"></div>
                <div class="price-item final">
                    <span class="price-item-label">최종 합계 (VAT 포함):</span>
                    <span class="price-item-value">${totalPrice}원</span>
                </div>
            </div>
        `;
    }
    
    function loadQuantityOptions() {
        if (!myType.value || !pnType.value || !myFsd.value) {
            myAmount.innerHTML = '<option value="">규격/종류를 먼저 선택하세요</option>';
            return;
        }
        const params = new URLSearchParams({
            MY_type: myType.value,
            PN_type: pnType.value,
            MY_Fsd: myFsd.value,
        });

        fetch(`get_quantity_options.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    myAmount.innerHTML = '<option value="">수량을 선택하세요</option>';
                    data.data.forEach(q => {
                        const label = q.unit ? `${q.quantity} (${q.unit}장)` : q.quantity;
                        const option = new Option(label, q.quantity);
                        myAmount.add(option);
                    });
                } else {
                     myAmount.innerHTML = '<option value="">수량 정보 없음</option>';
                }
                calculatePriceAjax();
            });
    }

    // =================================================================
    // 초기 실행
    // =================================================================
    loadAdditionalOptions();
    toggleAdditionalOptions();
    loadQuantityOptions();

    // 견적서 모달 호환성: autoCalculatePrice alias (함수명이 다름 주의!)
    window.autoCalculatePrice = calculatePriceAjax;
});
