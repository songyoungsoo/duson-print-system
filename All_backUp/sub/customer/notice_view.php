<?php
/**
 * 공지사항 조회수 증가 처리
 */

include $_SERVER['DOCUMENT_ROOT'] . "/db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $query = "UPDATE notices SET view_count = view_count + 1 WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($db);
?>
