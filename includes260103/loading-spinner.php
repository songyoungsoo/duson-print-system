<?php
/**
 * 두손기획인쇄 로딩 스피너 컴포넌트
 * 사용법: <?php include "includes/loading-spinner.php"; ?>
 *
 * JavaScript 함수:
 * - showDusonLoading('메시지')  : 로딩 표시
 * - hideDusonLoading()         : 로딩 숨김
 */

// 중복 포함 방지
if (!defined('DUSON_LOADING_SPINNER_INCLUDED')) {
    define('DUSON_LOADING_SPINNER_INCLUDED', true);
?>
<!-- 두손기획인쇄 로딩 스피너 -->
<link rel="stylesheet" href="/css/loading-spinner.css">
<script src="/js/loading-spinner.js"></script>
<?php
}
?>
