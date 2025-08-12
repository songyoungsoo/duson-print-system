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

    // Prepared Statement를 사용하여 SQL Injection 방지
    $stmt = $db->prepare("SELECT * FROM member WHERE id=?");
    $stmt->bind_param("s", $WebtingMemberLogin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $no = $row["no"];
    $stmt->close();
    $db->close();

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
            window.alert('사진을 업로드하지 않으셨습니다. 다시 시도해 주세요.');
            history.go(-1);
            </script>");
            exit;
        }
    }

    // Prepared Statement를 사용하여 SQL Injection 방지
    $query = "UPDATE member SET 
        pass=?,  
        phone1=?,  
        phone2=?,  
        phone3=?, 
        hendphone1=?,  
        hendphone2=?,  
        hendphone3=?,  
        email=?,  
        sample6_postcode=?,
        sample6_address=?,
        sample6_detailAddress=?, 
        sample6_extraAddress=?,
        po1=?,  
        po2=?,  
        po3=?,  
        po4=?,  
        po5=?,  
        po6=?,  
        po7=?,  
        connent=?  
        WHERE no=?";

    $stmt = $db->prepare($query);
    $stmt->bind_param("sssssssssssssssssssi", 
        $pass1, $phone1, $phone2, $phone3, $hendphone1, $hendphone2, $hendphone3, 
        $email, $sample6_postcode, $sample6_address, $sample6_detailAddress, 
        $sample6_extraAddress, $po1, $po2, $po3, $po4, $po5, $po6, $po7, $connent, $no
    );

    if ($stmt->execute()) {
        echo "<script language=javascript>
        alert('정보가 성공적으로 수정되었습니다.');
        window.location.href='$PHP_SELF?mode=modify';
        </script>";
    } else {
        echo "<script language=javascript>
        window.alert('DB 업데이트에 실패했습니다.');
        history.go(-1);
        </script>";
    }
    $stmt->close();
    $db->close();
}
?>
