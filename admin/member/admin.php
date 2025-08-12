<?php
////////////////// 인증 로그인 ////////////////////
function authenticate()
{
    header("WWW-Authenticate: Basic realm=\"관리자 페이지!\"");
    header("HTTP/1.0 401 Unauthorized");
    echo("<html>
<head>
    <script>
        function pop() { 
            alert('인증 실패'); 
            history.go(-1); 
        }
    </script>
</head>
<body onLoad='pop()'></body>
</html>");
    exit;
}

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    authenticate();
} else {
    include "../../db.php";

    $stmt = $db->prepare("SELECT * FROM member WHERE no = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || strcmp($_SERVER['PHP_AUTH_USER'], $row['id']) || strcmp($_SERVER['PHP_AUTH_PW'], $row['pass'])) {
        authenticate();
    }
    $stmt->close();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// mode 변수를 초기화하고 요청에서 값을 가져옵니다.
$mode = $_GET['mode'] ?? '';
$no = $_GET['no'] ?? 0;

if ($mode == "view") {
    include "../title.php";
    $op = "pop";
    $db_dir = "../..";
    include "../../member/member_fild.php";
    $action = "admin.php?mode=modifyok";
    $MdoifyMode = "view";
    include "../../member/form.php";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "delete") {
    if ($no == "1") {
        echo ("
        <html>
        <script language='javascript'>
        alert('$no 번 관리자는 삭제할 수 없습니다.\\n\\n본인 스스로를 삭제처리할 수 없습니다.');
        window.self.close();
        </script>
        </html>
        ");
        exit;
    }

    $stmt = $db->prepare("DELETE FROM member WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();

    echo ("
    <html>
    <script language='javascript'>
    alert('관리자님, $no 번 회원을 삭제 처리 하였습니다.');
    opener.parent.location.reload();
    window.self.close();
    </script>
    </html>
    ");
    exit;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "modifyok") {
    include "../../db.php";

    if (isset($PhoFileChick) && isset($photofile)) {
        $upload_dir = "./$PhotoFileDir";
        include "./upload.php";
        if (isset($PhotoFileDirName)) {
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
    }

    $query = "UPDATE member SET 
        pass = ?, 
        name = ?, 
        phone1 = ?,  
        phone2 = ?,  
        phone3 = ?, 
        hendphone1 = ?,  
        hendphone2 = ?,  
        hendphone3 = ?,  
        email = ?,  
        sample6_postcode = ?,
        sample6_address = ?,
        sample6_detailAddress = ?, 
        sample6_extraAddress = ?,
        po1 = ?,  
        po2 = ?,  
        po3 = ?,  
        po4 = ?,  
        po5 = ?,  
        po6 = ?,  
        po7 = ?,  
        connent = ?  
        WHERE no = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("ssssssssssssssssssssi", $pass1, $name, $phone1, $phone2, $phone3, $hendphone1, $hendphone2, $hendphone3, $email, $sample6_postcode, $sample6_address, $sample6_detailAddress, $sample6_extraAddress, $po1, $po2, $po3, $po4, $po5, $po6, $po7, $connent, $no);

    $result = $stmt->execute();

    if (!$result) {
        echo "
        <script language='javascript'>
            alert(\"DB 업데이트 실패!\");
            history.go(-1);
        </script>";
        exit;
    } else {
        echo ("
        <script language='javascript'>
        alert('\\n데이터가 성공적으로 업데이트 되었습니다.\\n');
        opener.parent.location.reload();
        window.self.close();
        </script>
        <meta charset='utf-8'>
        ");
        exit;
    }
    $stmt->close();
    $db->close();
}
?>
