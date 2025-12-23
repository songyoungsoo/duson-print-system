<?php
declare(strict_types=1);
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

include"pollconfig.php";?>

<form name="form1" method="post" action="admin.php">
  <table width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr> 
      <td colspan="2" bgcolor="#336699"><div align="center"><font style='font:bold; color:#FFFFFF;'>로그인 해주세요</font></div></td>
    </tr>
    <tr bgcolor="cfcfcf"> 
      <td width="30%"><div align="right">아이디</div></td>
      <td width="70%"><input name="username" type="text" id="username2"></td>
    </tr>
    <tr bgcolor="cfcfcf"> 
      <td><div align="right">비밀번호</div></td>
      <td><input name="password" type="password" id="password2"></td>
    </tr>
    <tr bgcolor="cfcfcf">
      <td>&nbsp;</td>
      <td><input name="loginpoll" type="submit" id="loginpoll3" value="Login"><BR><BR></td>
    </tr>
  </table>
</form>
</body>
</html>
