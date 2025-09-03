<script type="text/javascript">
function change_Field(getVal) {
    var objOne = document.choiceForm.MY_Fsd;
    var objTwo = document.choiceForm.PN_type;
    var i;

    // MY_Fsd 옵션 제거
    for (i = objOne.options.length - 1; i >= 0; i--) {
        objOne.options[i] = null; 
    }

    // PN_type 옵션 제거
    for (i = objTwo.options.length - 1; i >= 0; i--) {
        objTwo.options[i] = null; 
    }

    switch (getVal) {
        <?php
        include "../../db.php";
        $Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0'");
        $Cate_rows = mysqli_num_rows($Cate_result);

        if ($Cate_rows) {
            $m = 1;
            while ($Cate_row = mysqli_fetch_array($Cate_result)) {
        ?>
        case '<?= $Cate_row['no'] ?>':
            // 기본 선택 옵션 추가
            objTwo.options[0] = new Option(':: 선택 ::', '');

            <?php
 $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND TreeNo='" . $Cate_row['no'] . "' ORDER BY no ASC");

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

            // MY_Fsd 기본 선택 옵션 추가
            objOne.options[0] = new Option(':: 선택 ::', '');

            <?php
$result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='" . $Cate_row['no'] . "' ORDER BY no ASC");

            $rows = mysqli_num_rows($result);
            if ($rows) {
                $g = 1;
                while ($row = mysqli_fetch_array($result)) {
            ?>
            objOne.options[<?= $g ?>] = new Option('<?= $row['title'] ?>', '<?= $row['no'] ?>');
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

//////////----------------------------------------------------------------------------------------------------////////////////
function calc_re() {
    var T = document.choiceForm;
    var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
    var TYU = T.MY_Fsd.options[T.MY_Fsd.selectedIndex].value;
    var TYUO = T.PN_type.options[T.PN_type.selectedIndex].value;

    Tcal.document.location.href = 'Tprice_cal.php?TypeOne=' + TY + '&TypeTwo=' + TYU + '&TypeTree=' + TYUO + '&TRYCobe=ok';
}
</script>
