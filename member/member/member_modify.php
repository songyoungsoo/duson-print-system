<?php
if (!strcmp($mode, "modify")) {
    $Color1 = "1466BA";
    $Color2 = "4C90D6";
    $Color3 = "BBD5F0";
    $PageCode = "member";

    $login_dir = "..";
    $db_dir = "..";

    include "$db_dir/db.php";
    include "../top.php";

    $result = mysql_query("SELECT * FROM member WHERE id='$WebtingMemberLogin_id'", $db);
    $row = mysql_fetch_array($result);
    $no = $row["no"];
    mysql_close($db);

    include "./member_fild.php";
    $action = "$PHP_SELF?mode=modify_ok";
    $ModifyMode = "view";
    include "./form.php";

    include "../down.php";
} elseif (!strcmp($mode, "modify_ok")) {
    include "../db.php";

    if ($PhoFileChick) {
        if ($photofile) {
            $upload_dir = "./$PhotoFileDir";
            include "./upload.php";
            if ($PhotoFileDirName) {
                unlink("$upload_dir/$PhotoFileDirName");
            }
        } else {
            echo ("<script language=javascript>
            window.alert('내사진 자료를 수정한다고 체크하셨습니다. 그런데 수정할 사진자료가 빠져 있네요 *^^*');
            history.go(-1);
            </script>");
            exit;
        }
    }

    $query = "UPDATE member SET 
        pass='$pass1',  
        phone1='$phone1',  
        phone2='$phone2',  
        phone3='$phone3', 
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

    $result = mysql_query($query, $db);

    if (!$result) {
        echo "<script language=javascript>
        window.alert(\"DB 접속 에러입니다!\");
        history.go(-1);
        </script>";
        exit;
    } else {
        echo "<script language=javascript>
        alert('정보를 정상적으로 수정하였습니다.');
        window.location.href='$PHP_SELF?mode=modify';
        </script>";
        exit;
    }
    mysql_close($db);
}
?>