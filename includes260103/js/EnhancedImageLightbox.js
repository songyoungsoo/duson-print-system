/**
 * Enhanced Image Lightbox v2.0
 * 고급 이미지 확대 모달 시스템
 * Features: Click-to-enlarge, click-to-close, smooth animations, keyboard navigation
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 */

class EnhancedImageLightbox {
    constructor(options = {}) {
        this.options = {
            enableKeyboard: options.enableKeyboard !== false,
            enableSwipe: options.enableSwipe !== false,
            animationDuration: options.animationDuration || 300,
            closeOnBackdrop: options.closeOnBackdrop !== false,
            closeOnImageClick: options.closeOnImageClick !== false,
            showNavigation: options.showNavigation !== false,
            showCaption: options.showCaption !== false,
            zIndex: options.zIndex || 10000,
            className: options.className || 'enhanced-lightbox',
            ...options
        };
        
        this.state = {
            isOpen: false,
            currentIndex: 0,
            images: [],
            isLoading: false,
            touchStartX: 0,
            touchEndX: 0
        };
        
        this.elements = {};
        this.eventListeners = new Map();
        
        this.init();
    }
    
    /**
     * 초기화
     */
    init() {
        this.createLightboxHTML();
        this.injectStyles();
        this.bindEvents();
        console.log('✨ EnhancedImageLightbox initialized');
    }
    
    /**
     * 라이트박스 HTML 구조 생성
     */
    createLightboxHTML() {
        // 기존 라이트박스 제거
        const existing = document.getElementById('enhanced-lightbox');
        if (existing) {
            existing.remove();
        }
        
        const lightbox = document.createElement('div');
        lightbox.id = 'enhanced-lightbox';
        lightbox.className = `${this.options.className} lightbox-hidden`;
        lightbox.innerHTML = `
            <div class="lightbox-backdrop"></div>
            <div class="lightbox-container">
                <!-- 닫기 버튼 -->
                <button class="lightbox-close" aria-label="닫기">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
                
                <!-- 이미지 컨테이너 -->
                <div class="lightbox-image-container">
                    <div class="lightbox-loading">
                        <div class="loading-spinner"></div>
                        <p>이미지 로딩 중...</p>
                    </div>
                    <img class="lightbox-image" alt="" />
                </div>
                
                <!-- 네비게이션 -->
                <button class="lightbox-nav lightbox-prev" aria-label="이전 이미지">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                </button>
                <button class="lightbox-nav lightbox-next" aria-label="다음 이미지">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </button>
                
                <!-- 캡션 -->
                <div class="lightbox-caption">
                    <div class="caption-content">
                        <h4 class="caption-title"></h4>
                        <p class="caption-description"></p>
                        <div class="caption-info">
                            <span class="image-counter">1 / 1</span>
                        </div>
                    </div>
                </div>
                
                <!-- 썸네일 네비게이션 (다중 이미지 시) -->
                <div class="lightbox-thumbnails">
                    <div class="thumbnails-container"></div>
                </div>
            </div>
        `;
        
        document.body.appendChild(lightbox);
        
        // DOM 요소 참조 저장
        this.elements = {
            lightbox,
            backdrop: lightbox.querySelector('.lightbox-backdrop'),
            container: lightbox.querySelector('.lightbox-container'),
            close: lightbox.querySelector('.lightbox-close'),
            imageContainer: lightbox.querySelector('.lightbox-image-container'),
            image: lightbox.querySelector('.lightbox-image'),
            loading: lightbox.querySelector('.lightbox-loading'),
            prevBtn: lightbox.querySelector('.lightbox-prev'),
            nextBtn: lightbox.querySelector('.lightbox-next'),
            caption: lightbox.querySelector('.lightbox-caption'),
            captionTitle: lightbox.querySelector('.caption-title'),
            captionDescription: lightbox.querySelector('.caption-description'),
            counter: lightbox.querySelector('.image-counter'),
            thumbnails: lightbox.querySelector('.lightbox-thumbnails'),
            thumbnailsContainer: lightbox.querySelector('.thumbnails-container')
        };
    }
    
    /**
     * CSS 스타일 주입
     */
    injectStyles() {
        if (document.getElementById('enhanced-lightbox-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'enhanced-lightbox-styles';
        styles.textContent = `
            .${this.options.className} {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: ${this.options.zIndex};
                display: flex;
                align-items: center;
                justify-content: center;
                transition: opacity ${this.options.animationDuration}ms ease, visibility ${this.options.animationDuration}ms ease;
            }
            
            .lightbox-hidden {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }
            
            .lightbox-visible {
                opacity: 1;
                visibility: visible;
                pointer-events: all;
            }
            
            .lightbox-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                backdrop-filter: blur(8px);
                transition: opacity ${this.options.animationDuration}ms ease;
            }
            
            .lightbox-container {
                position: relative;
                max-width: 95vw;
                max-height: 95vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                animation: lightboxSlideIn ${this.options.animationDuration}ms ease;
            }
            
            .lightbox-close {
                position: absolute;
                top: -50px;
                right: -10px;
                background: rgba(255, 255, 255, 0.1);
                border: none;
                color: white;
                width: 44px;
                height: 44px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                z-index: 10;
                backdrop-filter: blur(10px);
            }
            
            .lightbox-close:hover {
                background: rgba(255, 255, 255, 0.2);
                transform: scale(1.1);
            }
            
            .lightbox-image-container {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                max-width: 90vw;
                max-height: 80vh;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            }
            
            .lightbox-image {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
                cursor: ${this.options.closeOnImageClick ? 'pointer' : 'default'};
                transition: transform 0.3s ease;
                border-radius: 8px;
            }
            
            .lightbox-image:hover {
                transform: ${this.options.closeOnImageClick ? 'scale(0.98)' : 'none'};
            }
            
            .lightbox-loading {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                display: flex;
                flex-direction: column;
                align-items: center;
                color: white;
                z-index: 5;
            }
            
            .loading-spinner {
                width: 40px;
                height: 40px;
                border: 3px solid rgba(255, 255, 255, 0.3);
                border-top: 3px solid white;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 15px;
            }
            
            .lightbox-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(255, 255, 255, 0.1);
                border: none;
                color: white;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
                z-index: 10;
            }
            
            .lightbox-nav:hover {
                background: rgba(255, 255, 255, 0.2);
                transform: translateY(-50%) scale(1.1);
            }
            
            .lightbox-nav:disabled {
                opacity: 0.3;
                cursor: not-allowed;
                transform: translateY(-50%);
            }
            
            .lightbox-prev {
                left: -70px;
            }
            
            .lightbox-next {
                right: -70px;
            }
            
            .lightbox-caption {
                margin-top: 20px;
                max-width: 600px;
                text-align: center;
                color: white;
                padding: 0 20px;
            }
            
            .caption-title {
                font-size: 1.3rem;
                font-weight: 600;
                margin: 0 0 8px 0;
                color: white;
            }
            
            .caption-description {
                font-size: 0.95rem;
                line-height: 1.5;
                margin: 0 0 15px 0;
                opacity: 0.9;
            }
            
            .caption-info {
                display: flex;
                justify-content: center;
                gap: 20px;
                font-size: 0.85rem;
                opacity: 0.7;
            }
            
            .lightbox-thumbnails {
                margin-top: 20px;
                max-width: 80vw;
                overflow-x: auto;
                padding: 0 10px;
            }
            
            .thumbnails-container {
                display: flex;
                gap: 8px;
                padding: 10px 0;
            }
            
            .thumbnail-item {
                flex-shrink: 0;
                width: 60px;
                height: 60px;
                border-radius: 6px;
                overflow: hidden;
                cursor: pointer;
                border: 2px solid transparent;
                transition: all 0.3s ease;
                opacity: 0.6;
            }
            
            .thumbnail-item:hover {
                opacity: 0.8;
                transform: scale(1.05);
            }
            
            .thumbnail-item.active {
                border-color: #667eea;
                opacity: 1;
                transform: scale(1.1);
            }
            
            .thumbnail-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            @keyframes lightboxSlideIn {
                from {
                    opacity: 0;
                    transform: scale(0.8) translateY(50px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* 모바일 최적화 */
            @media (max-width: 768px) {
                .lightbox-container {
                    max-width: 100vw;
                    max-height: 100vh;
                }
                
                .lightbox-image-container {
                    max-width: 95vw;
                    max-height: 75vh;
                }
                
                .lightbox-close {
                    top: 10px;
                    right: 10px;
                    width: 40px;
                    height: 40px;
                }
                
                .lightbox-nav {
                    width: 45px;
                    height: 45px;
                }
                
                .lightbox-prev {
                    left: 10px;
                }
                
                .lightbox-next {
                    right: 10px;
                }
                
                .lightbox-caption {
                    margin-top: 15px;
                    padding: 0 15px;
                }
                
                .caption-title {
                    font-size: 1.1rem;
                }
                
                .caption-description {
                    font-size: 0.9rem;
                }
                
                .thumbnail-item {
                    width: 50px;
                    height: 50px;
                }
            }
            
            @media (max-width: 480px) {
                .lightbox-image-container {
                    max-width: 100vw;
                    max-height: 70vh;
                    border-radius: 0;
                }
                
                .lightbox-thumbnails {
                    max-width: 100vw;
                }
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    /**
     * 이벤트 바인딩
     */
    bindEvents() {
        // 닫기 버튼
        this.elements.close.addEventListener('click', () => this.close());
        
        // 백드롭 클릭으로 닫기
        if (this.options.closeOnBackdrop) {
            this.elements.backdrop.addEventListener('click', () => this.close());
        }
        
        // 이미지 클릭으로 닫기
        if (this.options.closeOnImageClick) {
            this.elements.image.addEventListener('click', () => this.close());
        }
        
        // 네비게이션 버튼
        if (this.options.showNavigation) {
            this.elements.prevBtn.addEventListener('click', () => this.prev());
            this.elements.nextBtn.addEventListener('click', () => this.next());
        }
        
        // 키보드 네비게이션
        if (this.options.enableKeyboard) {
            document.addEventListener('keydown', (e) => {
                if (!this.state.isOpen) return;
                
                switch (e.key) {
                    case 'Escape':
                        this.close();
                        break;
                    case 'ArrowLeft':
                        this.prev();
                        break;
                    case 'ArrowRight':
                        this.next();
                        break;
                }
            });
        }
        
        // 터치/스와이프 지원
        if (this.options.enableSwipe) {
            this.elements.imageContainer.addEventListener('touchstart', (e) => {
                this.state.touchStartX = e.touches[0].clientX;
            });
            
            this.elements.imageContainer.addEventListener('touchend', (e) => {
                this.state.touchEndX = e.changedTouches[0].clientX;
                this.handleSwipe();
            });
        }
        
        // 이미지 로드 이벤트
        this.elements.image.addEventListener('load', () => {
            this.hideLoading();
        });
        
        this.elements.image.addEventListener('error', () => {
            this.hideLoading();
            this.showError();
        });
    }
    
    /**
     * 라이트박스 열기
     */
    open(images, startIndex = 0) {
        // 이미지 배열 정규화
        if (typeof images === 'string') {
            images = [{ src: images, title: '', description: '' }];
        } else if (!Array.isArray(images)) {
            images = [images];
        }
        
        this.state.images = images.map(img => {
            if (typeof img === 'string') {
                return { src: img, title: '', description: '' };
            }
            return {
                src: img.src || img.url || img.path || img,
                title: img.title || img.alt || '',
                description: img.description || img.caption || '',
                thumbnail: img.thumbnail || img.thumb || img.src || img.url || img.path || img
            };
        });
        
        this.state.currentIndex = Math.max(0, Math.min(startIndex, this.state.images.length - 1));
        this.state.isOpen = true;
        
        // DOM 업데이트
        this.elements.lightbox.classList.remove('lightbox-hidden');
        this.elements.lightbox.classList.add('lightbox-visible');
        document.body.style.overflow = 'hidden';
        
        // 네비게이션 버튼 표시 여부
        const showNav = this.options.showNavigation && this.state.images.length > 1;
        this.elements.prevBtn.style.display = showNav ? 'flex' : 'none';
        this.elements.nextBtn.style.display = showNav ? 'flex' : 'none';
        
        // 썸네일 표시 여부
        if (this.state.images.length > 1) {
            this.createThumbnails();
            this.elements.thumbnails.style.display = 'block';
        } else {
            this.elements.thumbnails.style.display = 'none';
        }
        
        // 현재 이미지 로드
        this.showImage(this.state.currentIndex);
        
        this.trigger('open', { images: this.state.images, index: this.state.currentIndex });
    }
    
    /**
     * 라이트박스 닫기
     */
    close() {
        this.state.isOpen = false;
        
        this.elements.lightbox.classList.remove('lightbox-visible');
        this.elements.lightbox.classList.add('lightbox-hidden');
        document.body.style.overflow = '';
        
        this.trigger('close');
    }
    
    /**
     * 이미지 표시
     */
    showImage(index) {
        if (!this.state.images.length || index < 0 || index >= this.state.images.length) {
            return;
        }
        
        this.state.currentIndex = index;
        const image = this.state.images[index];
        
        // 로딩 표시
        this.showLoading();
        
        // 이미지 로드
        this.elements.image.src = image.src;
        this.elements.image.alt = image.title;
        
        // 캡션 업데이트
        if (this.options.showCaption) {
            this.updateCaption(image);
            this.elements.caption.style.display = 'block';
        } else {
            this.elements.caption.style.display = 'none';
        }
        
        // 네비게이션 버튼 상태 업데이트
        this.updateNavigation();
        
        // 썸네일 활성 상태 업데이트
        this.updateThumbnailActive();
        
        this.trigger('imageChange', { image, index });
    }
    
    /**
     * 썸네일 생성
     */
    createThumbnails() {
        const container = this.elements.thumbnailsContainer;
        container.innerHTML = '';
        
        this.state.images.forEach((image, index) => {
            const thumbnail = document.createElement('div');
            thumbnail.className = 'thumbnail-item';
            thumbnail.innerHTML = `<img src="${image.thumbnail}" alt="${image.title}" />`;
            thumbnail.addEventListener('click', () => this.showImage(index));
            container.appendChild(thumbnail);
        });
    }
    
    /**
     * 썸네일 활성 상태 업데이트
     */
    updateThumbnailActive() {
        const thumbnails = this.elements.thumbnailsContainer.querySelectorAll('.thumbnail-item');
        thumbnails.forEach((thumb, index) => {
            if (index === this.state.currentIndex) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }
    
    /**
     * 캡션 업데이트
     */
    updateCaption(image) {
        this.elements.captionTitle.textContent = image.title;
        this.elements.captionDescription.textContent = image.description;
        this.elements.counter.textContent = `${this.state.currentIndex + 1} / ${this.state.images.length}`;
    }
    
    /**
     * 네비게이션 버튼 상태 업데이트
     */
    updateNavigation() {
        if (!this.options.showNavigation || this.state.images.length <= 1) return;
        
        this.elements.prevBtn.disabled = this.state.currentIndex === 0;
        this.elements.nextBtn.disabled = this.state.currentIndex === this.state.images.length - 1;
    }
    
    /**
     * 이전 이미지
     */
    prev() {
        if (this.state.currentIndex > 0) {
            this.showImage(this.state.currentIndex - 1);
        }
    }
    
    /**
     * 다음 이미지
     */
    next() {
        if (this.state.currentIndex < this.state.images.length - 1) {
            this.showImage(this.state.currentIndex + 1);
        }
    }
    
    /**
     * 스와이프 처리
     */
    handleSwipe() {
        const threshold = 50;
        const diff = this.state.touchStartX - this.state.touchEndX;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.next(); // 왼쪽 스와이프 -> 다음
            } else {
                this.prev(); // 오른쪽 스와이프 -> 이전
            }
        }
    }
    
    /**
     * 로딩 표시
     */
    showLoading() {
        this.state.isLoading = true;
        this.elements.loading.style.display = 'flex';
        this.elements.image.style.opacity = '0';
    }
    
    /**
     * 로딩 숨김
     */
    hideLoading() {
        this.state.isLoading = false;
        this.elements.loading.style.display = 'none';
        this.elements.image.style.opacity = '1';
    }
    
    /**
     * 에러 표시
     */
    showError() {
        this.elements.loading.innerHTML = `
            <div style="color: #ff6b6b; text-align: center;">
                <p>❌ 이미지를 불러올 수 없습니다</p>
            </div>
        `;
    }
    
    /**
     * 이벤트 리스너 등록
     */
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }
    
    /**
     * 이벤트 트리거
     */
    trigger(event, data = {}) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                callback.call(this, data);
            });
        }
    }
    
    /**
     * 라이트박스 파괴
     */
    destroy() {
        if (this.elements.lightbox) {
            this.elements.lightbox.remove();
        }
        
        document.body.style.overflow = '';
        this.eventListeners.clear();
        
        console.log('EnhancedImageLightbox destroyed');
    }
}

// 전역 접근 가능
window.EnhancedImageLightbox = EnhancedImageLightbox;

console.log('✨ EnhancedImageLightbox v2.0 loaded');