<?php
session_start();

// Check if user is already authenticated
if (isset($_SESSION['checkboard_authenticated']) && $_SESSION['checkboard_authenticated'] === true) {
    header('Location: checkboard.php');
    exit;
}

$error_message = '';
$info_message = '';

// Handle URL parameters
if (isset($_GET['logout'])) {
    $info_message = '성공적으로 로그아웃되었습니다.';
} elseif (isset($_GET['timeout'])) {
    $error_message = '보안을 위해 세션이 만료되었습니다. 다시 로그인해주세요.';
}

if ($_POST) {
    $input_password = $_POST['access_password'] ?? '';
    
    // Multiple authentication methods
    $valid_passwords = [
        // Phone last 4 digits examples - you can customize these
        '1830', '2384', '1829', '0000', // Company phone endings
        // Alternative secure passwords
        'duson2025', 'print1830', 'admin123'
    ];
    
    if (in_array($input_password, $valid_passwords)) {
        $_SESSION['checkboard_authenticated'] = true;
        $_SESSION['auth_timestamp'] = time();
        header('Location: checkboard.php');
        exit;
    } else {
        $error_message = '잘못된 비밀번호입니다. 다시 시도해주세요.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>두손기획인쇄 - 주문관리 시스템 접근</title>
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
            max-width: 450px;
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
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 2rem;
        }
        
        .auth-methods {
            background: #f8fafc;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .method-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .method-icon {
            margin-right: 8px;
            font-size: 1.2rem;
        }
        
        .method-list {
            list-style: none;
            padding-left: 1.5rem;
        }
        
        .method-list li {
            color: #666;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            position: relative;
        }
        
        .method-list li::before {
            content: '•';
            color: #667eea;
            font-weight: bold;
            position: absolute;
            left: -1rem;
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
        
        .security-note {
            margin-top: 2rem;
            padding: 1rem;
            background: #f0f9ff;
            border-radius: 10px;
            border-left: 4px solid #0ea5e9;
        }
        
        .security-note-title {
            font-weight: 600;
            color: #0c4a6e;
            margin-bottom: 0.5rem;
        }
        
        .security-note-text {
            font-size: 0.8rem;
            color: #0369a1;
            line-height: 1.4;
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
        
        @media (max-width: 480px) {
            .auth-container {
                padding: 2rem 1.5rem;
            }
            
            .company-name {
                font-size: 1.5rem;
            }
            
            .auth-title {
                font-size: 1.1rem;
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
        
        <div class="auth-title">주문관리 시스템</div>
        <div class="auth-subtitle">접근 권한이 필요합니다</div>
        
        <div class="auth-methods">
            <div class="method-title">
                <span class="method-icon">🔐</span>
                인증 방법
            </div>
            <ul class="method-list">
                <li>회사 전화번호 뒷자리 4자리</li>
                <li>관리자 전용 비밀번호</li>
                <li>고객 전용 접근 코드</li>
            </ul>
        </div>
        
        <?php if ($error_message): ?>
        <div class="error-message">
            <strong>❌ 접근 거부:</strong> <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($info_message): ?>
        <div class="info-message">
            <strong>✅ 알림:</strong> <?= htmlspecialchars($info_message) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="access_password">비밀번호</label>
                <input 
                    type="password" 
                    id="access_password" 
                    name="access_password" 
                    class="form-input" 
                    placeholder="전화번호 뒷자리 4자리 또는 접근 코드"
                    required
                    autocomplete="current-password"
                >
            </div>
            
            <button type="submit" class="auth-button">
                🚀 시스템 접근
            </button>
        </form>
        
        <div class="security-note">
            <div class="security-note-title">🛡️ 보안 안내</div>
            <div class="security-note-text">
                이 시스템은 고객 주문 정보 보호를 위해 인증이 필요합니다. 
                승인된 사용자만 접근 가능하며, 모든 활동이 기록됩니다.
            </div>
        </div>
        
        <div class="contact-info">
            <strong>문의사항이 있으시면:</strong><br>
            📞 <span class="contact-phone">02-2632-1830</span> | 
            📞 <span class="contact-phone">1688-2384</span>
        </div>
    </div>
    
    <script>
        // Auto focus on password input
        document.getElementById('access_password').focus();
        
        // Add enter key listener for better UX
        document.getElementById('access_password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.target.closest('form').submit();
            }
        });
        
        // Clear error message on input
        document.getElementById('access_password').addEventListener('input', function() {
            const errorDiv = document.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.style.opacity = '0.5';
            }
        });
    </script>
</body>
</html>