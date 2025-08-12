<?php
$Color1="1466BA";
$Color2="4C90D6";
$Color3="BBD5F0";
$PageCode="member";
include"../db.php";
?>

<?php
$id_login_ok = isset($_SESSION['id_login_ok']) ? $_SESSION['id_login_ok'] : false;
if($HTTP_COOKIE_VARS[id_login_ok] or $_COOKIE[id_login_ok]){

echo ("<script language=javascript>
window.alert('ȸ���� ������ �̹� �α��� �Ǿ��� �ֽ��ϴ�.');
window.self.close();
</script>
");
exit;

}

?>

<html>
<head>
<link rel=StyleSheet HREF='/css/sambo.css' type=text/css>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
        }

        form {
            width: 400px;
            border: 1px solid gray;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 1px 1px 10px gray;
            text-align: center;
        }

        form table {
            width: 100%;
        }

        form table td:first-child {
            text-align: right;
            font-weight: bold;
            color: #696969;
        }

        form table td input[type='text'],
        form table td input[type='password'] {
            width: 100%;
            padding: 5px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type='submit'],
        input[type='button'] {
            padding: 5px 10px;
            background-color: skyblue;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }

        input[type='submit']:hover,
        input[type='button']:hover {
            background-color: dodgerblue;
        }

        p a {
            color: #B3B46A;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>    

<body>

<script src="/js/login.js" type="text/javascript"></script>

<!--
<BR><BR>

     <table border=0 align=center width=419 cellpadding=0 cellspacing=0>
      <tr><td align=center><img src='/img/login/box/top.gif' width=419 height=131></td></tr>
	  <tr><td align=center width=419 height=68 background='/img/login/box/center.gif'>
-->
<!---------- �α����� ����-------------------------->
    <form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField();' action='/member/member_login_ok.php'>
        <input type='hidden' name='selfurl' value='<?php if
($pageselfurl){echo("$pageselfurl");}else{echo("$LoginChickBoxUrl");}?>'>
        <input type='hidden' name='mode'  value='member_login'>
<!--
<table border=0 align=center cellpadding='3' cellspacing='0'>
<tr>
<td align=right><font style='color:#696969; font:bold;'>���̵�</font></td>
<td><input type='text' name='id'size='20' maxLength='20'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
</tr>
<tr>
<td align=right><font style='color:#696969; font:bold;'>��й�ȣ</font></td>
<td><input type='password' name='pass'size='20' maxLength='12'></td>
</tr>
</table>
-->
        <table border=0 align=center cellpadding='3' cellspacing='0'>
            <tr>
                <td>���̵�</td>
                <td><input type='text' name='id' size='20' maxLength='20'></td>
            </tr>
            <tr>
                <td>��й�ȣ</td>
                <td><input type='password' name='pass' size='20' maxLength='12'></td>
            </tr>
        </table>
<!---------- �α����� ��-------------------------->	  
<!--
	  </td></tr> 
<tr><td align=center background='/img/login/box/down.gif'  width="419" height="112" valign=top>
<?$InputStyle="style='font-size:10pt; font:bold; background-color:#B3B46A; color:#FFFFFF; border-style:solid; height:20px; border:2 solid #C7C8A5'";?>

     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr><td align=center>
	   <input type='submit' value=' �� �� �� ' <?=$InputStyle?>>
<input type='button' value=' ȸ������ ' onClick="javascript:window.location.href='/member/join.php';" <?=$InputStyle?>>
	   </td></tr>
	   </form>
     </table>
<p align=center>
<img src='/img/login/box/down_icon.gif' align=absmiddle>
<a href='#' onClick="javascript:window.open('/member/member_search.php?mode=id', 'member_search','width=510,height=220,top=100,left=100,statusbar=no,scrollbars=yes,toolbar=no');"><font style='font:bold; color:#B3B46A; text-decoration:underline'>�н��� ȸ�� ���̵�/��й�ȣ ã��</font></a>
</p>
</td></tr> 
	 </table>
</body>
</html>-->
                <!-- �α��� ��ư�� ȸ������ ��ư -->
        <input type='submit' value=' �� �� �� '>
        <input type='button' value=' ȸ������ ' onClick="javascript:window.location.href='/member/join.php';">

        <!-- �н��� ȸ�� ���̵�/��й�ȣ ã�� ��ũ -->
        <p>
            <a href='#' onClick="javascript:window.open('/member/member_search.php?mode=id', 'member_search','width=510,height=220,top=100,left=100,statusbar=no,scrollbars=yes,toolbar=no');">�н��� ȸ�� ���̵�/��й�ȣ ã��</a>
        </p>
    </form>
</body>
</html>