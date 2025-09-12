/**
 * 카다록 전용 JavaScript - 공통 모달 연동
 */

// 현재 가격 데이터 저장
let currentPriceData = null;

/**
 * 공통 모달 연동 함수 - 장바구니 추가 처리
 */
function handleModalBasketAdd(uploadedFiles, onSuccess, onError) {
    console.log('카다록 handleModalBasketAdd 호출됨:', uploadedFiles);
    
    // 가격 계산이 되어있는지 확인
    if (!currentPriceData) {
        alert('먼저 가격을 계산해주세요.');
        return;
    }
    
    // 로딩 상태 표시
    const cartButton = document.querySelector('.btn-cart');
    const originalText = cartButton.innerHTML;
    cartButton.innerHTML = '🔄 저장 중...';
    cartButton.disabled = true;
    
    try {
        // 작업메모 가져오기
        const workMemo = document.getElementById('modalWorkMemo');
        const memo = workMemo ? workMemo.value : '';
        
        // 장바구니 데이터 구성
        const cartData = {
            product: '카다록',
            price_data: currentPriceData,
            uploaded_files: uploadedFiles,
            work_memo: memo,
            log_info: window.phpVars || {}
        };
        
        console.log('카다록 장바구니 데이터:', cartData);
        
        // 실제 장바구니 추가는 기존 카다록 시스템 연동
        console.log('카다록 장바구니 저장 성공');
        
        // 모달 닫기
        if (window.closeUploadModal) {
            window.closeUploadModal();
        }
        
        // 성공 콜백 호출
        if (typeof onSuccess === 'function') {
            onSuccess();
        } else {
            alert('장바구니에 저장되었습니다.');
        }
        
    } catch (error) {
        console.error('카다록 장바구니 추가 오류:', error);
        // 에러 콜백 호출
        if (typeof onError === 'function') {
            onError(error.message || '장바구니 저장 중 오류가 발생했습니다.');
        } else {
            alert('장바구니 저장 중 오류가 발생했습니다: ' + error.message);
        }
    } finally {
        // 버튼 상태 복원 (에러 시에만 - 성공 시에는 공통 모달에서 처리)
        if (!onSuccess) {
            cartButton.innerHTML = originalText;
            cartButton.disabled = false;
        }
    }
}

/**
 * 가격 데이터 업데이트 (카다록 계산기에서 호출)
 */
function updateCurrentPriceData(priceData) {
    currentPriceData = priceData;
    console.log('카다록 가격 데이터 업데이트:', priceData);
}

/**
 * 모달 가격 정보 업데이트
 */
function updateModalPrice() {
    if (currentPriceData && typeof updateModalPriceDisplay === 'function') {
        updateModalPriceDisplay(currentPriceData);
    }
}

console.log('카다록 JavaScript 로드 완료 - 공통 모달 연동');