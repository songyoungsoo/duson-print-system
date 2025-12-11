/**
 * 갤러리 라이트박스 컴포넌트
 * 재사용 가능한 이미지 갤러리 및 확대 기능
 * 
 * 사용법:
 * const gallery = new GalleryLightbox('galleryContainer', {
 *     dataSource: '/api/get_images.php',
 *     productType: 'sticker'
 * });
 * gallery.init();
 */

class GalleryLightbox {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = {
            dataSource: options.dataSource || null,
            productType: options.productType || 'default',
            autoLoad: options.autoLoad !== false,
            zoomEnabled: options.zoomEnabled !== false,
            animationSpeed: options.animationSpeed || 0.2,
            ...options
        };
        
        this.images = [];
        this.currentIndex = 0;
        this.isInitialized = false;
        
        // 줌 애니메이션 변수
        this.targetX = 50;
        this.targetY = 50;
        this.currentX = 50;
        this.currentY = 50;
        this.targetSize = 100;
        this.currentSize = 100;
        this.animationFrame = null;
    }

    /**
     * 갤러리 초기화
     */
    init() {
        if (this.isInitialized) return;
        
        this.createHTML();
        this.bindEvents();
        
        if (this.options.autoLoad && this.options.dataSource) {
            this.loadImages();
        }
        
        this.isInitialized = true;
        console.log('GalleryLightbox 초기화 완료:', this.options.productType);
    }

    /**
     * HTML 구조 생성
     */
    createHTML() {
        if (!this.container) {
            console.error('갤러리 컨테이너를 찾을 수 없습니다.');
            return;
        }

        this.container.innerHTML = `
            <div class="gallery-container">
                <div class="zoom-box" id="zoomBox_${this.options.productType}">
                    <!-- 배경 이미지로 표시됩니다 -->
                </div>
                
                <!-- 썸네일 이미지들 -->
                <div class="thumbnail-grid" id="thumbnailGrid_${this.options.productType}">
                    <!-- 썸네일들이 여기에 동적으로 로드됩니다 -->
                </div>
            </div>
            
            <!-- 로딩 상태 -->
            <div id="galleryLoading_${this.options.productType}" class="gallery-loading">
                <p>이미지를 불러오는 중...</p>
            </div>
            
            <!-- 에러 상태 -->
            <div id="galleryError_${this.options.productType}" class="gallery-error" style="display: none;">
                <p>이미지를 불러올 수 없습니다.</p>
            </div>
        `;
    }

    /**
     * 이벤트 바인딩
     */
    bindEvents() {
        const zoomBox = document.getElementById(`zoomBox_${this.options.productType}`);
        
        if (!zoomBox || !this.options.zoomEnabled) return;

        // 마우스 움직임에 따른 확대 효과
        zoomBox.addEventListener('mousemove', (e) => {
            const rect = zoomBox.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            this.targetX = (x / rect.width) * 100;
            this.targetY = (y / rect.height) * 100;
            this.targetSize = 200; // 2배 확대
            
            if (!this.animationFrame) {
                this.animate();
            }
        });

        // 마우스 벗어나면 원래대로
        zoomBox.addEventListener('mouseleave', () => {
            this.targetX = 50;
            this.targetY = 50;
            this.targetSize = 100;
        });
    }

    /**
     * 부드러운 애니메이션
     */
    animate() {
        const zoomBox = document.getElementById(`zoomBox_${this.options.productType}`);
        if (!zoomBox) return;

        // 부드러운 전환 계산
        const ease = 0.1;
        this.currentX += (this.targetX - this.currentX) * ease;
        this.currentY += (this.targetY - this.currentY) * ease;
        this.currentSize += (this.targetSize - this.currentSize) * ease;

        // CSS 적용
        zoomBox.style.backgroundSize = `${this.currentSize}%`;
        zoomBox.style.backgroundPosition = `${this.currentX}% ${this.currentY}%`;

        // 애니메이션 계속 실행 (목표에 가까우면 멈춤)
        const threshold = 0.1;
        if (Math.abs(this.targetX - this.currentX) > threshold ||
            Math.abs(this.targetY - this.currentY) > threshold ||
            Math.abs(this.targetSize - this.currentSize) > threshold) {
            this.animationFrame = requestAnimationFrame(() => this.animate());
        } else {
            this.animationFrame = null;
        }
    }

    /**
     * 이미지 데이터 로드
     */
    async loadImages() {
        if (!this.options.dataSource) {
            console.error('데이터 소스가 지정되지 않았습니다.');
            return;
        }

        this.showLoading(true);

        try {
            const response = await fetch(this.options.dataSource);
            const data = await response.json();

            if (data.success && data.data.length > 0) {
                this.images = data.data;
                this.createThumbnails();
                console.log(`${this.options.productType} 갤러리 로드 완료:`, data.count + '개 이미지');
            } else {
                this.showError(`${this.options.productType} 샘플 이미지가 없습니다.`);
            }
        } catch (error) {
            console.error('이미지 로드 오류:', error);
            this.showError('이미지를 불러오는 중 오류가 발생했습니다: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * 썸네일 생성
     */
    createThumbnails() {
        const thumbnailGrid = document.getElementById(`thumbnailGrid_${this.options.productType}`);
        if (!thumbnailGrid) return;

        thumbnailGrid.innerHTML = '';

        this.images.forEach((image, index) => {
            const thumbnail = document.createElement('img');
            thumbnail.src = image.thumbnail;
            thumbnail.alt = image.title;
            thumbnail.className = index === 0 ? 'active' : '';
            thumbnail.title = image.title;
            
            thumbnail.addEventListener('click', () => {
                this.updateMainImage(index);
                this.updateThumbnailActive(index);
            });
            
            thumbnailGrid.appendChild(thumbnail);
        });

        // 첫 번째 이미지로 초기화
        if (this.images.length > 0) {
            this.updateMainImage(0);
        }

        console.log('썸네일 생성 완료:', this.images.length + '개');
    }

    /**
     * 메인 이미지 업데이트
     */
    updateMainImage(index) {
        if (this.images.length === 0 || index >= this.images.length) return;

        const zoomBox = document.getElementById(`zoomBox_${this.options.productType}`);
        const image = this.images[index];

        if (zoomBox) {
            zoomBox.style.backgroundImage = `url('${image.path}')`;
            zoomBox.style.backgroundSize = '100%';
            zoomBox.style.backgroundPosition = 'center center';
            
            // 애니메이션 상태 초기화
            this.currentSize = 100;
            this.currentX = 50;
            this.currentY = 50;
            this.targetSize = 100;
            this.targetX = 50;
            this.targetY = 50;
        }

        this.currentIndex = index;
        console.log('메인 이미지 업데이트:', image);
    }

    /**
     * 썸네일 활성 상태 업데이트
     */
    updateThumbnailActive(activeIndex) {
        const thumbnails = document.querySelectorAll(`#thumbnailGrid_${this.options.productType} img`);
        thumbnails.forEach((thumb, index) => {
            if (index === activeIndex) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }

    /**
     * 로딩 상태 표시/숨김
     */
    showLoading(show) {
        const loadingElement = document.getElementById(`galleryLoading_${this.options.productType}`);
        if (loadingElement) {
            loadingElement.style.display = show ? 'block' : 'none';
        }
    }

    /**
     * 에러 메시지 표시
     */
    showError(message) {
        const errorElement = document.getElementById(`galleryError_${this.options.productType}`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    /**
     * 수동으로 이미지 설정
     */
    setImages(images) {
        this.images = images;
        this.createThumbnails();
    }

    /**
     * 다음 이미지
     */
    nextImage() {
        if (this.images.length === 0) return;
        const nextIndex = (this.currentIndex + 1) % this.images.length;
        this.updateMainImage(nextIndex);
        this.updateThumbnailActive(nextIndex);
    }

    /**
     * 이전 이미지
     */
    prevImage() {
        if (this.images.length === 0) return;
        const prevIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.updateMainImage(prevIndex);
        this.updateThumbnailActive(prevIndex);
    }

    /**
     * 갤러리 정리
     */
    destroy() {
        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
        }
        
        if (this.container) {
            this.container.innerHTML = '';
        }
        
        this.isInitialized = false;
        console.log('GalleryLightbox 정리 완료');
    }
}

// 전역으로 사용 가능하게 설정
window.GalleryLightbox = GalleryLightbox;