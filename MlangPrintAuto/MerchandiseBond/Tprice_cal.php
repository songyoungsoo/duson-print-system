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
function ERROR() {
    echo("<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>");
}

// $TypeOne = $_GET['TypeOne'] ?? ''; // TypeOne parameter value
// $TypeTwo = $_GET['TypeTwo'] ?? ''; // TypeTwo parameter value
// $TRYCobe = $_GET['TRYCobe'] ?? ''; // TRYCobe parameter value

if (isset($TRYCobe) && $TRYCobe === "ok") {
    if ($TypeOne === "========" || $TypeTwo === "========") {
        ERROR();
        exit();
    }

    $Ttable = "MerchandiseBond";
    include "../../db.php";
    $mysqli = new mysqli($host, $user, $password, $dataname);
    // Prepare the SQL statement
    $stmt = $mysqli->prepare("SELECT quantity FROM MlangPrintAuto_? WHERE style=? AND Section=? ORDER BY quantity ASC");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($mysqli->error));
    }

    // Bind parameters
    $stmt->bind_param("sss", $Ttable, $TypeOne, $TypeTwo);

    // Execute the statement
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    // Get the result
    $result = $stmt->get_result();
    $rows = $result->num_rows;
?>

<script>
    var obj = parent.document.forms["choiceForm"].MY_amount;

    // Clear existing options
    obj.options.length = 0;

    <?php
    if ($rows > 0) {
        $g = 0;
        while ($row = $result->fetch_assoc()) {
            echo "obj.options[$g] = new Option('{$row['quantity']}', '{$row['quantity']}');";
            $g++;
        }
    }
    ?>
</script>

<?php
    $stmt->close();
    $mysqli->close();
}
?>
