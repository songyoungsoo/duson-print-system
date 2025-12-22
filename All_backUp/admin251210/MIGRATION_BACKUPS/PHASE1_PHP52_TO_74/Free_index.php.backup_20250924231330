<?
if($mode=="view"){
include"../../db.php";

$result= mysql_query("select * from WebFreeMember where no='$no'",$db);
$row= mysql_fetch_array($result);

?>

<html>
<head>
<title>MlangWeb관리프로그램(3.0)</title>
<meta http-equiv="Content-type" content="text/html; charset=euc-kr">
<!--------------------------------------------------------------------------------

     프로그램명: MlangWeb관리프로그램 버젼3.0
     프로그램 제작툴-에디터플러스2
     프로그램언어: PHP, javascript, DHTML, html
     제작자: Mlang 

// 3.0 에 추가된  기능 --------------------------------------------------------------//

(1) 게시판 버그 수정
(2) PAGE 관리기능 버그 수정
(3) Photo자료실 관리기능 추가
(4) 거래실적 결과물 관리기능 추가
(5) 회원 입력폼 관리기능 추가

//-------------------------------------------------------------------------------//


* 현 사이트는 MYSQLDB(MySql데이터베이스) 화 작업되어져 있는 홈페이지 입니다.
* 홈페이지의 해킹, 사고등으로 자료가 없어질시 5분안에 복구가 가능합니다.
* 현사이트는 PHP프로그램화 되어져 있음으로 웹초보자가 자료를 수정/삭제 가능합니다.
* 페이지 수정시 의뢰자가 HTML에디터 추가를 원하면 프로그램을 지원합니다.
* 모든 페이지는 웹상에서 관리할수 있습니다.

   프로그램 에러 있을시 : ☏ 011-548-7038, 임태희 (전화안받을시 문자를주셔염*^^*)
----------------------------------------------------------------------------------->
<style>
body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:굴림; word-break:break-all;}
.td1 {color:white; font-size:10pt; FONT-FAMILY:굴림; word-break:break-all;}
</style>

</head>

<body LEFTMARGIN='10' TOPMARGIN='10' MARGINWIDTH='10' MARGINHEIGHT='10'>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#65B1B1'>

<tr>
<td bgcolor='#65B1B1' width=120 class='td1' align='left'>&nbsp;이 름&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?=htmlspecialchars($row[name]);?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' width=120 class='td1' align='left'>&nbsp;주민번호&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?=htmlspecialchars($row[jumin1]);?> - <?=htmlspecialchars($row[jumin2]);?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' width=120 class='td1' align='left'>&nbsp;학 력&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?=htmlspecialchars($row[school]);?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' width=120 class='td1' align='left'>&nbsp;연 락 처&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?=htmlspecialchars($row[headphone1]);?> - <?=htmlspecialchars($row[headphone2]);?> - <?=htmlspecialchars($row[headphone3]);?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' width=120 class='td1' align='left'>&nbsp;email&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?=htmlspecialchars($row[email]);?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' width=120 class='td1' align='left'>&nbsp;url&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?=htmlspecialchars($row[url]);?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' width=120 class='td1' align='left'>&nbsp;내 용&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?=htmlspecialchars($row[cont]);?>
</td>
</tr>

</table>

<p align=center>
<input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE' style='background-color:#FFFFFF; color:#539D26; border-width:1; border-style:solid; height:21px; border:1 solid #539D26;'>
</p>

</body>
</html>

<?
mysql_close($db); 
exit;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
function TDsearchCheckField()
{
var f=document.TDsearch;

if (f.TDsearchValue.value == "") {
alert("검색할 검색어 값을 입력해주세요");
f.TDsearchValue.focus();
return false;
}

}

function MM_jumpMenu(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>

</head>


<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
<font color=red>*</font> 회원정보보기에서 회원정보 수정 가능, 탈퇴한 자료는 두번다시 복구 되지 않으니 삭제는 신중을 기해주셔야 합니다.<BR>
<font color=red>*</font> 회원의 등급을 선택하시면 자동으로 변경 됩니다.<BR>
<font color=red>*</font> <b>BB</b> 는 검색을 할시 그값은 검색에 전달하지 않는다는 것을 나타내며, 무남독녀일경우: 0남 0녀 0째 로 선택하여 주십시요<BR>
<font color=red>*</font> 입력하지 않는값은 검색시 그값에 해당하는 자료가 호출되지 않습니다.<BR>
<font color=red>*</font> 회원의 아이디를 클릭하시면 그회원에게 개인적인 메일을 보내실수 있습니다.
</td>
</tr>
<tr>
<td align=right>

   <table border=0 align=center width=100% cellpadding=2 cellspacing=0>
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?=$PHP_SELF?>'>
	    <td align=left>
		<b>간단 검색 :&nbsp;</b>
		<select name='TDsearch'>
		<option value='id'>회원아이디</option>
		<option value='name'>회원이름</option>
        <input type='text' name='TDsearchValue' size='20'>
        <input type='submit' value=' 검 색 '>
	    </td>
		</form>
<td align=right>
<?$InputStyle="style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";?>
<select onChange="MM_jumpMenu('parent',this,0)">
<option value="0" selected>선택하세요</option>
<option value='<?=$PHP_SELF?>?job=PHP프로그램' <?if($job=="PHP프로그램"){echo("$InputStyle selected");}?>>PHP프로그램</option>
<option value='<?=$PHP_SELF?>?job=홈페이지 기획/디자인' <?if($job=="홈페이지 기획/디자인"){echo("$InputStyle selected");}?>>홈페이지 기획/디자인</option>
<option value='<?=$PHP_SELF?>?job=플래시 제작' <?if($job=="플래시 제작"){echo("$InputStyle selected");}?>>플래시 제작</option>
<option value='<?=$PHP_SELF?>?job=드림위버 HTML 코딩작업' <?if($job=="드림위버 HTML 코딩작업"){echo("$InputStyle selected");}?>>드림위버 HTML 코딩작업</option>
<option value='<?=$PHP_SELF?>?job=포토샵 작업' <?if($job=="포토샵 작업"){echo("$InputStyle selected");}?>>포토샵 작업</option>
<option value='<?=$PHP_SELF?>'>← 전체페이지</option>
</select>
</td>

	 </tr>

  </table>

</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>분야</td>
<td align=center>이름</td>
<td align=center>성별</td>
<td align=center>년생</td>
<td align=center>school</td>
<td align=center>가입날짜</td>
<td align=center>관리기능</td>
</tr>

<?
include"../../db.php";
$table="WebFreeMember";

if($job){ 

$Mlang_query="select * from $table where job like '%$job%'";

}else if($TDsearchValue){ // 회원 간단검색 TDsearch //  TDsearchValue

$Mlang_query="select * from $table where $TDsearch like '%$TDsearchValue%'";

}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 12;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?=$row[no]?></font></td>
<td><font color=white><?= htmlspecialchars($row[job]);?></font></a></td>
<td><font color=white><?= htmlspecialchars($row[name]);?></font></td>
<td align=center><font color=white>
<?
$Jumin2Cart = substr($row[jumin2], 0,1);
if($Jumin2Cart=="1"){echo("남성");}else if($Jumin2Cart=="2"){echo("여성");}else{echo("*^^*");}
?>
</font></td>
<td align=center><font color=white><?= substr($row[jumin1], 0,2);?></font></td>
<td align=center><font color=white><?=$row[school]?></font></td>
<td align=center><font color=white><?= htmlspecialchars($row[date]);?></font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?=$PHP_SELF?>?mode=view&no=<?=$row[no]?>', 'MemberModify','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='회원정보보기'>
</td>
<tr>

<?
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

<?
if($rows){

$mlang_pagego="job=$job&cate=$cate$title_search=$title_search"; // 필드속성들 전달값

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

mysql_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?
include"../down.php";
?>