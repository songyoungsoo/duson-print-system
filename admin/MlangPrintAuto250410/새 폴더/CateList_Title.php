<?php
$Cate = $_GET['Cate'] ?? $_POST['Cate'] ?? '';
$PageCode = $_GET['PageCode'] ?? $_POST['PageCode'] ?? '';
$Ttable = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
$TIO_CODE = $_GET['TIO_CODE'] ?? $_POST['TIO_CODE'] ?? '';
$Ttable = $Ttable ?: $TIO_CODE;
// 검색 조건 처리
$search = $_POST['search'] ?? '';
$RadOne = $_POST['RadOne'] ?? '';
$myListTreeSelect = $_POST['myListTreeSelect'] ?? '';
$myList = $_POST['myList'] ?? '';
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
// include "CateAdmin_title.php";
include 'table_title_config.php';
// 타이틀 설정
$DF_Tatle_1 = $DF_Tatle_2 = $DF_Tatle_3 = '';
if (isset($TtableTitles[$Ttable])) {
  $DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
  $DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
  $DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';
}
?>

<?php
$treeSelectMax = [
  "inserted" => 2,
  "sticker" => 1,
  "NameCard" => 1,
  "envelope" => 1,
  "NcrFlambeau" => 2,
  "cadarok" => 2,
  "cadarokTwo" => 2,
  "LittlePrint" => 2,
  "MerchandiseBond" => 1
];

if (isset($treeSelectMax[$Ttable])) {
  echo "<input type='button' onClick=\"javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable={$Ttable}" . ($Cate ? "&Cate={$Cate}" : "") . "', 'WebOffice_{$PageCode}Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();\" value=' {$DF_Tatle_1} 입력 ' >\n";

  for ($i = 1; $i <= $treeSelectMax[$Ttable]; $i++) {
    echo "<input type='button' onClick=\"javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable={$Ttable}&TreeSelect={$i}" . ($Cate ? "&Cate={$Cate}" : "") . "', 'WebOffice_Tree{$PageCode}Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();\" value=' ";
    $dfTitleVar = "DF_Tatle_" . ($i + 1);
    echo ${$dfTitleVar} . " 입력 ' >\n";
  }
}
?>
