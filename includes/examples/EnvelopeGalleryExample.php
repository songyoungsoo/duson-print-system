<?php
/**
 * 봉투 갤러리 적용 예시
 * 공통 갤러리 컴포넌트를 봉투 페이지에 적용하는 방법
 * 봉투는 이미 고급 이미지 애니메이션이 적용된 상태이므로 기존 기술과 조화롭게 통합
 */

// 기존 봉투 페이지 헤더 부분
session_start();
include "../../db.php";
include "../../includes/functions.php";

// 공통 갤러리 컴포넌트 포함 (새로 추가)
include "../../includes/CommonGallery.php";

$page_title = '✉️ 두손기획인쇄 - 봉투 견적';
$current_page = 'envelope';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- 기존 봉투 페이지 CSS -->
    <link rel="stylesheet" href="css/envelope-style.css">
    
    <!-- 공통 갤러리 CSS 추가 -->
    <?php echo CommonGallery::renderCSS(); ?>
    
    <!-- 기존 봉투 고급 애니메이션 CSS 유지 -->
    <link rel="stylesheet" href="css/envelope-animations.css">
</head>

<body>
    <div class="envelope-container">
        <!-- 페이지 제목 -->
        <div class="page-title">
            <h1>✉️ 봉투 자동견적</h1>
            <p>다양한 크기의 봉투 제작 서비스</p>
        </div>
        
        <div class="envelope-grid">
            <!-- 좌측: 공통 갤러리 섹션 -->
            <?php
            // 기존 봉투 갤러리를 공통 컴포넌트로 업그레이드
            // 봉투의 기존 고급 애니메이션 기술과 공통 컴포넌트 조화
            echo CommonGallery::render([
                'category' => 'envelope',
                'categoryLabel' => '봉투',
                'brandColor' => '#ff9800',  // 봉투 브랜드 색상 (주황)
                'icon' => '✉️',
                'containerId' => 'envelopeGallery'
            ]);
            ?>
            
            <!-- 우측: 계산기 섹션 (기존과 동일) -->
            <aside class="envelope-calculator">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>
                
                <!-- 기존 계산기 폼 내용 -->
                <form id="envelopeForm" method="post">
                    <!-- 봉투 옵션들... -->
                    <div class="option-group">
                        <label for="envelope_type">봉투 종류</label>
                        <select name="envelope_type" id="envelope_type">
                            <option value="small">소봉투 (90x160mm)</option>
                            <option value="medium">중봉투 (105x235mm)</option>
                            <option value="large">대봉투 (120x235mm)</option>
                            <option value="a4">A4봉투 (229x324mm)</option>
                        </select>
                    </div>
                    
                    <div class="option-group">
                        <label for="paper_type">용지 종류</label>
                        <select name="paper_type" id="paper_type">
                            <option value="white">백상지</option>
                            <option value="kraft">크라프트지</option>
                            <option value="color">칼라지</option>
                        </select>
                    </div>
                    
                    <!-- 기타 봉투 옵션들... -->
                </form>
            </aside>
        </div>
    </div>

    <!-- 통합 갤러리 모달 (기존) -->
    <?php include "../../includes/unified_gallery_modal.php"; ?>
    
    <!-- 공통 갤러리 JavaScript 추가 -->
    <script src="../../includes/js/CommonGalleryAPI.js"></script>
    <?php echo CommonGallery::renderScript(); ?>
    
    <!-- 기존 봉투 페이지 JavaScript -->
    <script src="js/envelope.js"></script>
    
    <script>
    // 봉투 갤러리 초기화 - 기존 고급 애니메이션과 조화
    document.addEventListener('DOMContentLoaded', function() {
        console.log('봉투 페이지 - 공통 갤러리 + 기존 애니메이션 기술 통합');
        
        // 공통 갤러리 초기화는 자동으로 처리됨
        
        // 봉투 특화 기능: 기존 고급 애니메이션 기술과 연동
        enhanceEnvelopeGallery();
    });
    
    /**
     * 봉투 갤러리 특화 기능
     * 기존 봉투의 고급 이미지 애니메이션 기술과 공통 갤러리 조화
     */
    function enhanceEnvelopeGallery() {
        // 기존 봉투 페이지의 라이트박스나 특수 애니메이션이 있다면 여기서 통합
        console.log('봉투 갤러리 특화 기능 활성화');
        
        // 예: 봉투 크기별 미리보기 기능
        document.getElementById('envelope_type')?.addEventListener('change', function() {
            const selectedType = this.value;
            console.log('봉투 타입 변경:', selectedType);
            
            // 갤러리에서 해당 타입의 봉투 이미지를 우선 표시하는 로직
            // (필요시 CommonGalleryAPI를 사용해서 필터링)
        });
    }
    </script>
    
    <!-- 봉투 특화 CSS (기존 애니메이션과 조화) -->
    <style>
    /* 봉투 갤러리만의 특별한 스타일이 필요하면 여기에 추가 */
    .envelope-gallery .lightbox-viewer {
        /* 봉투 이미지에 특화된 호버 효과가 필요하면 오버라이드 */
        border-radius: 15px; /* 봉투는 좀 더 둥근 모서리 */
    }
    
    .envelope-gallery .proof-thumbs .thumb {
        /* 봉투 썸네일 특화 스타일 */
        border-radius: 8px;
    }
    
    /* 봉투 크기별 구분 표시 (옵션) */
    .envelope-gallery .thumb[data-size="small"] {
        border-color: #4caf50;
    }
    .envelope-gallery .thumb[data-size="medium"] {
        border-color: #2196f3;
    }
    .envelope-gallery .thumb[data-size="large"] {
        border-color: #f44336;
    }
    </style>
</body>
</html>

<?php
// 데이터베이스 연결 종료
if ($db) {
    mysqli_close($db);
}
?>

<!--
봉투 갤러리 적용 특징:

1. 기존 기술 보존
   - 봉투 페이지의 고급 이미지 애니메이션 기술 유지
   - 기존 envelope-animations.css 계속 사용

2. 공통 컴포넌트 통합
   - CommonGallery로 기본 구조 통일
   - 브랜드 색상: #ff9800 (주황)
   - 아이콘: ✉️

3. 특화 기능 추가
   - 봉투 크기별 이미지 분류
   - 용지 종류별 미리보기
   - 기존 라이트박스 기술과 조화

4. 테스트 포인트
   - 공통 갤러리 정상 작동
   - 기존 애니메이션 유지
   - 봉투 크기 변경 시 갤러리 연동
   - 모바일 반응형

5. 마이그레이션 단계
   a) 기존 파일 백업
   b) CommonGallery 포함
   c) 기존 갤러리 HTML 대체
   d) 특화 기능 추가
   e) 테스트 및 조정
-->