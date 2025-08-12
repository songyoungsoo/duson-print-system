<?php
if($mode=="delete"){

include"../config.php";

mysql_query("DROP TABLE Mlang_${id}_Results");  // ���̺� ����
$result = mysql_query("DELETE FROM Mlnag_Results_Admin WHERE id='$id'"); // �Խ��ǰ����ڷ� ����
mysql_close();


// ��ü ���丮�� ����. //////////////////////////////////////////////////////////
$dir_path = "../../results/upload/$id";
$dir_handle = opendir($dir_path);

// ��ü ���丮 ������ ����Ѵ�.
while($tmp = readdir($dir_handle))
{

// �� ������ ���� ��ü ���� ������ �� ���� ���� --------------------//

	$Mlang_DIR = opendir("$dir_path/$tmp"); // upload ���� OPEN
	while($ufiles = readdir($Mlang_DIR)) {
              if(($ufiles != ".") && ($ufiles != "..")) {
			  unlink("$dir_path/$tmp/$ufiles"); // ���ϵ� ����
		}
	}
	closedir($Mlang_DIR);

	rmdir("$dir_path/$tmp");  // upload ���� ����

//-----------------------------------------------------------//


}

closedir($dir_handle);

rmdir("../../results/upload/$id");  // upload ���� ����

////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ("
<html>
<script language=javascript>
window.alert('���������� ������ �ý��� �� ��� �ڷḦ ���� �Ͽ����ϴ�.');
</script>
<meta http-equiv='Refresh' content='0; URL=../results/admin.php?mode=list'>
</html>
");

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="admin_modify"){

include"../config.php";

if ( !$title ) {
echo ("<script language=javascript>
window.alert('Ÿ��Ʋ(�����)�� �Էµ��� �ʾҽ��ϴ�..');
history.go(-1);
</script>
");
exit;
}


$query ="UPDATE Mlnag_Results_Admin SET item='$item', title='$title', celect='$celect' WHERE no='$no'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list'>
	");
		exit;

}

mysql_close($db);


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>