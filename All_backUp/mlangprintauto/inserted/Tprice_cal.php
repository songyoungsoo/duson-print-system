<?php
$typeOne = isset($_GET['TypeOne']) ? $_GET['TypeOne'] : '';
$typeTwo = isset($_GET['TypeTwo']) ? $_GET['TypeTwo'] : '';
$typeTree = isset($_GET['TypeTree']) ? $_GET['TypeTree'] : '';
$typeFour = isset($_GET['TypeFour']) ? $_GET['TypeFour'] : '';
$tryCobe = isset($_GET['TRYCobe']) ? $_GET['TRYCobe'] : '';

// 예: 값을 출력하여 확인
echo "TypeOne: " . htmlspecialchars($typeOne) . "<br>";
echo "TypeTwo: " . htmlspecialchars($typeTwo) . "<br>";
echo "TypeTree: " . htmlspecialchars($typeTree) . "<br>";
echo "TypeFour: " . htmlspecialchars($typeFour) . "<br>";
echo "TRYCobe: " . htmlspecialchars($tryCobe) . "<br>";
function ERROR(){
    echo("<script>alert('필수 정보를 입력해 주세요.');</script>");
}
if(isset($_GET['TRYCobe']) && $_GET['TRYCobe'] == "ok"){
    $TypeOne = $_GET['TypeOne'] ?? "========";
    $TypeTwo = $_GET['TypeTwo'] ?? "========";
    $TypeTree = $_GET['TypeTree'] ?? "========";
    $TypeFour = $_GET['TypeFour'] ?? "";
    
    if($TypeOne == "========" || $TypeTwo == "========" || $TypeTree == "========") {
        ERROR();
    }

    $Ttable = "inserted";
    $stmt = $db->prepare("SELECT * FROM mlangprintauto_$Ttable WHERE style=? AND Section=? AND TreeSelect=? AND POtype=? ORDER BY quantity ASC");
    $stmt->bind_param("ssss", $TypeOne, $TypeTwo, $TypeTree, $TypeFour);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;
    ?>

    <script>
        var obj = parent.document.forms["choiceForm"].MY_amount;
        var i;

        for (i = parent.document.forms["choiceForm"].MY_amount.options.length; i >= 0; i--) {
            parent.document.forms["choiceForm"].MY_amount.options[i] = null; 
        }

        <?php
        if($rows){
            $g = 0;
            while($row = $result->fetch_assoc()) {
                ?>
                obj.options[<?=$g?>] = new Option('<?=$row['quantity']?>연', '<?=$row['quantity']?>');
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