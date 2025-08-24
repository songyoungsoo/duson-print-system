<?php
// 임시 백업용 DB 연결 파일
// 상황에 따라 여러 DB 서버 시도

$db_configs = [
    // 설정 1: 로컬 XAMPP (기본 포트)
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'db' => 'duson1830', 'port' => 3306],
    
    // 설정 2: 로컬 XAMPP (다른 포트)  
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'db' => 'duson1830', 'port' => 3307],
    
    // 설정 3: 네트워크 서버
    ['host' => '192.168.0.250', 'user' => 'duson1830', 'password' => 'du1830', 'db' => 'duson1830', 'port' => 3306],
    
    // 설정 4: 외부 서버
    ['host' => 'sknas205.ipdisk.co.kr', 'user' => 'duson1830', 'password' => 'du1830', 'db' => 'duson1830', 'port' => 3306],
];

$db = null;
$used_config = null;

foreach ($db_configs as $config) {
    $host = $config['host'] . ':' . $config['port'];
    $db = @mysqli_connect($host, $config['user'], $config['password'], $config['db']);
    
    if ($db) {
        $used_config = $config;
        echo "데이터베이스 연결 성공: " . $config['host'] . ":" . $config['port'] . "<br>";
        mysqli_query($db, "SET NAMES 'utf8'");
        break;
    } else {
        echo "연결 실패: " . $config['host'] . ":" . $config['port'] . " - " . mysqli_connect_error() . "<br>";
    }
}

if (!$db) {
    die("모든 데이터베이스 서버 연결에 실패했습니다.");
}

// 관리자 정보
$admin_email = "dsp1830@naver.com";
$admin_name = "두손기획";
$MataTitle = "$admin_name - 인쇄, 스티커, 전단지, 리플렛, 포스터, 브로슈어, 카다로그, 패키지, 각종 판촉물,인쇄홍보물, 온라인견적 등 인쇄에서 후가공까지 일괄작업.공장직영으로 신속 제작.";
$SiteTitle = $admin_name;

echo "현재 사용 중인 DB: " . $used_config['host'] . ":" . $used_config['port'];
?>