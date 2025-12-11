/**
 * 통합 갤러리 컴포넌트 시스템 v2.0
 * 더욱 향상된 재사용성과 확장성을 제공하는 갤러리 컴포넌트
 * 
 * Features:
 * - 완전한 컴포넌트화 및 모듈화
 * - 이벤트 기반 통신 시스템
 * - 테마 및 스타일 커스터마이징
 * - 반응형 디자인 지원
 * - 접근성 향상 (ARIA 속성)
 * - 터치 디바이스 지원
 */

class GalleryComponent {
    static VERSION = '2.0.0';
    static instances = new Map();
    
    constructor(config = {}) {
        // 고유 ID 생성
        this.id = config.id || `gallery_${Date.now()}`;
        
        // 기본 설정과 사용자 설정 병합
        this.config = {
            ...this.getDefaultConfig(),
            ...config
        };
        
        // 컴포넌트 상태
        this.state = {
            images: [],
            currentIndex: 0,
            isLoading: false,
            isError: false,
            errorMessage: '',
            isZoomed: false,
            thumbnailsVisible: true
        };
        
        // 이벤트 리스너 저장소
        this.eventListeners = new Map();
        
        // 애니메이션 관련
        this.animation = {
            frame: null,
            currentX: 50,
            currentY: 50,
            currentScale: 1,
            targetX: 50,
            targetY: 50,
            targetScale: 1
        };
        
        // 터치 지원
        this.touch = {
            startX: 0,
            startY: 0,
            endX: 0,
            endY: 0,
            isSwipping: false
        };
        
        // 인스턴스 저장
        GalleryComponent.instances.set(this.id, this);
    }
    
    /**
     * 기본 설정
     */
    getDefaultConfig() {
        return {
            // 컨테이너 설정
            container: null,
            containerClass: 'gallery-component',
            
            // 데이터 소스
            dataSource: null,
            dataType: 'json', // json, array, html
            images: [],
            
            // 디스플레이 설정
            layout: 'grid', // grid, carousel, masonry
            columns: 4,
            gap: 10,
            aspectRatio: '1:1',
            
            // 기능 설정
            features: {
                zoom: true,
                lightbox: true,
                thumbnails: true,
                navigation: true,
                autoplay: false,
                download: false,
                share: false,
                fullscreen: true
            },
            
            // 애니메이션 설정
            animation: {
                enabled: true,
                duration: 300,
                easing: 'ease-in-out'
            },
            
            // 테마 설정
            theme: {
                primary: '#667eea',
                secondary: '#764ba2',
                background: '#ffffff',
                text: '#333333',
                border: '#e0e0e0'
            },
            
            // 반응형 설정
            responsive: {
                breakpoints: {
                    mobile: 576,
                    tablet: 768,
                    desktop: 1024
                },
                columnsOnMobile: 2,
                columnsOnTablet: 3
            },
            
            // 콜백 함수
            callbacks: {
                onInit: null,
                onLoad: null,
                onImageChange: null,
                onZoom: null,
                onError: null,
                onDestroy: null
            },
            
            // 접근성
            accessibility: {
                enabled: true,
                labels: {
                    gallery: '이미지 갤러리',
                    thumbnail: '썸네일',
                    mainImage: '메인 이미지',
                    nextButton: '다음 이미지',
                    prevButton: '이전 이미지',
                    zoomIn: '확대',
                    zoomOut: '축소',
                    close: '닫기'
                }
            },
            
            // 로컬라이제이션
            locale: 'ko',
            translations: {
                ko: {
                    loading: '이미지를 불러오는 중...',
                    error: '이미지를 불러올 수 없습니다.',
                    empty: '표시할 이미지가 없습니다.',
                    imageCounter: '{{current}} / {{total}}'
                },
                en: {
                    loading: 'Loading images...',
                    error: 'Failed to load images.',
                    empty: 'No images to display.',
                    imageCounter: '{{current}} / {{total}}'
                }
            }
        };
    }
    
    /**
     * 갤러리 초기화
     */
    async init() {
        try {
            // 컨테이너 검증
            if (typeof this.config.container === 'string') {
                this.container = document.querySelector(this.config.container);
            } else {
                this.container = this.config.container;
            }
            
            if (!this.container) {
                throw new Error('Gallery container not found');
            }
            
            // HTML 구조 생성
            this.render();
            
            // 이벤트 바인딩
            this.bindEvents();
            
            // 데이터 로드
            if (this.config.dataSource) {
                await this.loadData();
            } else if (this.config.images.length > 0) {
                this.setImages(this.config.images);
            }
            
            // 반응형 처리
            this.handleResponsive();
            
            // 초기화 콜백
            this.trigger('init');
            
            console.log(`✅ GalleryComponent v${GalleryComponent.VERSION} initialized:`, this.id);
            
        } catch (error) {
            console.error('Gallery initialization failed:', error);
            this.showError(error.message);
            this.trigger('error', error);
        }
    }
    
    /**
     * HTML 렌더링
     */
    render() {
        const theme = this.config.theme;
        const features = this.config.features;
        
        this.container.innerHTML = `
            <div class="${this.config.containerClass}" id="${this.id}" 
                 role="region" 
                 aria-label="${this.config.accessibility.labels.gallery}"
                 style="--primary-color: ${theme.primary}; --secondary-color: ${theme.secondary};">
                
                <!-- 메인 뷰어 영역 -->
                <div class="gallery-viewer">
                    <div class="gallery-main-image" role="img" aria-label="${this.config.accessibility.labels.mainImage}">
                        <img src="" alt="" />
                        ${features.zoom ? '<div class="zoom-indicator">🔍</div>' : ''}
                    </div>
                    
                    ${features.navigation ? `
                    <div class="gallery-navigation">
                        <button class="nav-prev" aria-label="${this.config.accessibility.labels.prevButton}">‹</button>
                        <button class="nav-next" aria-label="${this.config.accessibility.labels.nextButton}">›</button>
                    </div>
                    ` : ''}
                    
                    <div class="gallery-counter"></div>
                </div>
                
                ${features.thumbnails ? `
                <!-- 썸네일 영역 -->
                <div class="gallery-thumbnails" role="list">
                    <div class="thumbnails-container"></div>
                </div>
                ` : ''}
                
                <!-- 로딩/에러 상태 -->
                <div class="gallery-loading" style="display: none;">
                    <div class="spinner"></div>
                    <p>${this.getTranslation('loading')}</p>
                </div>
                
                <div class="gallery-error" style="display: none;">
                    <p class="error-message"></p>
                </div>
                
                ${features.lightbox ? `
                <!-- 라이트박스 -->
                <div class="gallery-lightbox" style="display: none;">
                    <div class="lightbox-overlay"></div>
                    <div class="lightbox-content">
                        <img src="" alt="" />
                        <button class="lightbox-close" aria-label="${this.config.accessibility.labels.close}">×</button>
                        ${features.fullscreen ? '<button class="lightbox-fullscreen">⛶</button>' : ''}
                        ${features.download ? '<button class="lightbox-download">⬇</button>' : ''}
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        // 스타일 주입
        this.injectStyles();
    }
    
    /**
     * 스타일 주입
     */
    injectStyles() {
        if (document.getElementById('gallery-component-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'gallery-component-styles';
        styles.textContent = `
            .gallery-component {
                position: relative;
                width: 100%;
                background: var(--background-color, #fff);
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            
            .gallery-viewer {
                position: relative;
                width: 100%;
                padding-bottom: 75%; /* 4:3 aspect ratio */
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                overflow: hidden;
            }
            
            .gallery-main-image {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .gallery-main-image img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
                transition: transform 0.3s ease;
            }
            
            .gallery-main-image:hover img {
                transform: scale(1.05);
            }
            
            .gallery-thumbnails {
                padding: 1rem;
                background: #f8f9fa;
            }
            
            .thumbnails-container {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 0.5rem;
            }
            
            .thumbnail-item {
                aspect-ratio: 1;
                border-radius: 8px;
                overflow: hidden;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }
            
            .thumbnail-item:hover {
                transform: scale(1.1);
                border-color: var(--primary-color);
            }
            
            .thumbnail-item.active {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            }
            
            .thumbnail-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .gallery-navigation button {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(255,255,255,0.9);
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                font-size: 24px;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }
            
            .gallery-navigation button:hover {
                background: white;
                transform: translateY(-50%) scale(1.1);
            }
            
            .nav-prev { left: 1rem; }
            .nav-next { right: 1rem; }
            
            .gallery-counter {
                position: absolute;
                bottom: 1rem;
                right: 1rem;
                background: rgba(0,0,0,0.6);
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.875rem;
            }
            
            .gallery-lightbox {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .lightbox-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
            }
            
            .lightbox-content {
                position: relative;
                max-width: 90%;
                max-height: 90%;
                z-index: 1;
            }
            
            .lightbox-content img {
                max-width: 100%;
                max-height: 90vh;
                object-fit: contain;
            }
            
            .lightbox-close {
                position: absolute;
                top: -40px;
                right: 0;
                background: none;
                border: none;
                color: white;
                font-size: 36px;
                cursor: pointer;
                transition: transform 0.3s ease;
            }
            
            .lightbox-close:hover {
                transform: scale(1.2);
            }
            
            .gallery-loading {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
            }
            
            .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid var(--primary-color);
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 1rem;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .gallery-error {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                color: #dc3545;
            }
            
            .zoom-indicator {
                position: absolute;
                top: 1rem;
                right: 1rem;
                background: rgba(255,255,255,0.9);
                padding: 0.5rem;
                border-radius: 50%;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .gallery-main-image:hover .zoom-indicator {
                opacity: 1;
            }
            
            /* 반응형 스타일 */
            @media (max-width: 768px) {
                .thumbnails-container {
                    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
                }
                
                .gallery-navigation button {
                    width: 35px;
                    height: 35px;
                    font-size: 20px;
                }
            }
            
            @media (max-width: 576px) {
                .gallery-viewer {
                    padding-bottom: 100%; /* 1:1 on mobile */
                }
                
                .thumbnails-container {
                    grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
                }
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    /**
     * 이벤트 바인딩
     */
    bindEvents() {
        const viewer = this.container.querySelector('.gallery-main-image');
        const thumbnails = this.container.querySelector('.thumbnails-container');
        const navPrev = this.container.querySelector('.nav-prev');
        const navNext = this.container.querySelector('.nav-next');
        const lightbox = this.container.querySelector('.gallery-lightbox');
        
        // 메인 이미지 클릭 - 라이트박스
        if (viewer && this.config.features.lightbox) {
            viewer.addEventListener('click', () => this.openLightbox());
        }
        
        // 썸네일 클릭 이벤트 위임
        if (thumbnails) {
            thumbnails.addEventListener('click', (e) => {
                const thumbnail = e.target.closest('.thumbnail-item');
                if (thumbnail) {
                    const index = parseInt(thumbnail.dataset.index);
                    this.goToImage(index);
                }
            });
        }
        
        // 네비게이션
        if (navPrev) navPrev.addEventListener('click', () => this.prevImage());
        if (navNext) navNext.addEventListener('click', () => this.nextImage());
        
        // 라이트박스 닫기
        if (lightbox) {
            const overlay = lightbox.querySelector('.lightbox-overlay');
            const closeBtn = lightbox.querySelector('.lightbox-close');
            
            if (overlay) overlay.addEventListener('click', () => this.closeLightbox());
            if (closeBtn) closeBtn.addEventListener('click', () => this.closeLightbox());
        }
        
        // 키보드 네비게이션
        document.addEventListener('keydown', (e) => {
            if (!this.state.isZoomed) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    this.prevImage();
                    break;
                case 'ArrowRight':
                    this.nextImage();
                    break;
                case 'Escape':
                    this.closeLightbox();
                    break;
            }
        });
        
        // 터치 이벤트 (모바일 스와이프)
        if (viewer && 'ontouchstart' in window) {
            viewer.addEventListener('touchstart', (e) => this.handleTouchStart(e));
            viewer.addEventListener('touchmove', (e) => this.handleTouchMove(e));
            viewer.addEventListener('touchend', (e) => this.handleTouchEnd(e));
        }
        
        // 윈도우 리사이즈
        window.addEventListener('resize', () => this.handleResponsive());
    }
    
    /**
     * 데이터 로드
     */
    async loadData() {
        this.setState({ isLoading: true, isError: false });
        this.showLoading(true);
        
        try {
            let data;
            
            if (this.config.dataType === 'json') {
                const response = await fetch(this.config.dataSource);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const json = await response.json();
                data = json.data || json.images || json;
            } else if (this.config.dataType === 'array') {
                data = this.config.dataSource;
            }
            
            if (!Array.isArray(data)) {
                throw new Error('Invalid data format');
            }
            
            this.setImages(data);
            this.trigger('load', data);
            
        } catch (error) {
            console.error('Failed to load gallery data:', error);
            this.showError(error.message);
            this.trigger('error', error);
        } finally {
            this.setState({ isLoading: false });
            this.showLoading(false);
        }
    }
    
    /**
     * 이미지 설정
     */
    setImages(images) {
        // 이미지 데이터 정규화
        this.state.images = images.map((img, index) => {
            if (typeof img === 'string') {
                return {
                    id: index,
                    src: img,
                    thumbnail: img,
                    title: `Image ${index + 1}`,
                    alt: `Image ${index + 1}`
                };
            }
            return {
                id: img.id || index,
                src: img.src || img.url || img.path,
                thumbnail: img.thumbnail || img.thumb || img.src || img.url || img.path,
                title: img.title || img.name || `Image ${index + 1}`,
                alt: img.alt || img.title || `Image ${index + 1}`,
                ...img
            };
        });
        
        if (this.state.images.length > 0) {
            this.renderThumbnails();
            this.goToImage(0);
        } else {
            this.showEmpty();
        }
    }
    
    /**
     * 썸네일 렌더링
     */
    renderThumbnails() {
        const container = this.container.querySelector('.thumbnails-container');
        if (!container) return;
        
        container.innerHTML = this.state.images.map((img, index) => `
            <div class="thumbnail-item ${index === 0 ? 'active' : ''}" 
                 data-index="${index}"
                 role="listitem"
                 tabindex="0"
                 aria-label="${img.alt}">
                <img src="${img.thumbnail}" alt="${img.alt}" loading="lazy" />
            </div>
        `).join('');
    }
    
    /**
     * 특정 이미지로 이동
     */
    goToImage(index) {
        if (index < 0 || index >= this.state.images.length) return;
        
        const image = this.state.images[index];
        this.state.currentIndex = index;
        
        // 메인 이미지 업데이트
        const mainImg = this.container.querySelector('.gallery-main-image img');
        if (mainImg) {
            mainImg.src = image.src;
            mainImg.alt = image.alt;
        }
        
        // 라이트박스 이미지 업데이트
        const lightboxImg = this.container.querySelector('.lightbox-content img');
        if (lightboxImg) {
            lightboxImg.src = image.src;
            lightboxImg.alt = image.alt;
        }
        
        // 썸네일 활성화 상태 업데이트
        this.updateThumbnailActive(index);
        
        // 카운터 업데이트
        this.updateCounter();
        
        // 이벤트 트리거
        this.trigger('imageChange', { index, image });
    }
    
    /**
     * 썸네일 활성화 상태 업데이트
     */
    updateThumbnailActive(index) {
        const thumbnails = this.container.querySelectorAll('.thumbnail-item');
        thumbnails.forEach((thumb, i) => {
            if (i === index) {
                thumb.classList.add('active');
                thumb.setAttribute('aria-selected', 'true');
            } else {
                thumb.classList.remove('active');
                thumb.setAttribute('aria-selected', 'false');
            }
        });
    }
    
    /**
     * 카운터 업데이트
     */
    updateCounter() {
        const counter = this.container.querySelector('.gallery-counter');
        if (!counter) return;
        
        const text = this.getTranslation('imageCounter')
            .replace('{{current}}', this.state.currentIndex + 1)
            .replace('{{total}}', this.state.images.length);
        
        counter.textContent = text;
    }
    
    /**
     * 다음 이미지
     */
    nextImage() {
        const nextIndex = (this.state.currentIndex + 1) % this.state.images.length;
        this.goToImage(nextIndex);
    }
    
    /**
     * 이전 이미지
     */
    prevImage() {
        const prevIndex = (this.state.currentIndex - 1 + this.state.images.length) % this.state.images.length;
        this.goToImage(prevIndex);
    }
    
    /**
     * 라이트박스 열기
     */
    openLightbox() {
        const lightbox = this.container.querySelector('.gallery-lightbox');
        if (!lightbox) return;
        
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        this.state.isZoomed = true;
        
        this.trigger('zoom', { opened: true });
    }
    
    /**
     * 라이트박스 닫기
     */
    closeLightbox() {
        const lightbox = this.container.querySelector('.gallery-lightbox');
        if (!lightbox) return;
        
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
        this.state.isZoomed = false;
        
        this.trigger('zoom', { opened: false });
    }
    
    /**
     * 터치 이벤트 처리
     */
    handleTouchStart(e) {
        this.touch.startX = e.touches[0].clientX;
        this.touch.startY = e.touches[0].clientY;
        this.touch.isSwipping = true;
    }
    
    handleTouchMove(e) {
        if (!this.touch.isSwipping) return;
        
        this.touch.endX = e.touches[0].clientX;
        this.touch.endY = e.touches[0].clientY;
    }
    
    handleTouchEnd(e) {
        if (!this.touch.isSwipping) return;
        
        const diffX = this.touch.startX - this.touch.endX;
        const diffY = this.touch.startY - this.touch.endY;
        const threshold = 50;
        
        // 수평 스와이프 감지
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > threshold) {
            if (diffX > 0) {
                this.nextImage(); // 왼쪽 스와이프
            } else {
                this.prevImage(); // 오른쪽 스와이프
            }
        }
        
        this.touch.isSwipping = false;
    }
    
    /**
     * 반응형 처리
     */
    handleResponsive() {
        const width = window.innerWidth;
        const breakpoints = this.config.responsive.breakpoints;
        const container = this.container.querySelector('.thumbnails-container');
        
        if (!container) return;
        
        let columns;
        if (width <= breakpoints.mobile) {
            columns = this.config.responsive.columnsOnMobile;
        } else if (width <= breakpoints.tablet) {
            columns = this.config.responsive.columnsOnTablet;
        } else {
            columns = this.config.columns;
        }
        
        container.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
    }
    
    /**
     * 상태 업데이트
     */
    setState(newState) {
        this.state = { ...this.state, ...newState };
    }
    
    /**
     * 로딩 표시
     */
    showLoading(show) {
        const loading = this.container.querySelector('.gallery-loading');
        if (loading) {
            loading.style.display = show ? 'block' : 'none';
        }
    }
    
    /**
     * 에러 표시
     */
    showError(message) {
        const error = this.container.querySelector('.gallery-error');
        const errorMessage = this.container.querySelector('.error-message');
        
        if (error && errorMessage) {
            errorMessage.textContent = message || this.getTranslation('error');
            error.style.display = 'block';
        }
        
        this.setState({ isError: true, errorMessage: message });
    }
    
    /**
     * 빈 상태 표시
     */
    showEmpty() {
        const viewer = this.container.querySelector('.gallery-viewer');
        if (viewer) {
            viewer.innerHTML = `
                <div class="gallery-empty">
                    <p>${this.getTranslation('empty')}</p>
                </div>
            `;
        }
    }
    
    /**
     * 번역 가져오기
     */
    getTranslation(key) {
        const locale = this.config.locale;
        const translations = this.config.translations[locale] || this.config.translations.ko;
        return translations[key] || key;
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
     * 이벤트 리스너 제거
     */
    off(event, callback) {
        if (!this.eventListeners.has(event)) return;
        
        const listeners = this.eventListeners.get(event);
        const index = listeners.indexOf(callback);
        
        if (index > -1) {
            listeners.splice(index, 1);
        }
    }
    
    /**
     * 이벤트 트리거
     */
    trigger(event, data = {}) {
        // 내부 콜백 실행
        const callbackName = `on${event.charAt(0).toUpperCase()}${event.slice(1)}`;
        if (this.config.callbacks[callbackName]) {
            this.config.callbacks[callbackName].call(this, data);
        }
        
        // 등록된 리스너 실행
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                callback.call(this, data);
            });
        }
    }
    
    /**
     * 갤러리 파괴
     */
    destroy() {
        // 애니메이션 정리
        if (this.animation.frame) {
            cancelAnimationFrame(this.animation.frame);
        }
        
        // 이벤트 리스너 정리
        this.eventListeners.clear();
        
        // DOM 정리
        if (this.container) {
            this.container.innerHTML = '';
        }
        
        // 인스턴스 제거
        GalleryComponent.instances.delete(this.id);
        
        // 콜백 실행
        this.trigger('destroy');
        
        console.log(`GalleryComponent destroyed: ${this.id}`);
    }
    
    /**
     * 정적 메서드: ID로 인스턴스 가져오기
     */
    static getInstance(id) {
        return GalleryComponent.instances.get(id);
    }
    
    /**
     * 정적 메서드: 모든 인스턴스 가져오기
     */
    static getAllInstances() {
        return Array.from(GalleryComponent.instances.values());
    }
    
    /**
     * 정적 메서드: 모든 인스턴스 파괴
     */
    static destroyAll() {
        GalleryComponent.instances.forEach(instance => instance.destroy());
    }
}

// 전역 등록
window.GalleryComponent = GalleryComponent;

// jQuery 플러그인 (선택적)
if (typeof jQuery !== 'undefined') {
    jQuery.fn.galleryComponent = function(options) {
        return this.each(function() {
            const $element = jQuery(this);
            let instance = $element.data('galleryComponent');
            
            if (!instance) {
                instance = new GalleryComponent({
                    container: this,
                    ...options
                });
                instance.init();
                $element.data('galleryComponent', instance);
            }
            
            return instance;
        });
    };
}

// 자동 초기화 (data 속성 사용)
document.addEventListener('DOMContentLoaded', function() {
    const galleries = document.querySelectorAll('[data-gallery-component]');
    
    galleries.forEach(element => {
        const config = element.dataset.galleryComponent;
        let options = {};
        
        try {
            options = config ? JSON.parse(config) : {};
        } catch (e) {
            console.warn('Invalid gallery config:', e);
        }
        
        const gallery = new GalleryComponent({
            container: element,
            ...options
        });
        
        gallery.init();
    });
});

console.log('✨ GalleryComponent v2.0.0 loaded');