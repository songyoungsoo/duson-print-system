<?php
// 2006�� 01.27�� ����ȭ���� ���� �����ν� �� ���嵵 ��Ų ����� �����.
// ��Ų��: seoulfireworks �������� => ������ �Է����� ����
// �ѱ����ϸ� ���� �Է� ������� ����/���ڷ� �ڵ�����

// �Խ��� ���� �ʵ��.  Mlang_BBS_Admin ////////////////////////////////////////////////////////////////////////////////////
//  no       : �Խ��� ��ȣ
//  title      : �Խ��� ����
//  id        : �Խ��� ID
//  pass    : �Խ��� ��й�ȣ
//  header  : �� html ����
//  footer   : �Ʒ� html ����
//  header_include  : �� INCLUDE ����
//  footer_include   : �Ʒ� INCLUDE ����    
//  file_select  : ������ ���� �ǰ��� ���ÿ���
//  link_select  : ��ũ�� �� �ǰ��� ���ÿ���
//  recnum : ���������� ��¼�
//  lnum    : �������̵� �޴���
//  cutlen  :  ������ڼ� ����
//  New_Article   : ����ǥ�� �����Ⱓ
//  date_select    : ����� ��¿���
//  name_select   : �̸� ��¿���
//  count_select   : ��ȸ�� ��¿���
//  recommendation_select   : ��õ�� ��¿���
//  secret_select   : ���� ����� ��¿���
//  write_select     : ���� ���� - member(ȸ����), guest(�ƹ���), admin(�����ڸ�)
//  date : �Խ����� ���糯¥
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<head>
<script language=javascript>

var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

////////////////////////////////////////////////////////////////////////////////
function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}

/////////////////////////////////////////////////////////////////////////////////

function ResultsAdminCheckField()
{
var f=document.ResultsAdmin;

if (f.item.value == "0") {
alert("������  SKIN�� �Է��Ͽ��ּ���...?");
return false;
}

if (f.title.value == "") {
alert("������  ���α׷��� Ÿ��Ʋ(����)�� �Է��Ͽ��ּ���...?");
return false;
}

if (f.id.value == "") {
alert("������  ���α׷��� ���̺���(����/����)�� �Է��Ͽ��ּ���...?");
return false;
}
if (!TypeCheck(f.id.value, ALPHA+NUM)) {
alert("������  ���α׷��� ���̺����� ������ �� ���ڷθ� ����� �� �ֽ��ϴ�.");
return false;
}

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function clearField(field)
{
	if (field.value == field.defaultValue) {
		field.value = "";
	}
}
function checkField(field)
{
	if (!field.value) {
		field.value = field.defaultValue;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function Mlnag_Results_Admin_Del(id){
	var str;
	if (confirm("������ �ڷḦ ���� �Ͻðڽ��ϱ�..?\n\n�������� �����ڷ��(���ε�����,DATA ��..)�� ���� �����˴ϴ�.\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!")) {
		str='<?php echo $PHP_SELF?>?id='+id+'&mode=delete';
        location.href=str;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr><td colspan=2>
* �з��� �Է��ҽô� : ���� ���� �־� ���ñ� �ٶ��ϴ�. (��- ���:�ູ:���:����)<BR>
* �ڷ������ ������ ���̺��� �ش��ϴ� ���α׷��� �ڷḦ �Է�/����/���� �ϽǼ� �ֽ��ϴ�..<BR>
* ���̺����� Ŭ���Ͻø� ���α׷� �ڷḦ �ٷ� ���Ǽ� �ֽ��ϴ�..<BR>
* ����� �������� ������ ������ �����ø� ���̺��� �ش��ϴ� ���α׷��� ������ �����ϽǼ� �ֽ��ϴ�.
<tr>
<form name='ResultsAdmin' method='post' OnSubmit='javascript:return ResultsAdminCheckField()' action='<?php echo $PHP_SELF?>'>
<input type='hidden' name='mode' value='submit'>
<td align=left>

<?php
$dir_path = "../../results/Skin";
$dir_handle = opendir($dir_path);
echo("<select name='item'><option value='0'>�� SKIN ���� ��</option>");

while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")){
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp'>$tmp</option>");   }
}

echo("</select>");

closedir($dir_handle);
?>


<INPUT TYPE='TEXT' SIZE=18 maxLength='20' NAME='title' VALUE="Ÿ��Ʋ(����)" onBlur="checkField(this);" onFocus="clearField(this);">
<INPUT TYPE='TEXT' SIZE=18 maxLength='12' NAME='id' VALUE="���̺���(����/����)" onBlur="checkField(this);" onFocus="clearField(this);">
�з�<INPUT TYPE='TEXT' SIZE=30 maxLength='500' NAME='celect' VALUE="">
<INPUT TYPE=SUBMIT VALUE='�����մϴ�..'>
</td>
</form>

<form name='BbsAdminSearch' method='post' OnSubmit='javascript:return BbsAdminSearchCheckField()' action='<?php echo $PHP_SELF?>'>
<input type='hidden' name='mode' value='list'>
<td align=right>
<select name='bbs_cate'>
<option value='title'>Ÿ��Ʋ(����)</title>
<option value='id'>���̺���</title>
<INPUT TYPE='TEXT' SIZE=18 NAME='search' onBlur="checkField(this);" onFocus="clearField(this);">
<INPUT TYPE=SUBMIT VALUE='�˻�'>
</td>
</form>
</tr>
</table>



<!------------------------------------------- ����Ʈ ����----------------------------------------->
<?php
include"../../db.php";
$table="Mlnag_Results_Admin";

if($search){ //�˻�����϶�
$Mlang_query="select * from $table where $bbs_cate like '%$search%'";}else{ // �Ϲݸ�� �϶�
$Mlang_query="select * from $table";
}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 15;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 

$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){

echo("
<table border=0 align=center width=100% cellpadding='5' cellspacing='2' class='coolBar'>
<tr>
<td align=center width=10%>SKIN</td>	
<td align=center width=15%>����</td>	
<td align=center width=15%>���̺���</td>
<td align=center width=20%>�з�</td>
<td align=center width=10%>������</td>
<td align=center width=10%>�ڷ��</td>
<td align=center width=20%>�������</td>		
</tr>
");

$i=1+$offset;
while($row= mysql_fetch_array($result)) 
{ 

if ($search) //�˻� Ű���尪
{$row[title] = str_replace($search, "<b><FONT COLOR=blue>$search</FONT></b>", $row[title]);}
if ($search) //�˻� Ű���尪
{$row[id] = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $row[id]);}

echo("
<tr bgcolor='#575757'>
<form method='post' action='$PHP_SELF'>
<input type='hidden' name='no' value='$row[no]'>
<input type='hidden' name='mode' value='admin_modify'>");
?>

<td align=center>
<?php
$dir_handle = opendir($dir_path);
$RRT="selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";

echo("<select name='item'>");

while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")){
		if($row[item]=="$tmp"){
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp' $RRT>$tmp</option>");  
			}else{
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp'>$tmp</option>");  
			}		  }
}

echo("</select>");

closedir($dir_handle);
?>
</td>	

<?echo("<td align=center>&nbsp;<input type='text' name='title' value='$row[title]' maxLength='20' size='20'></td>	
<td>&nbsp;<a href='/results/index.php?table=$row[id]' target='_blank'><font color=white>$row[id]</font></a></td>	
<td align=center>&nbsp;<input type='text' name='celect' value='$row[celect]' maxLength='500' size='35'></td>	
<td align=center><font color=white>$row[date]</font></td>");

echo("<td align=center>");


$total_query=mysql_query("select * from Mlang_$row[id]_Results",$db);
$total_bbs=mysql_affected_rows();

echo("<font color=#CCFFFF>$total_bbs</font></td>");

echo("<td align=center>");

echo("<input type='submit' value='����' style='width:40; height:22;'>");

echo("<input type='button' onClick=\"javascript:window.location.href='./data.php?mode=list&id=$row[id]';\" value='�ڷ����' style='width:60; height:22;'>");

echo("<input type='button' onClick=\"javascript:Mlnag_Results_Admin_Del('$row[id]');\" value='����' style='width:40; height:22;'>");

echo("<input type='button' onClick=\"javascript:window.open('../bbs/dump.php?TableName=Mlang_$row[id]_results', 'bbs_dump','width=567,height=451,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');\" value='����' style='width:40; height:22;'>");

echo("</td></form></tr>");

		$i=$i+1;
} 

echo("</table>");

}else{

if($search){ echo"<p align=center><b>$search �� ���� �Խ��� ����</b></p>";
}else{ echo"<p align=center><b>������ �ٹ� ���α׷� �� �����ϴ�..</b></p>"; }

}

?>

<p align='center'>

<?php
if($rows){

$mlang_pagego="mode=list&bbs_cate=$bbs_cate&search=$search"; // �ʵ�Ӽ��� ���ް�

$pagecut= 10;  //�� ��� ������ �������� 
$one_bbs= $listcut*$pagecut;  //�� ��� ���� �� �ִ� ���(�Խù�)�� 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  //�� �忡 ó�� �������� $offset��. 
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;  //������ ���� ù�������� $offset��. 
$start_page= intval($start_offset/$listcut)+1; //�� �忡 ó�� �������� ��. 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 
//������ ���� �� ������. 
if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[����]</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset) 
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>"; 
echo "[$i]"; 
if($offset!= $newoffset) 
  echo "</a>&nbsp;"; 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[����]...</a>"; 
} 
echo "�Ѹ�ϰ���: $end_page ��"; 


}

mysql_close($db); 
?> 

</p>
<!------------------------------------------- ����Ʈ ��----------------------------------------->