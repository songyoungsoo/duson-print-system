<?php
// 데이터베이스 연결
include "../../db.php";

// 데이터베이스 연결 확인
if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

// SQL 문 준비 (보안 강화: Prepared Statement 사용)
$query = "SELECT * FROM Mlnag_Results_Admin WHERE id = ?";
$stmt = mysqli_prepare($db, $query);

// SQL 문에 파라미터 바인딩
mysqli_stmt_bind_param($stmt, "s", $id);

// SQL 문 실행
mysqli_stmt_execute($stmt);

// 결과 가져오기
$result = mysqli_stmt_get_result($stmt);

// 행이 있는지 확인
if (mysqli_num_rows($result) > 0) {
    // 결과 처리
    while ($row = mysqli_fetch_assoc($result)) {
        $DataAdminFild_item = $row['item'];
        $DataAdminFild_title = $row['title'];
        $DataAdminFild_id = $row['id'];
        $DataAdminFild_celect = $row['celect'];
        $DataAdminFild_date = $row['date'];
    }
} else {
    echo ("<script language='javascript'>
    window.alert('$id - 테이블에서 조회된 데이터가 없습니다.');
    history.go(-1);
    </script>");
    exit;
}

// 데이터베이스 연결 닫기
mysqli_close($db);
?>
