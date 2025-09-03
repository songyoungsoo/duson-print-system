<script type="text/javascript">
function change_Field(getVal) {
    var objTwo = document.choiceForm.PN_type;
    var i;
    // 기존 옵션 제거
    for (i = objTwo.options.length - 1; i >= 0; i--) {
        objTwo.options[i] = null; 
    }
    // 카테고리 값에 따른 옵션 추가
    switch (getVal) {
        <?php
        include "../../db.php";
    // 더 이상 사용되지 않는 mysql_* 함수 대신 mysqli_* 함수를 사용하도록 보장
        $Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0'");
        $Cate_rows = mysqli_num_rows($Cate_result);

        if ($Cate_rows) {
            $m = 1;
            while ($Cate_row = mysqli_fetch_array($Cate_result)) {
        ?>
        case '<?= $Cate_row['no'] ?>': 
            objTwo.options[0] = new Option('::: 선택 :::', '#');
            <?php
            $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='$Cate_row[no]' ORDER BY no ASC");
            $rows = mysqli_num_rows($result);
            if ($rows) {
                $g = 1;
                while ($row = mysqli_fetch_array($result)) { 
            ?>
            objTwo.options[<?= $g ?>] = new Option('<?= $row['title'] ?>', '<?= $row['no'] ?>');
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
    }
}
    // 옵션이 선택된 후 결과 계산 페이지 호출

function calc_re() {
    var T = document.choiceForm;
    var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
    var TYU = T.PN_type.options[T.PN_type.selectedIndex].value;

    Tcal.document.location.href = 'Tprice_cal.php?TypeOne=' + TY + '&TypeTwo=' + TYU + '&TRYCobe=ok';
}
// function calc_re() {
// var T = document.choiceForm;
// var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
// var TYU = T.PN_type.options[T.PN_type.selectedIndex].value;
// var TYUOK = T.POtype.options[T.POtype.selectedIndex].value;

// Tcal.document.location.href = 'Tprice_cal.php?TypeOne=' + TY + '&TypeTwo=' + TYU + '&TypeTree=' + TYUOK + '&TRYCobe=ok';
// }
</script>
