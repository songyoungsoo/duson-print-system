<?php
define('DB_ACCESS_ALLOWED', true);
include "../../db.php";
// $db 변수는 db.php에서 이미 연결되어 제공됨
$no = intval($_GET['no']);
$query = "SELECT * FROM MlangPrintAuto_envelope WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$MlangPrintAutoFild_row = mysqli_fetch_assoc($result);

if ($MlangPrintAutoFild_row) {
    $MlangPrintAutoFildView_style = $MlangPrintAutoFild_row['style'] ?? '';
    $MlangPrintAutoFildView_Section = $MlangPrintAutoFild_row['Section'] ?? '';
    $MlangPrintAutoFildView_quantity = $MlangPrintAutoFild_row['quantity'] ?? '';
    $MlangPrintAutoFildView_money = $MlangPrintAutoFild_row['money'] ?? '';
    $MlangPrintAutoFildView_TreeSelect = $MlangPrintAutoFild_row['TreeSelect'] ?? ''; // ✅ 추가 확인
    $MlangPrintAutoFildView_DesignMoney = $MlangPrintAutoFild_row['DesignMoney'] ?? '';
    $MlangPrintAutoFildView_POtype = $MlangPrintAutoFild_row['POtype'] ?? '';
    $MlangPrintAutoFildView_quantityTwo = $MlangPrintAutoFild_row['quantityTwo'] ?? ''; // ✅ 추가 확인
} else {
    echo ("<script language='javascript'>
        window.alert('ERROR - 번호: $no에 해당하는 자료가 없거나 DB 에러입니다.');
        window.self.close();
    </script>");
    exit;
}
