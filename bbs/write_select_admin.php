<?php
// 변수 초기화 (Notice 에러 방지)
$DbDir = isset($DbDir) ? $DbDir : '..';
$WebtingMemberLogin_id = isset($WebtingMemberLogin_id) ? $WebtingMemberLogin_id : '';
$table = isset($table) ? $table : '';

include "$DbDir/db.php";

// 관리자 정보 조회
$result = mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1");
$row = mysqli_fetch_array($result);
$BBSAdminloginKK = $row['id'];

// 관리자 로그인 체크 (기존 방식)
$isMainSiteAdmin = ($WebtingMemberLogin_id == $BBSAdminloginKK);

// BBS 쿠키 체크 (관리자 비밀번호로 로그인한 경우)
$isBBSAdmin = false;
if (isset($_COOKIE['bbs_login'])) {
    $bbs_login = $_COOKIE['bbs_login'];
    
    // 최고관리자 비밀번호 체크
    $admin_pass = $row['pass']; // member 테이블의 관리자 비밀번호
    
    // 게시판 관리자 비밀번호 체크
    $bbs_admin_result = mysqli_query($db, "SELECT pass FROM mlang_bbs_admin WHERE id='$table'");
    $bbs_admin_row = mysqli_fetch_array($bbs_admin_result);
    $bbs_admin_pass = $bbs_admin_row ? $bbs_admin_row['pass'] : '';
    
    if ($bbs_login == $admin_pass || $bbs_login == $bbs_admin_pass) {
        $isBBSAdmin = true;
    }
}

// 포트폴리오 게시판의 경우 BBS 로그인 페이지로 리다이렉트
if (!$isMainSiteAdmin && !$isBBSAdmin) {
    if ($table == 'portfolio') {
        // 포트폴리오는 관리자 전용이므로 BBS 로그인 페이지로 리다이렉트
        $current_url = $_SERVER['REQUEST_URI'];
        echo "<script language=javascript>
        window.location.href = 'bbs_login.php?mode=write&table=$table&GH_url=" . urlencode($current_url) . "';
        </script>";
        exit;
    } else {
        // 다른 게시판은 기존 경고 메시지
        echo "<script language=javascript>
        window.alert('현페이지는 관리자만 이용할수 있게금 설정되어져 있습니다.');
        history.go(-1);
        </script>";
        exit;
    }
}

mysqli_close($db);
?>