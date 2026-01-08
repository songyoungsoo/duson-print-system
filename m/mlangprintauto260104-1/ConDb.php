<?php
$DhtmlTopFos = "430";
$DhtmlLeftFos = "180";

$TABLE = "mlangprintauto_transactioncate";
$GGTABLE = "mlangprintauto_transactioncate";  // envelope_Script.php에서 사용
$GGTABLESu = "mlangprintauto_SuCate";

$ConDb_A = "전단지:스티카:명함:상품권:봉투:양식지:리플렛:소량인쇄:카다로그:학원:음식:기업체:의류:상업:교회:비영리:기타";

$ListXTtable = $_POST['ListXTtable'] ?? null;
// $Ttable = $_POST['Ttable'] ?? null;
$Ttable = isset($_GET['Ttable']) ? $_GET['Ttable'] : (isset($_POST['Ttable']) ? $_POST['Ttable'] : '');
$ToTitle = $_POST['ToTitle'] ?? null;

$View_TtableA = "";
$View_TtableB = "";
$View_TtableC = "";

if ($ListXTtable == "0" || $Ttable == "inserted" || $ToTitle == "전단지") {
    $View_TtableA = "0";
    $View_TtableB = "inserted";
    $View_TtableC = "전단지";
}
if ($ListXTtable == "1" || $Ttable == "msticker" || $ToTitle == "스티카") {
    $View_TtableA = "1";
    $View_TtableB = "msticker";
    $View_TtableC = "스티카";
}
if ($ListXTtable == "1.5" || $Ttable == "sticker" || $ToTitle == "스티커") {
    $View_TtableA = "1.5";
    $View_TtableB = "sticker";
    $View_TtableC = "스티커";
}
if ($ListXTtable == "2" || $Ttable == "NameCard" || $ToTitle == "명함") {
    $View_TtableA = "2";
    $View_TtableB = "NameCard";
    $View_TtableC = "명함";
}
if ($ListXTtable == "3" || $Ttable == "MerchandiseBond" || $ToTitle == "상품권") {
    $View_TtableA = "3";
    $View_TtableB = "MerchandiseBond";
    $View_TtableC = "상품권";
}
if ($ListXTtable == "4" || $Ttable == "envelope" || $ToTitle == "봉투") {
    $View_TtableA = "4";
    $View_TtableB = "envelope";
    $View_TtableC = "봉투";
}
if ($ListXTtable == "5" || $Ttable == "NcrFlambeau" || $ToTitle == "양식지") {
    $View_TtableA = "5";
    $View_TtableB = "NcrFlambeau";
    $View_TtableC = "양식지";
}
if ($ListXTtable == "6" || $Ttable == "cadarok" || $ToTitle == "리플렛") {
    $View_TtableA = "6";
    $View_TtableB = "cadarok";
    $View_TtableC = "리플렛";
}
if ($ListXTtable == "7" || $Ttable == "LittlePrint" || $ToTitle == "소량인쇄") {
    $View_TtableA = "7";
    $View_TtableB = "LittlePrint";
    $View_TtableC = "소량인쇄";
}
if ($ListXTtable == "8" || $Ttable == "cadarokTwo" || $ToTitle == "카다로그") {
    $View_TtableA = "8";
    $View_TtableB = "cadarokTwo";
    $View_TtableC = "카다로그";
}
if ($ListXTtable == "9" || $Ttable == "hakwon" || $ToTitle == "학원") {
    $View_TtableA = "9";
    $View_TtableB = "hakwon";
    $View_TtableC = "학원";
}
if ($ListXTtable == "10" || $Ttable == "food" || $ToTitle == "음식") {
    $View_TtableA = "10";
    $View_TtableB = "food";
    $View_TtableC = "음식";
}
if ($ListXTtable == "11" || $Ttable == "company" || $ToTitle == "기업체") {
    $View_TtableA = "11";
    $View_TtableB = "company";
    $View_TtableC = "기업체";
}
if ($ListXTtable == "12" || $Ttable == "cloth" || $ToTitle == "의류") {
    $View_TtableA = "12";
    $View_TtableB = "cloth";
    $View_TtableC = "의류";
}
if ($ListXTtable == "13" || $Ttable == "commerce" || $ToTitle == "상업") {
    $View_TtableA = "13";
    $View_TtableB = "commerce";
    $View_TtableC = "상업";
}
if ($ListXTtable == "14" || $Ttable == "church" || $ToTitle == "교회") {
    $View_TtableA = "14";
    $View_TtableB = "church";
    $View_TtableC = "교회";
}
if ($ListXTtable == "15" || $Ttable == "nonprofit" || $ToTitle == "비영리") {
    $View_TtableA = "15";
    $View_TtableB = "nonprofit";
    $View_TtableC = "비영리";
}
if ($ListXTtable == "16" || $Ttable == "etc" || $ToTitle == "기타") {
    $View_TtableA = "16";
    $View_TtableB = "etc";
    $View_TtableC = "기타";
}

$MAXFSIZE = "999999";
$upload_dir = "../../mlangprintauto/" . ($Ttable ?? '') . "/upload";
?>
