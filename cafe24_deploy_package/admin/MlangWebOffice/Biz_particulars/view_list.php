<?php
include"../../../db.php";
include"../../config.php";
include"view_admin.php";
$TopTableWidth="630";
?>

<html>
<head>
<title>MlangWeb�������α׷�(3.0)-MlangWebOffice: �ŷ������� �μ�</title>
<meta http-equiv="Content-type" content="text/html; charset=euc-kr">
<!--------------------------------------------------------------------------------

     ���α׷���: MlangWeb�������α׷� ����3.0
     ���α׷� ������-�������÷���2
     ���α׷����: PHP, javascript, DHTML, html
     ������: Mlang 

// 3.0 �� �߰���  ��� --------------------------------------------------------------//

(1) �Խ��� ���� ����
(2) PAGE ������� ���� ����
(3) Photo�ڷ�� ������� �߰�
(4) �ŷ����� ����� ������� �߰�
(5) ȸ�� �Է��� ������� �߰�

//-------------------------------------------------------------------------------//

* �� ����Ʈ�� MYSQLDB(MySql�����ͺ��̽�) ȭ �۾��Ǿ��� �ִ� Ȩ������ �Դϴ�.
* Ȩ�������� ��ŷ, ��������� �ڷᰡ �������� 5�оȿ� ������ �����մϴ�.
* ������Ʈ�� PHP���α׷�ȭ �Ǿ��� �������� ���ʺ��ڰ� �ڷḦ ����/���� �����մϴ�.
* ������ ������ �Ƿ��ڰ� HTML������ �߰��� ���ϸ� ���α׷��� �����մϴ�.
* ��� �������� ���󿡼� �����Ҽ� �ֽ��ϴ�.

   ���α׷� ���� ������ : �� 011-548-7038, ������ (��ȭ�ȹ����� ���ڸ��ּſ�*^^*)
----------------------------------------------------------------------------------->
<style>
body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:����; word-break:break-all;}
td, table{BORDER-COLOR:#4C4C4C; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:����; word-break:break-all;}
.Td_b{font:bold; font-size:12pt;}
.Td_2{font-size:12pt;}
.Td_Bgcolor_1{color:#FFFFFF;}
</style>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' bgcolor='#FFFFFF'>

<table border=0 align=center width='<?php echo $TopTableWidth?>' cellpadding=0 cellspacing=0>
<tr><td width=100% height=20>&nbsp;</td></tr>
<tr>
<td width='100%'>

<!------------------------------ ����ó ��� �κ�  ���� ------------------------------->
<p align=center>
<font style='font-size:18pt; font:bold;'>�ŷ������� </font>
<font style='font-size:11pt; color:#336666'>( ���<?php echo  <?=$no?> )</font>
</p>

<table border=0 align=center width='100%' cellpadding=0 cellspacing=5 style='word-break:break-all;'>

<tr>
<td width=20% align=right class='Td_b'>����ó&nbsp;&nbsp;</td>
<td width=30% class='Td_2'><?echo("$Viewbiz_name");?></td>
<td width=20% align=right class='Td_b'>�����&nbsp;&nbsp;</td>
<td width=30% class='Td_2'><?echo("$Viewa_name");?></td>
</tr>

<tr>
<td align=right class='Td_b'>�����&nbsp;&nbsp;</td>
<td><?echo("$Viewb_name");?></td>
<td align=right class='Td_b'>����TEL&nbsp;&nbsp;</td>
<td>
<?echo("$Viewtel_1");?> - <?echo("$Viewtel_2");?> - <?echo("$Viewtel_3");?>
</td>
</tr>

<tr>
<td align=right colspan=4>&nbsp;<?php echo date("Y");?>&nbsp;��&n<?php echo ;<?=date("m");?>&nbsp;��<?php echo sp;<?=date("d");?>&nbsp;��&nbsp;</td>
</tr>
</table>
<!------------------------------ ����ó ��� �κ�  ���� ------------------------------->

<!--------------------------------- SoList ��� ���� ---------------------------------->
<table border=1 align=center width='100%' cellpadding='5' cellspacing='0' style='word-break:break-all;'>
<tr bgcolor='#757575'>
<td align=center height=25 class='Td_Bgcolor_1' width=90>�ŷ�����</td>
<td align=center class='Td_Bgcolor_1' width=105>����</td>
<td align=center class='Td_Bgcolor_1' width=105>����ȣ</td>
<td align=center class='Td_Bgcolor_1' width=90>����</td>
<td align=center class='Td_Bgcolor_1' width=100>�ݾ�</td>
<td align=center class='Td_Bgcolor_1' width=140>���</td>
</tr>

<?php
$table="MlangWebOffice_Biz_particulars where admin_no='$no'";

if($TDsearch){ // �˻�����ϰ��
$Mlang_query="select * from $table and $TDsearch like '%$TDsearchValue%'";
}else{
$Mlang_query="select * from $table";
}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 25;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 

$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);

$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>

<tr>
<td align=center height=25><?php echo $row[biz_date]?></td>
<td align=center><?php echo $row[kinds]?></td>
<td align=center><?php echo $row[fitting_no]?></td>
<td align=center><?php echo $row[engineer_name]?></td>
<td align=center><?$sum = "$row[money]"; $sum = number_format($sum);  echo("$sum"); $sum = str_replace(",","",$sum);?></td>
<td align=center><?php echo $row[remark]?></td>
</tr>

<?php
		$i=$i+1;
} 


}else{

if($search){
echo"<tr><td colspan=10><p align=center><BR><BR>���� �˻� �ڷ����</p></td></tr>";
}else if($TDsearchValue){ // ȸ�� ���ܰ˻� TDsearch //  TDsearchValue
echo"<tr><td colspan=10><p align=center><BR><BR>$TDsearch �� �˻��Ǵ� $TDsearchValue - ���� �˻� �ڷ����</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>��� �ڷ����</p></td></tr>";
}

}

?>


</table>


<table border=0 align=center width='100%' cellpadding=0 cellspacing=3 style='word-break:break-all;'>
<tr>
<td align=left class='Td_2'>&nbsp;* ��꼭 ����� VAT 10% ����</td>
<td align=right class='Td_2'>����: <b>�����߱�</b>&nbsp;</td>
</tr>
</table>

<p align='center'>

<?php
if($rows){

if($TDsearch){
	$mlang_pagego="mode=$mode&no=$no&TDsearch=$TDsearch&TDsearchValue=$TDsearchValue$YUh_offset";
}else{
	$mlang_pagego="mode=$mode&no=$no$YUh_offset"; 
}

$pagecut= 7;  //�� ��� ������ �������� 
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

if($offset!= $newoffset){
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;"; 
}else{echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"); } 

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
<!--------------------------------- SoList ��� ���� ---------------------------------->

</td></tr>
</table>

<p align=center>
�� ����Ʈ���� ����: WEBSIL.net (Ȩ����������/�����α׷������������)
</p>

</body>

</html>