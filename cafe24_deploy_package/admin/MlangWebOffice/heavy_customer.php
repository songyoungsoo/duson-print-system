<?php
$M123="..";
include"../top.php"; 

$PageCode="heavy_customer";
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
function WebOffice_customer_Del(no){
	if (confirm(+no+'�� �ڷḦ ���� �Ͻðڽ��ϱ�..?\n\nä������������ ��� �ڷ���� ���ÿ� �� ���� ������ �˴ϴ�.\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!')) {
		str='./<?php echo $PageCode?>/admin.php?no='+no+'&mode=delete';
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
$table="MlangWebOffice_$PageCode";

if($TDsearchValue){ // �˻�
$Mlang_query="select * from $table where $TDsearch like '%$TDsearchValue%'";
}else{ // �Ϲݸ�� �϶�
$Mlang_query="select * from $table";
}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 15;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 
?>


<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
<font color=red>*</font> �����ڷḦ ���/���� �Ͻ÷��� <b><u>�����ڼ�������</u></b>�� �̿��ϼ���
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
		<option value='bizname'>��ȣ</option>
		<option value='ceoname'>��ǥ</option>
		<option value='a_name'>�������</option>
        <input type='text' name='TDsearchValue' size='20'>
        <input type='submit' value=' �� �� '>
	    </td>
		</form>
	 </tr>
	</table>
</td>
<td align=right valign=bottom>
�ŷ�ó ��-<font style='color:bl<?php echo '><b><?=$total?></b></font>&nbsp;��ü
<input type='button' onClick="javascript:popup=window.open('./<?php echo $PageCode?>/admin.php?mode=form', 'WebOffice_<?php echo $PageCode?>Form','width=600,height=480,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' ���� �Է��ϱ� '>
</td>
</tr>
</table>


<!------------------------------------------- ����Ʈ ����----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>���NO</td>
<td align=center>��ȣ</td>
<td align=center>��ǥ(�޴���)</td>
<td align=center>�������(�޴���)</td>
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
<td align=center><font color=white><?php echo $row[bizname]?></font></td>
<td align=center><font color=white><?php echo $row[ceoname]?></font></td>
<td align=center><font color=white>��</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./<?php echo $PageCode?>/admin.php?mode=view&no=<?php echo $row[no]?>', 'WebOffice_<?php echo $PageCode?>View','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='�����ڼ�������'>
<input type='button' onClick="javascript:popup=window.open('./<?php echo $PageCode?>/admin.php?mode=form&code=modify&no=<?php echo $row[no]?>', 'WebOffice_<?php echo $PageCode?>Modify','width=600,height=480,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' ���� '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?php echo $row[no]?>');" value=' ���� '>
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