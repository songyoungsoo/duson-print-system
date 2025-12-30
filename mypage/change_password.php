<?php
/**
 * 비밀번호변경
 * 경로: /mypage/change_password.php
 */

session_start();

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/member/login.php';</script>";
    exit;
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 유효성 검증
    if (strlen($new_password) < 6) {
        $error = "새 비밀번호는 최소 6자 이상이어야 합니다.";
    } elseif ($new_password !== $confirm_password) {
        $error = "새 비밀번호가 일치하지 않습니다.";
    } else {
        // 현재 비밀번호 확인
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // 비밀번호 검증 (bcrypt 해시 또는 평문 모두 지원)
        $stored_password = $user['password'];
        $password_valid = false;

        // bcrypt 해시인 경우 ($2y$로 시작하고 60자)
        if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
            $password_valid = password_verify($current_password, $stored_password);
        } else {
            // 평문 비밀번호인 경우 직접 비교
            $password_valid = ($current_password === $stored_password);
        }

        if (!$password_valid) {
            $error = "현재 비밀번호가 일치하지 않습니다.";
        } else {
            // 비밀번호 업데이트
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);

            if (mysqli_stmt_execute($stmt)) {
                $message = "비밀번호가 성공적으로 변경되었습니다.";
            } else {
                $error = "비밀번호 변경 중 오류가 발생했습니다.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호변경 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/common-styles.css">
    <style>
        body {
            background: #f5f5f5;
            font-size: 13px;
        }

        .mypage-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
        }

        .mypage-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 600px;
        }

        .page-title {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #ffffff;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            border-left: 4px solid #1466BA;
        }

        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 15px;
            color: #1466BA;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #333;
        }

        .info-box li {
            margin-bottom: 6px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-label .required {
            color: #dc3545;
            margin-left: 4px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #1466BA;
        }

        .password-strength {
            margin-top: 6px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin: 6px 0;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s;
            width: 0%;
        }

        .strength-weak .strength-fill {
            width: 33%;
            background: #dc3545;
        }

        .strength-medium .strength-fill {
            width: 66%;
            background: #ffc107;
        }

        .strength-strong .strength-fill {
            width: 100%;
            background: #28a745;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #1466BA;
            color: white;
        }

        .btn-primary:hover {
            background: #0d4d8a;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .mypage-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="mypage-container">
        <!-- 사이드바 -->
        <?php include 'sidebar.php'; ?>

        <!-- 메인 컨텐츠 -->
        <div class="mypage-content">
            <h1 class="page-title">비밀번호변경</h1>

            <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- 안내 메시지 -->
            <div class="info-box">
                <h3>🔒 비밀번호 안전 수칙</h3>
                <ul>
                    <li>비밀번호는 최소 6자 이상 입력해주세요.</li>
                    <li>영문, 숫자, 특수문자를 조합하면 더 안전합니다.</li>
                    <li>다른 사이트와 동일한 비밀번호는 사용하지 마세요.</li>
                    <li>정기적으로 비밀번호를 변경해주세요.</li>
                </ul>
            </div>

            <form method="post" id="passwordForm">
                <!-- 현재 비밀번호 -->
                <div class="form-group">
                    <label class="form-label">
                        현재 비밀번호<span class="required">*</span>
                    </label>
                    <input type="password" name="current_password" class="form-control" required placeholder="현재 비밀번호를 입력하세요">
                </div>

                <!-- 새 비밀번호 -->
                <div class="form-group">
                    <label class="form-label">
                        새 비밀번호<span class="required">*</span>
                    </label>
                    <input type="password" name="new_password" id="newPassword" class="form-control" required placeholder="새 비밀번호를 입력하세요" minlength="6">
                    <div class="password-strength" id="strengthIndicator">
                        <div class="strength-bar">
                            <div class="strength-fill"></div>
                        </div>
                        <span id="strengthText">비밀번호 강도: -</span>
                    </div>
                </div>

                <!-- 새 비밀번호 확인 -->
                <div class="form-group">
                    <label class="form-label">
                        새 비밀번호 확인<span class="required">*</span>
                    </label>
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required placeholder="새 비밀번호를 다시 입력하세요" minlength="6">
                    <span id="matchMessage" style="font-size: 12px; margin-top: 6px; display: block;"></span>
                </div>

                <!-- 버튼 -->
                <div class="form-actions">
                    <button type="submit" name="change_password" class="btn btn-primary">비밀번호 변경</button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='/mypage/index.php'">취소</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 비밀번호 강도 체크
        document.getElementById('newPassword').addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('strengthIndicator');
            const strengthText = document.getElementById('strengthText');

            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            strengthIndicator.className = 'password-strength';
            if (strength <= 2) {
                strengthIndicator.classList.add('strength-weak');
                strengthText.textContent = '비밀번호 강도: 약함';
                strengthText.style.color = '#dc3545';
            } else if (strength <= 4) {
                strengthIndicator.classList.add('strength-medium');
                strengthText.textContent = '비밀번호 강도: 보통';
                strengthText.style.color = '#ffc107';
            } else {
                strengthIndicator.classList.add('strength-strong');
                strengthText.textContent = '비밀번호 강도: 강함';
                strengthText.style.color = '#28a745';
            }
        });

        // 비밀번호 확인 일치 체크
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            const matchMessage = document.getElementById('matchMessage');

            if (confirmPassword.length === 0) {
                matchMessage.textContent = '';
            } else if (newPassword === confirmPassword) {
                matchMessage.textContent = '✓ 비밀번호가 일치합니다.';
                matchMessage.style.color = '#28a745';
            } else {
                matchMessage.textContent = '✗ 비밀번호가 일치하지 않습니다.';
                matchMessage.style.color = '#dc3545';
            }
        });

        // 폼 제출 시 비밀번호 일치 확인
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('새 비밀번호가 일치하지 않습니다.');
                return false;
            }
        });
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
<?php
mysqli_close($db);
?>
