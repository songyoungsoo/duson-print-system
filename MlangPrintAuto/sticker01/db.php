<?php
session_start();
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$db = mysql_connect($host, $user, $password);
if (!$db) {
  die("�����ͺ��̽� ���ῡ �����߽��ϴ�: " . mysql_error());
}

mysql_select_db($dataname, $db);
mysql_query("SET NAMES 'euckr'", $db);

$admin_email = "dsp1830@naver.com";
$admin_name = "�μձ�ȹ";
$MataTitle = "$admin_name - �μ�, ��ƼĿ, ������, ���÷�, ������, ��ν���, ī�ٷα�, ��Ű��, ���� ���˹�,�μ�ȫ����, �¶��ΰ��� �� �μ⿡�� �İ������� �ϰ��۾�.������������ �ż� ����.";
$SiteTitle = $admin_name;
$admin_Tname = "Mlang";
$admin_url = "http://www.dsp114.com";
$Homedir = $admin_url;
$admin_table = "member"; // ������ ���̺�
$page_big_table = "page_menu_big"; // �ָ޴� ���̺�
$page_table = "page"; // ������ ���� ���̺�
$home_cookie_url = ".dsp114.com"; // Ȩ ��Ű url

$WebSoftCopyright = "
<p align=center>
  Copyright �� 2005 MlangWebProgram - WEBSOFT ����:
  <a href='http://www.websil.net' target='_blank'>
    <font style='color:#408080; text-decoration:none'><b>WEBSIL</b>.net</font>
  </a> Corp All rights reserved.
</p>";

$WebSoftCopyright2 = "
<p align=center>
  Copyright �� 2005 MlangWebProgram<br>
  WEBSOFT ����:
  <a href='http://www.websil.net' target='_blank'>
    <font style='color:#408080; text-decoration:none'><b>WEBSIL</b>.net</font>
  </a> Corp All rights reserved.
</p>";

$WebSoftCopyright3 = "
<p align=center>
  <font style='font-family:����; color:#B2B2B2; font-size:8pt;'>
    Copyright �� 2005 MlangWebProgram - WEBSOFT ����:
    <a href='http://www.websil.net' target='_blank'>
      <font style='color:#8C8C8C; text-decoration:none'><u><b>WEBSIL</b>.net</u></font>
    </a> Corp All rights reserved.
  </font>
</p>";
?>
