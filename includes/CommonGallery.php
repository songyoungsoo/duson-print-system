<?php
/**
 * 공통 갤러리 컴포넌트 v2.0
 * 두손기획인쇄 - 모든 품목에서 재사용 가능한 갤러리 시스템
 * 전단지(inserted) 갤러리 시스템을 기준으로 공통화
 * 
 * 기능:
 * - 포스터 방식 배경 이미지 호버 확대
 * - API 기반 실제 주문 데이터 로드
 * - 통합 모달 팝업 지원
 * - 품목별 브랜드 색상 적용
 * - 반응형 디자인
 */

class CommonGallery {
    
    /**
     * 갤러리 HTML 구조 생성
     * 
     * @param array $config 갤러리 설정 배열
     *   - category: API 카테고리 (예: 'inserted', 'namecard', 'envelope')
     *   - categoryLabel: 표시용 라벨 (예: '전단지', '명함', '봉투')  
     *   - brandColor: 브랜드 색상 (예: '#4caf50', '#2196f3', '#ff9800')
     *   - icon: 아이콘 (예: '📄', '💳', '✉️')
     *   - apiUrl: API 엔드포인트 (기본값: '/api/get_real_orders_portfolio.php')
     *   - thumbnailCount: 썸네일 개수 (기본값: 4)
     * @return string HTML 코드
     */
    public static function render($config = []) {
        // 기본 설정
        $defaults = [
            'category' => 'inserted',
            'categoryLabel' => '전단지', 
            'brandColor' => '#4caf50',
            'icon' => '📄',
            'apiUrl' => '/api/get_real_orders_portfolio.php',
            'thumbnailCount' => 4,
            'containerId' => 'commonGallery'
        ];
        
        $config = array_merge($defaults, $config);
        
        // HTML ID와 클래스명에 카테고리 포함
        $categoryClass = strtolower($config['category']);
        $uniqueId = $config['containerId'] . '_' . $categoryClass;
        
        // CSS 변수로 브랜드 색상 설정
        $cssVars = "--brand-color: {$config['brandColor']}; --brand-color-dark: " . self::darkenColor($config['brandColor'], 20) . ";";
        
        return "
        <!-- 공통 갤러리 컴포넌트 v2.0 -->
        <section class=\"common-gallery-section {$categoryClass}-gallery\" style=\"{$cssVars}\" aria-label=\"{$config['categoryLabel']} 샘플 갤러리\">
            <div class=\"gallery-section\">
                <div class=\"gallery-title\">{$config['icon']} {$config['categoryLabel']} 샘플 갤러리</div>
                
                <!-- 포스터 방식 메인 갤러리 -->
                <div id=\"{$uniqueId}\">
                    <div class=\"proof-gallery\" role=\"region\" aria-label=\"{$config['categoryLabel']} 샘플 갤러리\">
                        <!-- 메인 이미지 (포스터 방식 backgroundImage) -->
                        <div class=\"proof-large\">
                            <div class=\"lightbox-viewer\" id=\"{$uniqueId}_zoomBox\" role=\"img\" aria-label=\"선택된 {$config['categoryLabel']} 샘플 이미지\">
                            </div>
                        </div>

                        <!-- 썸네일 그리드 (4개) -->
                        <div class=\"proof-thumbs\" id=\"{$uniqueId}_thumbs\" role=\"list\" aria-label=\"{$config['categoryLabel']} 썸네일 목록\">
                            <!-- JavaScript로 동적 로드 -->
                        </div>

                        <!-- 더보기 버튼 -->
                        <button 
                            class=\"btn-primary\"
                            onclick=\"openUnifiedModal('{$config['categoryLabel']}', '{$config['icon']}')\"
                            aria-label=\"더 많은 {$config['categoryLabel']} 샘플 보기\"
                        >
                            <span aria-hidden=\"true\">📂</span> 더 많은 샘플 보기
                        </button>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- 갤러리 JavaScript -->
        <script>
        // {$config['categoryLabel']} 갤러리 초기화
        document.addEventListener('DOMContentLoaded', function() {
            initCommonGallery('{$uniqueId}', '{$config['category']}', '{$config['categoryLabel']}');
        });
        </script>
        ";
    }
    
    /**
     * 갤러리 JavaScript 함수들 생성
     * 
     * @return string JavaScript 코드
     */
    public static function renderScript() {
        return "
        <script>
        /**
         * 공통 갤러리 초기화 함수
         * 전단지 갤러리의 성공한 패턴을 모든 품목에 적용
         */
        async function initCommonGallery(containerId, category, categoryLabel) {
            console.log('🎨 공통 갤러리 초기화:', {containerId, category, categoryLabel});
            
            try {
                // API에서 이미지 로드 (전단지와 동일한 방식)
                const response = await fetch(`/api/get_real_orders_portfolio.php?category=\${category}&per_page=4`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP \${response.status}: \${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('📊 API 응답 데이터:', data);
                
                if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
                    console.log(`✅ \${data.data.length}개 \${categoryLabel} 이미지 발견!`);
                    renderCommonGallery(containerId, data.data, categoryLabel);
                } else {
                    console.warn('⚠️ API에서 유효한 데이터를 받지 못함:', data);
                    showCommonPlaceholder(containerId, categoryLabel);
                }
            } catch (error) {
                console.error('❌ 공통 갤러리 API 호출 실패:', error);
                showCommonPlaceholder(containerId, categoryLabel);
            }
        }
        
        /**
         * 공통 갤러리 렌더링 (포스터 방식)
         */
        function renderCommonGallery(containerId, images, categoryLabel) {
            console.log('🎨 공통 갤러리 렌더링 시작:', {containerId, images: images.length});
            
            const zoomBox = document.getElementById(containerId + '_zoomBox');
            const thumbsContainer = document.getElementById(containerId + '_thumbs');
            
            if (!zoomBox || !thumbsContainer) {
                console.error('❌ 갤러리 요소를 찾을 수 없음:', {
                    zoomBox: !!zoomBox,
                    thumbsContainer: !!thumbsContainer
                });
                return;
            }
            
            // 이미지 데이터 검증
            const validImages = images.filter(img => img && img.path && img.path.trim());
            if (validImages.length === 0) {
                console.warn('⚠️ 유효한 이미지가 없음');
                showCommonPlaceholder(containerId, categoryLabel);
                return;
            }
            
            // 포스터 방식: 첫 번째 이미지를 backgroundImage로 설정
            const firstImage = validImages[0];
            zoomBox.style.backgroundImage = `url(\"\${firstImage.path}\")`;
            zoomBox.style.backgroundSize = 'contain';
            zoomBox.style.backgroundPosition = '50% 50%';
            
            // 썸네일 생성 (전단지와 동일한 방식)
            thumbsContainer.innerHTML = validImages.map((img, index) => {
                const title = img.title || `\${categoryLabel} 샘플 \${index + 1}`;
                const isActive = index === 0;
                
                return `
                    <div class=\"thumb \${isActive ? 'active' : ''}\" 
                         data-img=\"\${img.path.replace(/\"/g, '&quot;')}\" 
                         data-index=\"\${index}\"
                         role=\"listitem\"
                         tabindex=\"0\"
                         aria-label=\"\${title.replace(/\"/g, '&quot;')}\"
                         aria-selected=\"\${isActive}\"
                         onclick=\"selectCommonThumb(this, '\${containerId}')\"
                         onkeypress=\"handleCommonThumbKeypress(event, this, '\${containerId}')\">
                        <img src=\"\${img.path.replace(/\"/g, '&quot;')}\" 
                             alt=\"\${title.replace(/\"/g, '&quot;')}\"
                             loading=\"lazy\"
                             onerror=\"handleCommonImageError(this)\">
                    </div>
                `;
            }).join('');
            
            console.log(`✅ 공통 갤러리 렌더링 완료 - \${validImages.length}개 이미지`);
            
            // 포스터 방식 호버링 시스템 초기화
            initCommonPosterHover(containerId);
        }
        
        /**
         * 썸네일 선택 함수 (포스터 방식)
         */
        function selectCommonThumb(thumbElement, containerId) {
            if (!thumbElement) return;
            
            console.log('👆 썸네일 선택:', thumbElement.getAttribute('data-index'));
            
            // 모든 썸네일에서 active 클래스 제거
            const thumbsContainer = document.getElementById(containerId + '_thumbs');
            thumbsContainer.querySelectorAll('.thumb').forEach(function(item) {
                item.classList.remove('active');
                item.setAttribute('aria-selected', 'false');
            });
            
            // 선택한 썸네일에 active 클래스 추가
            thumbElement.classList.add('active');
            thumbElement.setAttribute('aria-selected', 'true');
            
            // 포스터 방식: backgroundImage로 교체
            const imageUrl = thumbElement.getAttribute('data-img');
            const zoomBox = document.getElementById(containerId + '_zoomBox');
            
            if (zoomBox && imageUrl) {
                zoomBox.style.backgroundImage = `url(\"\${imageUrl}\")`;
                zoomBox.style.backgroundSize = 'contain';
                zoomBox.style.backgroundPosition = '50% 50%';
                
                console.log('🖼️ 이미지 교체 완료:', imageUrl);
            }
        }
        
        /**
         * 키보드 접근성
         */
        function handleCommonThumbKeypress(event, thumbElement, containerId) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectCommonThumb(thumbElement, containerId);
            }
        }
        
        /**
         * 이미지 로드 에러 처리
         */
        function handleCommonImageError(imgElement) {
            console.warn('⚠️ 이미지 로드 실패:', imgElement.src);
            imgElement.src = 'https://via.placeholder.com/400x300?text=이미지+로드+실패&color=999';
            imgElement.alt = '이미지를 불러올 수 없습니다';
        }
        
        /**
         * 플레이스홀더 이미지 표시
         */
        function showCommonPlaceholder(containerId, categoryLabel) {
            console.log('📷 플레이스홀더 이미지 표시:', categoryLabel);
            
            const zoomBox = document.getElementById(containerId + '_zoomBox');
            const thumbsContainer = document.getElementById(containerId + '_thumbs');
            
            if (zoomBox) {
                zoomBox.style.backgroundImage = `url('https://via.placeholder.com/900x600?text=\${encodeURIComponent(categoryLabel)}+샘플+준비중&color=999')`;
                zoomBox.style.backgroundSize = 'contain';
                zoomBox.style.backgroundPosition = '50% 50%';
            }
            
            if (thumbsContainer) {
                thumbsContainer.innerHTML = Array.from({length: 4}, (_, index) => `
                    <div class=\"thumb \${index === 0 ? 'active' : ''}\"
                         data-img=\"https://via.placeholder.com/200x150?text=샘플\${index + 1}&color=ccc\"
                         data-index=\"\${index}\"
                         onclick=\"selectCommonThumb(this, '\${containerId}')\">
                        <img src=\"https://via.placeholder.com/200x150?text=샘플\${index + 1}&color=ccc\" 
                             alt=\"\${categoryLabel} 샘플 \${index + 1} 준비중\"
                             loading=\"lazy\">
                    </div>
                `).join('');
            }
        }
        
        /**
         * 포스터 방식 호버링 시스템 (전단지와 동일)
         */
        function initCommonPosterHover(containerId) {
            const zoomBox = document.getElementById(containerId + '_zoomBox');
            if (!zoomBox) return;
            
            console.log('🎯 포스터 방식 호버링 초기화:', containerId);
            
            // 호버링 애니메이션 변수 (전역으로 저장)
            if (!window.commonGalleryAnimations) {
                window.commonGalleryAnimations = {};
            }
            
            const animationKey = containerId + '_animation';
            window.commonGalleryAnimations[animationKey] = {
                currentX: 50,
                currentY: 50, 
                currentSize: 100,
                targetX: 50,
                targetY: 50,
                targetSize: 100,
                animationId: null
            };
            
            const anim = window.commonGalleryAnimations[animationKey];
            
            // 마우스 움직임 추적
            zoomBox.addEventListener('mousemove', function(e) {
                const rect = zoomBox.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                
                anim.targetX = x;
                anim.targetY = y;
                anim.targetSize = 135; // 1.35배 확대
            });
            
            // 마우스 벗어날 때 초기화
            zoomBox.addEventListener('mouseleave', function() {
                anim.targetX = 50;
                anim.targetY = 50;
                anim.targetSize = 100;
                console.log('👋 호버 초기화:', containerId);
            });
            
            // 부드러운 애니메이션 시작
            function startCommonAnimation() {
                if (anim.animationId) {
                    cancelAnimationFrame(anim.animationId);
                }
                
                function animate() {
                    // 부드러운 보간 (0.08 lerp 계수)
                    anim.currentX += (anim.targetX - anim.currentX) * 0.08;
                    anim.currentY += (anim.targetY - anim.currentY) * 0.08;
                    anim.currentSize += (anim.targetSize - anim.currentSize) * 0.08;
                    
                    zoomBox.style.backgroundPosition = anim.currentX + '% ' + anim.currentY + '%';
                    
                    if (anim.currentSize > 100.1) {
                        zoomBox.style.backgroundSize = anim.currentSize + '%';
                    } else {
                        zoomBox.style.backgroundSize = 'contain';
                    }
                    
                    anim.animationId = requestAnimationFrame(animate);
                }
                
                animate();
            }
            
            startCommonAnimation();
            console.log('✅ 포스터 방식 호버링 설정 완료:', containerId);
        }
        </script>
        ";
    }
    
    /**
     * 공통 CSS 스타일 생성
     * 
     * @return string CSS 코드
     */
    public static function renderCSS() {
        return "
        <style>
        /* 공통 갤러리 컴포넌트 CSS v2.0 */
        .common-gallery-section {
            margin-bottom: 20px;
        }
        
        /* 갤러리 섹션 기본 스타일 */
        .gallery-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.9);
        }
        
        /* 갤러리 제목 - 브랜드 색상 적용 */
        .gallery-title {
            background: linear-gradient(135deg, var(--brand-color, #4caf50) 0%, var(--brand-color-dark, #2e7d32) 100%);
            color: white;
            padding: 18px 20px;
            margin: -25px -25px 20px -25px;
            border-radius: 15px 15px 0 0;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }
        
        /* 포스터 방식 갤러리 구조 */
        .proof-gallery {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
        }

        .proof-large {
            width: 100%; 
            height: 300px;
        }

        /* 포스터 방식: backgroundImage 기반 호버 확대 */
        .lightbox-viewer {
            width: 100%;
            height: 100%;
            background-color: #f9f9f9;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            cursor: zoom-in;
            transition: border-color 0.3s ease;
            border: 2px solid #e9ecef;
            position: relative;
            overflow: hidden;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: 50% 50%;
        }
        
        .lightbox-viewer:hover {
            border-color: var(--brand-color, #4caf50);
        }

        /* 썸네일 그리드 */
        .proof-thumbs {
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 10px;
            width: 100%;
        }

        .proof-thumbs .thumb {
            width: 100%; 
            height: 80px; 
            border-radius: 12px; 
            overflow: hidden; 
            border: 2px solid #ddd; 
            cursor: pointer;
            background: #f7f7f7;
            display: flex; 
            align-items: center; 
            justify-content: center;
            transition: border-color 0.3s ease, transform 0.2s ease;
        }

        .proof-thumbs .thumb:hover {
            border-color: var(--brand-color, #4caf50);
            transform: translateY(-2px);
        }

        .proof-thumbs .thumb.active {
            border-color: var(--brand-color, #4caf50);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .proof-thumbs .thumb img {
            max-width: 100%; 
            max-height: 100%; 
            object-fit: contain; 
            display: block;
        }

        /* 통일된 Primary 버튼 스타일 */
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 48px;
            padding: 0 20px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-color, #4caf50) 0%, var(--brand-color-dark, #2e7d32) 100%);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, filter 0.2s ease;
            margin-top: 16px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--brand-color-dark, #2e7d32) 0%, var(--brand-color, #4caf50) 100%);
            transform: translateY(-2px);
            filter: brightness(0.95);
        }

        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .gallery-section {
                padding: 20px;
                margin: 0 -10px;
                border-radius: 10px;
            }
            
            .gallery-title {
                margin: -20px -20px 15px -20px;
                padding: 12px 15px;
                font-size: 1rem;
            }
            
            .proof-large {
                height: 250px;
            }
            
            .proof-thumbs .thumb {
                height: 60px;
            }
        }
        </style>
        ";
    }
    
    /**
     * 색상을 어둡게 만드는 헬퍼 함수
     */
    private static function darkenColor($color, $percent) {
        // 간단한 색상 darkening (RGB hex 기준)
        $color = ltrim($color, '#');
        $rgb = array_map('hexdec', str_split($color, 2));
        
        foreach ($rgb as &$value) {
            $value = max(0, $value - ($value * $percent / 100));
        }
        
        return '#' . implode('', array_map(function($val) {
            return str_pad(dechex(round($val)), 2, '0', STR_PAD_LEFT);
        }, $rgb));
    }
}
?>