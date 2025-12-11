/**
 * 상품권/쿠폰 견적안내 컴팩트 시스템 - 고급 갤러리 및 실시간 계산기
 * NameCard 시스템 구조를 상품권에 적용
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 */

// 전역 변수들
let currentPriceData = null;
let uploadedFiles = [];
let selectedUploadMethod = 'upload';
let modalFileUploadInitialized = false; // 모달 파일 업로드 초기화 상태

// 갤러리 관련 변수들
let currentX = 50;
let currentY = 50;
let currentSize = 100;
let targetX = 50;
let targetY = 50; 
let targetSize = 100;
let originalBackgroundSize = 'contain';
let currentImageType = 'large'; // 'small' or 'large'
let animationId = null;

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // initializeGallery(); // 제거: 공통 갤러리 시스템 사용
    initializeCalculator();
    initializeFileUpload();

    // 🆕 프리미엄 옵션 이벤트 리스너 초기화
    initializePremiumOptionsListeners();

    // 기본값이 설정되어 있으면 자동으로 하위 옵션들 로드
    const typeSelect = document.getElementById('MY_type');
    if (typeSelect && typeSelect.value) {
        loadPaperTypes(typeSelect.value);
    }
});

// ============================================================================ 
// 고급 이미지 갤러리 시스템 (적응형 이미지 분석 및 부드러운 애니메이션)
// ============================================================================ 

function initializeGallery() {
    const galleryContainer = document.getElementById('merchandisebondGallery');
    if (!galleryContainer) return;
    
    // GalleryLightbox 클래스 사용
    if (typeof GalleryLightbox !== 'undefined') {
        // 고급 갤러리 라이트박스 시스템 초기화
        const gallery = new GalleryLightbox('merchandisebondGallery', {
            dataSource: 'get_merchandisebond_images.php',
            productType: 'merchandisebond',
            autoLoad: true,
            zoomEnabled: true,
            animationSpeed: 0.15
        });
        
        gallery.init();
        
        // GalleryLightbox 초기화 완료 후 더보기 버튼 확인
        setTimeout(() => {
            checkMoreButtonForLightbox();
        }, 1000);
        
        console.log('GalleryLightbox 시스템으로 상품권 갤러리 초기화 완료');
    } else {
        // 폴백: 기본 갤러리 시스템
        loadNamecardImages();
    }
}

function loadNamecardImages() {
    const galleryContainer = document.getElementById('merchandisebondGallery');
    if (!galleryContainer) return;
    
    galleryContainer.innerHTML = '<div class="loading">🖼️ 갤러리 로딩 중...</div>';
    
    fetch('get_merchandisebond_images.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                renderGallery(data.data, galleryContainer);
            } else {
                galleryContainer.innerHTML = '<div class="error">갤러리 이미지를 불러올 수 없습니다.</div>';
            }
        })
        .catch(error => {
            console.error('갤러리 로딩 오류:', error);
            galleryContainer.innerHTML = '<div class="error">갤러리 로딩 중 오류가 발생했습니다.</div>';
        });
}

function renderGallery(images, container) {
    const galleryHTML = `
        <div class="lightbox-viewer" id="zoomBox"></div>
        <div class="thumbnail-strip" id="thumbnailStrip"></div>
    `;
    
    container.innerHTML = galleryHTML;
    
    const zoomBox = document.getElementById('zoomBox');
    const thumbnailStrip = document.getElementById('thumbnailStrip');
    
    // 썸네일 생성
    images.forEach((image, index) => {
        const thumbnail = document.createElement('img');
        thumbnail.src = image.thumbnail || image.path;
        thumbnail.alt = image.title || `명함 샘플 ${index + 1}`;
        thumbnail.className = 'thumbnail';
        thumbnail.dataset.fullImage = image.path;
        
        if (index === 0) {
            thumbnail.classList.add('active');
            loadImageToZoomBox(image.path, zoomBox);
        }
        
        thumbnail.addEventListener('click', function() {
            // 활성 썸네일 변경
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // 메인 이미지 변경
            loadImageToZoomBox(this.dataset.fullImage, zoomBox);
        });
        
        thumbnailStrip.appendChild(thumbnail);
    });
    
    // 고급 확대 기능 초기화
    initializeAdvancedZoom(zoomBox);
    
    // 더보기 버튼 표시 확인 (4개 이상인 경우)
    checkMoreButtonVisibility(images.length);
}

function loadImageToZoomBox(imagePath, zoomBox) {
    // 이미지 크기 분석 및 적응형 표시
    analyzeImageSize(imagePath, function(backgroundSize) {
        zoomBox.style.backgroundImage = `url('${imagePath}')`;
        zoomBox.style.backgroundSize = backgroundSize;
        zoomBox.style.backgroundPosition = '50% 50%';
        
        // 초기값 리셋
        currentX = targetX = 50;
        currentY = targetY = 50;
        currentSize = targetSize = 100;
        originalBackgroundSize = backgroundSize;
    });
}

function analyzeImageSize(imagePath, callback) {
    const img = new Image();
    img.onload = function() {
        const containerHeight = 350;
        const containerWidth = document.getElementById('zoomBox').getBoundingClientRect().width;
        
        let backgroundSize;
        
        if (this.naturalHeight <= containerHeight && this.naturalWidth <= containerWidth) {
            // 1:1 크기 표시 (작은 이미지)
            backgroundSize = `${this.naturalWidth}px ${this.naturalHeight}px`;
            currentImageType = 'small';
        } else {
            // contain 모드 (큰 이미지)
            backgroundSize = 'contain';
            currentImageType = 'large';
        }
        
        callback(backgroundSize);
    };
    img.src = imagePath;
}

function initializeAdvancedZoom(zoomBox) {
    // 마우스 움직임 추적
    zoomBox.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        targetX = x;
        targetY = y;
        
        // 이미지 타입에 따른 확대 배율 조정
        if (currentImageType === 'small') {
            targetSize = 140; // 작은 이미지는 1.4배 확대
        } else {
            targetSize = 160; // 큰 이미지는 1.6배 확대
        }
    });
    
    zoomBox.addEventListener('mouseleave', function() {
        targetX = 50;
        targetY = 50;
        targetSize = 100;
    });
    
    // 부드러운 애니메이션 시작
    startSmoothAnimation();
}

function startSmoothAnimation() {
    if (animationId) {
        cancelAnimationFrame(animationId);
    }
    
    function animate() {
        const zoomBox = document.getElementById('zoomBox');
        if (!zoomBox) return;
        
        // 매우 부드러운 추적 (0.08 lerp 계수)
        currentX += (targetX - currentX) * 0.08;
        currentY += (targetY - currentY) * 0.08;
        currentSize += (targetSize - currentSize) * 0.08;
        
        zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
        
        if (currentSize > 100.1) {
            zoomBox.style.backgroundSize = `${currentSize}%`;
        } else {
            zoomBox.style.backgroundSize = originalBackgroundSize;
        }
        
        animationId = requestAnimationFrame(animate);
    }
    
    animate();
}

function checkMoreButtonVisibility(imageCount) {
    const moreButton = document.querySelector('.gallery-more-button');
    if (moreButton) {
        // 4개 이상의 이미지가 있는 경우 더보기 버튼 표시
        if (imageCount >= 4) {
            moreButton.style.display = 'block';
        } else {
            moreButton.style.display = 'none';
        }
    }
}

function checkMoreButtonForLightbox() {
    // GalleryLightbox 사용 시 더보기 버튼 표시 확인
    fetch('get_merchandisebond_images.php?all=true')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                checkMoreButtonVisibility(data.data.length);
            }
        })
        .catch(error => {
            console.error('더보기 버튼 확인 오류:', error);
        });
}

// ============================================================================ 
// 실시간 가격 계산 시스템 (동적 옵션 로딩 및 자동 계산)
// ============================================================================ 

function initializeCalculator() {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');
    const ordertypeSelect = document.getElementById('ordertype');

    if (!typeSelect) return;

    // 드롭다운 변경 이벤트 리스너 (새로운 순서: 종류 → 수량 → 인쇄면 → 후가공 → 편집비용)
    typeSelect.addEventListener('change', function() {
        const style = this.value;
        resetSelectWithText(quantitySelect, '수량을 선택해주세요');
        resetSelectWithText(paperSelect, '후가공을 선택해주세요');
        resetPrice();

        if (style) {
            // 종류 선택 시 수량과 후가공을 동시에 로드
            loadQuantities(style);
            loadPaperTypes(style);
        }
    });

    // 수량, 인쇄면, 후가공 변경 시 프리미엄 옵션 리셋 및 재계산
    if (quantitySelect) {
        quantitySelect.addEventListener('change', function() {
            console.log('💰 수량 변경:', this.value, '→ 프리미엄 옵션 리셋');

            // 1. 모든 프리미엄 옵션 체크박스 해제
            resetAllPremiumOptions();

            // 2. 프리미엄 옵션 가격 재계산 (리셋된 상태로)
            const premiumTotal = calculatePremiumOptions();
            updatePremiumPriceDisplay(premiumTotal);

            // 3. 메인 가격 계산도 다시 실행하여 전체 연동
            if (currentPriceData) {
                updatePriceDisplayWithPremium(currentPriceData);
            }
        });
    }
    if (sideSelect) {
        sideSelect.addEventListener('change', function() {
            console.log('인쇄면 변경:', this.value);
        });
    }
    if (paperSelect) {
        paperSelect.addEventListener('change', function() {
            console.log('후가공 변경:', this.value);
        });
    }
    
    // 모든 옵션 변경 시 자동 계산 (실시간)
    [typeSelect, paperSelect, sideSelect, quantitySelect, ordertypeSelect].forEach(select => {
        if (select) {
            select.addEventListener('change', autoCalculatePrice);
        }
    });
}

function resetSelectWithText(selectElement, defaultText) {
    if (selectElement) {
        selectElement.innerHTML = `<option value="">${defaultText}</option>`;
    }
}

function resetPrice() {
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const priceDisplay = document.getElementById('priceDisplay');
    const uploadOrderButton = document.getElementById('uploadOrderButton');
    
    if (priceAmount) priceAmount.textContent = '견적 계산 필요';
    if (priceDetails) priceDetails.innerHTML = '<span>모든 옵션을 선택하면 자동으로 계산됩니다</span>';
    if (priceDisplay) priceDisplay.classList.remove('calculated');
    if (uploadOrderButton) uploadOrderButton.style.display = 'none';
    
    currentPriceData = null;
}

function loadPaperTypes(style) {
    if (!style) return;

    fetch(`get_paper_types.php?style=${style}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const paperSelect = document.getElementById('Section');
                updateSelectWithOptions(paperSelect, data.data, '명함 재질을 선택해주세요');
                
                // 기본값이 있으면 자동 선택
                const defaultSection = paperSelect.dataset.defaultValue;
                if (defaultSection) {
                    paperSelect.value = defaultSection;
                    if (paperSelect.value) {
                        loadQuantities();
                    }
                }
            } else {
                showUserMessage('재질 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('재질 로드 오류:', error);
            showUserMessage('재질 로드 중 오류가 발생했습니다.', 'error');
        });
}

function loadQuantities(styleParam = null) {
    const typeSelect = document.getElementById('MY_type');
    const quantitySelect = document.getElementById('MY_amount');

    if (!typeSelect || !quantitySelect) return;

    const style = styleParam || typeSelect.value;

    resetSelectWithText(quantitySelect, '수량을 선택해주세요');
    resetPrice();

    if (!style) return;

    fetch(`get_quantities.php?style=${style}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                
                // 기본값이 있으면 자동 선택
                const defaultQuantity = quantitySelect.dataset.defaultValue;
                if (defaultQuantity) {
                    quantitySelect.value = defaultQuantity;
                    if (quantitySelect.value) {
                        autoCalculatePrice();
                    }
                }
            } else {
                showUserMessage('수량 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('수량 로드 오류:', error);
            showUserMessage('수량 로드 중 오류가 발생했습니다.', 'error');
        });
}

function updateSelectWithOptions(selectElement, options, defaultOptionText) {
    if (!selectElement) return; 
    
    selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
    if (options) {
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option.value || option.no;
            optionElement.textContent = option.text || option.title;
            selectElement.appendChild(optionElement);
        });
    }
}

// 자동 계산 (실시간)
function autoCalculatePrice() {
    const form = document.getElementById('merchandisebondForm');
    if (!form) return; 
    
    const formData = new FormData(form);
    
    // 모든 필수 옵션이 선택되었는지 확인
    if (!formData.get('MY_type') || !formData.get('Section') || 
        !formData.get('POtype') || !formData.get('MY_amount') || 
        !formData.get('ordertype')) {
        return; // 아직 모든 옵션이 선택되지 않음
    }
    
    // 실시간 계산 실행
    calculatePrice(true);
}

// 가격 계산 함수 (강화된 에러 처리 및 디버깅)
function calculatePrice(isAuto = true) {
    console.log('💰 calculatePrice 함수 호출됨');
    const form = document.getElementById('merchandisebondForm');
    if (!form) {
        console.log('❌ merchandisebondForm을 찾을 수 없습니다');
        return;
    }
    
    const formData = new FormData(form);
    
    // 필수 옵션 확인
    const required_fields = ['MY_type', 'Section', 'POtype', 'MY_amount', 'ordertype'];
    for (const field of required_fields) {
        if (!formData.get(field)) {
            console.log(`⚠️ 필수 필드 누락: ${field}. 가격 계산을 중단합니다.`);
            if (!isAuto) {
                showUserMessage(`'${field}' 옵션을 선택해야 가격 계산이 가능합니다.`, 'warning');
            }
            return;
        }
    }
    
    const params = new URLSearchParams(formData);
    const fetchUrl = 'calculate_price_ajax.php?' + params.toString();

    console.log('📡 [DEBUG] Fetching price from URL:', fetchUrl); // URL 로깅

    fetch(fetchUrl)
    .then(response => {
        console.log('📬 [DEBUG] Server response status:', response.status); // 상태 코드 로깅
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text(); // 항상 텍스트로 먼저 받기
    })
    .then(text => {
        console.log('📄 [DEBUG] Raw server response:', text); // 원본 응답 로깅
        try {
            const response = JSON.parse(text);
            if (response.success) {
                const priceData = response.data;
                currentPriceData = priceData;
                window.currentPriceData = priceData;  // 견적서 연동용 전역 변수 설정
                updatePriceDisplayWithPremium(priceData);

                // Directly show the apply button and hide the calculate button
                const applyBtn = document.getElementById('applyBtn');
                const calcBtn = document.getElementById('calculateBtn');
                if (applyBtn && calcBtn) {
                    calcBtn.style.display = 'none';
                    applyBtn.style.display = 'block';
                    console.log('✅ [DIRECT] 견적서 모드: 2단계 버튼 활성화됨');
                }

            } else {
                resetPrice();
                if (!isAuto) {
                    showUserMessage('가격 계산 실패: ' + (response.message || '알 수 없는 오류'), 'error');
                }
            }
        } catch (e) {
            console.error('JSON Parsing Error:', e);
            if (!isAuto) {
                 showUserMessage('서버 응답을 처리할 수 없습니다: ' + text.substring(0, 100), 'error');
            }
        }
    })
    .catch(error => {
        console.error('가격 계산 fetch 오류:', error);
        if (!isAuto) {
            showUserMessage('가격 계산 중 네트워크 오류가 발생했습니다.', 'error');
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
    
    // 인쇄비 + 디자인비 합계를 큰 금액으로 표시 (VAT 제외)
    if (priceAmount) {
        const printCost = Math.round(priceData.PriceForm);         // 인쇄비만
        const designCost = Math.round(priceData.DS_PriceForm);     // 디자인비만
        const supplyPrice = printCost + designCost;               // 공급가 (VAT 제외)
        
        priceAmount.textContent = supplyPrice.toLocaleString() + '원';
        console.log('💰 큰 금액 표시 (인쇄비+디자인비):', supplyPrice + '원');
    }
    
    if (priceDetails) {
        const printCost = Math.round(priceData.PriceForm);         // 인쇄비만
        const designCost = Math.round(priceData.DS_PriceForm);     // 디자인비만
        const supplyPrice = printCost + designCost;               // 공급가 (VAT 제외)
        const total = Math.round(priceData.Total_PriceForm);       // VAT 포함 총합계
        
        priceDetails.innerHTML = `
            <span>인쇄비: ${printCost.toLocaleString()}원</span>
            <span>디자인비: ${designCost.toLocaleString()}원</span>
            <span>부가세 포함: <span class="vat-amount">${total.toLocaleString()}원</span></span>
        `;
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

// 🆕 프리미엄 옵션 포함 가격 표시 업데이트
function updatePriceDisplayWithPremium(priceData) {
    // 기본 가격 표시
    updatePriceDisplay(priceData);

    // 프리미엄 옵션 가격 계산
    const premiumTotal = calculatePremiumOptions();

    // 프리미엄 옵션이 있으면 가격 재계산
    if (premiumTotal > 0) {
        const priceAmount = document.getElementById('priceAmount');
        const priceDetails = document.getElementById('priceDetails');

        if (priceAmount && priceDetails) {
            const printCost = Math.round(priceData.PriceForm);
            const designCost = Math.round(priceData.DS_PriceForm);
            const originalSupplyPrice = printCost + designCost;
            const newSupplyPrice = originalSupplyPrice + premiumTotal;
            const newTotal = Math.round(newSupplyPrice * 1.1); // VAT 포함

            // 메인 금액 업데이트
            priceAmount.textContent = newSupplyPrice.toLocaleString() + '원';

            // 상세 가격 업데이트 (프리미엄 옵션 포함)
            priceDetails.innerHTML = `
                <span>인쇄비: ${printCost.toLocaleString()}원</span>
                <span>디자인비: ${designCost.toLocaleString()}원</span>
                <span>프리미엄 옵션: ${premiumTotal.toLocaleString()}원</span>
                <span>부가세 포함: <span class="vat-amount">${newTotal.toLocaleString()}원</span></span>
            `;
        }
    }
}

// 🆕 프리미엄 옵션 가격 계산 (명함 방식)
function calculatePremiumOptions() {
    const quantity = parseInt(document.getElementById('MY_amount')?.value) || 500;
    let total = 0;

    console.log('🔧 프리미엄 옵션 계산 시작, 수량:', quantity);

    // 박 옵션 (500매 이하 30,000원, 초과시 매수×60원)
    const foilEnabled = document.getElementById('foil_enabled')?.checked;
    if (foilEnabled) {
        const price = calculateIndividualPrice('foil', quantity, 30000, 60);
        document.getElementById('foil_price').value = price;
        total += price;
        console.log('✨ 박 옵션 선택됨:', price + '원');
    } else {
        document.getElementById('foil_price').value = 0;
    }

    // 넘버링 옵션 (500매 이하 60,000원, 2개는 1000매당 15,000원 추가, 초과시 매수×120원)
    const numberingEnabled = document.getElementById('numbering_enabled')?.checked;
    if (numberingEnabled) {
        const type = document.getElementById('numbering_type')?.value || 'single';
        let basePrice = 60000;

        if (type === 'double') {
            const thousandUnits = Math.ceil(quantity / 1000);
            basePrice = 60000 + (thousandUnits * 15000);
        }

        const price = calculateIndividualPrice('numbering', quantity, basePrice, 120);
        document.getElementById('numbering_price').value = price;
        total += price;
        console.log('🔢 넘버링 옵션 선택됨:', price + '원');
    } else {
        document.getElementById('numbering_price').value = 0;
    }

    // 미싱 옵션 (가로/세로 20,000원, 십자 30,000원, 초과시 매수×40원/60원)
    if (document.getElementById('perforation_enabled')?.checked) {
        const type = document.getElementById('perforation_type')?.value || 'horizontal';
        let basePrice = 20000;
        let perUnitPrice = 40;

        if (type === 'cross') {
            basePrice = 30000;
            perUnitPrice = 60;
        }

        const price = calculateIndividualPrice('perforation', quantity, basePrice, perUnitPrice);
        document.getElementById('perforation_price').value = price;
        total += price;
    } else {
        document.getElementById('perforation_price').value = 0;
    }

    // 귀돌이 옵션 (네귀 15,000원, 두귀 12,000원, 초과시 매수×30원/25원)
    if (document.getElementById('rounding_enabled')?.checked) {
        const type = document.getElementById('rounding_type')?.value || '4corners';
        let basePrice = 15000;
        let perUnitPrice = 30;

        if (type === '2corners') {
            basePrice = 12000;
            perUnitPrice = 25;
        }

        const price = calculateIndividualPrice('rounding', quantity, basePrice, perUnitPrice);
        document.getElementById('rounding_price').value = price;
        total += price;
    } else {
        document.getElementById('rounding_price').value = 0;
    }

    // 오시 옵션 (1줄 18,000원, 2줄 25,000원, 초과시 매수×35원/50원)
    if (document.getElementById('creasing_enabled')?.checked) {
        const type = document.getElementById('creasing_type')?.value || 'single_crease';
        let basePrice = 18000;
        let perUnitPrice = 35;

        if (type === 'double_crease') {
            basePrice = 25000;
            perUnitPrice = 50;
        }

        const price = calculateIndividualPrice('creasing', quantity, basePrice, perUnitPrice);
        document.getElementById('creasing_price').value = price;
        total += price;
    } else {
        document.getElementById('creasing_price').value = 0;
    }

    // 총 프리미엄 옵션 가격 저장
    document.getElementById('premium_options_total').value = total;

    console.log('🎯 프리미엄 옵션 총액:', total + '원');

    // UI 업데이트
    updatePremiumPriceDisplay(total);

    return total;
}

// 개별 옵션 가격 계산 헬퍼
function calculateIndividualPrice(optionType, quantity, basePrice500, pricePerUnit) {
    if (quantity <= 500) {
        return basePrice500;
    } else {
        const additionalUnits = quantity - 500;
        return basePrice500 + (additionalUnits * pricePerUnit);
    }
}

// 프리미엄 옵션 가격 표시 업데이트
function updatePremiumPriceDisplay(total) {
    const premiumPriceElement = document.getElementById('premiumPriceTotal');
    if (premiumPriceElement) {
        if (total > 0) {
            premiumPriceElement.textContent = `(+${total.toLocaleString()}원)`;
            premiumPriceElement.style.color = '#d4af37';
        } else {
            premiumPriceElement.textContent = '(+0원)';
            premiumPriceElement.style.color = '#718096';
        }
    }
}
// ============================================================================ 
// 파일 업로드 모달 시스템 (드래그 앤 드롭 및 강화된 에러 처리)
// ============================================================================ 

function initializeFileUpload() {
    // 페이지 로드 시에는 모달 파일 업로드를 초기화하지 않음
    // 모달이 처음 열릴 때만 초기화
}

function openUploadModal() {
    if (!currentPriceData) {
        showUserMessage('먼저 가격을 계산해주세요.', 'warning');
        return;
    }
    
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // 파일 업로드 한 번만 초기화
        if (!modalFileUploadInitialized) {
            initializeModalFileUpload();
            modalFileUploadInitialized = true;
        }
    }
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // 업로드된 파일 초기화
        uploadedFiles = [];
        updateModalFileList();
        
        // 파일 입력 초기화
        const fileInput = document.getElementById('modalFileInput');
        if (fileInput) {
            fileInput.value = '';
        }
        
        const workMemo = document.getElementById('modalWorkMemo');
        if (workMemo) {
            workMemo.value = '';
        }
        
        console.log('모달 닫힘 - 모든 상태 초기화 완료');
    }
}

function initializeModalFileUpload() {
    const dropzone = document.getElementById('modalUploadDropzone');
    const fileInput = document.getElementById('modalFileInput');
    
    if (!dropzone || !fileInput) return;
    
    console.log('파일 업로드 모달 초기화 시작');
    
    // 드롭존 클릭 이벤트 - 한 번만 등록
    dropzone.addEventListener('click', function() {
        console.log('드롭존 클릭됨');
        fileInput.click();
    });
    
    // 파일 입력 변경 이벤트 - 한 번만 등록
    fileInput.addEventListener('change', function(e) {
        console.log('파일 선택됨:', e.target.files.length + '개');
        handleFileSelect(e);
    });
    
    // 드래그 앤 드롭 이벤트들
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });
    
    dropzone.addEventListener('dragleave', function() {
        dropzone.classList.remove('dragover');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        console.log('드롭된 파일:', files.length + '개');
        handleFiles(files);
    });
    
    console.log('파일 업로드 모달 초기화 완료');
}

function selectUploadMethod(method) {
    selectedUploadMethod = method;
    const buttons = document.querySelectorAll('.btn-upload-method');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // 클릭된 버튼에 active 클래스 추가
    const clickedButton = event.target;
    clickedButton.classList.add('active');
}

function handleFileSelect(e) {
    console.log('handleFileSelect 호출됨');
    const files = Array.from(e.target.files);
    console.log('선택된 파일 수:', files.length);
    
    // 파일 입력값 리셋하여 같은 파일 재선택 가능하게 함
    e.target.value = '';
    
    handleFiles(files);
}

function handleFiles(files) {
    const validTypes = ['.jpg', '.jpeg', '.png', '.pdf', '.ai', '.eps', '.psd'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    files.forEach(file => {
        const extension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(extension)) {
            showUserMessage(`지원하지 않는 파일 형식입니다: ${file.name}\n지원 형식: JPG, PNG, PDF, AI, EPS, PSD`, 'error');
            return;
        }
        
        if (file.size > maxSize) {
            showUserMessage(`파일 크기가 너무 큽니다: ${file.name}\n최대 10MB까지 업로드 가능합니다.`, 'error');
            return;
        }
        
        // 업로드된 파일 목록에 추가
        const fileObj = {
            id: Date.now() + Math.random(),
            file: file,
            name: file.name,
            size: formatFileSize(file.size),
            type: extension
        };
        
        uploadedFiles.push(fileObj);
        updateModalFileList();
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function updateModalFileList() {
    const uploadedFilesDiv = document.getElementById('modalUploadedFiles');
    const fileList = document.getElementById('modalFileList');
    
    if (!uploadedFilesDiv || !fileList) return;
    
    if (uploadedFiles.length === 0) {
        uploadedFilesDiv.style.display = 'none';
        return;
    }
    
    uploadedFilesDiv.style.display = 'block';
    fileList.innerHTML = '';
    
    uploadedFiles.forEach(fileObj => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <div class="file-info">
                <span class="file-icon">${getFileIcon(fileObj.type)}</span>
                <div class="file-details">
                    <div class="file-name">${fileObj.name}</div>
                    <div class="file-size">${fileObj.size}</div>
                </div>
            </div>
            <button class="file-remove" onclick="removeFile('${fileObj.id}')">삭제</button>
        `;
        fileList.appendChild(fileItem);
    });
}

function getFileIcon(extension) {
    switch(extension.toLowerCase()) {
        case '.jpg':
        case '.jpeg':
        case '.png': return '🖼️';
        case '.pdf': return '📄';
        case '.ai': return '🎨';
        case '.eps': return '🎨';
        case '.psd': return '🎨';
        default: return '📁';
    }
}

function removeFile(fileId) {
    uploadedFiles = uploadedFiles.filter(f => f.id != fileId);
    updateModalFileList();
}

// 상품권 전용 장바구니 추가 함수 (중복 방지를 위해 Direct 접미사 사용)
function addToBasketFromModalDirect(onSuccess, onError) {
    if (!currentPriceData) {
        showUserMessage('먼저 가격을 계산해주세요.', 'warning');
        return;
    }
    
    // 로딩 상태 표시
    const cartButton = document.querySelector('.btn-cart');
    if (!cartButton) return;
    
    const originalText = cartButton.innerHTML;
    cartButton.innerHTML = '🔄 저장 중...';
    cartButton.disabled = true;
    cartButton.style.opacity = '0.7';
    
    const form = document.getElementById('merchandisebondForm');
    const workMemoElement = document.getElementById('modalWorkMemo');
    const workMemo = workMemoElement ? workMemoElement.value : '';
    
    if (!form) {
        restoreButton(cartButton, originalText);
        showUserMessage('양식을 찾을 수 없습니다.', 'error');
        return;
    }
    
    const formData = new FormData(form);
    
    // 기본 주문 정보
    formData.set('action', 'add_to_basket');

    // 프리미엄 옵션 포함한 최종 가격 계산
    const printCost = Math.round(currentPriceData.PriceForm);
    const designCost = Math.round(currentPriceData.DS_PriceForm);
    const premiumTotal = parseInt(document.getElementById('premium_options_total')?.value || 0);
    const finalSupplyPrice = printCost + designCost + premiumTotal;
    const finalVatPrice = Math.round(finalSupplyPrice * 1.1);

    formData.set('price', finalSupplyPrice);
    formData.set('vat_price', finalVatPrice);
    formData.set('product_type', 'merchandisebond');
    
    // 추가 정보
    formData.set('work_memo', workMemo);
    formData.set('upload_method', selectedUploadMethod);

    // 프리미엄 옵션 데이터 추가
    const premiumOptionsData = {
        foil_enabled: document.getElementById('foil_enabled')?.checked || false,
        foil_type: document.getElementById('foil_type')?.value || '',
        foil_price: document.getElementById('foil_price')?.value || 0,
        numbering_enabled: document.getElementById('numbering_enabled')?.checked || false,
        numbering_type: document.getElementById('numbering_type')?.value || '',
        numbering_price: document.getElementById('numbering_price')?.value || 0,
        perforation_enabled: document.getElementById('perforation_enabled')?.checked || false,
        perforation_type: document.getElementById('perforation_type')?.value || '',
        perforation_price: document.getElementById('perforation_price')?.value || 0,
        rounding_enabled: document.getElementById('rounding_enabled')?.checked || false,
        rounding_type: document.getElementById('rounding_type')?.value || '',
        rounding_price: document.getElementById('rounding_price')?.value || 0,
        creasing_enabled: document.getElementById('creasing_enabled')?.checked || false,
        creasing_type: document.getElementById('creasing_type')?.value || '',
        creasing_price: document.getElementById('creasing_price')?.value || 0,
        premium_options_total: premiumTotal
    };

    // 프리미엄 옵션 개별 필드 추가 (PHP에서 $_POST로 접근 가능하도록)
    Object.keys(premiumOptionsData).forEach(key => {
        if (key.endsWith('_enabled')) {
            formData.set(key, premiumOptionsData[key] ? '1' : '0');
        } else {
            formData.set(key, premiumOptionsData[key]);
        }
    });

    // 업로드된 파일들 추가
    uploadedFiles.forEach((fileObj, index) => {
        formData.append(`uploaded_files[${index}]`, fileObj.file);
    });
    
    // 파일 정보 JSON
    const fileInfoArray = uploadedFiles.map(fileObj => ({
        name: fileObj.name,
        size: fileObj.size,
        type: fileObj.type
    }));
    formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
    
    fetch('add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text(); // 먼저 text로 받아서 확인
    })
    .then(text => {
        console.log('Raw response:', text);
        
        try {
            const response = JSON.parse(text);
            console.log('Parsed response:', response);
            
            if (response.success) {
                // 성공 콜백 호출
                if (typeof onSuccess === 'function') {
                    onSuccess();
                } else {
                    // 기본 성공 처리
                    closeUploadModal();
                    showUserMessage('장바구니에 저장되었습니다! 🛒', 'success');
                    setTimeout(() => {
                        window.location.href = '/mlangprintauto/shop/cart.php';
                    }, 1000);
                }

            } else {
                restoreButton(cartButton, originalText);
                const errorMsg = '장바구니 저장 중 오류가 발생했습니다: ' + response.message;

                // 실패 콜백 호출
                if (typeof onError === 'function') {
                    onError(errorMsg);
                } else {
                    showUserMessage(errorMsg, 'error');
                }
            }
        } catch (parseError) {
            restoreButton(cartButton, originalText);
            console.error('JSON Parse Error:', parseError);
            const parseErrorMsg = '서버 응답 처리 중 오류가 발생했습니다.';

            if (typeof onError === 'function') {
                onError(parseErrorMsg);
            } else {
                showUserMessage(parseErrorMsg, 'error');
            }
        }
    })
    .catch(error => {
        restoreButton(cartButton, originalText);
        console.error('Fetch Error:', error);
        const networkErrorMsg = '장바구니 저장 중 네트워크 오류가 발생했습니다: ' + error.message;

        if (typeof onError === 'function') {
            onError(networkErrorMsg);
        } else {
            showUserMessage(networkErrorMsg, 'error');
        }
    });
}

function restoreButton(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
    button.style.opacity = '1';
}

// ============================================================================ 
// 사용자 피드백 및 유틸리티 함수들
// ============================================================================ 

function showUserMessage(message, type = 'info') {
    // 토스트 메시지 구현 (간단한 alert 대신 사용)
    alert(message); // 향후 토스트 메시지로 교체 예정
}

function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// 호환성을 위한 기본 함수들
function addToBasket() {
    openUploadModal();
}

function directOrder() {
    openUploadModal();
}

// ============================================================================ 
// 상품권 프리미엄 옵션 관리 시스템
// ============================================================================ 

// 🆕 상품권 프리미엄 옵션 함수들 (명함 방식 적용)

// 프리미엄 옵션 가격 계산
function calculatePremiumOptions() {
    const quantityElement = document.getElementById('MY_amount');
    if (!quantityElement || !quantityElement.value) {
        console.log('⚠️ 수량이 선택되지 않음 - 프리미엄 옵션 계산 중단');
        return 0;
    }

    const quantity = parseInt(quantityElement.value) || 500;
    let total = 0;

    console.log('🔧 프리미엄 옵션 계산 시작, 수량:', quantity);

    // 박 옵션 (500매 이하 30,000원, 초과시 매수×60원)
    const foilEnabled = document.getElementById('foil_enabled')?.checked;
    if (foilEnabled) {
        const price = calculateIndividualPrice('foil', quantity, 30000, 60);
        document.getElementById('foil_price').value = price;
        total += price;
        console.log('✨ 박 옵션 선택됨:', price + '원');
    } else {
        document.getElementById('foil_price').value = 0;
        console.log('❌ 박 옵션 선택 안됨');
    }

    // 넘버링 옵션 (500매 이하 60,000원, 2개는 1000매당 15,000원 추가, 초과시 매수×120원)
    const numberingEnabled = document.getElementById('numbering_enabled')?.checked;
    if (numberingEnabled) {
        const type = document.getElementById('numbering_type')?.value || 'single';
        let basePrice = 60000;

        if (type === 'double') {
            // 2개인 경우: 기본 60,000원 + 1000매당 15,000원 추가
            const thousandUnits = Math.ceil(quantity / 1000);
            basePrice = 60000 + (thousandUnits * 15000);
        }

        const price = calculateIndividualPrice('numbering', quantity, basePrice, 120);
        document.getElementById('numbering_price').value = price;
        total += price;
        console.log('🔢 넘버링 옵션 선택됨:', price + '원');
    } else {
        document.getElementById('numbering_price').value = 0;
        console.log('❌ 넘버링 옵션 선택 안됨');
    }

    // 미싱 옵션 (가로/세로 20,000원, 십자 30,000원, 초과시 매수×40원/60원)
    if (document.getElementById('perforation_enabled')?.checked) {
        const type = document.getElementById('perforation_type')?.value || 'horizontal';
        let basePrice = 20000;
        let perUnitPrice = 40;

        if (type === 'cross') {
            basePrice = 30000;
            perUnitPrice = 60;
        }

        const price = calculateIndividualPrice('perforation', quantity, basePrice, perUnitPrice);
        document.getElementById('perforation_price').value = price;
        total += price;
    } else {
        document.getElementById('perforation_price').value = 0;
    }

    // 귀돌이 옵션 (네귀 15,000원, 두귀 12,000원, 초과시 매수×30원/25원)
    if (document.getElementById('rounding_enabled')?.checked) {
        const type = document.getElementById('rounding_type')?.value || '4corners';
        let basePrice = 15000;
        let perUnitPrice = 30;

        if (type === '2corners') {
            basePrice = 12000;
            perUnitPrice = 25;
        }

        const price = calculateIndividualPrice('rounding', quantity, basePrice, perUnitPrice);
        document.getElementById('rounding_price').value = price;
        total += price;
    } else {
        document.getElementById('rounding_price').value = 0;
    }

    // 오시 옵션 (1줄 18,000원, 2줄 25,000원, 초과시 매수×35원/50원)
    if (document.getElementById('creasing_enabled')?.checked) {
        const type = document.getElementById('creasing_type')?.value || 'single_crease';
        let basePrice = 18000;
        let perUnitPrice = 35;

        if (type === 'double_crease') {
            basePrice = 25000;
            perUnitPrice = 50;
        }

        const price = calculateIndividualPrice('creasing', quantity, basePrice, perUnitPrice);
        document.getElementById('creasing_price').value = price;
        total += price;
    } else {
        document.getElementById('creasing_price').value = 0;
    }

    // 총 프리미엄 옵션 가격 저장
    document.getElementById('premium_options_total').value = total;

    console.log('🎯 프리미엄 옵션 총액:', total + '원');

    // UI 업데이트
    updatePremiumPriceDisplay(total);

    return total;
}

// 개별 옵션 가격 계산 헬퍼
function calculateIndividualPrice(optionType, quantity, basePrice500, pricePerUnit) {
    if (quantity <= 500) {
        return basePrice500;
    } else {
        const additionalUnits = quantity - 500;
        return basePrice500 + (additionalUnits * pricePerUnit);
    }
}

// 프리미엄 옵션 가격 표시 업데이트
function updatePremiumPriceDisplay(total) {
    const premiumPriceElement = document.getElementById('premiumPriceTotal');
    if (premiumPriceElement) {
        if (total > 0) {
            premiumPriceElement.textContent = `(+${total.toLocaleString()}원)`;
            premiumPriceElement.style.color = '#d4af37';
        } else {
            premiumPriceElement.textContent = '(+0원)';
            premiumPriceElement.style.color = '#718096';
        }
    }
}

// 모든 프리미엄 옵션 리셋 함수
function resetAllPremiumOptions() {
    console.log('🔄 모든 프리미엄 옵션 리셋');

    // 모든 체크박스 해제
    const checkboxes = ['foil_enabled', 'numbering_enabled', 'perforation_enabled', 'rounding_enabled', 'creasing_enabled'];
    checkboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox) {
            checkbox.checked = false;
        }
    });

    // 모든 옵션 상세 영역 숨기기
    const optionDetails = ['foil_options', 'numbering_options', 'perforation_options', 'rounding_options', 'creasing_options'];
    optionDetails.forEach(id => {
        const detail = document.getElementById(id);
        if (detail) {
            detail.style.display = 'none';
        }
    });

    // 모든 드롭다운 초기화
    const selects = ['foil_type', 'numbering_type', 'perforation_type', 'rounding_type', 'creasing_type'];
    selects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.value = '';
        }
    });

    // 모든 가격 필드 초기화
    const priceFields = ['foil_price', 'numbering_price', 'perforation_price', 'rounding_price', 'creasing_price', 'premium_options_total'];
    priceFields.forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            field.value = '0';
        }
    });

    // 프리미엄 옵션 가격 표시 초기화
    const premiumPriceElement = document.getElementById('premiumPriceTotal');
    if (premiumPriceElement) {
        premiumPriceElement.textContent = '(+0원)';
        premiumPriceElement.style.color = '#718096';
    }
}

// 🆕 프리미엄 옵션 이벤트 리스너 초기화
function initializePremiumOptionsListeners() {
    console.log('프리미엄 옵션 이벤트 리스너 초기화');

    // 체크박스 토글 이벤트
    const toggles = document.querySelectorAll('.option-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function(e) {
            const optionType = e.target.id.replace('_enabled', '');
            const detailsDiv = document.getElementById(`${optionType}_options`);

            if (e.target.checked) {
                detailsDiv.style.display = 'block';
                console.log(`✅ ${optionType} 옵션 활성화`);
            } else {
                detailsDiv.style.display = 'none';
                // 가격 필드 초기화
                const priceField = document.getElementById(`${optionType}_price`);
                if (priceField) priceField.value = '0';
                console.log(`❌ ${optionType} 옵션 비활성화`);
            }

            // 가격 재계산
            calculatePrice();
        });
    });

    // 드롭다운 변경 이벤트
    const selects = document.querySelectorAll('.option-select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            console.log('프리미엄 옵션 선택 변경:', select.name, select.value);
            calculatePrice();
        });
    });
}