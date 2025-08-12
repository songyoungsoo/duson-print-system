<?php
if($mode=="view"){

include"../title.php";

include"../../db.php";
$result= mysql_query("select * from MlangOrder_LoveStry104 where no='$no'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
{
$BBAdminSelect="$row[AdminSelect]";	
?>

<style>
.td1 {font-family:����; font-size: 9pt;; color:#FFFFFF; font-weight:bold; line-height: normal;}
.td2 {font-family:����; font-size: 9pt;; color:#008080; font-weight:none; line-height:130%;}
</style>

</head>

<BR>
<table border=0 align=center width=90% cellpadding='0' cellspacing='1' bgcolor='#65B1B1'>
<tr><td valign=top>

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='#FFFFFF'>
<tr>
<td bgcolor='#65B1B1' width=100 class='td1' align='left'>&nbsp;ȸ���&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?php echo $row[fild_1]?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;��ǥ���̸�&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[fild_2]?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;������ּ�&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[fild_3]?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;������̸�&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[fild_4]?>
</td>
</tr>


<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;������̸���&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[fild_5]?>
</td>
</tr>


<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;�������ȭ��ȣ&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo $row[fild_6]?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;����&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php
        $CONTENT=$row[fild_7];
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;	
echo("$connent_text");
?>
</tr>

</table>

</td></tr></table>

<p align=center>
<input type='button' onClick='javascript:window.close();' value='â�ݱ�-CLOSE' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
</p>

<?php
}

}else{echo("<p align=center><b>��� �ڷᰡ ����.</b></p>");}

mysql_close($db); 


///////^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^/////////

if($BBAdminSelect=="no"){

include"../../db.php";
$query ="UPDATE MlangOrder_LoveStry104 SET AdminSelect='yes' WHERE no='$no'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
         opener.parent.location.reload();
		</script>
	");
		exit;

}

mysql_close($db);
}



exit;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

include"../../db.php";
$result = mysql_query("DELETE FROM MlangOrder_LoveStry104 WHERE no='$no'");
mysql_close();

	echo ("
		<script language=javascript>
		alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;

}
?>


<?php
$M123="..";
include"../top.php"; 
?>

<head>
<script>
function Member_Admin_Del(no){
	if (confirm(+no+'�� �� ��� �ڷḦ ���� �Ͻðڽ��ϱ�..?\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!')) {
		str='<?php echo $PHP_SELF?>?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
</script>

</head>


<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=12>
<font color=red>*</font>�����ڰ� �ڷḦ �鿩�� ���ڷ�� Ȯ������ �ڵ����� ���ŵ˴ϴ�.<BR>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>��Ϲ�ȣ</font></td>
<td align=center>ȸ���</font></td>
<td align=center>����Time</td>
<td align=center>Ȯ�ο���</td>
<td align=center>�ڼ�����������</td>
<td align=center>����</td>
<tr>

<?php
include"../../db.php";
$table="MlangOrder_LoveStry104";


$Mlang_query="select * from $table";

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 12;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 

$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo $row[no]?></font></td>
<td align=center><font color=white><?php echo $row[fild_1]?></font></td>
<td align=center><font color=white><?php echo $row[date]?></font></td>
<td align=center>
<?php
if($row[AdminSelect]=="no"){echo("<b><font color=red>��Ȯ��</font></b>");}
if($row[AdminSelect]=="yes"){echo("<font color=white>Ȯ��</font>");}
?>
</td>
<td align=center><input type='button' onClick="javascript:popup=window.open('<?php echo $PHP_SELF?>?mode=view&no=<?php echo $row[no]?>', 'MlangOrder_LoveStry104','width=600,height=430,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' �ڼ����������� '></font></td>
<td align=center>
<input type='button' onClick="javascript:Member_Admin_Del('<?php echo $row[no]?>');" value=' ���� '>
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