<?php
include "db.inc.php";
	
	$db = mysql_connect($db_server,$db_uname,$db_pass);
	if (!$db) die("�����ͺ��̽� ����");
	mysql_select_db($db_name,$db) or die ("Could'nt open $db: ".mysql_error() );

?>
