<?php
/**
 * mode_helper.php - 주문/견적 모드 감지 공통 함수
 *
 * 9개 제품 페이지에서 중복되던 모드 감지 로직을 중앙화합니다.
 * - normal: 일반 주문 모드 (기본값)
 * - quotation: 고객 견적서 모달 모드 (?mode=quotation)
 * - admin_quote: 관리자 견적서 모달 모드 (?mode=admin_quote)
 *
 * @since Phase 1.1 - 주문/견적 모드 분리
 */

/**
 * 현재 페이지 모드 감지
 *
 * @return string 'normal' | 'quotation' | 'admin_quote'
 */
function detectPageMode() {
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'normal';
    $allowed = array('normal', 'quotation', 'admin_quote');
    return in_array($mode, $allowed) ? $mode : 'normal';
}

/**
 * 견적서 모달 모드 여부 (quotation 또는 admin_quote)
 *
 * @return bool
 */
function isAnyQuotationMode() {
    $mode = detectPageMode();
    return ($mode === 'quotation' || $mode === 'admin_quote');
}

$_pageMode = detectPageMode();

// camelCase (8 products) + snake_case (sticker_new) — both naming conventions required
$isQuotationMode = ($_pageMode === 'quotation');
$isAdminQuoteMode = ($_pageMode === 'admin_quote');
$is_quotation_mode = $isQuotationMode;
$is_admin_quote_mode = $isAdminQuoteMode;

$isAnyQuoteMode = ($isQuotationMode || $isAdminQuoteMode);
$quotationBodyClass = $isAnyQuoteMode ? ' quotation-modal-mode' : '';
