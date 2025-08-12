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


?>

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
        background: url('./img/149414.jpg') no-repeat center center fixed;
        background-size: cover;
        font-size: 9pt;
        font-family: 'Noto Sans', sans-serif;
        margin: 0; /* Remove default margin to ensure the background covers the entire viewport */
    }
            /* display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('pic.jpg') no-repeat center center fixed;
            background-size: cover;
            background-color: #f5f5f5;
            font-size: 9pt;
            font-family: 'Noto Sans', sans-serif; */

        /* form {
            width: 420px;
            border: 1px solid gray;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 1px 1px 10px gray;
            text-align: center;
        }

        form table {
            width: 100%;
        } */
        form {
    width: 420px;
    border: 1px solid gray;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 1px 1px 10px gray;
    text-align: center;
    background-color: rgba(255, 255, 255, 0.8); /* Set the background color with transparency */
}

form table {
    width: 100%;
    background-color: rgba(255, 255, 255, 0); /* Set the background color of the table to be transparent */
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

<!---------- �α����� ����-------------------------->
    <form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField();' action='/member/member_login_ok.php'>
        <input type='hidden' name='selfurl' value='<?php if
($pageselfurl){echo("$pageselfurl");}else{echo("$LoginChickBoxUrl");}?>'>
        <input type='hidden' name='mode'  value='member_login'>

        <table border=0 align=center cellpadding='3' cellspacing='0'>
            <h2>�μձ�ȹ�μ�</h2>
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

<?$InputStyle="style='font-size:10pt; font:bold; background-color:#B3B46A; color:#FFFFFF; border-style:solid; height:20px; border:2 solid #C7C8A5'";?>

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