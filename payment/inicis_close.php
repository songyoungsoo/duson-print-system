<?php
/**
 * KG이니시스 결제창 닫기 페이지
 * 두손기획인쇄 - 사용자가 결제창을 닫았을 때 처리
 */

// 설정 파일 로드
require_once __DIR__ . '/inicis_config.php';

// 세션에서 주문 정보 가져오기
$order_no = $_SESSION['inicis_order_no'] ?? 0;

// 로그 기록
logInicisTransaction("결제창 닫힌 - 주문번호: {$order_no}", 'info');

// 세션 정리
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
    <title>결제 취소 - 두손기획인쇄</title>
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
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            text-align: center;
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .button-group {
            display: flex;
            gap: 15px;
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
            transition: transform 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
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

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⊗</div>
        <h1>결제가 취소되었습니다</h1>
        <p class="message">
            결제창을 닫으셨습니다.<br>
            다시 결제를 진행하시려면 아래 버튼을 클릭해주세요.
        </p>

        <div class="button-group">
            <?php if ($order_no > 0): ?>
                <a href="/payment/inicis_request.php?order_no=<?php echo $order_no; ?>" class="btn btn-secondary">다시 결제하기</a>
            <?php endif; ?>
            <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
        </div>
    </div>
</body>
</html>
