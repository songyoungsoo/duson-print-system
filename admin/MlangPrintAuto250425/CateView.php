<?php
// declare(strict_types=1);

// 변수 초기화 (방지용)
// $ACate = $_GET['ACate'] ?? null;
// $ATreeNo = $_GET['ATreeNo'] ?? null;
// $Ttable = $_GET['Ttable'] ?? null;
// $offset = $_GET['offset'] ?? 0;
// $Cate = $_GET['Cate'] ?? null;
// $search = $_GET['search'] ?? null;
// $TreeSelect = $_GET['TreeSelect'] ?? null;
// $no = isset($_GET['no']) ? (int)$_GET['no'] : 0; // ✅ 추가!

// 예시로 설정된 값들 (정확한 값은 기존 코드에 맞게 조정 필요)
// $View_TtableB = $Ttable;
// $View_TtableC = $Ttable; // 이건 실제 테이블 한글명이라면 따로 정의 필요
// $PageCode = "Category";
// $DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
// $DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
// $DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';

declare(strict_types=1);
// 📌 GET 값 초기화
$code            = $_GET['code']         ?? $_POST['code']         ?? '';
$ACate           = $_GET['ACate']        ?? $_POST['ACate']        ?? '';
$ATreeNo         = $_GET['ATreeNo']      ?? $_POST['ATreeNo']      ?? '';
$TreeSelect      = $_GET['TreeSelect']   ?? $_POST['TreeSelect']   ?? '';
$mode            = $_GET['mode']         ?? $_POST['mode']         ?? '';
$Cate            = $_GET['Cate']         ?? $_POST['Cate']         ?? '';
$PageCode        = $_GET['PageCode']     ?? $_POST['PageCode']     ?? '';
$Ttable          = $_GET['Ttable']       ?? $_POST['Ttable']       ?? '';
$TIO_CODE        = $_GET['TIO_CODE']     ?? $_POST['TIO_CODE']     ?? '';
$Ttable          = $Ttable ?: $TIO_CODE; // fallback 설정
$search          = $_GET['search']       ?? $_POST['search']       ?? '';
$RadOne          = $_GET['RadOne']       ?? $_POST['RadOne']       ?? '';
$myListTreeSelect= $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList          = $_GET['myList']       ?? $_POST['myList']       ?? '';
$offset          = isset($_GET['offset']) ? (int)$_GET['offset'] : (isset($_POST['offset']) ? (int)$_POST['offset'] : 0);
$no              = isset($_GET['no']) ? (int)$_GET['no'] : (isset($_POST['no']) ? (int)$_POST['no'] : 0);
$PHP_SELF        = htmlspecialchars($_SERVER['PHP_SELF'] ?? '');


// function getTtableTitle($code) {
//     $titles = [
//         "inserted" => "전단지",
//         "NameCard" => "명함",
//         "cadarok" => "리플렛",
//         "msticker" => "스티커",
//         "MerchandiseBond" => "상품권",
//         "envelope" => "봉투",
//         "NcrFlambeau" => "양식지",
//         "LittlePrint" => "소량인쇄",
//         "cadarokTwo" => "카다로그",
//         "hakwon" => "학원",
//         "food" => "음식",
//         "company" => "기업체",
//         "cloth" => "의류",
//         "commerce" => "상업",
//         "church" => "교회",
//         "nonprofit" => "비영리",
//         "etc" => "기타"
//     ];
//     return $titles[$code] ?? $code;
// }

include "../title.php";
include "../../MlangPrintAuto/ConDb.php";


$View_TtableB = $Ttable;

$View_TtableC = getTtableTitle($Ttable); // 이건 실제 테이블 한글명이라면 따로 정의 필요
$PageCode = "Category";
$DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
$DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
$DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';



// 타이틀 변수 초기화
$DF_Tatle_1 = $DF_Tatle_2 = $DF_Tatle_3 = '';

if (isset($TtableTitles[$Ttable])) {
    $DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
    $DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
    $DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';
}








// 데이터베이스 연결
include "../../db.php";
$mysqli = new mysqli($host, $user, $password, $dataname);

if ($mysqli->connect_error) {
    die("데이터베이스 연결 실패: " . $mysqli->connect_error);
}

// 준비된 문장을 사용해 레코드 조회
$stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE no=?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $row = $result->fetch_assoc();
    if ($row) {
        $View_Ttable = htmlspecialchars($row['Ttable'] ?? '', ENT_QUOTES, 'UTF-8');
        $View_style  = htmlspecialchars($row['style'] ?? '', ENT_QUOTES, 'UTF-8');
        $View_BigNo  = htmlspecialchars($row['BigNo'] ?? '', ENT_QUOTES, 'UTF-8');
        $View_title  = htmlspecialchars($row['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $View_TreeNo = htmlspecialchars($row['TreeNo'] ?? '', ENT_QUOTES, 'UTF-8');
    } else {
        die("해당하는 레코드가 없습니다.");
    }
} else {
    die("데이터베이스 쿼리 실행 중 오류가 발생했습니다: " . $mysqli->error);
}

$mysqli->close();
?>
