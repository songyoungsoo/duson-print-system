<?php
include "../../db.php"; // 여기에 $db 연결이 있어야 함
$db = new mysqli($host, $user, $password, $dataname);

$no = intval($_GET['no']); // Ensure the 'no' parameter is retrieved from the GET request and cast to an integer

$query = "SELECT * FROM MlangPrintAuto_cadarokTwo WHERE no = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $no);
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
        window.alert('에러 - 번호: $no에 해당하는 자료가 없거나 DB 에러입니다.');
        window.self.close();
    </script>");
    exit;
}

$stmt->close();
$db->close();
?>