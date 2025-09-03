<?php
$TreeSelect = $_GET['TreeSelect'] ?? $_POST['TreeSelect'] ?? '0';
$Cate = $_GET['Cate'] ?? $_POST['Cate'] ?? '';
$PageCode = $_GET['PageCode'] ?? $_POST['PageCode'] ?? '';
$Ttable = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
$TIO_CODE = $_GET['TIO_CODE'] ?? $_POST['TIO_CODE'] ?? '';
$Ttable = $Ttable ?: $TIO_CODE;
// 검색 조건 처리
$search = $_POST['search'] ?? $_GET['search'] ?? '';
$RadOne = $_POST['RadOne'] ?? $_GET['RadOne'] ?? '';
$myListTreeSelect = $_POST['myListTreeSelect'] ?? $_GET['myListTreeSelect'] ?? '';
$myList = $_POST['myList'] ?? $_GET['myList'] ?? '';
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : (isset($_GET['offset']) ? (int)$_GET['offset'] : 0);
$no = isset($_GET['no']) ? (int)$_GET['no'] : (isset($_POST['no']) ? (int)$_POST['no'] : 0);
$PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF'] ?? '');

include "CateAdmin_title.php";
$TtableTitles = include 'table_title_config.php';
?>

<?php
$treeSelectMax = [
  "inserted" => 2,
  "sticker" => 1,
  "namecard" => 1,
  "envelope" => 1,
  "ncrflambeau" => 2,
  "cadarok" => 2,
  "cadarokTwo" => 2,
  "littleprint" => 2,
  "merchandisebond" => 1
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
