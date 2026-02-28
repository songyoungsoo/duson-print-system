<?php
/**
 * [LEGACY] 이니시스 샘플 결제 요청 페이지 — inicis_request.php로 통합됨
 */

$order_no = intval($_GET['order_no'] ?? 0);

if ($order_no > 0) {
    header('Location: /payment/inicis_request.php?order_no=' . $order_no, true, 301);
    exit;
}

header('Location: /', true, 302);
exit;
