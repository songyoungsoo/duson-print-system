<?php
include 'db.php';

$ModifyCode = isset($_GET['no']) ? intval($_GET['no']) : 0;

$stmt = $db->prepare("SELECT * FROM $table WHERE no = ?");
$stmt->bind_param("i", $ModifyCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $GF_title = $row['title'];
        $GF_cont = $row['cont'];
        $GF_url = $row['url'];
        $GF_cate = $row['cate'];
        $GF_upfile = $row['upfile'];
        $GF_count = $row['count'];
        $GF_date = $row['date'];
    }
} else {
    echo "<p align=center><b>DB 에 $ModifyCode 의 등록 자료가 없음.</b></p>";
    exit;
}

$stmt->close();
$db->close();
?>
