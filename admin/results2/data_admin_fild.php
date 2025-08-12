<?php
// �ڷ� ȣ��
include"../../db.php";
$result= mysql_query("select * from Mlnag_Results_Admin where id='$id'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
{ 

$DataAdminFild_item="$row[item]";  
$DataAdminFild_title="$row[title]";  
$DataAdminFild_id="$row[id]";   
$DataAdminFild_celect="$row[celect]";   
$DataAdminFild_date="$row[date]";   

}

}else{
echo ("<script language=javascript>
window.alert('$id - ���̺��� ��ȯ �ڷᰡ �����ϴ�.');
history.go(-1);
</script>
");
exit;
}

mysql_close($db); 
?>