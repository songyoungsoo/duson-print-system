<?php
if($mode=="ChickBoxAll"){ // ��ۿϷ�ó�� 

include"../../db.php";

if ( !$check ) {
echo ("<script language=javascript>
window.alert('���� [ó��]�� üũ�׸��� �����ϴ�.\\n\\n[����] ó���� ���� üũ�Ͽ� �ֽʽÿ�.');
history.go(-1);
</script>
");
exit;
}


for($i=0;$i<count($check);$i++) {

	// ������Ʈ ImgFolder  �� ���߾��ε��������� �����Ͽ���... �����ؾ� �Ѵ�.					 	
	//$Mlang_DIR = opendir("../../MlangOrder_PrintAuto/upload/$check[$i]"); // upload ���� OPEN
	//while($ufiles = readdir($Mlang_DIR)) {
		//if(($ufiles != ".") && ($ufiles != "..")) {
		//	unlink("../../MlangOrder_PrintAuto/upload/$check[$i]/$ufiles"); // ���ϵ� ����
		//}
	//}
	//closedir($Mlang_DIR);

	//rmdir("../../MlangOrder_PrintAuto/upload/$check[$i]");  // upload ���� ����


$qry="delete from MlangPrintAuto_MemberOrderOffice where no='$check[$i]'";
mysql_query($qry);
}

mysql_close();

          echo ("<script language=javascript>
          window.alert('üũ�� �׸��� ���������� [����] ó�� �Ͽ����ϴ�..');
           </script>
           <meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>
            ");
            exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="delete"){

include"../../db.php";
$result = mysql_query("DELETE FROM MlangPrintAuto_MemberOrderOffice WHERE no='$no'");

	echo ("
		<script language=javascript>
		alert('$no ���� �ڷ��� ���������� ���� �Ͽ����ϴ�.');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
mysql_close();

exit;
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////


$M123="..";
include"../top.php"; 

echo ("<script language=javascript>
window.alert('�������� [�ֹ��� �����Ϻ�] �� ���α׷��� �׽�Ʈ �غ��Ǽ� �ֽ��ϴ�.\\n\\n�׷��� �� ���α׷��� 1��  �������� �Ƿڿ� ���߾� ���� �Ͽ�������\\n\\n��뿩�ο� ���Ͽ����� ������ �����ϼž� �մϴ�.');
</script>
");
	
?><head>

<script>
function popUp(L, e) {
if(n4) {
var barron = document.layers[L]
barron.left = e.pageX 
barron.top = e.pageY + 5
barron.visibility = "visible"
}
else if(e4) {
var barron = document.all[L]
barron.style.pixelLeft = event.clientX + document.body.scrollLeft 
barron.style.pixelTop = event.clientY + document.body.scrollTop + 5
barron.style.visibility = "visible"
}
}
function popDown(L) {
if(n4) document.layers[L].visibility = "hidden"
else if(e4) document.all[L].style.visibility = "hidden"
}
n4 = (document.layers) ? 1 : 0
e4 = (document.all) ? 1 : 0
</script>

<script>
function allcheck(MemoPlusecheckForm) { 
for( var i=0; i<MemoPlusecheckForm.elements.length; i++) { 
var check = MemoPlusecheckForm.elements[i]; 
check.checked = true; 
} 
return; 
} 

function uncheck(MemoPlusecheckForm) { 
for( var i=0; i<MemoPlusecheckForm.elements.length; i++) { 
var check = MemoPlusecheckForm.elements[i]; 
check.checked = false; 
} 
return; 
} 

function DelGCheckField(){
if (confirm('�ڷ��� ����ó�� �Ͻ÷� �Ͻʴϴ�....\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!')){
document.MemoPlusecheckForm.action="<?php echo $PHP_SELF?>";
document.MemoPlusecheckForm.submit(); 
} 
}
</script>

<SCRIPT LANGUAGE=JAVASCRIPT src='../js/exchange.js'></SCRIPT>

</head>




<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>

<?$CateFF="style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;' selected";?>

   <table border=0 cellpadding=2 cellspacing=0 width=100%> 
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?php echo $PHP_SELF?>'>
	    <td align=left>
&nbsp;��¥�˻� :&nbsp;
<input type='text' name='YearOne' size='10' onClick="Calendar(this);" value='<?php echo $YearOne?>'>
~
<input type='text' name='YearTwo' size='10' onClick="Calendar(this);" value='<?php echo $YearTwo?>'>
&nbsp;&nbsp;
<select name='Type'>
<option value='One_1' <?php if ($Type=="inserted"){echo("$CateFF");}?>>�ۼ���</option>
<option value='One_3' <?php if ($Type=="sticker"){echo("$CateFF");}?>>��ü��</option>
</select>
		&nbsp;&nbsp;<b>�˻��� :&nbsp;</b>
        <input type='text' name='TDsearchValue' size='30'>
        <input type='submit' value=' �� �� '>
		<?php if ($Type){?>
		<input type='button' onClick="javascript:window.location='<?php echo $PHP_SELF?>';" value='ó������..'>
		<?php } ?>
	    </td>
		</form>
	 </tr>
  </table>

</td>
	 <td align=right>
	 <input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/int.php?mode=bizinfo', 'MViertbizinfo','width=450,height=300,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='���������'>
	 <input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/admin.php?mode=form', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' ���ڷ� �Է� '>
	 </td>
</tr>
</table>

<!------------------------------------------- ����Ʈ ����----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>��ȣ</td>
<td align=center>�ۼ���</td>
<td align=center>��ü��</td>
<td align=center>��ü����</td>
<td align=center>�����</td>
<td align=center>����</td>
</tr>

<form method='post' name='MemoPlusecheckForm'>
<INPUT TYPE="hidden" name='mode' value='ChickBoxAll'>

<?php
function Error($msg) {
echo ("<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>
");
exit;
}

include"../../db.php";
$table="MlangPrintAuto_MemberOrderOffice";

if($Type){ // �˻��� ��

if($YearOne){ if(!$YearTwo){ $msg="��¥ �˻��� �Ͻ÷���  ~ ���� �� ���� �Է��� �ּž� �մϴ�."; Error($msg); }  }
if($YearTwo){ if(!$YearOne){ $msg="��¥ �˻��� �Ͻ÷���  ~ ���� �� ���� �Է��� �ּž� �մϴ�."; Error($msg); }  }

        if($YearOne || $YearTwo){ $YearOneOk=$YearOne." 00:00:00";  $YearTwoOk=$YearTwo." 00:00:00"; 
		   $Mlang_query="select * from $table where date > '$YearOneOk' and date < '$YearTwoOk' and $Type like '%$TDsearchValue%'";
		  }else{
		    $Mlang_query="select * from $table where $Type like '%$TDsearchValue%'";
		}	             

}else{ // �Ϲݸ�� �϶�
$Mlang_query="select * from $table";
}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 20;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 

if($CountWW){
$result= mysql_query("$Mlang_query order by $CountWW $s limit $offset,$listcut",$db);
}else{
$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);
}

$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>


<tr bgcolor='#575757'>
<td align=center>
&nbsp;
<?php if ($row[OrderStyle]=="5"){}else{?>
<input type=checkbox name=check[] value='<?php echo $row[no]?>'>
<?php } ?>
<font color=white><?php echo $row[no]?></font>
&nbsp;
</td>
<td align=center><font color=white><?php echo htmlspecialchars($row[One_1]);?></font></td>
<td align=center><font color=white><?php echo htmlspecialchars($row[One_3]);?></font></td>
<td align=center><font color=white>
<?php if ($row[One_2]=="1"){?>�űԾ�ü<?php } ?>
<?php if ($row[One_2]=="2"){?>�ŷ���ü<?php } ?>
<?php if ($row[One_2]=="3"){?>��û<?php } ?>
</font></td>
<td align=center><font color=white><?php echo htmlspecialchars($row[date]);?></font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/admin.php?mode=form&code=fff&no=<?php echo $row[no]?>', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value='�������'>
<input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/admin.php?mode=form&code=Print&no=<?php echo $row[no]?>', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value='�μ���'>
<input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/admin.php?mode=form&code=modify&no=<?php echo $row[no]?>', 'MViertWmodify','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='����'>
</td>
</tr>

<?php
		$i=$i+1;
} 


}else{

if($TDsearchValue){ // ȸ�� ���ܰ˻� TDsearch //  TDsearchValue
echo"<tr><td colspan=10><p align=center><BR><BR>$TDsearch �� �˻��Ǵ� $TDsearchValue - ���� �˻� �ڷ����</p></td></tr>";
}else if($OrderCate){
echo"<tr><td colspan=10><p align=center><BR><BR>$cate �� �˻��Ǵ� - ���� �˻� �ڷ����</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>��� �ڷ����</p></td></tr>";
}

}

?>

<tr><td colspan=12 height=10></td></tr>
</table>

     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td>
<input type='button' onClick="javascript:allcheck(MemoPlusecheckForm);" value=' �� ü �� �� '><input type='button' onClick="javascript:uncheck(MemoPlusecheckForm);" value=' �� �� �� �� '><input type='button' onClick="javascript:DelGCheckField();" value=' üũ�׸� �� �� '>
		 </td>
       </tr>
	   </form>
     </table>



<p align='center'>

<?php
if($rows){

if($TDsearchValue){
$mlang_pagego="TDsearch=$TDsearch&TDsearchValue=$TDsearchValue"; // �ʵ�Ӽ��� ���ް�
}else if($OrderStyleYU9OK){
$mlang_pagego="OrderStyleYU9OK=$OrderStyleYU9OK"; // �ʵ�Ӽ��� ���ް�
}else if($OrderCate){
$mlang_pagego="OrderCate=$OrderCate"; // �ʵ�Ӽ��� ���ް�
}else{}

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