<?php
/**
 * [LEGACY] 결제 요청 페이지 — inicis_request.php로 통합됨
 * 
 * 이 파일은 더 이상 직접 사용하지 않습니다.
 * 모든 결제 요청은 inicis_request.php를 사용합니다.
 */

$order_no = intval($_GET['order_no'] ?? 0);

if ($order_no > 0) {
    header('Location: /payment/inicis_request.php?order_no=' . $order_no, true, 301);
    exit;
}

header('Location: /', true, 302);
exit;
