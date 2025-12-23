/**
 * 스티커 페이지 JavaScript - 실시간 계산 및 갤러리
 * 기존 view_modern.php의 계산 로직을 적용
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 */

// 전역 변수
let isCalculating = false;
let uploadedFiles = [];

// DOM 로딩 완료 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    initializeStickerCalculator();
    initializeStickerGallery();
    initializeFileUpload();
    
    // 초기 계산 실행
    setTimeout(calculatePriceAuto, 500);
});

/**
 * 스티커 계산기 초기화
 */
function initializeStickerCalculator() {
    const form = document.getElementById('stickerForm');
    if (!form) return;
    
    // 모든 입력 요소에 이벤트 리스너 추가
    const inputs = form.querySelectorAll('select, input[type="number"]');
    inputs.forEach(input => {
        input.addEventListener('change', calculatePriceAuto);
        if (input.type === 'number') {
            input.addEventListener('input', debounce(calculatePriceAuto, 300));
        }
    });
    
    console.log('스티커 계산기 초기화 완료');
}

/**
 * 자동 가격 계산 (수식 기반)
 */
function calculatePriceAuto() {
    if (isCalculating) return;
    isCalculating = true;
    
    try {
        const formData = getFormData();
        
        // 필수값 검증
        if (!validateFormData(formData)) {
            updatePriceDisplay('견적 계산 필요', '모든 옵션을 선택해주세요');
            return;
        }
        
        // 가격 계산 (기존 calculate_price.php 로직 적용)
        const result = calculateStickerPrice(formData);
        
        if (result.success) {
            updatePriceDisplay(
                `${number_format(result.price_vat)}원 (VAT포함)`, 
                `VAT별도: ${number_format(result.price)}원`
            );
            
            // 업로드 버튼 표시
            showUploadButton();
        } else {
            updatePriceDisplay('계산 오류', result.message || '계산 중 오류가 발생했습니다');
            hideUploadButton();
        }
        
    } catch (error) {
        console.error('가격 계산 오류:', error);
        updatePriceDisplay('계산 오류', '계산 중 오류가 발생했습니다');
        hideUploadButton();
    } finally {
        isCalculating = false;
    }
}

/**
 * 폼 데이터 가져오기
 */
function getFormData() {
    return {
        jong: document.getElementById('jong')?.value || '',
        garo: parseInt(document.getElementById('garo')?.value) || 0,
        sero: parseInt(document.getElementById('sero')?.value) || 0,
        mesu: parseInt(document.getElementById('mesu')?.value) || 0,
        uhyung: parseInt(document.getElementById('uhyung')?.value) || 0,
        domusong: document.getElementById('domusong')?.value || ''
    };
}

/**
 * 폼 데이터 유효성 검사
 */
function validateFormData(data) {
    if (!data.jong) return false;
    if (data.garo <= 0 || data.garo > 590) return false;
    if (data.sero <= 0 || data.sero > 590) return false;
    if (data.mesu <= 0) return false;
    if (!data.domusong) return false;
    return true;
}

/**
 * 스티커 가격 계산 (수식 기반)
 * 기존 calculate_price.php의 로직을 JavaScript로 구현
 */
function calculateStickerPrice(data) {
    try {
        const { jong, garo, sero, mesu, uhyung, domusong } = data;
        
        // 범위 검증
        if (garo > 590) return { success: false, message: '가로사이즈를 590mm이하만 입력할 수 있습니다' };
        if (sero > 590) return { success: false, message: '세로사이즈를 590mm이하만 입력할 수 있습니다' };
        if ((garo * sero) > 250000 && mesu > 5000) {
            return { success: false, message: '500mm이상 대형사이즈를 5000매이상 주문은 전화요청바랍니다' };
        }
        if (mesu > 10000) return { success: false, message: '1만매 이상은 할인가 적용-전화주시기바랍니다' };
        
        // 도무송 강제 선택 검증
        if ((garo < 50 || sero < 60) && (garo < 60 || sero < 50) && domusong === '00000 사각') {
            return { success: false, message: '가로,세로사이즈가 50mmx60mm 미만일 경우, 도무송을 선택해야 합니다.' };
        }
        
        // 재질 코드 추출
        const j1 = jong.substring(0, 3);
        const j = jong.substring(4, 14);
        
        // 특수 재질 검증
        if (j === '금지스티커') return { success: false, message: '금지스티커는 전화 또는 메일로 견적 문의하세요' };
        if (j === '금박스티커') return { success: false, message: '금박스티커는 전화 또는 메일로 견적 문의하세요' };
        if (j === '롤형스티커') return { success: false, message: '롤스티커는 전화 또는 메일로 견적 문의하세요' };
        
        // 도무송 정보 추출
        const d1 = parseInt(domusong.substring(0, 5));
        
        // 기본값 설정
        let yoyo = 0.15; // 기본 요율
        let mg = 7000;   // 기본 비용
        let ts = 9;      // 기본 톰슨비용
        
        // 재질별 요율 및 비용 설정 (간소화된 버전)
        const materialRates = {
            'jil': [0.15, 0.14, 0.13, 0.12, 0.11, 0.10, 0.09],
            'jka': [0.16, 0.15, 0.14, 0.13, 0.12, 0.11, 0.10],
            'jsp': [0.17, 0.16, 0.15, 0.14, 0.13, 0.12, 0.11],
            'cka': [0.16, 0.15, 0.14, 0.13, 0.12, 0.11, 0.10]
        };
        
        // 수량별 요율 설정
        const rates = materialRates[j1] || materialRates['jil'];
        if (mesu <= 1000) {
            yoyo = rates[0];
            mg = 7000;
        } else if (mesu <= 4000) {
            yoyo = rates[1];
            mg = 6500;
        } else if (mesu <= 5000) {
            yoyo = rates[2];
            mg = 6500;
        } else if (mesu <= 9000) {
            yoyo = rates[3];
            mg = 6000;
        } else if (mesu <= 10000) {
            yoyo = rates[4];
            mg = 5500;
        } else {
            yoyo = rates[5];
            mg = 5000;
        }
        
        // 재질별 톰슨비용
        if (j1 === 'jsp' || j1 === 'jka' || j1 === 'cka') {
            ts = 14;
        }
        
        // 도무송칼 크기 계산
        const d2 = Math.max(garo, sero);
        
        // 사이즈별 마진비율
        const gase = (garo * sero <= 18000) ? 1 : 1.25;
        
        // 도무송 비용 계산
        let d1_cost = 0;
        if (d1 > 0) {
            if (mesu === 500) {
                d1_cost = ((d1 + (d2 * 20)) * 900 / 1000) + (900 * ts);
            } else if (mesu === 1000) {
                d1_cost = ((d1 + (d2 * 20)) * mesu / 1000) + (mesu * ts);
            } else if (mesu > 1000) {
                d1_cost = ((d1 + (d2 * 20)) * mesu / 1000) + (mesu * (ts / 9));
            }
        }
        
        // 특수용지 비용
        let jsp = 0, jka = 0, cka = 0;
        
        if (j1 === 'jsp') {
            if (mesu === 500) {
                jsp = 10000 * (mesu + 400) / 1000;
            } else if (mesu > 500) {
                jsp = 10000 * mesu / 1000;
            }
        }
        
        if (j1 === 'jka') {
            if (mesu === 500) {
                jka = 4000 * (mesu + 400) / 1000;
            } else if (mesu > 500) {
                jka = 10000 * mesu / 1000;
            }
        }
        
        if (j1 === 'cka') {
            if (mesu === 500) {
                cka = 4000 * (mesu + 400) / 1000;
            } else if (mesu > 500) {
                cka = 10000 * mesu / 1000;
            }
        }
        
        // 최종 가격 계산
        let s_price, st_price;
        if (mesu === 500) {
            s_price = ((garo + 4) * (sero + 4) * (mesu + 400)) * yoyo + jsp + jka + cka + d1_cost;
            st_price = Math.round(s_price * gase / 1000) * 1000 + uhyung + (mg * (mesu + 400) / 1000);
        } else {
            s_price = ((garo + 4) * (sero + 4) * mesu) * yoyo + jsp + jka + cka + d1_cost;
            st_price = Math.round(s_price * gase / 1000) * 1000 + uhyung + (mg * mesu / 1000);
        }
        
        const st_price_vat = st_price * 1.1;
        
        return {
            success: true,
            price: Math.round(st_price),
            price_vat: Math.round(st_price_vat)
        };
        
    } catch (error) {
        console.error('가격 계산 오류:', error);
        return { success: false, message: '계산 중 오류가 발생했습니다' };
    }
}

/**
 * 가격 표시 업데이트
 */
function updatePriceDisplay(amount, details) {
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');
    const priceDisplay = document.getElementById('priceDisplay');
    
    if (priceAmount) priceAmount.textContent = amount;
    if (priceDetails) priceDetails.textContent = details;
    
    // calculated 클래스 토글
    if (priceDisplay) {
        if (amount.includes('원')) {
            priceDisplay.classList.add('calculated');
        } else {
            priceDisplay.classList.remove('calculated');
        }
    }
}

/**
 * 업로드 버튼 표시/숨김
 */
function showUploadButton() {
    const button = document.getElementById('uploadOrderButton');
    if (button) {
        button.style.display = 'block';
    }
}

function hideUploadButton() {
    const button = document.getElementById('uploadOrderButton');
    if (button) {
        button.style.display = 'none';
    }
}

/**
 * 스티커 갤러리 초기화 - 명함 갤러리 방식 적용
 */
function initializeStickerGallery() {
    const galleryContainer = document.getElementById('stickerGallery');
    if (!galleryContainer) return;
    
    // GalleryLightbox 클래스 사용 (명함과 동일한 방식)
    if (typeof GalleryLightbox !== 'undefined') {
        // 고급 갤러리 라이트박스 시스템 초기화
        const gallery = new GalleryLightbox('stickerGallery', {
            dataSource: 'get_sticker_images.php',
            productType: 'sticker',
            autoLoad: true,
            zoomEnabled: true,
            animationSpeed: 0.2
        });
        gallery.init();
    } else {
        // GalleryLightbox가 없는 경우 간단한 갤러리 로드
        loadStickerPortfolio();
    }
}

/**
 * 스티커 포트폴리오 로드 (폴백 방식)
 */
function loadStickerPortfolio() {
    fetch('./get_sticker_images.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                renderStickerGallery(data.data);
            } else {
                // 데이터가 없는 경우 하드코딩된 이미지 사용
                const fallbackImages = [
                    { path: '/bbs/upload/portfolio/sticker20061207155050199.jpg', title: '스티커 샘플 1' },
                    { path: '/bbs/upload/portfolio/sticker20061207155200149.jpg', title: '스티커 샘플 2' },
                    { path: '/bbs/upload/portfolio/sticker20061207155224555.jpg', title: '스티커 샘플 3' },
                    { path: '/bbs/upload/portfolio/sticker20061207155249734.jpg', title: '스티커 샘플 4' }
                ];
                renderStickerGallery(fallbackImages);
            }
        })
        .catch(error => {
            console.error('스티커 갤러리 로딩 오류:', error);
            // 오류 시 기본 이미지 표시
            const gallery = document.getElementById('stickerGallery');
            if (gallery) {
                gallery.innerHTML = '<div class="loading">갤러리 로딩 중 오류가 발생했습니다.</div>';
            }
        });
}

/**
 * 명함 갤러리와 동일한 방식으로 스티커 갤러리 렌더링
 */
function renderStickerGallery(images) {
    const gallery = document.getElementById('stickerGallery');
    if (!gallery || !images.length) return;
    
    // 첫 번째 이미지를 큰 이미지로, 나머지를 작은 이미지로 표시
    const mainImage = images[0];
    const thumbnails = images.slice(1, 5); // 최대 4개의 썸네일
    
    const galleryHtml = `
        <div class="gallery-main-container">
            <!-- 큰 이미지 -->
            <div class="gallery-main-image" onclick="openImageLightbox('${mainImage.path}')">
                <img src="${mainImage.path}" alt="${mainImage.title || '스티커 샘플'}" loading="lazy">
                <div class="gallery-overlay">
                    <span>🔍 확대보기</span>
                </div>
            </div>
            
            <!-- 작은 이미지들 -->
            <div class="gallery-thumbnails">
                ${thumbnails.map((image, index) => `
                    <div class="gallery-thumbnail" onclick="switchMainImage('${image.path}', '${image.title || '스티커 샘플 ' + (index + 2)}')">
                        <img src="${image.path}" alt="${image.title || '스티커 샘플 ' + (index + 2)}" loading="lazy">
                        <div class="thumbnail-overlay">
                            <span>👆</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    
    gallery.innerHTML = galleryHtml;
}

/**
 * 메인 이미지 교체 (썸네일 클릭 시)
 */
function switchMainImage(imagePath, imageTitle) {
    const mainImageContainer = document.querySelector('.gallery-main-image');
    const mainImage = mainImageContainer.querySelector('img');
    
    if (mainImage) {
        // 부드러운 전환 효과
        mainImage.style.opacity = '0.3';
        
        setTimeout(() => {
            mainImage.src = imagePath;
            mainImage.alt = imageTitle;
            mainImage.style.opacity = '1';
            
            // 클릭 이벤트도 업데이트
            mainImageContainer.onclick = () => openImageLightbox(imagePath);
        }, 200);
    }
}

/**
 * 이미지 라이트박스 열기
 */
function openImageLightbox(imageSrc) {
    // GalleryLightbox.js 사용
    if (window.GalleryLightbox) {
        window.GalleryLightbox.open(imageSrc);
    } else {
        // 간단한 대안
        window.open(imageSrc, '_blank');
    }
}

/**
 * 파일 업로드 초기화
 */
function initializeFileUpload() {
    const dropzone = document.getElementById('modalUploadDropzone');
    const fileInput = document.getElementById('modalFileInput');
    
    if (dropzone) {
        // 드래그 앤 드롭 이벤트
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, unhighlight, false);
        });
        
        dropzone.addEventListener('drop', handleDrop, false);
        dropzone.addEventListener('click', () => fileInput?.click());
    }
    
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }
}

/**
 * 파일 업로드 모달 열기
 */
function openUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * 파일 업로드 모달 닫기
 */
function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

/**
 * 장바구니에 추가
 */
function addToBasketFromModal() {
    const formData = getFormData();
    
    if (!validateFormData(formData)) {
        alert('모든 옵션을 선택해주세요.');
        return;
    }
    
    // 작업메모 추가
    const memo = document.getElementById('modalWorkMemo')?.value || '';
    
    // 장바구니 추가 로직 (기존 시스템과 연동)
    addToBasket(formData, uploadedFiles, memo);
}

/**
 * 장바구니 추가 실행
 */
function addToBasket(formData, files, memo) {
    const basketData = {
        product_type: 'sticker',
        jong: formData.jong,
        garo: formData.garo,
        sero: formData.sero,
        mesu: formData.mesu,
        uhyung: formData.uhyung,
        domusong: formData.domusong,
        memo: memo,
        files: files.map(f => f.name).join(','),
        session_id: document.querySelector('meta[name="session-id"]')?.content
    };
    
    // AJAX 요청으로 장바구니 추가 (스티커 전용 API 사용)
    fetch('./add_to_basket.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(basketData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('장바구니에 추가되었습니다.');
            closeUploadModal();
            
            // 장바구니 페이지로 이동 여부 확인
            if (confirm('장바구니 페이지로 이동하시겠습니까?')) {
                window.location.href = '/shop/cart.php';
            }
        } else {
            alert('장바구니 추가 실패: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('장바구니 추가 오류:', error);
        alert('장바구니 추가 중 오류가 발생했습니다.');
    });
}

/**
 * 유틸리티 함수들
 */

// 디바운스 함수
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// 숫자 포맷팅
function number_format(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// 드래그 앤 드롭 관련 함수들
function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(e) {
    e.currentTarget.classList.add('dragover');
}

function unhighlight(e) {
    e.currentTarget.classList.remove('dragover');
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

function handleFileSelect(e) {
    const files = e.target.files;
    handleFiles(files);
}

function handleFiles(files) {
    Array.from(files).forEach(uploadFile);
}

function uploadFile(file) {
    // 파일 타입 검증
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        alert('지원하지 않는 파일 형식입니다. (JPG, PNG, PDF만 가능)');
        return;
    }
    
    // 파일 크기 검증 (10MB)
    if (file.size > 10 * 1024 * 1024) {
        alert('파일 크기는 10MB 이하만 업로드 가능합니다.');
        return;
    }
    
    uploadedFiles.push(file);
    displayUploadedFiles();
}

function displayUploadedFiles() {
    const filesContainer = document.getElementById('modalUploadedFiles');
    const fileList = document.getElementById('modalFileList');
    
    if (!filesContainer || !fileList) return;
    
    if (uploadedFiles.length > 0) {
        filesContainer.style.display = 'block';
        
        fileList.innerHTML = uploadedFiles.map((file, index) => `
            <div class="file-item">
                <div>
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">(${formatFileSize(file.size)})</span>
                </div>
                <button type="button" class="file-remove" onclick="removeFile(${index})">삭제</button>
            </div>
        `).join('');
    } else {
        filesContainer.style.display = 'none';
    }
}

function removeFile(index) {
    uploadedFiles.splice(index, 1);
    displayUploadedFiles();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function selectUploadMethod(method) {
    const buttons = document.querySelectorAll('.btn-upload-method');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    if (method === 'upload') {
        buttons[0]?.classList.add('active');
    }
}

// 전역 함수로 내보내기
window.openUploadModal = openUploadModal;
window.closeUploadModal = closeUploadModal;
window.addToBasketFromModal = addToBasketFromModal;
window.selectUploadMethod = selectUploadMethod;
window.removeFile = removeFile;
window.switchMainImage = switchMainImage;