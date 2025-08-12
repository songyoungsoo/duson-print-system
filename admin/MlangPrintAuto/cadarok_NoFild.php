<?php
include "../../db.php";
$db = new mysqli($host, $user, $password, $dataname);
$no = intval($_GET['no']);
$MlangPrintAutoFild_result = mysqli_query($db, "SELECT * FROM MlangPrintAuto_cadarok WHERE no='$no'");
$MlangPrintAutoFild_row = mysqli_fetch_array($MlangPrintAutoFild_result);

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
            window.alert('▒ ERROR - 등록번호: $no 번에 관련된 자료가 없거나 DB 에러일수 있습니다.');
            window.self.close();
           </script>");
    exit;
}
?>