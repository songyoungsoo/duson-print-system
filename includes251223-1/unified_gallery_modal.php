<?php
/**
 * 통일된 갤러리 모달 - 모든 제품 페이지에서 사용
 * 스티커 스타일의 모달로 통일
 */
?>

<!-- 통일된 갤러리 모달 -->
<div id="unifiedGalleryModal" class="unified-gallery-modal" style="display: none;">
    <div class="modal-overlay" onclick="closeUnifiedModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <span id="modalIcon">📂</span>
                <span id="modalTitle">샘플 갤러리</span>
            </h2>
            <button class="modal-close" onclick="closeUnifiedModal()">✕</button>
        </div>
        
        <div class="modal-body">
            <!-- 갤러리 그리드 -->
            <div id="unifiedGalleryGrid" class="gallery-grid">
                <div class="loading-message">
                    <span class="loading-icon">⏳</span>
                    <p>이미지를 불러오는 중...</p>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <!-- 페이지네이션 -->
            <div class="pagination" id="unifiedPagination">
                <button class="page-btn page-first" onclick="loadUnifiedPage(1)" id="firstBtn" title="맨 처음">⏮ 맨처음</button>
                <button class="page-btn page-prev" onclick="loadUnifiedPage('prev')" id="prevBtn" title="이전 페이지">◀ 이전</button>
                <div class="page-numbers" id="pageNumbers"></div>
                <button class="page-btn page-next" onclick="loadUnifiedPage('next')" id="nextBtn" title="다음 페이지">다음 ▶</button>
                <button class="page-btn page-last" onclick="loadUnifiedPage('last')" id="lastBtn" title="맨 끝">맨끝 ⏭</button>
                <div class="page-jump">
                    <input type="number" id="pageJumpInput" min="1" placeholder="페이지" title="페이지 번호를 입력하세요">
                    <button class="jump-btn" onclick="jumpToPage()" title="페이지로 이동">이동</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* 통일된 갤러리 모달 스타일 */
.unified-gallery-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
}

.unified-gallery-modal .modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    animation: fadeIn 0.3s ease;
}

.unified-gallery-modal .modal-content {
    position: relative;
    width: 1200px;
    max-width: 95vw;
    height: auto;
    max-height: 90vh;
    background: white;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.unified-gallery-modal .modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px 16px 0 0;
}

.unified-gallery-modal .modal-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.unified-gallery-modal .modal-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: white;
    color: #6c757d;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.unified-gallery-modal .modal-close:hover {
    background: #dc3545;
    color: white;
    transform: rotate(90deg);
}

.unified-gallery-modal .modal-body {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8f9fa;
}

.unified-gallery-modal .gallery-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    grid-template-rows: repeat(3, 1fr);
    gap: 15px;
    width: 100%;
    min-height: 450px;
}

.unified-gallery-modal .gallery-item {
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
}

.unified-gallery-modal .gallery-item:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.unified-gallery-modal .gallery-item img {
    width: 100%;
    height: 140px;
    object-fit: contain;
    background-color: #d1d5db;
    border: none;
}

.unified-gallery-modal .gallery-item-title {
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 500;
    color: #495057;
    text-align: center;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.unified-gallery-modal .modal-footer {
    padding: 15px 25px;
    border-top: 1px solid #e5e7eb;
    background: white;
    border-radius: 0 0 16px 16px;
}

#unifiedGalleryModal .pagination {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
    padding: 15px 25px !important;
}

.unified-gallery-modal .modal-footer .pagination {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
}

.unified-gallery-modal .page-btn {
    padding: 8px 16px;
    border: 1px solid #dee2e6;
    background: white;
    color: #495057;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    font-weight: 500;
}

.unified-gallery-modal .page-btn:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.unified-gallery-modal .page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.unified-gallery-modal .page-numbers {
    display: flex;
    gap: 5px;
}

.unified-gallery-modal .page-number {
    width: 32px;
    height: 32px;
    border: 1px solid #dee2e6;
    background: white;
    color: #495057;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.unified-gallery-modal .page-number:hover {
    background: #f8f9fa;
}

.unified-gallery-modal .page-number.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.unified-gallery-modal .page-ellipsis {
    padding: 0 8px;
    color: #6c757d;
    font-weight: bold;
    display: flex;
    align-items: center;
}

.unified-gallery-modal .page-jump {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-left: 15px;
    padding-left: 15px;
    border-left: 1px solid #dee2e6;
}

.unified-gallery-modal .page-jump input {
    width: 60px;
    height: 32px;
    padding: 0 8px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    text-align: center;
    font-size: 0.9rem;
}

.unified-gallery-modal .page-jump input:focus {
    outline: none;
    border-color: #007bff;
}

.unified-gallery-modal .jump-btn {
    height: 32px;
    padding: 0 12px;
    border: 1px solid #007bff;
    background: #007bff;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.unified-gallery-modal .jump-btn:hover {
    background: #0056b3;
    border-color: #0056b3;
}

.unified-gallery-modal .loading-message {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.unified-gallery-modal .loading-icon {
    font-size: 2rem;
    animation: spin 1s linear infinite;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .unified-gallery-modal .modal-content {
        width: 95%;
        height: 95%;
    }
    
    .unified-gallery-modal .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }
    
    .unified-gallery-modal .gallery-grid img {
        height: 120px;
    }
}
</style>

<script>
// 통일된 갤러리 모달 JavaScript
let unifiedCurrentPage = 1;
let unifiedTotalPages = 1;
let currentCategory = '';

// 모달 열기 함수
function openUnifiedModal(category, icon = '📂') {
    currentCategory = category;
    const modal = document.getElementById('unifiedGalleryModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalIcon = document.getElementById('modalIcon');
    
    if (modal) {
        modalTitle.textContent = category + ' 샘플 갤러리';
        modalIcon.textContent = icon;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // 첫 페이지 로드
        loadUnifiedPage(1);
    }
}

// 모달 닫기 함수
function closeUnifiedModal() {
    const modal = document.getElementById('unifiedGalleryModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUnifiedModal();
    }
});

// 갤러리 더보기 버튼 이벤트 리스너
document.addEventListener('DOMContentLoaded', function() {
    const moreButtons = document.querySelectorAll('.gallery-more-thumb');
    moreButtons.forEach(button => {
        button.addEventListener('click', function() {
            const product = this.getAttribute('data-product') || '';
            const categoryMap = {
                'msticker': '자석스티커',
                'inserted': '전단지',
                'namecard': '명함',
                'envelope': '봉투',
                'sticker': '스티커',
                'littleprint': '포스터',
                'cadarok': '카탈로그',
                'merchandisebond': '상품권',
                'ncrflambeau': '양식지',
                'leaflet': '전단지'
            };
            const category = categoryMap[product] || product;
            openUnifiedModal(category, '📂');
        });
    });
});

// 페이지 로드 함수
async function loadUnifiedPage(page) {
    if (typeof page === 'string') {
        if (page === 'prev') {
            page = Math.max(1, unifiedCurrentPage - 1);
        } else if (page === 'next') {
            page = Math.min(unifiedTotalPages, unifiedCurrentPage + 1);
        } else if (page === 'last') {
            page = unifiedTotalPages;
        } else {
            page = parseInt(page);
        }
    }
    
    const gallery = document.getElementById('unifiedGalleryGrid');
    if (!gallery) return;
    
    // 로딩 표시
    gallery.innerHTML = '<div class="loading-message"><span class="loading-icon">⏳</span><p>이미지를 불러오는 중...</p></div>';
    
    try {
        let allImages = [];
        
        // 카탈로그의 경우 카다록과 리플렛 두 카테고리를 함께 가져오기
        if (currentCategory === '카탈로그') {
            const [cadarokResponse, leafletResponse] = await Promise.all([
                fetch(`/api/get_real_orders_portfolio.php?category=cadarok&page=${page}&per_page=9`),
                fetch(`/api/get_real_orders_portfolio.php?category=leaflet&page=${page}&per_page=9`)
            ]);
            
            const cadarokData = await cadarokResponse.json();
            const leafletData = await leafletResponse.json();
            
            // 두 카테고리의 이미지를 합치기
            if (cadarokData.success && cadarokData.data) {
                allImages = allImages.concat(cadarokData.data);
            }
            if (leafletData.success && leafletData.data) {
                allImages = allImages.concat(leafletData.data);
            }
            
            // 가상 페이지네이션 정보 생성 (18개씩 표시)
            const totalItems = allImages.length;
            unifiedTotalPages = Math.max(1, Math.ceil(totalItems / 18));
            unifiedCurrentPage = page;
            
            // 현재 페이지에 해당하는 이미지만 표시
            const startIndex = (page - 1) * 18;
            const endIndex = startIndex + 18;
            allImages = allImages.slice(startIndex, endIndex);
            
        } else {
            // 다른 카테고리는 기존 방식대로
            const response = await fetch(`/api/get_real_orders_portfolio.php?category=${encodeURIComponent(getCategoryCode(currentCategory))}&page=${page}&per_page=18`);
            const data = await response.json();
            
            if (data.success && data.data) {
                allImages = data.data;
                unifiedCurrentPage = data.pagination.current_page;
                unifiedTotalPages = data.pagination.total_pages;
            }
        }
        
        if (allImages.length > 0) {
            // 갤러리 업데이트 - 이미지 + 제목 구조
            gallery.innerHTML = '';
            allImages.forEach(image => {
                // ⚠️ 파일이 존재하지 않는 경우 렌더링하지 않음
                if (image.file_exists === false) {
                    return; // 건너뛰기
                }

                const galleryItem = document.createElement('div');
                galleryItem.className = 'gallery-item';
                galleryItem.onclick = () => viewLargeImage(image.path, image.title);

                const img = document.createElement('img');
                img.src = image.path;
                img.alt = image.title;

                // ⚠️ 이미지 로드 실패 시 gallery-item 자체를 DOM에서 제거
                img.onerror = function() {
                    console.warn('이미지 로드 실패:', image.path);
                    if (galleryItem.parentNode) {
                        galleryItem.parentNode.removeChild(galleryItem);
                    }
                };

                const title = document.createElement('div');
                title.className = 'gallery-item-title';
                title.textContent = image.title || '샘플 이미지';

                galleryItem.appendChild(img);
                galleryItem.appendChild(title);
                gallery.appendChild(galleryItem);
            });
            
            // 페이지네이션 UI 업데이트
            updateUnifiedPagination({
                current_page: unifiedCurrentPage,
                total_pages: unifiedTotalPages,
                has_prev: unifiedCurrentPage > 1,
                has_next: unifiedCurrentPage < unifiedTotalPages
            });
        } else {
            gallery.innerHTML = '<div class="loading-message"><p>이미지를 불러올 수 없습니다.</p></div>';
        }
    } catch (error) {
        console.error('갤러리 로드 오류:', error);
        gallery.innerHTML = '<div class="loading-message"><p>오류가 발생했습니다.</p></div>';
    }
}

// 페이지네이션 UI 업데이트
function updateUnifiedPagination(pagination) {
    const firstBtn = document.getElementById('firstBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const lastBtn = document.getElementById('lastBtn');
    const pageNumbers = document.getElementById('pageNumbers');
    const pageJumpInput = document.getElementById('pageJumpInput');

    // 맨처음/맨끝/이전/다음 버튼 상태
    firstBtn.disabled = pagination.current_page === 1;
    prevBtn.disabled = !pagination.has_prev;
    nextBtn.disabled = !pagination.has_next;
    lastBtn.disabled = pagination.current_page === pagination.total_pages;

    // 페이지 점프 입력 필드 설정
    pageJumpInput.max = pagination.total_pages;
    pageJumpInput.placeholder = `1-${pagination.total_pages}`;

    // 페이지 번호 생성 (개선된 알고리즘)
    pageNumbers.innerHTML = '';
    const currentPage = pagination.current_page;
    const totalPages = pagination.total_pages;

    // 항상 첫 페이지 표시
    if (totalPages > 0) {
        addPageButton(1, currentPage === 1);
    }

    // ... 표시 로직 (첫 페이지와 표시 범위 사이)
    if (currentPage > 4) {
        addEllipsis();
    }

    // 현재 페이지 주변 표시 (좌우 2개씩)
    const rangeStart = Math.max(2, currentPage - 2);
    const rangeEnd = Math.min(totalPages - 1, currentPage + 2);

    for (let i = rangeStart; i <= rangeEnd; i++) {
        addPageButton(i, i === currentPage);
    }

    // ... 표시 로직 (표시 범위와 마지막 페이지 사이)
    if (currentPage < totalPages - 3) {
        addEllipsis();
    }

    // 항상 마지막 페이지 표시 (페이지가 2개 이상일 때)
    if (totalPages > 1) {
        addPageButton(totalPages, currentPage === totalPages);
    }

    // 헬퍼 함수: 페이지 버튼 추가
    function addPageButton(pageNum, isActive) {
        const pageBtn = document.createElement('button');
        pageBtn.className = 'page-number' + (isActive ? ' active' : '');
        pageBtn.textContent = pageNum;
        pageBtn.onclick = () => loadUnifiedPage(pageNum);
        pageNumbers.appendChild(pageBtn);
    }

    // 헬퍼 함수: ... (생략 표시) 추가
    function addEllipsis() {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'page-ellipsis';
        ellipsis.textContent = '...';
        pageNumbers.appendChild(ellipsis);
    }
}

// 카테고리 코드 변환
function getCategoryCode(category) {
    const categoryMap = {
        '전단지': 'inserted',
        '스티커': 'sticker',
        '명함': 'namecard',
        '봉투': 'envelope',
        '포스터': 'littleprint',
        '카탈로그': 'cadarok',
        '상품권': 'merchandisebond',
        '자석스티커': 'msticker',
        '양식지': 'ncrflambeau'
    };
    return categoryMap[category] || category.toLowerCase();
}

// 페이지 점프 함수
function jumpToPage() {
    const pageJumpInput = document.getElementById('pageJumpInput');
    const targetPage = parseInt(pageJumpInput.value);

    if (!isNaN(targetPage) && targetPage >= 1 && targetPage <= unifiedTotalPages) {
        loadUnifiedPage(targetPage);
        pageJumpInput.value = ''; // 입력 필드 초기화
    } else {
        alert(`1부터 ${unifiedTotalPages} 사이의 페이지 번호를 입력하세요.`);
        pageJumpInput.value = '';
        pageJumpInput.focus();
    }
}

// Enter 키로 페이지 점프
document.addEventListener('DOMContentLoaded', function() {
    const pageJumpInput = document.getElementById('pageJumpInput');
    if (pageJumpInput) {
        pageJumpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                jumpToPage();
            }
        });
    }
});

// 큰 이미지 보기 - 라이트박스 방식
function viewLargeImage(imagePath, title) {
    // 라이트박스 생성
    const lightbox = document.createElement('div');
    lightbox.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 20000;
        cursor: pointer;
        animation: fadeIn 0.3s ease;
    `;
    
    // 이미지 컨테이너
    const imgContainer = document.createElement('div');
    imgContainer.style.cssText = `
        position: relative;
        max-width: 90%;
        max-height: 90%;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    // 이미지
    const img = document.createElement('img');
    img.src = imagePath;
    img.alt = title || '확대 이미지';
    img.style.cssText = `
        max-width: 100%;
        max-height: 90vh;
        object-fit: contain;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        animation: slideUp 0.3s ease;
    `;
    
    // 닫기 버튼
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '✕';
    closeBtn.style.cssText = `
        position: absolute;
        top: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        border: none;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 24px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 20001;
    `;
    
    closeBtn.onmouseover = () => {
        closeBtn.style.background = 'rgba(255, 255, 255, 0.3)';
        closeBtn.style.transform = 'rotate(90deg)';
    };
    
    closeBtn.onmouseout = () => {
        closeBtn.style.background = 'rgba(255, 255, 255, 0.1)';
        closeBtn.style.transform = 'rotate(0deg)';
    };
    
    // 타이틀
    if (title) {
        const titleDiv = document.createElement('div');
        titleDiv.textContent = title;
        titleDiv.style.cssText = `
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
        `;
        lightbox.appendChild(titleDiv);
    }
    
    // 클릭으로 닫기 기능
    const closeLightbox = () => {
        lightbox.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(lightbox);
            document.body.style.overflow = '';
        }, 300);
    };
    
    // 라이트박스 전체 클릭 시 닫기
    lightbox.onclick = closeLightbox;
    
    // 이미지 클릭 시에도 닫기
    img.onclick = (e) => {
        e.stopPropagation();
        closeLightbox();
    };
    
    // 닫기 버튼 클릭
    closeBtn.onclick = (e) => {
        e.stopPropagation();
        closeLightbox();
    };
    
    // ESC 키로 닫기
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeLightbox();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // DOM에 추가
    imgContainer.appendChild(img);
    lightbox.appendChild(imgContainer);
    lightbox.appendChild(closeBtn);
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
}
</script>