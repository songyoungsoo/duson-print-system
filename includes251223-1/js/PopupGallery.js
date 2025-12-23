/**
 * 팝업 갤러리 컴포넌트 v1.0
 * 포트폴리오 샘플 더보기 시스템
 * Created: 2025년 8월 (AI Assistant)
 */

class PopupGallery {
    constructor(options = {}) {
        this.options = {
            category: options.category || 'all',
            apiUrl: options.apiUrl || '/api/get_portfolio_gallery.php',
            perPage: options.perPage || 24,
            ...options
        };
        
        this.state = {
            isOpen: false,
            currentPage: 1,
            loading: false,
            images: [],
            totalPages: 0,
            searchQuery: '',
            selectedCategory: options.category || 'all'
        };
        
        this.eventListeners = new Map();
        this.init();
    }
    
    /**
     * 초기화
     */
    init() {
        this.createHTML();
        this.bindEvents();
        console.log('PopupGallery initialized for category:', this.options.category);
    }
    
    /**
     * HTML 구조 생성
     */
    createHTML() {
        // 팝업 컨테이너가 이미 존재하면 제거
        const existing = document.getElementById('popup-gallery');
        if (existing) {
            existing.remove();
        }
        
        const popup = document.createElement('div');
        popup.id = 'popup-gallery';
        popup.className = 'popup-gallery';
        popup.innerHTML = `
            <div class="popup-overlay"></div>
            <div class="popup-content">
                <!-- 헤더 -->
                <div class="popup-header">
                    <div class="popup-title">
                        <h3>📸 ${this.getCategoryDisplayName()} 샘플 갤러리</h3>
                        <p>이미지를 클릭하면 크게 보실 수 있습니다</p>
                    </div>
                    <button class="popup-close" aria-label="닫기">×</button>
                </div>
                
                <!-- 카테고리 필터 -->
                <div class="popup-category-filters">
                    <div class="category-tabs">
                        <button class="category-tab ${this.state.selectedCategory === 'all' ? 'active' : ''}" data-category="all">
                            📁 전체
                        </button>
                        <button class="category-tab ${this.state.selectedCategory === 'sticker' ? 'active' : ''}" data-category="sticker">
                            🏷️ 스티커
                        </button>
                        <button class="category-tab ${this.state.selectedCategory === 'namecard' ? 'active' : ''}" data-category="namecard">
                            💳 명함
                        </button>
                        <button class="category-tab ${this.state.selectedCategory === 'cadarok' ? 'active' : ''}" data-category="cadarok">
                            📖 카탈로그
                        </button>
                        <button class="category-tab ${this.state.selectedCategory === 'littleprint' ? 'active' : ''}" data-category="littleprint">
                            🖼️ 포스터
                        </button>
                        <button class="category-tab ${this.state.selectedCategory === 'envelope' ? 'active' : ''}" data-category="envelope">
                            ✉️ 봉투
                        </button>
                        <button class="category-tab ${this.state.selectedCategory === 'other' ? 'active' : ''}" data-category="other">
                            📁 기타
                        </button>
                    </div>
                </div>
                
                <!-- 컨트롤 바 -->
                <div class="popup-controls">
                    <div class="search-container">
                        <input type="text" id="gallery-search" placeholder="이미지 검색..." />
                        <button class="search-btn">🔍</button>
                    </div>
                    <div class="gallery-info">
                        <span class="gallery-hint">💡 이미지를 클릭하면 크게 보고, 큰 이미지를 클릭하면 닫힙니다</span>
                    </div>
                </div>
                
                <!-- 이미지 그리드 -->
                <div class="popup-body">
                    <div class="gallery-grid" id="gallery-grid">
                        <!-- 이미지들이 여기에 동적으로 로드됩니다 -->
                    </div>
                    
                    <!-- 로딩 상태 -->
                    <div class="gallery-loading" style="display: none;">
                        <div class="spinner"></div>
                        <p>이미지를 불러오는 중...</p>
                    </div>
                    
                    <!-- 빈 상태 -->
                    <div class="gallery-empty" style="display: none;">
                        <p>표시할 이미지가 없습니다.</p>
                    </div>
                </div>
                
                <!-- 페이지네이션 -->
                <div class="popup-pagination">
                    <button class="prev-page" disabled>이전</button>
                    <span class="page-info">1 / 1</span>
                    <button class="next-page" disabled>다음</button>
                </div>
                
                <!-- 푸터 -->
                <div class="popup-footer">
                    <div class="footer-left">
                        <span class="total-images">총 0개 이미지</span>
                    </div>
                    <div class="footer-right">
                        <button class="btn-cancel">닫기</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(popup);
        this.popup = popup;
        this.injectStyles();
    }
    
    /**
     * 스타일 주입
     */
    injectStyles() {
        if (document.getElementById('popup-gallery-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'popup-gallery-styles';
        styles.textContent = `
            .popup-gallery {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                display: none;
                animation: fadeIn 0.3s ease;
            }
            
            .popup-gallery.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .popup-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                backdrop-filter: blur(5px);
            }
            
            .popup-content {
                position: relative;
                width: 90%;
                max-width: 1200px;
                height: 90%;
                max-height: 800px;
                background: white;
                border-radius: 20px;
                display: flex;
                flex-direction: column;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.3s ease;
            }
            
            .popup-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 25px;
                border-bottom: 1px solid #e9ecef;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 20px 20px 0 0;
            }
            
            .popup-title h3 {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 600;
            }
            
            .popup-title p {
                margin: 5px 0 0 0;
                font-size: 0.9rem;
                opacity: 0.9;
            }
            
            .popup-close {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                font-size: 24px;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .popup-close:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: scale(1.1);
            }
            
            .popup-controls {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 25px;
                border-bottom: 1px solid #e9ecef;
                background: #f8f9fa;
            }
            
            .search-container {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            
            #gallery-search {
                padding: 8px 15px;
                border: 2px solid #e9ecef;
                border-radius: 20px;
                width: 250px;
                font-size: 0.9rem;
                transition: border-color 0.3s ease;
            }
            
            #gallery-search:focus {
                outline: none;
                border-color: #667eea;
            }
            
            .search-btn {
                background: #667eea;
                color: white;
                border: none;
                padding: 8px 15px;
                border-radius: 20px;
                cursor: pointer;
                transition: background 0.3s ease;
            }
            
            .search-btn:hover {
                background: #5a6fd8;
            }
            
            .gallery-info {
                display: flex;
                gap: 15px;
                align-items: center;
            }
            
            .gallery-hint {
                font-weight: 500;
                color: #6c757d;
                font-size: 0.9rem;
            }
            
            .popup-body {
                flex: 1;
                padding: 20px 25px;
                overflow: auto;
                position: relative;
            }
            
            .gallery-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 15px;
            }
            
            .gallery-item {
                position: relative;
                aspect-ratio: 1;
                border-radius: 12px;
                overflow: hidden;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 3px solid transparent;
                background: white;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }
            
            .gallery-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }
            
            
            .gallery-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            
            .gallery-item:hover img {
                transform: scale(1.05);
            }
            
            .gallery-item-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .gallery-item:hover .gallery-item-overlay {
                opacity: 1;
            }
            
            .gallery-item-title {
                font-size: 0.9rem;
                font-weight: 600;
                text-align: center;
                padding: 0 10px;
                margin-bottom: 10px;
            }
            
            .gallery-item-hint {
                font-size: 0.8rem;
                font-weight: 500;
                text-align: center;
                padding: 0 10px;
                background: rgba(0, 0, 0, 0.7);
                border-radius: 15px;
                color: white;
            }
            
            .gallery-loading, .gallery-empty {
                text-align: center;
                padding: 60px 20px;
                color: #6c757d;
            }
            
            .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }
            
            .popup-pagination {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px 25px;
                border-top: 1px solid #e9ecef;
                background: #f8f9fa;
                gap: 20px;
                font-weight: 600;
            }
            
            .prev-page, .next-page {
                background: #667eea;
                color: white;
                border: none;
                padding: 10px 25px;
                border-radius: 25px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-weight: 600;
                font-size: 0.9rem;
            }
            
            .prev-page:hover, .next-page:hover {
                background: #5a6fd8;
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            }
            
            .prev-page:disabled, .next-page:disabled {
                background: #e9ecef;
                color: #6c757d;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }
            
            .page-info {
                font-weight: 700;
                color: #495057;
                font-size: 1.1rem;
                background: white;
                padding: 8px 20px;
                border-radius: 20px;
                border: 2px solid #e9ecef;
                min-width: 80px;
                text-align: center;
            }
            
            .popup-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 25px;
                border-top: 1px solid #e9ecef;
                background: #f8f9fa;
                border-radius: 0 0 20px 20px;
            }
            
            .total-images {
                color: #6c757d;
                font-size: 0.9rem;
            }
            
            .footer-right {
                display: flex;
                gap: 15px;
            }
            
            .btn-cancel {
                padding: 12px 25px;
                border: none;
                border-radius: 25px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                background: #6c757d;
                color: white;
            }
            
            .btn-cancel:hover {
                background: #5a6268;
                transform: translateY(-2px);
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes slideUp {
                from { 
                    opacity: 0;
                    transform: translateY(50px) scale(0.9);
                }
                to { 
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* 카테고리 필터 스타일 */
            .popup-category-filters {
                padding: 15px 25px;
                background: #f8f9fa;
                border-bottom: 1px solid #e9ecef;
            }
            
            .category-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: center;
            }
            
            .category-tab {
                background: #fff;
                border: 2px solid #e9ecef;
                color: #6c757d;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                white-space: nowrap;
                display: flex;
                align-items: center;
                gap: 0.3rem;
            }
            
            .category-tab:hover {
                background: #e9ecef;
                border-color: #ced4da;
                transform: translateY(-1px);
            }
            
            .category-tab.active {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-color: #667eea;
                color: white;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                transform: translateY(-1px);
            }
            
            /* 단순 이미지 모달 스타일 */
            #simple-image-modal .simple-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.85);
                cursor: pointer;
            }
            
            #simple-image-modal .simple-modal-content {
                position: relative;
                max-width: 90vw;
                max-height: 90vh;
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
                animation: scaleIn 0.3s ease;
            }
            
            #simple-image-modal .simple-modal-header {
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            #simple-image-modal .simple-modal-header h3 {
                margin: 0;
                font-size: 1.3rem;
                font-weight: 600;
            }
            
            #simple-image-modal .simple-modal-close {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                font-size: 24px;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            #simple-image-modal .simple-modal-close:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: scale(1.1);
            }
            
            #simple-image-modal .simple-modal-body {
                text-align: center;
                padding: 0;
                position: relative;
            }
            
            #simple-image-modal .simple-modal-body img {
                max-width: 100%;
                max-height: 70vh;
                object-fit: contain;
                cursor: pointer;
                transition: transform 0.3s ease;
                display: block;
            }
            
            #simple-image-modal .simple-modal-body img:hover {
                transform: scale(1.02);
            }
            
            #simple-image-modal .simple-modal-hint {
                position: absolute;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 0.9rem;
                margin: 0;
                white-space: nowrap;
                pointer-events: none;
            }
            
            @keyframes scaleIn {
                from {
                    opacity: 0;
                    transform: scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }
            
            /* 반응형 */
            @media (max-width: 768px) {
                .popup-content {
                    width: 95%;
                    height: 95%;
                }
                
                .gallery-grid {
                    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                    gap: 10px;
                }
                
                .popup-controls {
                    flex-direction: column;
                    gap: 15px;
                    align-items: stretch;
                }
                
                #gallery-search {
                    width: 100%;
                }
                
                .footer-right {
                    flex-direction: column;
                    gap: 10px;
                }
                
                /* 단순 이미지 모달 반응형 */
                #simple-image-modal .simple-modal-content {
                    max-width: 95vw;
                    max-height: 95vh;
                }
                
                #simple-image-modal .simple-modal-header {
                    padding: 15px;
                }
                
                #simple-image-modal .simple-modal-header h3 {
                    font-size: 1.1rem;
                }
                
                #simple-image-modal .simple-modal-body img {
                    max-height: 60vh;
                }
                
                #simple-image-modal .simple-modal-hint {
                    bottom: 10px;
                    font-size: 0.8rem;
                    padding: 6px 12px;
                }
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    /**
     * 이벤트 바인딩
     */
    bindEvents() {
        const popup = this.popup;
        
        // 닫기 버튼들
        popup.querySelector('.popup-close').addEventListener('click', () => this.close());
        popup.querySelector('.btn-cancel').addEventListener('click', () => this.close());
        popup.querySelector('.popup-overlay').addEventListener('click', () => this.close());
        
        // 검색
        const searchInput = popup.querySelector('#gallery-search');
        const searchBtn = popup.querySelector('.search-btn');
        
        searchBtn.addEventListener('click', () => this.search());
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.search();
            }
        });
        
        // 카테고리 필터
        const categoryTabs = popup.querySelectorAll('.category-tab');
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                const category = e.target.dataset.category;
                this.filterByCategory(category);
            });
        });
        
        // 페이지네이션
        popup.querySelector('.prev-page').addEventListener('click', () => this.prevPage());
        popup.querySelector('.next-page').addEventListener('click', () => this.nextPage());
        
        // ESC 키로 닫기
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.state.isOpen) {
                this.close();
            }
        });
    }
    
    /**
     * 팝업 열기
     */
    open() {
        this.state.isOpen = true;
        this.popup.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // 첫 페이지 로드
        this.loadImages();
        
        this.trigger('open');
    }
    
    /**
     * 팝업 닫기
     */
    close() {
        this.state.isOpen = false;
        this.popup.classList.remove('active');
        document.body.style.overflow = '';
        
        // 상태 초기화
        this.state.currentPage = 1;
        this.state.searchQuery = '';
        this.popup.querySelector('#gallery-search').value = '';
        this.trigger('close');
    }
    
    /**
     * 이미지 로드
     */
    async loadImages() {
        if (this.state.loading) return;
        
        this.state.loading = true;
        this.showLoading(true);
        
        try {
            const params = new URLSearchParams({
                category: this.state.selectedCategory,
                page: this.state.currentPage,
                per_page: this.options.perPage,
                search: this.state.searchQuery
            });
            
            const response = await fetch(`${this.options.apiUrl}?${params}`);
            const data = await response.json();
            
            if (data.success) {
                this.state.images = data.data;
                this.state.totalPages = data.pagination.total_pages;
                
                this.renderImages();
                this.updatePagination(data.pagination);
                this.updateTotalCount(data.pagination.total_count);
                
                if (data.data.length === 0) {
                    this.showEmpty(true);
                }
            } else {
                throw new Error(data.message || '이미지를 불러올 수 없습니다.');
            }
            
        } catch (error) {
            console.error('Gallery load error:', error);
            this.showError(error.message);
        } finally {
            this.state.loading = false;
            this.showLoading(false);
        }
    }
    
    /**
     * 이미지 렌더링
     */
    renderImages() {
        const grid = this.popup.querySelector('.gallery-grid');
        
        grid.innerHTML = this.state.images.map(image => `
            <div class="gallery-item" data-id="${image.id}" data-image='${JSON.stringify(image)}'>
                <img src="${image.thumbnail}" alt="${image.title}" loading="lazy" />
                <div class="gallery-item-overlay">
                    <div class="gallery-item-title">${image.title}</div>
                    <div class="gallery-item-hint">🔍 클릭하여 확대</div>
                </div>
            </div>
        `).join('');
        
        // 이미지 클릭 이벤트 (단순 확대 기능)
        grid.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', (e) => {
                // 클릭시 즉시 이미지 확대
                this.openImageLightbox(item);
            });
        });
    }
    
    /**
     * 단순 이미지 확대 모달 열기
     */
    openImageLightbox(item) {
        const imageData = JSON.parse(item.dataset.image);
        const imageSrc = imageData.src || imageData.url || imageData.path || imageData.full_image;
        const imageTitle = imageData.title || '스티커 샘플';
        
        // 단순한 이미지 확대 모달 생성
        this.createSimpleImageModal(imageSrc, imageTitle);
    }
    
    /**
     * 단순한 이미지 확대 모달 생성
     */
    createSimpleImageModal(imageSrc, imageTitle) {
        // 기존 모달이 있으면 제거
        const existingModal = document.getElementById('simple-image-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // 새 모달 생성
        const modal = document.createElement('div');
        modal.id = 'simple-image-modal';
        modal.innerHTML = `
            <div class="simple-modal-overlay"></div>
            <div class="simple-modal-content">
                <div class="simple-modal-header">
                    <h3>${imageTitle}</h3>
                    <button class="simple-modal-close">&times;</button>
                </div>
                <div class="simple-modal-body">
                    <img src="${imageSrc}" alt="${imageTitle}" />
                    <p class="simple-modal-hint">💡 이미지를 클릭하면 닫힙니다</p>
                </div>
            </div>
        `;
        
        // 닫기 함수 정의
        const closeModal = () => {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = '';
            }, 300);
        };
        
        // 이벤트 리스너 추가
        modal.querySelector('.simple-modal-overlay').addEventListener('click', closeModal);
        modal.querySelector('.simple-modal-close').addEventListener('click', closeModal);
        modal.querySelector('.simple-modal-body img').addEventListener('click', closeModal);
        
        // 모달 스타일 추가
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 20000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        `;
        
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        // ESC 키로 닫기
        const closeOnEsc = (e) => {
            if (e.key === 'Escape') {
                closeModal();
                document.removeEventListener('keydown', closeOnEsc);
            }
        };
        document.addEventListener('keydown', closeOnEsc);
        
        // 모달이 제거될 때 이벤트 리스너도 정리
        modal.addEventListener('remove', () => {
            document.removeEventListener('keydown', closeOnEsc);
        });
        
        console.log('🖼️ Simple image modal opened:', imageTitle);
    }
    
    
    /**
     * 검색
     */
    search() {
        const searchInput = this.popup.querySelector('#gallery-search');
        this.state.searchQuery = searchInput.value.trim();
        this.state.currentPage = 1;
        this.loadImages();
    }
    
    /**
     * 이전 페이지
     */
    prevPage() {
        if (this.state.currentPage > 1) {
            this.state.currentPage--;
            this.loadImages();
        }
    }
    
    /**
     * 다음 페이지
     */
    nextPage() {
        if (this.state.currentPage < this.state.totalPages) {
            this.state.currentPage++;
            this.loadImages();
        }
    }
    
    /**
     * 페이지네이션 업데이트
     */
    updatePagination(pagination) {
        const prevBtn = this.popup.querySelector('.prev-page');
        const nextBtn = this.popup.querySelector('.next-page');
        const pageInfo = this.popup.querySelector('.page-info');
        
        prevBtn.disabled = !pagination.has_prev;
        nextBtn.disabled = !pagination.has_next;
        pageInfo.textContent = `${pagination.current_page} / ${pagination.total_pages}`;
    }
    
    /**
     * 총 개수 업데이트
     */
    updateTotalCount(count) {
        const totalElement = this.popup.querySelector('.total-images');
        totalElement.textContent = `📸 총 ${count.toLocaleString()}개 샘플 이미지`;
    }
    
    
    /**
     * 로딩 상태 표시
     */
    showLoading(show) {
        const loading = this.popup.querySelector('.gallery-loading');
        const grid = this.popup.querySelector('.gallery-grid');
        const empty = this.popup.querySelector('.gallery-empty');
        
        if (show) {
            loading.style.display = 'block';
            grid.style.display = 'none';
            empty.style.display = 'none';
        } else {
            loading.style.display = 'none';
            grid.style.display = 'grid';
        }
    }
    
    /**
     * 빈 상태 표시
     */
    showEmpty(show) {
        const empty = this.popup.querySelector('.gallery-empty');
        const grid = this.popup.querySelector('.gallery-grid');
        
        if (show) {
            empty.style.display = 'block';
            grid.style.display = 'none';
        } else {
            empty.style.display = 'none';
            grid.style.display = 'grid';
        }
    }
    
    /**
     * 에러 표시
     */
    showError(message) {
        const empty = this.popup.querySelector('.gallery-empty');
        empty.innerHTML = `<p style="color: #dc3545;">❌ ${message}</p>`;
        this.showEmpty(true);
    }
    
    /**
     * 카테고리 표시명 가져오기
     */
    getCategoryDisplayName() {
        const names = {
            'all': '전체',
            'sticker': '스티커',
            'namecard': '명함',
            'leaflet': '전단지',
            'cadarok': '카다록',
            'envelope': '봉투',
            'littleprint': '포스터',
            'msticker': '자석스티커',
            'merchandisebond': '상품권',
            'ncrflambeau': '양식지',
            'other': '기타'
        };
        
        return names[this.state.selectedCategory] || this.state.selectedCategory;
    }
    
    /**
     * 카테고리별 필터링
     */
    filterByCategory(category) {
        console.log('🏷️ Filtering by category:', category);
        
        // 상태 업데이트
        this.state.selectedCategory = category;
        this.state.currentPage = 1;
        
        // 탭 UI 업데이트
        const categoryTabs = this.popup.querySelectorAll('.category-tab');
        categoryTabs.forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.category === category) {
                tab.classList.add('active');
            }
        });
        
        // 헤더 제목 업데이트
        const titleElement = this.popup.querySelector('.popup-title h3');
        if (titleElement) {
            titleElement.textContent = `📸 ${this.getCategoryDisplayName()} 샘플 갤러리`;
        }
        
        // 갤러리 다시 로드
        this.loadImages();
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
     * 파괴
     */
    destroy() {
        if (this.popup) {
            this.popup.remove();
        }
        
        this.eventListeners.clear();
        console.log('PopupGallery destroyed');
    }
}

// 전역 등록
window.PopupGallery = PopupGallery;

console.log('✨ PopupGallery v1.0 loaded');