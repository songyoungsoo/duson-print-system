<?php
declare(strict_types=1);


// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

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
alert("거래일자 을 입력하여주세요!!");
f.biz_date.focus();
return false;
}

if (f.kinds.value == "") {
alert("기종 을 입력하여주세요!!");
f.kinds.focus();
return false;
}

if (f.fitting_no.value == "") {
alert("장비번호 을 입력하여주세요!!");
f.fitting_no.focus();
return false;
}

if (f.engineer_name.value == "") {
alert("기사명 을 입력하여주세요!!");
f.engineer_name.focus();
return false;
}

if (f.money.value == "") {
alert("금액 을 입력하여주세요!!");
f.money.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("금액 은 숫자로만 사용할 수 있습니다.");
f.money.focus();
return false;
}


}
</script>
<script src="../../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<?if(!$code){?>
<table border=0 align=center width=100% cellpadding=5 cellspacing=0>
<tr bgcolor='#<?=$Bgcolor1?>'>
<td class='Left1' align=left colspan=6>&nbsp;&nbsp;거래내역서 정보입력란</td>
<td align=right><font color='#FFFFFF'>등록NO: <b><big><?=$no?></big></b>&nbsp;&nbsp;</font></td>
</tr>
</table>


<table border=0 align=center width=100% cellpadding=5 cellspacing=1>
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>제출처&nbsp;&nbsp;</td>
<td><?echo("$Viewbiz_name");?></td>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>담당자&nbsp;&nbsp;</td>
<td><?echo("$Viewa_name");?></td>
</tr>


<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>현장명&nbsp;&nbsp;</td>
<td><?echo("$Viewb_name");?></td>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>본사TEL&nbsp;&nbsp;</td>
<td>
<?echo("$Viewtel_1");?>
-
<?echo("$Viewtel_2");?>
-
<?echo("$Viewtel_3");?>
</td>
</tr>
</table>
<?}?>

<table border=0 align=center width=100% cellpadding=5 cellspacing=1 bgcolor='#<?=$Bgcolor1?>'>

<tr><td align=center colspan=6 height=4>
<font color=red>*</font> 거래일자는 예) 2006-10-23  형식 등으로 올리셔야 하며 입력창을 마우스로 클릭하면 자동입력창이 나옵니다.
</td></tr>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='SoForm_ok'>
<INPUT TYPE="hidden" name='admin_no' value='<?=$no?>'>

<tr>
<td align=center class='coolBar' height=25>거래일자</td>
<td align=center class='coolBar'>기종</td>
<td align=center class='coolBar'>장비번호</td>
<td align=center class='coolBar'>기사명</td>
<td align=center class='coolBar'>금액</td>
<td align=center class='coolBar'>비고</td>
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
<input type='submit' value=' 저장 합니다.'>
</td>
</tr>
</form>
<tr><td align=center colspan=6 height=10></td></tr>

</table>

<head>
<script>
function MlangWebOffice_Biz_particularsSoDel(no){
	if (confirm('자료를 삭제하시겠습니까.........*^^*\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='<?=$PHP_SELF?>?no='+no+'&mode=SoDelete&Big_no=<?=$no?>';
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
alert("검색할 검색어 값을 입력해주세요");
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
	echo("<input type='button' onClick=\"javascript:window.location.href ='$PHP_SELF?mode=$mode&no=$no';\" value='전체목록으로..'>");
}
?>
</td>

<td align=right>

   <table border=0 align=center width=100% cellpadding=2 cellspacing=0>
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?=$PHP_SELF?>'>
		<?if($code){?><INPUT TYPE="hidden" name='code' value='<?=$code?>'><?}?>
		<INPUT TYPE="hidden" name='mode' value='<?=$mode?>'>
		<INPUT TYPE="hidden" name='no' value='<?=$no?>'>
		<INPUT TYPE="hidden" name='Big_no' value='<?=$no?>'>
	    <td align=right>
		<b>검색 :&nbsp;</b>
		<select name='TDsearch'>
		<option value='kinds'>기종</option>
		<option value='fitting_no'>장비번호</option>
		<option value='engineer_name'>기사명</option>
        <input type='text' name='TDsearchValue' size='20'>
        <input type='submit' value=' 검 색 '>
	    </td>
		</form>
	 </tr>
  </table>

</td>

</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center class='coolBar' height=25>거래일자</td>
<td align=center class='coolBar'>기종</td>
<td align=center class='coolBar'>장비번호</td>
<td align=center class='coolBar'>기사명</td>
<td align=center class='coolBar'>금액</td>
<td align=center class='coolBar'>비고</td>
<td align=center class='coolBar'>관리</td>
</tr>

<?php 
$table="MlangWebOffice_Biz_particulars where admin_no='$no'";

if($TDsearch){ // 검색모드일경우
$Mlang_query="select * from $table and $TDsearch like '%$TDsearchValue%'";
}else{
$Mlang_query="select * from $table";
}

$query= mysqli_query($db, "$Mlang_query");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut= 20;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

$result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");

$rows=mysqli_num_rows($result);
if($rows){


while($row= mysqli_fetch_array($result)) 
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

function SoModifypart99<?=$row[no]?>_CheckField()
{
var f=document.SoModifypart99<?=$row[no]?>;

if (f.biz_date.value == "") {
alert("거래일자 을 입력하여주세요!!");
f.biz_date.focus();
return false;
}

if (f.kinds.value == "") {
alert("기종 을 입력하여주세요!!");
f.kinds.focus();
return false;
}

if (f.fitting_no.value == "") {
alert("장비번호 을 입력하여주세요!!");
f.fitting_no.focus();
return false;
}

if (f.engineer_name.value == "") {
alert("기사명 을 입력하여주세요!!");
f.engineer_name.focus();
return false;
}

if (f.money.value == "") {
alert("금액 을 입력하여주세요!!");
f.money.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("금액 은 숫자로만 사용할 수 있습니다.");
f.money.focus();
return false;
}


}
</script>
</head>

<tr bgcolor='#575757'>
<form name='SoModifypart99<?=$row[no]?>' method='post' OnSubmit='javascript:return SoModifypart99<?=$row[no]?>_CheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='SoModifypart99'>
<INPUT TYPE="hidden" name='SoTyuno' value='<?=$row[no]?>'>
<INPUT TYPE="hidden" name='Big_no' value='<?=$row[admin_no]?>'>
<?if($offset){?><INPUT TYPE="hidden" name='offset' value='<?=$offset?>'><?}?>
<?if($TDsearch){?>
<INPUT TYPE="hidden" name='TDsearch' value='<?=$TDsearch?>'>
<INPUT TYPE="hidden" name='TDsearchValue' value='<?=$TDsearchValue?>'>
<?}?>
<td align=center class='SoList'><INPUT TYPE="text" NAME="biz_date" size=13 value='<?=$row[biz_date]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="kinds" size=13 value='<?=$row[kinds]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="fitting_no" size=13 value='<?=$row[fitting_no]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="engineer_name" size=13 value='<?=$row[engineer_name]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="money" size=13 value='<?=$row[money]?>' maxLength='50'></td>
<td align=center class='SoList'><INPUT TYPE="text" NAME="remark" size=13 value='<?=$row[remark]?>' maxLength='50'></td>
<td align=center>
<input type='submit' value='수정'>
<input type='button' onClick="javascript:MlangWebOffice_Biz_particularsSoDel('<?=$row[no]?>');" value='삭제'>
</td>
</form>
<tr>

<?php 
		$i=$i+1;
} 


}else{

if($search){
echo"<tr><td colspan=10><p align=center><BR><BR>관련 검색 자료없음</p></td></tr>";
}else if($TDsearchValue){ // 회원 간단검색 TDsearch //  TDsearchValue
echo"<tr><td colspan=10><p align=center><BR><BR>$TDsearch 로 검색되는 $TDsearchValue - 관련 검색 자료없음</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>등록 자료없음</p></td></tr>";
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

$pagecut= 7;  //한 장당 보여줄 페이지수 
$one_bbs= $listcut*$pagecut;  //한 장당 실을 수 있는 목록(게시물)수 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  //각 장에 처음 페이지의 $offset값. 
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;  //마지막 장의 첫페이지의 $offset값. 
$start_page= intval($start_offset/$listcut)+1; //각 장에 처음 페이지의 값. 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 
//마지막 장의 끝 페이지. 
if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;"; 
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
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>"; 
} 
echo "총목록갯수: $end_page 개"; 


}

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->