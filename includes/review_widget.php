<?php
/**
 * 고객 리뷰 위젯
 * 경로: includes/review_widget.php
 *
 * 사용법: 제품 페이지에서 include 전에 $product_type 변수 설정
 *   $product_type = 'namecard';
 *   include __DIR__ . '/../../includes/review_widget.php';
 *
 * 요구사항:
 *   - $product_type (string): 제품 폴더명 (namecard, inserted, sticker_new 등)
 *   - auth.php 이미 include 되어 있어야 함 ($is_logged_in, $_SESSION 사용)
 *
 * 데이터 로딩: 모든 데이터는 AJAX로 /api/reviews.php에서 가져옴 (이 파일은 DB 쿼리 안 함)
 */

// $product_type 미설정 시 경고
if (empty($product_type)) {
    return;
}

$_reviewWidgetCssPath = __DIR__ . '/css/review_widget.css';
$_reviewWidgetJsPath  = __DIR__ . '/js/review_widget.js';
$_reviewCssVersion = file_exists($_reviewWidgetCssPath) ? filemtime($_reviewWidgetCssPath) : time();
$_reviewJsVersion  = file_exists($_reviewWidgetJsPath)  ? filemtime($_reviewWidgetJsPath)  : time();
?>

<!-- 리뷰 위젯 CSS/JS -->
<link rel="stylesheet" href="/includes/css/review_widget.css?v=<?php echo $_reviewCssVersion; ?>">

<!-- 리뷰 위젯 데이터 -->
<script>
window.__reviewProductType = '<?php echo htmlspecialchars($product_type, ENT_QUOTES, 'UTF-8'); ?>';
window.__reviewUser = {
    loggedIn: <?php echo (!empty($is_logged_in) && $is_logged_in) ? 'true' : 'false'; ?>,
    userId: <?php echo (int)($_SESSION['user_id'] ?? 0); ?>,
    userName: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'
};
</script>

<!-- ====== 리뷰 위젯 시작 ====== -->
<div class="review-widget" id="reviewWidget">
    <div class="review-widget-header">
        <h3 class="review-widget-title">고객 리뷰</h3>
    </div>

    <!-- (1) 별점 요약 -->
    <div class="review-summary" id="reviewSummary">
        <div class="review-summary-loading">
            <span class="review-loading-spinner"></span> 리뷰 정보를 불러오는 중...
        </div>
    </div>

    <!-- (2) 정렬 + 리뷰 쓰기 버튼 -->
    <div class="review-toolbar" id="reviewToolbar" style="display:none;">
        <div class="review-sort">
            <select id="reviewSortSelect" class="review-sort-select">
                <option value="newest">최신순</option>
                <option value="rating_high">별점높은순</option>
                <option value="rating_low">별점낮은순</option>
                <option value="likes">도움순</option>
            </select>
        </div>
        <button type="button" class="review-write-btn" id="reviewWriteToggle">리뷰 쓰기</button>
    </div>

    <!-- (3) 리뷰 작성 폼 (접힌 상태) -->
    <div class="review-form-wrapper" id="reviewFormWrapper" style="display:none;">
        <?php if (!empty($is_logged_in) && $is_logged_in): ?>
        <form id="reviewForm" class="review-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="product_type" value="<?php echo htmlspecialchars($product_type, ENT_QUOTES, 'UTF-8'); ?>">

            <!-- 별점 선택 -->
            <div class="review-form-group">
                <label class="review-form-label">별점 <span class="review-required">*</span></label>
                <div class="review-star-selector" id="reviewStarSelector">
                    <span class="review-star-btn" data-rating="1">★</span>
                    <span class="review-star-btn" data-rating="2">★</span>
                    <span class="review-star-btn" data-rating="3">★</span>
                    <span class="review-star-btn" data-rating="4">★</span>
                    <span class="review-star-btn" data-rating="5">★</span>
                    <span class="review-star-text" id="reviewStarText">5점</span>
                </div>
                <input type="hidden" name="rating" id="reviewRatingInput" value="5">
            </div>

            <!-- 제목 -->
            <div class="review-form-group">
                <label class="review-form-label" for="reviewTitle">제목 <span class="review-optional">(선택)</span></label>
                <input type="text" id="reviewTitle" name="title" class="review-form-input" maxlength="200" placeholder="리뷰 제목을 입력해주세요">
            </div>

            <!-- 내용 -->
            <div class="review-form-group">
                <label class="review-form-label" for="reviewContent">내용 <span class="review-required">*</span></label>
                <textarea id="reviewContent" name="content" class="review-form-textarea" maxlength="5000" rows="5" placeholder="제품에 대한 솔직한 리뷰를 남겨주세요"></textarea>
                <div class="review-char-count"><span id="reviewCharCount">0</span> / 5,000</div>
            </div>

            <!-- 주문번호 (구매인증용) -->
            <div class="review-form-group">
                <label class="review-form-label" for="reviewOrderId">주문번호 <span class="review-optional">(구매인증용, 선택)</span></label>
                <input type="number" id="reviewOrderId" name="order_id" class="review-form-input" placeholder="주문번호를 입력하면 구매인증 배지가 표시됩니다">
            </div>

            <!-- 사진 업로드 -->
            <div class="review-form-group">
                <label class="review-form-label">사진 첨부 <span class="review-optional">(최대 5장, 각 5MB)</span></label>
                <div class="review-photo-upload">
                    <div class="review-photo-previews" id="reviewPhotoPreviews"></div>
                    <label class="review-photo-add-btn" id="reviewPhotoAddLabel">
                        <span>+</span>
                        <span>사진 추가</span>
                        <input type="file" id="reviewPhotoInput" accept="image/jpeg,image/png,image/webp" multiple style="display:none;">
                    </label>
                </div>
            </div>

            <!-- 제출 -->
            <div class="review-form-actions">
                <button type="button" class="review-cancel-btn" id="reviewCancelBtn">취소</button>
                <button type="submit" class="review-submit-btn" id="reviewSubmitBtn">리뷰 등록</button>
            </div>
            <p class="review-form-notice">관리자 승인 후 게시됩니다.</p>
        </form>
        <?php else: ?>
        <div class="review-login-prompt">
            <p>리뷰를 작성하려면 로그인이 필요합니다.</p>
            <a href="/login.php" class="review-login-btn">로그인</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- (4) 리뷰 목록 -->
    <div class="review-list" id="reviewList">
        <div class="review-list-loading">
            <span class="review-loading-spinner"></span> 리뷰를 불러오는 중...
        </div>
    </div>

    <!-- (5) 페이지네이션 -->
    <div class="review-pagination" id="reviewPagination"></div>
</div>

<!-- 라이트박스 -->
<div class="review-lightbox" id="reviewLightbox" style="display:none;">
    <div class="review-lightbox-backdrop"></div>
    <div class="review-lightbox-content">
        <button type="button" class="review-lightbox-close" title="닫기">&times;</button>
        <button type="button" class="review-lightbox-prev" title="이전">&#10094;</button>
        <img class="review-lightbox-img" id="reviewLightboxImg" src="" alt="리뷰 사진">
        <button type="button" class="review-lightbox-next" title="다음">&#10095;</button>
        <div class="review-lightbox-counter" id="reviewLightboxCounter"></div>
    </div>
</div>

<!-- 토스트 알림 -->
<div class="review-toast" id="reviewToast" style="display:none;"></div>

<!-- 리뷰 위젯 JS -->
<script src="/includes/js/review_widget.js?v=<?php echo $_reviewJsVersion; ?>"></script>
<!-- ====== 리뷰 위젯 끝 ====== -->
