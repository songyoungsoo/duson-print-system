<?php
// 교정본 확인 완료 감사 페이지
include "../db.php";

// 주문번호와 토큰 가져오기
$order_no = $_GET['order'] ?? 0;
$token = $_GET['token'] ?? '';
$action = $_GET['action'] ?? 'approved';

// 보안 검증
if (!$order_no || !$token) {
    die("잘못된 접근입니다.");
}

// 주문 정보 조회
$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("주문을 찾을 수 없습니다.");
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>감사합니다 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        .thank-you-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon svg {
            width: 60px;
            height: 60px;
            stroke: white;
            stroke-width: 3;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .reject-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .message {
            color: #666;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .order-info {
            background: #f7f9fc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: left;
        }

        .order-info h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: 600;
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
            color: #888;
            font-size: 14px;
        }

        .info-value {
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }

        .next-steps {
            background: #fff9e6;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .next-steps h3 {
            color: #f57c00;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .next-steps ul {
            list-style: none;
            padding-left: 0;
        }

        .next-steps li {
            color: #666;
            font-size: 14px;
            line-height: 1.8;
            padding-left: 25px;
            position: relative;
            margin-bottom: 10px;
        }

        .next-steps li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4caf50;
            font-weight: bold;
        }

        .reject-steps {
            background: #ffebee;
            border-left: 4px solid #f44336;
        }

        .reject-steps h3 {
            color: #c62828;
        }

        .reject-steps li:before {
            content: "→";
            color: #f44336;
        }

        .contact-info {
            background: #f0f4f8;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .contact-info h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        .contact-item svg {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            stroke: #667eea;
        }

        .home-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .home-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        @media (max-width: 640px) {
            .thank-you-card {
                padding: 40px 25px;
            }

            h1 {
                font-size: 24px;
            }

            .message {
                font-size: 16px;
            }

            .info-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="thank-you-card">
            <?php if ($action === 'approved'): ?>
                <!-- 승인 완료 -->
                <div class="success-icon">
                    <svg viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>

                <h1>교정본 승인 완료!</h1>

                <p class="message">
                    교정본을 승인해 주셔서 감사합니다.<br>
                    곧 제작을 시작하겠습니다.
                </p>

                <div class="order-info">
                    <h3>📦 주문 정보</h3>
                    <div class="info-row">
                        <span class="info-label">주문번호</span>
                        <span class="info-value">#<?php echo htmlspecialchars($order['no']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">주문자</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">제품명</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['Product']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">현재 상태</span>
                        <span class="info-value" style="color: #4caf50;">✓ 교정본 승인됨</span>
                    </div>
                </div>

                <div class="next-steps">
                    <h3>📋 다음 단계</h3>
                    <ul>
                        <li>제작 팀에서 승인된 교정본을 확인합니다</li>
                        <li>품질 검사 후 제작을 시작합니다</li>
                        <li>제작 완료 시 이메일로 알려드립니다</li>
                        <li>출고 준비가 완료되면 배송 정보를 발송합니다</li>
                    </ul>
                </div>

            <?php else: ?>
                <!-- 거부 완료 -->
                <div class="success-icon reject-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>

                <h1>수정 요청 접수 완료</h1>

                <p class="message">
                    소중한 의견 감사합니다.<br>
                    요청하신 내용을 반영하여 다시 제작하겠습니다.
                </p>

                <div class="order-info">
                    <h3>📦 주문 정보</h3>
                    <div class="info-row">
                        <span class="info-label">주문번호</span>
                        <span class="info-value">#<?php echo htmlspecialchars($order['no']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">주문자</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">제품명</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['Product']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">현재 상태</span>
                        <span class="info-value" style="color: #f44336;">수정 요청됨</span>
                    </div>
                </div>

                <div class="next-steps reject-steps">
                    <h3>📋 다음 단계</h3>
                    <ul>
                        <li>담당자가 수정 요청 사항을 확인합니다</li>
                        <li>요청하신 내용을 반영하여 재작업합니다</li>
                        <li>수정된 교정본을 다시 제작합니다</li>
                        <li>새로운 교정본이 준비되면 이메일로 알려드립니다</li>
                    </ul>
                </div>

            <?php endif; ?>

            <div class="contact-info">
                <h3>💬 문의사항이 있으신가요?</h3>
                <div class="contact-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    Tel: 032-123-4567
                </div>
                <div class="contact-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    Email: info@dusonprint.com
                </div>
            </div>

            <a href="../index.php" class="home-button">홈페이지로 돌아가기</a>
        </div>
    </div>
</body>
</html>
