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

if($code=="modify"){
include"../../db.php";
$result= mysql_query("select * from member_T where no='$no'",$db);
$row= mysql_fetch_array($result);
$Viewname="$row[name]";
$Viewyear="$row[year]";
$Viewmap="$row[map]";
$Viewjob="$row[job]";
$Viewphoto="$row[photo]";
mysql_close($db); 
}
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


if (f.name.value == "") {
alert("이름을 입력하여주세요!!");
f.name.focus();
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

<?if($code=="modify"){}else{?>
if (f.photofile.value == "") {
alert("사진을 입력해 주세요. ");
f.photofile.focus();
return false;
}
if((f.photofile.value.lastIndexOf(".jpg")==-1) && (f.photofile.value.lastIndexOf(".gif")==-1))
{
alert("사진 자료등록은 JPG 와 GIF 파일만 하실수 있습니다.");
f.photofile.focus();
return false
}
<?}?>

}
//////////////// 이미지 미리보기 //////////////////////////////////
/* 소스제작: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><a href=\"#\" onClick=\"javascript:window.close();\"><img src=\"" + image + "\" border=\"0\"></a></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='윈도우 닫기' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<b>&nbsp;&nbsp;▒ 남성 회원정보 <?if($code=="modify"){?>수정<?}else{?>입력<?}?>창 ▒▒▒▒▒</b><BR>
<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?if($code=="modify"){?>modify_ok<?}else{?>form_ok<?}?>'>
<?if($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?=$no?>'><?}?>
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>이름&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="name" size=20 maxLength='20' value='<?if($code=="modify"){echo("$Viewname");}?>'></td>
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
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>사진&nbsp;&nbsp;</td>
<td>
<?if($code=="modify"){?>
<img src='../../IndexSoft/member_T/upload/<?=$Viewphoto?>' width=50><BR>
<INPUT TYPE="hidden" name='TTFileName' value='<?=$Viewphoto?>'>
<INPUT TYPE="checkbox" name='PhotoFileModify'> 사진을 변경하려면 체크해주세요!!<BR>
<?}?>
<INPUT TYPE="file" NAME="photofile" size=30 onChange="Mlamg_image(this.value)"><BR>
사진의 크기는 105 X 130 에 맞추어 주세요
</td>
</tr>
<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' <?if($code=="modify"){?>수정<?}else{?>저장<?}?> 합니다.'>
</td>
</tr>
</table>

<?if($code=="modify"){?>
<script language="JavaScript"> 
var f=document.FrmUserXInfo;
f.year.value="<?=$Viewyear?>"; 
f.map.value="<?=$Viewmap?>"; 
f.job.value="<?=$Viewjob?>"; 
</script>
<?}?>

<?
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysql_query("SELECT max(no) FROM member_T");
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

$upload_dir="../../IndexSoft/member_T/upload";
include"upload.php";

$dbinsert ="insert into member_T values('$new_no',
'$name',   
'$year',
'$map',
'$job',   
'$PhotofileName'
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

$result = mysql_query("DELETE FROM member_T WHERE no='$no'");
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 남성회원정보 $no번 자료을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify_ok"){

if($PhotoFileModify){
$upload_dir="../../IndexSoft/member_T/upload";
include"upload.php";
$YYPjFile="$PhotofileName";
if($TTBigFileName){unlink("$upload_dir/$TTFileName");}
}else{
$YYPjFile="$TTFileName";
}


$query ="UPDATE member_T SET 
name='$name',  
year='$year',
map='$map',
job='$job',
photo='$YYPjFile' 
WHERE no='$no'";
$result= mysql_query($query,$db);


	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;

}
mysql_close($db);


}
?>