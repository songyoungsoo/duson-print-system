<?php
include "../../db.php"; // 데이터베이스 연결

function change_Field($getVal) {
    global $db, $GGTABLE, $page;
    echo "<script>
    var objOne = document.choiceForm.MY_Fsd;
    var objTwo = document.choiceForm.PN_type;
    var i;

    for (i = document.choiceForm.MY_Fsd.options.length; i >= 0; i--) {
        document.choiceForm.MY_Fsd.options[i] = null;
    }

    for (i = document.choiceForm.PN_type.options.length; i >= 0; i--) {
        document.choiceForm.PN_type.options[i] = null;
    }

    switch ('$getVal') {";

    $stmt = $db->prepare("SELECT * FROM $GGTABLE WHERE Ttable=? AND BigNo='0'");
    $stmt->bind_param("s", $page);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($Cate_row = $result->fetch_assoc()) {
            echo "case '{$Cate_row['no']}':\n";

            $stmt_inner = $db->prepare("SELECT * FROM $GGTABLE WHERE Ttable=? AND TreeNo=? ORDER BY no ASC");
            $stmt_inner->bind_param("ss", $page, $Cate_row['no']);
            $stmt_inner->execute();
            $result_inner = $stmt_inner->get_result();

            if ($result_inner->num_rows > 0) {
                $g = 0;
                while ($row = $result_inner->fetch_assoc()) {
                    echo "objOne.options[$g] = new Option('{$row['title']}', '{$row['no']}');\n";
                    $g++;
                }
            }

            $stmt_inner = $db->prepare("SELECT * FROM $GGTABLE WHERE Ttable=? AND BigNo=? ORDER BY no ASC");
            $stmt_inner->bind_param("ss", $page, $Cate_row['no']);
            $stmt_inner->execute();
            $result_inner = $stmt_inner->get_result();

            if ($result_inner->num_rows > 0) {
                $g = 0;
                while ($row = $result_inner->fetch_assoc()) {
                    echo "objTwo.options[$g] = new Option('{$row['title']}', '{$row['no']}');\n";
                    $g++;
                }
            }
            echo "return;\n";
        }
    }

    echo "}
    </script>";
}
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
function calc_re() {
    var T = document.choiceForm;
    var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
    var TYU = T.PN_type.options[T.PN_type.selectedIndex].value;
    var TYUO = T.MY_Fsd.options[T.MY_Fsd.selectedIndex].value;
    var TYUOK = T.POtype.options[T.POtype.selectedIndex].value;

    Tcal.document.location.href = 'Tprice_cal.php?TypeOne=' + TY + '&TypeTwo=' + TYU + '&TypeTree=' + TYUO + '&TypeFour=' + TYUOK + '&TRYCobe=ok';
}
});
</script>
