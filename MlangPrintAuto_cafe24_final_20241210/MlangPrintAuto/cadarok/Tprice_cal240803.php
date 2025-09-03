<?php
function ERROR() {
    echo "<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>";
}

if (isset($TRYCobe) && $TRYCobe == "ok") {
    if ($TypeOne == "========" || $TypeTwo == "========" || $TypeThree == "========") {
        ERROR();
        exit;
    }

    $Ttable = "cadarok";
    include "../../db.php";

    // Sanitize input
    $TypeOne = mysqli_real_escape_string($db, $TypeOne);
    $TypeTwo = mysqli_real_escape_string($db, $TypeTwo);
    $TypeThree = mysqli_real_escape_string($db, $TypeThree);

    $stmt = $db->prepare("SELECT * FROM MlangPrintAuto_{$Ttable} WHERE style = ? AND Section = ? AND TreeSelect = ? ORDER BY quantity ASC");
    $stmt->bind_param("sss", $TypeOne, $TypeTwo, $TypeThree);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;
?>
<script>
    var obj = parent.document.forms["choiceForm"].MY_amount;
    for (var i = obj.options.length - 1; i >= 0; i--) {
        obj.options[i] = null;
    }

    <?php
    if ($rows) {
        $g = 0;
        while ($row = $result->fetch_assoc()) {
    ?>
    obj.options[<?= $g ?>] = new Option('<?= $row['quantity'] == "9999" ? "기타" : $row['quantity'] . "부" ?>', '<?= $row['quantity'] ?>');
    <?php
            $g++;
        }
    }
    ?>
</script>
<?php
    $stmt->close();
    $db->close();
}
?>
