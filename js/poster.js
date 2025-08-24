/**
 * 포스터/리플렛 견적안내 컴팩트 시스템 - 고급 갤러리 및 실시간 계산기
 * PROJECT_SUCCESS_REPORT.md 스펙에 따른 완전 재구축
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
    initializeGallery();
    initializeCalculator();
    initializeFileUpload();
    
    // 기본값이 설정되어 있으면 자동으로 하위 옵션들 로드
    const typeSelect = document.getElementById('MY_type');
    if (typeSelect && typeSelect.value) {
        console.log('🚀 페이지 로드 시 기본값 포스터 종류 감지:', typeSelect.value);
        loadPaperTypes(typeSelect.value);
    }
});

// ============================================================================
// 고급 이미지 갤러리 시스템 (적응형 이미지 분석 및 부드러운 애니메이션)
// ============================================================================

function initializeGallery() {
    const galleryContainer = document.getElementById('posterGallery');
    if (!galleryContainer) return;
    
    // GalleryLightbox 클래스 사용
    if (typeof GalleryLightbox !== 'undefined') {
        // 고급 갤러리 라이트박스 시스템 초기화
        const gallery = new GalleryLightbox('posterGallery', {
            dataSource: 'get_poster_images.php',
            productType: 'poster',
            autoLoad: true,
            zoomEnabled: true,
            animationSpeed: 0.15
        });
        
        gallery.init();
        
        // GalleryLightbox 초기화 완료 후 더보기 버튼 확인
        setTimeout(() => {
            checkMoreButtonForLightbox();
        }, 1000);
        
        console.log('GalleryLightbox 시스템으로 포스터 갤러리 초기화 완료');
    } else {
        // 폴백: 기본 갤러리 시스템
        loadPosterImages();
    }
}

function loadPosterImages() {
    const galleryContainer = document.getElementById('posterGallery');
    if (!galleryContainer) return;
    
    galleryContainer.innerHTML = '<div class="loading">🖼️ 갤러리 로딩 중...</div>';
    
    fetch('get_poster_images.php')
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
        thumbnail.alt = image.title || `포스터 샘플 ${index + 1}`;
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
        // 항상 더보기 버튼 표시 (사용자 요청에 따라)
        moreButton.style.display = 'block';
    }
}

function checkMoreButtonForLightbox() {
    // GalleryLightbox 사용 시 더보기 버튼 표시 확인
    fetch('get_poster_images.php?all=true')
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
    const sizeSelect = document.getElementById('PN_type');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');
    const ordertypeSelect = document.getElementById('ordertype');

    if (!typeSelect) return;

    // 드롭다운 변경 이벤트 리스너
    typeSelect.addEventListener('change', function() {
        const style = this.value;
        resetSelectWithText(paperSelect, '용지 재질을 선택해주세요');
        resetSelectWithText(sizeSelect, '규격을 선택해주세요');
        resetSelectWithText(quantitySelect, '수량을 선택해주세요');
        resetPrice();

        if (style) {
            loadPaperTypes(style);
        }
    });

    if (paperSelect) {
        paperSelect.addEventListener('change', function() {
            const section = this.value;
            resetSelectWithText(sizeSelect, '규격을 선택해주세요');
            resetSelectWithText(quantitySelect, '수량을 선택해주세요');
            resetPrice();

            if (section) {
                loadPaperSizes(section);
            }
        });
    }
    
    if (sizeSelect) {
        sizeSelect.addEventListener('change', loadQuantities);
    }
    if (sideSelect) {
        sideSelect.addEventListener('change', loadQuantities);
    }
    
    // 모든 옵션 변경 시 자동 계산 (실시간)
    [typeSelect, paperSelect, sizeSelect, sideSelect, quantitySelect, ordertypeSelect].forEach(select => {
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
    console.log('🔍 loadPaperTypes 호출됨, style:', style);
    
    if (!style) {
        console.log('❌ 스타일이 없어서 로드 중단');
        return;
    }

    const url = `get_paper_types.php?style=${style}`;
    console.log('📡 용지 재질 API 호출:', url);

    fetch(url)
        .then(response => {
            console.log('📡 용지 재질 응답 상태:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📊 용지 재질 데이터:', data);
            
            if (data.success) {
                const paperSelect = document.getElementById('Section');
                updateSelectWithOptions(paperSelect, data.data, '용지 재질을 선택해주세요');
                console.log(`✅ 용지 재질 옵션 ${data.data.length}개 로드됨`);
                
                // 첫 번째 용지 재질 자동 선택
                if (data.data.length > 0) {
                    const firstOption = data.data[0];
                    paperSelect.value = firstOption.no;
                    console.log(`🎯 첫 번째 용지 재질 자동 선택: ${firstOption.title}`);
                    
                    // 규격 로드 (지연 실행으로 안정성 향상)
                    setTimeout(() => loadPaperSizes(firstOption.no), 100);
                }
            } else {
                console.error('❌ 용지 재질 로드 실패:', data.message);
                showUserMessage('재질 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('💥 용지 재질 로드 오류:', error);
            showUserMessage('재질 로드 중 오류가 발생했습니다.', 'error');
        });
}

function loadPaperSizes(section) {
    console.log('🔍 loadPaperSizes 호출됨, section:', section);
    
    if (!section) {
        console.log('❌ section이 없어서 로드 중단');
        return;
    }

    const url = `get_paper_sizes.php?section=${section}`;
    console.log('📡 규격 API 호출:', url);

    fetch(url)
        .then(response => {
            console.log('📡 규격 응답 상태:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📊 규격 데이터:', data);
            
            if (data.success) {
                const sizeSelect = document.getElementById('PN_type');
                updateSelectWithOptions(sizeSelect, data.data, '규격을 선택해주세요');
                console.log(`✅ 규격 옵션 ${data.data.length}개 로드됨`);
                
                // 첫 번째 규격 자동 선택
                if (data.data.length > 0) {
                    const firstSize = data.data[0];
                    sizeSelect.value = firstSize.no;
                    console.log(`🎯 첫 번째 규격 자동 선택: ${firstSize.title}`);
                    
                    // 인쇄면도 자동 선택 (단면 기본값)
                    const sideSelect = document.getElementById('POtype');
                    if (sideSelect && !sideSelect.value) {
                        sideSelect.value = '1'; // 단면
                        console.log('🎯 인쇄면 자동 선택: 단면');
                    }
                    
                    // 모든 필수 옵션이 선택된 후 수량 로드
                    loadQuantities();
                }
            } else {
                console.error('❌ 규격 로드 실패:', data.message);
                showUserMessage('규격 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('💥 규격 로드 오류:', error);
            showUserMessage('규격 로드 중 오류가 발생했습니다.', 'error');
        });
}

function loadQuantities() {
    const typeSelect = document.getElementById('MY_type');
    const paperSelect = document.getElementById('Section');
    const sizeSelect = document.getElementById('PN_type');
    const sideSelect = document.getElementById('POtype');
    const quantitySelect = document.getElementById('MY_amount');

    console.log('🔍 loadQuantities 호출됨');
    console.log('Elements found:', {
        typeSelect: !!typeSelect,
        paperSelect: !!paperSelect,
        sizeSelect: !!sizeSelect,
        sideSelect: !!sideSelect,
        quantitySelect: !!quantitySelect
    });

    if (!typeSelect || !paperSelect || !sizeSelect || !sideSelect || !quantitySelect) {
        console.error('❌ 필수 엘리먼트를 찾을 수 없습니다');
        return;
    }

    const style = typeSelect.value;
    const section = paperSelect.value;
    const size = sizeSelect.value;
    const potype = sideSelect.value;

    console.log('📊 현재 값들:', { style, section, size, potype });

    resetSelectWithText(quantitySelect, '수량을 선택해주세요');
    resetPrice();

    if (!style || !section || !size || !potype) {
        console.log('⚠️ 필수 값이 누락됨 - 수량 로드 중단', { style, section, size, potype });
        return;
    }
    
    // 기본 선택값이 아닌 실제 선택된 값인지 확인
    if (section === '' || size === '' || potype === '') {
        console.log('⚠️ 아직 선택되지 않은 값들이 있음 - 수량 로드 중단');
        return;
    }

    // 수량 조회에서는 규격(PN_type)를 추가로 포함
    const url = `get_quantities.php?style=${style}&section=${section}&pn_type=${size}&potype=${potype}`;
    console.log('📡 API 호출:', url);

    fetch(url)
        .then(response => {
            console.log('📡 응답 상태:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📊 수량 데이터:', data);
            
            if (data.success) {
                updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                console.log(`✅ 수량 옵션 ${data.data.length}개 로드됨`);
                
                // 기본값 자동 선택 (10매 우선, 없으면 첫 번째 옵션)
                let selectedValue = null;
                
                // 1순위: 10매 찾기
                const option10 = data.data.find(opt => opt.value === '10');
                if (option10) {
                    selectedValue = '10';
                    console.log('🎯 기본값으로 10매 자동 선택');
                } else if (data.data.length > 0) {
                    // 2순위: 첫 번째 옵션
                    selectedValue = data.data[0].value;
                    console.log(`🎯 기본값으로 첫 번째 옵션 자동 선택: ${selectedValue}매`);
                }
                
                // 선택값 적용 및 가격 계산
                if (selectedValue) {
                    quantitySelect.value = selectedValue;
                    console.log(`✅ 수량 자동 선택됨: ${selectedValue}매`);
                    autoCalculatePrice();
                }
            } else {
                console.error('❌ 수량 로드 실패:', data.message);
                showUserMessage('수량 로드 실패: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('💥 수량 로드 오류:', error);
            showUserMessage('수량 로드 중 오류가 발생했습니다.', 'error');
        });
}

function updateSelectWithOptions(selectElement, options, defaultOptionText) {
    console.log('🔧 updateSelectWithOptions 호출됨:', {
        hasElement: !!selectElement,
        optionsLength: options ? options.length : 0,
        defaultText: defaultOptionText
    });
    
    if (!selectElement) {
        console.error('❌ selectElement가 없습니다');
        return;
    }
    
    selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
    
    if (options && options.length > 0) {
        options.forEach((option, index) => {
            const optionElement = document.createElement('option');
            optionElement.value = option.value || option.no;
            optionElement.textContent = option.text || option.title;
            selectElement.appendChild(optionElement);
            
            console.log(`📝 옵션 ${index + 1}: ${optionElement.value} = ${optionElement.textContent}`);
        });
        console.log(`✅ ${options.length}개 옵션이 ${selectElement.id}에 추가됨`);
    } else {
        console.log('⚠️ 추가할 옵션이 없습니다');
    }
}

// 자동 계산 (실시간)
function autoCalculatePrice() {
    const form = document.getElementById('posterForm');
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
    const form = document.getElementById('posterForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    if (!formData.get('MY_type') || !formData.get('Section') || 
        !formData.get('POtype') || !formData.get('MY_amount') || 
        !formData.get('ordertype')) {
        return;
    }
    
    const params = new URLSearchParams(formData);
    
    fetch('calculate_price_ajax.php?' + params.toString())
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
            
            // 가격 표시 업데이트
            updatePriceDisplay(priceData);
            
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
            인쇄비: ${formatNumber(priceData.base_price)}원<br>
            디자인비: ${formatNumber(priceData.design_price)}원<br>
            <strong>부가세 포함: ${formatNumber(Math.round(priceData.total_with_vat))}원</strong>
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

// 모달에서 장바구니에 추가 (강화된 에러 처리)
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
    
    const form = document.getElementById('posterForm');
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
    formData.set('vat_price', Math.round(currentPriceData.total_with_vat));
    formData.set('product_type', 'poster');
    
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