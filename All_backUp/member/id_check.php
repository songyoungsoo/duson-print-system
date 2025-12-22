<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($admin_name); ?></title>
<style>
p, br, body, td, input, select, submit { color: black; font-size: 10pt; font-family: Arial, sans-serif; }
b { color: black; font-size: 10pt; font-family: Arial, sans-serif; }
</style>
<link rel="stylesheet" type="text/css" href="https://www.script.ne.kr/script.css">
</head>
<body bgcolor='#FFFFFF'>
<table border="0" width="100%" align="center" cellpadding="0" cellspacing="0">
<tr>
  <td height="80" align="center" bgcolor="#FFFFFF" width="100%">
    <img src="img/loading.gif">
    <br><br>
    &nbsp;&nbsp;
    <b>
      요청하신 데이터를 검색중입니다..
    </b>
  </td>
</tr>
</table>
<?php
include "../db.php";

// 데이터베이스 연결 확인
if ($db->connect_error) {
  die("데이터베이스 연결에 실패했습니다: " . $db->connect_error);
}

$id = isset($_GET['id']) ? $_GET['id'] : '';

$query = "SELECT id FROM member WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('s', $id);

if ($stmt->execute()) {
  $stmt->store_result();
  $rows = $stmt->num_rows;

  if ($rows > 0) {
    echo ("
      <script>
        alert('$id 는 이미 사용중인 ID입니다.');
        window.close(); // 팝업 창 닫기
      </script>
    ");
  } else {
    echo ("
      <script>
        alert('$id 는 사용 가능한 ID입니다.');
        window.close(); // 팝업 창 닫기
      </script>
    ");
  }
} else {
  die("쿼리 실행 중 오류가 발생했습니다: " . $stmt->error);
}

$stmt->close();
$db->close();
?>
</body>
</html>
