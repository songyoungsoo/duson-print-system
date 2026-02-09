<?php
/**
 * KG이니시스 결제 결과 처리 페이지
 * 두손기획인쇄 - dsp114.co.kr
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/libs/INIStdPayUtil.php';
require_once __DIR__ . '/libs/HttpClient.php';
require_once __DIR__ . '/libs/properties.php';

$util = new INIStdPayUtil();
$prop = new properties();

$success = false;
$error_message = '';
$order_no = 0;
$resultData = [];

try {
    inicis_log('결제 결과 수신: ' . json_encode($_REQUEST, JSON_UNESCAPED_UNICODE), 'response');

    // ================================
    // 인증 결과 확인
    // ================================
    if (strcmp("0000", $_REQUEST["resultCode"] ?? '') !== 0) {
        throw new Exception($_REQUEST["resultMsg"] ?? '인증에 실패했습니다.');
    }

    // ================================
    // 파라미터 수신
    // ================================
    $mid = $_REQUEST["mid"];
    $signKey = INICIS_SIGNKEY;
    $timestamp = $util->getTimestamp();
    $authToken = $_REQUEST["authToken"];
    $idc_name = $_REQUEST["idc_name"];

    // 승인 URL 확인
    $authUrl = $prop->getAuthUrl($idc_name);
    if (strcmp($authUrl, $_REQUEST["authUrl"]) !== 0) {
        throw new Exception('인증 URL 검증 실패');
    }

    // ================================
    // Signature 생성
    // ================================
    $signParam = array(
        "authToken" => $authToken,
        "timestamp" => $timestamp
    );
    $signature = $util->makeSignature($signParam);

    $veriParam = array(
        "authToken" => $authToken,
        "signKey" => $signKey,
        "timestamp" => $timestamp
    );
    $verification = $util->makeSignature($veriParam);

    // ================================
    // 승인 요청
    // ================================
    $authMap = array(
        "mid" => $mid,
        "authToken" => $authToken,
        "signature" => $signature,
        "verification" => $verification,
        "timestamp" => $timestamp,
        "charset" => "UTF-8",
        "format" => "JSON"
    );

    $httpUtil = new HttpClient();
    if (!$httpUtil->processHTTP($authUrl, $authMap)) {
        throw new Exception('승인 서버 통신 오류');
    }

    $resultData = json_decode($httpUtil->body, true);
    inicis_log('승인 결과: ' . json_encode($resultData, JSON_UNESCAPED_UNICODE), 'response');

    // ================================
    // 승인 결과 확인
    // ================================
    if (($resultData["resultCode"] ?? '') !== "0000") {
        throw new Exception($resultData["resultMsg"] ?? '승인에 실패했습니다.');
    }

    // ================================
    // 주문번호 추출 (DSP123_타임스탬프 형식)
    // ================================
    $oid = $resultData["MOID"] ?? $_REQUEST["oid"] ?? '';
    preg_match('/DSP(\d+)_/', $oid, $matches);
    $order_no = intval($matches[1] ?? 0);

    if (!$order_no) {
        // 세션에서 시도
        $order_no = intval($_SESSION['inicis_order_no'] ?? 0);
    }

    if (!$order_no) {
        throw new Exception('주문번호를 확인할 수 없습니다.');
    }

    // ================================
    // 주문 조회
    // ================================
    $stmt = mysqli_prepare($db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
    mysqli_stmt_bind_param($stmt, 'i', $order_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$order) {
        throw new Exception('주문 정보를 찾을 수 없습니다.');
    }

    // ================================
    // 결제 로그 저장
    // ================================
    $tid = $resultData["tid"] ?? '';
    $payMethod = $resultData["payMethod"] ?? '';
    $paidPrice = intval($resultData["TotPrice"] ?? $resultData["price"] ?? 0);
    $resultCode = $resultData["resultCode"] ?? '';
    $resultMsg = $resultData["resultMsg"] ?? '';
    $paidAt = date('Y-m-d H:i:s');

    // 결제 로그 테이블이 있으면 저장
    $log_query = "INSERT INTO order_payment_log
                  (order_no, pg_name, tid, pay_method, amount, result_code, result_msg, paid_at, created_at)
                  VALUES (?, 'inicis', ?, ?, ?, ?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE
                  tid = VALUES(tid), pay_method = VALUES(pay_method), amount = VALUES(amount),
                  result_code = VALUES(result_code), result_msg = VALUES(result_msg), paid_at = VALUES(paid_at)";

    $stmt = mysqli_prepare($db, $log_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'issdsss',
            $order_no, $tid, $payMethod, $paidPrice, $resultCode, $resultMsg, $paidAt
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // ================================
    // 주문 상태 업데이트
    // ================================
    $update_query = "UPDATE mlangorder_printauto
                     SET money_2 = ?,
                         OrderStyle = 'payment_confirmed'
                     WHERE no = ?";

    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, 'di', $paidPrice, $order_no);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('주문 상태 업데이트 실패');
    }
    mysqli_stmt_close($stmt);

    $success = true;
    inicis_log("결제 완료 - 주문번호: {$order_no}, TID: {$tid}, 금액: {$paidPrice}원", 'success');

    // 세션 정리
    unset($_SESSION['inicis_order_no'], $_SESSION['inicis_oid'], $_SESSION['inicis_price']);

} catch (Exception $e) {
    $error_message = $e->getMessage();
    inicis_log("결제 오류 - {$error_message}", 'error');

    // 망취소 시도 (승인 요청 후 오류 발생 시)
    if (isset($authMap) && isset($idc_name)) {
        try {
            $netCancel = $prop->getNetCancel($idc_name);
            $httpUtil = new HttpClient();
            $httpUtil->processHTTP($netCancel, $authMap);
            inicis_log('망취소 요청 완료', 'info');
        } catch (Exception $e2) {
            inicis_log('망취소 오류: ' . $e2->getMessage(), 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? '결제 완료' : '결제 실패'; ?> - 두손기획인쇄</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: <?php echo $success ? 'linear-gradient(135deg, #667eea, #764ba2)' : 'linear-gradient(135deg, #e74c3c, #c0392b)'; ?>;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px 40px;
            text-align: center;
        }
        .icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            animation: bounceIn 0.6s;
        }
        .icon.success { background: linear-gradient(135deg, #4caf50, #45a049); }
        .icon.error { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        h1 { color: #333; font-size: 26px; margin-bottom: 16px; }
        .message { color: #666; font-size: 15px; line-height: 1.6; margin-bottom: 30px; }
        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            text-align: left;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6c757d; font-size: 14px; }
        .info-value { color: #333; font-size: 14px; font-weight: 600; }
        .info-value.success { color: #4caf50; }
        .buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn {
            flex: 1;
            max-width: 200px;
            padding: 14px 24px;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn:hover { transform: translateY(-2px); }
        .contact {
            margin-top: 24px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="icon success">✓</div>
            <h1>결제가 완료되었습니다!</h1>
            <p class="message">주문하신 제품을 빠르게 제작하여 발송해드리겠습니다.</p>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">주문번호</span>
                    <span class="info-value">#<?php echo $order_no; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">결제금액</span>
                    <span class="info-value"><?php echo format_price($resultData["TotPrice"] ?? $resultData["price"] ?? 0); ?>원</span>
                </div>
                <div class="info-row">
                    <span class="info-label">결제수단</span>
                    <span class="info-value">
                        <?php
                        $methods = ['Card' => '신용카드', 'DirectBank' => '계좌이체', 'VBank' => '가상계좌', 'HPP' => '휴대폰'];
                        echo $methods[$resultData["payMethod"] ?? ''] ?? ($resultData["payMethod"] ?? '-');
                        ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">거래번호</span>
                    <span class="info-value"><?php echo htmlspecialchars($resultData["tid"] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">주문상태</span>
                    <span class="info-value success">✓ 입금 확인됨</span>
                </div>
            </div>

            <div class="buttons">
                <a href="/" class="btn btn-primary">홈으로</a>
                <a href="/mypage/" class="btn btn-secondary">주문내역</a>
            </div>

        <?php else: ?>
            <div class="icon error">✕</div>
            <h1>결제에 실패했습니다</h1>
            <p class="message"><?php echo htmlspecialchars($error_message); ?></p>

            <div class="info-box">
                <?php if ($order_no): ?>
                <div class="info-row">
                    <span class="info-label">주문번호</span>
                    <span class="info-value">#<?php echo $order_no; ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">오류코드</span>
                    <span class="info-value"><?php echo htmlspecialchars($resultData["resultCode"] ?? $_REQUEST["resultCode"] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">오류메시지</span>
                    <span class="info-value"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            </div>

            <div class="buttons">
                <?php if ($order_no): ?>
                <a href="/payment/request.php?order_no=<?php echo $order_no; ?>" class="btn btn-secondary">다시 결제</a>
                <?php endif; ?>
                <a href="/" class="btn btn-primary">홈으로</a>
            </div>
        <?php endif; ?>

        <p class="contact">문의: 02-2632-1830</p>
    </div>
</body>
</html>
