<?php
/**
 * [LEGACY] 결제 성공 페이지 — inicis_return.php로 통합됨
 * 
 * 이 파일은 더 이상 직접 사용하지 않습니다.
 * 결제 콜백은 inicis_return.php → OrderComplete_universal.php 흐름을 사용합니다.
 * 
 * 혹시 이 파일이 직접 호출되는 경우를 위한 안전 리다이렉트.
 */

// GET으로 order_no가 전달된 경우 (inicis_return.php에서 리다이렉트 된 경우)
$order_no = intval($_GET['order_no'] ?? 0);

if ($order_no > 0) {
    header('Location: /mlangorder_printauto/OrderComplete_universal.php?orders=' . $order_no . '&payment=success', true, 301);
    exit;
}

// POST로 호출된 경우 (이니시스 콜백이 직접 여기로 온 경우) → inicis_return.php로 전달
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resultCode'])) {
    // POST 데이터를 inicis_return.php로 전달하는 것은 불가하므로 로그만 남기고 홈으로
    error_log('[LEGACY success.php] POST callback received directly — should go to inicis_return.php. Data: ' . json_encode($_POST));
    header('Location: /', true, 302);
    exit;
}

// 기본: 홈으로
header('Location: /', true, 302);
exit;
