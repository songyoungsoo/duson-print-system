<?php
/**
 * [LEGACY] 이니시스 샘플 결제 결과 페이지 — inicis_return.php로 통합됨
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resultCode'])) {
    error_log('[LEGACY INIstdpay_pc_return.php] POST callback received — should go to inicis_return.php.');
}

header('Location: /', true, 302);
exit;
