<?php
if ($mode == "ok") {

    include "../../db.php";

    $table = "MlangOrder_PrintAuto_OfferOrder";

    // mysqli로 변경
    $result = mysqli_query($conn, "SELECT max(no) FROM $table");
    if (!$result) {
        echo "
            <script>
                window.alert(\"DB 접속 에러입니다!\")
                history.go(-1)
            </script>";
        exit;
    }
    $row = mysqli_fetch_row($result);

    if ($row[0]) {
        $new_no = $row[0] + 1;
    } else {
        $new_no = 1;
    }
    
    ############################################
    $date = date("Y-m-d H:i:s");
    $dbinsert = "INSERT INTO $table VALUES('$new_no', '$name', '', '', '$phone', '$email', '', '$MlangBody', '$date', 'no', '')";
    
    // mysqli로 변경
    $result_insert = mysqli_query($conn, $dbinsert);

    // 완료 메세지를 보인후 페이지를 이동 시킨다
    echo ("
        <script language=javascript>
        alert('\\n정상적으로  수동견적 신청을 하였습니다..\\n\\n관련 당당자가 견적을 내어 빠른 답변을 드릴것입니다.');
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>
    ");
    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$Bgcolor_1 = "#FFFFFF";
$Color2 = "#408080";
$Color3 = "#FFFFFF";
$align_td1 = "left";
$InputStyle = "style='font-size:10pt; background-color:#$Color3; color:#000000; border-style:solid; border:1 solid $Bgcolor_2'";

include "../MlangPrintAutoTopTwo.php";
?>

<head>
<script>
function checkForm(form) {
    var f = document.OfferOrderInfo;

    if (f.name.value == "") {
        alert("이름을 입력하여주세요?");
        f.name.focus();
        return false;
    }

    if (f.phone.value == "") {
        alert("전화번호을 입력하여주세요?");
        f.phone.focus();
        return false;
    }

    if (f.email.value == "") {
        alert("E 메일 주소를 입력해 주시기 바랍니다.");
        f.email.focus();
        return false;
    }

    if (f.email.value.lastIndexOf(" ") > -1) {
        alert("E 메일 주소에는 공백이 올수 없습니다.")
        f.email.focus();
        return false;
    }

    if (f.email.value.lastIndexOf(".") == -1) {
        alert("E 메일 주소를 정상적으로 입력해 주시기 바랍니다.")
        f.email.focus();
        return false;
    }

    if (f.email.value.lastIndexOf("@") == -1) {
        alert("E 메일 주소를 정상적으로 입력해 주시기 바랍니다.")
        f.email.focus();
        return false;
    }

    form.MlangBody.value = form.innerHTML;
    return true;
}
</script>

<style>
body, input, select, submit {color: black; font-size: 9pt; font-family: 굴림; word-break: break-all;}
td, table {border-color: #7C7C7C; border-collapse: collapse; font-size: 9pt;}

.TDF {font-size: 9pt; background-color: #429EB2; font: bold; color: #FFFFFF; font-family: 굴림; word-break: break-all;}
.TDF2 {font-size: 9pt; background-color: #FFDFEF; font: bold; color: #000000; font-family: 굴림; word-break: break-all;}
</style>

</head>

<!--------ergtssdfffffffffffffff------------------------>
<table border=0 align=center width=580 cellpadding='0' cellspacing='0'>

<form action="<?= $_SERVER['PHP_SELF'] ?>" name='OfferOrderInfo' method="post" onsubmit="return checkForm(this);">
<input type="hidden" name="mode" value='ok'>
<input type="hidden" name="MlangBody">

<tr><td align=center>
<?php include "Form.php"; ?>
</td></tr>

</table>    
<!--------ergtssdfffffffffffffff------------------------>

<p align=center>
<input type='image' src='' border=0>
</p>

</form>

<?php include "../MlangPrintAutoDown.php"; ?>