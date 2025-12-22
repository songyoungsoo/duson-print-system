function change_Field(getVal) {
var objTwo = document.choiceForm.PN_type;
var i;
    // 기존 옵션 제거
    for (i = objTwo.options.length - 1; i >= 0; i--) {
        objTwo.options[i] = null; 
    }
switch (getVal) {

<?php
include "../../db.php";
// mysqli 쿼리 실행
// $TABLE = "mlangprintauto_transactioncate"; // $GGTABLE이 어디에서 정의되었는지 확인 필요
// $page = 'namecard';    // $page가 어디에서 오는지 확인 필요

$Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0'");
$Cate_rows = mysqli_num_rows($Cate_result);
if ($Cate_rows) {
    $m = 1;
    while ($Cate_row = mysqli_fetch_assoc($Cate_result)) 
    {
?>

case '<?=$Cate_row['no']?>': 
objTwo.options[0] = new Option(':::선택:::', '#');

<?php
$result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='{$Cate_row['no']}' ORDER BY no ASC");
$rows = mysqli_num_rows($result);
if ($rows > 0) {
    $g = 1;
    while ($row = mysqli_fetch_assoc($result)) {
?>

objTwo.options[<?=$g?>] = new Option('<?=$row['title']?>', '<?=$row['no']?>');

<?php
        $g++;
    }
}
?>

return;

<?php
    $m++;
    }
}

mysqli_close($db);
?>

     default:
     obj.length = 1;
     return;
}
}

//////////----------------------------------------------------------------------------------------------------////////////////

function calc_re() {
var T = document.choiceForm;
var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
var TYU = T.PN_type.options[T.PN_type.selectedIndex].value;
var TYUOK = T.POtype.options[T.POtype.selectedIndex].value;

Tcal.document.location.href = 'Tprice_cal.php?TypeOne=' + TY + '&TypeTwo=' + TYU + '&TypeTree=' + TYUOK + '&TRYCobe=ok';
}
