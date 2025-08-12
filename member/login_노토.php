<?php
$Color1="1466BA";
$Color2="4C90D6";
$Color3="BBD5F0";
$PageCode="member";
include"../db.php";
?>

<?php
$id_login_ok = isset($_SESSION['id_login_ok']) ? $_SESSION['id_login_ok'] : false;

if ($HTTP_COOKIE_VARS[id_login_ok] or $_COOKIE[id_login_ok]) {
    echo ("<script language=javascript>
    window.alert('ȸ���� ������ �̹� �α��� �Ǿ��� �ֽ��ϴ�.');
    window.self.close();
    </script>");
    exit;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the submitted username and password
    $username = $_POST['id'];
    $password = $_POST['pass'];

    // Check if the user wants to remember the login
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Add your login authentication logic here
    // ...

    // If login is successful
    if ($login_successful) {
        // Set session variables
        $_SESSION['id_login_ok'] = true;

        // Set cookies for automatic login if the "Remember Me" checkbox is checked
        if ($remember_me) {
            setcookie('id_login_ok', true, time() + (86400 * 30), "/"); // 30 days expiration
            // You may also store other user data in cookies if needed
            // setcookie('user_id', $user_id, time() + (86400 * 30), "/");
        }

        // Redirect to the desired page after successful login
        header("Location: /dashboard.php"); // Change this to your actual dashboard page
        exit;
    } else {
        echo ("<script language=javascript>
        window.alert('�α��� ����. ���̵�� ��й�ȣ�� Ȯ�����ּ���.');
        </script>");
    }
}
?>


<!-- <html>
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
            background-color: dodgerblue;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }

        input[type='submit']:hover,
        input[type='button']:hover {
            background-color: skyblue;
        }

        p a {
            color: #B3B46A;
            text-decoration: none;
            font-weight: bold;
        }
    </style> -->
    <!DOCTYPE html>
<html lang="kr">
<head>
    <meta charset="euc-kr">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/sambo.css" type="text/css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400&display=swap">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
            font-size: 9pt;
            font-family: 'Noto Sans', sans-serif;
        }

        form {
            width: 420px;
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
            width: calc(100% - 12px);
            padding: 5px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input[type='submit'],
        input[type='button'] {
            padding: 5px 10px;
            background-color: dodgerblue;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            font-size: 9pt;
            font-family: 'Noto Sans', sans-serif;
        }

        input[type='submit']:hover,
        input[type='button']:hover {
            background-color: skyblue;
        }

        p a {
            color: #B3B46A;
            text-decoration: none;
            font-weight: bold;
        }

        /* Title above username and password fields */
        h2 {
            font-size: 12pt;
            font-weight: bold;
            font-family: 'Noto Sans', sans-serif;
        }
    </style>
</head>    

<body>

<script src="/js/login.js" type="text/javascript"></script>
<h2>�μձ�ȹ�μ�</h2>
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
        <!-- Add this checkbox inside your form -->
        <input type="checkbox" name="remember_me" id="remember_me">
        <label for="remember_me">�ڵ� �α���</label>


        <!-- �н��� ȸ�� ���̵�/��й�ȣ ã�� ��ũ -->
        <p>
            <a href='#' onClick="javascript:window.open('/member/member_search.php?mode=id', 'member_search','width=510,height=220,top=100,left=100,statusbar=no,scrollbars=yes,toolbar=no');">�н��� ȸ�� ���̵�/��й�ȣ ã��</a>
        </p>
    </form>
</body>
</html>