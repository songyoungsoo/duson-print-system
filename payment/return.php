<?php
/**
 * [LEGACY] 결제 결과 처리 페이지 — inicis_return.php로 통합됨
 * 
 * 이 파일은 더 이상 직접 사용하지 않습니다.
 * 모든 결제 콜백은 inicis_return.php를 사용합니다.
 * 
 * POST 콜백이 직접 여기로 올 경우를 대비한 안전 처리.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resultCode'])) {
    error_log('[LEGACY return.php] POST callback received — should go to inicis_return.php. Data: ' . json_encode($_POST));
}

header('Location: /', true, 302);
exit;
