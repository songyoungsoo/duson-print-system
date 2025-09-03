<?php
$M123="..";
include"../top.php"; 
?>

<head>
<script>
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
function Biz_particularsBigDel(no){
	if (confirm(+no+'�� �ڷḦ ���� �Ͻðڽ��ϱ�..?\n\n����ó ������ �ŷ������� ��� �ڷ���� ���ÿ� �� ���� ������ �˴ϴ�.\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!')) {
		str='./Biz_particulars/admin.php?no='+no+'&mode=BigDelete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

</head>

<?php
include"../../db.php";
$table="MlangWebOffice_Biz_particulars_admin";

if($TDsearchValue){ // �˻�
$Mlang_query="select * from $table where $TDsearch like '%$TDsearchValue%'";
}else{ // �Ϲݸ�� �϶�
$Mlang_query="select * from $table";
}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 20;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 
?>


<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
<font color=red>*</font> �� �������� �ŷ�������(�μ�)�� �������� ������� ���α׷� �Դϴ�.<BR>
<font color=red>*</font> �̿��� : ����ó ���� �Է��ϱ� Ŭ�� => ����ó, �������... �Է� => �����ڼ������� Ŭ�� => �ŷ�����, ����ȣ��... ��������
</td>
</tr>
<tr>
<td>
   <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?php echo $PHP_SELF?>'>
	    <td align=left>
		<b>�˻� :&nbsp;</b>
		<select name='TDsearch'>
		<option value='bizname'>�����</option>
		<option value='ceoname'>����ó</option>
		<option value='ceoname'>�����</option>
        <input type='text' name='TDsearchValue' size='20'>
        <input type='submit' value=' �� �� '>
	    </td>
		</form>
	 </tr>
	</table>
</td>
<td align=right valign=bottom>
�ڷ� ��-<font style='color:bl<?php echo '><b><?=$total?></b></font>&nbsp;��
<input type='button' onClick="javascript:popup=window.open('./Biz_particulars/admin.php?mode=form', 'WebOffice_Biz_particularsForm','width=700,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' ����ó ���� �Է��ϱ� '>
</td>
</tr>
</table>


<!------------------------------------------- ����Ʈ ����----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>���NO</td>
<td align=center>�����</td>
<td align=center>����ó</td>
<td align=center>�����</td>
<td align=center>�������</td>
</tr>

<?php
$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo $row[no]?></font></td>
<td align=center><font color=white><?php echo $row[a_name]?></font></td>
<td align=center><font color=white><?php echo $row[b_name]?></font></td>
<td align=center><font color=white>
<?php
$result_staff=mysql_query("select * from WebOffice_customer_staff where customer_no=$row[no]",$db);
$total_staff = mysql_affected_rows();
if($result_staff){echo("$total_staff");}else{echo("�ڷṫ");}
?>
��</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./Biz_particulars/view_list.php?no=<?php echo $row[no]?>', 'WebOffice_Biz_particularsViewList','width=700,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='�μ� ���'>
<input type='button' onClick="javascript:popup=window.open('./Biz_particulars/admin.php?mode=form&code=modify&no=<?php echo $row[no]?>', 'WebOffice_Biz_particularsModifyj3','width=700,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();"value=' ���� '>
<input type='button' onClick="javascript:Biz_particularsBigDel('<?php echo $row[no]?>');" value=' ���� '>
</td>
<tr>

<?php
		$i=$i+1;
} 


}else{

if($search){
echo"<tr><td colspan=10><p align=center><BR><BR>���� �˻� �ڷ����</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>��� �ڷ����</p></td></tr>";
}

}

?>


</table>



<p align='center'>

<?php
if($rows){

$mlang_pagego="cate=$cate$title_search=$title_search"; // �ʵ�Ӽ��� ���ް�

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
<!------------------------------------------- ����Ʈ ��----------------------------------------->

<?php
include"../down.php";
?>