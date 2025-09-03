/**
 * 명함 견적안내 컴팩트 시스템 - 고급 갤러리 및 실시간 계산기
 * PROJECT_SUCCESS_REPORT.md 스펙에 따른 완전 재구축
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 */

// 전역 변수들
let currentPriceData = null;
let uploadedFiles = [];
let selectedUploadMethod = 'upload';
let modalFileUploadInitialized = false; // 모달 파일 업로드 초기화 상태

// 통합 갤러리 시스템 사용 (갤러리 관련 변수들 제거됨)

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 통합 갤러리 시스템 사용 (include_product_gallery에서 자동 처리)
    initializeCalculator();
    initializeFileUpload();
    
    // 기본값이 설정되어 있으면 자동으로 하위 옵션들 로드
    const typeSelect = document.getElementById('MY_type');
    if (typeSelect && typeSelect.value) {
        loadPaperTypes(typeSelect.value);
    }
});

// ============================================================================
// 통합 갤러리 시스템 사용 중 (include_product_gallery에서 자동 처리)
// 기존 인라인 갤러리 코드 제거됨 - 공통 갤러리 시스템으로 통합
// ============================================================================

// 통합 갤러리 시스템으로 대체됨 (include_product_gallery 사용)

// 포스터 방식 호버링으로 변경 - 복잡한 애니메이션 함수 제거됨

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
        // resetPrice() 제거 - autoCalculatePrice()에서 처리

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
    if (priceDetails) {
        priceDetails.innerHTML = '<span>모든 옵션을 선택하면 자동으로 계산됩니다</span>';
        
        // 초기 상태에서도 flex 레이아웃 강제 적용
        priceDetails.style.display = 'flex';
        priceDetails.style.justifyContent = 'center';
        priceDetails.style.alignItems = 'center';
        priceDetails.style.gap = '15px';
        priceDetails.style.flexWrap = 'nowrap';
        priceDetails.style.whiteSpace = 'nowrap';
        priceDetails.style.flexDirection = 'row';
    }
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
    // resetPrice() 제거 - autoCalculatePrice()에서 처리

    if (!style || !section || !potype) return;

    fetch(`get_quantities.php?style=${style}&section=${section}&potype=${potype}`)
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
    const form = document.getElementById('namecardForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    // 모든 필수 옵션이 선택되었는지 확인
    if (!formData.get('MY_type') || !formData.get('Section') || 
        !formData.get('POtype') || !formData.get('MY_amount') || 
        !formData.get('ordertype')) {
        // 옵션이 부족할 때만 가격 초기화
        resetPrice();
        return;
    }
    
    // 실시간 계산 실행
    calculatePrice(true);
}

// 가격 계산 함수 (강화된 에러 처리)
function calculatePrice(isAuto = true) {
    const form = document.getElementById('namecardForm');
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
            <span>인쇄비: ${formatNumber(priceData.base_price)}원</span>
            <span>디자인비: ${formatNumber(priceData.design_price)}원</span>
            <span>부가세 포함: <span class="vat-amount">${formatNumber(Math.round(priceData.total_with_vat))}원</span></span>
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
    
    const form = document.getElementById('namecardForm');
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
    formData.set('product_type', 'namecard');
    
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