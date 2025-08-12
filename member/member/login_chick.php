<?php
if($Gourl=="pop"){


if($HTTP_COOKIE_VARS[id_login_ok]){
}else if($_COOKIE[id_login_ok]) {
}else{
echo ("<script language=javascript>
window.alert('���������� �α����� �Ͽ��� �̿��ϽǼ� �ֽ��ϴ�.\\n\\n�α����� ���̵� �����ø� ȸ�������� �̿��Ͻñ� �ٶ��ϴ�.');
opener.parent.location=\"/member/login.php\"; 
window.self.close();
</script>
");
exit;
}


}else{

if($HTTP_COOKIE_VARS[id_login_ok]){
}else if($_COOKIE[id_login_ok]) {
}else{

$Login_pageselfurl = eregi_replace("&", "@", $_SERVER[REQUEST_URI]);

echo ("<script language=javascript>
window.alert('���������� �α����� �Ͽ��� �̿��ϽǼ� �ֽ��ϴ�.\\n\\n�α����� ���̵� �����ø� ȸ�������� �̿��Ͻñ� �ٶ��ϴ�.');
</script>
<meta http-equiv='Refresh' content='0; URL=/member/login.php?LoginChickBoxUrl=$Login_pageselfurl'>
");
exit;
}

}
?>
