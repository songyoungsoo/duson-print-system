<?php
$Color1 = "1466BA";
$Color2 = "4C90D6";
$Color3 = "BBD5F0";
$PageCode = "member";

include "../top.php";

$DbDir = isset($DbDir) ? $DbDir : "..";
$MemberDir = isset($MemberDir) ? $MemberDir : ".";

session_start();

include "$DbDir/db.php";

$id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : '';

if ($id) {
    $query = "SELECT * FROM member WHERE id='$id'";
    $result = mysqli_query($db, $query);
    $rows = mysqli_num_rows($result);

    if ($rows) {
        echo("
            <script language='javascript'>
                window.alert('\\n $id 는 이미 등록되어 있는\\n\\n이름이므로 신청하실 수 없습니다.\\n');
                history.go(-1);
            </script>
        ");
        exit;
    }
}

$login_dir = "$MemberDir";
$db_dir = "$MemberDir";

$action = "$MemberDir/member_form_ok.php";
include "$MemberDir/form.php";

include "../down.php";
?>
