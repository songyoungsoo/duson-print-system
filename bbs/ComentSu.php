<?php
// ComFFCode 변수 확인
$ComFFCode = isset($ComFFCode) ? $ComFFCode : '';

// 테이블 이름 확인
if (!isset($table) || empty($table)) {
    // 테이블 이름이 없으면 아무것도 하지 않음
    return;
}

// 댓글 테이블이 존재하는지 확인
$check_table_query = "SHOW TABLES LIKE 'mlang_{$table}_bbs_coment'";
$check_result = mysqli_query($db, $check_table_query);
if (!$check_result || mysqli_num_rows($check_result) == 0) {
    // 댓글 테이블이 없으면 아무것도 하지 않음
    return;
}

// 댓글 수 조회
if ($ComFFCode == "1" && isset($row['Mlang_bbs_no'])) {
    $CommentK_result = mysqli_query($db, "select * from mlang_{$table}_bbs_coment where Mlang_coment_BBS_no='{$row['Mlang_bbs_no']}'");
} elseif ($ComFFCode == "2" && isset($row_reply['Mlang_bbs_no'])) {
    $CommentK_result = mysqli_query($db, "select * from mlang_{$table}_bbs_coment where Mlang_coment_BBS_no='{$row_reply['Mlang_bbs_no']}'");
} else {
    // 필요한 변수가 없으면 아무것도 하지 않음
    return;
}

// 쿼리 결과 확인
if ($CommentK_result) {
    $CommentK_rows = mysqli_num_rows($CommentK_result);
    $CommentK_Su = mysqli_affected_rows($db);
    if ($CommentK_rows > 0) {
        echo("<font style='color:#A1A1A1; font-size:8pt;'>[$CommentK_Su]</font>");
    }
}
?>
