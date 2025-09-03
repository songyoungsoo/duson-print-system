<?php
// GET 파라미터 처리
$typeOne = $_GET['TypeOne'] ?? '';
$typeTwo = $_GET['TypeTwo'] ?? '';
$typeTree = $_GET['TypeTree'] ?? '';
$typeFour = $_GET['TypeFour'] ?? '';
$tryCobe = $_GET['TRYCobe'] ?? '';

function ERROR() {
    echo "<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>";
}

// TRYCobe 값이 'ok'일 때만 실행
if ($tryCobe === "ok") {
    // 필수 값이 설정되지 않으면 오류 출력 후 실행 중지
    if ($typeOne === "========" || $typeTwo === "========") {
        ERROR();
        return;  // 오류 발생 시 쿼리 실행 중지
    }

    // 데이터베이스 연결
    $Ttable = "namecard";
    include "../../db.php";

    // 연결 오류 처리
    if (mysqli_connect_errno()) {
        echo "<script>alert('데이터베이스 연결에 실패했습니다.');</script>";
        return;
    }

    // 쿼리 실행
    $query = "SELECT * FROM MlangPrintAuto_$Ttable 
              WHERE style='$typeOne' AND Section='$typeTwo' 
              AND POtype='$typeTree' 
              ORDER BY CAST(quantity AS UNSIGNED) ASC";
    $result = mysqli_query($db, $query);
    $rows = mysqli_num_rows($result);
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
    // 쿼리 결과 출력 확인 (디버깅용)
    var_dump($rows);

    // 데이터베이스 연결 종료
    mysqli_close($db);
}
?>
