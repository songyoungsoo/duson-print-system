<?php
// 데이터베이스 연결
include "../../db.php";

// 데이터베이스에서 레코드 가져오기
$stmt = $db->prepare("SELECT * FROM MlangPrintAuto_sticker WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$MlangPrintAutoFild_row = $result->fetch_assoc();

if ($MlangPrintAutoFild_row) {
    $MlangPrintAutoFildView_style = $MlangPrintAutoFild_row['style'];
    $MlangPrintAutoFildView_Section = $MlangPrintAutoFild_row['Section'];
    $MlangPrintAutoFildView_quantity = $MlangPrintAutoFild_row['quantity'];
    $MlangPrintAutoFildView_money = $MlangPrintAutoFild_row['money'];
    $MlangPrintAutoFildView_TreeSelect = $MlangPrintAutoFild_row['TreeSelect'];
    $MlangPrintAutoFildView_DesignMoney = $MlangPrintAutoFild_row['DesignMoney'];
    $MlangPrintAutoFildView_POtype = $MlangPrintAutoFild_row['POtype'];
    $MlangPrintAutoFildView_quantityTwo = $MlangPrintAutoFild_row['quantityTwo'];
} else {
    echo ("<script language='javascript'>
        window.alert('ERROR - 번호: $no 에 해당하는 데이터가 없거나 DB 오류가 발생했습니다.');
        window.self.close();
        </script>");
    exit;
}
?>
