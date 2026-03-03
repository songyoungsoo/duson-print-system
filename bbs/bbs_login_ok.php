<?php
/**
 * BBS 게시판 비밀번호 인증 처리
 * - 최고관리자 / 보드관리자 / 글 등록자 3단계 검증
 * - prepared statements 적용 (SQL 인젝션 방지)
 * - users 테이블 우선 + member 폴백 (bcrypt 지원)
 * 경로: bbs/bbs_login_ok.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 변수 초기화
$bbs_pass = isset($_POST['bbs_pass']) ? $_POST['bbs_pass'] : '';
$table = isset($_POST['table']) ? $_POST['table'] : '';
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$no = isset($_POST['no']) ? $_POST['no'] : '';
$GH_url = isset($_POST['GH_url']) ? $_POST['GH_url'] : '';
$DbDir = isset($DbDir) ? $DbDir : "..";

/**
 * BBS 비밀번호 검증 (bcrypt + 평문 호환)
 */
function verify_bbs_password($input, $stored) {
    if (empty($stored) || empty($input)) return false;
    // bcrypt 해시인 경우
    if (strlen($stored) === 60 && strpos($stored, '$2y$') === 0) {
        return password_verify($input, $stored);
    }
    // 평문 비교 (레거시)
    return ($input === $stored);
}

if ($bbs_pass) {

    // 테이블명 안전성 검증 (영문/숫자/언더스코어만 허용)
    if (!empty($table) && !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        echo "<script>alert('잘못된 요청입니다.'); history.go(-1);</script>";
        exit;
    }

    include "$DbDir/db.php";

    // 1. 최고관리자 패스워드 (users 테이블 우선, member 폴백)
    $PP_BBS_login1 = '';
    $admin_stmt = mysqli_prepare($db, "SELECT password FROM users WHERE id = 1");
    if ($admin_stmt) {
        mysqli_stmt_execute($admin_stmt);
        $admin_result = mysqli_stmt_get_result($admin_stmt);
        $admin_row = mysqli_fetch_assoc($admin_result);
        if ($admin_row) {
            $PP_BBS_login1 = $admin_row['password'];
        }
        mysqli_stmt_close($admin_stmt);
    }
    // users에 없으면 member 폴백
    if (empty($PP_BBS_login1)) {
        $admin_stmt2 = mysqli_prepare($db, "SELECT pass FROM member WHERE no = 1");
        if ($admin_stmt2) {
            mysqli_stmt_execute($admin_stmt2);
            $admin_result2 = mysqli_stmt_get_result($admin_stmt2);
            $admin_row2 = mysqli_fetch_assoc($admin_result2);
            if ($admin_row2) {
                $PP_BBS_login1 = $admin_row2['pass'];
            }
            mysqli_stmt_close($admin_stmt2);
        }
    }

    // 2. 보드관리자 패스워드 (prepared statement)
    $PP_BBS_login2 = '';
    if (!empty($table)) {
        $bbs_admin_stmt = mysqli_prepare($db, "SELECT pass FROM mlang_bbs_admin WHERE id = ?");
        if ($bbs_admin_stmt) {
            mysqli_stmt_bind_param($bbs_admin_stmt, "s", $table);
            mysqli_stmt_execute($bbs_admin_stmt);
            $bbs_admin_result = mysqli_stmt_get_result($bbs_admin_stmt);
            $bbs_admin_row = mysqli_fetch_assoc($bbs_admin_result);
            if ($bbs_admin_row) {
                $PP_BBS_login2 = $bbs_admin_row['pass'];
            }
            mysqli_stmt_close($bbs_admin_stmt);
        }
    }

    // 3. 글 등록자 패스워드 (테이블명은 위에서 regex 검증 완료)
    $PP_BBS_login3 = '';
    if (!empty($table) && !empty($no)) {
        $bbs_table_name = "mlang_{$table}_bbs";
        $bbs_write_stmt = mysqli_prepare($db, "SELECT Mlang_bbs_pass FROM `$bbs_table_name` WHERE Mlang_bbs_no = ?");
        if ($bbs_write_stmt) {
            mysqli_stmt_bind_param($bbs_write_stmt, "s", $no);
            mysqli_stmt_execute($bbs_write_stmt);
            $bbs_write_result = mysqli_stmt_get_result($bbs_write_stmt);
            $bbs_write_row = mysqli_fetch_assoc($bbs_write_result);
            if ($bbs_write_row) {
                $PP_BBS_login3 = $bbs_write_row['Mlang_bbs_pass'];
            }
            mysqli_stmt_close($bbs_write_stmt);
        }
    }

    mysqli_close($db);

    // 리다이렉트 URL 안전성 검증 (상대경로만 허용)
    $safe_url = $GH_url;
    if (empty($safe_url) || strpos($safe_url, '//') === 0 || preg_match('/^https?:\/\//i', $safe_url)) {
        $safe_url = '/bbs/';
    }
    $safe_url = htmlspecialchars($safe_url, ENT_QUOTES, 'UTF-8');

    // 패스워드 검증 (최고관리자 → 보드관리자 → 글쓴사람)
    if (verify_bbs_password($bbs_pass, $PP_BBS_login1)) {
        setcookie("bbs_login", $bbs_pass, 0, "/");
        echo "<html><meta http-equiv='Refresh' content='0; URL=$safe_url'></html>";
    } elseif (verify_bbs_password($bbs_pass, $PP_BBS_login2)) {
        setcookie("bbs_login", $bbs_pass, 0, "/");
        echo "<html><meta http-equiv='Refresh' content='0; URL=$safe_url'></html>";
    } elseif (verify_bbs_password($bbs_pass, $PP_BBS_login3)) {
        setcookie("bbs_login", $bbs_pass, 0, "/");
        echo "<html><meta http-equiv='Refresh' content='0; URL=$safe_url'></html>";
    } else {
        echo "<script>alert('입력하신 비밀번호로는 이용 권한이 없습니다.'); history.go(-1);</script>";
        exit;
    }

} else {
    echo "<script>alert('현 페이지를 이용하시려면 비밀번호를 입력해 주셔야 합니다.'); history.go(-1);</script>";
    exit;
}
?>
