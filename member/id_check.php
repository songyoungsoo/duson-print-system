<?php
include "../db.php";

if ($db->connect_error) {
    die("데이터베이스 연결에 실패했습니다: " . $db->connect_error);
}

$id = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id']) : '';

if (empty($id)) {
    echo "<script>alert('아이디를 입력해주세요.'); window.close();</script>";
    exit;
}

$query = "SELECT username FROM users WHERE username = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('s', $id);

if ($stmt->execute()) {
    $stmt->store_result();
    $rows = $stmt->num_rows;

    if ($rows > 0) {
        echo "<script>alert('" . htmlspecialchars($id, ENT_QUOTES) . " 는 이미 사용중인 ID입니다.'); window.close();</script>";
    } else {
        echo "<script>alert('" . htmlspecialchars($id, ENT_QUOTES) . " 는 사용 가능한 ID입니다.'); window.close();</script>";
    }
} else {
    echo "<script>alert('조회 중 오류가 발생했습니다.'); window.close();</script>";
}

$stmt->close();
$db->close();
?>
