<?php
//�α����� ����Ǹ�..
if($log=="logout") { // �α׾ƿ� 
      
	 session_unregister("sess_adID");
	 setCookie("adminVar",$ad_id, time()+0,"/");
	 echo ("<script> location.href='index.php';</script>");
	 exit;
}

else if ($log=="conn"){
	include("../sub/dbConn.inc");

	$SQL = "select * from admin where id='$id' and pwd='$pwd'";
	$query = mysql_query($SQL,$connection);

	$row = mysql_fetch_array ($query);

	if (!$row) {
		echo ("
			<script>
			window.alert ('�������� �ʴ� ���̵�ų� ��й�ȣ�� Ʋ���ϴ�.')
			history.go(-1)
			</script>
		");
		exit;
	}


	if (($row["id"] == $id) and ($row["pwd"] == $pwd ))
	{
		$sess_admin = $id;
		SetCookie("adminVar",$id, time()+36000,"/");
		echo ("
			<script>
			location.href='ad_auto.php';
			</script>
		");
		db_close();
	}
}

?>

<html>
<head>
<title>�� �μձ�ȹ - ������</title>

<script language="JavaScript">
	function chk(){
		f = document.form1;
		if(f.id.value==""){
			f.id.focus();
			return false;
		}
		else if(f.pwd.value==""){
			f.pwd.focus();
			return false;
		}
	}
</script>

<meta http-equiv="Content-Type" content="text/html; charset=euc-kr"></head>

<body bgcolor="#ffffff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" background="img/login_bg.gif"  onLoad="document.form1.id.focus();">
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p><table border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td><img src="img/login_image_top.gif" width="496" height="55"></td>
  </tr>
  <tr> 
    <td height="144" background="img/login_image_middle.gif" align="center"> 
	<form name="form1" method="post" action="index.php?log=conn" onSubmit="return chk()">
        <table border="0" cellspacing="0" cellpadding="0">
          <tr> 
            <td><img src="img/login_id.gif" width="67" height="19"></td>
            <td> <input type="text" name="id" value="" class="textfield3" size="15" maxlength="40" onBlur="javascript: document.forms[0].pwd.focus();"> 
            </td>
            <td rowspan="3"><input type="image" src="img/login_login.gif" width="85" height="51" border="0"></td>
          </tr>
          <tr> 
            <td height="3"></td>
            <td height="3"></td>
          </tr>
          <tr> 
            <td><img src="img/login_pass.gif" width="67" height="19"></td>
            <td> <input type="password" name="pwd" value="" class="textfield3" size="15" maxlength="40"></td>
          </tr>
          <tr> 
            <td height="7" colspan="3"></td>
          </tr>
        </table>
      </form></td>
  </tr>
  <tr> 
    <td> <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td><input type="image" src="img/login_image_bottom.gif" width="496" height="42"></td>
        </tr>
      </table></td>
  </tr>
</table>
</body>
</html>
