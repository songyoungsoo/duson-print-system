<?php
include "../../db.php";
global $db;

if (!$db) {
    die("Database connection error: " . mysqli_connect_error());
}

$Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0'");
$Cate_rows = mysqli_num_rows($Cate_result);
if ($Cate_rows) {
    $m = 1;
    while ($Cate_row = mysqli_fetch_array($Cate_result)) {
?>
        case '<?= htmlspecialchars($Cate_row['no'], ENT_QUOTES, 'UTF-8') ?>': 
<?php
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND TreeNo='{$Cate_row['no']}' ORDER BY no ASC");
        $rows = mysqli_num_rows($result);
        if ($rows) {
            $g = 0;
            while ($row = mysqli_fetch_array($result)) {
?>
                objTwo.options[<?= $g ?>] = new Option('<?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($row['no'], ENT_QUOTES, 'UTF-8') ?>');
<?php
                $g++;
            }
        }
?>

<?php
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='{$Cate_row['no']}' ORDER BY no ASC");
        $rows = mysqli_num_rows($result);
        if ($rows) {
            $g = 0;
            while ($row = mysqli_fetch_array($result)) {
?>
                objOne.options[<?= $g ?>] = new Option('<?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($row['no'], ENT_QUOTES, 'UTF-8') ?>');
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
            // default:
            // obj.length = 1;
            // return;
        }
}

function change_Field(getVal) {
    var objOne = document.choiceForm.MY_Fsd;
    var objTwo = document.choiceForm.PN_type;
    var i;

    for (i = document.choiceForm.MY_Fsd.options.length; i >= 0; i--) {
        document.choiceForm.MY_Fsd.options[i] = null; 
    }

    for (i = document.choiceForm.PN_type.options.length; i >= 0; i--) {
        document.choiceForm.PN_type.options[i] = null; 
    }

    switch (getVal) {
        // The generated PHP cases will be inserted here
    }
}

function calc_re() {
    var T = document.choiceForm;
    var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
    var TYU = T.MY_Fsd.options[T.MY_Fsd.selectedIndex].value;
    var TYUO = T.PN_type.options[T.PN_type.selectedIndex].value;

    Tcal.document.location.href = 'Tprice_cal.php?TypeOne=' + TY + '&TypeTwo=' + TYU + '&TypeTree=' + TYUO + '&TRYCobe=ok';
}
