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


$qry="delete from MlangOrder_PrintAuto where no='$check[$i]'";
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

if($mode=="sendback"){ // �ݼ�ó�� 
include"../title.php";
?>

<head>
<script src="../js/coolbar.js" type="text/javascript"></script>

<script language=javascript>
window.moveTo(screen.width/5, screen.height/5); 

function MemberCheckField()
{
var f=document.FrmUserInfo;
if (f.cont.value == "") {
alert("�ݼ������� ���� �ּž� ó���Ҽ� �ֽ��ϴ�....");
f.cont.focus();
return false;
}
}
</script>
</head>

<body LEFTMARGIN='5' TOPMARGIN='5' MARGINWIDTH='5' MARGINHEIGHT='5' class='coolBar'>

<table border='0' align='center' width='100%' cellpadding='10' cellspacing='5'>
<tr><td bgcolor='#336699'>
<font style='font-size:10pt; line-hright:130; color:#FFFFFF;'>
�ݼ� ����(�����ȣ ��...) �� ���� �ּ���....<BR>
<font style='font-size:8pt;'><font color=red>*</font> �ݼ� ó���ϸ� PM ȸ�� ������ ���հ迡�� �ڵ� ���� �˴ϴ�.</font>
</font>
</td></tr>
<form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField()' action='<?php echo $PHP_SELF?>'>
<tr><td>
<INPUT TYPE="hidden" name='mode' value='sendback_ok'>
<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>
<INPUT TYPE="text" NAME="cont" size=50>
<input type='submit' value='ó���ϱ�'>
</td></tr>
</form>
</table>

</body>

</html>


<?php
exit;
} ////////////////////////////////////////////////////////////////////////////////////////////////////////
if($mode=="sendback_ok"){

$Eroor="<script language=javascript>
             window.alert('DataBase ���� �Դϴ�.');
             opener.parent.location.reload();
             window.self.close();
               </script>";

// �������� ������ no ���� MlangOrder_PrintAuto�� ��ȣ�̴�.
include"../../db.php";
$date=date("Y-m-d H:i;s");

   $result= mysql_query("select * from MlangOrder_PrintAuto where no='$no'",$db);
   $row= mysql_fetch_array($result);
     if($row){

	         // ȸ����ü ������ ��� ���� MlangPM_MemberTotalMoney
             //ȸ�� ���� ������ �ľ� TotalMoney
			 $result_Pluse= mysql_query("select * from MlangPM_MemberTotalMoney where id='$row[PMmember]' order by no desc limit 0, 1",$db);
             $row_Pluse= mysql_fetch_array($result_Pluse);
             if($row_Pluse){$SS_TotalMoney="$row_Pluse[TotalMoney]"; $TotalMoneyNo="$row_Pluse[no]";}else{echo ("$Eroor"); exit;}
			
			 $result_MemberMoney= mysql_query("select * from MlangPM_MemberMoney where PMThingOrderNo='$row[no]'",$db);
             $row_MemberMoney= mysql_fetch_array($result_MemberMoney);
             if($row_MemberMoney){$SS_MemberMoney="$row_MemberMoney[Money_2]";}else{echo ("$Eroor"); exit;}
             
			 // ȸ����ü ������ ��� ���� 
             $SS_TotalMoney_ok=$SS_TotalMoney-$SS_MemberMoney;
             $query ="UPDATE MlangPM_MemberTotalMoney SET TotalMoney='$SS_TotalMoney_ok' WHERE no='$TotalMoneyNo'";
             $result= mysql_query($query,$db);
             ////////////////////////////////////////////////////////////

             // �ֹ� ���̺� �ݼ����� ���� MlangOrder_PrintAuto   OrderStyle �ݼ� ó�� 6�� ok
             $query ="UPDATE MlangOrder_PrintAuto SET OrderStyle='6' WHERE no='$no'";
             $result= mysql_query($query,$db);
			 ///////////////////////////////////////////////////////////
             
			 // MlangPM_MemberMoney ���̺� sendback�ʵ忡 �ݼ� ��� ����  TakingStyle�ʵ忡 �ݼ� ��� ok
             $query ="UPDATE MlangPM_MemberMoney SET TakingStyle='�ݼ�', sendback='$cont', sendback_date='$date' WHERE PMThingOrderNo='$no'";
             $result= mysql_query($query,$db);
			 ///////////////////////////////////////////////////////////

              	echo ("
		<script language=javascript>
		alert('$no ���� �ڷ��� ���������� �ݼ�ó�� �Ͽ����ϴ�.');
        opener.parent.location.reload();
        window.self.close();
		</script>
	           ");


         }else{
                         echo ("$Eroor"); exit;
                  }
                         mysql_close($db); 


exit;
} ////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="delete"){

include"../../db.php";
$result = mysql_query("DELETE FROM MlangOrder_PrintAuto WHERE no='$no'");

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

if($mode=="OrderStyleModify"){
include"../../db.php";

$query ="UPDATE MlangOrder_PrintAuto SET OrderStyle='$JK' WHERE no='$no'";
$result= mysql_query($query,$db);
	
	echo ("
			<script language=javascript>
		alert('$no ���� �ڷḦ ���������� ���� ó���Ͽ����ϴ�.');
		</script>
	<meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>
		");

mysql_close($db);

exit;
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////


$M123="..";
include"../top.php"; 
?>

<head>

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
<font color=red>*</font> �ֹ������� ���ø� �ڵ����� �����Ϸ�� ó�� �˴ϴ�.<BR>
<font color=red>*</font> �þ����� �� �����ø� �þ� �ڷḦ ���� �ø��Ǽ� �ֽ��ϴ�.<BR>
<font color=red>*</font> ��¥�� �˻��� - �� �־��ּž� �մϴ�. ( ��: 2005-03-03 ~ 2006-11-21 )<BR>
</td>
	 <td align=right><BR>
	 <input type='button' onClick="javascript:popup=window.open('admin.php?mode=OrderView', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' Order ���ڷ� �Է�'>
	 </td>
</tr>
<tr>
<td align=left colspan=2>

<?$CateFF="style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;' selected";?>

   <table border=0 cellpadding=2 cellspacing=0 width=100%> 
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?php echo $PHP_SELF?>'>
	    <td align=left>
<select name='Type'>
<option value='total'>��ü</option>
<option value='inserted' <?php if ($Type=="inserted"){echo("$CateFF");}?>>������</option>
<option value='sticker' <?php if ($Type=="sticker"){echo("$CateFF");}?>>��Ƽī</option>
<option value='NameCard' <?php if ($Type=="NameCard"){echo("$CateFF");}?>>����</option>
<option value='MerchandiseBond' <?php if ($Type=="MerchandiseBond"){echo("$CateFF");}?>>��ǰ��</option>
<option value='envelope' <?php if ($Type=="envelope"){echo("$CateFF");}?>>����</option>
<option value='NcrFlambeau' <?php if ($Type=="NcrFlambeau"){echo("$CateFF");}?>>�����</option>
<option value='cadarok' <?php if ($Type=="cadarok"){echo("$CateFF");}?>>���÷�</option>
<option value='cadarokTwo' <?php if ($Type=="cadarokTwo"){echo("$CateFF");}?>>ī�ٷα�</option>
<option value='LittlePrint' <?php if ($Type=="LittlePrint"){echo("$CateFF");}?>>�ҷ��μ�</option>
</select>
		<select name='Cate'>
		<option value='name' <?php if ($Cate=="name"){echo("$CateFF");}?>>��ȣ/����</option>
		<option value='phone' <?php if ($Cate=="phone"){echo("$CateFF");}?>>��ȭ��ȣ</option>
		<option value='Hendphone' <?php if ($Cate=="Hendphone"){echo("$CateFF");}?>>�޴���</option>
		<option value='bizname' <?php if ($Cate=="bizname"){echo("$CateFF");}?>>�μ⳻��</option>
        <option value='OrderStyle' <?php if ($Cate=="OrderStyle"){echo("$CateFF");}?>>�������</option>
		</select>
&nbsp;��¥�˻� :&nbsp;
<input type='text' name='YearOne' size='14' onClick="Calendar(this);">
~
<input type='text' name='YearTwo' size='14' onClick="Calendar(this);">
		&nbsp;&nbsp;<b>�˻��� :&nbsp;</b>
        <input type='text' name='TDsearchValue' size='45'>
        <input type='submit' value=' �� �� '>
		<?php if ($Type){?>
		<input type='button' onClick="javascript:window.location='<?php echo $PHP_SELF?>';" value='ó������..'>
		<?php } ?>
	    </td>
		</form>
	 </tr>
  </table>

</td>

</tr>
</table>

<!------------------------------------------- ����Ʈ ����----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>��ȣ</td>
<td align=center>�о�</td>
<td align=center>�ֹ��μ���</td>
<td align=center>�ֹ���¥</td>
<td align=center>���ó��</td>
<td align=center>�þ�</td>
<td align=center>�ֹ�������</td>
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
$table="MlangOrder_PrintAuto";

if($Type){ // �˻��� ��

if($YearOne){ if(!$YearTwo){ $msg="��¥ �˻��� �Ͻ÷���  ~ ���� �� ���� �Է��� �ּž� �մϴ�."; Error($msg); }  }
if($YearTwo){ if(!$YearOne){ $msg="��¥ �˻��� �Ͻ÷���  ~ ���� �� ���� �Է��� �ּž� �մϴ�."; Error($msg); }  }

if($Type=="total"){$TypeOk="";}else{$TypeOk="and Type='$Type'";}

        if($YearOne || $YearTwo){ $YearOneOk=$YearOne." 00:00:00";  $YearTwoOk=$YearTwo." 00:00:00"; 
		   $Mlang_query="select * from $table where date > '$YearOneOk' and date < '$YearTwoOk' $TypeOk and $Cate like '%$TDsearchValue%'";
		  }else{
		    $Mlang_query="select * from $table where $Cate like '%$TDsearchValue%' $TypeOk";
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
<td align=center><font color=white>
<?php if ($row[Type]=="inserted"){?>������
<?}else if($row[Type]=="sticker"){?>��Ƽī
<?}else if($row[Type]=="NameCard"){?>����
<?}else if($row[Type]=="MerchandiseBond"){?>��ǰ��
<?}else if($row[Type]=="envelope"){?>����
<?}else if($row[Type]=="NcrFlambeau"){?>�����
<?}else if($row[Type]=="cadarok"){?>���÷�
<?}else if($row[Type]=="cadarokTwo"){?>ī�ٷα�
<?}else if($row[Type]=="LittlePrint"){?>�ҷ��μ�
<?}else{echo("$row[Type]");}?>
</font></td>
<td align=center><font color=white><?php echo htmlspecialchars($row[name]);?></font></td>
<td align=center><font color=white><?php echo htmlspecialchars($row[date]);?></font></td>
<td align=center>
<script>
function MM_jumpMenuYY_<?php echo $row[no]?>G(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
<select onChange="MM_jumpMenuYY_<?php echo $row[no]?>G('parent',this,0)">
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=1&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="1"){echo("selected style='font-size:10pt; background-color:#6600FF; color:#FFFFFF;'");}?>>��������</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=2&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="2"){echo("selected style='font-size:10pt; background-color:#6600FF; color:#FFFFFF;'");}?>>�ֹ�����</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=3&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="3"){echo("selected style='font-size:10pt; background-color:#6633CC; color:#FFFFFF;'");}?>>�����Ϸ�</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=4&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="4"){echo("selected style='font-size:10pt; background-color:#CC0066; color:#FFFFFF;'");}?>>�Աݴ��</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=5&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="5"){echo("selected style='font-size:10pt; background-color:#993333; color:#FFFFFF;'");}?>>�þ�������</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=6&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="6"){echo("selected style='font-size:10pt; background-color:#333300; color:#FFFFFF;'");}?>>�þ�</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=7&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="7"){echo("selected style='font-size:10pt; background-color:#336600; color:#FFFFFF;'");}?>>����</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=8&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="8"){echo("selected style='font-size:10pt; background-color:#000000; color:#FFFFFF;'");}?>>�۾��Ϸ�</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=9&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="9"){echo("selected style='font-size:10pt; background-color:#333399; color:#FFFFFF;'");}?>>�۾���</option>
<option value='<?php echo $PHP_SELF?>?mode=OrderStyleModify&JK=10&no=<?php echo $row[no]?>' <?php if ($row[OrderStyle]=="10"){echo("selected style='font-size:10pt; background-color:#660000; color:#FFFFFF;'");}?>>�����۾���</option>
</select>
</td>
<td align=center>

<input type='button' onClick="javascript:popup=window.open('admin.php?mode=SinForm&coe&no=<?php echo $row[no]?><?php if ($row[ThingCate]){?>&ModifyCode=ok<?php } ?>', 'SinHH','width=600,height=100,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='����/�þ� <?php if ($row[ThingCate]){?>����<?}else{?>���<?php } ?>'>

</td>

<td align=center>
<input type='button' onClick="javascript:popup=window.open('admin.php?mode=OrderView&no=<?php echo $row[no]?>', 'MViertW','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='�ֹ���������'>
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

<?php
include"../down.php";
?>