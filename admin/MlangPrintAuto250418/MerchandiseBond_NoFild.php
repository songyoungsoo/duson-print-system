<?php
include "../../db.php";

$no = isset($_GET['no']) ? intval($_GET['no']) : 0;

$stmt = $db->prepare("SELECT * FROM MlangPrintAuto_MerchandiseBond WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$MlangPrintAutoFild_row = $result->fetch_assoc();

if ($MlangPrintAutoFild_row) {
    $MlangPrintAutoFildView_style = htmlspecialchars($MlangPrintAutoFild_row['style']);
    $MlangPrintAutoFildView_Section = htmlspecialchars($MlangPrintAutoFild_row['Section']);
    $MlangPrintAutoFildView_quantity = htmlspecialchars($MlangPrintAutoFild_row['quantity']);
    $MlangPrintAutoFildView_money = htmlspecialchars($MlangPrintAutoFild_row['money']);
    $MlangPrintAutoFildView_TreeSelect = $MlangPrintAutoFild_row['TreeSelect'] ?? ''; // ✅ 추가 확인
    $MlangPrintAutoFildView_DesignMoney = htmlspecialchars($MlangPrintAutoFild_row['DesignMoney']);
    $MlangPrintAutoFildView_POtype = htmlspecialchars($MlangPrintAutoFild_row['POtype']);
    $MlangPrintAutoFildView_quantityTwo = $MlangPrintAutoFild_row['quantityTwo'] ?? ''; // ✅ 추가 확인
} else {
    echo ("<script>
        alert('ERROR - 데이터 번호: $no에 해당하는 자료가 없거나 DB 오류가 발생했습니다.');
        window.self.close();
    </script>");
    exit;
}

$stmt->close();
$db->close();
?>
