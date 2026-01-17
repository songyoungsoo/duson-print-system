<?php
/**
 * 관리자 로그인 페이지
 * 보안 강화 버전 - 2026-01-13
 *
 * 기능:
 * - 세션 기반 인증
 * - 브루트포스 방지 (로그인 시도 제한)
 * - CSRF 토큰 보호
 * - 보안 로깅
 */

require_once __DIR__ . '/../includes/admin_auth.php';

$error_message = '';
$success_message = '';
$show_password_change = false;

// 이미 로그인된 경우 리다이렉트
if (isAdminLoggedIn()) {
    $redirect = $_GET['redirect'] ?? '/admin/mlangprintauto/quote/';
    // 안전한 리다이렉트 (같은 도메인만)
    if (strpos($redirect, '/') === 0 && strpos($redirect, '//') !== 0) {
        header("Location: $redirect");
    } else {
        header("Location: /admin/mlangprintauto/quote/");
    }
    exit;
}

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF 검증
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf_token)) {
        $error_message = '보안 토큰이 만료되었습니다. 페이지를 새로고침 후 다시 시도해주세요.';
    } else {
        if ($_POST['action'] === 'login') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error_message = '아이디와 비밀번호를 입력해주세요.';
            } else {
                $result = adminLogin($username, $password);

                if ($result['success']) {
                    if (isset($result['needs_password_change']) && $result['needs_password_change']) {
                        $show_password_change = true;
                        $success_message = $result['message'];
                    } else {
                        $redirect = $_GET['redirect'] ?? '/admin/mlangprintauto/quote/';
                        if (strpos($redirect, '/') === 0 && strpos($redirect, '//') !== 0) {
                            header("Location: $redirect");
                        } else {
                            header("Location: /admin/mlangprintauto/quote/");
                        }
                        exit;
                    }
                } else {
                    $error_message = $result['message'];
                }
            }
        } elseif ($_POST['action'] === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($new !== $confirm) {
                $error_message = '새 비밀번호가 일치하지 않습니다.';
                $show_password_change = true;
            } else {
                $result = changeAdminPassword($current, $new);
                if ($result['success']) {
                    $success_message = $result['message'];
                    // 비밀번호 변경 후 로그인 페이지로
                    adminLogout();
                    $success_message .= ' 새 비밀번호로 다시 로그인해주세요.';
                } else {
                    $error_message = $result['message'];
                    $show_password_change = true;
                }
            }
        }
    }

    // 새 CSRF 토큰 생성
    regenerateCsrfToken();
}

// 세션 오류 메시지
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_invalid':
            $error_message = '세션이 만료되었거나 보안 문제가 감지되었습니다. 다시 로그인해주세요.';
            break;
        case 'session_expired':
            $error_message = '세션이 만료되었습니다. 다시 로그인해주세요.';
            break;
    }
}

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>관리자 로그인 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header .logo {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .login-header h1 {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #444;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .message.success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .security-info {
            margin-top: 24px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 8px;
            font-size: 12px;
            color: #64748b;
        }

        .security-info h4 {
            color: #475569;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .security-info ul {
            margin: 0;
            padding-left: 16px;
        }

        .security-info li {
            margin-bottom: 4px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 6px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">🔐</div>
            <h1>관리자 로그인</h1>
            <p>두손기획 상품관리 시스템</p>
        </div>

        <?php if ($error_message): ?>
        <div class="message error">
            <span>⚠️</span>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
        <div class="message success">
            <span>✓</span>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <?php if ($show_password_change): ?>
        <!-- 비밀번호 변경 폼 -->
        <form method="POST" action="">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="current_password">현재 비밀번호</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>

            <div class="form-group">
                <label for="new_password">새 비밀번호</label>
                <input type="password" id="new_password" name="new_password" required autocomplete="new-password" minlength="8">
                <div class="password-requirements">8자 이상, 영문+숫자 조합</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">새 비밀번호 확인</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
            </div>

            <button type="submit" class="login-button">비밀번호 변경</button>
        </form>

        <?php else: ?>
        <!-- 로그인 폼 -->
        <form method="POST" action="">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="username">아이디</label>
                <input type="text" id="username" name="username" required autocomplete="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="login-button">로그인</button>
        </form>

        <div class="security-info">
            <h4>🛡️ 보안 안내</h4>
            <ul>
                <li>5회 로그인 실패 시 15분간 잠금됩니다</li>
                <li>8시간 후 자동 로그아웃됩니다</li>
                <li>모든 접속 기록이 저장됩니다</li>
            </ul>
        </div>
        <?php endif; ?>

        <a href="/" class="back-link">← 메인 사이트로 돌아가기</a>
    </div>
</body>
</html>
