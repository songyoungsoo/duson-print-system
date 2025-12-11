<?php
/**
 * 명함 갤러리 적용 예시
 * 공통 갤러리 컴포넌트를 명함 페이지에 적용하는 방법
 */

// 기존 명함 페이지 헤더 부분
session_start();
include "../../db.php";
include "../../includes/functions.php";

// 공통 갤러리 컴포넌트 포함 (새로 추가)
include "../../includes/CommonGallery.php";

$page_title = '💳 두손기획인쇄 - 명함 견적';
$current_page = 'namecard';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- 기존 명함 페이지 CSS -->
    <link rel="stylesheet" href="css/namecard-style.css">
    
    <!-- 공통 갤러리 CSS 추가 -->
    <?php echo CommonGallery::renderCSS(); ?>
    
    <!-- 통합 갤러리 CSS (기존) -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">
</head>

<body>
    <div class="namecard-container">
        <!-- 페이지 제목 -->
        <div class="page-title">
            <h1>💳 명함 자동견적</h1>
            <p>고급 명함 제작 - 실시간 계산기</p>
        </div>
        
        <div class="namecard-grid">
            <!-- 좌측: 공통 갤러리 섹션 -->
            <?php
            // 기존 갤러리 HTML을 공통 컴포넌트로 대체
            echo CommonGallery::render([
                'category' => 'namecard',
                'categoryLabel' => '명함',
                'brandColor' => '#2196f3',  // 명함 브랜드 색상 (파랑)
                'icon' => '💳',
                'containerId' => 'namecardGallery'
            ]);
            ?>
            
            <!-- 우측: 계산기 섹션 (기존과 동일) -->
            <aside class="namecard-calculator">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>
                
                <!-- 기존 계산기 폼 내용 -->
                <form id="namecardForm" method="post">
                    <!-- 명함 옵션들... -->
                </form>
            </aside>
        </div>
    </div>

    <!-- 통합 갤러리 모달 (기존) -->
    <?php include "../../includes/unified_gallery_modal.php"; ?>
    
    <!-- 공통 갤러리 JavaScript 추가 -->
    <script src="../../includes/js/CommonGalleryAPI.js"></script>
    <?php echo CommonGallery::renderScript(); ?>
    
    <!-- 기존 명함 페이지 JavaScript -->
    <script src="js/namecard.js"></script>
    
    <script>
    // 명함 갤러리 초기화 (페이지 로드 시)
    document.addEventListener('DOMContentLoaded', function() {
        console.log('명함 페이지 - 공통 갤러리 시스템 적용');
        
        // 공통 갤러리가 자동으로 초기화됨 (CommonGallery::renderScript()에서 처리)
        // 추가적인 명함 특화 기능이 필요하면 여기에 추가
    });
    </script>
</body>
</html>

<?php
// 데이터베이스 연결 종료
if ($db) {
    mysqli_close($db);
}
?>

<!--
적용 단계:

1. 기존 파일 백업
   cp index.php index.php.backup_20251210

2. CommonGallery.php 포함 추가
   include "../../includes/CommonGallery.php";

3. 기존 갤러리 HTML 대체
   기존: <div class="gallery-section">...</div>
   신규: echo CommonGallery::render([...]);

4. CSS/JS 추가
   - CommonGallery::renderCSS()
   - CommonGallery::renderScript()  
   - CommonGalleryAPI.js

5. 브랜드 색상 확인
   명함: #2196f3 (파랑)

6. 테스트
   - 썸네일 로드 확인
   - 호버 확대 확인
   - 모달 팝업 확인
   - 모바일 반응형 확인
-->