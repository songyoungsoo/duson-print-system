<?php
////////////////// 관리자 로그인 ////////////////////

function authenticate()
{
    header("WWW-authenticate: basic realm=\"관리자 인증!\" ");
    header("HTTP/1.0 401 Unauthorized");
    echo("<html><head><script>
        function pop() { 
            alert('관리자 인증 실패');
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
        window.alert('$no 번은 관리자 입니다.\\n\\n관리자 자신을 탈퇴처리할수없습니다.');
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
    window.alert('정상적으로 $no번 회원을 탈퇴 처리 하였습니다.');
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
            window.alert('내사진 자료를 수정한다고 체크하셨습니다.\\n\\n그런데 수정할 사진자료가 빠져 있네요 *^^*');
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
        window.alert(\"DB 접속 에러입니다!\")
        history.go(-1);
        </script>";
        exit;
    } else {
        echo ("
        <script language=javascript>
        alert('\\n정보를 정상적으로 수정하였습니다.\\n');
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
