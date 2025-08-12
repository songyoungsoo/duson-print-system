<?php
// XAMPP 환경용 데이터베이스 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// XAMPP 기본 설정
$host = "localhost";
$user = "duson1830";  // XAMPP 기본 사용자
$password = "du1830";  // XAMPP 기본 비밀번호 (빈 문자열)
$dataname = "duson1830";  // 데이터베이스명은 동일하게 유지

// 데이터베이스 연결 시도
$db = mysqli_connect($host, $user, $password, $dataname);

if (!$db) {
    // 연결 실패 시 상세 에러 정보 출력
    echo "<script>console.log('데이터베이스 연결 실패');</script>";
    echo "<script>console.log('에러: " . mysqli_connect_error() . "');</script>";
    echo "<script>console.log('호스트: $host, 사용자: $user, 데이터베이스: $dataname');</script>";
    
    // 데이터베이스가 없을 경우를 대비해 기본 연결 시도
    $db_temp = mysqli_connect($host, $user, $password);
    if ($db_temp) {
        echo "<script>console.log('MySQL 서버 연결 성공, 데이터베이스 '$dataname' 확인 중...');</script>";
        
        // 데이터베이스 존재 여부 확인
        $db_check = mysqli_query($db_temp, "SHOW DATABASES LIKE '$dataname'");
        if (mysqli_num_rows($db_check) == 0) {
            echo "<script>console.log('데이터베이스 '$dataname'가 존재하지 않습니다.');</script>";
            echo "<script>alert('데이터베이스 \"$dataname\"가 존재하지 않습니다.\\nXAMPP phpMyAdmin에서 데이터베이스를 생성해주세요.');</script>";
        }
        mysqli_close($db_temp);
    }
    die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
}

// 연결 성공 시
mysqli_query($db, "SET NAMES 'utf8'");
echo "<script>console.log('데이터베이스 연결 성공!');</script>";

$admin_email = "dsp1830@naver.com";
$admin_name = "두손기획";
$MataTitle = "$admin_name - 인쇄, 스티커, 전단지, 리플렛, 포스터, 브로슈어, 카다로그, 패키지, 각종 판촉물,인쇄홍보물, 온라인견적 등 인쇄에서 후가공까지 일괄작업.공장직영으로 신속 제작.";
$SiteTitle = $admin_name;
$admin_Tname = "Mlang";
$admin_url = "http://localhost";
$Homedir = $admin_url;
$admin_table = "member";
$page_big_table = "page_menu_big";
$page_table = "page";
$home_cookie_url = ".localhost";

$WebSoftCopyright = "
<p align=center>
</p>
";
?>