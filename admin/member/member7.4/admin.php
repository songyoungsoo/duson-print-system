<?php
////////////////// ������ �α��� ////////////////////

function authenticate()
{
    header("WWW-authenticate: basic realm=\"������ ����!\" ");
    header("HTTP/1.0 401 Unauthorized");
    echo("<html><head><script>
        function pop() { 
            alert('������ ���� ����');
            history.go(-1);
        }
        </script>
        </head>
        <body onload='pop()'></body>
        </html>
    ");
    exit;
}

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']))
{
    authenticate();
}
else
{
    include "../../db.php";
    $result = mysqli_query($db, "SELECT * FROM member WHERE no='1'");
    $row = mysqli_fetch_array($result);

    $adminid = $row['id'];
    $adminpasswd = $row['pass'];

    if ($_SERVER['PHP_AUTH_USER'] !== $adminid || $_SERVER['PHP_AUTH_PW'] !== $adminpasswd)
    {
        authenticate();
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "view") {
    include "../title.php";
    $op = "pop";
    $db_dir = "../..";
    include "../../member/member_fild.php";
    $action = "admin.php?mode=modifyok";
    $MdoifyMode = "view";
    include "../../member/form.php";
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "delete") {
    if ($no == "1") {
        echo ("
        <html>
        <script language=javascript>
        window.alert('$no ���� ������ �Դϴ�.\\n\\n������ �ڽ��� Ż��ó���Ҽ������ϴ�.');
        window.self.close();
        </script>
        </html>
        ");
        exit;
    }

    $result = mysqli_query($db, "DELETE FROM member WHERE no='$no'");
    mysqli_close($db);

    echo ("
    <html>
    <script language=javascript>
    window.alert('���������� $no�� ȸ���� Ż�� ó�� �Ͽ����ϴ�.');
    opener.parent.location.reload();
    window.self.close();
    </script>
    </html>
    ");
    exit;
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "modifyok") {
    include "../../db.php";

    if ($PhoFileChick) {
        if ($photofile) {
            $upload_dir = "./$PhotoFileDir";
            include "./upload.php";
            if ($PhotoFileDirName) {
                unlink("$upload_dir/$PhotoFileDirName");
                unlink("$upload_dir/$SunPhotoName");
            }

            if ($SunPhotoName_ok == "yes") {
                include "../PHPClass/UpFileProcessClass.php";
                $file = "$upload_dir/$PhotofileName";
                $save_filename = "$SunPhotoName";
                $save_path = "$upload_dir/";
                $max_width = "$SunPhotoName_width";
                $max_height = "$SunPhotoName_height";
                thumnail($file, $save_filename, $save_path, $max_width, $max_height);
            }
        } else {
            echo ("<script language=javascript>
            window.alert('������ �ڷḦ �����Ѵٰ� üũ�ϼ̽��ϴ�.\\n\\n�׷��� ������ �����ڷᰡ ���� �ֳ׿� *^^*');
            history.go(-1);
            </script>
            ");
            exit;
        }

        $query = "UPDATE member SET 
            pass='$pass1', 
            name='$name', 
            phone1='$phone1',  
            phone2='$phone2',  
            phone3 ='$phone3', 
            hendphone1='$hendphone1',  
            hendphone2='$hendphone2',  
            hendphone3='$hendphone3',  
            email='$email',  
            sample6_postcode='$sample6_postcode',
            sample6_address='$sample6_address',
            sample6_detailAddress='$sample6_detailAddress', 
            sample6_extraAddress='$sample6_extraAddress',
            po1='$po1',  
            po2='$po2',  
            po3='$po3',  
            po4='$po4',  
            po5='$po5',  
            po6='$po6',  
            po7='$po7',  
            connent='$connent'  
            WHERE no='$no'";
    } else {
        $query = "UPDATE member SET 
            pass='$pass1',  
            name='$name', 
            phone1='$phone1',  
            phone2='$phone2',  
            phone3 ='$phone3', 
            hendphone1='$hendphone1',  
            hendphone2='$hendphone2',  
            hendphone3='$hendphone3',  
            email='$email',  
            sample6_postcode='$sample6_postcode',
            sample6_address='$sample6_address',
            sample6_detailAddress='$sample6_detailAddress', 
            sample6_extraAddress='$sample6_extraAddress',
            po1='$po1',  
            po2='$po2',  
            po3='$po3',  
            po4='$po4',  
            po5='$po5',  
            po6='$po6',  
            po7='$po7',  
            connent='$connent'  
            WHERE no='$no'";
    }

    $result = mysqli_query($db, $query);

    if (!$result) {
        echo "
        <script language=javascript>
        window.alert(\"DB ���� �����Դϴ�!\")
        history.go(-1);
        </script>";
        exit;
    } else {
        echo ("
        <script language=javascript>
        alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
        opener.parent.location.reload();
        window.self.close();
        </script>
        <meta charset='euc-kr'>
        ");
        exit;
    }

    mysqli_close($db);
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
