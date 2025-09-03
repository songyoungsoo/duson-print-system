<!------------------------------------------- ����Ʈ ����----------------------------------------->
<?php
include"$M123/../db.php";
$table="Mlang_${id}_Results";

if($search){$Mlang_query="select * from $table where $search_cate like '%$search%'";}else{$Mlang_query="select * from $table";}


$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();
?>

<table border=0 align=center width=100% cellpadding=0 cellspacing=0  class='coolBar'>
<tr>
<td width=20></td>
<td>
���������α<?php echo ���̺���: <b><?=$Da<?php echo dminFild_title?></b>&nbsp;(<?=$total?>)&nbsp;&nbsp;
<input type='button' onClick="javascript:popup=window.open('data_submit.php?id=<?php echo $id?>&mode=submit', 'data_submit','width=600,height=300,top=10,left=10,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='�ڷ� ���â ����'>
</td>
<td height=40 align=right>

<table border=0 cellpadding=0 cellspacing=0><tr>
<td width=20></td>
<head>
<script language=javascript>
function SrarchCheckField()
{
var f=document.SrarchInfo;

if (f.search.value == "") {
alert("�˻��� ������ �Է��ϼ���!!");
return false;
}

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function ResultsDelTT(no){
	var str;
		if (confirm("������ �ڷ�� �ι� �ٽ� ���� ���� �ʽ��ϴ�.\n\n������ �ڷᰡ Ȯ���Ͻø� �����Ͻʽÿ�!!")) {
		// ���⼭ style �� ����� �߰��Ǿ���  bbstop �� ���忡�� �ֱ⶧���� ���� Ȱ�밡���ϴ�. 
		str='/admin/int/delete.php?no='+no+'&table=<?php echo $table?>&bbs=del&file=ok&id=<?php echo $id?>&style=results';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
</script>
</head>

<form name='SrarchInfo' method='post' OnSubmit='javascript:return SrarchCheckField()' action='<?echo("$PHP_SELF");?>'>
<td>

<select name=search_cate>
<option value='Mlang_bbs_title'>����</font> 
<option value='Mlang_bbs_connent'>����</font> 
</select>

<input type='hidden' name='mode' value='<?php echo $mode?>'>
<input type='hidden' name='id' value='<?php echo $id?>'>
<input type='text' name='search' size='25'>
<input type='submit' value='�˻�'>
</td>
</form>
<td>
&nbsp;&nbsp;
<input type='button' onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=list&id=<?php echo $id?>';" value='��ü��Ϻ���' style='width:80;'>
<input type='button' onClick="javascript:window.location.reload();" value='���ΰ�ħ' style='width:60;'>
&nbsp;
</tr></table>

</td></tr>
</table>


<?php
$listcut= 15;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 

$result=mysql_query("$Mlang_query order by Mlang_bbs_no desc limit $offset,$listcut",$db);
$rows=@mysql_num_rows($result);
if($rows){

echo("
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#666600'>
<tr>
<td align=center height=30><font color=white>��Ϲ�ȣ</font></td>");

if($DataAdminFild_celect){echo("<td align=center><font color=white>�׸�</font></td>");}

if($DataAdminFild_item=="text"){
echo("<td align=center><font color=white>���ۻ�</font></td>
<td align=center><font color=white>���۹�</font></td>
<td align=center><font color=white>�������</font></td>
</tr>	
");
}else{
echo("
<td align=center><font color=white>����</font></td>
<td align=center><font color=white>�������</font></td>
</tr>	
");
}


$i=1+$offset;
while($row= mysql_fetch_array($result)) 
{ 
?>

<?php
echo("
<tr bgcolor='#FFFFFF'>
<td>&nbsp;&nbsp;$row[Mlang_bbs_no]&nbsp;</td>");

if($DataAdminFild_celect){echo("<td align=center>$row[Mlang_bbs_link]</td>");}

if ($search) //�˻� Ű���尪
{$row[Mlang_bbs_title] = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $row[Mlang_bbs_title]);}
if ($search) //�˻� Ű���尪
{$row[Mlang_bbs_connent] = str_replace($search, "<b><FONT COLOR=blue>$search</FONT></b>", $row[Mlang_bbs_connent]);}

if($row[Mlang_bbs_title]){
echo("<td><a href='/results/index.php?table=$id&mode=view&no=$row[Mlang_bbs_no]' target='_blank'>$row[Mlang_bbs_title]</a></td>");
}else{
echo("<td><a href='/results/index.php?table=$id&mode=view&no=$row[Mlang_bbs_no]' target='_blank'>�������</a></td>");
}

echo("
<td align=center>
<input type='button' onClick=\"javascript:popup=window.open('data_submit.php?id=$id&mode=modify&no=$row[Mlang_bbs_no]', 'data_submit','width=600,height=300,top=10,left=10,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();\" value='����' style='width:50;'>
<input type='button' onClick=\"javascript:ResultsDelTT('$row[Mlang_bbs_no]');\" value='����'  style='width:50;'>	
</td>
</tr>
");		
		
		$i=$i+1;
} 

echo("</table>");

}else{
	
if($search){
	echo"<p align=center><b>$search</b> �� ���õ� ��� �ڷ����</p>";
}else{
	echo"<p align=center><b>��� �ڷ����</b></p>";
}	

}

?>

<p align='center'>

<?php
if($rows){

if($search){
$mlang_pagego="mode=$mode&id=$id&search_cate=$search_cate&search=$search"; // �ʵ�Ӽ��� ���ް�
}else{
$mlang_pagego="mode=$mode&id=$id"; // �ʵ�Ӽ��� ���ް�
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




</td></tr>
</table>