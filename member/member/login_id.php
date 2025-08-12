<?php
if($HTTP_COOKIE_VARS[id_login_ok]){$WebtingMemberLogin_id="$HTTP_COOKIE_VARS[id_login_ok]";}else if($_COOKIE[id_login_ok]){$WebtingMemberLogin_id="$_COOKIE[id_login_ok]";	}else{
echo ("<script language=javascript>
window.alert('���������� �α��� ó���Ǿ� ���� ���� ��Ȳ�Դϴ�.\\n\\n����1) ������� ���ͳ� ��Ű������ �������ֽʽÿ�.\\n\\n����2) ������ DataBase�����ϼ� �ֽ��ϴ�.\\n\\n����1�� ����ڰ� �ذ����ּž� �ϸ� ����2�� �����츦 �������Ͻø� �ذ� ó�� �˴ϴ�.');
history.go(-1);
</script>
");
exit;
}
?>