<?php
// 출력 전에 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

// 기본 디렉토리 경로 설정
$db_dir = isset($db_dir) ? $db_dir : "..";

// db.php에서 데이터베이스 연결 정보 가져오기
include_once("$db_dir/db.php");

if($mode=="view") { $TT3r_ok="$BBS_ADMIN_view_select"; }
if($mode=="write") { $TT3r_ok="$BBS_ADMIN_write_select"; }

if($TT3r_ok=="guest"){}else{
    include_once($_SERVER['DOCUMENT_ROOT'] ."/member/member_fild_id.php");

    if($BBS_ADMIN_BBS_Level < $MlangMember_level ){
        echo ("<script language=javascript>
        window.alert('현 페이지는 레벨: $BBS_ADMIN_BBS_Level 이상 이용하게끔 설정되어져 있습니다.\\n\\n$WebtingMemberLogin_id 회원님의 현재 레벨은 $MlangMember_level 입니다..');
        history.go(-1);
        </script>
        ");
        exit;
    }
}
?>