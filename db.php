<?php
// 간단하고 안정적인 데이터베이스 연결 파일 (원본 방식 복원)
$host = "localhost";
$user = "dsp1830"; 
$password = "ds701018";
$dataname = "dsp1830";

$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
}
mysqli_set_charset($db, "utf8mb4");

// 테이블명 자동 매핑 시스템 - 필요할 때만 로드
if (!function_exists('map_table_names')) {
    function load_table_mapper() {
        if (!function_exists('map_table_names')) {
            include_once(__DIR__ . "/includes/table_mapper.php");
        }
    }
    
    // 조건부 래퍼 함수들 - 기본적으로는 일반 mysqli 함수 사용
    function safe_mysqli_query($connection, $query) {
        // 대문자 테이블명이 있을 때만 매핑 적용
        if (preg_match('/\b(Member|Shop_Temp|MlangOrder_PrintAuto|MlangPrintAuto_[A-Z])\b/', $query)) {
            load_table_mapper();
            $query = map_table_names($query);
        }
        return mysqli_query($connection, $query);
    }
    
    function safe_mysqli_prepare($connection, $query) {
        // 대문자 테이블명이 있을 때만 매핑 적용
        if (preg_match('/\b(Member|Shop_Temp|MlangOrder_PrintAuto|MlangPrintAuto_[A-Z])\b/', $query)) {
            load_table_mapper();
            $query = map_table_names($query);
        }
        return mysqli_prepare($connection, $query);
    }
}

$admin_email = "dsp1830@naver.com";
$admin_name = "두손기획";
$MataTitle = "$admin_name - 인쇄, 스티커, 전단지, 리플렛, 포스터, 브로슈어, 카다로그, 패키지, 각종 판촉물,인쇄홍보물, 온라인견적 등 인쇄에서 후가공까지 일괄작업.공장직영으로 신속 제작.";
$SiteTitle = $admin_name;
$admin_Tname = "Mlang";
$admin_url = "http://localhost";
$Homedir = $admin_url;
$admin_table = "users"; // 관리자 테이블
$page_big_table = "page_menu_big"; // 주메뉴 테이블
$page_table = "page"; // 페이지 내용 테이블
$home_cookie_url = ".dsp114.com"; // 홈 쿠키 url

