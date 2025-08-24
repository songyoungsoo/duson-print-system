<?php
session_start(); 

$DhtmlTopFos = "430";
$DhtmlLeftFos = "180";

$GGTABLE = "MlangPrintAuto_transactionCate";
$GGTABLESu = "MlangPrintAuto_SuCate";

$ConDb_A = "전단지:스티카:명함:상품권:봉투:양식지:리플렛:소량인쇄:카다로그:학원:음식:기업체:의류:상업:교회:비영리:기타";

$mapping = [
    "0" => ["inserted", "전단지"],
    "1" => ["msticker", "스티카"],
    "2" => ["NameCard", "명함"],
    "3" => ["MerchandiseBond", "상품권"],
    "4" => ["envelope", "봉투"],
    "5" => ["NcrFlambeau", "양식지"],
    "6" => ["cadarok", "리플렛"],
    "7" => ["LittlePrint", "소량인쇄"],
    "8" => ["cadarokTwo", "카다로그"],
    "9" => ["hakwon", "학원"],
    "10" => ["food", "음식"],
    "11" => ["company", "기업체"],
    "12" => ["cloth", "의류"],
    "13" => ["commerce", "상업"],
    "14" => ["church", "교회"],
    "15" => ["nonprofit", "비영리"],
    "16" => ["etc", "기타"],
];

foreach ($mapping as $key => $value) {
    if ($ListXTtable == $key || $Ttable == $value[0] || $ToTitle == $value[1]) {
        $View_TtableA = $key;
        $View_TtableB = $value[0];
        $View_TtableC = $value[1];
        break;
    }
}

$MAXFSIZE = "999999";
$upload_dir = "../../MlangPrintAuto/$T_TABLE/upload";
?>
