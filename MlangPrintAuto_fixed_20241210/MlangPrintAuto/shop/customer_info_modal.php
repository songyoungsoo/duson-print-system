<?php
/**
 * 고객 정보 입력 모달
 * 견적서 생성 전에 고객 정보를 입력받는 모달 창
 */
?>

<!-- 고객 정보 입력 모달 -->
<div id="customerInfoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📋 견적서 고객 정보 입력</h3>
            <span class="close" onclick="closeCustomerModal()">&times;</span>
        </div>
        
        <form id="customerInfoForm" onsubmit="generateQuoteWithCustomerInfo(event)">
            <div class="form-group">
                <label for="customer_name">고객명 *</label>
                <input type="text" id="customer_name" name="customer_name" required 
                       placeholder="홍길동" maxlength="50">
            </div>
            
            <div class="form-group">
                <label for="customer_phone">연락처 *</label>
                <input type="tel" id="customer_phone" name="customer_phone" required 
                       placeholder="010-1234-5678" maxlength="20">
            </div>
            
            <div class="form-group">
                <label for="customer_company">회사명</label>
                <input type="text" id="customer_company" name="customer_company" 
                       placeholder="(주)회사명" maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="customer_email">이메일</label>
                <input type="email" id="customer_email" name="customer_email" 
                       placeholder="example@company.com" maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="quote_memo">요청사항</label>
                <textarea id="quote_memo" name="quote_memo" rows="3" 
                          placeholder="견적서 관련 요청사항이 있으시면 입력해주세요" maxlength="500"></textarea>
            </div>
            
            <div class="modal-buttons">
                <button type="button" onclick="closeCustomerModal()" class="btn-cancel">
                    취소
                </button>
                <button type="submit" class="btn-generate">
                    📄 견적서 생성
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(3px);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    background: linear-gradient(135deg, #2c5aa0 0%, #17a2b8 100%);
    color: white;
    padding: 20px;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
}

.close:hover {
    color: #ffeb3b;
}

#customerInfoForm {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s, box-shadow 0.3s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #2c5aa0;
    box-shadow: 0 0 0 3px rgba(44, 90, 160, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.modal-buttons {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-cancel,
.btn-generate {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.btn-generate {
    background: linear-gradient(135deg, #2c5aa0 0%, #17a2b8 100%);
    color: white;
}

.btn-generate:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(44, 90, 160, 0.3);
}

.btn-generate:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    #customerInfoForm {
        padding: 20px;
    }
    
    .modal-buttons {
        flex-direction: column;
    }
}
</style>

<script>
// 고객 정보 모달 열기
function openCustomerModal() {
    document.getElementById('customerInfoModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
    
    // 첫 번째 입력 필드에 포커스
    setTimeout(() => {
        document.getElementById('customer_name').focus();
    }, 300);
}

// 고객 정보 모달 닫기
function closeCustomerModal() {
    document.getElementById('customerInfoModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // 배경 스크롤 복원
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCustomerModal();
    }
});

// 모달 배경 클릭 시 닫기
document.getElementById('customerInfoModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeCustomerModal();
    }
});

// 전화번호 자동 포맷팅
document.getElementById('customer_phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9]/g, '');
    
    if (value.length >= 11) {
        value = value.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
    } else if (value.length >= 7) {
        value = value.replace(/(\d{3})(\d{4})(\d+)/, '$1-$2-$3');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d+)/, '$1-$2');
    }
    
    e.target.value = value;
});

// 고객 정보와 함께 견적서 생성
function generateQuoteWithCustomerInfo(event) {
    event.preventDefault();
    
    const form = document.getElementById('customerInfoForm');
    const formData = new FormData(form);
    
    // 버튼 비활성화
    const submitBtn = form.querySelector('.btn-generate');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '⏳ 생성중...';
    submitBtn.disabled = true;
    
    // 고객 정보를 URL 파라미터로 전달
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            params.append(key, value.trim());
        }
    }
    
    // 견적서 생성 페이지로 이동
    const quoteWindow = window.open(
        '/MlangPrintAuto/shop/generate_quote_pdf.php?' + params.toString(), 
        '_blank', 
        'width=800,height=600,scrollbars=yes'
    );
    
    // 버튼 복원 및 모달 닫기
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        closeCustomerModal();
        
        // 폼 초기화 (선택사항)
        // form.reset();
    }, 1000);
    
    // 새 창이 차단된 경우 처리
    if (!quoteWindow) {
        alert('팝업이 차단되었습니다. 팝업 차단을 해제하거나 직접 견적서 페이지로 이동합니다.');
        window.location.href = '/MlangPrintAuto/shop/generate_quote_pdf.php?' + params.toString();
    }
}
</script>