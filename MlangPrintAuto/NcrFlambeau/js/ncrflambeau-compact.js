/**
 * NcrFlambeau 컴팩트 버전 JavaScript
 * 명함 성공 패턴 적용 - 안정성과 사용자 경험 최적화
 */

// 전역 변수
let currentPriceData = null;
let galleryImages = [];
let currentImageIndex = 0;

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
    console.log('🚀 NcrFlambeau 컴팩트 페이지 초기화 시작');
    
    // 이미지 갤러리 초기화
    loadImageGallery();
    initGalleryZoom();
    animate();
    
    // 드롭다운 이벤트 리스너 추가
    initDropdownEvents();
    
    // 초기 옵션 로드
    const categorySelect = document.querySelector('select[name="MY_type"]');
    if (categorySelect && categorySelect.value) {
        console.log('🎯 페이지 로드 시 기본 카테고리:', categorySelect.value);
        loadSizes(categorySelect.value);
    } else if (categorySelect) {
        // 양식(100매철)이 기본 선택되도록 설정
        const defaultOption = categorySelect.querySelector('option[value="475"]');
        if (defaultOption) {
            categorySelect.value = '475';
            console.log('🎯 양식(100매철) 기본 선택 설정');
            loadSizes('475');
        }
    }
    
    // 페이지 로드 시 초기 가격 계산 (기본값으로)
    setTimeout(() => {
        console.log('💰 초기 가격 계산 시작 (기본값 적용)');
        calculateInitialPrice();
    }, 500); // DOM 로딩 완료 후 0.5초 대기
    
    console.log('✅ 페이지 초기화 완료');
});

// ============================================================================
// 드롭다운 이벤트 초기화
// ============================================================================

function initDropdownEvents() {
    const categorySelect = document.querySelector('select[name="MY_type"]');
    const sizeSelect = document.querySelector('select[name="MY_Fsd"]');
    const colorSelect = document.querySelector('select[name="PN_type"]');
    const quantitySelect = document.querySelector('select[name="MY_amount"]');
    const designSelect = document.querySelector('select[name="ordertype"]');
    
    // 구분 변경 시
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            if (this.value) {
                loadSizes(this.value);
                resetDownstreamSelects(['MY_Fsd', 'PN_type', 'MY_amount']);
                resetPriceDisplay();
            }
        });
    }
    
    // 규격 변경 시
    if (sizeSelect) {
        sizeSelect.addEventListener('change', function() {
            const categoryValue = categorySelect.value;
            if (categoryValue && this.value) {
                loadColors(categoryValue, this.value);
                resetDownstreamSelects(['PN_type', 'MY_amount']);
                resetPriceDisplay();
            }
        });
    }
    
    // 색상 변경 시
    if (colorSelect) {
        colorSelect.addEventListener('change', function() {
            const categoryValue = categorySelect.value;
            const sizeValue = sizeSelect.value;
            if (categoryValue && sizeValue && this.value) {
                loadQuantities(categoryValue, sizeValue, this.value);
                resetDownstreamSelects(['MY_amount']);
                resetPriceDisplay();
            }
        });
    }
    
    // 수량 또는 편집디자인 변경 시 자동 계산
    if (quantitySelect) {
        quantitySelect.addEventListener('change', autoCalculatePrice);
    }
    if (designSelect) {
        designSelect.addEventListener('change', autoCalculatePrice);
    }
}

// 하위 선택 박스 초기화
function resetDownstreamSelects(selectNames) {
    selectNames.forEach(name => {
        const select = document.querySelector(`select[name="${name}"]`);
        if (select) {
            select.innerHTML = '<option value="">선택해주세요</option>';
        }
    });
}

// 가격 표시 초기화
function resetPriceDisplay() {
    const priceDisplay = document.getElementById('priceDisplay');
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    
    if (priceDisplay) priceDisplay.classList.remove('calculated');
    if (priceAmount) priceAmount.textContent = '0원';
    if (priceDetails) priceDetails.innerHTML = '옵션을 선택하시면<br>실시간으로 가격이 계산됩니다';
    
    currentPriceData = null;
}

// ============================================================================
// AJAX 옵션 로딩 함수들
// ============================================================================

function loadSizes(categoryId) {
    console.log('📏 규격 옵션 로드 시작:', categoryId);
    
    fetch(`get_sizes.php?style=${categoryId}`)
        .then(response => response.json())
        .then(response => {
            console.log('📏 규격 응답:', response);
            
            if (!response.success || !response.data) {
                console.error('규격 로드 실패:', response.message);
                return;
            }
            
            const sizeSelect = document.querySelector('select[name="MY_Fsd"]');
            if (sizeSelect) {
                sizeSelect.innerHTML = '<option value="">규격을 선택해주세요</option>';
                
                response.data.forEach((option, index) => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.no || option.value;
                    optionElement.textContent = option.title || option.text;
                    
                    // 양식(100매철) 선택 시 첫 번째 옵션 자동 선택
                    if (categoryId === '475' && index === 0) {
                        optionElement.selected = true;
                        console.log('🎯 양식(100매철) 첫 번째 규격 자동 선택:', option.title);
                        
                        // 자동 선택 후 후속 옵션도 로드
                        setTimeout(() => {
                            loadColors(categoryId, optionElement.value);
                        }, 100);
                    }
                    
                    sizeSelect.appendChild(optionElement);
                });
                
                console.log('✅ 규격 옵션 로드 완료:', response.data.length, '개');
            }
        })
        .catch(error => {
            console.error('규격 로드 오류:', error);
        });
}

function loadColors(categoryId, sizeId) {
    console.log('🎨 색상 옵션 로드 시작:', categoryId, sizeId);
    
    fetch(`get_colors.php?style=${categoryId}&size=${sizeId}`)
        .then(response => response.json())
        .then(response => {
            console.log('🎨 색상 응답:', response);
            
            if (!response.success || !response.data) {
                console.error('색상 로드 실패:', response.message);
                return;
            }
            
            const colorSelect = document.querySelector('select[name="PN_type"]');
            if (colorSelect) {
                colorSelect.innerHTML = '<option value="">색상을 선택해주세요</option>';
                
                response.data.forEach((option, index) => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.no || option.value;
                    optionElement.textContent = option.title || option.text;
                    
                    // 첫 번째 색상 옵션 자동 선택
                    if (index === 0) {
                        optionElement.selected = true;
                        console.log('🎯 첫 번째 색상 자동 선택:', option.title);
                        
                        // 자동 선택 후 수량 옵션도 로드
                        setTimeout(() => {
                            loadQuantities(categoryId, sizeId, optionElement.value);
                        }, 100);
                    }
                    
                    colorSelect.appendChild(optionElement);
                });
                
                console.log('✅ 색상 옵션 로드 완료:', response.data.length, '개');
            }
        })
        .catch(error => {
            console.error('색상 로드 오류:', error);
        });
}

function loadQuantities(categoryId, sizeId, colorId) {
    console.log('🔢 수량 옵션 로드 시작:', categoryId, sizeId, colorId);
    
    fetch(`get_quantities.php?style=${categoryId}&section=${sizeId}&treeselect=${colorId}`)
        .then(response => response.json())
        .then(response => {
            console.log('🔢 수량 응답:', response);
            
            if (!response.success || !response.data) {
                console.error('수량 로드 실패:', response.message);
                return;
            }
            
            const quantitySelect = document.querySelector('select[name="MY_amount"]');
            if (quantitySelect) {
                quantitySelect.innerHTML = '<option value="">수량을 선택해주세요</option>';
                
                response.data.forEach((option, index) => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.text;
                    
                    // 첫 번째 수량 옵션 자동 선택
                    if (index === 0) {
                        optionElement.selected = true;
                        console.log('🎯 첫 번째 수량 자동 선택:', option.text);
                        
                        // 자동 선택 후 가격 계산
                        setTimeout(() => {
                            autoCalculatePrice();
                        }, 100);
                    }
                    
                    quantitySelect.appendChild(optionElement);
                });
                
                console.log('✅ 수량 옵션 로드 완료:', response.data.length, '개');
            }
        })
        .catch(error => {
            console.error('수량 로드 오류:', error);
        });
}

// ============================================================================
// 실시간 가격 계산
// ============================================================================

function autoCalculatePrice() {
    const form = document.getElementById('ncr-quote-form');
    const formData = new FormData(form);
    
    // 모든 필수 옵션 선택 확인
    if (!formData.get('MY_type') || !formData.get('MY_Fsd') || 
        !formData.get('PN_type') || !formData.get('MY_amount') || 
        !formData.get('ordertype')) {
        return;
    }
    
    console.log('💰 자동 가격 계산 시작');
    calculatePrice(true);
}

function calculatePrice(isAuto = false) {
    console.log('💰 가격 계산 시작 (자동:', isAuto, ')');
    
    const form = document.getElementById('ncr-quote-form');
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
        if (!isAuto) {
            alert('모든 옵션을 선택해주세요.\\n누락된 항목: ' + missingFields.join(', '));
        }
        return;
    }
    
    // 버튼 로딩 상태 (수동 계산인 경우만)
    let button = null;
    let originalText = '';
    if (!isAuto) {
        button = event.target;
        originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
    }
    
    // AJAX로 실제 가격 계산
    fetch('calculate_price_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('💰 가격 계산 응답 상태:', response.status);
        return response.json();
    })
    .then(response => {
        console.log('💰 가격 계산 응답:', response);
        
        // 버튼 복원
        if (button) {
            button.innerHTML = originalText;
            button.disabled = false;
        }
        
        if (response.success && response.data) {
            currentPriceData = response.data;
            updatePriceDisplay(response.data);
            updateHiddenPriceFields(response.data);
        } else {
            console.error('가격 계산 실패:', response.message);
            if (!isAuto) {
                alert('가격 계산 중 오류가 발생했습니다: ' + response.message);
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
    const uploadOrderButton = document.getElementById('uploadOrderButton');
    
    if (priceDisplay) {
        priceDisplay.classList.add('calculated');
    }
    
    // 인쇄비 + 디자인비 합계를 큰 금액으로 표시
    if (priceAmount) {
        // API에서 이미 계산된 total_price 사용
        const totalPrice = priceData.total_price || 0;
        
        priceAmount.textContent = formatNumber(totalPrice) + '원';
        console.log('💰 큰 금액 표시 (인쇄비+디자인비):', totalPrice + '원');
    }
    
    if (priceDetails) {
        priceDetails.innerHTML = `
            인쇄만: ${priceData.formatted.base_price}<br>
            디자인비: ${priceData.formatted.design_price}<br>
            <strong>부가세 포함: ${priceData.formatted.vat_price}</strong>
        `;
    }
    
    // 업로드 버튼 표시 (명함 패턴과 동일)
    if (uploadOrderButton) {
        uploadOrderButton.style.display = 'block';
    }
    
    console.log('✅ 가격 표시 업데이트 완료');
}

function updateHiddenPriceFields(priceData) {
    const calculatedPrice = document.getElementById('calculated_price');
    const calculatedVatPrice = document.getElementById('calculated_vat_price');
    
    if (calculatedPrice) {
        calculatedPrice.value = priceData.total_price;
    }
    
    if (calculatedVatPrice) {
        calculatedVatPrice.value = priceData.vat_price;
    }
}

// ============================================================================
// 이미지 갤러리 시스템 (명함 패턴 적용)
// ============================================================================

function loadImageGallery() {
    console.log('🖼️ 갤러리 이미지 로드 시작');
    
    fetch('get_ncrflambeau_images.php')
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
                    console.warn('⚠️ 갤러리 데이터 없음:', response.message);
                    showGalleryError('갤러리 이미지가 없습니다.');
                }
            } catch (parseError) {
                console.error('JSON 파싱 오류:', parseError);
                console.error('원시 응답:', text);
                showGalleryError('갤러리 데이터 처리 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('갤러리 로드 네트워크 오류:', error);
            showGalleryError('갤러리를 불러올 수 없습니다.');
        });
}

function renderGallery() {
    const thumbnailStrip = document.getElementById('thumbnailStrip');
    if (!thumbnailStrip) return;
    
    thumbnailStrip.innerHTML = '';
    
    galleryImages.forEach((image, index) => {
        const thumbnail = document.createElement('img');
        // 명함 패턴과 동일한 경로 처리
        thumbnail.src = image.thumbnail_path || image.thumbnail || image.path || image.image_path;
        thumbnail.alt = image.title || `양식지 샘플 ${index + 1}`;
        thumbnail.className = 'thumbnail';
        thumbnail.dataset.index = index;
        
        // 이미지 로드 오류 처리
        thumbnail.onerror = function() {
            console.warn('썸네일 로드 실패:', this.src);
            // 플레이스홀더 이미지로 대체
            this.src = 'data:image/svg+xml;base64,' + btoa(`
                <svg width="80" height="80" xmlns="http://www.w3.org/2000/svg">
                    <rect width="80" height="80" fill="#f8f9fa" stroke="#dee2e6" stroke-width="1"/>
                    <text x="40" y="45" text-anchor="middle" font-family="Arial" font-size="20" fill="#6c757d">📋</text>
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
    
    // 메인 이미지 설정 (명함 패턴과 동일한 경로 처리)
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
                <text x="200" y="140" text-anchor="middle" font-family="Arial" font-size="24" fill="#6c757d">📋</text>
                <text x="200" y="170" text-anchor="middle" font-family="Arial" font-size="16" fill="#6c757d">양식지 샘플</text>
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
    
    // 매우 부드러운 추적 (명함 패턴: 0.08)
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
                <text x="200" y="130" text-anchor="middle" font-family="Arial" font-size="24" fill="#6c757d">📋</text>
                <text x="200" y="160" text-anchor="middle" font-family="Arial" font-size="16" fill="#6c757d">양식지 샘플</text>
                <text x="200" y="180" text-anchor="middle" font-family="Arial" font-size="14" fill="#6c757d">준비중입니다</text>
            </svg>
        `);
        
        zoomBox.style.backgroundImage = `url(${placeholderSvg})`;
        zoomBox.style.backgroundSize = 'contain';
        zoomBox.style.backgroundPosition = '50% 50%';
    }
}

// ============================================================================
// 파일 업로드 모달 시스템 (명함 성공 패턴 적용)
// ============================================================================

// 전역 변수
let uploadedFiles = [];
let selectedUploadMethod = 'upload';
let modalFileUploadInitialized = false;

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

function selectUploadMethod(method) {
    selectedUploadMethod = method;
    
    // 버튼 상태 업데이트
    document.querySelectorAll('.btn-upload-method').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-method="${method}"]`).classList.add('active');
    
    // 업로드 영역 표시/숨김
    const uploadArea = document.getElementById('modalUploadArea');
    if (method === 'upload') {
        uploadArea.style.display = 'block';
    } else {
        uploadArea.style.display = 'none';
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

function handleFileSelect(e) {
    console.log('handleFileSelect 호출됨');
    const files = Array.from(e.target.files);
    console.log('선택된 파일 수:', files.length);
    
    // 파일 입력값 리셋하여 같은 파일 재선택 가능하게 함
    e.target.value = '';
    
    handleFiles(files);
}

function handleFiles(files) {
    const validTypes = ['.jpg', '.jpeg', '.png', '.pdf', '.zip'];
    const maxSize = 15 * 1024 * 1024; // 15MB
    
    files.forEach(file => {
        const extension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(extension)) {
            showUserMessage(`지원하지 않는 파일 형식입니다: ${file.name}\n지원 형식: JPG, PNG, PDF, ZIP`, 'error');
            return;
        }
        
        if (file.size > maxSize) {
            showUserMessage(`파일 크기가 너무 큽니다: ${file.name}\n최대 15MB까지 업로드 가능합니다.`, 'error');
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
        case '.zip': return '📦';
        default: return '📁';
    }
}

function removeFile(fileId) {
    uploadedFiles = uploadedFiles.filter(f => f.id != fileId);
    updateModalFileList();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function addToBasketFromModal() {
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
    
    const form = document.getElementById('ncr-quote-form');
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
    formData.set('price', Math.round(currentPriceData.total_price));
    formData.set('vat_price', Math.round(currentPriceData.vat_price));
    formData.set('product_type', 'ncrflambeau');
    
    // 추가 정보
    formData.set('work_memo', workMemo);
    formData.set('upload_method', selectedUploadMethod);
    
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
                // 모달 닫기
                closeUploadModal();
                
                // 성공 메시지 표시
                showUserMessage('장바구니에 저장되었습니다! 🛒', 'success');
                
                // 장바구니 페이지로 이동
                setTimeout(() => {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                }, 1000);
                
            } else {
                restoreButton(cartButton, originalText);
                showUserMessage('장바구니 저장 중 오류가 발생했습니다: ' + response.message, 'error');
            }
        } catch (parseError) {
            restoreButton(cartButton, originalText);
            console.error('JSON Parse Error:', parseError);
            showUserMessage('서버 응답 처리 중 오류가 발생했습니다.', 'error');
        }
    })
    .catch(error => {
        restoreButton(cartButton, originalText);
        console.error('Fetch Error:', error);
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

// 호환성을 위한 기본 함수들
function addToBasket() {
    openUploadModal();
}

function directOrder() {
    openUploadModal();
}

// 로그인 메시지 처리 (공통 시스템과 연동)
document.addEventListener('DOMContentLoaded', function() {
    // 로그인 관련 처리는 공통 시스템에서 처리
});

// ============================================================================
// 초기 가격 계산 (페이지 로드 시 기본값으로 계산)
// ============================================================================

function calculateInitialPrice() {
    console.log('🎯 초기 가격 계산 함수 시작');
    
    const form = document.getElementById('ncr-quote-form');
    if (!form) {
        console.error('❌ 폼을 찾을 수 없습니다');
        return;
    }
    
    // 현재 선택된 기본값들 확인
    const formData = new FormData(form);
    const categoryValue = formData.get('MY_type') || '';
    const sizeValue = formData.get('Section') || '';
    const colorValue = formData.get('POtype') || '';
    const quantityValue = formData.get('MY_amount') || '';
    const designValue = formData.get('ordertype') || '';
    
    console.log('📋 기본값 확인:', {
        category: categoryValue,
        size: sizeValue,
        color: colorValue,
        quantity: quantityValue,
        design: designValue
    });
    
    // 필수 필드가 모두 선택되었는지 확인
    if (!categoryValue || !sizeValue || !colorValue || !quantityValue || !designValue) {
        console.log('⚠️ 기본값이 완전하지 않음 - 계산 생략');
        return;
    }
    
    // 실제 가격 계산 수행
    console.log('💰 기본값으로 가격 계산 수행');
    performInitialCalculation(formData);
}

function performInitialCalculation(formData) {
    // 로딩 상태 표시
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    
    if (priceAmount) {
        priceAmount.textContent = '계산중...';
    }
    if (priceDetails) {
        priceDetails.innerHTML = '기본 옵션으로<br>가격을 계산하고 있습니다';
    }
    
    // AJAX로 실제 가격 계산
    fetch('calculate_price_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('💰 초기 가격 계산 응답 상태:', response.status);
        return response.json();
    })
    .then(response => {
        console.log('💰 초기 가격 계산 응답:', response);
        
        if (response.success && response.data) {
            console.log('✅ 초기 가격 계산 성공');
            updatePriceDisplay(response.data);
            updateHiddenPriceFields(response.data);
            currentPriceData = response.data;
            
            // 초기 계산에서도 큰 금액 수정 (인쇄비 + 디자인비)
            if (priceAmount) {
                const totalPrice = response.data.total_price || 0;
                priceAmount.textContent = formatNumber(totalPrice) + '원';
                console.log('💰 초기 큰 금액 표시 (인쇄비+디자인비):', totalPrice + '원');
            }
        } else {
            console.warn('⚠️ 초기 가격 계산 실패:', response.message);
            
            // 실패 시 기본 상태로 복원
            if (priceAmount) {
                priceAmount.textContent = '0원';
            }
            if (priceDetails) {
                priceDetails.innerHTML = '옵션을 선택하시면<br>실시간으로 가격이 계산됩니다';
            }
        }
    })
    .catch(error => {
        console.error('❌ 초기 가격 계산 네트워크 오류:', error);
        
        // 오류 시 기본 상태로 복원
        if (priceAmount) {
            priceAmount.textContent = '0원';
        }
        if (priceDetails) {
            priceDetails.innerHTML = '옵션을 선택하시면<br>실시간으로 가격이 계산됩니다';
        }
    });
}

// 에러 처리 및 디버깅
window.addEventListener('error', function(e) {
    console.error('JavaScript 오류:', e.error);
});

console.log('✅ NcrFlambeau 컴팩트 JavaScript 로드 완료');