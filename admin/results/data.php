<?php
// 기본적으로 mode 값이 없을 경우 list로 리디렉션
if (!$mode) {
    echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list'>";
    exit;
}

// 데이터베이스 연결
include "../../db.php";

// 모드에 따른 처리
switch ($mode) {
    case "list":
        // 리스트 페이지
        $M123 = "..";
        
        // 관리자 필드 데이터 포함
        include "data_admin_fild.php";
        
        // 상단 페이지 포함
        include "../top.php";
        
        // 데이터 리스트 페이지 포함
        include "data_list.php";
        
        // 하단 페이지 포함
        include "../down.php";
        break;
    
    case "submit":
        // 데이터 입력 페이지
        include "admin_submit.php";
        break;
    
    case "delete":
        // 데이터 삭제 처리
        include "admin_public.php";
        break;

    case "admin_modify":
        // 관리자 수정 처리
        include "admin_public.php";
        break;

    default:
        // 정의되지 않은 모드일 경우 list로 리디렉션
        echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list'>";
        exit;
}

mysqli_close($db); // 데이터베이스 연결 종료
?>
