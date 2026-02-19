/**
 * 카다록/리플렛 견적안내 컴팩트 시스템 - 고급 갤러리 및 실시간 계산기
 * PROJECT_SUCCESS_REPORT.md 스펙에 따른 완전 재구축
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 */

// 전역 변수들
let currentPriceData = null;
// 파일 업로드 — 공통 upload_modal.js 사용 (window.uploadedFiles, window.selectedUploadMethod)

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
    initializeGallery();
    initializeCalculator();
    initializeFileUpload();
    
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
    const galleryContainer = document.getElementById('cadarokGallery');
    if (!galleryContainer) return;
    
    // GalleryLightbox 클래스 사용
    if (typeof GalleryLightbox !== 'undefined') {
        // 고급 갤러리 라이트박스 시스템 초기화
        const gallery = new GalleryLightbox('cadarokGallery', {
            dataSource: '/mlangprintauto/cadarok/get_cadarok_images.php',
            productType: 'cadarok',
            autoLoad: true,
            zoomEnabled: true,
            animationSpeed: 0.15
        });
        
        gallery.init();
        
        // GalleryLightbox 초기화 완료 후 더보기 버튼 확인
        setTimeout(() => {
            checkMoreButtonForLightbox();
        }, 1000);
        
        console.log('GalleryLightbox 시스템으로 카다록 갤러리 초기화 완료');
    } else {
        // 폴백: 기본 갤러리 시스템
        loadCadarokImages();
    }
}

function loadCadarokImages() {
    const galleryContainer = document.getElementById('cadarokGallery');
    if (!galleryContainer) return;
    
    galleryContainer.innerHTML = '<div class="loading">🖼️ 갤러리 로딩 중...</div>';
    
    fetch('/mlangprintauto/cadarok/get_cadarok_images.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success && data.data && data.data.length > 0) {
                    renderGallery(data.data, galleryContainer);
                } else {
                    const errorMsg = data.message || '갤러리 이미지를 불러올 수 없습니다.';
                    galleryContainer.innerHTML = `<div class="error">${errorMsg}</div>`;
                }
            } catch (jsonError) {
                console.error('JSON 파싱 오류:', jsonError);
                console.error('응답 내용:', text);
                galleryContainer.innerHTML = '<div class="error">갤러리 데이터 형식 오류</div>';
            }
        })
        .catch(error => {
            console.error('갤러리 로딩 오류:', error);
            galleryContainer.innerHTML = '<div class="error">갤러리 로딩 중 네트워크 오류가 발생했습니다.</div>';
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
        thumbnail.alt = image.title || `카다록 샘플 ${index + 1}`;
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
            targetSize = 200; // 작은 이미지는 2배 확대
        } else {
            targetSize = 200; // 큰 이미지는 2배 확대
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
    fetch('/mlangprintauto/cadarok/get_cadarok_images.php?all=true')
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

    // 드롭다운 변경 이벤트 리스너
    typeSelect.addEventListener('change', function() {
        const style = this.value;
        resetSelectWithText(paperSelect, '명함 재질을 선택해주세요');
        resetSelectWithText(quantitySelect, '수량을 선택해주세요');
        resetPrice();

        if (style) {
            loadPaperTypes(style);
        }
    });

    if (paperSelect) {
        paperSelect.addEventListener('change', loadQuantities);
    }
    if (sideSelect) {
        sideSelect.addEventListener('change', loadQuantities);
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
    if (priceDetails) priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
    if (priceDisplay) priceDisplay.classList.remove('calculated');
    if (uploadOrderButton) uploadOrderButton.style.display = 'none';
    
    currentPriceData = null;
}

function loadPaperTypes(style) {
    if (!style) return;

    fetch(`/mlangprintauto/cadarok/get_paper_types.php?style=${style}`)
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

function loadQuantities() {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');

    if (!typeSelect || !paperSelect || !sideSelect || !quantitySelect) return;

    const style = typeSelect.value;
    const section = paperSelect.value;
    const potype = sideSelect.value;

    resetSelectWithText(quantitySelect, '수량을 선택해주세요');
    resetPrice();

    if (!style || !section || !potype) return;

    fetch(`/mlangprintauto/cadarok/get_quantities.php?style=${style}&section=${section}&potype=${potype}`)
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
    const form = document.getElementById('cadarokForm');
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

// 가격 계산 함수 (강화된 에러 처리)
function calculatePrice(isAuto = true) {
    const form = document.getElementById('cadarokForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    if (!formData.get('MY_type') || !formData.get('Section') || 
        !formData.get('POtype') || !formData.get('MY_amount') || 
        !formData.get('ordertype')) {
        return;
    }
    
    const params = new URLSearchParams(formData);
    
    fetch('/mlangprintauto/cadarok/calculate_price_ajax.php?' + params.toString())
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(response => {
        if (response.success) {
            const priceData = response.data;
            currentPriceData = priceData;
            window.currentPriceData = priceData;  // ✅ 견적서 모달에서 접근 가능하도록

            // 서버에서 이미 추가 옵션이 포함된 가격을 반환하므로 여기서 다시 더하지 않음
            console.log('가격 계산 완료:', {
                base_price: priceData.base_price,
                design_price: priceData.design_price,
                additional_options: priceData.additional_options_total,
                total_price: priceData.total_price,
                total_with_vat: priceData.total_with_vat
            });

            // 가격 표시 업데이트
            updatePriceDisplay(priceData);

            // 플로팅 견적서 UI 업데이트
            if (typeof updateQfPricing === 'function') {
                updateQfPricing();
            }

        } else {
            resetPrice();
            if (!isAuto) {
                showUserMessage('가격 계산 실패: ' + (response.message || '알 수 없는 오류'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('가격 계산 오류:', error);
        if (!isAuto) {
            showUserMessage('가격 계산 중 오류가 발생했습니다.', 'error');
        }
    });
}

function updatePriceDisplay(priceData) {
    const priceDisplay = document.getElementById('priceDisplay');
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const uploadOrderButton = document.getElementById('uploadOrderButton');
    
    // 인쇄비 + 디자인비 합계를 큰 금액으로 표시 (VAT 제외)
    if (priceAmount) {
        const supplyPrice = priceData.total_price || (priceData.base_price + priceData.design_price);
        priceAmount.textContent = formatNumber(supplyPrice) + '원';
        console.log('💰 큰 금액 표시 (인쇄비+디자인비):', supplyPrice + '원');
    }
    
    if (priceDetails) {
        priceDetails.innerHTML = `
            <span>인쇄비: ${formatNumber(priceData.base_price)}원</span>
            <span>디자인비: ${formatNumber(priceData.design_price)}원</span>
            <span>부가세 포함: <span class="vat-amount">${formatNumber(Math.round(priceData.total_with_vat))}원</span></span>
        `;
    }
    
    if (priceDisplay) {
        priceDisplay.classList.add('calculated');
    }
    
    if (uploadOrderButton) {
        uploadOrderButton.style.display = 'block';
    }
}

// ============================================================================
// 파일 업로드 모달 시스템 (드래그 앤 드롭 및 강화된 에러 처리)
// ============================================================================

function initializeFileUpload() {}

// 카다록 전용 openUploadModal — 가격 검증 후 공통 모달 오픈
function openUploadModal() {
    if (!currentPriceData) {
        showUserMessage('먼저 가격을 계산해주세요.', 'warning');
        return;
    }
    if (typeof window._commonOpenUploadModal === 'function') {
        window._commonOpenUploadModal();
    }
}

// 카다록 전용 장바구니 추가 (카다록 add_to_basket.php 사용)
function addToBasketFromModal() {
    if (!currentPriceData) {
        showUserMessage('먼저 가격을 계산해주세요.', 'warning');
        return;
    }
    
    const cartButton = document.querySelector('.btn-cart');
    if (!cartButton) return;
    
    const originalText = cartButton.innerHTML;
    cartButton.innerHTML = '🔄 저장 중...';
    cartButton.disabled = true;
    cartButton.style.opacity = '0.7';
    
    const form = document.getElementById('cadarokForm');
    const workMemoElement = document.getElementById('modalWorkMemo');
    const workMemo = workMemoElement ? workMemoElement.value : '';
    
    if (!form) {
        restoreButton(cartButton, originalText);
        showUserMessage('양식을 찾을 수 없습니다.', 'error');
        return;
    }
    
    const formData = new FormData(form);
    
    formData.set('action', 'add_to_basket');
    formData.set('price', Math.round(currentPriceData.total_price));
    formData.set('vat_price', Math.round(currentPriceData.total_with_vat));
    formData.set('product_type', 'cadarok');
    
    formData.set('work_memo', workMemo);
    formData.set('upload_method', window.selectedUploadMethod || 'upload');

    const quantitySelect = document.getElementById('MY_amount');
    if (quantitySelect && quantitySelect.selectedIndex >= 0) {
        const selectedOption = quantitySelect.options[quantitySelect.selectedIndex];
        formData.set('quantity_display', selectedOption.text);
    }

    if (window.uploadedFiles && window.uploadedFiles.length > 0) {
        window.uploadedFiles.forEach((fileObj, index) => {
            formData.append(`uploaded_files[${index}]`, fileObj.file);
        });
        
        const fileInfoArray = window.uploadedFiles.map(fileObj => ({
            name: fileObj.name,
            size: fileObj.size,
            type: fileObj.type
        }));
        formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
    }
    
    fetch('/mlangprintauto/cadarok/add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
        return response.text();
    })
    .then(text => {
        try {
            const response = JSON.parse(text);
            if (response.success) {
                window.closeUploadModal();
                window.location.href = '/mlangprintauto/shop/cart.php';
            } else {
                restoreButton(cartButton, originalText);
                showUserMessage('장바구니 저장 중 오류가 발생했습니다: ' + response.message, 'error');
            }
        } catch (parseError) {
            restoreButton(cartButton, originalText);
            showUserMessage('서버 응답 처리 중 오류가 발생했습니다.', 'error');
        }
    })
    .catch(error => {
        restoreButton(cartButton, originalText);
        showUserMessage('장바구니 저장 중 네트워크 오류가 발생했습니다: ' + error.message, 'error');
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
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// 호환성을 위한 기본 함수들
function addToBasket() {
    openUploadModal();
}

function directOrder() {
    openUploadModal();
}