<?php
/**
 * 환경별 자동 감지 데이터베이스 연결 시스템
 * 로컬(XAMPP)과 운영(웹호스팅) 환경을 자동으로 구분하여 연결
 */

// 환경 설정 파일 로드
require_once __DIR__ . '/config.env.php';

// 환경별 데이터베이스 설정 가져오기
$db_config = get_db_config();

// 데이터베이스 연결 변수 설정
$host = $db_config['host'];
$user = $db_config['user'];
$password = $db_config['password'];
$dataname = $db_config['database'];

// 데이터베이스 연결
$db = mysqli_connect($host, $user, $password, $dataname);

if (!$db) {
    $error_msg = "데이터베이스 연결에 실패했습니다: " . mysqli_connect_error();

    // 로컬 환경에서는 상세한 오류 정보 표시
    if (is_local_environment()) {
        $error_msg .= "\n환경: " . get_current_environment();
        $error_msg .= "\n호스트: $host";
        $error_msg .= "\n사용자: $user";
        $error_msg .= "\n데이터베이스: $dataname";
    }

    die($error_msg);
}

// 문자셋 설정
mysqli_set_charset($db, $db_config['charset']);

// 호환성을 위한 별칭 변수
$conn = $db;

// 디버그 정보 (로컬 환경에서만)
if (is_local_environment() && isset($_GET['debug_db'])) {
    echo "<div style='background: #f0f8ff; padding: 10px; border: 1px solid #0066cc; margin: 10px 0;'>";
    echo "<strong>🔧 데이터베이스 연결 정보 (로컬 환경)</strong><br>";
    echo "환경: " . get_current_environment() . "<br>";
    echo "호스트: $host<br>";
    echo "사용자: $user<br>";
    echo "데이터베이스: $dataname<br>";
    echo "문자셋: " . $db_config['charset'];
    echo "</div>";
}

// 테이블명 자동 매핑 시스템 - 필요할 때만 로드
if (!function_exists('load_table_mapper')) {
    function load_table_mapper() {
        if (!function_exists('map_table_names')) {
            include_once(__DIR__ . "/includes/table_mapper.php");
        }
    }
}

if (!function_exists('safe_mysqli_query')) {
    // 조건부 래퍼 함수들 - 기본적으로는 일반 mysqli 함수 사용
    function safe_mysqli_query($connection, $query) {
        // 대문자 테이블명이 있을 때만 매핑 적용
        if (strpos($query, 'Member') !== false ||
            strpos($query, 'Shop_Temp') !== false ||
            strpos($query, 'mlangorder_printauto') !== false ||
            strpos($query, 'mlangprintauto_') !== false) {

            load_table_mapper();
            if (function_exists('map_table_names')) {
                $query = map_table_names($query);
            }
        }
        return mysqli_query($connection, $query);
    }
}

if (!function_exists('safe_mysqli_prepare')) {
    function safe_mysqli_prepare($connection, $query) {
        // 대문자 테이블명이 있을 때만 매핑 적용
        if (strpos($query, 'Member') !== false ||
            strpos($query, 'Shop_Temp') !== false ||
            strpos($query, 'mlangorder_printauto') !== false ||
            strpos($query, 'mlangprintauto_') !== false) {

            load_table_mapper();
            if (function_exists('map_table_names')) {
                $query = map_table_names($query);
            }
        }
        return mysqli_prepare($connection, $query);
    }
}

$admin_email = "dsp1830@naver.com";
$admin_name = "두손기획";
$MataTitle = "$admin_name - 인쇄, 스티커, 전단지, 리플렛, 포스터, 브로슈어, 카다로그, 패키지, 각종 판촉물,인쇄홍보물, 온라인견적 등 인쇄에서 후가공까지 일괄작업.공장직영으로 신속 제작.";
$SiteTitle = $admin_name;
$admin_Tname = "Mlang";

// 환경별 URL 자동 설정
$current_env = get_current_environment();
if ($current_env === 'local') {
    $admin_url = "http://localhost";
    $home_cookie_url = "localhost"; // 로컬은 점 제거
} else {
    // 프로덕션: 현재 접속 도메인 자동 감지
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'dsp1830.shop';
    $admin_url = $protocol . $host;

    // 쿠키 도메인: 최종 목표 도메인 사용 (dsp1830.shop에서도 작동하도록)
    if (strpos($host, 'dsp1830.shop') !== false) {
        $home_cookie_url = ".dsp1830.shop";
    } else {
        $home_cookie_url = ".dsp1830.shop";
    }
}

$Homedir = $admin_url;
$admin_table = "users"; // 관리자 테이블
$page_big_table = "page_menu_big"; // 주메뉴 테이블
$page_table = "page"; // 페이지 내용 테이블

