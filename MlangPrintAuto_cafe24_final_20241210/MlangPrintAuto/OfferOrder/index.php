<?php
if ($mode == "ok") {

    include "../../db.php";

    $table = "MlangOrder_PrintAuto_OfferOrder";

    // mysqli�� ����
    $result = mysqli_query($conn, "SELECT max(no) FROM $table");
    if (!$result) {
        echo "
            <script>
                window.alert(\"DB ���� �����Դϴ�!\")
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
    
    // mysqli�� ����
    $result_insert = mysqli_query($conn, $dbinsert);

    // �Ϸ� �޼����� ������ �������� �̵� ��Ų��
    echo ("
        <script language=javascript>
        alert('\\n����������  �������� ��û�� �Ͽ����ϴ�..\\n\\n���� ����ڰ� ������ ���� ���� �亯�� �帱���Դϴ�.');
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
        alert("�̸��� �Է��Ͽ��ּ���?");
        f.name.focus();
        return false;
    }

    if (f.phone.value == "") {
        alert("��ȭ��ȣ�� �Է��Ͽ��ּ���?");
        f.phone.focus();
        return false;
    }

    if (f.email.value == "") {
        alert("E ���� �ּҸ� �Է��� �ֽñ� �ٶ��ϴ�.");
        f.email.focus();
        return false;
    }

    if (f.email.value.lastIndexOf(" ") > -1) {
        alert("E ���� �ּҿ��� ������ �ü� �����ϴ�.")
        f.email.focus();
        return false;
    }

    if (f.email.value.lastIndexOf(".") == -1) {
        alert("E ���� �ּҸ� ���������� �Է��� �ֽñ� �ٶ��ϴ�.")
        f.email.focus();
        return false;
    }

    if (f.email.value.lastIndexOf("@") == -1) {
        alert("E ���� �ּҸ� ���������� �Է��� �ֽñ� �ٶ��ϴ�.")
        f.email.focus();
        return false;
    }

    form.MlangBody.value = form.innerHTML;
    return true;
}
</script>

<style>
body, input, select, submit {color: black; font-size: 9pt; font-family: ����; word-break: break-all;}
td, table {border-color: #7C7C7C; border-collapse: collapse; font-size: 9pt;}

.TDF {font-size: 9pt; background-color: #429EB2; font: bold; color: #FFFFFF; font-family: ����; word-break: break-all;}
.TDF2 {font-size: 9pt; background-color: #FFDFEF; font: bold; color: #000000; font-family: ����; word-break: break-all;}
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