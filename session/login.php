<?php
/**
 * 세션 로그인 - 메인 로그인으로 리다이렉트
 * 통합 로그인 시스템 사용
 */
$redirect_to = urlencode($_GET['redirect'] ?? '/session/index.php');
header("Location: /member/login.php?redirect=$redirect_to");
exit;
?>
