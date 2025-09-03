<?php
include"data_admin_fild.php";

if($mode=="Submit"){

	$CATEGORY_LIST_script = split(":", $DataAdminFild_celect);
	$k = 0;
	while($k < sizeof($CATEGORY_LIST_script)) {
		if($F=="$CATEGORY_LIST_script[$k]"){
		echo ("<script language=javascript>
		          alert('\\n$F �ڷ�� �̹� ��ϵǾ��ֽ��ϴ�. �ٸ������� �Է����ּ���\\n');
                 opener.parent.location.reload();
                  window.self.close();
		       </script>");
		     exit;}
		$k++;
	} 



$Ok_celect="${DataAdminFild_celect}:${F}";

include"../../db.php";
$query ="UPDATE Mlnag_Results_Admin SET celect='$Ok_celect' WHERE id='$id'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				window.self.close();
			</script>";
		exit;

} else {
	
	echo ("<script language=javascript>
		          alert('\\n$F �ڷ��� �߰��� �Է��Ͽ����ϴ�.\\n');
                 opener.parent.location.reload();
                 window.self.close();
		       </script>");
		     exit;

}

mysql_close($db);

} /////////////////////////////////////////////////////////////////////////////////////

if($mode=="Modify"){

	$CATEGORY_LIST_script = split(":", $DataAdminFild_celect);
	$k = 0;
	while($k < sizeof($CATEGORY_LIST_script)) {
		if($F=="$CATEGORY_LIST_script[$k]"){
		echo ("<script language=javascript>
		          alert('\\n$F �ڷ�� �̹� ��ϵǾ��ֽ��ϴ�. �ٸ������� �Է����ּ���\\n');
                 opener.parent.location.reload();
                  window.self.close();
		       </script>");
		     exit;}
		$k++;
	} 


} /////////////////////////////////////////////////////////////////////////////////////

if($mode=="Delete"){
include"../../db.php";

// id �� �ִ� ��� �ڷ� �� ���� ó�� �ؾ���� ��///////////

// 1�� F���õ� ����ڷ� �˻� �Ͽ� no �����������ó��
$result= mysql_query("select * from Mlang_${id}_Results where Mlang_bbs_secret='$F'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) { 
 $dir_path = "$DOCUMENT_ROOT/results/upload/$id/$row[Mlang_bbs_no]";
	$Mlang_DIR = opendir("$dir_path"); // upload ���� OPEN
	while($ufiles = readdir($Mlang_DIR)) {
		if(($ufiles != ".") && ($ufiles != "..")) {
			unlink("$dir_path/$ufiles"); // ���ϵ� ����
		}
	}
	closedir($Mlang_DIR);

rmdir("$dir_path");  // upload ���� ����

mysql_query("DELETE FROM Mlang_${id}_Results WHERE Mlang_bbs_no='$row[Mlang_bbs_no]'");
                                                          }

}else{}

///////////////////////////////////////////////////////

	$ListOne= eregi_replace("$F", "", $DataAdminFild_celect);
    $ListTwo= eregi_replace("::", ":", $ListOne); 
	$ListTree="@${ListTwo}@";
	$ListFour= eregi_replace("@:", "", $ListTree); 
	$ListFive= eregi_replace(":@", "", $ListFour);
    $ListSix= eregi_replace("@", "", $ListFive);

$query ="UPDATE Mlnag_Results_Admin SET celect='$ListSix' WHERE id='$id'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				window.self.close();
			</script>";
		exit;

} else {
	
	echo ("<script language=javascript>
		          alert('\\n���������� �ڷ��� ���� �Ͽ����ϴ�.\\n');
                 opener.parent.location.reload();
                 window.self.close();
		       </script>");
		     exit;

}

mysql_close($db);

} /////////////////////////////////////////////////////////////////////////////////////
?>