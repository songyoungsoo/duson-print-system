<?php
// 변수 초기화
$View_Ttable = '';
$View_style = '';
$View_BigNo = '';
$View_title = '';
$View_TreeNo = '';

// 데이터베이스 연결 및 데이터 조회
if ($no > 0) {
    // ConDb.php에서 $GGTABLE 변수가 이미 정의되어 있어야 함
    // $GGTABLE = "MlangPrintAuto_transactionCate"; // ConDb.php에서 정의됨
    
    // 카테고리 데이터 조회
    $query = "SELECT * FROM $GGTABLE WHERE no = ?";
    $stmt = mysqli_prepare($db, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $View_Ttable = $row['Ttable'] ?? '';
            $View_style = $row['style'] ?? '';
            $View_BigNo = $row['BigNo'] ?? '';
            $View_title = $row['title'] ?? '';
            $View_TreeNo = $row['TreeNo'] ?? '';
            
            // 디버깅을 위한 로그 (나중에 제거 가능)
            // error_log("CateView.php - 로드된 데이터: no=$no, title=$View_title, BigNo=$View_BigNo");
        } else {
            // 데이터가 없는 경우
            error_log("CateView.php - 데이터를 찾을 수 없음: no=$no, table=$GGTABLE");
            $View_title = '';
            $View_BigNo = '';
            $View_TreeNo = '';
        }
        
        mysqli_stmt_close($stmt);
    } else {
        // 쿼리 준비 실패
        error_log("CateView.php - 쿼리 준비 실패: " . mysqli_error($db));
        $View_title = '';
        $View_BigNo = '';
        $View_TreeNo = '';
    }
} else {
    error_log("CateView.php - 잘못된 no 값: $no");
}
?>
