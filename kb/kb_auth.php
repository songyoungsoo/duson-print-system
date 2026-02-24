<?php
define('KB_PASSWORD', 'duson2026!kb');

function kb_is_local() {
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
}

function kb_check_auth() {
    if (kb_is_local()) return;

    if (isset($_POST['kb_password'])) {
        if ($_POST['kb_password'] === KB_PASSWORD) {
            $_SESSION['kb_auth'] = true;
            if (basename($_SERVER['SCRIPT_NAME']) !== 'api.php') {
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }
            return;
        }
    }

    if (!empty($_SESSION['kb_auth'])) return;

    if (basename($_SERVER['SCRIPT_NAME']) === 'api.php') {
        http_response_code(401);
        exit(json_encode(['error' => 'auth required']));
    }

    kb_show_login();
    exit;
}

function kb_show_login() {
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KB Login</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center}
.login{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:32px;width:340px;text-align:center}
.login h1{font-size:18px;font-weight:700;color:#f8fafc;margin-bottom:4px}
.login h1 span{color:#6366f1}
.login p{font-size:12px;color:#64748b;margin-bottom:20px}
.login input{width:100%;padding:12px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#f1f5f9;font-size:14px;outline:none;text-align:center;margin-bottom:12px}
.login input:focus{border-color:#6366f1}
.login button{width:100%;padding:10px;background:#6366f1;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer}
.login button:hover{background:#4f46e5}
</style>
</head>
<body>
<form class="login" method="POST" autocomplete="on">
    <h1><span>KB</span> Knowledge Vault</h1>
    <p>비밀번호를 입력하세요</p>
    <input type="text" name="kb_user" value="kb" autocomplete="username" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden" tabindex="-1" aria-hidden="true">
    <input type="password" name="kb_password" placeholder="비밀번호" autofocus autocomplete="current-password">
    <button type="submit">로그인</button>
</form>
</body>
</html>
<?php
}
