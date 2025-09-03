<?php
function ERROR(){
    echo("<script>alert('필수 정보를 입력해 주세요.');</script>");
}

if ($TRYCobe == "ok") {
    if ($TypeOne == "========" || $TypeTwo == "========" || $TypeTree == "========") {
        ERROR();
        exit;
    }

    $Ttable = "cadarokTwo";
    include "../../db.php";

    // Use mysqli and prepared statements to safely interact with the database
    $stmt = $db->prepare("SELECT * FROM MlangPrintAuto_$Ttable WHERE style=? AND Section=? AND TreeSelect=? ORDER BY quantity ASC");
    $stmt->bind_param("sss", $TypeOne, $TypeTwo, $TypeTree);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;
?>
<script>
    var obj = parent.document.forms["choiceForm"].MY_amount;
    var i;

    // Clear previous options
    for (i = parent.document.forms["choiceForm"].MY_amount.options.length; i >= 0; i--) {
        parent.document.forms["choiceForm"].MY_amount.options[i] = null;
    }

    // Populate new options
    <?php if ($rows) {
        $g = 0;
        while ($row = $result->fetch_assoc()) {
            $quantity = htmlspecialchars($row['quantity'], ENT_QUOTES, 'UTF-8');
            $displayQuantity = ($row['quantity'] == "9999") ? '기타' : $quantity . '부';
            echo "obj.options[$g] = new Option('$displayQuantity', '$quantity');";
            $g++;
        }
    } ?>
</script>
<?php
    // Close the statement and connection
    $stmt->close();
    $db->close();
}
?>
