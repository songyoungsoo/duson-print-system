<html>
<head>
<title><?php echo $admin_name; ?></title>
<style>
p,br,body,td,input,select,submit {color:black; font-size:10pt; FONT-FAMILY:����;}
b {color:black; font-size:10pt; FONT-FAMILY:����;}
</style>

<link rel="stylesheet" type="text/css" href="https://www.script.ne.kr/script.css">
</head>

<body bgcolor='#FFFFFF'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<!-- <tr><td  height=5 align=left bgcolor='#000000' width=100%></td></tr> -->
<!-- <tr><td  height=25 align=left bgcolor='#43B5C9' width=100%> -->
&nbsp;&nbsp;<b><font color=#FFFFFF>�� <?php echo $admin_name;?> ���̵� �ߺ��˻�</font></b>
<!-- </td></tr>
<tr><td  height=80 align=center bgcolor='#FFFFFF' width=100%> -->
<img src='img/loding.gif'>
<BR><BR>
&nbsp;&nbsp;
<b>
��밡���� ���̵� �˻��߿� �ֽ��ϴ�..
</b>
</td></tr>
</table>
</body>
</html>
<!-- // ���� üũ�� ����� db�������� ��Ų��... -->
<?php
include "../db.php";

$id = $_GET['id'];

$query = "SELECT id FROM member WHERE id='$id'";
$result = mysql_query($query, $db);

if (!$result) {
  die("���� ���࿡ �����߽��ϴ�: " . mysql_error($db));
}

$rows = mysql_num_rows($result);

if ($rows) {
  echo ("
    <script language='javascript'>
      alert('$id �� �̹� ��ϵǾ� �ִ� ID�Դϴ�.');
      window.close(); // �˾� â �ݱ�
    </script>
  ");
} else {
  echo ("
    <script language='javascript'>
      alert('$id �� ����Ͻ� �� �ִ� ID�Դϴ�.');
      window.close(); // �˾� â �ݱ�
    </script>
  ");
}

mysql_close($db);
?>
