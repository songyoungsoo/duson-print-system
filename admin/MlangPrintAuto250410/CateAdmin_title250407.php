<?php
// $DF_Tatle_1 = $DF_Tatle_2 = $DF_Tatle_3 = ''; 
// 기본값 초기화

$TtableTitles = [
    'inserted' => ['인쇄규격', '종이규격', '종이종류'],
    'sticker' => ['스티카종류', '규격'],
    'NameCard' => ['명함종류', '명함재질'],
    'envelope' => ['구분', '종류'],
    'NcrFlambeau' => ['구분', '규격', '색상 및 재질'],
    'cadarok' => ['구분', '규격', '종이종류'],
    'cadarokTwo' => ['구분', '규격', '종이종류'],
    'LittlePrint' => ['종류', '종이종류', '종이규격'],
    'MerchandiseBond' => ['종류', '후가공']
];

if (array_key_exists($Ttable, $TtableTitles)) {
    $DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
    $DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
    $DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';
}
?>
