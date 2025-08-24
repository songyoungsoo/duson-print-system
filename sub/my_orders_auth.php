<?php
session_start();

// Check if user is already authenticated
if (isset($_SESSION['customer_authenticated']) && $_SESSION['customer_authenticated'] === true) {
    header('Location: my_orders.php');
    exit;
}

$error_message = '';
$info_message = '';

// Handle URL parameters
if (isset($_GET['logout'])) {
    $info_message = '안전하게 로그아웃되었습니다.';
} elseif (isset($_GET['timeout'])) {
    $error_message = '보안을 위해 세션이 만료되었습니다. 다시 로그인해주세요.';
}

if ($_POST) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    
    if (empty($customer_name) || empty($customer_phone)) {
        $error_message = '주문자명과 전화번호를 모두 입력해주세요.';
    } else {
        // Database connection
        include "../db.php";
        
        // 주문자명과 전화번호로 주문 확인 (대소문자 구분 없이)
        $stmt = $db->prepare("SELECT COUNT(*) as order_count FROM MlangOrder_PrintAuto WHERE LOWER(name) = LOWER(?) AND (phone LIKE ? OR phone LIKE ? OR phone LIKE ?)");
        $phone_patterns = [
            '%' . $customer_phone . '%',
            $customer_phone,
            str_replace('-', '', $customer_phone)
        ];
        $stmt->bind_param("ssss", $customer_name, $phone_patterns[0], $phone_patterns[1], $phone_patterns[2]);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['order_count'] > 0) {
            // 인증 성공
            $_SESSION['customer_authenticated'] = true;
            $_SESSION['customer_name'] = $customer_name;
            $_SESSION['customer_phone'] = $customer_phone;
            $_SESSION['customer_auth_timestamp'] = time();
            
            header('Location: my_orders.php');
            exit;
        } else {
            $error_message = '입력하신 정보와 일치하는 주문을 찾을 수 없습니다. 주문자명과 전화번호를 확인해주세요.';
        }
        
        $stmt->close();
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>두손기획인쇄 - 내 주문 조회</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
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
        
        .auth-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-container::before {
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
        
        .company-logo {
            margin-bottom: 2rem;
        }
        
        .company-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .company-subtitle {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 2rem;
        }
        
        .auth-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .security-notice {
            background: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 1.2rem;
            margin-bottom: 2rem;
            text-align: left;
            border-radius: 10px;
        }
        
        .security-notice-title {
            font-weight: 600;
            color: #0c4a6e;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .security-icon {
            margin-right: 8px;
            font-size: 1.2rem;
        }
        
        .security-notice-text {
            font-size: 0.9rem;
            color: #0369a1;
            line-height: 1.4;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        .auth-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .auth-button:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dc2626;
            text-align: left;
            font-size: 0.9rem;
        }
        
        .info-message {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #10b981;
            text-align: left;
            font-size: 0.9rem;
        }
        
        .help-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 15px;
            text-align: left;
        }
        
        .help-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .help-list {
            list-style: none;
            padding: 0;
        }
        
        .help-list li {
            color: #666;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            position: relative;
            padding-left: 1.5rem;
        }
        
        .help-list li::before {
            content: '💡';
            position: absolute;
            left: 0;
        }
        
        .contact-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .contact-phone {
            color: #667eea;
            font-weight: 600;
        }
        
        .admin-link {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .admin-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .admin-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .auth-container {
                padding: 2rem 1.5rem;
            }
            
            .company-name {
                font-size: 1.5rem;
            }
            
            .auth-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="company-logo">
            <div class="company-name">두손기획인쇄</div>
            <div class="company-subtitle">기획에서 인쇄까지 원스톱 서비스</div>
        </div>
        
        <div class="auth-title">내 주문 조회</div>
        <div class="auth-subtitle">본인의 주문 정보만 안전하게 확인하실 수 있습니다</div>
        
        <div class="security-notice">
            <div class="security-notice-title">
                <span class="security-icon">🔐</span>
                개인정보 보호 시스템
            </div>
            <div class="security-notice-text">
                주문 시 입력하신 정보로 본인 확인 후, 해당 주문만 표시됩니다. 
                다른 고객의 주문 정보는 절대 노출되지 않습니다.
            </div>
        </div>
        
        <?php if ($error_message): ?>
        <div class="error-message">
            <strong>❌ 인증 실패:</strong> <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($info_message): ?>
        <div class="info-message">
            <strong>✅ 알림:</strong> <?= htmlspecialchars($info_message) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="customer_name">주문자명</label>
                <input 
                    type="text" 
                    id="customer_name" 
                    name="customer_name" 
                    class="form-input" 
                    placeholder="주문 시 입력하신 이름"
                    value="<?= isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : '' ?>"
                    required
                    autocomplete="name"
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="customer_phone">전화번호</label>
                <input 
                    type="text" 
                    id="customer_phone" 
                    name="customer_phone" 
                    class="form-input" 
                    placeholder="010-1234-5678 또는 01012345678"
                    value="<?= isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : '' ?>"
                    required
                    autocomplete="tel"
                >
            </div>
            
            <button type="submit" class="auth-button">
                🔍 내 주문 조회하기
            </button>
        </form>
        
        <div class="help-section">
            <div class="help-title">
                <span class="security-icon">❓</span>
                조회가 안 될 때
            </div>
            <ul class="help-list">
                <li>주문 시 입력한 이름과 정확히 일치하는지 확인</li>
                <li>전화번호는 하이픈(-) 유무와 관계없이 입력 가능</li>
                <li>최근 주문한 내역이 아직 시스템에 반영되지 않았을 수 있음</li>
                <li>온라인 주문이 아닌 전화/방문 주문은 별도 문의</li>
            </ul>
        </div>
        
        <div class="contact-info">
            <strong>문의사항이 있으시면:</strong><br>
            📞 <span class="contact-phone">02-2632-1830</span> | 
            📞 <span class="contact-phone">1688-2384</span>
        </div>
        
        <div class="admin-link">
            <a href="checkboard_auth.php">🛠️ 관리자 전체 주문 조회</a>
        </div>
    </div>
    
    <script>
        // Auto focus on name input
        document.getElementById('customer_name').focus();
        
        // 전화번호 입력 시 자동 하이픈 추가 (선택사항)
        document.getElementById('customer_phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value.length >= 3 && value.length <= 7) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            } else if (value.length >= 8) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7) + '-' + value.substring(7, 11);
            }
            e.target.value = value;
        });
        
        // Clear error message on input
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', function() {
                const errorDiv = document.querySelector('.error-message');
                if (errorDiv) {
                    errorDiv.style.opacity = '0.5';
                }
            });
        });
    </script>
</body>
</html>