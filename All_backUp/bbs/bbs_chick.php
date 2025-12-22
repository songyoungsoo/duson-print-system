<?php
session_start();

// 기본 디렉토리 경로 설정
$DbDir = isset($DbDir) ? $DbDir : "..";
$BbsDir = isset($BbsDir) ? $BbsDir : ".";

// 데이터베이스 연결
include "$DbDir/db.php";

try {
    // 관리자 비밀번호 조회
    $query = "SELECT pass FROM member WHERE no = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $admin_no);
    $admin_no = 1;
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_array($result)) {
        $bbsadminpasswd = $row['pass'];
    } else {
        throw new Exception("관리자 정보를 찾을 수 없습니다.");
    }
    
    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    error_log("Error in bbs_chick.php: " . $e->getMessage());
    $bbsadminpasswd = '';
}

// 로그인 체크
if (!isset($_COOKIE['bbs_login'])) {
    // 메시지 출력
    $TT_msg = isset($TT_msg) ? $TT_msg : "로그인이 필요합니다.";
    echo("<p align='center'><br><br><br><b>" . htmlspecialchars($TT_msg) . "</b></p>");
    
    // 로그인 페이지 포함
    include "$BbsDir/bbs_login.php";
    
    // 푸터 출력
    if (isset($BBS_ADMIN_footer)) {
        echo htmlspecialchars($BBS_ADMIN_footer);
    }
    exit;
}
if($BBS_ADMIN_footer_include){include "$BBS_ADMIN_footer_include ";}

exit;

}


///////////////////////////////////////////////////////////////////////////////////////////////////////
if($HTTP_COOKIE_VARS['bbs_login']=="$bbsadminpasswd"){ 
}else if($HTTP_COOKIE_VARS['bbs_login']=="$BBS_ADMIN_pass"){
}else if($HTTP_COOKIE_VARS['bbs_login']=="$BbsViewmlang_bbs_pass"){
}else{
	
echo("<p align=center><BR><BR><BR><b>$TT_msg</b></p>");
	
include "$BbsDir/bbs_login.php"; 

if($BBS_ADMIN_footer){echo("$BBS_ADMIN_footer");} 
if($BBS_ADMIN_footer_include){include "$BBS_ADMIN_footer_include ";}

exit;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
?>