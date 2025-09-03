<?php
$result= mysql_query("select * from MlangHomePage_Movic where no='$no'",$db);
$row= mysql_fetch_array($result);
$View_cate="$row[cate]"; 
$View_title="$row[title]"; 
$View_upfile="$row[upfile]";  
$View_ContStyle="$row[ContStyle]";
$View_cont="$row[cont]"; 
?>