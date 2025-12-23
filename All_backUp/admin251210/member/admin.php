<?php
declare(strict_types=1);
////////////////// 관리자 로그인 ////////////////////
function authenticate()
{
  HEADER("WWW-authenticate: basic realm=\"관리자 전용!\" ");
  HEADER("HTTP/1.0 401 Unauthorized");
  echo("<html><head><script>
        function pop()
        { alert('인증 실패 오류');
             history.go(-1);}
        </script>
        </head>
        <body onLoad='pop()'></body>
        </html>
       ");
exit;
}

// ✅ PHP 7.4 호환: 변수 초기화
$auth_user = $_SERVER['PHP_AUTH_USER'] ?? '';
$auth_pw = $_SERVER['PHP_AUTH_PW'] ?? '';

// ✅ 입력 변수 초기화
$mode = $_GOST['mode'] ?? ''ET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GOST['no'] ?? ''ET['no'] ?? $_POST['no'] ?? '';
$search = $_GOST['search'] ?? ''ET['search'] ?? $_POST['search'] ?? '';
$PhoFileChick = $_POST['PhoFileChick'] ?? '';
$photofile = $_FILES['photofile']['name'] ?? '';

if (empty($auth_user) || empty($auth_pw))
{
 authenticate();
}

else
{

include"../../db.php";
$result= mysqli_query($db, "select * from member where no='1'");
$row = mysqli_fetch_array($result);

$adminid="$row[id]";
$adminpasswd="$row[pass]";


 if(strcmp($auth_user,$adminid) || strcmp($auth_pw,$adminpasswd) )
 { authenticate(); }


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode==="view"){

include"../title.php";

$op="pop";
$db_dir="../..";
include"../../member/member_fild.php";

$action="admin.php?mode=modifyok";
$MdoifyMode="view";
include"../../member/form.php";

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode==="delete"){

if($no==="1"){
// ✅ XSS 보호 적용
$safe_no = htmlspecialchars($no, ENT_QUOTES, 'UTF-8');
echo ("
<html>
<script language=javascript>
window.alert('$safe_no 번은 관리자 입니다.\\n\\n관리자 자신을 탈퇴처리할수없습니다.');
window.self.close();
</script>
</html>
");
exit;
}

// ✅ Prepared Statement 적용 (보안 강화)
$stmt = mysqli_prepare($db, "DELETE FROM member WHERE no = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $no);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        mysqli_stmt_close($stmt);
        mysqli_close($db);

        // ✅ XSS 보호 적용
        $safe_no = htmlspecialchars($no, ENT_QUOTES, 'UTF-8');
        echo ("
        <html>
        <script language=javascript>
        window.alert('정상적으로 $safe_no번 회원을 탈퇴 처리 하였습니다.');
        opener.parent.location.reload();
        window.self.close();
        </script>
        </html>
        ");
        exit;
    } else {
        mysqli_stmt_close($stmt);
        error_log("Delete member failed: " . mysqli_error($db));
        echo ("
        <html>
        <script language=javascript>
        window.alert('회원 삭제 중 오류가 발생했습니다.');
        history.go(-1);
        </script>
        </html>
        ");
        exit;
    }
} else {
    error_log("Database prepare statement failed: " . mysqli_error($db));
    echo ("
    <html>
    <script language=javascript>
    window.alert('데이터베이스 오류가 발생했습니다.');
    history.go(-1);
    </script>
    </html>
    ");
    exit;
}

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode==="modifyok"){

include"../../db.php";

if($PhoFileChick){

//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
if($photofile){
$upload_dir="./$PhotoFileDir";
include"./upload.php";
if($PhotoFileDirName){
	unlink("$upload_dir/$PhotoFileDirName");
	unlink("$upload_dir/$SunPhotoName");
	}

// 이미지 리사이즈 처리는 db.php에서 크기를 설정해둔다.
if($SunPhotoName_ok=="yes"){ //db.php에서 설정여부를
include"../PHPClass/UpFileProcessClass.php";
$file="$upload_dir/$PhotofileName"; //원본이미지 위치
$save_filename="$SunPhotoName"; //저장될 파일명
$save_path="$upload_dir/"; //저장될 위치
$max_width="$SunPhotoName_width"; //리사이즈이미지의 width 값
$max_height="$SunPhotoName_height"; //리사이즈이미지의 height 
thumnail($file, $save_filename, $save_path, $max_width, $max_height);
}

}else{
echo ("<script language=javascript>
window.alert('사진을 자료를 업로드한다고 체크하셨습니다.\\n\\n그런데 사진을 업로드자료가 없네 있네요 *^^*');
history.go(-1);
</script>
");
exit;
}
//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//

// ✅ Prepared Statement 적용 (보안 강화)
$update_query = "UPDATE member SET
pass=?, name=?, phone1=?, phone2=?, phone3=?,
hendphone1=?, hendphone2=?, hendphone3=?, email=?,
sample6_postcode=?, sample6_address=?, sample6_detailAddress=?, sample6_extraAddress=?,
po1=?, po2=?, po3=?, po4=?, po5=?, po6=?, po7=?, connent=?
WHERE no=?";

$stmt = mysqli_prepare($db, $update_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssss",
        $pass1, $name, $phone1, $phone2, $phone3,
        $hendphone1, $hendphone2, $hendphone3, $email,
        $sample6_postcode, $sample6_address, $sample6_detailAddress, $sample6_extraAddress,
        $po1, $po2, $po3, $po4, $po5, $po6, $po7, $connent, $no
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    error_log("Database prepare statement failed: " . mysqli_error($db));
    $result = false;
}

}else{

// ✅ Prepared Statement 적용 (보안 강화) - 파일 업로드 없는 경우
$update_query = "UPDATE member SET
pass=?, name=?, phone1=?, phone2=?, phone3=?,
hendphone1=?, hendphone2=?, hendphone3=?, email=?,
sample6_postcode=?, sample6_address=?, sample6_detailAddress=?, sample6_extraAddress=?,
po1=?, po2=?, po3=?, po4=?, po5=?, po6=?, po7=?, connent=?
WHERE no=?";

$stmt = mysqli_prepare($db, $update_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssss",
        $pass1, $name, $phone1, $phone2, $phone3,
        $hendphone1, $hendphone2, $hendphone3, $email,
        $sample6_postcode, $sample6_address, $sample6_detailAddress, $sample6_extraAddress,
        $po1, $po2, $po3, $po4, $po5, $po6, $po7, $connent, $no
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    error_log("Database prepare statement failed: " . mysqli_error($db));
    $result = false;
}

}


	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 실패입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정상적 수정처리를 하였습니다.\\n');
		opener.parent.location.reload();
        window.self.close();
		</script>
		<meta charset='utf-8'>
			");
		exit;

}
mysqli_close($db);

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>