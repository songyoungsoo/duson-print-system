<?php
// 디버깅을 위해 일시적으로 에러 로그 활성화
error_log("get_paper_types.php 호출됨 - CV_no: " . ($_GET['CV_no'] ?? 'null'));

header("Content-Type: application/json; charset=utf-8");

$HomeDir = "../../";

// 데이터베이스 연결 설정 (index.php와 동일하게)
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    error_log("get_paper_types.php - DB 연결 실패");
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

mysqli_set_charset($db, "utf8");

// 파라미터 받기 (규격 번호를 받지만, 실제로는 해당 규격이 속한 구분의 종이종류를 가져와야 함)
$size_no = $_GET['CV_no'] ?? '';
if (!$size_no || !is_numeric($size_no)) {
    error_log("get_paper_types.php - 잘못된 size_no: " . $size_no);
    echo json_encode(["error" => "Invalid size_no", "received" => $size_no]);
    exit;
}

// 테이블 이름 고정 또는 인자 기반 설정 가능
$table = "MlangPrintAuto_transactionCate";

// 1단계: 규격 번호로 해당 규격이 속한 구분 번호를 찾기
$category_sql = "SELECT BigNo FROM $table WHERE no = ? LIMIT 1";
$category_stmt = $db->prepare($category_sql);
$category_stmt->bind_param("s", $size_no);
$category_stmt->execute();
$category_result = $category_stmt->get_result();

if ($category_row = $category_result->fetch_assoc()) {
    $category_no = $category_row['BigNo'];
    error_log("get_paper_types.php - 규격 {$size_no}이 속한 구분: {$category_no}");
    
    // 2단계: 구분 번호로 종이종류들 가져오기 (TreeNo = 구분 번호)
    $sql = "SELECT no, title FROM $table WHERE TreeNo = ? ORDER BY no ASC";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $category_no);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    error_log("get_paper_types.php - 규격 {$size_no}에 해당하는 구분을 찾을 수 없음");
    echo json_encode([]);
    exit;
}

$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = [
        "no" => $row['no'],
        "title" => $row['title']
    ];
}

error_log("get_paper_types.php - 결과 개수: " . count($options));

// 연결 종료
if ($db) {
    mysqli_close($db);
}

echo json_encode($options, JSON_UNESCAPED_UNICODE);
