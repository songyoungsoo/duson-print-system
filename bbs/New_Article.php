<?php
// 세션 시작 코드 제거 - 이미 상위 파일에서 세션을 시작했을 수 있음

// 데이터베이스 연결 코드 제거 - 이미 상위 파일에서 연결했을 것임

// 변수 확인
$writedate = isset($writedate) ? $writedate : date('Y-m-d H:i:s');
$BBS_ADMIN_New_Article_Time = isset($BBS_ADMIN_New_Article_Time) ? $BBS_ADMIN_New_Article_Time : 3;
$Homedir = isset($Homedir) ? $Homedir : '';

// 새 글 표시
if (( time() - strtotime($writedate) ) < 24*60*60*$BBS_ADMIN_New_Article_Time) {
    echo ("<img src='$Homedir/bbs/img/new.gif' align='absmiddle'>");
}
?>