<?
////////////////// 관리자 로그인 ////////////////////
function authenticate()
{
  HEADER("WWW-authenticate: basic realm=\"관리자 인증!\" ");
  HEADER("HTTP/1.0 401 Unauthorized");
  echo("<html><head><script>
       <!--
        function pop()
        { alert('관리자 인증 실패');
             history.go(-1);}
       //--->
        </script>
        </head>
        <body onLoad='pop()'></body>
        </html>
       ");
exit;
}

if(!$PHP_AUTH_USER || !$PHP_AUTH_PW)
{
 authenticate();
}

else
{

include"../../db.php";
$result= mysql_query("select * from $admin_table where no='1'",$db);
$row= mysql_fetch_array($result);

$adminid="$row[id]";
$adminpasswd="$row[pass]";


 if(strcmp($PHP_AUTH_USER,$adminid) || strcmp($PHP_AUTH_PW,$adminpasswd) )
 { authenticate(); }


}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form"){

include"../title.php";
$Bgcolor1="408080";
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
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

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.sex.value == "0") {
alert("성별을 선택하여주세요!!");
f.sex.focus();
return false;
}

if (f.id.value == "") {
alert("ID을 입력하여주세요!!");
f.id.focus();
return false;
}

if (f.year.value == "0") {
alert("나이를 선택하여주세요!!");
f.year.focus();
return false;
}

if (f.map.value == "0") {
alert("지역를 선택하여주세요!!");
f.map.focus();
return false;
}

if (f.job.value == "0") {
alert("직업를 선택하여주세요!!");
f.job.focus();
return false;
}

if (f.school.value == "0") {
alert("학력를 선택하여주세요!!");
f.school.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<b>&nbsp;&nbsp;▒ 가상 회원정보 입력창 ▒▒▒▒▒</b><BR>
<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<form name='FrmUserXInfo' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='form_ok'>
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>성별&nbsp;&nbsp;</td>
<td>
<select name=sex>
<option value=0>선택하세요 ::::::</option>
<option value=1>남성</option>
<option value=2>여성</option>
</select>
</td>
</tr>
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>ID&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="id" size=20 maxLength='20'></td>
</tr>
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>나이&nbsp;&nbsp;</td>
<td>
<select name=year>
<option value=0>선택하세요 ::::::</option>
<?
$i=1900;
while( $i < 2100) 
{ 
$i=$i+1;
echo("<option value=");
echo("$i");
echo(">");
echo("$i");
echo(" 년생");
echo("</option>");
}
?>
</select>
</td>
</tr>
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>지역&nbsp;&nbsp;</td>
<td>
<select name=map>
<option value=0>선택하세요 ::::::</option>
<option value=서울>서울</option>
<option value=인천>인천</option>
<option value=대구>대구</option>
<option value=부산>부산</option>
<option value=대전>대전</option>
<option value=광주>광주</option>
<option value=울산>울산</option>
<option value=충남>충남</option>
<option value=충북>충북</option>
<option value=전남>전남</option>
<option value=전북>전북</option>
<option value=경남>경남</option>
<option value=경북>경북</option>
<option value=제주>제주</option>
<option value=강원>강원</option>
<option value=해외>해외</option>
</select>
</td>
</tr>
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>직업&nbsp;&nbsp;</td>
<td>
<select name=job>
                <option value=0>선택하세요 ::::::</option>
				<option value="기획/사무직">기획/사무직</option>
				<option value="금융/증권">금융/증권</option>
				<option value="인사/총무">인사/총무</option>
				<option value="엔지니어">엔지니어</option>
				<option value="연구원">연구원</option>
				<option value="정보통신">정보통신</option>
				<option value="컴퓨터관련">컴퓨터관련</option>
				<option value="인터넷관련">인터넷관련</option>
				<option value="건설/토목">건설/토목</option>
				<option value="서비스/영업">서비스/영업</option>
				<option value="승무원/항공관련">승무원/항공관련</option>
				<option value="공무원">공무원</option>
				<option value="교직원">교직원</option>
				<option value="교사">교사</option>
				<option value="학원강사">학원강사</option>
				<option value="사업">사업</option>
				<option value="프리랜서">프리랜서</option>
				<option value="예술가">예술가</option>
				<option value="연예인">연예인</option>
				<option value="디자이너">디자이너</option>
				<option value="학생">학생</option>
				<option value="유학생">유학생</option>
				<option value="석/박사과정">석/박사과정</option>
				<option value="의사/한의사">의사/한의사</option>
				<option value="변호사/법조인">변호사/법조인</option>
				<option value="자영업">자영업</option>
				<option value="교수/전임강사">교수/전임강사</option>
				<option value="언론인">언론인</option>
				<option value="회계/세무">회계/세무</option>
				<option value="유치원교사">유치원교사</option>
				<option value="간호사/병원기술직">간호사/병원기술직</option>
				<option value="운동선수">운동선수</option>
				<option value="무직">무직</option>
				<option value="기타">기타</option>
</select>
</td>
</tr>
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>학력&nbsp;&nbsp;</td>
<td>
<select name=school>
<option value=0>선택하세요 ::::::</option>
<option value=1>초등(국민)학교 졸업</option>
<option value=2>초등(국민)학교 중퇴</option>
<option value=3>중학교 졸업</option>
<option value=4>중학교 중퇴</option>
<option value=5>고등학교 졸업</option>
<option value=6>고등학교 중퇴</option>
<option value=5>대학교 졸업</option>
<option value=6>대학교 중퇴</option>
<option value=7>대학원 졸업</option>
<option value=8>대학원 중퇴</option>
<option value=8>대학원 이상</option>
<option value=9>학력 무</option>
</select>
</td>
</tr>
<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' 저장 합니다.'>
</td>
</tr>
</table>

<?
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysql_query("SELECT max(no) FROM member_X");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################

$dbinsert ="insert into member_X values('$new_no',
'$id',   
'$year',
'$map',
'$job',   
'$school',
'$sex'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n\\n자료를 새로 등록하시려면 창을 다시 여세요\\n');
        opener.parent.location=\"index.php\"; 
        window.self.close();
		</script>
	");
		exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

$result = mysql_query("DELETE FROM member_X WHERE no='$no'");
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 가상회원정보 $no번 자료을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>