<?php
include"../db.php";
// �ּҰ�åâ �ٿ��
if(!strcmp($mode,"search")) {
?>


<html>
<head>
<title>�ּ��ڵ��˻�-<?=$admin_name?></title>
<STYLE>
<!--
p,br,body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:����;}
b {color:black; font-size:10pt; FONT-FAMILY:����;}
-->
</STYLE>
<script language=javascript>
function ZipCheckField()
{
var f=document.zip;
if (f.zip_search.value == "") {
alert("�˻��Ͻ� ������ �Է��Ͽ��ּ���");
return false;
}
}
</script>
</head>

<body bgcolor='#F7FFEF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<BR><BR><BR><BR><BR>


<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='../img/12345.gif' height=5></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=40 align=center bgcolor='#D2F9BF' width=100%><img src='../img/12345.gif' height=30 width=200></td></tr>
<tr><td bgcolor='#D2F9BF' valign=top>

<table border=0 width=80% align=center cellpadding='5' cellspacing='1' bgcolor='#8CDF63'>
<tr>
<td align=left  width=100%>
&nbsp;&nbsp;<b>�Ʒ��� ã���� ��,��,��,�� �� �Է��ϼ���</b>
</td>
</tr>
<form name='zip' method='post' OnSubmit='javascript:return ZipCheckField()' action='<?echo"$PHP_SELF";?>'>
<input type='hidden' name='mode' value='zip_ok'>
<tr>
<td  height=110 align=center bgcolor='#F7FFEF' width=100%>
<BR>
��) ����ϵ� <u>���ֱ�</u> �� ã�����Ұ�� => ���ֱ� ���� �Է��Ѵ�.
<BR><BR>
<font color=green>ã���ܾ��Է�:</font>
<INPUT onmouseover="this.style.backgroundColor='#FFFFFF'" maxLength="10" size="25" style="font-size:9pt; background-color:#FFFFFF; color:#000000; border-width:1; border-style:solid; height:22px; border:1 solid #8CDF63;" name="zip_search">
<INPUT type='submit' size="20" style="font-size:9pt; background-color:#000000; color:#FFFFFF; border-width:1; border-style:solid; height:22px; border:1 solid #1F5E00;" value='�ּҰ˻�'>
&nbsp;&nbsp;&nbsp;
<BR><BR>

</td>
</tr>
</form>
</table>

</td></tr></table>


<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=40 align=center bgcolor='#D2F9BF' width=100%><img src='../img/12345.gif' height=10 width=200></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='../img/12345.gif' height=5></td></tr>
<tr><td  height=20 align=center bgcolor='#F7FFEF' width=100% valign=bottom>
<BR>
<font style='font-size:8pt; color:green'>
Copyright �� 2003 <?=$admin_name?> Corp. All rights reserved. 
</font>
</td></tr>
<tr><td  height=50 align=center width=100%><img src='../img/12345.gif' height=50 width=200></td></tr>
</table>


</body>
</html>


<?php
// �˻���� �����ֱ�
}elseif(!strcmp($mode,"zip_ok")) {
?>

<html>
<head>
<title>�ּ��ڵ��˻�-<?=$admin_name?></title>
<STYLE>
<!--
p,br,body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:����;}
-->
</STYLE>
		<SCRIPT language=JavaScript>
		function Copy(zip,zip1,zip2) {

			top.opener.document.JoinInfo.zip.value = zip;
			top.opener.document.JoinInfo.zip1.value = zip1;
			top.opener.document.JoinInfo.zip2.value = zip2;

			top.opener.document.JoinInfo.zip2.focus();

			parent.window.close();

		}
		</SCRIPT>
</head>
<body bgcolor='#F7FFEF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'><tr><td width=100% height=500 valign=middle>
<BR>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='../img/12345.gif' height=5></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=20 align=center bgcolor='#D2F9BF' width=100%><img src='../img/12345.gif' height=20 width=200></td></tr>
<tr><td bgcolor='#D2F9BF'>
<?php
$query= mysql_query("select * from zipcode where SIDO like '%$zip_search%' or GUGUN like '%$zip_search%' or DONG like '%$zip_search%'",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 15;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 

$result= mysql_query("select * from zipcode where SIDO like '%$zip_search%' or GUGUN like '%$zip_search%' or DONG like '%$zip_search%' order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){

echo"<table border=0 width=90% align=center cellpadding='2' cellspacing='0'>
<tr><td>
<b><font color=green><u>$zip_search</u></font> ���� ���� �ּҰ� <font color=red>$total</font> �� �˻� �Ǿ����ϴ�.</b>
</td></tr>
<tr><td  height=5 align=left width=100%><img src='../img/12345.gif' height=5></td></tr>
";

$i=1+$offset;
while($row= mysql_fetch_array($result)) 
{ 


echo"
<tr><td>
$i )
<font color=green>$row[ZIPCODE]</font>
 $row[SIDO]
 $row[GUGUN]
 $row[DONG]
 $row[BUNJI]
<a href=\"javascript: Copy('$row[ZIPCODE]','$row[SIDO] $row[GUGUN] $row[DONG]','$row[BUNJI]')\">[����]</a>
</td></tr>
";

		$i=$i+1;
} 

echo"</table>";
}
else{
echo"
<p align=center>
<font style='font-size:10pt; color:red;'><u><b>$zip_search</b></u></font><font style='font-size:10pt; color:green;'> �� �˻��Ǵ� �ڷ�����ϴ�.</font><BR><BR>
(�����ô� <b>������ ��</b> ���� �Է��Ͽ�) �ٽ� �ѹ� �˻� �Ͽ� �ֽñ� �ٶ��ϴ�.
<BR><BR>
<input type='button' onClick='javascript:history.back();' value='�ٽ� �˻��Ϸΰ���'>
</p>

</td></tr></table>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=20 align=center bgcolor='#D2F9BF' width=100%><img src='../img/12345.gif' height=20 width=200></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='../img/12345.gif' height=5></td></tr>
<tr><td  height=20 align=center bgcolor='#F7FFEF' width=100% valign=bottom>
<BR>
<font style='font-size:8pt; color:green'>
Copyright �� 2003 $admin_name Corp. All rights reserved. 
</font>
</td></tr>
<tr><td  height=50 align=center width=100%><img src='../img/12345.gif' height=50 width=200></td></tr>
</table>

</td></tr></table>


</body>
</html>
";
		exit;
}

mysql_close($db); 

?>


</td></tr></table>



<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=20 align=center bgcolor='#D2F9BF' width=100%><img src='../img/12345.gif' height=20 width=200></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='../img/12345.gif' height=5></td></tr>


<tr><td  height=20 align=center bgcolor='#F7FFEF' width=100% valign=bottom>
<?php
$pagecut= 5;  //�� ��� ������ �������� 
$one_bbs= $listcut*$pagecut;  //�� ��� ���� �� �ִ� ���(�Խù�)�� 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  //�� �忡 ó�� �������� $offset��. 
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;  //������ ���� ù�������� $offset��. 
$start_page= intval($start_offset/$listcut)+1; //�� �忡 ó�� �������� ��. 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 
//������ ���� �� ������. 
if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&mode=zip_ok&zip_search=$zip_search'>...[����]</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset) 
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&mode=zip_ok&zip_search=$zip_search'>"; 
echo "[$i]"; 
if($offset!= $newoffset) 
  echo "</a>&nbsp;"; 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&mode=zip_ok&zip_search=$zip_search'>[����]...</a>"; 
} 
echo " �˻��� �Ѹ�ϰ���: $end_page ��
"; 
?> 
</td></tr>



<tr><td  height=20 align=center bgcolor='#F7FFEF' width=100% valign=bottom>
<BR>
<font style='font-size:8pt; color:green'>
Copyright �� 2003 <?=$admin_name?> Corp. All rights reserved. 
</font>
</td></tr>
<tr><td  height=50 align=center width=100%><img src='/img/12345.gif' height=50 width=200></td></tr>
</table>

</td></tr></table>

</body>
</html>

<?php
} else {

echo"
<script language=javascript>
alert('\\n�������� ������ �ƴմϴ�.\\n\���α׷�����: http://www.script.ne.kr - Mlang\n\\n');
window.close();
</script>
";
exit;

}
?>