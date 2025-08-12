<script type="text/javascript">
        function change_Field(getVal) {
            var objTwo = document.choiceForm.PN_type;
            var i;

            for (i = document.choiceForm.PN_type.options.length; i >= 0; i--) {
                document.choiceForm.PN_type.options[i] = null;
            }

            switch (getVal) {
                <?php
// MySQL 데이터베이스에 연결 설정
$mysqli = new mysqli($host, $user, $password, $dataname);

// 데이터베이스 연결 확인
if ($mysqli->connect_error) {
    // 연결 실패 시 오류 메시지 출력 후 종료
    die("연결 실패: " . $mysqli->connect_error);
}
$GGTABLE = "mlangprintauto_transactioncate";  // 실제 테이블명으로 변경하세요
$page = isset($page) ? $page : "envelope";  // $page가 설정되지 않은 경우 기본값 설정
// 쿼리 실행: BigNo가 0인 데이터를 조회
$Cate_result = $mysqli->query("SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0'");

if ($Cate_result->num_rows > 0) {
    // 카테고리가 있는 경우 반복문 실행
    $m = 1;
    while ($Cate_row = $Cate_result->fetch_assoc()) {
        ?>
        // 자바스크립트의 case 문으로 PHP 데이터 전달
        case '<?= $Cate_row['no'] ?>':
            <?php
            // 선택된 카테고리에 대한 하위 항목들을 조회
            $result = $mysqli->query("SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='{$Cate_row['no']}' ORDER BY no ASC");
            if ($result->num_rows > 0) {
                $g = 0;
                // 조회된 하위 항목들을 자바스크립트 옵션으로 추가
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
        $m++;
    }
}

// 데이터베이스 연결 종료
$mysqli->close();
?>
            }
        }

        function calc_re() {
            var T = document.choiceForm;
            var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
            var TYU = T.PN_type.options[T.PN_type.selectedIndex].value;
            var TYUK = T.POtype.options[T.POtype.selectedIndex].value;

            Tcal.document.location.href = 'Tprice_cal.php?TypeOne=' + TY + '&TypeTwo=' + TYU + '&TypeTree=' + TYUK + '&TRYCobe=ok';
        }
</script> 