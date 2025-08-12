<?php
$M123="..";
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=780,screen.availHeight)

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

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.biz_date.value == "") {
alert("�ŷ����� �� �Է��Ͽ��ּ���!!");
f.biz_date.focus();
return false;
}

if (f.kinds.value == "") {
alert("���� �� �Է��Ͽ��ּ���!!");
f.kinds.focus();
return false;
}

if (f.fitting_no.value == "") {
alert("����ȣ �� �Է��Ͽ��ּ���!!");
f.fitting_no.focus();
return false;
}

if (f.engineer_name.value == "") {
alert("���� �� �Է��Ͽ��ּ���!!");
f.engineer_name.focus();
return false;
}

if (f.money.value == "") {
alert("�ݾ� �� �Է��Ͽ��ּ���!!");
f.money.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("�ݾ� �� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.money.focus();
return false;
}


}
</script>
<script src="../../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<?php if (!$code){?>
<table border=0 align=center width=100% cellpadding=5 cellspacing=0>
<tr bgcolor='#<?php echo $Bgcolor1?>'>
<td class='Left1' align=left colspan=6>&nbsp;&nbsp;�ŷ������� �����Է¶�</td>
<td align=right><font color='#FFFFFF'>���NO: <b><?php echo g><?=$no?></big></b>&nbsp;&nbsp;</font></td>
</tr>
</table>


<table border=0 align=center width=100% cellpadding=5 cellspacing=1>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����ó&nbsp;&nbsp;</td>
<td><?echo("$Viewbiz_name");?></td>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�����&nbsp;&nbsp;</td>
<td><?echo("$Viewa_name");?></td>
</tr>


<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>�����&nbsp;&nbsp;</td>
<td><?echo("$Viewb_name");?></td>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>����TEL&nbsp;&nbsp;</td>
<td>
<?echo("$Viewtel_1");?>
-
<?echo("$Viewtel_2");?>
-
<?echo("$Viewtel_3");?>
</td>
</tr>
</table>
<?php } ?>

<table border=0 align=center width=100% cellpadding=5 cellspacing=1 bgcolor='#<?php echo $Bgcolor1?>'>

<tr><td align=center colspan=6 height=4>
<font color=red>*</font> �ŷ����ڴ� ��) 2006-10-23  ���� ������ �ø��ž� �ϸ� �Է�â�� ���콺�� Ŭ���ϸ� �ڵ��Է�â�� ���ɴϴ�.
</td></tr>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='SoForm_ok'>
<INPUT TYPE="hidden" name='admin_no' value='<?php echo $no?>'>

<tr>
<td align=center class='coolBar' height=25>�ŷ�����</td>
<td align=center class='coolBar'>����</td>
<td align=center class='coolBar'>����ȣ</td>
<td align=center class='coolBar'>����</td>
<td align=center class='coolBar'>�ݾ�</td>
<td align=center class='coolBar'>���</td>
</tr>
<tr bgcolor='#575757'>
<td align=center><?$FormYear="biz_date"; include"../../int/almanac.php";?></td>
<td align=center><INPUT TYPE="text" NAME="kinds" size=13 maxLength='50'></td>
<td align=center><INPUT TYPE="text" NAME="fitting_no" size=13 maxLength='50'></td>
<td align=center><INPUT TYPE="text" NAME="engineer_name" size=13 maxLength='50'></td>
<td align=center><INPUT TYPE="text" NAME="money" size=13 maxLength='50'></td>
<td align=center><INPUT TYPE="text" NAME="remark" size=13 maxLength='50'></td>
</tr>

<tr>
<td align=center colspan=6>
<input type='submit' value=' ���� �մϴ�.'>
</td>
</tr>
</form>
<tr><td align=center colspan=6 height=10></td></tr>

</table>

<head>
<script>
function MlangWebOffice_Biz_particularsSoDel(no){
	if (confirm('�ڷḦ �����Ͻðڽ��ϱ�.........*^^*\n\n�ѹ� ������ �ڷ�� ���� ���� ������ ������ �����ּ���.............!!')) {
		str='<?php echo $PHP_SELF?>?no='+no+'&mode=SoDelete&Big_no=<?php echo $no?>';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function TDsearchCheckField()
{
var f=document.TDsearch;

if (f.TDsearchValue.value == "") {
alert("�˻��� �˻��� ���� �Է����ּ���");
f.TDsearchValue.focus();
return false;
}

}
</script>

<style>
.SoList {font-size:9pt; color:#FFFFFF;}
</style>

</head>


<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>

<td align=left>
<?php
if($TDsearch){ 
	echo("<input type='button' onClick=\"javascript:window.location.href ='$PHP_SELF?mode=$mode&no=$no';\" value='��ü�������..'>");
}
?>
</td>

<td align=right>

   <table border=0 align=center width=100% cellpadding=2 cellspacing=0>
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?php echo $PHP_SELF?>'>
		<?php if ($code){?><INPUT TYPE="hidden" name='code' value='<?php echo $code?>'><?php } ?>
		<INPUT TYPE="hidden" name='mode' value='<?php echo $mode?>'>
		<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>
		<INPUT TYPE="hidden" name='Big_no' value='<?php echo $no?>'>
	    <td align=right>
		<b>�˻� :&nbsp;</b>
		<select name='TDsearch'>
		<option value='kinds'>����</option>
		<option value='fitting_no'>����ȣ</option>
		<option value='engineer_name'>����</option>
        <input type='text' name='TDsearchValue' size='20'>
        <input type='submit' value=' �� �� '>
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
<td align=center class='coolBar' height=25>�ŷ�����</td>
<td align=center class='coolBar'>����</td>
<td align=center class='coolBar'>����ȣ</td>
<td align=center class='coolBar'>����</td>
<td align=center class='coolBar'>�ݾ�</td>
<td align=center class='coolBar'>���</td>
<td align=center class='coolBar'>����</td>
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

$listcut= 20;  //�� �������� ������ ��� �Խù���. 
if(!$offset) $offset=0; 

$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);

$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>

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

function SoModifypart99<?php echo $row[no]?>_CheckField()
{
var f=document.SoModifypart99<?php echo $row[no]?>;

if (f.biz_date.value == "") {
alert("�ŷ����� �� �Է��Ͽ��ּ���!!");
f.biz_date.focus();
return false;
}

if (f.kinds.value == "") {
alert("���� �� �Է��Ͽ��ּ���!!");
f.kinds.focus();
return false;
}

if (f.fitting_no.value == "") {
alert("����ȣ �� �Է��Ͽ��ּ���!!");
f.fitting_no.focus();
return false;
}

if (f.engineer_name.value == "") {
alert("���� �� �Է��Ͽ��ּ���!!");
f.engineer_name.focus();
return false;
}

if (f.money.value == "") {
alert("�ݾ� �� �Է��Ͽ��ּ���!!");
f.money.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("�ݾ� �� ���ڷθ� ����� �� �ֽ��ϴ�.");
f.money.focus();
return false;
}


}
</script>
</head>

<tr bgcolor='#575757'>
<form name='SoModifypart99<?php echo $row[no]?>' method='post' OnSubmit='javascript:return SoModifypart99<?php echo $row[no]?>_CheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='SoModifypart99'>
<INPUT TYPE="hidden" name='SoTyuno' value='<?php echo $row[no]?>'>
<INPUT TYPE="hidden" name='Big_no' value='<?php echo $row[admin_no]?>'>
<?php if ($offset){?><INPUT TYPE="hidden" name='offset' value='<?php echo $offset?>'><?php } ?>
<?php if ($TDsearch){?>
<INPUT TYPE="hidden" name='TDsearch' value='<?php echo $TDsearch?>'>
<INPUT TYPE="hidden" name='TDsearchValue' value='<?php echo $TDsearchValue?>'>
<?php } ?>
<td align=center class='SoList'><INPUT TYPE="text" NAME="biz_date" size=13 value='<?php echo $row[biz_date]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="kinds" size=13 value='<?php echo $row[kinds]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="fitting_no" size=13 value='<?php echo $row[fitting_no]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="engineer_name" size=13 value='<?php echo $row[engineer_name]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="money" size=13 value='<?php echo $row[money]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="remark" size=13 value='<?php echo $row[remark]?>' maxLength='50'></td>
<td align=center>
<input type='submit' value='����'>
<input type='button' onClick="javascript:MlangWebOffice_Biz_particularsSoDel('<?php echo $row[no]?>');" value='����'>
</td>
</form>
<tr>

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
<!------------------------------------------- ����Ʈ ��----------------------------------------->