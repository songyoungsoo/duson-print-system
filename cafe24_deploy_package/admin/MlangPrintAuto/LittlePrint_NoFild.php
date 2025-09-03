<?php
include "../../db.php";
$db = new mysqli($host, $user, $password, $dataname);
$no = intval($_GET['no']);

$T_DirUrl = "../../MlangPrintAuto";

// 데이터베이스 연결 설정
include "$T_DirUrl/ConDb.php";

$MlangPrintAutoFild_result = mysqli_query($db, "SELECT * FROM MlangPrintAuto_LittlePrint WHERE no='$no'");

if ($MlangPrintAutoFild_row = mysqli_fetch_array($MlangPrintAutoFild_result)) {

    $MlangPrintAutoFildView_style = $MlangPrintAutoFild_row['style'];
    $MlangPrintAutoFildView_Section = $MlangPrintAutoFild_row['Section'];
    $MlangPrintAutoFildView_quantity = $MlangPrintAutoFild_row['quantity'];
    $MlangPrintAutoFildView_money = $MlangPrintAutoFild_row['money'];
    $MlangPrintAutoFildView_TreeSelect = $MlangPrintAutoFild_row['TreeSelect'];
    $MlangPrintAutoFildView_DesignMoney = $MlangPrintAutoFild_row['DesignMoney'];
    $MlangPrintAutoFildView_POtype = $MlangPrintAutoFild_row['POtype'];
    $MlangPrintAutoFildView_quantityTwo = $MlangPrintAutoFild_row['quantityTwo'];

} else {
    echo ("<script language=javascript>
        window.alert('DB ERROR - 번호: $no 에 해당하는 데이터가 없거나 DB 오류입니다.');
        window.self.close();
        </script>");
    exit;
}
?>
