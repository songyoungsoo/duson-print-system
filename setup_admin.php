<?php
/**
 * 관리자 계정 생성 스크립트
 *
 * 사용법: http://localhost/setup_admin.php
 * 실행 후 이 파일은 삭제하세요!
 */

require_once __DIR__ . '/db.php';

if (!$db) {
    die('데이터베이스 연결 실패');
}

mysqli_set_charset($db, 'utf8mb4');

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? 'admin';
    $password = $_POST['password'] ?? 'admin1234';
    $email = $_POST['email'] ?? 'admin@dsp114.com';
    $phone = $_POST['phone'] ?? '02-1234-5678';
    $name = $_POST['name'] ?? '관리자';

    // 비밀번호 해시
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 기존 관리자 계정 확인
    $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $message = "❌ 이미 존재하는 계정입니다. (아이디 또는 이메일 중복)";
        $message_type = "error";
    } else {
        // 관리자 계정 생성
        $insert_query = "INSERT INTO users (username, password, email, phone, name, role, created_at)
                         VALUES (?, ?, ?, ?, ?, 'admin', NOW())";
        $stmt = mysqli_prepare($db, $insert_query);
        mysqli_stmt_bind_param($stmt, "sssss", $username, $password_hash, $email, $phone, $name);

        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ 관리자 계정이 생성되었습니다!<br><br>
                        <strong>아이디:</strong> {$username}<br>
                        <strong>비밀번호:</strong> {$password}<br>
                        <strong>이메일:</strong> {$email}<br><br>
                        <a href='/admin/mlangprintauto/' style='color: #667eea; text-decoration: underline;'>관리자 페이지로 이동 →</a>";
            $message_type = "success";
        } else {
            $message = "❌ 계정 생성 실패: " . mysqli_error($db);
            $message_type = "error";
        }
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 계정 생성</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
        }

        .helper {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 관리자 계정 생성</h1>
        <p class="subtitle">테스트를 위한 관리자 계정을 생성합니다</p>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">아이디</label>
                <input type="text" id="username" name="username" value="admin" required>
                <div class="helper">로그인 시 사용할 아이디</div>
            </div>

            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="text" id="password" name="password" value="admin1234" required>
                <div class="helper">최소 8자 이상 권장</div>
            </div>

            <div class="form-group">
                <label for="name">이름</label>
                <input type="text" id="name" name="name" value="관리자" required>
            </div>

            <div class="form-group">
                <label for="email">이메일</label>
                <input type="email" id="email" name="email" value="admin@dsp114.com" required>
            </div>

            <div class="form-group">
                <label for="phone">전화번호</label>
                <input type="text" id="phone" name="phone" value="02-1234-5678" required>
            </div>

            <button type="submit">관리자 계정 생성</button>
        </form>

        <div class="warning">
            ⚠️ <strong>보안 주의:</strong><br>
            계정 생성 후 이 파일(setup_admin.php)을 반드시 삭제하세요!<br>
            운영 서버에서는 절대 사용하지 마세요.
        </div>
    </div>
</body>
</html>
