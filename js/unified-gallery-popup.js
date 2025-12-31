/**
 * 통일된 갤러리 팝업 시스템
 * 1200px × 800px, 6×3 그리드, 페이지네이션
 * Created: 2025년 12월 10일
 */

class UnifiedGalleryPopup {
    constructor(options = {}) {
        this.options = {
            category: options.category || 'default',
            apiUrl: options.apiUrl || '/api/get_real_orders_portfolio.php',
            perPage: 18, // 6×3 그리드
            title: options.title || '갤러리',
            icon: options.icon || '📸',
            ...options
        };
        
        this.currentPage = 1;
        this.totalPages = 1;
        this.isLoading = false;
        this.data = [];
        
        this.init();
    }
    
    init() {
        this.createPopupHTML();
        this.bindEvents();
        console.log(`✨ ${this.options.category} 통일된 갤러리 팝업 초기화 완료`);
    }
    
    createPopupHTML() {
        // 기존 팝업이 있으면 제거
        const existingPopup = document.getElementById(`unified-gallery-popup-${this.options.category}`);
        if (existingPopup) {
            existingPopup.remove();
        }
        
        const popupHTML = `
            <div id="unified-gallery-popup-${this.options.category}" class="unified-gallery-popup" style="display: none;">
                <div class="unified-popup-container">
                    <div class="unified-popup-header">
                        <h3 class="unified-popup-title">
                            <span>${this.options.icon}</span>
                            <span>${this.options.title}</span>
                        </h3>
                        <button class="unified-popup-close" type="button">✕</button>
                    </div>
                    
                    <div class="unified-popup-body">
                        <div class="unified-gallery-grid" id="unified-gallery-grid-${this.options.category}">
                            <!-- 갤러리 카드들이 여기에 로드됩니다 -->
                        </div>
                        
                        <div class="unified-pagination" id="unified-pagination-${this.options.category}">
                            <div class="unified-page-info" id="unified-page-info-${this.options.category}">
                                페이지 1 / 1 (총 0개)
                            </div>
                            
                            <div class="unified-page-controls">
                                <button class="unified-page-btn" id="unified-first-btn-${this.options.category}" disabled>
                                    ◀◀ 맨 처음
                                </button>

                                <button class="unified-page-btn" id="unified-prev-btn-${this.options.category}" disabled>
                                    ← 이전
                                </button>

                                <div class="unified-page-numbers" id="unified-page-numbers-${this.options.category}">
                                    <!-- 페이지 번호들 -->
                                </div>

                                <button class="unified-page-btn" id="unified-next-btn-${this.options.category}" disabled>
                                    다음 →
                                </button>

                                <button class="unified-page-btn" id="unified-last-btn-${this.options.category}" disabled>
                                    맨 끝 ▶▶
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', popupHTML);
    }
    
    bindEvents() {
        const popup = document.getElementById(`unified-gallery-popup-${this.options.category}`);
        const closeBtn = popup.querySelector('.unified-popup-close');
        const firstBtn = document.getElementById(`unified-first-btn-${this.options.category}`);
        const prevBtn = document.getElementById(`unified-prev-btn-${this.options.category}`);
        const nextBtn = document.getElementById(`unified-next-btn-${this.options.category}`);
        const lastBtn = document.getElementById(`unified-last-btn-${this.options.category}`);

        // 팝업 닫기 이벤트
        closeBtn.addEventListener('click', () => this.close());
        popup.addEventListener('click', (e) => {
            if (e.target === popup) this.close();
        });

        // ESC 키로 닫기
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && popup.classList.contains('active')) {
                this.close();
            }
        });

        // 페이지네이션 이벤트
        firstBtn.addEventListener('click', () => this.goToPage(1));
        prevBtn.addEventListener('click', () => this.goToPage(this.currentPage - 1));
        nextBtn.addEventListener('click', () => this.goToPage(this.currentPage + 1));
        lastBtn.addEventListener('click', () => this.goToPage(this.totalPages));
    }
    
    async open() {
        console.log(`📸 ${this.options.category} 갤러리 팝업 열기`);

        const popup = document.getElementById(`unified-gallery-popup-${this.options.category}`);
        popup.style.display = 'flex';
        popup.classList.add('active');
        document.body.style.overflow = 'hidden';

        // 첫 페이지 로드
        await this.loadPage(1);
    }

    // show() 별칭 (open()과 동일)
    async show() {
        return this.open();
    }
    
    close() {
        console.log(`❌ ${this.options.category} 갤러리 팝업 닫기`);

        const popup = document.getElementById(`unified-gallery-popup-${this.options.category}`);
        popup.style.display = 'none';
        popup.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    async loadPage(page) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.currentPage = page;
        
        const galleryGrid = document.getElementById(`unified-gallery-grid-${this.options.category}`);
        
        // 로딩 상태 표시
        galleryGrid.innerHTML = '<div class="unified-gallery-loading">갤러리 로딩 중...</div>';
        
        try {
            const url = `${this.options.apiUrl}?category=${this.options.category}&page=${page}&per_page=${this.options.perPage}&all=true`;
            console.log(`🌐 API 호출:`, url);
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                this.data = data.data;
                this.renderGallery(data.data);
                this.updatePagination(data.pagination);
                
                console.log(`✅ ${this.options.category} 갤러리 로딩 성공:`, data.data.length + '개');
            } else {
                galleryGrid.innerHTML = '<div class="unified-gallery-error">갤러리 이미지를 불러올 수 없습니다.</div>';
                console.log(`❌ ${this.options.category} 갤러리 데이터 없음:`, data);
            }
        } catch (error) {
            console.error(`🚨 ${this.options.category} 갤러리 로딩 오류:`, error);
            galleryGrid.innerHTML = '<div class="unified-gallery-error">갤러리 로딩 중 오류가 발생했습니다.</div>';
        }
        
        this.isLoading = false;
    }
    
    renderGallery(images) {
        const galleryGrid = document.getElementById(`unified-gallery-grid-${this.options.category}`);
        galleryGrid.innerHTML = '';
        
        images.forEach((image, index) => {
            const cardHTML = `
                <div class="unified-gallery-card" onclick="UnifiedGalleryPopup.viewImage('${image.image_path}', '${image.title}')">
                    <img 
                        class="unified-card-image" 
                        src="${image.image_path}" 
                        alt="${image.title}"
                        onerror="this.src='/images/placeholder.jpg'; this.style.background='#f0f0f0';"
                    />
                    <div class="unified-card-title">${image.title}</div>
                </div>
            `;
            
            galleryGrid.insertAdjacentHTML('beforeend', cardHTML);
        });
    }
    
    updatePagination(pagination) {
        this.totalPages = pagination.total_pages;
        
        // 페이지 정보 업데이트
        const pageInfo = document.getElementById(`unified-page-info-${this.options.category}`);
        pageInfo.textContent = `페이지 ${pagination.current_page} / ${pagination.total_pages} (총 ${pagination.total_count.toLocaleString()}개)`;
        
        // 페이지네이션 버튼 상태
        const firstBtn = document.getElementById(`unified-first-btn-${this.options.category}`);
        const prevBtn = document.getElementById(`unified-prev-btn-${this.options.category}`);
        const nextBtn = document.getElementById(`unified-next-btn-${this.options.category}`);
        const lastBtn = document.getElementById(`unified-last-btn-${this.options.category}`);

        firstBtn.disabled = !pagination.has_prev;
        prevBtn.disabled = !pagination.has_prev;
        nextBtn.disabled = !pagination.has_next;
        lastBtn.disabled = !pagination.has_next;

        // 페이지 번호 생성
        this.renderPageNumbers(pagination);
    }
    
    renderPageNumbers(pagination) {
        const pageNumbers = document.getElementById(`unified-page-numbers-${this.options.category}`);
        pageNumbers.innerHTML = '';
        
        const currentPage = pagination.current_page;
        const totalPages = pagination.total_pages;
        
        // 페이지 범위 계산 (최대 5개 표시)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        // 시작이나 끝에서 5개를 채우기
        if (endPage - startPage < 4) {
            if (startPage === 1) {
                endPage = Math.min(totalPages, startPage + 4);
            } else {
                startPage = Math.max(1, endPage - 4);
            }
        }
        
        // 첫 페이지
        if (startPage > 1) {
            pageNumbers.insertAdjacentHTML('beforeend', `
                <button class="unified-page-btn" onclick="window.unifiedGalleryPopup_${this.options.category}.goToPage(1)">1</button>
            `);
            if (startPage > 2) {
                pageNumbers.insertAdjacentHTML('beforeend', `<span style="padding: 0 5px; color: #999;">...</span>`);
            }
        }
        
        // 페이지 번호들
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === currentPage ? ' active' : '';
            pageNumbers.insertAdjacentHTML('beforeend', `
                <button class="unified-page-btn${isActive}" onclick="window.unifiedGalleryPopup_${this.options.category}.goToPage(${i})">${i}</button>
            `);
        }
        
        // 마지막 페이지
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pageNumbers.insertAdjacentHTML('beforeend', `<span style="padding: 0 5px; color: #999;">...</span>`);
            }
            pageNumbers.insertAdjacentHTML('beforeend', `
                <button class="unified-page-btn" onclick="window.unifiedGalleryPopup_${this.options.category}.goToPage(${totalPages})">${totalPages}</button>
            `);
        }
    }
    
    goToPage(page) {
        if (page < 1 || page > this.totalPages || page === this.currentPage || this.isLoading) {
            return;
        }
        
        console.log(`📄 ${this.options.category} 페이지 이동: ${this.currentPage} → ${page}`);
        this.loadPage(page);
    }
    
    // 정적 메서드 - 이미지 확대보기
    static viewImage(imagePath, title) {
        console.log('🔍 이미지 확대보기:', title);
        
        // EnhancedImageLightbox가 있으면 사용
        if (typeof EnhancedImageLightbox !== 'undefined') {
            const lightbox = new EnhancedImageLightbox({
                closeOnImageClick: true,
                showNavigation: false,
                showCaption: true,
                enableKeyboard: true,
                zoomEnabled: true
            });
            
            lightbox.open([{
                src: imagePath,
                title: title,
                description: '실제 고객 주문으로 제작된 제품입니다. 클릭하면 닫힙니다.'
            }]);
        } else {
            // 폴백: 새 창으로 열기
            window.open(imagePath, '_blank');
        }
    }
}

// 전역 함수로 등록 (HTML onclick에서 사용)
window.UnifiedGalleryPopup = UnifiedGalleryPopup;