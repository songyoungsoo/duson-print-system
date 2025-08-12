<?php
declare(strict_types=1);

// 변수 초기화 (방지용)
$ACate = $_GET['ACate'] ?? null;
$ATreeNo = $_GET['ATreeNo'] ?? null;
$Ttable = $_GET['Ttable'] ?? null;
$offset = $_GET['offset'] ?? 0;
$Cate = $_GET['Cate'] ?? null;
$search = $_GET['search'] ?? null;
$TreeSelect = $_GET['TreeSelect'] ?? null;
$no = isset($_GET['no']) ? (int)$_GET['no'] : 0; // ✅ 추가!

// 예시로 설정된 값들 (정확한 값은 기존 코드에 맞게 조정 필요)
$View_TtableB = $Ttable;
$View_TtableC = $Ttable; // 이건 실제 테이블 한글명이라면 따로 정의 필요
$PageCode = "Category";
// $DF_Tatle_1 = $_POST['DF_Tatle_1'] ?? null;
// $DF_Tatle_2 = $_POST['DF_Tatle_2'] ?? null;
// $DF_Tatle_3 = $_POST['DF_Tatle_3'] ?? null;
$DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
$DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
$DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';

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
