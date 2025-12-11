/**
 * 봉투 양면테이프 옵션 계산 시스템
 * Created: 2025-01-17
 * 가격 구조: 500매: 25,000원, 1000매: 40,000원, 1000매 이상: 매당 40원
 */

// 기존 calculatePrice 함수 확장
const originalCalculatePrice = window.calculatePrice;

// 새로운 calculatePrice 함수
window.calculatePrice = function(isAuto = true) {
    // 기존 계산 실행
    if (originalCalculatePrice) {
        originalCalculatePrice.call(this, isAuto);
    }

    // 양면테이프 가격 포함하여 재계산
    includeTapeInTotal();
};

// 양면테이프 가격을 총액에 포함
function includeTapeInTotal() {
    const tapeEnabled = document.getElementById('tape_enabled');
    if (!tapeEnabled) return;

    const tapePrice = parseInt(document.getElementById('tape_price').value) || 0;

    // 현재 표시된 가격 가져오기
    const priceAmount = document.getElementById('priceAmount');
    const priceDetails = document.getElementById('priceDetails');

    if (!priceAmount || !priceDetails) return;

    // 기존 가격 파싱 (콤마 제거)
    let currentPriceText = priceAmount.textContent.replace(/[^\d]/g, '');
    let currentPrice = parseInt(currentPriceText) || 0;

    // 양면테이프 포함 가격이 이미 계산되었는지 확인
    if (priceAmount.dataset.tapeIncluded === 'true') {
        // 이전 양면테이프 가격 빼기
        const prevTapePrice = parseInt(priceAmount.dataset.prevTapePrice) || 0;
        currentPrice = currentPrice - prevTapePrice;
    }

    // 새로운 총액 계산
    const totalPrice = currentPrice + tapePrice;

    // 가격 표시 업데이트
    if (totalPrice > 0) {
        priceAmount.textContent = `${totalPrice.toLocaleString()}원`;
        priceAmount.dataset.tapeIncluded = tapeEnabled.checked ? 'true' : 'false';
        priceAmount.dataset.prevTapePrice = tapePrice.toString();

        // 상세 내역 업데이트
        updatePriceDetailsWithTape(currentPrice, tapePrice, totalPrice);

        // 업로드 버튼 표시
        const uploadButton = document.getElementById('uploadOrderButton');
        if (uploadButton) {
            uploadButton.style.display = 'block';
        }
    }
}

// 가격 상세 내역에 양면테이프 추가
function updatePriceDetailsWithTape(basePrice, tapePrice, totalPrice) {
    const priceDetails = document.getElementById('priceDetails');
    if (!priceDetails) return;

    let detailsHTML = '';

    // 기존 가격 정보 유지
    const existingDetails = priceDetails.innerHTML;

    // 양면테이프 정보가 없으면 추가
    if (!existingDetails.includes('양면테이프')) {
        detailsHTML = existingDetails;

        if (tapePrice > 0) {
            // 양면테이프 라인 추가
            const tapeLineHTML = `
                <div class="price-breakdown" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ddd;">
                    <div class="price-item">
                        <span class="price-item-label">양면테이프:</span>
                        <span class="price-item-value">${tapePrice.toLocaleString()}원</span>
                    </div>
                </div>
            `;

            // VAT 포함 총액 업데이트
            const totalWithVat = Math.round(totalPrice * 1.1);
            const vatLineHTML = `
                <div class="price-item final" style="margin-top: 5px; font-weight: bold;">
                    <span class="price-item-label">부가세 포함:</span>
                    <span class="price-item-value">${totalWithVat.toLocaleString()}원</span>
                </div>
            `;

            detailsHTML += tapeLineHTML + vatLineHTML;
        }

        priceDetails.innerHTML = detailsHTML;
    } else {
        // 양면테이프 가격 업데이트
        updateExistingTapePrice(priceDetails, tapePrice, totalPrice);
    }
}

// 기존 양면테이프 가격 업데이트
function updateExistingTapePrice(priceDetails, tapePrice, totalPrice) {
    let detailsHTML = priceDetails.innerHTML;

    if (tapePrice > 0) {
        // 양면테이프 가격 업데이트
        detailsHTML = detailsHTML.replace(
            /양면테이프:.*?<span class="price-item-value">.*?<\/span>/,
            `양면테이프:</span><span class="price-item-value">${tapePrice.toLocaleString()}원</span>`
        );

        // VAT 포함 총액 업데이트
        const totalWithVat = Math.round(totalPrice * 1.1);
        detailsHTML = detailsHTML.replace(
            /부가세 포함:.*?<span class="price-item-value">.*?<\/span>/,
            `부가세 포함:</span><span class="price-item-value">${totalWithVat.toLocaleString()}원</span>`
        );
    } else {
        // 양면테이프 섹션 제거
        detailsHTML = detailsHTML.replace(
            /<div class="price-breakdown"[^>]*>[\s\S]*?양면테이프[\s\S]*?<\/div>/,
            ''
        );
    }

    priceDetails.innerHTML = detailsHTML;
}

// 폼 제출 시 양면테이프 데이터 포함
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('envelopeForm');
    if (form) {
        // 폼 제출 전 양면테이프 데이터 검증
        form.addEventListener('submit', function(e) {
            const tapeEnabled = document.getElementById('tape_enabled');
            if (tapeEnabled && tapeEnabled.checked) {
                const tapeQuantity = document.getElementById('tape_quantity');
                const tapePrice = document.getElementById('tape_price');

                if (!tapeQuantity.value || tapePrice.value === '0') {
                    e.preventDefault();
                    alert('양면테이프 옵션을 확인해주세요.');
                    return false;
                }
            }
        });
    }
});

// 장바구니 추가 시 양면테이프 데이터 포함
function addToBasketWithTape(uploadedFiles) {
    const formData = new FormData(document.getElementById('envelopeForm'));

    // 양면테이프 데이터 추가
    const tapeEnabled = document.getElementById('tape_enabled');
    if (tapeEnabled && tapeEnabled.checked) {
        formData.append('tape_enabled', '1');
        formData.append('tape_quantity', document.getElementById('tape_quantity').value);
        formData.append('tape_price', document.getElementById('tape_price').value);
    } else {
        formData.append('tape_enabled', '0');
        formData.append('tape_quantity', '');
        formData.append('tape_price', '0');
    }

    // 파일 추가
    if (uploadedFiles && uploadedFiles.length > 0) {
        uploadedFiles.forEach((file, index) => {
            formData.append(`uploaded_files[${index}]`, file);
        });
    }

    // AJAX 요청
    fetch('add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('장바구니에 추가되었습니다.');
            window.location.href = '/shop/basket.php';
        } else {
            alert(data.message || '장바구니 추가 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('장바구니 추가 중 오류가 발생했습니다.');
    });
}

console.log('✅ 양면테이프 계산 시스템 로드 완료');