<?php
session_start();

// 이미 로그인된 경우 주문 조회 페이지로 이동
if (isset($_SESSION['customer_authenticated']) && $_SESSION['customer_authenticated'] === true) {
    header('Location: my_orders.php');
    exit;
}

$error_message = '';
$success_message = '';

// 메시지 처리
if (isset($_GET['logout'])) {
    $success_message = '안전하게 로그아웃되었습니다.';
} elseif (isset($_GET['timeout'])) {
    $error_message = '보안을 위해 세션이 만료되었습니다. 다시 로그인해주세요.';
}

if ($_POST) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    
    if (empty($customer_name) || empty($customer_phone)) {
        $error_message = '주문자명과 전화번호를 모두 입력해주세요.';
    } else {
        include "../db.php";
        
        // 전화번호 정규화 (하이픈 제거)
        $phone_normalized = preg_replace('/[^0-9]/', '', $customer_phone);
        
        // 데이터베이스에서 확인
        $safe_name = mysqli_real_escape_string($db, $customer_name);
        $safe_phone = mysqli_real_escape_string($db, $customer_phone);
        $safe_phone_normalized = mysqli_real_escape_string($db, $phone_normalized);
        
        $query = "SELECT * FROM MlangOrder_PrintAuto WHERE name='$safe_name' AND (
                    phone='$safe_phone' OR 
                    phone='$safe_phone_normalized' OR 
                    REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '(', '') LIKE '%$safe_phone_normalized%'
                  ) LIMIT 1";
        
        $result = mysqli_query($db, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            // 인증 성공
            $_SESSION['customer_authenticated'] = true;
            $_SESSION['customer_name'] = $customer_name;
            $_SESSION['customer_phone'] = $customer_phone;
            $_SESSION['customer_auth_time'] = time();
            
            mysqli_close($db);
            header('Location: my_orders.php');
            exit;
        } else {
            $error_message = '입력하신 정보와 일치하는 주문 내역을 찾을 수 없습니다.<br>주문자명과 전화번호를 다시 확인해주세요.';
        }
        
        mysqli_close($db);
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>두손기획인쇄 - 나의 주문 조회</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradient 3s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .company-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .company-logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .company-subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .page-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .page-title::before {
            content: '👤';
            margin-right: 0.5rem;
        }
        
        .page-subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .info-box {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-box h3 {
            color: #0c4a6e;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .info-box h3::before {
            content: '🔒';
            margin-right: 0.5rem;
        }
        
        .info-list {
            color: #0369a1;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .info-list li {
            margin-bottom: 0.5rem;
            position: relative;
            padding-left: 1rem;
        }
        
        .info-list li::before {
            content: '•';
            color: #0ea5e9;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .success-message {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: #333;
            font-size: 1rem;
        }
        
        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #fafafa;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .form-input::placeholder {
            color: #94a3b8;
        }
        
        .login-button {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.3);
        }
        
        .login-button:active {
            transform: translateY(0);
        }
        
        .help-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .help-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .help-text {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .contact-info {
            margin-top: 1rem;
            color: #667eea;
            font-weight: 600;
        }
        
        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .admin-link:hover {
            background: rgba(0,0,0,0.9);
            transform: translateY(-2px);
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 1.5rem;
                margin: 10px;
            }
            
            .company-logo {
                font-size: 1.8rem;
            }
            
            .page-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="company-header">
            <div class="company-logo">두손기획인쇄</div>
            <div class="company-subtitle">기획에서 인쇄까지 원스톱 서비스</div>
            <div class="page-title">나의 주문 조회</div>
            <div class="page-subtitle">본인의 주문 내역만 안전하게 확인하세요</div>
        </div>
        
        <div class="info-box">
            <h3>개인정보 보안 시스템</h3>
            <ul class="info-list">
                <li>주문 시 입력한 정보로만 조회 가능</li>
                <li>본인의 주문 내역만 표시</li>
                <li>다른 고객의 정보는 완전 차단</li>
                <li>2시간 후 자동 로그아웃</li>
            </ul>
        </div>
        
        <?php if ($error_message): ?>
        <div class="message error-message">
            <strong>❌ 조회 실패:</strong><br><?= $error_message ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
        <div class="message success-message">
            <strong>✅ 알림:</strong> <?= $success_message ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="customer_name">📝 주문자명</label>
                <input 
                    type="text" 
                    id="customer_name" 
                    name="customer_name" 
                    class="form-input" 
                    placeholder="주문 시 입력한 이름을 정확히 입력해주세요"
                    value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>"
                    required
                    autocomplete="name"
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="customer_phone">📞 전화번호</label>
                <input 
                    type="tel" 
                    id="customer_phone" 
                    name="customer_phone" 
                    class="form-input" 
                    placeholder="010-1234-5678 또는 01012345678"
                    value="<?= htmlspecialchars($_POST['customer_phone'] ?? '') ?>"
                    required
                    autocomplete="tel"
                >
            </div>
            
            <button type="submit" class="login-button">
                🔍 나의 주문 조회하기
            </button>
        </form>
        
        <div class="help-section">
            <div class="help-title">❓ 조회가 안 되시나요?</div>
            <div class="help-text">
                주문 시 입력한 정보와 정확히 일치해야 합니다.<br>
                문의사항이 있으시면 연락주세요.
            </div>
            <div class="contact-info">
                📞 02-2632-1830 | 1688-2384
            </div>
        </div>
    </div>
    
    <!-- 관리자 링크 -->
    <a href="checkboard_auth.php" class="admin-link">🔧 관리자</a>
    
    <script>
        // 자동 포커스
        document.getElementById('customer_name').focus();
        
        // 전화번호 자동 하이픈 추가
        document.getElementById('customer_phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length >= 3 && value.length <= 7) {
                value = value.replace(/(\d{3})(\d{1,4})/, '$1-$2');
            } else if (value.length >= 8) {
                value = value.replace(/(\d{3})(\d{4})(\d{1,4})/, '$1-$2-$3');
            }
            e.target.value = value;
        });
        
        // 엔터키로 폼 제출
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>