/**
 * 고객센터 JavaScript
 * Customer Center Interactive Features
 */

// ==================== FAQ 페이지 기능 ====================

// FAQ 아코디언 토글
document.addEventListener('DOMContentLoaded', function() {

    // FAQ 아이템 클릭 이벤트
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');

        if (question) {
            question.addEventListener('click', function() {
                // 현재 아이템이 활성화되어 있는지 확인
                const isActive = item.classList.contains('active');

                // 모든 FAQ 아이템 닫기 (선택사항: 하나만 열리게 하려면 주석 해제)
                // faqItems.forEach(i => i.classList.remove('active'));

                // 현재 아이템 토글
                if (isActive) {
                    item.classList.remove('active');
                } else {
                    item.classList.add('active');
                }
            });
        }
    });

    // 카테고리 필터링
    const categoryTabs = document.querySelectorAll('.category-tab');

    categoryTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const category = this.getAttribute('data-category');

            // 활성 탭 변경
            categoryTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // FAQ 아이템 필터링
            faqItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');

                if (category === 'all' || itemCategory === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }

                // 필터링 시 모든 아이템 닫기
                item.classList.remove('active');
            });
        });
    });

    // FAQ 검색 기능
    const searchInput = document.getElementById('faqSearch');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();

            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question h3').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer p').textContent.toLowerCase();

                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';

                    // 검색어가 있고 일치하면 자동으로 열기
                    if (searchTerm.length > 0) {
                        item.classList.add('active');
                    }
                } else {
                    item.style.display = 'none';
                    item.classList.remove('active');
                }
            });

            // 검색 중일 때는 카테고리 필터 해제
            if (searchTerm.length > 0) {
                categoryTabs.forEach(t => t.classList.remove('active'));
            } else {
                // 검색어가 없으면 '전체' 탭 활성화
                const allTab = document.querySelector('.category-tab[data-category="all"]');
                if (allTab) {
                    allTab.classList.add('active');
                }
            }
        });
    }

    // FAQ 피드백 버튼
    const feedbackBtns = document.querySelectorAll('.feedback-btn');

    feedbackBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); // 아코디언 토글 방지

            const faqId = this.getAttribute('data-faq-id');
            const isHelpful = this.classList.contains('helpful');

            // 서버로 피드백 전송 (AJAX)
            sendFaqFeedback(faqId, isHelpful);

            // 버튼 비활성화 및 피드백 표시
            const feedbackContainer = this.parentElement;
            feedbackContainer.innerHTML = `
                <p style="color: #4CAF50; font-weight: 600;">
                    ${isHelpful ? '👍 피드백 감사합니다!' : '👎 더 나은 답변을 준비하겠습니다.'}
                </p>
            `;
        });
    });

    // ==================== 입금계좌 페이지 기능 ====================

    // 계좌번호 복사 기능
    const copyBtns = document.querySelectorAll('.btn-copy, .btn-copy-sm');

    copyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const accountNumber = this.getAttribute('data-account');

            // 클립보드에 복사
            if (navigator.clipboard) {
                navigator.clipboard.writeText(accountNumber).then(() => {
                    // 복사 성공 피드백
                    const originalText = this.textContent;
                    this.textContent = '✓ 복사완료!';
                    this.style.background = '#4CAF50';
                    this.style.color = '#fff';
                    this.style.borderColor = '#4CAF50';

                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.background = '';
                        this.style.color = '';
                        this.style.borderColor = '';
                    }, 2000);
                }).catch(err => {
                    alert('복사 실패: ' + err);
                });
            } else {
                // 구형 브라우저 대비 fallback
                const textArea = document.createElement('textarea');
                textArea.value = accountNumber;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();

                try {
                    document.execCommand('copy');
                    alert('계좌번호가 복사되었습니다: ' + accountNumber);
                } catch (err) {
                    alert('복사 실패: ' + err);
                }

                document.body.removeChild(textArea);
            }
        });
    });

    // ==================== 공지사항 페이지 기능 ====================

    // 공지사항 검색 (페이지가 있을 경우)
    const noticeSearchInput = document.getElementById('noticeSearch');

    if (noticeSearchInput) {
        noticeSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const noticeItems = document.querySelectorAll('.notice-item');

            noticeItems.forEach(item => {
                const title = item.querySelector('.notice-title').textContent.toLowerCase();
                const content = item.querySelector('.notice-preview') ?
                    item.querySelector('.notice-preview').textContent.toLowerCase() : '';

                if (title.includes(searchTerm) || content.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // ==================== 문의하기 폼 검증 ====================

    const inquiryForm = document.getElementById('inquiryForm');

    if (inquiryForm) {
        inquiryForm.addEventListener('submit', function(e) {
            // 필수 필드 검증
            const name = document.getElementById('inquiry_name');
            const email = document.getElementById('inquiry_email');
            const subject = document.getElementById('inquiry_subject');
            const message = document.getElementById('inquiry_message');

            let isValid = true;
            let errorMessage = '';

            if (!name || name.value.trim() === '') {
                errorMessage += '이름을 입력해주세요.\n';
                isValid = false;
            }

            if (!email || email.value.trim() === '') {
                errorMessage += '이메일을 입력해주세요.\n';
                isValid = false;
            } else if (!isValidEmail(email.value)) {
                errorMessage += '올바른 이메일 형식이 아닙니다.\n';
                isValid = false;
            }

            if (!subject || subject.value.trim() === '') {
                errorMessage += '제목을 입력해주세요.\n';
                isValid = false;
            }

            if (!message || message.value.trim() === '') {
                errorMessage += '문의내용을 입력해주세요.\n';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
                return false;
            }
        });
    }

    // ==================== 파일 업로드 미리보기 ====================

    const fileInputs = document.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();

                // 파일 크기 제한 (100MB)
                if (fileSize > 100) {
                    alert('파일 크기는 100MB를 초과할 수 없습니다.\n현재 파일 크기: ' + fileSize + 'MB');
                    this.value = '';
                    return;
                }

                // 허용된 파일 확장자 체크
                const allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'ai', 'psd'];
                if (!allowedExts.includes(fileExt)) {
                    alert('지원하지 않는 파일 형식입니다.\n허용 형식: ' + allowedExts.join(', '));
                    this.value = '';
                    return;
                }

                // 파일 정보 표시
                const fileInfo = this.parentElement.querySelector('.file-info');
                if (fileInfo) {
                    fileInfo.textContent = `${fileName} (${fileSize}MB)`;
                }
            }
        });
    });

});

// ==================== 유틸리티 함수 ====================

/**
 * FAQ 피드백 전송
 */
function sendFaqFeedback(faqId, isHelpful) {
    // AJAX로 서버에 피드백 전송
    fetch('/api/faq_feedback.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            faq_id: faqId,
            is_helpful: isHelpful
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Feedback sent:', data);
    })
    .catch(error => {
        console.error('Error sending feedback:', error);
    });
}

/**
 * 이메일 형식 검증
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * 전화번호 형식 자동 변환
 */
function formatPhoneNumber(input) {
    var d = input.value.replace(/\D/g, '');
    var formatted = '';

    if (d.length === 0) {
        formatted = '';
    } else if (d.substring(0, 2) === '02') {
        // 02 지역번호 (9~10자리)
        if (d.length <= 2) formatted = d;
        else if (d.length <= 5) formatted = d.substring(0,2) + '-' + d.substring(2);
        else if (d.length <= 9) formatted = d.substring(0,2) + '-' + d.substring(2, d.length-4) + '-' + d.substring(d.length-4);
        else formatted = d.substring(0,2) + '-' + d.substring(2,6) + '-' + d.substring(6,10);
    } else {
        // 010/0XX 번호 (10~11자리)
        if (d.length <= 3) formatted = d;
        else if (d.length <= 7) formatted = d.substring(0,3) + '-' + d.substring(3);
        else if (d.length <= 11) formatted = d.substring(0,3) + '-' + d.substring(3, d.length-4) + '-' + d.substring(d.length-4);
        else formatted = d.substring(0,3) + '-' + d.substring(3,7) + '-' + d.substring(7,11);
    }

    input.value = formatted;
}

/**
 * 숫자만 입력 허용
 */
function onlyNumbers(input) {
    input.value = input.value.replace(/[^\d]/g, '');
}

/**
 * 날짜 포맷팅 (YYYY-MM-DD)
 */
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * 페이지 상단으로 스크롤
 */
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// ==================== 인쇄 가이드 툴팁 ====================

/**
 * 툴팁 표시
 */
function showTooltip(element, message) {
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = message;
    tooltip.style.position = 'absolute';
    tooltip.style.background = '#333';
    tooltip.style.color = '#fff';
    tooltip.style.padding = '8px 12px';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '13px';
    tooltip.style.zIndex = '1000';
    tooltip.style.whiteSpace = 'nowrap';

    document.body.appendChild(tooltip);

    const rect = element.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
    tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';

    element.addEventListener('mouseleave', function() {
        tooltip.remove();
    }, { once: true });
}

// ==================== 로컬 스토리지 활용 ====================

/**
 * 최근 본 FAQ 저장
 */
function saveRecentFaq(faqId) {
    let recentFaqs = JSON.parse(localStorage.getItem('recentFaqs') || '[]');

    // 중복 제거
    recentFaqs = recentFaqs.filter(id => id !== faqId);

    // 최신 항목을 맨 앞에 추가
    recentFaqs.unshift(faqId);

    // 최대 10개까지만 저장
    if (recentFaqs.length > 10) {
        recentFaqs = recentFaqs.slice(0, 10);
    }

    localStorage.setItem('recentFaqs', JSON.stringify(recentFaqs));
}

/**
 * 최근 본 FAQ 불러오기
 */
function loadRecentFaqs() {
    const recentFaqs = JSON.parse(localStorage.getItem('recentFaqs') || '[]');
    return recentFaqs;
}

// ==================== 페이지 로드 시 초기화 ====================

// FAQ 페이지에서 최근 본 항목 표시
if (document.querySelector('.faq-list')) {
    const recentFaqs = loadRecentFaqs();
    // 최근 본 FAQ 표시 로직 (옵션)
}

// 스크롤 시 사이드바 고정
window.addEventListener('scroll', function() {
    const sidebar = document.querySelector('.customer-sidebar');
    if (sidebar && window.innerWidth > 1024) {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > 100) {
            sidebar.style.position = 'sticky';
            sidebar.style.top = '20px';
        }
    }
});

// 인쇄 페이지 감지 및 스타일 조정
if (window.matchMedia) {
    const mediaQueryList = window.matchMedia('print');
    mediaQueryList.addListener(function(mql) {
        if (mql.matches) {
            // 인쇄 시 사이드바 숨기기
            const sidebar = document.querySelector('.customer-sidebar');
            if (sidebar) {
                sidebar.style.display = 'none';
            }
        }
    });
}

console.log('Customer Center JS loaded successfully');
