<?php
$Color1="1466BA";
$Color2="4C90D6";
$Color3="BBD5F0";
$PageCode="member";
include"../db.php";
include"../top.php";
?>

<?php
if(!$DbDir){$DbDir="..";}
if(!$MemberDir){$MemberDir=".";}

include"$DbDir/db.php";
$query = "select * from member where id='$id'";
$result = mysql_query($query,$db);
$rows=mysql_num_rows($result);
if($rows){
echo("
<script language=javascript>
window.alert('\\n $id �� �̵̹�ϵǾ��ִ�\\n\\n�̸��̹Ƿ� ��û�ϽǼ� �����ϴ�.\\n');
history.go(-1);
</script>	
");
exit;

}

$login_dir="$MemberDir";
$db_dir="$MemberDir";

$action="$MemberDir/member_form_ok.php";
include"$MemberDir/form.php";

include"../down.php";
?>