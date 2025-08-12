<?php
if($mode=="member_login"){  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../db.php";
$result_id= mysql_query("select * from member where id='$id'",$db);
$rows_id=mysql_num_rows($result_id);
if($rows_id){

while($row_id= mysql_fetch_array($result_id)) 
{ 
//-----------------------------------------------------//

$result= mysql_query("select * from member where id='$id' and pass='$pass'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
{ 
// ���ӱ��, ����ī���͸� ������Ʈ ��Ų��..
$Countresult= mysql_query("select * from member where id='$id'",$db);
$Countrow= mysql_fetch_array($Countresult);
$LogonCountOk=$Countrow[Logincount]+1;

$Logindate=date("Y-m-d H:i;s");
$query ="UPDATE member SET Logincount='$LogonCountOk', EndLogin='$Logindate' WHERE id='$id'";
mysql_query($query,$db);


$id_login_ok=$row[id]; 
@ session_register(id_login_ok); 
@ setcookie("id_login_ok", stripslashes($row[id]), 0, "/" );

echo ("<html>
<script language=javascript>
window.alert('���������� �� $admin_name �� �� �α��� �Ǽ̽��ϴ�..\\n\\n���� �Ϸ� �ǽñ⸦  �ٶ��ϴ�.....*^^*');
window.self.close();
</script>");

if($selfurl){


$selfurl_ok = eregi_replace("@", "&", $selfurl);

echo("<meta http-equiv='Refresh' content='0; URL=$selfurl_ok'>");
}else{
echo("<meta http-equiv='Refresh' content='0; URL=/'>");
}

echo("</html>");
}

}else{
echo ("<html>
<script language=javascript>
window.alert('�Է��Ͻ� $id (��)�� ��й�ȣ $pass �� ����ġ �մϴ�..\\n\\n�ٽ� �ѹ� Ȯ���� �ֽñ� �ٶ��ϴ�.....*^^*');
history.go(-1);
</script>
</html>");
exit;
}


//-----------------------------------------------------//
}


}else{


echo ("<html>
<script language=javascript>
window.alert('�Է��Ͻ� $id  �δ� ȸ�� ������ �Ǿ����� �ʽ��ϴ�.\\n\\n�ٽ� �ѹ� Ȯ���� �ֽñ� �ٶ��ϴ�.....*^^*');
history.go(-1);
</script>
</html>");
exit;


}

mysql_close($db); 


}

if($mode=="member_logout") { //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

@ setcookie("bbs_login","",0,"/");
@ setcookie("id_login_ok","",0, "/");
@ session_destroy();

echo ("<html>
<script language=javascript>
window.alert('���������� �α׾ƿ� ó�� �Ǿ����ϴ�........*^^*');
</script>
<meta http-equiv='Refresh' content='0; URL=/'>
</html>");
exit;


}


if(!$mode){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ("<html><script language=javascript>
window.alert('���������� ������ ������� �ʾƿ�....��!!');
</script>
<meta http-equiv='Refresh' content='0; URL=/'>
</html>");
exit;

}
?>
