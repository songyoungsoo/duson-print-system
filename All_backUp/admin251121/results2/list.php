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

// 2006년 01.27일 서울화약을 제작 함으로써 본 보드도 스킨 기능을 만든다.
// 스킨명: seoulfireworks 원본사진 => 동영상 입력으로 변경
// 한글파일명 으로 입력 받을경우 영문/숫자로 자동변경

// 게시판 관리 필드들. Mlnag_BBS_Admin ////////////////////////////////////////////////////////////////////////////////////
//  no       : 게시판 번호
//  title      : 게시판 제목
//  id        : 게시판 ID
//  pass    : 게시판 비밀번호
//  header  : 윗 html 내용
//  footer   : 아래 html 내용
//  header_include  : 윗 INCLUDE 파일
//  footer_include   : 아래 INCLUDE 파일    
//  file_select  : 파일을 받을 건가의 선택여부
//  link_select  : 링크을 할 건가의 선택여부
//  recnum : 한페이지당 출력수
//  lnum    : 페이지이동 메뉴수
//  cutlen  :  제목글자수 끊기
//  New_Article   : 새글표시 유지기간
//  date_select    : 등록일 출력여부
//  name_select   : 이름 출력여부
//  count_select   : 조회수 출력여부
//  recommendation_select   : 추천수 출력여부
//  secret_select   : 공개 비공개 출력여부
//  write_select     : 쓰기 권한 - member(회원들), guest(아무나), admin(관리자만)
//  date : 게시판을 만든날짜
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<head>
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

function ResultsAdminCheckField()
{
var f=document.ResultsAdmin;

if (f.item.value == "0") {
alert("실적물  SKIN을 입력하여주세요...?");
return false;
}

if (f.title.value == "") {
alert("실적물  프로그램의 타이틀(제목)을 입력하여주세요...?");
return false;
}

if (f.id.value == "") {
alert("실적물  프로그램의 테이블명(영문/숫자)을 입력하여주세요...?");
return false;
}
if (!TypeCheck(f.id.value, ALPHA+NUM)) {
alert("실적물  프로그램의 테이블명은 영문자 및 숫자로만 사용할 수 있습니다.");
return false;
}

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
function Mlnag_Results_Admin_Del(id){
	var str;
	if (confirm("실적물 자료를 삭제 하시겠습니까..?\n\n실적물의 관련자료들(업로드파일,DATA 등..)이 전부 삭제됩니다.\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!")) {
		str='<?=$PHP_SELF?>?id='+id+'&mode=delete';
        location.href=str;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr><td colspan=2>
* 분류를 입력할시는 : 으로 구분 주어 지시기 바랍니다. (예- 사랑:행복:사람:좋은)<BR>
* 자료관리를 누르면 테이블에 해당하는 프로그램의 자료를 입력/수정/삭제 하실수 있습니다..<BR>
* 테이블명을 클릭하시면 프로그램 자료를 바로 보실수 있습니다..<BR>
* 제목과 수정일을 변경후 수정을 누르시면 테이블에 해당하는 프로그램의 정보를 변경하실수 있습니다.
<tr>
<form name='ResultsAdmin' method='post' OnSubmit='javascript:return ResultsAdminCheckField()' action='<?=$PHP_SELF?>'>
<input type='hidden' name='mode' value='submit'>
<td align=left>

<?php $dir_path = "../../results/Skin";
$dir_handle = opendir($dir_path);
echo("<select name='item'><option value='0'>▒ SKIN 선택 ▒</option>");

while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")){
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp'>$tmp</option>");   }
}

echo("</select>");

closedir($dir_handle);
?>


<INPUT TYPE='TEXT' SIZE=18 maxLength='20' NAME='title' VALUE="타이틀(제목)" onBlur="checkField(this);" onFocus="clearField(this);">
<INPUT TYPE='TEXT' SIZE=18 maxLength='12' NAME='id' VALUE="테이블명(영문/숫자)" onBlur="checkField(this);" onFocus="clearField(this);">
분류<INPUT TYPE='TEXT' SIZE=30 maxLength='500' NAME='celect' VALUE="">
<INPUT TYPE=SUBMIT VALUE='생성합니다..'>
</td>
</form>

<form name='BbsAdminSearch' method='post' OnSubmit='javascript:return BbsAdminSearchCheckField()' action='<?=$PHP_SELF?>'>
<input type='hidden' name='mode' value='list'>
<td align=right>
<select name='bbs_cate'>
<option value='title'>타이틀(제목)</title>
<option value='id'>테이블명</title>
<INPUT TYPE='TEXT' SIZE=18 NAME='search' onBlur="checkField(this);" onFocus="clearField(this);">
<INPUT TYPE=SUBMIT VALUE='검색'>
</td>
</form>
</tr>
</table>



<!------------------------------------------- 리스트 시작----------------------------------------->
<?php include"../../db.php";
$table="Mlnag_Results_Admin";

if($search){ //검색모드일때
$Mlang_query="select * from $table where $bbs_cate like '%$search%'";}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}

$query= mysqli_query($db, "$Mlang_query");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut= 15;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

$result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
$rows=mysqli_num_rows($result);
if($rows){

echo("
<table border=0 align=center width=100% cellpadding='5' cellspacing='2' class='coolBar'>
<tr>
<td align=center width=10%>SKIN</td>	
<td align=center width=15%>제목</td>	
<td align=center width=15%>테이블명</td>
<td align=center width=20%>분류</td>
<td align=center width=10%>생성일</td>
<td align=center width=10%>자료수</td>
<td align=center width=20%>관리기능</td>		
</tr>
");

$i=1+$offset;
while($row= mysqli_fetch_array($result)) 
{ 

if ($search) //검색 키워드값
{$row[title] = str_replace($search, "<b><FONT COLOR=blue>$search</FONT></b>", $row[title]);}
if ($search) //검색 키워드값
{$row[id] = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $row[id]);}

echo("
<tr bgcolor='#575757'>
<form method='post' action='$PHP_SELF'>
<input type='hidden' name='no' value='$row[no]'>
<input type='hidden' name='mode' value='admin_modify'>");
?>

<td align=center>
<?php $dir_handle = opendir($dir_path);
$RRT="selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";

echo("<select name='item'>");

while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")){
		if($row[item]=="$tmp"){
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp' $RRT>$tmp</option>");  
			}else{
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp'>$tmp</option>");  
			}		  }
}

echo("</select>");

closedir($dir_handle);
?>
</td>	

<?echo("<td align=center>&nbsp;<input type='text' name='title' value='$row[title]' maxLength='20' size='20'></td>	
<td>&nbsp;<a href='/results/index.php?table=$row[id]' target='_blank'><font color=white>$row[id]</font></a></td>	
<td align=center>&nbsp;<input type='text' name='celect' value='$row[celect]' maxLength='500' size='35'></td>	
<td align=center><font color=white>$row[date]</font></td>");

echo("<td align=center>");


$total_query=mysqli_query($db, "select * from Mlang_$row[id]_Results");
$total_bbs=mysqli_affected_rows($db);

echo("<font color=#CCFFFF>$total_bbs</font></td>");

echo("<td align=center>");

echo("<input type='submit' value='수정' style='width:40; height:22;'>");

echo("<input type='button' onClick=\"javascript:window.location.href='./data.php?mode=list&id=$row[id]';\" value='자료관리' style='width:60; height:22;'>");

echo("<input type='button' onClick=\"javascript:Mlnag_Results_Admin_Del('$row[id]');\" value='삭제' style='width:40; height:22;'>");

echo("<input type='button' onClick=\"javascript:window.open('../bbs/dump.php?TableName=Mlang_$row[id]_results', 'bbs_dump','width=567,height=451,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');\" value='빽업' style='width:40; height:22;'>");

echo("</td></form></tr>");

		$i=$i+1;
} 

echo("</table>");

}else{

if($search){ echo"<p align=center><b>$search 에 대한 게시판 없음</b></p>";
}else{ echo"<p align=center><b>생성된 앨범 프로그램 이 없습니다..</b></p>"; }

}

?>

<p align='center'>

<?php if($rows){

$mlang_pagego="mode=list&bbs_cate=$bbs_cate&search=$search"; // 필드속성들 전달값

$pagecut= 10;  //한 장당 보여줄 페이지수 
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
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>"; 
} 
echo "총목록갯수: $end_page 개"; 


}

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->