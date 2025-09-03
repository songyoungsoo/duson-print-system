<?php
$result= mysql_query("select * from WebOffice_customer where no='$no'",$db);
$row= mysql_fetch_array($result);
$Viewbizname="$row[bizname]";
$Viewceoname="$row[ceoname]";
$Viewtel_1="$row[tel_1]"; 
$Viewtel_2="$row[tel_2]";   
$Viewtel_3="$row[tel_3]";  
$Viewfax_1="$row[fax_1]";
$Viewfax_2="$row[fax_2]"; 
$Viewfax_3="$row[fax_3]";
$Viewzip="$row[zip]"; 
$Viewofftel_1="$row[offtel_1]";
$Viewofftel_2="$row[offtel_2]";
$Viewofftel_3="$row[offtel_3]";   
$Viewofffax_1="$row[offfax_1]"; 
$Viewofffax_2="$row[offfax_2]";
$Viewofffax_3="$row[offfax_3]"; 
$Viewoffmap="$row[offmap]";
$Viewcont="$row[cont]";
mysql_close($db); 
?>