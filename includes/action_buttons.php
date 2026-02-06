<?php
/**
 * action_buttons.php - 모드별 액션 버튼 렌더링
 *
 * @param string $uploadFunction JS 함수명 (default: 'openUploadModal', msticker: 'mstickerOpenUploadModal')
 * @requires mode_helper.php 필수 (변수: $isQuotationMode, $isAdminQuoteMode)
 * @since Phase 1.1 - 주문/견적 모드 분리
 */

if (!isset($isQuotationMode) || !isset($isAdminQuoteMode)) {
    require_once __DIR__ . '/mode_helper.php';
}

$_uploadFunction = isset($uploadFunction) ? $uploadFunction : 'openUploadModal';
?>

<?php if ($isQuotationMode || $isAdminQuoteMode): ?>
<div class="quotation-apply-button">
    <button type="button" class="btn-quotation-apply" onclick="applyToQuotation()">
        &#10003; 견적서에 적용
    </button>
</div>
<?php else: ?>
<div class="action-buttons" id="actionButtons">
    <button type="button" class="btn-upload-order" onclick="<?php echo htmlspecialchars($_uploadFunction); ?>()">
        파일 업로드 및 주문하기
    </button>
</div>
<?php endif; ?>
