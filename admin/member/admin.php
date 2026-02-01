<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';

// DB 연결
include"../../db.php";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode==="view"){

include"../title.php";

$stmt = mysqli_prepare($db, "SELECT * FROM users WHERE id = ?");
if ($stmt) {
    $no_int = (int)$no;
    mysqli_stmt_bind_param($stmt, "i", $no_int);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($row) {
        $MlangMember_id = $row['username'] ?? '';
        $MlangMember_pass1 = '';
        $MlangMember_name = $row['name'] ?? '';
        
        $phone_parts = explode('-', $row['phone'] ?? '');
        $MlangMember_phone1 = $phone_parts[0] ?? '';
        $MlangMember_phone2 = $phone_parts[1] ?? '';
        $MlangMember_phone3 = $phone_parts[2] ?? '';
        
        $MlangMember_hendphone1 = '';
        $MlangMember_hendphone2 = '';
        $MlangMember_hendphone3 = '';
        
        $MlangMember_email = $row['email'] ?? '';
        $MlangMember_sample6_postcode = $row['postcode'] ?? '';
        $MlangMember_sample6_address = $row['address'] ?? '';
        $MlangMember_sample6_detailAddress = $row['detail_address'] ?? '';
        $MlangMember_sample6_extraAddress = $row['extra_address'] ?? '';
        $MlangMember_po1 = $row['business_number'] ?? '';
        $MlangMember_po2 = $row['business_name'] ?? '';
        $MlangMember_po3 = $row['business_owner'] ?? '';
        $MlangMember_po4 = $row['business_type'] ?? '';
        $MlangMember_po5 = $row['business_item'] ?? '';
        $MlangMember_po6 = $row['business_address'] ?? '';
        $MlangMember_po7 = $row['tax_invoice_email'] ?? '';
        $MlangMember_date = $row['created_at'] ?? '';
        $MlangMember_level = $row['level'] ?? '';
    }
}

$action="admin.php?mode=modifyok";
$MdoifyMode="view";
include"../../member/form.php";

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode==="delete"){

if($no==="1"){
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

$stmt = mysqli_prepare($db, "DELETE FROM users WHERE id = ?");
if ($stmt) {
    $no_int = (int)$no;
    mysqli_stmt_bind_param($stmt, "i", $no_int);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        mysqli_stmt_close($stmt);
        mysqli_close($db);

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

$pass1 = $_POST['pass1'] ?? '';
$name = $_POST['name'] ?? '';
$phone1 = $_POST['phone1'] ?? '';
$phone2 = $_POST['phone2'] ?? '';
$phone3 = $_POST['phone3'] ?? '';
$hendphone1 = $_POST['hendphone1'] ?? '';
$hendphone2 = $_POST['hendphone2'] ?? '';
$hendphone3 = $_POST['hendphone3'] ?? '';
$email = $_POST['email'] ?? '';
$sample6_postcode = $_POST['sample6_postcode'] ?? '';
$sample6_address = $_POST['sample6_address'] ?? '';
$sample6_detailAddress = $_POST['sample6_detailAddress'] ?? '';
$sample6_extraAddress = $_POST['sample6_extraAddress'] ?? '';
$po1 = $_POST['po1'] ?? '';
$po2 = $_POST['po2'] ?? '';
$po3 = $_POST['po3'] ?? '';
$po4 = $_POST['po4'] ?? '';
$po5 = $_POST['po5'] ?? '';
$po6 = $_POST['po6'] ?? '';
$po7 = $_POST['po7'] ?? '';
$no = $_POST['no'] ?? $_GET['no'] ?? '';

$phone = "$phone1-$phone2-$phone3";

$no_int = (int)$no;

if (!empty($pass1)) {
    $password_hash = password_hash($pass1, PASSWORD_DEFAULT);
    
    $placeholder_count = substr_count("UPDATE users SET password=?, name=?, phone=?, email=?, postcode=?, address=?, detail_address=?, extra_address=?, business_number=?, business_name=?, business_owner=?, business_type=?, business_item=?, business_address=?, tax_invoice_email=? WHERE id=?", '?');
    $type_string = "sssssssssssssssi";
    $type_count = strlen($type_string);
    $var_count = 16;
    
    if ($placeholder_count === $type_count && $type_count === $var_count) {
        $update_query = "UPDATE users SET password=?, name=?, phone=?, email=?, postcode=?, address=?, detail_address=?, extra_address=?, business_number=?, business_name=?, business_owner=?, business_type=?, business_item=?, business_address=?, tax_invoice_email=? WHERE id=?";
        
        $stmt = mysqli_prepare($db, $update_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $type_string,
                $password_hash, $name, $phone, $email, $sample6_postcode, $sample6_address, $sample6_detailAddress, $sample6_extraAddress,
                $po1, $po2, $po3, $po4, $po5, $po6, $po7, $no_int
            );
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            error_log("Database prepare statement failed: " . mysqli_error($db));
            $result = false;
        }
    } else {
        error_log("bind_param mismatch: placeholders=$placeholder_count, types=$type_count, vars=$var_count");
        $result = false;
    }
} else {
    $placeholder_count = substr_count("UPDATE users SET name=?, phone=?, email=?, postcode=?, address=?, detail_address=?, extra_address=?, business_number=?, business_name=?, business_owner=?, business_type=?, business_item=?, business_address=?, tax_invoice_email=? WHERE id=?", '?');
    $type_string = "ssssssssssssssi";
    $type_count = strlen($type_string);
    $var_count = 15;
    
    if ($placeholder_count === $type_count && $type_count === $var_count) {
        $update_query = "UPDATE users SET name=?, phone=?, email=?, postcode=?, address=?, detail_address=?, extra_address=?, business_number=?, business_name=?, business_owner=?, business_type=?, business_item=?, business_address=?, tax_invoice_email=? WHERE id=?";
        
        $stmt = mysqli_prepare($db, $update_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $type_string,
                $name, $phone, $email, $sample6_postcode, $sample6_address, $sample6_detailAddress, $sample6_extraAddress,
                $po1, $po2, $po3, $po4, $po5, $po6, $po7, $no_int
            );
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            error_log("Database prepare statement failed: " . mysqli_error($db));
            $result = false;
        }
    } else {
        error_log("bind_param mismatch: placeholders=$placeholder_count, types=$type_count, vars=$var_count");
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