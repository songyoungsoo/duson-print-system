<!DOCTYPE html>
<html>
<head>
    <script>
        function change_Field(getVal) {
            var objTwo = document.choiceForm.PN_type;
            var i;

            for (i = document.choiceForm.PN_type.options.length - 1; i >= 0; i--) {
                document.choiceForm.PN_type.options[i] = null; 
            }

            switch (getVal) {
                <?php
                include "../../db.php";
                $Cate_result = $mysqli->query("SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0'");
                if ($Cate_result->num_rows > 0) {
                    while ($Cate_row = $Cate_result->fetch_assoc()) {
                        ?>
                        case '<?= $Cate_row['no'] ?>':
                            <?php
                            $result = $mysqli->query("SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='{$Cate_row['no']}' ORDER BY no ASC");
                            if ($result->num_rows > 0) {
                                $g = 0;
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    objTwo.options[<?= $g ?>] = new Option('<?= $row['title'] ?>', '<?= $row['no'] ?>');
                                    <?php
                                    $g++;
                                }
                            }
                            ?>
                            return;
                        <?php
                    }
                }
                $mysqli->close();
                ?>
            }
        }

        function calc_re() {
            var T = document.choiceForm;
            var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
            var TYU = T.PN_type.options[T.PN_type.selectedIndex].value;

            Tcal.document.location.href = 'Tprice_cal.php?TypeOne=' + TY + '&TypeTwo=' + TYU + '&TRYCobe=ok';
        }
    </script>
