/**
 * 전단지 컴팩트 버전 JavaScript
 * NCR 성공 패턴 적용 - 안정성과 사용자 경험 최적화
 */

// 전역 변수
let currentPriceData = null;
let galleryImages = [];
let currentImageIndex = 0;

/**
 * 공통 모달 연동 함수 - 장바구니 추가 처리
 */
function handleModalBasketAdd(uploadedFiles, onSuccess, onError) {
    if (!currentPriceData) {
        alert('먼저 가격을 계산해주세요.');
        return;
    }
    
    // 로딩 상태 표시
    const cartButton = document.querySelector('.btn-cart');
    const originalText = cartButton.innerHTML;
    cartButton.innerHTML = '🔄 저장 중...';
    cartButton.disabled = true;
    cartButton.style.opacity = '0.7';
    
    const form = document.getElementById('orderForm');
    const workMemo = document.getElementById('modalWorkMemo').value;
    
    const formData = new FormData(form);
    
    // 기본 주문 정보
    formData.set('action', 'add_to_basket');
    formData.set('price', Math.round(currentPriceData.Order_PriceForm));
    formData.set('vat_price', Math.round(currentPriceData.Total_PriceForm));
    formData.set('product_type', 'leaflet');
    
    // 추가 정보
    formData.set('work_memo', workMemo);
    formData.set('upload_method', selectedUploadMethod || 'upload');
    
    // 추가 옵션 데이터 수집 (개선된 로직)
    console.log('🔧 추가 옵션 데이터 수집 시작');
    
    // 코팅 옵션
    const coatingEnabled = document.querySelector('input[name="coating_enabled"]:checked');
    const coatingEnabledValue = coatingEnabled ? coatingEnabled.value : '0';
    formData.set('coating_enabled', coatingEnabledValue);
    console.log('코팅 활성화:', coatingEnabledValue);
    
    if (coatingEnabledValue === '1') {
        const coatingType = document.querySelector('select[name="coating_type"]')?.value || '';
        const coatingPriceElement = document.getElementById('coating_price');
        const coatingPrice = coatingPriceElement ? coatingPriceElement.value : '0';
        formData.set('coating_type', coatingType);
        formData.set('coating_price', coatingPrice);
        console.log('코팅 타입:', coatingType, '가격:', coatingPrice);
    } else {
        formData.set('coating_type', '');
        formData.set('coating_price', '0');
    }

    // 접지 옵션
    const foldingEnabled = document.querySelector('input[name="folding_enabled"]:checked');
    const foldingEnabledValue = foldingEnabled ? foldingEnabled.value : '0';
    formData.set('folding_enabled', foldingEnabledValue);
    console.log('접지 활성화:', foldingEnabledValue);
    
    if (foldingEnabledValue === '1') {
        const foldingType = document.querySelector('select[name="folding_type"]')?.value || '';
        const foldingPriceElement = document.getElementById('folding_price');
        const foldingPrice = foldingPriceElement ? foldingPriceElement.value : '0';
        formData.set('folding_type', foldingType);
        formData.set('folding_price', foldingPrice);
        console.log('접지 타입:', foldingType, '가격:', foldingPrice);
    } else {
        formData.set('folding_type', '');
        formData.set('folding_price', '0');
    }

    // 오시 옵션
    const creasingEnabled = document.querySelector('input[name="creasing_enabled"]:checked');
    const creasingEnabledValue = creasingEnabled ? creasingEnabled.value : '0';
    formData.set('creasing_enabled', creasingEnabledValue);
    console.log('오시 활성화:', creasingEnabledValue);
    
    if (creasingEnabledValue === '1') {
        const creasingLines = document.querySelector('select[name="creasing_lines"]')?.value || '';
        const creasingPriceElement = document.getElementById('creasing_price');
        const creasingPrice = creasingPriceElement ? creasingPriceElement.value : '0';
        formData.set('creasing_lines', creasingLines);
        formData.set('creasing_price', creasingPrice);
        console.log('오시 줄 수:', creasingLines, '가격:', creasingPrice);
    } else {
        formData.set('creasing_lines', '');
        formData.set('creasing_price', '0');
    }

    // 추가 옵션 총합
    const additionalOptionsTotalElement = document.getElementById('additional_options_total');
    const additionalOptionsTotal = additionalOptionsTotalElement ? additionalOptionsTotalElement.value : '0';
    formData.set('additional_options_total', additionalOptionsTotal);
    console.log('추가 옵션 총합:', additionalOptionsTotal);
    
    // FormData 내용 디버깅 출력
    console.log('🔍 FormData에 추가된 옵션 데이터:');
    for (let pair of formData.entries()) {
        if (pair[0].includes('coating') || pair[0].includes('folding') || pair[0].includes('creasing') || pair[0].includes('additional')) {
            console.log(pair[0] + ':', pair[1]);
        }
    }
    
    // 업로드된 파일들 추가
    uploadedFiles.forEach((fileObj, index) => {
        formData.append('uploaded_files[]', fileObj.file);
    });
    
    // 파일 정보 JSON
    const fileInfoArray = uploadedFiles.map(fileObj => ({
        name: fileObj.name,
        size: fileObj.size,
        type: fileObj.type
    }));
    formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
    
    // 전송 전 최종 확인
    console.log('🚀 서버로 전송할 데이터:');
    console.log('- 기본가격:', Math.round(currentPriceData.Order_PriceForm));
    console.log('- VAT포함가격:', Math.round(currentPriceData.Total_PriceForm));
    console.log('- 작업메모:', workMemo);
    console.log('- 업로드파일수:', uploadedFiles.length);
    
    // 장바구니에 추가
    fetch('add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('전단지 장바구니 저장 성공');
            if (typeof closeUploadModal === 'function') {
                closeUploadModal();
            }
            // 성공 콜백 호출
            if (typeof onSuccess === 'function') {
                onSuccess();
            }
        } else {
            console.error('전단지 장바구니 저장 실패:', data.message);
            // 실패 콜백 호출
            if (typeof onError === 'function') {
                onError(data.message || '장바구니 저장에 실패했습니다.');
            } else {
                alert('❌ 장바구니 추가 실패: ' + (data.message || '알 수 없는 오류'));
            }
        }
    })
    .catch(error => {
        console.error('장바구니 추가 오류:', error);
        // 에러 콜백 호출
        if (typeof onError === 'function') {
            onError('네트워크 오류가 발생했습니다.');
        } else {
            alert('❌ 장바구니 추가 중 오류가 발생했습니다.');
        }
    });
}

// 갤러리 줌 기능 초기화 - 적응형 이미지 표시 및 확대
let targetX = 50, targetY = 50;
let currentX = 50, currentY = 50;
let targetSize = 100, currentSize = 100;
let currentImageDimensions = { width: 0, height: 0 };
let currentImageType = 'large'; // 'small' 또는 'large'
let originalBackgroundSize = 'contain'; // 원래 배경 크기 저장

// 숫자 포맷팅 함수
function formatNumber(number) {
    return new Intl.NumberFormat('ko-KR').format(number);
}

// 안전한 HTML 이스케이프
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================================================
// 페이지 초기화
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 전단지 컴팩트 페이지 초기화 시작');
    
    // 통합 갤러리 시스템이 이미지를 처리하므로 기존 갤러리 코드 비활성화
    // loadImageGallery();
    // initGalleryZoom();
    // animate();
    
    // 드롭다운 이벤트 리스너 추가
    initDropdownEvents();
    
    // 초기 옵션 로드
    const colorSelect = document.querySelector('select[name="MY_type"]');
    if (colorSelect && colorSelect.value) {
        loadPaperTypes(colorSelect.value);
        loadPaperSizes(colorSelect.value);
    }
    
    // 페이지 로드 후 기본값으로 수량 자동 로드 및 가격 계산
    setTimeout(() => {
        console.log('🔄 초기 기본값 설정 및 자동 계산 시작');
        
        // 종이종류를 첫 번째 옵션으로 자동 선택
        const paperTypeSelect = document.querySelector('select[name="MY_Fsd"]');
        if (paperTypeSelect && paperTypeSelect.options.length > 1) {
            paperTypeSelect.selectedIndex = 1; // 두 번째 옵션 (첫 번째는 "선택해주세요")
            console.log('📄 종이종류 자동 선택:', paperTypeSelect.value, paperTypeSelect.options[paperTypeSelect.selectedIndex].text);
        }
        
        // 수량 및 가격 자동 계산
        updateQuantities();
    }, 1000); // 다른 드롭다운들이 로드된 후 실행
    
    console.log('✅ 페이지 초기화 완료');
});

// ============================================================================
// 드롭다운 이벤트 초기화
// ============================================================================

function initDropdownEvents() {
    const colorSelect = document.querySelector('select[name="MY_type"]');
    const paperTypeSelect = document.querySelector('select[name="MY_Fsd"]');
    const paperSizeSelect = document.querySelector('select[name="PN_type"]');
    const sidesSelect = document.querySelector('select[name="POtype"]'); // 단면/양면 추가
    const quantitySelect = document.querySelector('select[name="MY_amount"]');
    const designSelect = document.querySelector('select[name="ordertype"]');
    
    // 인쇄색상 변경 시
    if (colorSelect) {
        colorSelect.addEventListener('change', function() {
            if (this.value) {
                loadPaperTypes(this.value);
                loadPaperSizes(this.value);
                resetDownstreamSelects(['MY_amount']);
                resetPriceDisplay();
            }
        });
    }
    
    // 종이종류 변경 시
    if (paperTypeSelect) {
        paperTypeSelect.addEventListener('change', function() {
            resetDownstreamSelects(['MY_amount']);
            resetPriceDisplay();
            updateQuantities();
        });
    }
    
    // 종이규격 변경 시
    if (paperSizeSelect) {
        paperSizeSelect.addEventListener('change', function() {
            resetDownstreamSelects(['MY_amount']);
            resetPriceDisplay();
            updateQuantities();
        });
    }
    
    // 인쇄면 변경 시 (단면/양면) - 수량 업데이트 필요
    if (sidesSelect) {
        sidesSelect.addEventListener('change', function() {
            console.log('💫 인쇄면 변경됨:', this.value);
            resetDownstreamSelects(['MY_amount']);
            resetPriceDisplay();
            updateQuantities(); // 단면/양면에 따라 수량 다시 로드
        });
    }
    
    // 수량 변경 시 자동 계산
    if (quantitySelect) {
        quantitySelect.addEventListener('change', function() {
            // 추가 옵션 수량 업데이트
            if (typeof updateAdditionalOptionsQuantity === 'function') {
                updateAdditionalOptionsQuantity(this.value);
            }
            
            // 자동 계산 (조건 확인 후 수행)
            const form = document.getElementById('orderForm');
            if (form) {
                const formData = new FormData(form);
                // 필수 필드가 모두 채워졌을 때만 자동 계산 실행
                if (formData.get('MY_type') && formData.get('MY_Fsd') && 
                    formData.get('PN_type') && formData.get('MY_amount') && 
                    formData.get('ordertype')) {
                    console.log('📊 수량 변경 → 자동 가격 계산 실행');
                    calculatePrice(true); // isAuto = true로 alert 방지
                } else {
                    console.log('📊 수량 변경 → 필수 필드 미완성으로 계산 대기');
                }
            }
        });
    }
    
    // 편집디자인 변경 시 자동 계산
    if (designSelect) {
        designSelect.addEventListener('change', autoCalculatePrice);
    }
}

// 하위 선택 박스 초기화
function resetDownstreamSelects(selectNames) {
    selectNames.forEach(name => {
        const select = document.querySelector(`select[name="${name}"]`);
        if (select) {
            select.innerHTML = '<option value="">수량을 선택해주세요</option>';
        }
    });
}

// 가격 표시 초기화
function resetPriceDisplay() {
    const priceDisplay = document.getElementById('priceDisplay');
    const priceAmount = document.getElementById('priceAmount');
    const priceVat = document.getElementById('priceVat');
    const selectedOptions = document.getElementById('selectedOptions');
    
    if (priceDisplay) priceDisplay.classList.remove('calculated');
    if (priceAmount) priceAmount.textContent = '0원';
    if (priceVat) priceVat.innerHTML = '옵션을 선택하시면<br>실시간으로 가격이 계산됩니다';
    if (selectedOptions) selectedOptions.style.display = 'none';
    
    currentPriceData = null;
}

// ============================================================================
// AJAX 옵션 로딩 함수들 (기존 전단지 패턴 사용)
// ============================================================================

function loadPaperTypes(colorNo) {
    console.log('📄 종이종류 옵션 로드 시작:', colorNo);
    
    fetch(`get_paper_types.php?CV_no=${colorNo}&page=inserted`)
        .then(response => response.json())
        .then(data => {
            console.log('📄 종이종류 응답:', data);
            
            const paperTypeSelect = document.querySelector('select[name="MY_Fsd"]');
            if (paperTypeSelect) {
                paperTypeSelect.innerHTML = '<option value="">종이종류를 선택해주세요</option>';
                
                data.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.no;
                    optionElement.textContent = option.title;
                    paperTypeSelect.appendChild(optionElement);
                });
                
                console.log('✅ 종이종류 옵션 로드 완료:', data.length, '개');
            }
        })
        .catch(error => {
            console.error('종이종류 로드 오류:', error);
        });
}

function loadPaperSizes(colorNo) {
    console.log('📏 종이규격 옵션 로드 시작:', colorNo);
    
    fetch(`get_paper_sizes.php?CV_no=${colorNo}&page=inserted`)
        .then(response => response.json())
        .then(data => {
            console.log('📏 종이규격 응답:', data);
            
            const paperSizeSelect = document.querySelector('select[name="PN_type"]');
            if (paperSizeSelect) {
                paperSizeSelect.innerHTML = '<option value="">종이규격을 선택해주세요</option>';
                
                data.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.no;
                    optionElement.textContent = option.title;
                    // A4 기본 선택
                    if (option.title && (option.title.includes('A4') && option.title.includes('210') && option.title.includes('297'))) {
                        optionElement.selected = true;
                    }
                    paperSizeSelect.appendChild(optionElement);
                });
                
                console.log('✅ 종이규격 옵션 로드 완료:', data.length, '개');
                
                // A4 선택 후 수량 업데이트
                updateQuantities();
            }
        })
        .catch(error => {
            console.error('종이규격 로드 오류:', error);
        });
}

function updateQuantities() {
    const colorSelect = document.querySelector('select[name="MY_type"]');
    const paperTypeSelect = document.querySelector('select[name="MY_Fsd"]');
    const paperSizeSelect = document.querySelector('select[name="PN_type"]');
    const sidesSelect = document.querySelector('select[name="POtype"]'); // 단면/양면 추가
    const quantitySelect = document.querySelector('select[name="MY_amount"]');
    
    const MY_type = colorSelect ? colorSelect.value : '';
    const MY_Fsd = paperTypeSelect ? paperTypeSelect.value : '';
    const PN_type = paperSizeSelect ? paperSizeSelect.value : '';
    const POtype = sidesSelect ? sidesSelect.value : '1'; // 단면/양면 값 추가
    
    if (!MY_type || !MY_Fsd || !PN_type) {
        return;
    }
    
    console.log('🔢 수량 옵션 로드 시작:', MY_type, MY_Fsd, PN_type, 'POtype:', POtype);
    
    // POtype 파라미터를 포함하여 API 호출
    fetch(`get_quantities.php?MY_type=${MY_type}&PN_type=${PN_type}&MY_Fsd=${MY_Fsd}&POtype=${POtype}`)
        .then(response => response.json())
        .then(data => {
            console.log('🔢 수량 응답 (POtype ' + POtype + '):', data);
            
            if (quantitySelect) {
                quantitySelect.innerHTML = '<option value="">수량을 선택해주세요</option>';
                
                if (data.length === 0) {
                    quantitySelect.innerHTML = '<option value="">수량 정보가 없습니다</option>';
                    return;
                }
                
                data.forEach((option, index) => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.text;
                    if (index === 0) optionElement.selected = true; // 첫 번째 옵션 자동 선택
                    quantitySelect.appendChild(optionElement);
                });
                
                console.log('✅ 수량 옵션 로드 완료 (POtype ' + POtype + '):', data.length, '개');
                
                // 첫 번째 수량이 자동 선택되면 가격 계산
                if (data.length > 0) {
                    setTimeout(() => autoCalculatePrice(), 100);
                }
            }
        })
        .catch(error => {
            console.error('수량 로드 오류:', error);
            if (quantitySelect) {
                quantitySelect.innerHTML = '<option value="">수량 로드 오류</option>';
            }
        });
}

// ============================================================================
// 실시간 가격 계산
// ============================================================================

function autoCalculatePrice() {
    const form = document.getElementById('orderForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    // 모든 필수 옵션 선택 확인 (디버깅 추가)
    const requiredFields = ['MY_type', 'MY_Fsd', 'PN_type', 'MY_amount', 'ordertype'];
    const missingFields = requiredFields.filter(field => !formData.get(field));
    
    if (missingFields.length > 0) {
        console.log('📋 자동 계산 대기 - 미완성 필드:', missingFields);
        return;
    }
    
    console.log('💰 자동 가격 계산 시작 (모든 필수 필드 완성)');
    calculatePrice(true);
}

function calculatePrice(isAuto = false) {
    console.log('💰 가격 계산 시작 (자동:', isAuto, ')');
    
    const form = document.getElementById('orderForm');
    if (!form) {
        console.error('❌ 폼을 찾을 수 없습니다');
        return;
    }
    
    const formData = new FormData(form);
    
    // 필수 필드 검증
    const requiredFields = ['MY_type', 'MY_Fsd', 'PN_type', 'MY_amount', 'ordertype'];
    const missingFields = [];
    
    requiredFields.forEach(field => {
        if (!formData.get(field)) {
            missingFields.push(field);
        }
    });
    
    if (missingFields.length > 0) {
        console.log('📋 미완성 필드:', missingFields, '| 자동모드:', isAuto);
        if (!isAuto) {
            console.warn('⚠️ ALERT 표시 예정: 모든 옵션을 선택해주세요');
            alert('모든 옵션을 선택해주세요.');
        } else {
            console.log('✅ 자동모드: alert 생략');
        }
        return;
    }
    
    // 버튼 로딩 상태 (수동 계산인 경우만)
    let button = null;
    let originalText = '';
    if (!isAuto && event && event.target) {
        button = event.target;
        originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
    }
    
    // 추가 옵션 가격 포함
    const additionalOptionsTotal = getAdditionalOptionsTotal ? getAdditionalOptionsTotal() : 0;
    console.log('🔧 추가 옵션 총액:', additionalOptionsTotal);
    
    // AJAX로 실제 가격 계산 (기존 전단지 패턴 사용)
    const params = new URLSearchParams({
        MY_type: formData.get('MY_type'),
        PN_type: formData.get('PN_type'),
        MY_Fsd: formData.get('MY_Fsd'),
        MY_amount: formData.get('MY_amount'),
        ordertype: formData.get('ordertype'),
        POtype: formData.get('POtype') || '1',
        additional_options_total: additionalOptionsTotal
    });
    
    fetch('calculate_price_ajax.php?' + params.toString())
    .then(response => response.json())
    .then(data => {
        console.log('💰 가격 계산 응답:', data);
        
        // 버튼 복원
        if (button) {
            button.innerHTML = originalText;
            button.disabled = false;
        }
        
        if (data.success) {
            currentPriceData = data.data;
            updatePriceDisplay(data.data);
            updateSelectedOptions();
        } else {
            console.error('가격 계산 실패:', data.error?.message);
            if (!isAuto) {
                alert('가격 계산 중 오류가 발생했습니다: ' + (data.error?.message || '알 수 없는 오류'));
            }
        }
    })
    .catch(error => {
        console.error('가격 계산 네트워크 오류:', error);
        
        // 버튼 복원
        if (button) {
            button.innerHTML = originalText;
            button.disabled = false;
        }
        
        if (!isAuto) {
            alert('가격 계산 중 네트워크 오류가 발생했습니다.');
        }
    });
}

function updatePriceDisplay(priceData) {
    const priceDisplay = document.getElementById('priceDisplay');
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const uploadButton = document.getElementById('uploadOrderButton');
    
    if (priceDisplay) {
        priceDisplay.classList.add('calculated');
    }
    
    // 추가 옵션 가격 가져오기
    let additionalOptionsPrice = 0;
    const additionalOptionsTotalInput = document.getElementById('additional_options_total');
    if (additionalOptionsTotalInput) {
        additionalOptionsPrice = parseInt(additionalOptionsTotalInput.value) || 0;
    }
    
    // 인쇄비 + 디자인비 + 추가옵션 합계를 큰 금액으로 표시 (VAT 제외)
    if (priceAmount) {
        const printCost = Math.round(priceData.PriceForm);         // 인쇄비만
        const designCost = Math.round(priceData.DS_PriceForm);     // 디자인비만
        const supplyPrice = printCost + designCost + additionalOptionsPrice;  // 공급가 + 옵션 (VAT 제외)
        
        priceAmount.textContent = supplyPrice.toLocaleString() + '원';
        console.log('💰 큰 금액 표시 (인쇄비+디자인비+옵션):', supplyPrice + '원');
        console.log('   - 인쇄비:', printCost + '원');
        console.log('   - 디자인비:', designCost + '원');
        console.log('   - 추가옵션:', additionalOptionsPrice + '원');
    }
    
    if (priceDetails) {
        const printCost = Math.round(priceData.PriceForm);         // 인쇄비만
        const designCost = Math.round(priceData.DS_PriceForm);     // 디자인비만
        const supplyPrice = printCost + designCost + additionalOptionsPrice;  // 공급가 + 옵션 (VAT 제외)
        const total = Math.round(supplyPrice * 1.1);  // 전체 공급가에 VAT 적용
        
        let optionHtml = '';
        if (additionalOptionsPrice > 0) {
            optionHtml = `
                <div class="price-divider"></div>
                <div class="price-item">
                    <span class="price-item-label">추가옵션:</span>
                    <span class="price-item-value">${additionalOptionsPrice.toLocaleString()}원</span>
                </div>`;
        }
        
        priceDetails.innerHTML = `
            <div class="price-breakdown">
                <div class="price-item">
                    <span class="price-item-label">인쇄비:</span>
                    <span class="price-item-value">${printCost.toLocaleString()}원</span>
                </div>
                <div class="price-divider"></div>
                <div class="price-item">
                    <span class="price-item-label">디자인비:</span>
                    <span class="price-item-value">${designCost.toLocaleString()}원</span>
                </div>
                ${optionHtml}
                <div class="price-divider"></div>
                <div class="price-item final">
                    <span class="price-item-label">부가세 포함:</span>
                    <span class="price-item-value">${total.toLocaleString()}원</span>
                </div>
            </div>
        `;
        console.log('💰 상세 가격 정보 표시:', {인쇄비: printCost, 디자인비: designCost, 추가옵션: additionalOptionsPrice, 공급가: supplyPrice, 부가세포함: total});
    }
    
    // VAT 포함 가격 표시 (추가 옵션 포함)
    const priceVat = document.getElementById('priceVat');
    if (priceVat) {
        const printCost = Math.round(priceData.PriceForm);
        const designCost = Math.round(priceData.DS_PriceForm);
        const totalSupply = printCost + designCost + additionalOptionsPrice;  // 전체 공급가
        const totalWithVat = Math.round(totalSupply * 1.1);  // VAT 적용
        priceVat.innerHTML = `<strong>부가세 포함: ${totalWithVat.toLocaleString()}원</strong>`;
        console.log('💰 부가세 포함 가격 계산:', {
            인쇄비: printCost,
            디자인비: designCost,
            추가옵션: additionalOptionsPrice,
            공급가합계: totalSupply,
            부가세포함: totalWithVat
        });
        
        // hidden input에 가격 정보 저장 (추가 옵션 포함)
        const priceInput = document.getElementById('calculated_price');
        const vatPriceInput = document.getElementById('calculated_vat_price');
        if (priceInput) {
            priceInput.value = totalSupply;  // 공급가 (인쇄비 + 디자인비 + 추가옵션)
            console.log('💰 Hidden input price 설정:', totalSupply);
        }
        if (vatPriceInput) {
            vatPriceInput.value = totalWithVat;  // VAT 포함
            console.log('💰 Hidden input vat_price 설정:', totalWithVat);
        }
    }
    
    // 파일 업로드 버튼 표시
    if (uploadButton) {
        uploadButton.style.display = 'block';
    }
    
    // 선택한 옵션 요약 표시
    const selectedOptions = document.getElementById('selectedOptions');
    if (selectedOptions) {
        selectedOptions.style.display = 'block';
    }
    
    console.log('✅ 가격 표시 업데이트 완료');
}

function updateSelectedOptions() {
    // 선택된 옵션들 표시 (기존 전단지 패턴과 동일)
    const form = document.getElementById('orderForm');
    if (!form) return;
    
    const selects = {
        'selectedColor': 'select[name="MY_type"]',
        'selectedPaperType': 'select[name="MY_Fsd"]', 
        'selectedPaperSize': 'select[name="PN_type"]',
        'selectedSides': 'select[name="POtype"]',
        'selectedQuantity': 'select[name="MY_amount"]',
        'selectedDesign': 'select[name="ordertype"]'
    };
    
    Object.keys(selects).forEach(id => {
        const element = document.getElementById(id);
        const select = form.querySelector(selects[id]);
        if (element && select) {
            element.textContent = select.options[select.selectedIndex]?.text || '-';
        }
    });
}

// ============================================================================
// 이미지 갤러리 시스템 (NCR 패턴 적용)
// ============================================================================

function loadImageGallery() {
    console.log('🖼️ 갤러리 이미지 로드 시작');
    
    // 먼저 실제 데이터를 시도하고, 실패하면 샘플 데이터 사용
    fetch('/api/get_real_orders_portfolio.php?category=inserted')
        .then(response => {
            console.log('🖼️ 갤러리 응답 상태:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('🖼️ 갤러리 원시 응답:', text);
            try {
                const response = JSON.parse(text);
                console.log('🖼️ 갤러리 파싱된 응답:', response);
                
                if (response.success && response.data && response.data.length > 0) {
                    galleryImages = response.data;
                    renderGallery();
                    hideGalleryLoading();
                    console.log('✅ 갤러리 로드 성공:', galleryImages.length, '개 이미지');
                } else {
                    console.warn('⚠️ 실제 이미지 없음, 샘플 데이터 로드 시도');
                    loadSampleGallery();
                }
            } catch (parseError) {
                console.error('JSON 파싱 오류:', parseError);
                console.error('원시 응답:', text);
                loadSampleGallery();
            }
        })
        .catch(error => {
            console.error('갤러리 로드 네트워크 오류:', error);
            loadSampleGallery();
        });
}

// 샘플 갤러리 로드 함수
function loadSampleGallery() {
    console.log('📋 샘플 갤러리 로드 시작');
    
    fetch('/api/get_leaflet_samples.php')
        .then(response => response.json())
        .then(response => {
            if (response.success && response.data && response.data.length > 0) {
                galleryImages = response.data;
                renderGallery();
                hideGalleryLoading();
                console.log('✅ 샘플 갤러리 로드 성공:', galleryImages.length, '개 샘플');
            } else {
                showGalleryError('갤러리를 불러올 수 없습니다.');
            }
        })
        .catch(error => {
            console.error('샘플 갤러리 로드 오류:', error);
            showGalleryError('갤러리를 불러올 수 없습니다.');
        });
}

function renderGallery() {
    const thumbnailStrip = document.getElementById('thumbnailStrip');
    if (!thumbnailStrip) return;
    
    thumbnailStrip.innerHTML = '';
    
    galleryImages.forEach((image, index) => {
        const thumbnail = document.createElement('img');
        // NCR 패턴과 동일한 경로 처리
        thumbnail.src = image.thumbnail_path || image.thumbnail || image.path || image.image_path;
        thumbnail.alt = image.title || `전단지 샘플 ${index + 1}`;
        thumbnail.className = 'thumbnail';
        thumbnail.dataset.index = index;
        
        // 이미지 로드 오류 처리
        thumbnail.onerror = function() {
            console.warn('썸네일 로드 실패:', this.src);
            // 플레이스홀더 이미지로 대체
            this.src = 'data:image/svg+xml;base64,' + btoa(`
                <svg width="80" height="80" xmlns="http://www.w3.org/2000/svg">
                    <rect width="80" height="80" fill="#f8f9fa" stroke="#dee2e6" stroke-width="1"/>
                    <text x="40" y="45" text-anchor="middle" font-family="Arial" font-size="20" fill="#6c757d">📄</text>
                </svg>
            `);
        };
        
        if (index === 0) {
            thumbnail.classList.add('active');
            setMainImage(image.image_path || image.path || image.url);
        }
        
        thumbnail.addEventListener('click', function() {
            selectImage(index);
        });
        
        thumbnailStrip.appendChild(thumbnail);
    });
    
    console.log('✅ 갤러리 렌더링 완료:', galleryImages.length, '개');
}

function selectImage(index) {
    if (index < 0 || index >= galleryImages.length) return;
    
    currentImageIndex = index;
    const image = galleryImages[index];
    
    // 썸네일 활성 상태 업데이트
    document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });
    
    // 메인 이미지 설정 (NCR 패턴과 동일한 경로 처리)
    const imagePath = image.image_path || image.path || image.url;
    setMainImage(imagePath);
}

function setMainImage(imagePath) {
    const zoomBox = document.getElementById('zoomBox');
    if (!zoomBox || !imagePath) return;
    
    // 이미지 로드 오류 처리
    const testImage = new Image();
    testImage.onload = function() {
        analyzeImageSize(imagePath, (backgroundSize) => {
            originalBackgroundSize = backgroundSize;
            zoomBox.style.backgroundImage = `url(${imagePath})`;
            zoomBox.style.backgroundSize = backgroundSize;
            zoomBox.style.backgroundPosition = '50% 50%';
            
            // 줌 상태 초기화
            currentX = targetX = 50;
            currentY = targetY = 50;
            currentSize = targetSize = 100;
        });
    };
    
    testImage.onerror = function() {
        console.warn('메인 이미지 로드 실패:', imagePath);
        // 플레이스홀더 이미지로 대체
        const placeholderSvg = 'data:image/svg+xml;base64,' + btoa(`
            <svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="300" fill="#f8f9fa" stroke="#dee2e6" stroke-width="2"/>
                <text x="200" y="140" text-anchor="middle" font-family="Arial" font-size="24" fill="#6c757d">📄</text>
                <text x="200" y="170" text-anchor="middle" font-family="Arial" font-size="16" fill="#6c757d">전단지 샘플</text>
                <text x="200" y="190" text-anchor="middle" font-family="Arial" font-size="14" fill="#6c757d">이미지 준비중</text>
            </svg>
        `);
        
        zoomBox.style.backgroundImage = `url(${placeholderSvg})`;
        zoomBox.style.backgroundSize = 'contain';
        zoomBox.style.backgroundPosition = '50% 50%';
        originalBackgroundSize = 'contain';
    };
    
    testImage.src = imagePath;
}

function analyzeImageSize(imagePath, callback) {
    const img = new Image();
    img.onload = function() {
        const containerHeight = 450;
        const containerWidth = document.getElementById('zoomBox').getBoundingClientRect().width;
        
        currentImageDimensions = {
            width: this.naturalWidth,
            height: this.naturalHeight
        };
        
        if (this.naturalHeight <= containerHeight && this.naturalWidth <= containerWidth) {
            // 1:1 크기 표시 (작은 이미지)
            const backgroundSize = `${this.naturalWidth}px ${this.naturalHeight}px`;
            currentImageType = 'small';
            callback(backgroundSize);
        } else {
            // contain 모드 (큰 이미지)
            currentImageType = 'large';
            callback('contain');
        }
    };
    img.src = imagePath;
}

function initGalleryZoom() {
    const zoomBox = document.getElementById('zoomBox');
    if (!zoomBox) return;
    
    zoomBox.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        targetX = Math.max(0, Math.min(100, x));
        targetY = Math.max(0, Math.min(100, y));
        
        // 확대 배율 설정
        if (currentImageType === 'small') {
            targetSize = 140; // 작은 이미지는 1.4배
        } else {
            targetSize = 160; // 큰 이미지는 1.6배
        }
    });
    
    zoomBox.addEventListener('mouseleave', function() {
        targetX = targetY = 50;
        targetSize = 100;
    });
}

// 부드러운 애니메이션 루프 (0.08 lerp)
function animate() {
    const zoomBox = document.getElementById('zoomBox');
    if (!zoomBox) {
        requestAnimationFrame(animate);
        return;
    }
    
    // 매우 부드러운 추적 (NCR 패턴: 0.08)
    currentX += (targetX - currentX) * 0.08;
    currentY += (targetY - currentY) * 0.08;
    currentSize += (targetSize - currentSize) * 0.08;
    
    zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
    zoomBox.style.backgroundSize = currentSize > 100.1 ? 
        `${currentSize}%` : originalBackgroundSize;
    
    requestAnimationFrame(animate);
}

function hideGalleryLoading() {
    const loading = document.getElementById('galleryLoading');
    if (loading) loading.style.display = 'none';
}

function showGalleryError(message = '이미지를 불러올 수 없습니다.') {
    const loading = document.getElementById('galleryLoading');
    const error = document.getElementById('galleryError');
    
    if (loading) loading.style.display = 'none';
    if (error) {
        error.style.display = 'block';
        error.innerHTML = `<p>${message}</p>`;
    }
    
    // 기본 플레이스홀더 이미지라도 표시
    const zoomBox = document.getElementById('zoomBox');
    if (zoomBox) {
        const placeholderSvg = 'data:image/svg+xml;base64,' + btoa(`
            <svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="300" fill="#f8f9fa" stroke="#dee2e6" stroke-width="2"/>
                <text x="200" y="130" text-anchor="middle" font-family="Arial" font-size="24" fill="#6c757d">📄</text>
                <text x="200" y="160" text-anchor="middle" font-family="Arial" font-size="16" fill="#6c757d">전단지 샘플</text>
                <text x="200" y="180" text-anchor="middle" font-family="Arial" font-size="14" fill="#6c757d">준비중입니다</text>
            </svg>
        `);
        
        zoomBox.style.backgroundImage = `url(${placeholderSvg})`;
        zoomBox.style.backgroundSize = 'contain';
        zoomBox.style.backgroundPosition = '50% 50%';
    }
}

// ============================================================================
// 장바구니 및 주문 기능 (기존 전단지 패턴 사용)
// ============================================================================

function addToBasket() {
    // 가격 계산이 먼저 되었는지 확인
    if (!currentPriceData) {
        alert('먼저 가격을 계산해주세요.');
        return;
    }
    
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    
    // 가격 정보 추가
    formData.set('action', 'add_to_basket');
    formData.set('price', Math.round(currentPriceData.Order_PriceForm));
    formData.set('vat_price', Math.round(currentPriceData.Total_PriceForm));
    formData.set('product_type', 'leaflet');
    
    // 📎 추가 옵션 데이터 명시적으로 추가
    console.log('📎 추가 옵션 데이터 수집 중...');
    
    // 코팅 옵션
    const coatingEnabled = document.querySelector('input[name="coating_enabled"]:checked');
    if (coatingEnabled && coatingEnabled.value === '1') {
        formData.set('coating_enabled', '1');
        const coatingType = document.querySelector('select[name="coating_type"]')?.value || '';
        const coatingPrice = document.querySelector('input[name="coating_price"]')?.value || '0';
        formData.set('coating_type', coatingType);
        formData.set('coating_price', coatingPrice);
        console.log('📎 코팅 옵션 추가:', coatingType, coatingPrice + '원');
    } else {
        formData.set('coating_enabled', '0');
        formData.set('coating_type', '');
        formData.set('coating_price', '0');
    }
    
    // 접기 옵션
    const foldingEnabled = document.querySelector('input[name="folding_enabled"]:checked');
    if (foldingEnabled && foldingEnabled.value === '1') {
        formData.set('folding_enabled', '1');
        const foldingType = document.querySelector('select[name="folding_type"]')?.value || '';
        const foldingPrice = document.querySelector('input[name="folding_price"]')?.value || '0';
        formData.set('folding_type', foldingType);
        formData.set('folding_price', foldingPrice);
        console.log('📎 접기 옵션 추가:', foldingType, foldingPrice + '원');
    } else {
        formData.set('folding_enabled', '0');
        formData.set('folding_type', '');
        formData.set('folding_price', '0');
    }
    
    // 크리징 옵션
    const creasingEnabled = document.querySelector('input[name="creasing_enabled"]:checked');
    if (creasingEnabled && creasingEnabled.value === '1') {
        formData.set('creasing_enabled', '1');
        const creasingLines = document.querySelector('select[name="creasing_lines"]')?.value || '';
        const creasingPrice = document.querySelector('input[name="creasing_price"]')?.value || '0';
        formData.set('creasing_lines', creasingLines);
        formData.set('creasing_price', creasingPrice);
        console.log('📎 크리징 옵션 추가:', creasingLines, creasingPrice + '원');
    } else {
        formData.set('creasing_enabled', '0');
        formData.set('creasing_lines', '');
        formData.set('creasing_price', '0');
    }
    
    // 추가 옵션 총액
    const additionalOptionsTotal = (parseInt(formData.get('coating_price') || '0') + 
                                  parseInt(formData.get('folding_price') || '0') + 
                                  parseInt(formData.get('creasing_price') || '0'));
    formData.set('additional_options_total', additionalOptionsTotal.toString());
    
    console.log('📎 총 추가 옵션 금액:', additionalOptionsTotal + '원');
    
    // 기존 방식도 유지 (호환성)
    if (typeof additionalOptionsManager !== 'undefined' && additionalOptionsManager) {
        const options = additionalOptionsManager.getCurrentOptions();
        for (const key in options) {
            if (!formData.has(key)) { // 이미 설정되지 않은 경우에만
                formData.set(key, options[key]);
            }
        }
    }
    
    // 로딩 표시
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '⏳ 추가중...';
    button.disabled = true;
    
    // AJAX로 장바구니에 추가
    fetch('add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        button.innerHTML = originalText;
        button.disabled = false;
        
        if (data.success) {
            alert('장바구니에 추가되었습니다! 🛒');
            
            // 장바구니 확인 여부 묻기
            if (confirm('장바구니를 확인하시겠습니까?')) {
                window.location.href = '/MlangPrintAuto/shop/cart.php';
            } else {
                // 폼 초기화하고 계속 쇼핑
                resetForm();
            }
        } else {
            alert('장바구니 추가 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        button.innerHTML = originalText;
        button.disabled = false;
        console.error('Error:', error);
        alert('장바구니 추가 중 오류가 발생했습니다.');
    });
}

function directOrder() {
    // 가격 계산이 먼저 되었는지 확인
    if (!currentPriceData) {
        alert('먼저 가격을 계산해주세요.');
        return;
    }
    
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    
    // 주문 정보를 URL 파라미터로 구성
    const params = new URLSearchParams();
    params.set('direct_order', '1');
    params.set('product_type', 'leaflet');
    params.set('MY_type', formData.get('MY_type'));
    params.set('MY_Fsd', formData.get('MY_Fsd'));
    params.set('PN_type', formData.get('PN_type'));
    params.set('POtype', formData.get('POtype'));
    params.set('MY_amount', formData.get('MY_amount'));
    params.set('ordertype', formData.get('ordertype'));
    params.set('price', Math.round(currentPriceData.Order_PriceForm));
    params.set('vat_price', Math.round(currentPriceData.Total_PriceForm));
    
    // 선택된 옵션 텍스트도 전달
    const selects = {
        'color_text': 'select[name="MY_type"]',
        'paper_type_text': 'select[name="MY_Fsd"]',
        'paper_size_text': 'select[name="PN_type"]',
        'sides_text': 'select[name="POtype"]',
        'quantity_text': 'select[name="MY_amount"]',
        'design_text': 'select[name="ordertype"]'
    };
    
    Object.keys(selects).forEach(param => {
        const select = document.querySelector(selects[param]);
        if (select) {
            params.set(param, select.options[select.selectedIndex].text);
        }
    });
    
    // 주문 페이지로 이동
    window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
}

function resetForm() {
    const form = document.getElementById('orderForm');
    if (form) form.reset();
    
    resetPriceDisplay();
    currentPriceData = null;
    
    // 첫 번째 옵션들로 다시 로드
    const colorSelect = document.querySelector('select[name="MY_type"]');
    if (colorSelect && colorSelect.value) {
        loadPaperTypes(colorSelect.value);
        loadPaperSizes(colorSelect.value);
        // 단면/양면 기본값으로 리셋 후 수량 로드
        setTimeout(() => {
            updateQuantities();
        }, 500);
    }
}

// ============================================================================
// 공통 모달 연동 시스템
// ============================================================================

/**
 * 공통 모달 업데이트 함수 - 가격 정보 전달
 */
function updateModalPrice() {
    const priceElement = document.getElementById('modalPriceAmount');
    if (priceElement && currentPriceData) {
        priceElement.textContent = formatNumber(Math.round(currentPriceData.Total_PriceForm)) + '원';
    }
}

/**
 * 로그인 상태 체크 (공통 모달용)
 */
function checkLoginStatus() {
    // 세션 체크 또는 로그인 상태 확인 로직
    return document.cookie.includes('PHPSESSID');
}

/**
 * 업로드 모달 열기 전 사전 체크
 */
function openUploadModal() {
    if (!currentPriceData) {
        alert('먼저 가격을 계산해주세요.');
        return;
    }
    
    // 공통 모달의 openUploadModal 호출
    if (typeof window.openUploadModal_Common === 'function') {
        window.openUploadModal_Common();
    } else {
        // 직접 모달 열기 (폴백)
        const modal = document.getElementById('uploadModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // 파일 업로드 초기화
            if (typeof initializeModalFileUpload === 'function') {
                initializeModalFileUpload();
            }
            
            updateModalPrice();
        }
    }
}

// 중복 함수 제거됨 - handleModalBasketAdd 사용

// ============================================================================
// 유틸리티 함수들
// ============================================================================

// 에러 처리 및 디버깅
window.addEventListener('error', function(e) {
    console.error('JavaScript 오류:', e.error);
});

console.log('✅ 전단지 컴팩트 JavaScript 로드 완료 (업로드 모달 포함)');