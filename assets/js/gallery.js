/**
 * 갤러리 시스템 JavaScript v1.0
 * 썸네일 인터랙션, 모달 팝업, 페이지네이션, 접근성 지원
 */

(function() {
    'use strict';
    
    // 상태 관리
    let currentPage = 1;
    let totalPages = 1;
    let currentProduct = '';
    let isModalOpen = false;
    let isLoading = false;
    
    // DOM 준비 완료 대기
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    /**
     * 초기화
     */
    function init() {
        initThumbnailEvents();
        initModalEvents();
        initKeyboardEvents();
        initPaginationEvents();
        initHoverZoom();
    }
    
    /**
     * 썸네일 이벤트 초기화
     */
    function initThumbnailEvents() {
        // 썸네일 클릭 이벤트 (이벤트 위임)
        document.addEventListener('click', function(e) {
            const thumb = e.target.closest('.gallery-thumb');
            if (thumb) {
                handleThumbnailClick(thumb);
            }
        });
        
        // 썸네일 키보드 이벤트
        document.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('gallery-thumb')) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    handleThumbnailClick(e.target);
                }
            }
        });
    }
    
    /**
     * 썸네일 클릭 처리
     */
    function handleThumbnailClick(thumb) {
        const container = thumb.closest('.gallery-container');
        if (!container) return;
        
        // "샘플 더보기" 썸네일 버튼 클릭 처리
        if (thumb.classList.contains('gallery-more-thumb')) {
            const product = thumb.dataset.product;
            if (product) {
                console.log('샘플 더보기 썸네일 클릭:', product);
                openModal(product);
                return;
            }
        }
        
        const mainImg = container.querySelector('.gallery-main-img');
        if (!mainImg) return;
        
        // 메인 이미지 교체
        const newSrc = thumb.dataset.src;
        const newAlt = thumb.dataset.alt;
        
        if (newSrc) {
            // 기존 호버 상태 초기화
            resetImageToOriginal(mainImg);
            
            // 페이드 효과
            mainImg.style.opacity = '0';
            
            setTimeout(function() {
                mainImg.src = newSrc;
                mainImg.alt = newAlt;
                mainImg.setAttribute('aria-label', '메인 이미지: ' + newAlt);
                mainImg.style.opacity = '1';
                
                // 호버 데이터 완전 초기화
                resetImageHoverData(mainImg);
            }, 150);
        }
        
        // active 클래스 업데이트 (샘플 더보기 버튼은 제외)
        if (!thumb.classList.contains('gallery-more-thumb')) {
            container.querySelectorAll('.gallery-thumb:not(.gallery-more-thumb)').forEach(function(t) {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            thumb.classList.add('active');
            thumb.setAttribute('aria-selected', 'true');
        }
    }
    
    /**
     * 모달 이벤트 초기화
     */
    function initModalEvents() {
        // 더보기 버튼 클릭
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('gallery-more-btn')) {
                e.preventDefault();
                console.log('Gallery more button clicked!', e.target);
                const product = e.target.dataset.product;
                console.log('Product:', product);
                openModal(product);
            }
        });
        
        // 모달 닫기 버튼
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('gallery-modal-close')) {
                closeModal();
            }
        });
        
        // 백드롭 클릭으로 닫기
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('gallery-modal-backdrop')) {
                closeModal();
            }
        });
        
        // 모달 내 이미지 클릭 시 라이트박스 열기
        document.addEventListener('click', function(e) {
            if (e.target.parentElement && e.target.parentElement.id === 'unifiedGalleryGrid' && e.target.tagName === 'IMG') {
                e.preventDefault();
                openLightbox(e.target.src, e.target.alt);
            }
        });
    }
    
    /**
     * 키보드 이벤트 초기화
     */
    function initKeyboardEvents() {
        document.addEventListener('keydown', function(e) {
            if (!isModalOpen) return;
            
            switch(e.key) {
                case 'Escape':
                    closeModal();
                    break;
                case 'ArrowLeft':
                    if (currentPage > 1) {
                        loadModalPage(currentProduct, currentPage - 1);
                    }
                    break;
                case 'ArrowRight':
                    if (currentPage < totalPages) {
                        loadModalPage(currentProduct, currentPage + 1);
                    }
                    break;
            }
        });
    }
    
    /**
     * 페이지네이션 이벤트 초기화
     */
    function initPaginationEvents() {
        // 이전 페이지
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('pagination-prev')) {
                if (currentPage > 1 && !isLoading) {
                    loadModalPage(currentProduct, currentPage - 1);
                }
            }
        });
        
        // 다음 페이지
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('pagination-next')) {
                if (currentPage < totalPages && !isLoading) {
                    loadModalPage(currentProduct, currentPage + 1);
                }
            }
        });
    }
    
    /**
     * 모달 열기
     */
    function openModal(product) {
        const modal = document.getElementById('unifiedGalleryModal');
        if (!modal) {
            console.error('갤러리 모달 요소를 찾을 수 없습니다.');
            return;
        }
        
        currentProduct = product;
        isModalOpen = true;
        
        // 모달 표시
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        
        // 바디 스크롤 방지
        document.body.style.overflow = 'hidden';
        
        // 제목 업데이트
        const titleElement = document.getElementById('gallery-modal-title');
        if (titleElement) {
            const productNames = {
                'inserted': '전단지',
                'namecard': '명함',
                'littleprint': '포스터',
                'merchandisebond': '상품권',
                'envelope': '봉투',
                'cadarok': '카탈로그',
                'ncrflambeau': '양식지',
                'msticker': '자석스티커'
            };
            titleElement.textContent = (productNames[product] || '갤러리') + ' 샘플 갤러리';
        }
        
        // 첫 페이지 로드
        loadModalPage(product, 1);
        
        // 포커스 이동
        const closeBtn = modal.querySelector('.gallery-modal-close');
        if (closeBtn) {
            closeBtn.focus();
        }
    }
    
    /**
     * 모달 닫기
     */
    function closeModal() {
        const modal = document.getElementById('unifiedGalleryModal');
        if (!modal) return;
        
        isModalOpen = false;
        
        // 모달 숨기기
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        
        // 바디 스크롤 복원
        document.body.style.overflow = '';
        
        // 포커스 복원 (더보기 버튼으로)
        const moreBtn = document.querySelector('.gallery-more-btn[data-product="' + currentProduct + '"]');
        if (moreBtn) {
            moreBtn.focus();
        }
    }
    
    /**
     * 모달 페이지 로드 (AJAX)
     */
    function loadModalPage(product, page) {
        if (isLoading) return;
        isLoading = true;
        
        const grid = document.getElementById('unifiedGalleryGrid');
        if (!grid) {
            isLoading = false;
            return;
        }
        
        // 로딩 표시
        grid.innerHTML = '<div class="gallery-loading">이미지를 불러오는 중...</div>';
        
        // AJAX 요청 (상대 경로 사용)
        fetch('../../api/gallery_items.php?product=' + encodeURIComponent(product) + 
              '&page=' + page + '&per_page=12')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('네트워크 응답 오류: ' + response.status);
                }
                return response.json();
            })
            .then(function(data) {
                // 그리드 업데이트
                grid.innerHTML = '';
                
                // API 응답 구조에 맞게 data.data.items 또는 data.items 사용
                const items = data.data ? data.data.items : data.items;
                
                if (items && items.length > 0) {
                    items.forEach(function(item, index) {
                        const img = document.createElement('img');
                        img.src = item.src;
                        img.alt = item.alt;
                        img.loading = 'lazy';
                        img.tabIndex = 0;
                        img.setAttribute('role', 'button');
                        img.setAttribute('aria-label', '이미지 ' + (index + 1) + ': ' + item.alt + ' (클릭하여 확대보기)');
                        img.title = '클릭하여 확대보기';
                        
                        // 이미지 로드 에러 처리 (SVG 플레이스홀더)
                        img.onerror = function() {
                            this.src = 'data:image/svg+xml;base64,' + btoa(
                                '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="150" viewBox="0 0 200 150">' +
                                '<rect width="200" height="150" fill="#f5f5f5" stroke="#ddd" stroke-width="1"/>' +
                                '<text x="100" y="70" font-family="Arial" font-size="12" fill="#999" text-anchor="middle">이미지 준비중</text>' +
                                '<text x="100" y="85" font-family="Arial" font-size="10" fill="#bbb" text-anchor="middle">Image Loading...</text>' +
                                '</svg>'
                            );
                            this.alt = '이미지 로드 실패';
                        };
                        
                        grid.appendChild(img);
                    });
                } else {
                    grid.innerHTML = '<div class="gallery-error">이미지를 불러올 수 없습니다.</div>';
                }
                
                // 페이지네이션 업데이트 - API 응답 구조에 맞게
                const pageData = data.data || data;
                currentPage = pageData.currentPage || 1;
                totalPages = pageData.totalPages || 1;
                updatePagination();
            })
            .catch(function(error) {
                console.error('갤러리 로드 오류:', error);
                grid.innerHTML = '<div class="gallery-error">이미지를 불러오는 중 오류가 발생했습니다.</div>';
            })
            .finally(function() {
                isLoading = false;
            });
    }
    
    /**
     * 페이지네이션 UI 업데이트
     */
    function updatePagination() {
        // 현재 페이지 표시
        const currentPageElement = document.querySelector('.current-page');
        if (currentPageElement) {
            currentPageElement.textContent = currentPage;
        }
        
        // 전체 페이지 표시
        const totalPagesElement = document.querySelector('.total-pages');
        if (totalPagesElement) {
            totalPagesElement.textContent = totalPages;
        }
        
        // 버튼 상태 업데이트
        const prevBtn = document.querySelector('.pagination-prev');
        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
            prevBtn.setAttribute('aria-disabled', currentPage <= 1);
        }
        
        const nextBtn = document.querySelector('.pagination-next');
        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages;
            nextBtn.setAttribute('aria-disabled', currentPage >= totalPages);
        }
    }
    
    /**
     * 이미지 지연 로딩 (선택사항)
     */
    function setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            observer.unobserve(img);
                        }
                    }
                });
            }, {
                rootMargin: '50px'
            });
            
            // 지연 로딩 적용
            document.querySelectorAll('img[data-src]').forEach(function(img) {
                imageObserver.observe(img);
            });
        }
    }
    
    // 지연 로딩 초기화
    setupLazyLoading();
    
    /**
     * 라이트박스 열기
     */
    function openLightbox(imageSrc, imageAlt) {
        // 기존 라이트박스가 있으면 제거
        const existingLightbox = document.getElementById('gallery-lightbox');
        if (existingLightbox) {
            existingLightbox.remove();
        }
        
        // 라이트박스 HTML 생성 (CSS 클래스 사용)
        const lightbox = document.createElement('div');
        lightbox.id = 'gallery-lightbox';
        lightbox.className = 'gallery-lightbox';
        lightbox.innerHTML = `
            <div class="gallery-lightbox-backdrop"></div>
            <div class="gallery-lightbox-content">
                <img src="${imageSrc}" alt="${imageAlt}" class="gallery-lightbox-image">
                <button class="gallery-lightbox-close" aria-label="닫기">&times;</button>
                <div class="gallery-lightbox-info">
                    <p>${imageAlt}</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(lightbox);
        
        // 바디 스크롤 방지
        document.body.style.overflow = 'hidden';
        
        // 이벤트 리스너 추가
        const backdrop = lightbox.querySelector('.gallery-lightbox-backdrop');
        const closeBtn = lightbox.querySelector('.gallery-lightbox-close');
        const image = lightbox.querySelector('.gallery-lightbox-image');
        
        backdrop.addEventListener('click', closeLightbox);
        closeBtn.addEventListener('click', closeLightbox);
        image.addEventListener('click', closeLightbox); // 큰 이미지 클릭 시 닫기
        
        // ESC 키로 닫기
        const handleEscape = function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        };
        document.addEventListener('keydown', handleEscape);
        
        // 정리 함수를 lightbox 객체에 저장
        lightbox._cleanup = function() {
            document.removeEventListener('keydown', handleEscape);
        };
        
        // 포커스 이동
        closeBtn.focus();
    }
    
    /**
     * 라이트박스 닫기
     */
    function closeLightbox() {
        const lightbox = document.getElementById('gallery-lightbox');
        if (!lightbox) return;
        
        // 정리 작업
        if (lightbox._cleanup) {
            lightbox._cleanup();
        }
        
        // 바디 스크롤 복원
        document.body.style.overflow = '';
        
        // 라이트박스 제거
        lightbox.remove();
        
        // 포커스를 모달의 그리드로 복원
        const modalGrid = document.getElementById('modal-gallery-grid');
        if (modalGrid) {
            modalGrid.focus();
        }
    }
    
    // 라이트박스 스타일은 gallery.css에서 관리됨
    
    /**
     * 호버 줌 이벤트 초기화 (상품권 방식 - 부드러운 backgroundPosition + backgroundSize)
     */
    function initHoverZoom() {
        console.log('InitHoverZoom 호출됨'); // 디버그 로그
        
        // 이미지에 마우스 진입 시
        document.addEventListener('mouseenter', function(e) {
            if (e.target && e.target.classList && e.target.classList.contains('gallery-main-img')) {
                console.log('마우스 진입:', e.target); // 디버그 로그
                initImageForHover(e.target);
            }
        }, true);
        
        // 마우스 움직임 추적
        document.addEventListener('mousemove', function(e) {
            if (e.target && e.target.classList && e.target.classList.contains('gallery-main-img')) {
                handleImageHover(e);
            }
        });
        
        // 마우스 벗어날 때
        document.addEventListener('mouseleave', function(e) {
            if (e.target && e.target.classList && e.target.classList.contains('gallery-main-img')) {
                console.log('마우스 벗어남:', e.target); // 디버그 로그
                resetImageTransform(e.target);
            }
        }, true);
        
        // 부드러운 애니메이션 시작
        startSmoothAnimation();
    }
    
    /**
     * 이미지 호버 초기화
     */
    function initImageForHover(img) {
        if (img._hoverInitialized) return;
        
        // 호버 데이터 초기화
        img._hoverData = {
            currentX: 50,
            currentY: 50,
            currentSize: 100,
            targetX: 50,
            targetY: 50,
            targetSize: 100,
            isHovering: false
        };
        
        // 이미지를 background로 변환
        convertToBackgroundMode(img);
        img._hoverInitialized = true;
        
        console.log('이미지 호버 초기화 완료:', img.src);
    }
    
    /**
     * 이미지 호버 처리 (상품권 방식 - backgroundPosition 기반)
     */
    function handleImageHover(event) {
        const img = event.target;
        if (!img._hoverData) return;
        
        const rect = img.getBoundingClientRect();
        
        // 마우스 위치를 이미지 내 상대 좌표로 변환 (0-100%)
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;
        
        // 경계값 제한 (0-100%)
        const targetX = Math.max(0, Math.min(100, x));
        const targetY = Math.max(0, Math.min(100, y));
        
        // 타겟 위치 및 크기 설정
        img._hoverData.targetX = targetX;
        img._hoverData.targetY = targetY;
        img._hoverData.targetSize = 135; // 135% 확대
        img._hoverData.isHovering = true;
    }
    
    /**
     * 이미지를 background 모드로 변환
     */
    function convertToBackgroundMode(img) {
        if (img._isBackgroundMode) return;
        
        // 현재 이미지 소스를 background로 설정
        const imgSrc = img.src;
        const imgAlt = img.alt;
        
        console.log('Background 모드 변환:', imgSrc); // 디버그 로그
        
        // background 스타일 설정 - contain으로 전체 이미지 표시
        img.style.backgroundImage = `url('${imgSrc}')`;
        img.style.backgroundSize = 'contain';  // cover -> contain으로 변경: 전체 이미지 표시
        img.style.backgroundPosition = '50% 50%';
        img.style.backgroundRepeat = 'no-repeat';
        
        // 원본 img 요소 숨기기 대신 투명하게 만들기
        img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; // 1x1 투명 gif
        img.style.transition = 'none';
        
        // 접근성을 위한 속성 유지
        img.setAttribute('aria-label', '메인 이미지: ' + imgAlt);
        img._isBackgroundMode = true;
        img._originalSrc = imgSrc;
        img._originalAlt = imgAlt;
        
        console.log('Background 모드 변환 완료');
    }
    
    /**
     * 이미지 변형 초기화 (호버 해제)
     */
    function resetImageTransform(img) {
        if (!img._hoverData) return;
        
        // 타겟을 원래 상태로 되돌리기
        img._hoverData.targetX = 50;
        img._hoverData.targetY = 50;
        img._hoverData.targetSize = 100;
        img._hoverData.isHovering = false;
    }
    
    /**
     * 부드러운 애니메이션 (상품권 방식 - requestAnimationFrame + lerp)
     */
    function startSmoothAnimation() {
        function animate() {
            // 모든 갤러리 메인 이미지에 대해 애니메이션 적용
            const galleryImages = document.querySelectorAll('.gallery-main-img');
            
            galleryImages.forEach(img => {
                if (!img._hoverData) return;
                
                const data = img._hoverData;
                
                // 매우 부드러운 추적 (0.08 lerp 계수 - 상품권과 동일)
                data.currentX += (data.targetX - data.currentX) * 0.08;
                data.currentY += (data.targetY - data.currentY) * 0.08;
                data.currentSize += (data.targetSize - data.currentSize) * 0.08;
                
                // background 모드인 경우에만 적용
                if (img._isBackgroundMode) {
                    img.style.backgroundPosition = `${data.currentX}% ${data.currentY}%`;
                    
                    if (data.currentSize > 100.1) {
                        img.style.backgroundSize = `${data.currentSize}%`;
                    } else {
                        img.style.backgroundSize = 'contain';  // cover -> contain으로 변경: 원본 상태에서도 전체 이미지 표시
                    }
                }
                
                // 호버가 끝나고 원래 상태로 돌아왔을 때 정리
                if (!data.isHovering && Math.abs(data.currentSize - 100) < 0.1) {
                    restoreOriginalMode(img);
                }
            });
            
            requestAnimationFrame(animate);
        }
        
        animate();
    }
    
    /**
     * 원본 이미지 모드로 복원
     */
    function restoreOriginalMode(img) {
        if (!img._isBackgroundMode) return;
        
        console.log('원본 모드 복원:', img._originalSrc); // 디버그 로그
        
        // background 스타일 제거
        img.style.backgroundImage = '';
        img.style.backgroundSize = '';
        img.style.backgroundPosition = '';
        img.style.backgroundRepeat = '';
        
        // 원본 이미지 표시 복원
        img.src = img._originalSrc;
        img.alt = img._originalAlt;
        
        // 플래그 리셋
        img._isBackgroundMode = false;
        
        // 호버 데이터 정리
        if (img._hoverData) {
            img._hoverData.currentX = 50;
            img._hoverData.currentY = 50;
            img._hoverData.currentSize = 100;
        }
        
        console.log('원본 모드 복원 완료');
    }
    
    /**
     * 이미지를 완전히 원본 상태로 초기화 (썸네일 클릭 시 사용)
     */
    function resetImageToOriginal(img) {
        if (!img) return;
        
        console.log('이미지 완전 초기화:', img.src);
        
        // background 스타일 제거
        img.style.backgroundImage = '';
        img.style.backgroundSize = '';
        img.style.backgroundPosition = '';
        img.style.backgroundRepeat = '';
        img.style.transition = '';
        
        // 모든 플래그 리셋
        img._isBackgroundMode = false;
        img._hoverInitialized = false;
        img._originalSrc = '';
        img._originalAlt = '';
        
        // 호버 데이터 완전 제거
        img._hoverData = null;
    }
    
    /**
     * 호버 데이터 초기화 (새 이미지 로드 후 사용)
     */
    function resetImageHoverData(img) {
        if (!img) return;
        
        console.log('호버 데이터 초기화:', img.src);
        
        // 모든 플래그 리셋
        img._isBackgroundMode = false;
        img._hoverInitialized = false;
        img._originalSrc = '';
        img._originalAlt = '';
        
        // 호버 데이터 완전 제거
        img._hoverData = null;
        
        // 스타일 초기화
        img.style.backgroundImage = '';
        img.style.backgroundSize = '';
        img.style.backgroundPosition = '';
        img.style.backgroundRepeat = '';
    }

    // 전역 함수로 노출 (외부에서 접근 가능하도록)
    window.openGalleryModal = openModal;
    window.closeGalleryModal = closeModal;
    window.openGalleryLightbox = openLightbox;
    window.closeGalleryLightbox = closeLightbox;
    
})();

/**
 * 갤러리 시스템 모니터링 (DevOps 요구사항)
 */
(function() {
    'use strict';
    
    // 이미지 404 에러 추적
    window.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG') {
            const isGalleryImage = e.target.closest('.gallery-container') || 
                                   e.target.closest('.gallery-modal');
            
            if (isGalleryImage) {
                console.error('[Gallery] Image 404:', e.target.src);
                
                // 모니터링 서비스로 전송 (예: Google Analytics)
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'gallery_image_error', {
                        'event_category': 'Gallery',
                        'event_label': e.target.src
                    });
                }
            }
        }
    }, true);

})();