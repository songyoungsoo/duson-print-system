<?php
/**
 * 고객 정보 입력 모달
 * 견적서 생성 전에 고객 정보를 입력받는 모달 창
 */
?>

<!-- 견적 발송 성공 모달 -->
<div id="quoteSuccessModal" class="modal customer-modal" style="display: none;">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header" style="background:linear-gradient(135deg,#1E4E79 0%,#2a6496 100%);">
            <h2 class="modal-brand">두손기획인쇄</h2>
            <span class="close" onclick="closeQuoteSuccessModal()">&times;</span>
            <h3 class="modal-title">✅ 견적서 발송 완료</h3>
        </div>
        <div style="padding:28px 25px;text-align:center;">
            <div style="font-size:48px;margin-bottom:16px;">📧</div>
            <p style="font-size:16px;font-weight:700;color:#1E4E79;margin:0 0 8px;">견적서가 이메일로 발송되었습니다!</p>
            <p id="quoteSuccessNo" style="font-size:13px;color:#64748b;margin:0 0 20px;"></p>
            <div style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;margin-bottom:20px;text-align:left;font-size:13px;color:#334155;line-height:1.7;">
                <div>📋 견적번호: <strong id="quoteNoDisplay" style="color:#1E4E79;"></strong></div>
                <div>📧 발송 이메일: <strong id="quoteEmailDisplay" style="color:#1E4E79;"></strong></div>
                <div style="margin-top:8px;font-size:12px;color:#64748b;">스팸함도 확인해 주세요. 문의: 02-2632-1830</div>
            </div>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button onclick="closeQuoteSuccessModal()" style="padding:10px 24px;background:#1E4E79;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;">확인</button>
                <button onclick="closeQuoteSuccessModal();showQuotation();" style="padding:10px 24px;background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;">견적서 보기</button>
            </div>
        </div>
    </div>
</div>

<!-- 고객 정보 입력 모달 -->
<div id="customerInfoModal" class="modal customer-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-brand">두손기획인쇄</h2>
            <span class="close" onclick="closeCustomerModal()">&times;</span>
            <h3 class="modal-title">📋 견적서 고객 정보 입력</h3>
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
.modal.customer-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.customer-modal .modal-content {
    background-color: white;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
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

.customer-modal .modal-header {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
    padding: 16px 20px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
}

.customer-modal .modal-header .modal-brand {
    font-size: 1.2rem;
    font-weight: 700;
    color: white;
    margin: 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.customer-modal .modal-header .modal-title {
    font-size: 0.95rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    width: 100%;
    order: 1;
}

.customer-modal .close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-weight: normal;
    line-height: 1;
}

.customer-modal .close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
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
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
}

.btn-generate:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.btn-generate:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .customer-modal .modal-content {
        width: 92%;
        max-width: none;
        max-height: 90vh;
        overflow-y: auto;
        margin: 5% auto;
    }
    
    .customer-modal .modal-header {
        padding: 12px 16px;
    }
    
    .customer-modal .modal-header .modal-brand {
        font-size: 1rem;
    }
    
    .customer-modal .modal-header .modal-title {
        font-size: 0.85rem;
    }
    
    #customerInfoForm {
        padding: 16px;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group label {
        font-size: 13px;
        margin-bottom: 6px;
    }
    
    .form-group input,
    .form-group textarea {
        padding: 10px 12px;
        font-size: 13px;
    }
    
    .modal-buttons {
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }
    
    .btn-cancel,
    .btn-generate {
        padding: 11px 16px;
        font-size: 13px;
    }
    
    /* Success modal mobile */
    #quoteSuccessModal .modal-content {
        max-width: 90%;
        margin: 10% auto;
    }
}

/* Very small screens */
@media (max-width: 400px) {
    .customer-modal .modal-content {
        width: 95%;
        max-height: 90vh;
        overflow-y: auto;
        margin: 3% auto;
    }
    
    #customerInfoForm {
        padding: 14px;
    }
    
    .form-group {
        margin-bottom: 14px;
    }
    
    #quoteSuccessModal .modal-content {
        max-width: 95%;
        margin: 5% auto;
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

function closeQuoteSuccessModal() {
    document.getElementById('quoteSuccessModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function generateQuoteWithCustomerInfo(event) {
    event.preventDefault();

    const form = document.getElementById('customerInfoForm');
    const submitBtn = form.querySelector('.btn-generate');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '⏳ 발송중...';
    submitBtn.disabled = true;

    const payload = {
        name:    document.getElementById('customer_name').value.trim(),
        phone:   document.getElementById('customer_phone').value.trim(),
        email:   document.getElementById('customer_email').value.trim(),
        company: document.getElementById('customer_company').value.trim(),
        memo:    document.getElementById('quote_memo').value.trim(),
    };

    fetch('/mlangprintauto/shop/send_cart_quotation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        body: JSON.stringify(payload),
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        closeCustomerModal();

        if (data.success) {
            document.getElementById('quoteNoDisplay').textContent    = data.data.quote_no || '';
            document.getElementById('quoteEmailDisplay').textContent = payload.email;
            document.getElementById('quoteSuccessNo').textContent    = payload.name + '님의 견적서가 발송되었습니다.';
            document.getElementById('quoteSuccessModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } else {
            alert('견적서 발송 실패: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(err => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
        console.error(err);
    });
}
</script>