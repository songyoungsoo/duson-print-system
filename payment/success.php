<?php
require_once __DIR__ . '/inicis_config.php';
require_once __DIR__ . '/libs/INIStdPayUtil.php';
require_once __DIR__ . '/libs/HttpClient.php';
require_once __DIR__ . '/libs/properties.php';

$util = new INIStdPayUtil();
$prop = new properties();

try {
    if (strcmp("0000", $_REQUEST["resultCode"]) == 0) {
        $mid = $_REQUEST["mid"];
        $signKey = INICIS_SIGNKEY;
        $timestamp = $util->getTimestamp();
        $charset = "UTF-8";
        $format = "JSON";
        $authToken = $_REQUEST["authToken"]; 
        $authUrl = $_REQUEST["authUrl"];
        $netCancel = $_REQUEST["netCancelUrl"];        
        $merchantData = $_REQUEST["merchantData"];
        
        $idc_name = $_REQUEST["idc_name"];
        $authUrl = $prop->getAuthUrl($idc_name);

        if (strcmp($authUrl, $_REQUEST["authUrl"]) == 0) {
            $signParam["authToken"] = $authToken;
            $signParam["timestamp"] = $timestamp;
            $signature = $util->makeSignature($signParam);

            $veriParam["authToken"] = $authToken;
            $veriParam["signKey"] = $signKey;
            $veriParam["timestamp"] = $timestamp;
            $signature = $util->makeSignature($veriParam);

            $client = new HttpClient();
            $client->setRequestType("POST");
            $client->setCharSet($charset);
            $client->setReferer($_SERVER["HTTP_HOST"]);
            $client->setUserAgent($_SERVER["HTTP_USER_AGENT"]);
            
            $authUrl .= "?format=" . $format . "&charset=" . $charset;
            
            $authData["mid"] = $mid;
            $authData["authToken"] = $authToken;
            $authData["signature"] = $signature;
            $authData["timestamp"] = $timestamp;
            $authData["currency"] = "WON";
            $authData["merchantData"] = $merchantData;
            
            $client->addParam("format", $format);
            $client->addParam("charset", $charset);
            $client->addParam("mid", $mid);
            $client->addParam("authToken", $authToken);
            $client->addParam("signature", $signature);
            $client->addParam("timestamp", $timestamp);
            $client->addParam("currency", "WON");
            $client->addParam("merchantData", $merchantData);

            $client->submit($authUrl);

            $responseBody = $client->getBody();
            $result = json_decode($responseBody, true);

            logInicisTransaction('결제 결과 수신: ' . json_encode($result, JSON_UNESCAPED_UNICODE), 'response');

            $client_ip = $_SERVER['REMOTE_ADDR'];
            if (!validateInicisIP($client_ip)) {
                logInicisTransaction("허용되지 않은 IP에서 접근: {$client_ip}", 'error');
                die('Access Denied');
            }

            if (!isset($_SESSION['inicis_oid'])) {
                logInicisTransaction("세션 정보 없음", 'error');
            }

            $oid = $_REQUEST["oid"];
            preg_match('/DSP(\d+)_/', $oid, $matches);
            $order_no = intval($matches[1] ?? 0);

            if (!$order_no) {
                logInicisTransaction("주문번호 추출 실패 - OID: {$oid}", 'error');
                die('Invalid Order ID');
            }

            $stmt = mysqli_prepare($db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
            mysqli_stmt_bind_param($stmt, 'i', $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $order = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if (!$order) {
                logInicisTransaction("주문 정보 없음 - 주문번호: {$order_no}", 'error');
                die('Order Not Found');
            }

            $success = false;
            $error_message = '';

            if ($result["resultCode"] === "0000" || $result["resultCode"] === "00") {
                logInicisTransaction("결제 승인 성공 - 주문번호: {$order_no}, TID: " . ($result["tid"] ?? ''), 'response');

                $payment_data = array(
                    'order_no' => $order_no,
                    'tid' => $result["tid"] ?? '',
                    'pay_method' => $result["payMethod"] ?? '',
                    'amount' => $result["price"] ?? 0,
                    'result_code' => $result["resultCode"] ?? '',
                    'result_msg' => $result["resultMsg"] ?? '',
                    'paid_at' => date('Y-m-d H:i:s')
                );

                $insert_query = "INSERT INTO order_payment_log
                                 (order_no, pg_name, tid, pay_method, amount, result_code, result_msg, paid_at, created_at)
                                 VALUES (?, 'inicis', ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = mysqli_prepare($db, $insert_query);
                $tid = $result["tid"] ?? '';
                $payMethod = $result["payMethod"] ?? '';
                $price = $result["price"] ?? 0;
                $resultCode = $result["resultCode"] ?? '';
                $resultMsg = $result["resultMsg"] ?? '';
                mysqli_stmt_bind_param($stmt, 'issdsss',
                    $order_no,
                    $tid,
                    $payMethod,
                    $price,
                    $resultCode,
                    $resultMsg,
                    $payment_data['paid_at']
                );

                if (mysqli_stmt_execute($stmt)) {
                    logInicisTransaction("결제 로그 저장 성공 - 주문번호: {$order_no}", 'info');
                } else {
                    logInicisTransaction("결제 로그 저장 실패: " . mysqli_error($db), 'error');
                }
                mysqli_stmt_close($stmt);

                $update_query = "UPDATE mlangorder_printauto
                                 SET money_2 = ?,
                                     OrderStyle = 'payment_confirmed'
                                 WHERE no = ?";

                $stmt = mysqli_prepare($db, $update_query);
                $update_price = $result["price"] ?? 0;
                mysqli_stmt_bind_param($stmt, 'di', $update_price, $order_no);

                if (mysqli_stmt_execute($stmt)) {
                    logInicisTransaction("주문 상태 업데이트 성공 - 주문번호: {$order_no}", 'info');
                    $success = true;

                    $email_query = "INSERT INTO order_email_log
                                    (order_no, email_type, recipient, subject, body, sent_status, created_at)
                                    VALUES (?, 'payment_confirmed', ?, '', '', 'pending', NOW())";

                    $stmt_email = mysqli_prepare($db, $email_query);
                    $order_no_param = $order_no;
                    $recipient_param = $order['email'];
                    mysqli_stmt_bind_param($stmt_email, 'is', $order_no_param, $recipient_param);
                    mysqli_stmt_execute($stmt_email);
                    mysqli_stmt_close($stmt_email);

                } else {
                    logInicisTransaction("주문 상태 업데이트 실패: " . mysqli_error($db), 'error');
                    $error_message = '결제는 완료되었으나 주문 처리 중 오류가 발생했습니다.';
                }
                mysqli_stmt_close($stmt);

            } else {
                logInicisTransaction("결제 실패 - 주문번호: {$order_no}, 코드: " . ($result["resultCode"] ?? '') . ", 메시지: " . ($result["resultMsg"] ?? ''), 'error');
                $error_message = $result["resultMsg"] ?? getInicisErrorMessage($result["resultCode"] ?? '');
            }

            unset($_SESSION['inicis_oid']);
            unset($_SESSION['inicis_order_no']);
            unset($_SESSION['inicis_price']);
            unset($_SESSION['inicis_timestamp']);

            ?>
            <!DOCTYPE html>
            <html lang="ko">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?php echo $success ? '결제 완료' : '결제 실패'; ?> - 두손기획인쇄</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        min-height: 100vh;
                        padding: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .container {
                        max-width: 600px;
                        width: 100%;
                        background: white;
                        border-radius: 20px;
                        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                        padding: 60px 40px;
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
                        font-size: 60px;
                        animation: scaleIn 0.5s ease-out;
                    }

                    .icon.success {
                        background: linear-gradient(135deg, #4caf50, #45a049);
                    }

                    .icon.error {
                        background: linear-gradient(135deg, #f44336, #e53935);
                    }

                    @keyframes scaleIn {
                        0% { transform: scale(0); opacity: 0; }
                        50% { transform: scale(1.1); }
                        100% { transform: scale(1); opacity: 1; }
                    }

                    h1 {
                        color: #333;
                        font-size: 28px;
                        margin-bottom: 20px;
                    }

                    .message {
                        color: #666;
                        font-size: 16px;
                        line-height: 1.6;
                        margin-bottom: 30px;
                    }

                    .info-box {
                        background: #f8f9fa;
                        border-radius: 12px;
                        padding: 25px;
                        margin-bottom: 30px;
                        text-align: left;
                    }

                    .info-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 12px 0;
                        border-bottom: 1px solid #e1e8ed;
                    }

                    .info-row:last-child {
                        border-bottom: none;
                    }

                    .info-label {
                        color: #6c757d;
                        font-size: 14px;
                    }

                    .info-value {
                        color: #333;
                        font-size: 14px;
                        font-weight: 600;
                    }

                    .button-group {
                        display: flex;
                        gap: 15px;
                        margin-top: 30px;
                    }

                    .btn {
                        flex: 1;
                        padding: 15px 30px;
                        border: none;
                        border-radius: 50px;
                        font-size: 16px;
                        font-weight: bold;
                        cursor: pointer;
                        text-decoration: none;
                        display: inline-block;
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                    }

                    .btn-primary {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                    }

                    .btn-primary:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
                    }

                    .btn-secondary {
                        background: #95a5a6;
                        color: white;
                    }

                    .btn-secondary:hover {
                        background: #7f8c8d;
                    }

                    @media (max-width: 640px) {
                        .container {
                            padding: 40px 25px;
                        }

                        h1 {
                            font-size: 22px;
                        }

                        .button-group {
                            flex-direction: column;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <?php if ($success): ?>
                        <div class="icon success">✓</div>
                        <h1>결제가 완료되었습니다!</h1>
                        <p class="message">
                            주문하신 제품을 빠르게 제작하여 발송해드리겠습니다.
                        </p>

                        <div class="info-box">
                            <div class="info-row">
                                <span class="info-label">주문번호</span>
                                <span class="info-value">#<?php echo $order_no; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">결제금액</span>
                                <span class="info-value"><?php echo formatInicisAmount($result["price"] ?? 0); ?>원</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">결제수단</span>
                                <span class="info-value">
                                    <?php
                                    $method_names = [
                                        'Card' => '신용카드',
                                        'DirectBank' => '계좌이체',
                                        'VBank' => '가상계좌',
                                        'HPP' => '휴대폰'
                                    ];
                                    echo $method_names[$result["payMethod"] ?? ''] ?? ($result["payMethod"] ?? '');
                                    ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">거래번호</span>
                                <span class="info-value"><?php echo htmlspecialchars($result["tid"] ?? ''); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">주문상태</span>
                                <span class="info-value" style="color: #4caf50;">✓ 입금 확인됨</span>
                            </div>
                        </div>

                        <p class="message" style="font-size: 14px; color: #888;">
                            결제 확인 메일이 발송되었습니다.<br>
                            제작 진행 상황은 이메일로 안내해드리겠습니다.
                        </p>

                    <?php else: ?>
                        <div class="icon error">✕</div>
                        <h1>결제에 실패했습니다</h1>
                        <p class="message">
                            <?php echo htmlspecialchars($error_message); ?>
                        </p>

                        <div class="info-box">
                            <div class="info-row">
                                <span class="info-label">주문번호</span>
                                <span class="info-value">#<?php echo $order_no; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">오류코드</span>
                                <span class="info-value"><?php echo htmlspecialchars($result["resultCode"] ?? ''); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">오류메시지</span>
                                <span class="info-value"><?php echo htmlspecialchars($result["resultMsg"] ?? ''); ?></span>
                            </div>
                        </div>

                        <p class="message" style="font-size: 14px; color: #888;">
                            문제가 계속되면 고객센터로 문의주세요.<br>
                            전화: 02-2632-1830
                        </p>
                    <?php endif; ?>

                    <div class="button-group">
                        <?php if ($success): ?>
                            <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
                        <?php else: ?>
                            <a href="/payment/request.php?order_no=<?php echo $order_no; ?>" class="btn btn-secondary">다시 결제하기</a>
                            <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
                        <?php endif; ?>
                    </div>
                </div>
            </body>
            </html>
            <?php

        } else {
            echo "<script>alert('인증 오류가 발생했습니다.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('결제가 실패했습니다.'); history.back();</script>";
    }

} catch (Exception $e) {
    logInicisTransaction("처리 중 예외 발생: " . $e->getMessage(), 'error');
    echo "<script>alert('처리 중 오류가 발생했습니다.'); history.back();</script>";
}
?>