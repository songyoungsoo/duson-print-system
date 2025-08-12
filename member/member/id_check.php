<html>
<head>
<title><?php echo $admin_name; ?></title>
<style>
p,br,body,td,input,select,submit {color:black; font-size:10pt; FONT-FAMILY:굴림;}
b {color:black; font-size:10pt; FONT-FAMILY:굴림;}
</style>

<link rel="stylesheet" type="text/css" href="https://www.script.ne.kr/script.css">
</head>

<body bgcolor='#FFFFFF'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<!-- <tr><td  height=5 align=left bgcolor='#000000' width=100%></td></tr> -->
<!-- <tr><td  height=25 align=left bgcolor='#43B5C9' width=100%> -->
&nbsp;&nbsp;<b><font color=#FFFFFF>▶ <?php echo $admin_name;?> 아이디 중복검사</font></b>
<!-- </td></tr>
<tr><td  height=80 align=center bgcolor='#FFFFFF' width=100%> -->
<img src='img/loding.gif'>
<BR><BR>
&nbsp;&nbsp;
<b>
사용가능한 아이디를 검색중에 있습니다..
</b>
</td></tr>
</table>
</body>
</html>
<!-- // 위의 체크폼 통과후 db에저장을 시킨다... -->
<?php
include "../db.php";

$id = $_GET['id'];

$query = "SELECT id FROM member WHERE id='$id'";
$result = mysql_query($query, $db);

if (!$result) {
  die("쿼리 실행에 실패했습니다: " . mysql_error($db));
}

$rows = mysql_num_rows($result);

if ($rows) {
  echo ("
    <script language='javascript'>
      alert('$id 는 이미 등록되어 있는 ID입니다.');
      window.close(); // 팝업 창 닫기
    </script>
  ");
} else {
  echo ("
    <script language='javascript'>
      alert('$id 는 사용하실 수 있는 ID입니다.');
      window.close(); // 팝업 창 닫기
    </script>
  ");
}

mysql_close($db);
?>
