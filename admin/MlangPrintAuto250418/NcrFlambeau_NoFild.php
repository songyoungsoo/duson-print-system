<?php
// 안전하게 숫자형으로 변환 (기본값 0)
$no = isset($no) ? (int)$no : 0;

$query = "SELECT * FROM MlangPrintAuto_NcrFlambeau WHERE no = '$no'";
$result = mysqli_query($db, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $MlangPrintAutoFildView_style         = $row['style'];
    $MlangPrintAutoFildView_Section       = $row['Section'];
    $MlangPrintAutoFildView_quantity      = $row['quantity'];
    $MlangPrintAutoFildView_money         = $row['money'];
    $MlangPrintAutoFildView_TreeSelect    = $row['TreeSelect'];
    $MlangPrintAutoFildView_DesignMoney   = $row['DesignMoney'];
    $MlangPrintAutoFildView_POtype        = $row['POtype'];
    $MlangPrintAutoFildView_quantityTwo   = $row['quantityTwo'];
} else {
    echo ("<script>
        alert('▒ ERROR - 등록번호: {$no} 번에 관련된 자료가 없거나 DB 에러일 수 있습니다.');
        window.self.close();
    </script>");
    exit;
}
?>
