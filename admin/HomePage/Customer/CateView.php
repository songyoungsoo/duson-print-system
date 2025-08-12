<?php
$result= mysql_query("select * from MlangHomePage_Customer where no='$no'",$db);
$row= mysql_fetch_array($result);
$View_BigNo="$row[BigNo]";
$View_title="$row[title]";
$View_newy="$row[newy]";
?>