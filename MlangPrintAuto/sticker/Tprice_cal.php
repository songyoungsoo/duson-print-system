<?php
// GET 파라미터 처리
$typeOne = $_GET['TypeOne'] ?? '';
$typeTwo = $_GET['TypeTwo'] ?? '';
$typeTree = $_GET['TypeTree'] ?? '';
$typeFour = $_GET['TypeFour'] ?? '';
$tryCobe = $_GET['TRYCobe'] ?? '';
function ERROR() {
    echo("<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>");
}

if ($TRYCobe == "ok") {
    if ($TypeOne == "========") { ERROR(); }
    else if ($TypeTwo == "========") { ERROR(); }

    $Ttable = "msticker";
    include "../../db.php";

    // 최신 MySQLi 사용
    $stmt = $db->prepare("SELECT * FROM MlangPrintAuto_? WHERE style = ? AND Section = ? ORDER BY quantity ASC");
    $stmt->bind_param("sss", $Ttable, $TypeOne, $TypeTwo);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;
?>

<script>
    // MY_amount 필드를 참조하여 기존 옵션을 제거
    var obj = parent.document.forms["choiceForm"].MY_amount;

    // 기존 옵션 제거
    while (obj.options.length > 0) {
        obj.remove(0);
    }

    // PHP 부분: 데이터베이스 쿼리 결과가 있는 경우 새로운 옵션 추가
    <?php if ($rows > 0): ?>
        <?php
        $g = 0;
        while ($row = mysqli_fetch_assoc($result)): 
        ?>
            // 새로운 옵션을 추가
            obj.options[<?= $g ?>] = new Option('<?= $row['quantity'] ?>매', '<?= $row['quantity'] ?>');
            <?php $g++; ?>
        <?php endwhile; ?>
    <?php endif; ?>
</script>

<?php
    $stmt->close();
    $db->close();
}
?>
