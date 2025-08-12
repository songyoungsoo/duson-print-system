<?php
/**
 * 자동 환경 감지 데이터베이스 연결 파일
 * Linux 서버와 XAMPP 환경을 자동으로 구분하여 적절한 설정 사용
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 환경 감지
$is_localhost = (
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
    strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
);

if ($is_localhost) {
    // XAMPP 환경 설정
    $host = "localhost";
    $user = "root";
    $password = "";
    $dataname = "duson1830";
    $environment = "XAMPP";
} else {
    // Linux 서버 환경 설정
    $host = "localhost";
    $user = "duson1830";
    $password = "du1830";
    $dataname = "duson1830";
    $environment = "Linux Server";
}

// 데이터베이스 연결
$db = mysqli_connect($host, $user, $password, $dataname);

if (!$db) {
    echo "<script>console.log('[$environment] 데이터베이스 연결 실패: " . mysqli_connect_error() . "');</script>";
    
    if ($is_localhost) {
        // XAMPP 환경에서 연결 실패 시 안내
        echo "<script>console.log('XAMPP 환경에서 데이터베이스 연결에 실패했습니다.');</script>";
        echo "<script>console.log('1. XAMPP Control Panel에서 MySQL이 실행 중인지 확인하세요.');</script>";
        echo "<script>console.log('2. phpMyAdmin에서 \"$dataname\" 데이터베이스가 존재하는지 확인하세요.');</script>";
    }
    
    die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
}

// 연결 성공
mysqli_query($db, "SET NAMES 'utf8'");
echo "<script>console.log('[$environment] 데이터베이스 연결 성공');</script>";

// 공통 변수들
$admin_email = "dsp1830@naver.com";
$admin_name = "두손기획";
$MataTitle = "$admin_name - 인쇄, 스티커, 전단지, 리플렛, 포스터, 브로슈어, 카다로그, 패키지, 각종 판촉물,인쇄홍보물, 온라인견적 등 인쇄에서 후가공까지 일괄작업.공장직영으로 신속 제작.";
$SiteTitle = $admin_name;
$admin_Tname = "Mlang";

if ($is_localhost) {
    $admin_url = "http://localhost";
    $home_cookie_url = ".localhost";
} else {
    $admin_url = "http://sknas205.ipdisk.co.kr:8000";
    $home_cookie_url = ".dsp114.com";
}

$Homedir = $admin_url;
$admin_table = "member";
$page_big_table = "page_menu_big";
$page_table = "page";

$WebSoftCopyright = "
<p align=center>
</p>
";
?>