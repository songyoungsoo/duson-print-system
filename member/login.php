<?php
// 에러 표시 설정
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

// 세션 수명 8시간 통일
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 28800);
    session_set_cookie_params([
        'lifetime' => 28800,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
$session_id = session_id();

// 통합 로그인 상태 확인 (신규 시스템 우선)
$is_logged_in = false;

// 1. 신규 시스템 확인 (user_id)
if (isset($_SESSION['user_id'])) {
    $is_logged_in = true;
}
// 2. 구 시스템 확인 (id_login_ok) - 신규 시스템 로그인이 없을 때만
elseif (isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok'])) {
    unset($_SESSION['id_login_ok']);
    if (isset($_COOKIE['id_login_ok'])) {
        setcookie('id_login_ok', '', time() - 3600, '/');
    }
}

if ($is_logged_in) {
    echo "<script>
          if (confirm('이미 로그인 상태입니다.\n\n로그아웃 하시겠습니까?')) {
              location.href = '/auth/logout.php';
          } else {
              location.href = '/';
          }
          </script>";
    exit;
}

// 로그인 상태가 아닌 경우에만 로그인 화면을 표시
include __DIR__ . "/../db.php";
include_once __DIR__ . '/../includes/csrf.php';
csrf_token();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - 두손기획인쇄</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700;900&display=swap">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #000033;
            font-family: 'Noto Sans KR', sans-serif;
            font-size: 13px;
            color: #333;
        }
        .login-box {
            width: 320px;
            background: #0000ff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.25);
        }
        .login-header {
            background: #2846ff;
            padding: 22px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .login-header img {
            width: 48px; height: 48px;
            border-radius: 50%;
            object-fit: contain;
            background: #fff;
            border: 2px solid rgba(255,255,255,0.4);
        }
        .login-header .name {
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.15);
        }
        .login-header .sub {
            font-size: 10px;
            color: rgba(255,255,255,0.75);
        }
        .login-body {
            padding: 16px 18px 18px;
            background: #fff;
        }
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 3px;
            margin-bottom: 8px;
            color: #fff;
        }
        .badge-blue { background: #0ea5e9; }
        .badge-green { background: #03C75A; }
        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 3px;
        }
        .field-input {
            width: 100%;
            padding: 8px 10px;
            border: 1.5px solid #d0d5dd;
            border-radius: 5px;
            font-size: 13px;
            background: #f0f7ff;
            margin-bottom: 8px;
            box-sizing: border-box;
            font-family: inherit;
        }
        .field-input:focus {
            outline: none;
            border-color: #38bdf8;
            background: #fff;
        }
        .check-row {
            margin-bottom: 10px;
        }
        .check-row label {
            font-size: 12px;
            color: #666;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .check-row input[type="checkbox"] {
            width: 14px; height: 14px;
            accent-color: #0ea5e9;
        }
        .btn-login {
            width: 100%;
            padding: 9px;
            background: #2846ff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #38bdf8, #7dd3fc);
        }
        .links {
            text-align: center;
            margin: 10px 0 14px;
            font-size: 12px;
            color: #999;
        }
        .links a {
            color: #555;
            text-decoration: none;
        }
        .links a:hover { text-decoration: underline; }
        .links span { margin: 0 6px; }
        .naver-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .naver-desc {
            flex: 1;
            font-size: 11px;
            color: #e65100;
            line-height: 1.4;
        }
        .btn-naver {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            background: #03C75A;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-naver b { font-size: 15px; }
        .warning {
            font-size: 11px;
            color: #c53030;
            line-height: 1.5;
            padding: 8px;
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-header">
        <img src="/ImgFolder/icon-192x192.png" alt="두손기획인쇄">
        <div class="name">두손기획인쇄</div>
        <div class="sub">www.dsp114.com · 1688-2384</div>
    </div>

    <div class="login-body">

    <form name="FrmUserInfo" method="post" action="login_unified.php" onsubmit="return MemberCheckField();">
        <?php csrf_field(); ?>
        <input type="hidden" name="mode" value="member_login">
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'] ?? '/'); ?>">

        <span class="badge badge-blue">개인 / 사업자 회원</span>

        <label class="field-label">아이디</label>
        <input type="text" name="id" class="field-input" placeholder="아이디" maxlength="20" required>

        <label class="field-label">비밀번호</label>
        <input type="password" name="pass" class="field-input" placeholder="비밀번호" maxlength="12" required>

        <div class="check-row">
            <label><input type="checkbox" name="remember_me" value="1"> 자동 로그인</label>
        </div>

        <button type="submit" class="btn-login">로그인</button>

        <div class="links">
            <a href="join.php">회원가입</a>
            <span>|</span>
            <a href="password_reset_simple.php">비밀번호 찾기</a>
        </div>
    </form>

    <span class="badge badge-green">SNS 회원 가입자 전용</span>

    <div class="naver-row">
        <div class="naver-desc">네이버 계정으로 간편하게<br>로그인/회원가입 하세요.</div>
        <a href="/member/naver_login.php?redirect=/" class="btn-naver">
            <b>N</b> 네이버 로그인
        </a>
    </div>

    <div class="warning">
        주의 : 기존 개인 / 사업자 회원은 중복가입 하지 마시고,<br>상단 개인 / 사업자 회원 로그인을 이용 바랍니다.
    </div>
    </div><!-- login-body -->

</div>

<script>
function MemberCheckField() {
    var form = document.FrmUserInfo;
    if (!form.id.value) { alert('아이디를 입력해주세요.'); form.id.focus(); return false; }
    if (!form.pass.value) { alert('비밀번호를 입력해주세요.'); form.pass.focus(); return false; }
    return true;
}
</script>
</body>
</html>
