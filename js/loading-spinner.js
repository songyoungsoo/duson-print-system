/**
 * 두손기획인쇄 로딩 스피너 JavaScript
 * 파일 업로드, 주문하기 등 느린 동작에 로딩 표시
 */

// 로딩 오버레이 표시
function showDusonLoading(message) {
    const overlay = document.getElementById('dusonLoadingOverlay');
    if (overlay) {
        overlay.classList.add('active');

        // 메시지가 있으면 표시
        const msgEl = overlay.querySelector('.duson-loading-message');
        if (msgEl && message) {
            msgEl.textContent = message;
            msgEl.style.display = 'block';
        } else if (msgEl) {
            msgEl.style.display = 'none';
        }
    }
}

// 로딩 오버레이 숨김
function hideDusonLoading() {
    const overlay = document.getElementById('dusonLoadingOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// 페이지 로드 시 로딩 오버레이 HTML 동적 추가
document.addEventListener('DOMContentLoaded', function() {
    // 이미 존재하면 추가하지 않음
    if (document.getElementById('dusonLoadingOverlay')) {
        return;
    }

    const loadingHTML = `
        <div id="dusonLoadingOverlay" class="duson-loading-overlay">
            <div class="duson-spinner-container">
                <div class="duson-spinner">
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                    <div class="duson-petal"></div>
                </div>
                <div class="duson-center-logo">
                    <span>두손</span>
                </div>
                <div class="duson-loading-message"></div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', loadingHTML);
});

// 폼 제출 시 로딩 표시 (자동 연결)
function attachLoadingToForms() {
    // 주문 폼에 연결
    const orderForms = document.querySelectorAll('form[action*="ProcessOrder"], form[action*="Order"]');
    orderForms.forEach(form => {
        form.addEventListener('submit', function() {
            showDusonLoading('주문 처리 중...');
        });
    });
}

// 버튼 클릭에 로딩 표시 연결 헬퍼
function attachLoadingToButton(buttonSelector, message) {
    const buttons = document.querySelectorAll(buttonSelector);
    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            showDusonLoading(message || '처리 중...');
        });
    });
}

// AJAX 요청에 로딩 표시 래퍼
function fetchWithLoading(url, options, message) {
    showDusonLoading(message || '로딩 중...');

    return fetch(url, options)
        .then(response => {
            hideDusonLoading();
            return response;
        })
        .catch(error => {
            hideDusonLoading();
            throw error;
        });
}

// XMLHttpRequest 래퍼
function ajaxWithLoading(xhr, message) {
    showDusonLoading(message || '로딩 중...');

    const originalOnload = xhr.onload;
    const originalOnerror = xhr.onerror;

    xhr.onload = function() {
        hideDusonLoading();
        if (originalOnload) originalOnload.apply(this, arguments);
    };

    xhr.onerror = function() {
        hideDusonLoading();
        if (originalOnerror) originalOnerror.apply(this, arguments);
    };

    return xhr;
}

// 전역 함수로 노출
window.showDusonLoading = showDusonLoading;
window.hideDusonLoading = hideDusonLoading;
window.attachLoadingToButton = attachLoadingToButton;
window.fetchWithLoading = fetchWithLoading;
window.ajaxWithLoading = ajaxWithLoading;
