<?php
// 데이터베이스 연결 설정 가져오기
include "db.php";

// 테이블 이름 변경 쿼리 실행
$query = "RENAME TABLE  Mlang_BBS_Admin TO Mlang_BBS_Admin";
$result = mysqli_query($db, $query);

if ($result) {
    echo "테이블 이름이 성공적으로 변경되었습니다:  Mlang_BBS_Admin → Mlang_BBS_Admin";
} else {
    echo "테이블 이름 변경 중 오류가 발생했습니다: " . mysqli_error($db);
}

// 데이터베이스 연결 종료
mysqli_close($db);
?>