<?php
/**
 * 주문조회&배송조회
 * 기존 orderhistory.php로 리다이렉트
 */
session_start();

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/member/login.php';</script>";
    exit;
}

// 기존 주문조회 페이지로 리다이렉트
header("Location: /session/orderhistory.php");
exit;
?>
