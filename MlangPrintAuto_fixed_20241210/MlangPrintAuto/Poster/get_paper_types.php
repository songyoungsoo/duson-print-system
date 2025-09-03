<?php
// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

$style = $_GET['style'] ?? '';

if (empty($style)) {
    error_response('필수 파라미터(style)가 누락되었습니다.');
}

// mlangprintauto_littleprint에서 해당 스타일에 사용 가능한 재질(TreeSelect) 찾기
$query = "SELECT DISTINCT TreeSelect FROM MlangPrintAuto_LittlePrint 
          WHERE style = '" . mysqli_real_escape_string($db, $style) . "'
          ORDER BY TreeSelect ASC";

$result = mysqli_query($db, $query);
$treeselect_ids = [];

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        if (!empty($row['TreeSelect'])) {
            $treeselect_ids[] = $row['TreeSelect'];
        }
    }
}

$options = [];

// TreeSelect ID들로 재질 정보 가져오기
if (!empty($treeselect_ids)) {
    $treeselect_ids_str = "'" . implode("','", array_map(function($id) use ($db) {
        return mysqli_real_escape_string($db, $id);
    }, $treeselect_ids)) . "'";
    
    $query2 = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE no IN ($treeselect_ids_str) AND Ttable='LittlePrint'
               ORDER BY no ASC";
    
    $result2 = mysqli_query($db, $query2);
    if ($result2) {
        while ($row = mysqli_fetch_array($result2)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
}

mysqli_close($db);
success_response($options);
?>