/**
 * 공통 갤러리 API 함수 라이브러리 v2.0
 * 두손기획인쇄 - 모든 품목에서 사용하는 갤러리 API 통신
 * 전단지(inserted) 갤러리의 성공한 패턴을 기준으로 작성
 */

class CommonGalleryAPI {
    constructor() {
        this.baseUrl = '/api/get_real_orders_portfolio.php';
        this.cache = new Map(); // 간단한 캐싱 시스템
        this.cacheTimeout = 5 * 60 * 1000; // 5분
    }
    
    /**
     * 품목별 포트폴리오 이미지 가져오기 (썸네일용 - 4개)
     * 
     * @param {string} category - 품목 카테고리 (예: 'inserted', 'namecard', 'envelope')
     * @param {number} count - 가져올 이미지 수 (기본값: 4)
     * @returns {Promise<Array>} 이미지 데이터 배열
     */
    async getThumbnailImages(category, count = 4) {
        const cacheKey = `thumbnails_${category}_${count}`;
        
        // 캐시 확인
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < this.cacheTimeout) {
                console.log('🚀 캐시에서 썸네일 이미지 로드:', category);
                return cached.data;
            }
        }
        
        try {
            console.log(`🔍 API에서 ${category} 썸네일 이미지 로드 시작`);
            
            const response = await fetch(`${this.baseUrl}?category=${category}&per_page=${count}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📊 API 응답 데이터:', data);
            
            if (data.success && data.data && Array.isArray(data.data)) {
                // 캐시에 저장
                this.cache.set(cacheKey, {
                    data: data.data,
                    timestamp: Date.now()
                });
                
                console.log(`✅ ${data.data.length}개 ${category} 썸네일 이미지 로드 완료`);
                return data.data;
            } else {
                console.warn('⚠️ API에서 유효한 데이터를 받지 못함:', data);
                return [];
            }
        } catch (error) {
            console.error(`❌ ${category} 썸네일 API 호출 실패:`, error);
            return [];
        }
    }
    
    /**
     * 품목별 전체 포트폴리오 이미지 가져오기 (팝업용)
     * 
     * @param {string} category - 품목 카테고리
     * @param {number} page - 페이지 번호 (기본값: 1)
     * @param {number} perPage - 페이지당 이미지 수 (기본값: 18)
     * @returns {Promise<Object>} 이미지 데이터 및 페이지네이션 정보
     */
    async getAllImages(category, page = 1, perPage = 18) {
        try {
            console.log(`🔍 API에서 ${category} 전체 이미지 로드 시작 (페이지: ${page})`);
            
            const response = await fetch(`${this.baseUrl}?category=${category}&page=${page}&per_page=${perPage}&all=true`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📊 전체 이미지 API 응답:', data);
            
            if (data.success) {
                console.log(`✅ ${category} 전체 이미지 로드 완료 (페이지 ${page}/${data.pagination?.total_pages || 1})`);
                return {
                    images: data.data || [],
                    pagination: data.pagination || {
                        current_page: 1,
                        total_pages: 1,
                        total_count: 0,
                        has_next: false,
                        has_prev: false
                    }
                };
            } else {
                console.warn('⚠️ 전체 이미지 API에서 유효한 데이터를 받지 못함:', data);
                return { images: [], pagination: {} };
            }
        } catch (error) {
            console.error(`❌ ${category} 전체 이미지 API 호출 실패:`, error);
            return { images: [], pagination: {} };
        }
    }
    
    /**
     * 플레이스홀더 이미지 생성
     * 
     * @param {string} categoryLabel - 품목명 (예: '전단지', '명함')
     * @param {number} count - 생성할 개수 (기본값: 4)
     * @returns {Array} 플레이스홀더 이미지 데이터 배열
     */
    generatePlaceholders(categoryLabel, count = 4) {
        console.log(`📷 ${categoryLabel} 플레이스홀더 이미지 생성 (${count}개)`);
        
        return Array.from({length: count}, (_, index) => ({
            id: `placeholder_${index + 1}`,
            title: `${categoryLabel} 샘플 ${index + 1}`,
            path: `https://via.placeholder.com/400x300?text=${encodeURIComponent(categoryLabel)}+샘플+${index + 1}&color=999`,
            thumbnail: `https://via.placeholder.com/200x150?text=샘플${index + 1}&color=ccc`,
            is_placeholder: true
        }));
    }
    
    /**
     * 카테고리 코드 변환 맵핑
     * 
     * @param {string} categoryLabel - 한글 품목명
     * @returns {string} API 카테고리 코드
     */
    getCategoryCode(categoryLabel) {
        const categoryMap = {
            '전단지': 'inserted',
            '명함': 'namecard', 
            '봉투': 'envelope',
            '포스터': 'littleprint',
            '카탈로그': 'cadarok',
            '상품권': 'merchandisebond',
            '자석스티커': 'msticker',
            '양식지': 'ncrflambeau',
            '스티커': 'sticker'
        };
        return categoryMap[categoryLabel] || categoryLabel.toLowerCase();
    }
    
    /**
     * 캐시 초기화
     */
    clearCache() {
        this.cache.clear();
        console.log('🗑️ 갤러리 API 캐시 초기화 완료');
    }
    
    /**
     * 이미지 URL 유효성 검증
     * 
     * @param {string} imageUrl - 검증할 이미지 URL
     * @returns {Promise<boolean>} 유효 여부
     */
    async validateImageUrl(imageUrl) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => resolve(true);
            img.onerror = () => resolve(false);
            img.src = imageUrl;
        });
    }
}

// 전역 인스턴스 생성
window.commonGalleryAPI = new CommonGalleryAPI();

/**
 * 레거시 호환성을 위한 함수들
 * 기존 전단지 갤러리 함수명과 호환
 */

// 전단지 갤러리 로드 함수 (레거시 호환)
async function loadLeafletGallery() {
    const images = await window.commonGalleryAPI.getThumbnailImages('inserted');
    if (images.length > 0) {
        renderGallery(images);
    } else {
        showPlaceholderImages();
    }
}

// 명함 갤러리 로드 함수
async function loadNamecardGallery() {
    const images = await window.commonGalleryAPI.getThumbnailImages('namecard');
    if (images.length > 0) {
        renderNamecardGallery(images);
    } else {
        showNamecardPlaceholder();
    }
}

// 봉투 갤러리 로드 함수  
async function loadEnvelopeGallery() {
    const images = await window.commonGalleryAPI.getThumbnailImages('envelope');
    if (images.length > 0) {
        renderEnvelopeGallery(images);
    } else {
        showEnvelopePlaceholder();
    }
}

// 포스터 갤러리 로드 함수
async function loadPosterGallery() {
    const images = await window.commonGalleryAPI.getThumbnailImages('littleprint');
    if (images.length > 0) {
        renderPosterGallery(images);
    } else {
        showPosterPlaceholder();
    }
}

// 범용 갤러리 로드 함수
async function loadCommonGallery(category, containerId, categoryLabel) {
    const images = await window.commonGalleryAPI.getThumbnailImages(category);
    if (images.length > 0) {
        renderCommonGallery(containerId, images, categoryLabel);
    } else {
        showCommonPlaceholder(containerId, categoryLabel);
    }
}

console.log('✅ 공통 갤러리 API 라이브러리 로드 완료');