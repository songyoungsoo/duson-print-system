/**
 * 품목별 갤러리 공통 JavaScript
 * 이미지 레이지 로딩, 필터링, 검색 등
 */

class GalleryManager {
    constructor() {
        this.currentPage = 1;
        this.isLoading = false;
        this.init();
    }

    init() {
        this.setupLazyLoading();
        this.setupSearch();
        this.setupImageModal();
        this.setupKeyboardNavigation();
        this.setupInfiniteScroll();
    }

    // 이미지 레이지 로딩
    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            document.querySelectorAll('img[data-src]').forEach(img => {
                this.loadImage(img);
            });
        }
    }

    loadImage(img) {
        const src = img.dataset.src;
        if (!src) return;

        img.src = src;
        img.onload = () => {
            img.classList.remove('loading');
            img.classList.add('loaded');
        };
        img.onerror = () => {
            img.classList.remove('loading');
            img.classList.add('error');
            img.alt = '이미지를 불러올 수 없습니다';
        };
    }

    // 검색 기능
    setupSearch() {
        const searchForm = document.querySelector('.search-box form');
        const searchInput = document.querySelector('.search-box input[type="text"]');
        
        if (!searchForm || !searchInput) return;

        // 검색 입력 디바운싱
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.performSearch(e.target.value);
            }, 500);
        });

        // 엔터키 검색
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch(searchInput.value);
        });
    }

    performSearch(query) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('search', query);
        urlParams.set('page', '1');
        
        window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
    }

    // 이미지 모달 (간단한 오버레이)
    setupImageModal() {
        document.addEventListener('click', (e) => {
            const img = e.target;
            if (img.tagName === 'IMG' && img.closest('.item-image')) {
                this.showImageModal(img);
            }
        });
    }

    showImageModal(img) {
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.innerHTML = `
            <div class="modal-overlay" onclick="this.parentElement.remove()">
                <div class="modal-content" onclick="event.stopPropagation()">
                    <img src="${img.src}" alt="${img.alt}">
                    <button class="modal-close" onclick="this.closest('.image-modal').remove()">✕</button>
                </div>
            </div>
        `;

        // 모달 스타일
        const style = document.createElement('style');
        style.textContent = `
            .image-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 10000;
                background: rgba(0, 0, 0, 0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                animation: fadeIn 0.3s ease;
            }
            .modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .modal-content {
                position: relative;
                max-width: 90vw;
                max-height: 90vh;
            }
            .modal-content img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
                border-radius: 8px;
            }
            .modal-close {
                position: absolute;
                top: -40px;
                right: -40px;
                width: 32px;
                height: 32px;
                background: rgba(255, 255, 255, 0.9);
                border: none;
                border-radius: 50%;
                cursor: pointer;
                font-size: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(modal);

        // ESC 키로 닫기
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    // 키보드 네비게이션
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // 입력 필드에서는 무시
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.navigatePage(-1);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.navigatePage(1);
                    break;
                case '/':
                    e.preventDefault();
                    this.focusSearch();
                    break;
            }
        });
    }

    navigatePage(direction) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = parseInt(urlParams.get('page') || '1');
        const newPage = Math.max(1, currentPage + direction);
        
        // 최대 페이지 확인
        const pagination = document.querySelector('.pagination');
        if (pagination) {
            const lastPageLink = pagination.querySelector('.page-num:last-of-type');
            if (lastPageLink) {
                const maxPage = parseInt(lastPageLink.textContent);
                if (newPage > maxPage) return;
            }
        }

        urlParams.set('page', newPage.toString());
        window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
    }

    focusSearch() {
        const searchInput = document.querySelector('.search-box input[type="text"]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    // 무한 스크롤 (옵션)
    setupInfiniteScroll() {
        if (!this.shouldEnableInfiniteScroll()) return;

        const observer = new IntersectionObserver((entries) => {
            const lastEntry = entries[0];
            if (lastEntry.isIntersecting && !this.isLoading) {
                this.loadNextPage();
            }
        });

        const footer = document.querySelector('.gallery-footer');
        if (footer) {
            observer.observe(footer);
        }
    }

    shouldEnableInfiniteScroll() {
        // URL 파라미터로 무한스크롤 활성화 여부 결정
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('infinite') === 'true';
    }

    async loadNextPage() {
        if (this.isLoading) return;

        this.isLoading = true;
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = parseInt(urlParams.get('page') || '1');
        const nextPage = currentPage + 1;

        try {
            urlParams.set('page', nextPage.toString());
            const response = await fetch(`${window.location.pathname}?${urlParams.toString()}`);
            const text = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newItems = doc.querySelectorAll('.gallery-item');
            
            if (newItems.length > 0) {
                const gallery = document.querySelector('.gallery-grid');
                newItems.forEach(item => {
                    gallery.appendChild(item);
                });
                
                // 새로운 이미지에 대해 레이지 로딩 적용
                this.setupLazyLoading();
                
                // URL 업데이트 (히스토리에 추가하지 않음)
                window.history.replaceState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
            }
        } catch (error) {
            console.error('Failed to load next page:', error);
        } finally {
            this.isLoading = false;
        }
    }

    // 필터 애니메이션
    animateFilterChange() {
        const items = document.querySelectorAll('.gallery-item');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.5s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    // 유틸리티 함수들
    static debounce(func, wait) {
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

    static throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// 갤러리 관리자 초기화
document.addEventListener('DOMContentLoaded', () => {
    window.galleryManager = new GalleryManager();
});

// 전역 함수들 (PHP에서 호출)
function openDetail(orderNo) {
    window.open(`/MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=${orderNo}`, 
                `detail_${orderNo}`,
                'width=1000,height=800,scrollbars=yes,resizable=yes');
}

function openPopup(orderNo) {
    window.open(`category_gallery_popup.php?no=${orderNo}`,
                `popup_${orderNo}`,
                'width=1200,height=900,scrollbars=yes,resizable=yes');
}

// PWA 지원 (옵션)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/gallery/sw.js')
            .then((registration) => {
                console.log('SW registered: ', registration);
            })
            .catch((registrationError) => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}