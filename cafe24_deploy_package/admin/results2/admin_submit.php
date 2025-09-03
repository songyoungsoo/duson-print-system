<?php
include"../config.php";

$result_ID_inspection= mysql_query("select * from Mlnag_Results_Admin where id='$id'",$db);
$rows_ID_inspection=mysql_num_rows($result_ID_inspection);
if($rows_ID_inspection){

while($row_ID_inspection= mysql_fetch_array($result_ID_inspection)) 
{ 
echo ("<script language=javascript>
window.alert('�Է��Ͻ� $id �� ��ġ�ϴ� ���̺��� �����մϴ�.\\n\\n������ �Է��� �ֽñ� �ٶ��ϴ�.');
history.go(-1);
</script>
");
exit;
}

}else{// Mlang_abc_table �� $table ���� �����Ѱ� ���� ���� ���̺� ������ ���� ��Ų��...



$result= mysql_query("select * from Mlang_${id}_Results",$db); 
if($result){ // ���̺���db�� ���� �� 

echo (" 
<script language=javascript> 
window.alert('EROOR(2): $table �� ID�� �� ���̺��� �̹����� �մϴ�.\\n\\n�ٸ������� �Է��� �����Ͻʽÿ�...!!'); 
history.go(-1);
</script>"); 
exit; 

}else{ // ���̺��� ����ġ �������� ���̺� ���� 

// 1�������� id ���� �޾Ƽ� Mlnag_Results_Admin ���� ������ ���� ��Ų��...///////////////////////////////////////
	$result = mysql_query("SELECT max(no) FROM Mlnag_Results_Admin");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################
$date = date("Y-m-d");
$dbinsert ="insert into Mlnag_Results_Admin values('$new_no',
'$item',
'$title',
'$id',
'$celect',
'$date'
)";
$result_insert= mysql_query($dbinsert,$db);

// 2���� db �� ���� �����ش�..///////////////////////////////////////////////////////////////////////////////////////
mysql_query("DROP TABLE IF EXISTS Mlang_${id}_Results"); 
mysql_query(" CREATE TABLE Mlang_${id}_Results ( 
Mlang_bbs_no mediumint(12) unsigned NOT NULL auto_increment, 
Mlang_bbs_member varchar(100) NOT NULL default '',                
Mlang_bbs_title text,                                                                 
Mlang_bbs_style varchar(100) NOT NULL default 'br',                
Mlang_bbs_connent text,                                                             
Mlang_bbs_link text,                                                                   
Mlang_bbs_file text,                                                                  
Mlang_bbs_pass varchar(100) NOT NULL default '',                  
Mlang_bbs_count int(12) NOT NULL default '0',                              
Mlang_bbs_rec int(12) NOT NULL default '0',                                                  
Mlang_bbs_secret varchar(100) NOT NULL default 'yes',                  
Mlang_bbs_reply int(12) NOT NULL default '0',                                           
Mlang_date datetime NOT NULL default '0000-00-00 00:00:00',                   
PRIMARY KEY (Mlang_bbs_no) 
) "); 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



// �ڷḦ ���ε��� ������ ���� �����ش�.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../results/upload/$id"; mkdir("$dir", 0755);  exec("chmod 777 $dir"); 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//�Ϸ� �޼����� ������ �������� �̵� ��Ų��
echo ("
		<script language=javascript>
		alert('\\n���������� �������ý��� ���α׷��� ���� �Ͽ����ϴ�..\\n\\n')
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list'>
		");
		exit;

} 

}
mysql_close($db); 
?>