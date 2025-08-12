<?php
$connection = mysql_connect("localhost","duson1830","du1830");
$status = mysql_select_db("duson1830",$connection);
if (!$status) {   error("DB_ERROR");   exit;}
?>