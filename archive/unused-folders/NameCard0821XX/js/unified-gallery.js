/**
 * 통합 갤러리 시스템 - 명함 기준 개발
 * 기능: 썸네일 4개, 팝업 갤러리, 라이트박스, 페이지네이션
 */

class UnifiedGallery {
    constructor(productType, options = {}) {
        this.productType = productType;
        this.options = {
            thumbnailLimit: 4,
            popupLimit: 12,
            ...options
        };
        
        // 상태 관리
        this.currentImages = [];
        this.currentPage = 1;
        this.totalPages = 1;
        this.popupImages = [];
        this.lightboxIndex = 0;
        this.currentMainIndex = 0;
        
        // 포스터 방식 호버링 변수들
        this.hoverTargetX = 50;
        this.hoverTargetY = 50; 
        this.hoverTargetSize = 100;
        this.hoverCurrentX = 50;
        this.hoverCurrentY = 50;
        this.hoverCurrentSize = 100;
        this.hoverAnimationId = null;
        
        // DOM 요소
        this.mainImage = document.getElementById('mainImage');
        this.thumbnailStrip = document.getElementById('thumbnailStrip');
        this.mainViewer = document.getElementById('mainViewer');
        this.galleryPopup = document.getElementById('galleryPopup');
        this.imageGrid = document.getElementById('imageGrid');
        this.pagination = document.getElementById('pagination');
        this.lightbox = document.getElementById('lightbox');
        
        this.init();
    }
    
    async init() {
        console.log('통합 갤러리 시스템 초기화:', this.productType);
        
        try {
            await this.loadThumbnails();
            this.setupEventListeners();
        } catch (error) {
            console.error('갤러리 초기화 실패:', error);
            this.showError('갤러리를 불러올 수 없습니다.');
        }
    }
    
    async loadThumbnails() {
        try {
            const response = await fetch(`/api/get_real_orders_portfolio.php?category=${this.productType}&per_page=${this.options.thumbnailLimit}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.currentImages = data.data || [];
                this.renderThumbnails();
                this.setMainImage(0);
            } else {
                throw new Error(data.message || '이미지 로딩 실패');
            }
        } catch (error) {
            console.error('썸네일 로딩 실패:', error);
            this.loadDefaultImages();
        }
    }
    
    renderThumbnails() {
        if (!this.thumbnailStrip) return;
        
        // 썸네일 아이템들 업데이트
        const thumbnailItems = this.thumbnailStrip.querySelectorAll('.thumbnail-item');
        
        thumbnailItems.forEach((item, index) => {
            // 로딩 상태 제거
            item.classList.remove('loading');
            item.innerHTML = '';
            
            if (this.currentImages[index]) {
                const img = document.createElement('img');
                img.src = this.currentImages[index].path;
                img.alt = this.currentImages[index].title || '샘플 이미지';
                img.onerror = () => {
                    img.src = '/images/placeholder.jpg';
                };
                
                item.appendChild(img);
                item.onclick = () => this.setMainImage(index);
                
                // 첫 번째 썸네일 활성화
                if (index === 0) {
                    item.classList.add('active');
                }
            } else {
                // 기본 이미지 표시
                const img = document.createElement('img');
                img.src = '/images/placeholder.jpg';
                img.alt = '기본 이미지';
                item.appendChild(img);
            }
        });
    }
    
    setMainImage(index) {
        if (!this.currentImages[index] || !this.mainImage) return;
        
        this.currentMainIndex = index;
        
        // 메인 이미지 변경
        this.mainImage.src = this.currentImages[index].path;
        this.mainImage.alt = this.currentImages[index].title || '샘플 이미지';
        this.mainImage.onerror = () => {
            this.mainImage.src = '/images/placeholder.jpg';
        };
        
        // 썸네일 활성화 상태 변경
        const thumbnailItems = this.thumbnailStrip.querySelectorAll('.thumbnail-item');
        thumbnailItems.forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
        
        // 메인 이미지 호버링 시스템 초기화 (포스터 방식)
        this.initializeMainImageHover();
    }
    
    loadDefaultImages() {
        console.log('기본 이미지 로딩');
        
        // 기본 이미지 데이터 생성
        this.currentImages = Array.from({length: 4}, (_, i) => ({
            id: `default-${i}`,
            title: `${this.productType} 샘플 ${i + 1}`,
            path: '/images/placeholder.jpg',
            is_default: true
        }));
        
        this.renderThumbnails();
        this.setMainImage(0);
    }
    
    setupEventListeners() {
        // 메인 뷰어 클릭 - 라이트박스 열기
        if (this.mainViewer) {
            this.mainViewer.addEventListener('click', () => {
                this.openLightbox(this.currentMainIndex);
            });
        }
        
        // ESC 키로 팝업/라이트박스 닫기
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeGalleryPopup();
                this.closeLightbox();
            }
        });
        
        // 라이트박스 키보드 네비게이션
        document.addEventListener('keydown', (e) => {
            if (this.lightbox && this.lightbox.style.display !== 'none') {
                if (e.key === 'ArrowLeft') {
                    this.prevLightboxImage();
                } else if (e.key === 'ArrowRight') {
                    this.nextLightboxImage();
                }
            }
        });
    }
    
    async openGalleryPopup() {
        if (!this.galleryPopup) return;
        
        this.galleryPopup.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // 팝업 이미지 로딩
        await this.loadPopupImages(1);
    }
    
    closeGalleryPopup() {
        if (!this.galleryPopup) return;
        
        this.galleryPopup.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    async loadPopupImages(page = 1) {
        if (!this.imageGrid) return;
        
        try {
            // 로딩 상태 표시
            this.imageGrid.innerHTML = `
                <div class="grid-loading">
                    <div class="loading-spinner"></div>
                    <p>포트폴리오를 불러오는 중...</p>
                </div>
            `;
            
            const response = await fetch(`/api/get_real_orders_portfolio.php?category=${this.productType}&page=${page}&per_page=${this.options.popupLimit}&all=true`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.popupImages = data.data || [];
                this.currentPage = data.pagination.current_page;
                this.totalPages = data.pagination.total_pages;
                
                this.renderPopupImages();
                this.renderPagination();
            } else {
                throw new Error(data.message || '팝업 이미지 로딩 실패');
            }
        } catch (error) {
            console.error('팝업 이미지 로딩 실패:', error);
            this.imageGrid.innerHTML = `
                <div class="grid-loading">
                    <p style="color: #dc3545;">이미지를 불러올 수 없습니다.</p>
                    <button onclick="gallery.loadPopupImages(${page})" 
                            style="margin-top: 10px; padding: 8px 16px; background: #17a2b8; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        다시 시도
                    </button>
                </div>
            `;
        }
    }
    
    renderPopupImages() {
        if (!this.imageGrid || !this.popupImages.length) return;
        
        this.imageGrid.innerHTML = '';
        
        this.popupImages.forEach((image, index) => {
            const img = document.createElement('img');
            img.src = image.path;
            img.alt = image.title || '포트폴리오 이미지';
            img.className = 'grid-image';
            img.loading = 'lazy';
            
            img.onerror = () => {
                img.src = '/images/placeholder.jpg';
            };
            
            img.addEventListener('click', () => {
                this.openLightbox(index);
            });
            
            this.imageGrid.appendChild(img);
        });
    }
    
    renderPagination() {
        if (!this.pagination || this.totalPages <= 1) {
            if (this.pagination) {
                this.pagination.style.display = 'none';
            }
            return;
        }
        
        this.pagination.style.display = 'flex';
        this.pagination.innerHTML = '';
        
        // 이전 버튼
        const prevBtn = document.createElement('button');
        prevBtn.textContent = '‹ 이전';
        prevBtn.disabled = this.currentPage <= 1;
        prevBtn.onclick = () => this.loadPopupImages(this.currentPage - 1);
        this.pagination.appendChild(prevBtn);
        
        // 페이지 번호
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(this.totalPages, this.currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = i === this.currentPage ? 'active' : '';
            pageBtn.onclick = () => this.loadPopupImages(i);
            this.pagination.appendChild(pageBtn);
        }
        
        // 다음 버튼
        const nextBtn = document.createElement('button');
        nextBtn.textContent = '다음 ›';
        nextBtn.disabled = this.currentPage >= this.totalPages;
        nextBtn.onclick = () => this.loadPopupImages(this.currentPage + 1);
        this.pagination.appendChild(nextBtn);
    }
    
    openLightbox(index) {
        if (!this.lightbox) return;
        
        // 현재 활성 이미지 배열 결정 (팝업이 열려있으면 팝업 이미지, 아니면 썸네일 이미지)
        const activeImages = this.galleryPopup.style.display === 'flex' ? this.popupImages : this.currentImages;
        
        if (!activeImages[index]) return;
        
        this.lightboxIndex = index;
        this.lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        this.updateLightboxImage(activeImages[index]);
    }
    
    updateLightboxImage(image) {
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxTitle = document.getElementById('lightboxTitle');
        const lightboxCategory = document.getElementById('lightboxCategory');
        
        if (lightboxImage) {
            lightboxImage.src = image.path;
            lightboxImage.alt = image.title || '이미지';
        }
        
        if (lightboxTitle) {
            lightboxTitle.textContent = image.title || '제목 없음';
        }
        
        if (lightboxCategory) {
            lightboxCategory.textContent = image.category || this.productType;
        }
    }
    
    closeLightbox() {
        if (!this.lightbox) return;
        
        this.lightbox.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    prevLightboxImage() {
        const activeImages = this.galleryPopup.style.display === 'flex' ? this.popupImages : this.currentImages;
        
        if (this.lightboxIndex > 0) {
            this.lightboxIndex--;
            this.updateLightboxImage(activeImages[this.lightboxIndex]);
        }
    }
    
    nextLightboxImage() {
        const activeImages = this.galleryPopup.style.display === 'flex' ? this.popupImages : this.currentImages;
        
        if (this.lightboxIndex < activeImages.length - 1) {
            this.lightboxIndex++;
            this.updateLightboxImage(activeImages[this.lightboxIndex]);
        }
    }
    
    showError(message) {
        console.error('갤러리 오류:', message);
        
        if (this.thumbnailStrip) {
            this.thumbnailStrip.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #dc3545;">
                    ${message}
                </div>
            `;
        }
    }
    
    initializeMainImageHover() {
        if (!this.mainViewer || !this.mainImage) return;
        
        console.log('🎯 포스터 방식 단순 호버링 시스템 초기화');
        
        // 포스터와 동일한 변수 초기화
        this.hoverTargetX = 50;
        this.hoverTargetY = 50; 
        this.hoverTargetSize = 100;
        this.hoverCurrentX = 50;
        this.hoverCurrentY = 50;
        this.hoverCurrentSize = 100;
        
        // 기존 애니메이션 정지
        if (this.hoverAnimationId) {
            cancelAnimationFrame(this.hoverAnimationId);
        }
        
        // 마우스 움직임 추적 (포스터 방식 동일)
        this.mainViewer.addEventListener('mousemove', (e) => {
            const rect = this.mainViewer.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            this.hoverTargetX = x;
            this.hoverTargetY = y;
            this.hoverTargetSize = 135; // 1.35배 확대
        });
        
        // 마우스 벗어날 때 초기화 (핵심!)
        this.mainViewer.addEventListener('mouseleave', () => {
            this.hoverTargetX = 50;
            this.hoverTargetY = 50;
            this.hoverTargetSize = 100;
            console.log('👋 호버 초기화: 중앙(50%, 50%), 원래크기(100%)');
        });
        
        // 부드러운 애니메이션 시작 (포스터 방식 동일)
        this.startHoverAnimation();
        
        // 라이트박스 클릭 이벤트
        this.mainViewer.addEventListener('click', () => {
            this.openLightbox(this.currentMainIndex);
        });
        
        console.log('✅ 포스터 방식 단순 호버링 설정 완료');
    }
    
    startHoverAnimation() {
        if (this.hoverAnimationId) {
            cancelAnimationFrame(this.hoverAnimationId);
        }
        
        const animate = () => {
            if (!this.mainImage || !this.mainViewer) return;
            
            // 포스터와 동일한 부드러운 보간 (0.08 lerp 계수)
            this.hoverCurrentX += (this.hoverTargetX - this.hoverCurrentX) * 0.08;
            this.hoverCurrentY += (this.hoverTargetY - this.hoverCurrentY) * 0.08;
            this.hoverCurrentSize += (this.hoverTargetSize - this.hoverCurrentSize) * 0.08;
            
            // CSS 적용
            this.mainImage.style.transformOrigin = `${this.hoverCurrentX}% ${this.hoverCurrentY}%`;
            
            if (this.hoverCurrentSize > 100.1) {
                this.mainImage.style.transform = `scale(${this.hoverCurrentSize / 100})`;
                this.mainImage.style.transition = 'none'; // 애니메이션 중엔 CSS transition 비활성화
            } else {
                this.mainImage.style.transform = 'scale(1)';
                this.mainImage.style.transformOrigin = 'center center';
            }
            
            this.hoverAnimationId = requestAnimationFrame(animate);
        };
        
        animate();
    }
}

// 전역 함수들 (HTML onclick에서 사용)
let gallery = null;

function openGalleryPopup() {
    if (gallery) {
        gallery.openGalleryPopup();
    }
}

function closeGalleryPopup() {
    if (gallery) {
        gallery.closeGalleryPopup();
    }
}

function openLightbox(index) {
    if (gallery) {
        gallery.openLightbox(index);
    }
}

function closeLightbox() {
    if (gallery) {
        gallery.closeLightbox();
    }
}

function prevLightboxImage() {
    if (gallery) {
        gallery.prevLightboxImage();
    }
}

function nextLightboxImage() {
    if (gallery) {
        gallery.nextLightboxImage();
    }
}

// 페이지 로드 시 갤러리 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 현재 페이지의 제품 타입 감지
    const pageUrl = window.location.pathname;
    let productType = 'namecard'; // 기본값
    
    if (pageUrl.includes('/NameCard/')) {
        productType = 'namecard';
    } else if (pageUrl.includes('/envelope/')) {
        productType = 'envelope';
    } else if (pageUrl.includes('/sticker/')) {
        productType = 'sticker';
    } else if (pageUrl.includes('/leaflet/') || pageUrl.includes('/inserted/')) {
        productType = 'leaflet';
    } else if (pageUrl.includes('/LittlePrint/')) {
        productType = 'poster';
    } else if (pageUrl.includes('/cadarok/')) {
        productType = 'cadarok';
    } else if (pageUrl.includes('/MerchandiseBond/')) {
        productType = 'merchandisebond';
    } else if (pageUrl.includes('/msticker/')) {
        productType = 'msticker';
    } else if (pageUrl.includes('/NcrFlambeau/')) {
        productType = 'ncrflambeau';
    }
    
    // 갤러리 초기화
    gallery = new UnifiedGallery(productType);
    
    console.log('통합 갤러리 시스템 로드 완료:', productType);
});