<?php
if(!$DbDir){ $DbDir="../../.."; }
include "$DbDir/db.php";

$result= mysqli_query($db, "select * from MlangPrintAuto_BizInfo where no='1'");
$row= mysqli_fetch_array($result);

$View_MlangFild_1="$row[MlangFild_1]";
$View_MlangFild_2="$row[MlangFild_2]";
$View_MlangFild_3="$row[MlangFild_3]";
$View_MlangFild_4="$row[MlangFild_4]";
$View_MlangFild_5="$row[MlangFild_5]";
$View_MlangFild_6="$row[MlangFild_6]";
$View_MlangFild_7="$row[MlangFild_7]"; 


mysqli_close($db); 
?>