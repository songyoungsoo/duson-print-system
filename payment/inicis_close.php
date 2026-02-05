<?php
/**
 * KG이니시스 결제창 닫기 페이지
 * 두손기획인쇄 - 사용자가 결제창을 닫았을 때 처리
 * 
 * 이니시스 결제창(팝업/iframe)이 닫힐 때 호출됨
 * 부모 창에 취소를 알리고 팝업을 닫음
 */

// 설정 파일 로드
require_once __DIR__ . '/inicis_config.php';

// 세션에서 주문 정보 가져오기
$order_no = $_SESSION['inicis_order_no'] ?? 0;

// 로그 기록
logInicisTransaction("결제창 닫힘 - 주문번호: {$order_no}", 'info');

// 세션 정리
unset($_SESSION['inicis_oid']);
unset($_SESSION['inicis_order_no']);
unset($_SESSION['inicis_price']);
unset($_SESSION['inicis_timestamp']);

// 리다이렉트 URL 설정
$redirect_url = '/';
if ($order_no > 0) {
    $redirect_url = '/mlangorder_printauto/OrderComplete_universal.php?orders=' . $order_no . '&payment=cancelled';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 취소 - 두손기획인쇄</title>
    <script>
    (function() {
        var redirectUrl = '<?php echo $redirect_url; ?>';
        
        if (window.opener && !window.opener.closed) {
            window.opener.location.href = redirectUrl;
            window.close();
        } else if (window.parent && window.parent !== window) {
            window.parent.location.href = redirectUrl;
        } else {
            window.location.href = redirectUrl;
        }
    })();
    </script>
</head>
<body>
    <p style="text-align:center; padding:50px; font-family:sans-serif;">
        결제가 취소되었습니다. 잠시 후 이동합니다...
    </p>
</body>
</html>
