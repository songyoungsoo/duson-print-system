<?php
/**
 * KG이니시스 결제 요청 페이지
 * 두손기획인쇄 - 결제 시작
 *
 * 사용법: /payment/inicis_request.php?order_no=123
 */

// 설정 파일 로드
require_once __DIR__ . '/inicis_config.php';
require_once __DIR__ . '/../db.php';

// 주문번호 받기
$order_no = intval($_GET['order_no'] ?? 0);

if (!$order_no) {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head><meta charset='UTF-8'><title>오류</title></head>
    <body>
        <h1>잘못된 접근입니다.</h1>
        <p>주문번호가 없습니다.</p>
        <a href='/'>홈으로 돌아가기</a>
    </body>
    </html>
    ");
}

// 주문 정보 조회
$stmt = mysqli_prepare($db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head><meta charset='UTF-8'><title>오류</title></head>
    <body>
        <h1>주문을 찾을 수 없습니다.</h1>
        <p>주문번호: {$order_no}</p>
        <a href='/'>홈으로 돌아가기</a>
    </body>
    </html>
    ");
}

// 이미 결제된 주문인지 확인
if ($order['OrderStyle'] === 'payment_confirmed' || $order['OrderStyle'] === 'in_production') {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head><meta charset='UTF-8'><title>알림</title></head>
    <body>
        <h1>이미 결제가 완료된 주문입니다.</h1>
        <p>주문번호: {$order_no}</p>
        <a href='/'>홈으로 돌아가기</a>
    </body>
    </html>
    ");
}

// 결제 정보 준비
$timestamp = getInicisTimestamp();
$oid = 'DSP' . $order_no . '_' . $timestamp; // 이니시스 주문번호

// money_5 = 부가세 포함 결제 금액, money_4 = 부가세 제외 금액
$price = intval($order['money_5'] ?? $order['money_4'] ?? $order['money_1'] ?? 0);

// 금액이 0원이면 결제 불가
if ($price <= 0) {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head><meta charset='UTF-8'><title>오류</title></head>
    <body style='font-family: sans-serif; padding: 40px; text-align: center;'>
        <h1>결제 금액 오류</h1>
        <p>주문번호 <strong>{$order_no}</strong>의 결제 금액이 0원입니다.</p>
        <p>주문 정보를 확인해주세요.</p>
        <p style='color: #888; font-size: 14px;'>money_5: " . ($order['money_5'] ?? 'NULL') . ", money_4: " . ($order['money_4'] ?? 'NULL') . "</p>
        <a href='/' style='color: #3498db;'>홈으로 돌아가기</a>
    </body>
    </html>
    ");
}

// 상품명 정리 (Type 필드 사용)
$goods_name = sanitizeGoodsName($order['Type'] ?? '인쇄물');

// 구매자 정보 정리
$buyer_name = sanitizeBuyerName($order['name']);
$buyer_tel = sanitizePhone($order['Hendphone'] ?? $order['phone'] ?? '');
$buyer_email = $order['email'] ?? '';

// 서명 및 mKey 생성
$signature = generateInicisSignature($oid, $price, $timestamp);
$mKey = generateInicisMKey();

// 필수 필드 기본값 설정
if (empty($buyer_tel)) {
    $buyer_tel = '01000000000';
}
if (empty($buyer_email)) {
    $buyer_email = 'guest@dsp114.co.kr';
}

// 로그 기록
logInicisTransaction("결제 요청 시작 - 주문번호: {$order_no}, 금액: {$price}원", 'request');

// 세션에 주문 정보 저장
$_SESSION['inicis_order_no'] = $order_no;
$_SESSION['inicis_oid'] = $oid;
$_SESSION['inicis_price'] = $price;
$_SESSION['inicis_timestamp'] = $timestamp;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제하기 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 420px;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 20px 20px 20px 50px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header-close {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }

        .header-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .content {
            padding: 24px;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .order-info h2 {
            color: #2c3e50;
            font-size: 14px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
            font-size: 13px;
        }

        .info-value {
            color: #2c3e50;
            font-size: 13px;
            font-weight: 600;
        }

        .amount-box {
            background: #3498db;
            color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .amount-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 6px;
        }

        .amount-value {
            font-size: 28px;
            font-weight: bold;
        }

        .payment-methods {
            margin-bottom: 20px;
        }

        .payment-methods h3 {
            color: #2c3e50;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .method-list {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .method-item {
            flex: 1;
            min-width: 70px;
            background: #f0f4f8;
            padding: 10px 8px;
            border-radius: 6px;
            text-align: center;
            font-size: 12px;
            color: #333;
        }

        .btn-pay {
            width: 100%;
            background: #2c3e50;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-pay:hover {
            background: #34495e;
        }

        .btn-pay:active {
            background: #1a252f;
        }

        .notice {
            background: #f8f9fa;
            border-left: 3px solid #6c757d;
            padding: 12px;
            border-radius: 4px;
            margin-top: 16px;
            font-size: 11px;
            color: #6c757d;
        }

        .notice ul {
            margin-left: 16px;
            margin-top: 8px;
        }

        .notice li {
            margin-bottom: 4px;
        }

        .notice .warning-text {
            color: #dc3545;
            font-weight: bold;
            margin: 10px 0;
            line-height: 1.5;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 360px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-box h3 {
            color: #dc3545;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }

        .modal-box p {
            color: #dc3545;
            font-weight: bold;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 10px;
        }

        .modal-box .phone {
            color: #dc3545;
            font-weight: bold;
            font-size: 18px;
            margin: 15px 0;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-buttons button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-confirm {
            background: #dc3545;
            color: white;
        }

        @media (max-width: 640px) {
            .content {
                padding: 20px;
            }

            .amount-value {
                font-size: 24px;
            }
        }
    </style>
    <!-- KG이니시스 표준결제 JS -->
    <script src="<?php echo INICIS_STD_URL; ?>" charset="UTF-8"></script>
    <script type="text/javascript">
        function paybtn() {
            document.getElementById('warningModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('warningModal').classList.remove('show');
        }

        function goToOrderComplete() {
            var orderNo = '<?php echo $order_no; ?>';
            // 주문 완료 페이지로 돌아가기 (세션에서 주문 정보 복원)
            window.location.href = '/mlangorder_printauto/OrderComplete_universal.php?orders=' + orderNo + '&payment=cancelled';
        }
        
        function proceedPayment() {
            closeModal();
            INIStdPay.pay('SendPayForm_id');
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <button type="button" class="header-close" onclick="goToOrderComplete()" title="닫기">&times;</button>
            <h1>결제하기</h1>
            <p>두손기획인쇄</p>
        </div>

        <div class="content">
            <!-- 주문 정보 -->
            <div class="order-info">
                <h2>주문 정보</h2>
                <div class="info-row">
                    <span class="info-label">주문번호</span>
                    <span class="info-value">#<?php echo $order_no; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">상품명</span>
                    <span class="info-value"><?php echo htmlspecialchars($goods_name); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">주문자</span>
                    <span class="info-value"><?php echo htmlspecialchars($buyer_name); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">연락처</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['phone1'] ?? $order['Hendphone'] ?? $order['phone'] ?? ''); ?></span>
                </div>
            </div>

            <!-- 결제 금액 -->
            <div class="amount-box">
                <div class="amount-label">결제 금액</div>
                <div class="amount-value"><?php echo formatInicisAmount($price); ?>원</div>
            </div>

            <!-- 결제 버튼 -->
            <button type="button" class="btn-pay" onclick="paybtn()">
                결제하기
            </button>

            <!-- 안내사항 -->
            <div class="notice">
                <strong>결제 전 확인사항</strong>
                <p class="warning-text">택배 선불 (금액 확인 필수), 디자인 수정(금액 변동)에 해당하시는 분은 반드시 전화 후 결제 바랍니다.</p>
                <ul>
                    <li>결제 금액과 주문 정보를 확인해주세요</li>
                    <li>문의: 02-2632-1830</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 경고 모달 -->
    <div id="warningModal" class="modal-overlay">
        <div class="modal-box">
            <h3>결제 전 필수 확인</h3>
            <p>택배 선불 (금액 확인 필수)</p>
            <p>디자인 수정 (금액 변동)</p>
            <p>위 사항에 해당하시는 분은<br>반드시 전화 후 결제해 주세요!</p>
            <div class="phone">02-2632-1830</div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="goToOrderComplete()">이전으로</button>
                <button type="button" class="btn-confirm" onclick="proceedPayment()">결제 진행</button>
            </div>
        </div>
    </div>

    <!-- 결제 폼 (숨김) -->
    <form id="SendPayForm_id" name="SendPayForm_id" method="post">
        <input type="hidden" name="version" value="1.0">
        <input type="hidden" name="mid" value="<?php echo INICIS_MID; ?>">
        <input type="hidden" name="goodname" value="<?php echo htmlspecialchars($goods_name); ?>">
        <input type="hidden" name="oid" value="<?php echo $oid; ?>">
        <input type="hidden" name="price" value="<?php echo $price; ?>">
        <input type="hidden" name="timestamp" value="<?php echo $timestamp; ?>">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>">
        <input type="hidden" name="mKey" value="<?php echo $mKey; ?>">
        <input type="hidden" name="returnUrl" value="<?php echo INICIS_RETURN_URL; ?>">
        <input type="hidden" name="closeUrl" value="<?php echo INICIS_CLOSE_URL; ?>">
        <input type="hidden" name="gopaymethod" value="Card">
        <input type="hidden" name="acceptmethod" value="below1000:HPP(1):cardonly">
        <input type="hidden" name="buyername" value="<?php echo htmlspecialchars($buyer_name); ?>">
        <input type="hidden" name="buyertel" value="<?php echo $buyer_tel; ?>">
        <input type="hidden" name="buyeremail" value="<?php echo htmlspecialchars($buyer_email); ?>">
        <input type="hidden" name="currency" value="WON">
    </form>


</body>
</html>
