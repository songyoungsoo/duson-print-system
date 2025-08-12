<?php
$M123="..";
include"../top.php"; 

$PageCode="Movic";
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
	if (confirm(+no+'�� �ڷḦ ���� �Ͻðڽ��ϱ�..?\n\n�ֻ��� �ϰ�� �����׸���� ������ �˴ϴ�.\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!')) {
		str='./<?php echo $PageCode?>/CateAdmin.php?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

</head>

<?php
include "../../db.php";
$table="MlangHomePage_Movic";

if($HomePage_YearCate){ // �˻�
$Mlang_query="select * from $table where BigNo='$HomePage_YearCate'";
}else{ // �Ϲݸ�� �϶�
$Mlang_query="select * from $table";
}

$query= mysqli_query($db, "$Mlang_query");
$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows();

$listcut= 30;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 
?>


<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
&nbsp;
</td>
</tr>
<tr>
<td align=right valign=bottom>
<input type='button' onClick="javascript:popup=window.open('./<?php echo $PageCode?>/CateAdmin.php?mode=form', 'WebOffice_<?php echo $PageCode?>Form','width=680,height=400,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' ������ ���� �Է��ϱ� '>
</td>
</tr>
</table>


<!------------------------------------------- ����Ʈ ����----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>���NO</td>
<td align=center>���ϸ�</td>
<td align=center>����</td>
<td align=center>�������</td>
</tr>

<?php
$result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
$rows=mysqli_num_rows($result);
if($rows){


while($row= mysqli_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo $row[no]?></font></td>
<td>&nbsp;&nbsp<a href='./Movic/upload/<?php echo $row[upfile]?>' target='_blank'><font color=white><?php echo $row[upfile]?></font></a>&nbsp;&nbsp</td>
<td>&nbsp;&nbsp;<font color=white><?php echo $row[title]?></font>&nbsp;&nbsp;</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./<?php echo $PageCode?>/CateAdmin.php?mode=form&code=modify&no=<?php echo $row[no]?>', 'WebOffice_<?php echo $PageCode?>Modify','width=680,height=400,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' ���� '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?php echo $row[no]?>');" value=' ���� '>
</td>
<tr>

<?php
		$i=$i+1;
} 


}else{

if($HomePage_YearCate){
echo"<tr><td colspan=10><p align=center><b>$HomePage_YearCate</b> ����<BR><BR> �˻� �ڷ����</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>��� �ڷ����</p></td></tr>";
}

}

?>


</table>



<p align='center'>

<?php
if($rows){

if($HomePage_YearCate){
       $mlang_pagego="HomePage_YearCate=$HomePage_YearCate&offset=$offset"; // �ʵ�Ӽ��� ���ް�
}else{
     $mlang_pagego="offset=$offset"; // �ʵ�Ӽ��� ���ް�
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
  echo "<a href='$_SERVER['PHP_SELF']?offset=$apoffset&$mlang_pagego'>...[����]</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset){
  echo "&nbsp;<a href='$_SERVER['PHP_SELF']?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;"; 
}else{echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"); } 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$_SERVER['PHP_SELF']?offset=$nextoffset&$mlang_pagego'>[����]...</a>"; 
} 
echo "�Ѹ�ϰ���: $end_page ��"; 


}

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- ����Ʈ ��----------------------------------------->

<?php
include "../down.php";
?>