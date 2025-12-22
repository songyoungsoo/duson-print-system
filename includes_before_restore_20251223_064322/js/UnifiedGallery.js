/**
 * 통합 갤러리 컴포넌트 v1.0
 * 모든 제품 페이지에서 재사용 가능한 갤러리 시스템
 * Created: 2025년 12월
 */

class UnifiedGallery {
    constructor(config = {}) {
        // 기본 설정
        this.config = {
            container: '#gallery-section',
            category: 'envelope',
            categoryLabel: '봉투',
            mainImageSize: 500,
            thumbnailCount: 4,
            apiUrl: '/api/get_portfolio_images.php',
            uploadPath: '/uploads/portfolio/',
            perPage: 24,
            animationDuration: 300,
            ...config
        };
        
        // 상태 관리
        this.state = {
            mainImage: null,
            thumbnails: [],
            allImages: [],
            currentPage: 1,
            totalPages: 1,
            popupOpen: false,
            lightboxOpen: false,
            selectedIndex: 0,
            loading: false
        };
        
        // DOM 요소 참조
        this.elements = {};
        
        // 초기화
        this.init();
    }
    
    /**
     * 컴포넌트 초기화
     */
    async init() {
        console.log('UnifiedGallery 초기화 시작');
        console.log('Container:', this.config.container);
        
        this.createHTML();
        console.log('HTML 생성 완료');
        
        // DOM이 생성된 후 잠시 대기
        await new Promise(resolve => setTimeout(resolve, 100));
        
        this.cacheElements();
        console.log('DOM 요소 캐싱 완료:', this.elements);
        
        this.bindEvents();
        console.log('이벤트 바인딩 완료');
        
        await this.loadInitialImages();
    }
    
    /**
     * HTML 구조 생성
     */
    createHTML() {
        const container = document.querySelector(this.config.container);
        console.log('Container 찾기:', this.config.container, container);
        if (!container) {
            console.error('Container를 찾을 수 없습니다:', this.config.container);
            return;
        }
        
        container.innerHTML = `
            <div class="unified-gallery">
                <div class="gallery-title">🖼️ ${this.config.categoryLabel} 샘플 갤러리</div>
                
                <!-- 메인 이미지 영역 -->
                <div class="main-image-container">
                    <div class="main-image-wrapper">
                        <img class="main-image" src="" alt="${this.config.categoryLabel} 샘플">
                        <div class="image-loader">
                            <div class="spinner"></div>
                        </div>
                    </div>
                </div>
                
                <!-- 썸네일 영역 -->
                <div class="thumbnail-container">
                    <div class="thumbnail-grid"></div>
                </div>
                
                <!-- 더보기 버튼 -->
                <div class="more-button-container">
                    <button class="btn-more-gallery">
                        <i class="icon-gallery">📸</i>
                        <span>더 많은 샘플 보기</span>
                    </button>
                </div>
            </div>
        `;
        
        console.log('Main gallery HTML 생성 완료');
        
        // 팝업 갤러리 HTML
        this.createPopupHTML();
        
        // 라이트박스 HTML
        this.createLightboxHTML();
        
        console.log('팝업 및 라이트박스 HTML 생성 완료');
    }
    
    /**
     * 팝업 갤러리 HTML 생성
     */
    createPopupHTML() {
        const popup = document.createElement('div');
        popup.className = 'gallery-popup';
        popup.innerHTML = `
            <div class="popup-overlay"></div>
            <div class="popup-content">
                <div class="popup-header">
                    <h3>${this.config.categoryLabel} 포트폴리오</h3>
                    <button class="popup-close">✕</button>
                </div>
                <div class="popup-body">
                    <div class="popup-gallery-grid"></div>
                    <div class="popup-loader">
                        <div class="spinner"></div>
                    </div>
                </div>
                <div class="popup-footer">
                    <div class="pagination"></div>
                </div>
            </div>
        `;
        document.body.appendChild(popup);
    }
    
    /**
     * 라이트박스 HTML 생성
     */
    createLightboxHTML() {
        const lightbox = document.createElement('div');
        lightbox.className = 'gallery-lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-overlay"></div>
            <div class="lightbox-content">
                <button class="lightbox-close">✕</button>
                <button class="lightbox-prev">◀</button>
                <button class="lightbox-next">▶</button>
                <div class="lightbox-image-wrapper">
                    <img class="lightbox-image" src="" alt="">
                </div>
                <div class="lightbox-caption"></div>
            </div>
        `;
        document.body.appendChild(lightbox);
    }
    
    /**
     * DOM 요소 캐싱
     */
    cacheElements() {
        this.elements = {
            mainImage: document.querySelector('.main-image'),
            mainImageWrapper: document.querySelector('.main-image-wrapper'),
            thumbnailGrid: document.querySelector('.thumbnail-grid'),
            moreButton: document.querySelector('.btn-more-gallery'),
            
            // 팝업 요소
            popup: document.querySelector('.gallery-popup'),
            popupOverlay: document.querySelector('.popup-overlay'),
            popupClose: document.querySelector('.popup-close'),
            popupGrid: document.querySelector('.popup-gallery-grid'),
            popupLoader: document.querySelector('.popup-loader'),
            pagination: document.querySelector('.pagination'),
            
            // 라이트박스 요소
            lightbox: document.querySelector('.gallery-lightbox'),
            lightboxOverlay: document.querySelector('.lightbox-overlay'),
            lightboxClose: document.querySelector('.lightbox-close'),
            lightboxPrev: document.querySelector('.lightbox-prev'),
            lightboxNext: document.querySelector('.lightbox-next'),
            lightboxImage: document.querySelector('.lightbox-image'),
            lightboxCaption: document.querySelector('.lightbox-caption')
        };
    }
    
    /**
     * 이벤트 바인딩
     */
    bindEvents() {
        // 더보기 버튼
        this.elements.moreButton?.addEventListener('click', () => this.openPopup());
        
        // 팝업 닫기
        this.elements.popupClose?.addEventListener('click', () => this.closePopup());
        this.elements.popupOverlay?.addEventListener('click', () => this.closePopup());
        
        // 라이트박스 닫기
        this.elements.lightboxClose?.addEventListener('click', () => this.closeLightbox());
        this.elements.lightboxOverlay?.addEventListener('click', () => this.closeLightbox());
        this.elements.lightboxImage?.addEventListener('click', () => this.closeLightbox());
        
        // 라이트박스 네비게이션
        this.elements.lightboxPrev?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.prevImage();
        });
        this.elements.lightboxNext?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.nextImage();
        });
        
        // 메인 이미지 호버 확대
        this.bindMainImageZoom();
        
        // 키보드 이벤트
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));
    }
    
    /**
     * 초기 이미지 로드 (최근 4개)
     */
    async loadInitialImages() {
        try {
            this.state.loading = true;
            const url = `${this.config.apiUrl}?category=${this.config.category}&per_page=4`;
            console.log('Loading images from:', url);
            const response = await fetch(url);
            const data = await response.json();
            console.log('API Response:', data);
            
            if (data.success && data.data && data.data.length > 0) {
                this.state.thumbnails = data.data;
                this.state.mainImage = data.data[0];
                console.log('Thumbnails loaded:', this.state.thumbnails);
                console.log('Main image:', this.state.mainImage);
                this.renderThumbnails();
                this.updateMainImage();
            } else {
                console.warn('No images found in API response');
            }
        } catch (error) {
            console.error('Failed to load images:', error);
        } finally {
            this.state.loading = false;
        }
    }
    
    /**
     * 썸네일 렌더링
     */
    renderThumbnails() {
        if (!this.elements.thumbnailGrid) {
            console.error('Thumbnail grid element not found');
            return;
        }
        
        console.log('Rendering thumbnails:', this.state.thumbnails);
        
        this.elements.thumbnailGrid.innerHTML = this.state.thumbnails.map((image, index) => {
            const imgSrc = image.thumb || image.thumbnail || image.path || image.image_path;
            console.log(`Thumbnail ${index + 1} src:`, imgSrc);
            return `
                <div class="thumbnail-item" data-index="${index}">
                    <img src="${imgSrc}" alt="${image.title || '샘플 ' + (index + 1)}" 
                         onerror="console.error('Failed to load thumbnail:', this.src)">
                </div>
            `;
        }).join('');
        
        // 썸네일 호버 이벤트
        this.elements.thumbnailGrid.querySelectorAll('.thumbnail-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                const index = parseInt(item.dataset.index);
                this.handleThumbnailHover(index);
            });
        });
    }
    
    /**
     * 썸네일 호버 처리
     */
    handleThumbnailHover(index) {
        this.state.mainImage = this.state.thumbnails[index];
        this.updateMainImage();
        
        // 활성 썸네일 표시
        document.querySelectorAll('.thumbnail-item').forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
    }
    
    /**
     * 메인 이미지 업데이트
     */
    updateMainImage() {
        if (!this.elements.mainImage || !this.state.mainImage) {
            console.error('Main image element or state not found');
            return;
        }
        
        const imgSrc = this.state.mainImage.path || this.state.mainImage.full || this.state.mainImage.image_path || this.state.mainImage.thumb;
        console.log('Updating main image to:', imgSrc);
        
        // 페이드 효과
        this.elements.mainImageWrapper.classList.add('loading');
        
        const img = new Image();
        img.onload = () => {
            this.elements.mainImage.src = img.src;
            this.elements.mainImage.alt = this.state.mainImage.title || '';
            this.elements.mainImageWrapper.classList.remove('loading');
            console.log('Main image loaded successfully');
        };
        img.onerror = () => {
            console.error('Failed to load main image:', imgSrc);
            this.elements.mainImageWrapper.classList.remove('loading');
        };
        img.src = imgSrc;
    }
    
    /**
     * 팝업 열기
     */
    async openPopup() {
        this.state.popupOpen = true;
        this.elements.popup.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        await this.loadAllImages(1);
    }
    
    /**
     * 팝업 닫기
     */
    closePopup() {
        this.state.popupOpen = false;
        this.elements.popup.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    /**
     * 전체 이미지 로드
     */
    async loadAllImages(page = 1) {
        try {
            this.elements.popupLoader.classList.add('active');
            
            const url = `${this.config.apiUrl}?category=${this.config.category}&page=${page}&per_page=${this.config.perPage}&all=true`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success) {
                this.state.allImages = data.data || [];
                this.state.currentPage = data.pagination.current_page;
                this.state.totalPages = data.pagination.total_pages;
                
                this.renderPopupGallery();
                this.renderPagination();
            }
        } catch (error) {
            console.error('Failed to load all images:', error);
        } finally {
            this.elements.popupLoader.classList.remove('active');
        }
    }
    
    /**
     * 팝업 갤러리 렌더링
     */
    renderPopupGallery() {
        if (!this.elements.popupGrid) return;
        
        this.elements.popupGrid.innerHTML = this.state.allImages.map((image, index) => `
            <div class="popup-image-item" data-index="${index}">
                <img src="${image.thumb || image.thumbnail || image.path || image.image_path}" alt="${image.title || ''}">
                <div class="image-overlay">
                    <span class="image-title">${image.title || ''}</span>
                </div>
            </div>
        `).join('');
        
        // 이미지 클릭 이벤트
        this.elements.popupGrid.querySelectorAll('.popup-image-item').forEach(item => {
            item.addEventListener('click', () => {
                const index = parseInt(item.dataset.index);
                this.openLightbox(index);
            });
        });
    }
    
    /**
     * 페이지네이션 렌더링
     */
    renderPagination() {
        if (!this.elements.pagination || this.state.totalPages <= 1) return;
        
        let html = '';
        
        // 이전 버튼
        if (this.state.currentPage > 1) {
            html += `<button class="page-btn page-prev" data-page="${this.state.currentPage - 1}">◀</button>`;
        }
        
        // 페이지 번호
        const maxVisible = 5;
        let start = Math.max(1, this.state.currentPage - Math.floor(maxVisible / 2));
        let end = Math.min(this.state.totalPages, start + maxVisible - 1);
        
        if (end - start + 1 < maxVisible) {
            start = Math.max(1, end - maxVisible + 1);
        }
        
        for (let i = start; i <= end; i++) {
            html += `<button class="page-btn ${i === this.state.currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        
        // 다음 버튼
        if (this.state.currentPage < this.state.totalPages) {
            html += `<button class="page-btn page-next" data-page="${this.state.currentPage + 1}">▶</button>`;
        }
        
        this.elements.pagination.innerHTML = html;
        
        // 페이지 버튼 이벤트
        this.elements.pagination.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = parseInt(btn.dataset.page);
                this.loadAllImages(page);
            });
        });
    }
    
    /**
     * 라이트박스 열기
     */
    openLightbox(index) {
        this.state.lightboxOpen = true;
        this.state.selectedIndex = index;
        this.elements.lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        this.updateLightboxImage();
    }
    
    /**
     * 라이트박스 닫기
     */
    closeLightbox() {
        this.state.lightboxOpen = false;
        this.elements.lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    /**
     * 라이트박스 이미지 업데이트
     */
    updateLightboxImage() {
        const image = this.state.allImages[this.state.selectedIndex];
        if (!image) return;
        
        this.elements.lightboxImage.src = image.full || image.path || image.image_path || image.thumb;
        this.elements.lightboxImage.alt = image.title || '';
        this.elements.lightboxCaption.textContent = image.title || '';
        
        // 네비게이션 버튼 표시/숨김
        this.elements.lightboxPrev.style.display = this.state.selectedIndex > 0 ? 'block' : 'none';
        this.elements.lightboxNext.style.display = this.state.selectedIndex < this.state.allImages.length - 1 ? 'block' : 'none';
    }
    
    /**
     * 이전 이미지
     */
    prevImage() {
        if (this.state.selectedIndex > 0) {
            this.state.selectedIndex--;
            this.updateLightboxImage();
        }
    }
    
    /**
     * 다음 이미지
     */
    nextImage() {
        if (this.state.selectedIndex < this.state.allImages.length - 1) {
            this.state.selectedIndex++;
            this.updateLightboxImage();
        }
    }
    
    /**
     * 메인 이미지 확대 효과 바인딩 (포스터 갤러리 기술 적용)
     */
    bindMainImageZoom() {
        if (!this.elements.mainImageWrapper) return;
        
        // 포스터 갤러리의 고급 애니메이션 시스템 적용
        this.zoomAnimation = {
            targetX: 50,
            targetY: 50,
            currentX: 50,
            currentY: 50,
            targetSize: 100,
            currentSize: 100,
            animationFrame: null,
            ease: 0.15 // 포스터보다 약간 빠른 반응성
        };
        
        this.elements.mainImageWrapper.addEventListener('mouseenter', () => {
            this.elements.mainImageWrapper.classList.add('zoom-active');
            // 배경 이미지 방식으로 전환
            this.switchToBackgroundMode();
        });
        
        this.elements.mainImageWrapper.addEventListener('mouseleave', () => {
            this.elements.mainImageWrapper.classList.remove('zoom-active');
            // 애니메이션 목표값 리셋
            this.zoomAnimation.targetX = 50;
            this.zoomAnimation.targetY = 50;
            this.zoomAnimation.targetSize = 100;
            
            // 부드러운 리셋 애니메이션 시작
            if (!this.zoomAnimation.animationFrame) {
                this.startZoomAnimation();
            }
        });
        
        this.elements.mainImageWrapper.addEventListener('mousemove', (e) => {
            if (!this.elements.mainImage) return;
            
            const rect = this.elements.mainImageWrapper.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width; // 0~1 사이 값
            const y = (e.clientY - rect.top) / rect.height; // 0~1 사이 값
            
            // 포스터 갤러리의 마우스 추적 시스템 적용
            this.zoomAnimation.targetX = x * 100; // 0~100%
            this.zoomAnimation.targetY = y * 100; // 0~100%
            this.zoomAnimation.targetSize = 200; // 2배 확대 (포스터와 동일)
            
            // 애니메이션 시작
            if (!this.zoomAnimation.animationFrame) {
                this.startZoomAnimation();
            }
        });
    }
    
    /**
     * 배경 이미지 모드로 전환 (포스터 갤러리 기술)
     */
    switchToBackgroundMode() {
        if (!this.elements.mainImage || !this.state.mainImage) return;
        
        // 현재 이미지 URL 가져오기
        const imageUrl = this.state.mainImage.full;
        
        // 배경 이미지로 설정
        this.elements.mainImageWrapper.style.backgroundImage = `url('${imageUrl}')`;
        this.elements.mainImageWrapper.style.backgroundSize = '100%';
        this.elements.mainImageWrapper.style.backgroundPosition = 'center center';
        this.elements.mainImageWrapper.style.backgroundRepeat = 'no-repeat';
        
        // 기존 img 요소 숨기기
        this.elements.mainImage.style.opacity = '0';
        
        // 애니메이션 상태 초기화
        this.zoomAnimation.currentSize = 100;
        this.zoomAnimation.currentX = 50;
        this.zoomAnimation.currentY = 50;
    }
    
    /**
     * 부드러운 줌 애니메이션 (포스터 갤러리 기술)
     */
    startZoomAnimation() {
        if (!this.elements.mainImageWrapper) return;

        // 부드러운 전환 계산 (포스터 갤러리와 동일한 알고리즘)
        const ease = this.zoomAnimation.ease;
        this.zoomAnimation.currentX += (this.zoomAnimation.targetX - this.zoomAnimation.currentX) * ease;
        this.zoomAnimation.currentY += (this.zoomAnimation.targetY - this.zoomAnimation.currentY) * ease;
        this.zoomAnimation.currentSize += (this.zoomAnimation.targetSize - this.zoomAnimation.currentSize) * ease;

        // CSS 배경 속성 적용
        this.elements.mainImageWrapper.style.backgroundSize = `${this.zoomAnimation.currentSize}%`;
        this.elements.mainImageWrapper.style.backgroundPosition = `${this.zoomAnimation.currentX}% ${this.zoomAnimation.currentY}%`;

        // 애니메이션 계속 실행 (목표에 가까우면 멈춤)
        const threshold = 0.5;
        if (Math.abs(this.zoomAnimation.targetX - this.zoomAnimation.currentX) > threshold ||
            Math.abs(this.zoomAnimation.targetY - this.zoomAnimation.currentY) > threshold ||
            Math.abs(this.zoomAnimation.targetSize - this.zoomAnimation.currentSize) > threshold) {
            this.zoomAnimation.animationFrame = requestAnimationFrame(() => this.startZoomAnimation());
        } else {
            this.zoomAnimation.animationFrame = null;
            
            // 줌이 끝나면 원래 모드로 복귀
            if (this.zoomAnimation.targetSize === 100) {
                this.switchBackToImageMode();
            }
        }
    }
    
    /**
     * 이미지 모드로 복귀
     */
    switchBackToImageMode() {
        if (!this.elements.mainImage) return;
        
        // 배경 이미지 제거
        this.elements.mainImageWrapper.style.backgroundImage = '';
        this.elements.mainImageWrapper.style.backgroundSize = '';
        this.elements.mainImageWrapper.style.backgroundPosition = '';
        
        // img 요소 다시 표시
        this.elements.mainImage.style.opacity = '1';
    }
    
    /**
     * 키보드 이벤트 처리
     */
    handleKeyboard(e) {
        if (this.state.lightboxOpen) {
            switch(e.key) {
                case 'Escape':
                    this.closeLightbox();
                    break;
                case 'ArrowLeft':
                    this.prevImage();
                    break;
                case 'ArrowRight':
                    this.nextImage();
                    break;
            }
        } else if (this.state.popupOpen) {
            if (e.key === 'Escape') {
                this.closePopup();
            }
        }
    }
}

// 전역으로 노출
window.UnifiedGallery = UnifiedGallery;