<?php
/**
 * KG이니시스 결제 요청 페이지
 * 두손기획인쇄 - dsp114.com
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/libs/INIStdPayUtil.php';

$SignatureUtil = new INIStdPayUtil();

// ================================
// 주문 정보 조회
// ================================
$orderId = intval($_GET['order_no'] ?? 0);

if (!$orderId) {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head><meta charset='UTF-8'><title>오류</title>
    <style>body{font-family:sans-serif;padding:40px;text-align:center;}</style>
    </head>
    <body>
        <h1>⚠️ 잘못된 접근입니다</h1>
        <p>주문번호가 없습니다.</p>
        <a href='/'>홈으로 돌아가기</a>
    </body>
    </html>
    ");
}

// 주문 조회
$stmt = mysqli_prepare($db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
mysqli_stmt_bind_param($stmt, 'i', $orderId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head><meta charset='UTF-8'><title>오류</title>
    <style>body{font-family:sans-serif;padding:40px;text-align:center;}</style>
    </head>
    <body>
        <h1>⚠️ 주문을 찾을 수 없습니다</h1>
        <p>주문번호: {$orderId}</p>
        <a href='/'>홈으로 돌아가기</a>
    </body>
    </html>
    ");
}

// 이미 결제된 주문 체크
if (in_array($order['OrderStyle'], ['payment_confirmed', 'in_production'])) {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head><meta charset='UTF-8'><title>알림</title>
    <style>body{font-family:sans-serif;padding:40px;text-align:center;}</style>
    </head>
    <body>
        <h1>✅ 이미 결제가 완료된 주문입니다</h1>
        <p>주문번호: {$orderId}</p>
        <a href='/'>홈으로 돌아가기</a>
    </body>
    </html>
    ");
}

// ================================
// 결제 금액 확인
// ================================
$price = intval($order['money_5'] ?? $order['money_4'] ?? $order['money_1'] ?? 0);

if ($price <= 0) {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head><meta charset='UTF-8'><title>오류</title>
    <style>body{font-family:sans-serif;padding:40px;text-align:center;}</style>
    </head>
    <body>
        <h1>⚠️ 결제 금액 오류</h1>
        <p>주문번호 <strong>{$orderId}</strong>의 결제 금액이 0원입니다.</p>
        <a href='/'>홈으로 돌아가기</a>
    </body>
    </html>
    ");
}

// ================================
// 결제 파라미터 생성
// ================================
$mid = INICIS_MID;
$signKey = INICIS_SIGNKEY;
$mKey = $SignatureUtil->makeHash($signKey, "sha256");
$timestamp = $SignatureUtil->getTimestamp();

// 주문번호: DSP + 시스템주문번호 + 타임스탬프
$oid = "DSP{$orderId}_{$timestamp}";

// 구매자 정보
$goodsName = sanitize_goods_name($order['Type'] ?? '인쇄물');
$buyerName = sanitize_buyer_name($order['name'] ?? '');
$buyerTel = sanitize_phone($order['Hendphone'] ?? $order['phone'] ?? '');
$buyerEmail = $order['email'] ?? 'guest@' . SITE_DOMAIN;

// Signature 생성
$params1 = array(
    "oid" => $oid,
    "price" => $price,
    "timestamp" => $timestamp
);
$signature = $SignatureUtil->makeSignature($params1);

$params2 = array(
    "oid" => $oid,
    "price" => $price,
    "signKey" => $signKey,
    "timestamp" => $timestamp
);
$verification = $SignatureUtil->makeSignature($params2);

// 세션에 저장
$_SESSION['inicis_order_no'] = $orderId;
$_SESSION['inicis_oid'] = $oid;
$_SESSION['inicis_price'] = $price;

inicis_log("결제 요청 - 주문번호: {$orderId}, OID: {$oid}, 금액: {$price}원", 'request');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제하기 - 두손기획인쇄</title>

    <!-- KG이니시스 JS -->
    <script type="text/javascript" src="<?php echo INICIS_JS_URL; ?>" charset="UTF-8"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 440px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 { font-size: 20px; margin-bottom: 4px; }
        .header p { font-size: 13px; opacity: 0.9; }
        .content { padding: 24px; }
        .order-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-box h2 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6c757d; font-size: 13px; }
        .info-value { color: #2c3e50; font-size: 13px; font-weight: 600; }
        .price-box {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin-bottom: 20px;
        }
        .price-label { font-size: 12px; opacity: 0.9; margin-bottom: 8px; }
        .price-value { font-size: 32px; font-weight: bold; }
        .methods {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .method {
            flex: 1;
            background: #f0f4f8;
            padding: 12px 8px;
            border-radius: 8px;
            text-align: center;
            font-size: 12px;
            color: #333;
        }
        .btn-pay {
            width: 100%;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44,62,80,0.3);
        }
        .btn-pay:active { transform: translateY(0); }
        .notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            border-radius: 4px;
            margin-top: 16px;
            font-size: 12px;
            color: #856404;
        }
        .notice ul { margin-left: 16px; margin-top: 8px; }
        .notice li { margin-bottom: 4px; }
        <?php if (INICIS_TEST_MODE): ?>
        .test-badge {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        <?php endif; ?>
    </style>

    <script type="text/javascript">
        function doPayment() {
            if (typeof INIStdPay === 'undefined') {
                alert('결제 모듈 로딩에 실패했습니다. 페이지를 새로고침 해주세요.');
                return;
            }
            INIStdPay.pay('payForm');
        }

        window.onload = function() {
            if (typeof INIStdPay === 'undefined') {
                console.error('INIStdPay SDK 로딩 실패');
            } else {
                console.log('INIStdPay SDK 로딩 완료');
            }
        };
    </script>
</head>
<body>
    <?php if (INICIS_TEST_MODE): ?>
    <div class="test-badge">테스트 모드</div>
    <?php endif; ?>

    <div class="container">
        <div class="header">
            <h1>💳 결제하기</h1>
            <p>두손기획인쇄</p>
        </div>

        <div class="content">
            <div class="order-box">
                <h2>📦 주문 정보</h2>
                <div class="info-row">
                    <span class="info-label">주문번호</span>
                    <span class="info-value">#<?php echo $orderId; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">상품명</span>
                    <span class="info-value"><?php echo htmlspecialchars($goodsName); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">주문자</span>
                    <span class="info-value"><?php echo htmlspecialchars($buyerName); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">연락처</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['Hendphone'] ?? $order['phone'] ?? ''); ?></span>
                </div>
            </div>

            <div class="price-box">
                <div class="price-label">결제 금액</div>
                <div class="price-value"><?php echo format_price($price); ?>원</div>
            </div>

            <div class="methods">
                <div class="method">💳 신용카드</div>
                <div class="method">🏦 계좌이체</div>
                <div class="method">📱 휴대폰</div>
            </div>

            <button type="button" class="btn-pay" onclick="doPayment()">
                결제하기
            </button>

            <div class="notice">
                <strong>📌 결제 전 확인사항</strong>
                <ul>
                    <li>결제 금액과 주문 정보를 확인해주세요</li>
                    <li>문의: 02-2632-1830</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 결제 폼 (숨김) -->
    <form id="payForm" method="post" style="display:none;">
        <input type="hidden" name="version" value="1.0">
        <input type="hidden" name="mid" value="<?php echo $mid; ?>">
        <input type="hidden" name="oid" value="<?php echo $oid; ?>">
        <input type="hidden" name="price" value="<?php echo $price; ?>">
        <input type="hidden" name="timestamp" value="<?php echo $timestamp; ?>">
        <input type="hidden" name="use_chkfake" value="Y">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>">
        <input type="hidden" name="verification" value="<?php echo $verification; ?>">
        <input type="hidden" name="mKey" value="<?php echo $mKey; ?>">
        <input type="hidden" name="currency" value="WON">
        <input type="hidden" name="goodname" value="<?php echo htmlspecialchars($goodsName); ?>">
        <input type="hidden" name="buyername" value="<?php echo htmlspecialchars($buyerName); ?>">
        <input type="hidden" name="buyertel" value="<?php echo $buyerTel; ?>">
        <input type="hidden" name="buyeremail" value="<?php echo htmlspecialchars($buyerEmail); ?>">
        <input type="hidden" name="returnUrl" value="<?php echo INICIS_RETURN_URL; ?>">
        <input type="hidden" name="closeUrl" value="<?php echo INICIS_CLOSE_URL; ?>">
        <input type="hidden" name="gopaymethod" value="Card:DirectBank:HPP">
        <input type="hidden" name="acceptmethod" value="below1000:HPP(1)">
    </form>
</body>
</html>
