<?php
$host="localhost";
$user="imepa";
$dataname="imepa";
$password="imepa1004";
$table="BizMap";
$AdCate="경기,서울,인천:강원:충남,대전:충북:경북,대구:경남,울산,부산:전북:전남:제주";
$db=mysql_connect($host,$user,$password);
mysql_select_db("$dataname",$db);
$Copyright="Copyright (c) 2004 by <a href='http://www.script.ne.kr' target='_blank'><font style='font-size:8pt; color:#ADADAD;'>스크립트네꺼</font></a> Comp. All right Reserved.";
?>