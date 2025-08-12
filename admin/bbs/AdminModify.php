<?php
include "../../db.php";
include "../config.php"; // 관리자 로그인

// 문자 인코딩 설정
header('Content-Type: text/html; charset=UTF-8');

$code = isset($_GET['code']) ? $_GET['code'] : '';  // GET 방식으로 전달받는 경우
$no = isset($_GET['no']) ? $_GET['no'] : '';  // GET 방식으로 전달받는 경우

if($code=="start"){

include "../../db.php";
$no = mysqli_real_escape_string($db, $no);
$result= mysqli_query($db, "select * from Mlang_BBS_Admin where no='$no'");
$rows=mysqli_num_rows($result);
if($rows){

while ($row = mysqli_fetch_array($result))  
{ 
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
//  view_select      : 읽기 권한 - member(회원들), guest(아무나), admin(관리자만)
//  td_width            : 게시판의 넓이
//  td_color1          : 제목 등... 상단색
//  td_color2          : 리스트 목록색

$BBS_ADMIN_no=$row['no'];
$BBS_ADMIN_title=$row['title']; 
$BBS_ADMIN_id=$row['id']; 
$BBS_ADMIN_pass=$row['pass']; 
$BBS_ADMIN_skin=$row['skin']; 
$BBS_ADMIN_header=$row['header']; 
$BBS_ADMIN_footer=$row['footer']; 
$BBS_ADMIN_header_include=$row['header_include']; 
$BBS_ADMIN_footer_include=$row['footer_include'];   
$BBS_ADMIN_file_select=$row['file_select']; 
$BBS_ADMIN_link_select=$row['link_select']; 
$BBS_ADMIN_recnum=$row['recnum']; 
$BBS_ADMIN_lnum=$row['lnum']; 
$BBS_ADMIN_cutlen=$row['cutlen']; 
$BBS_ADMIN_New_Article=$row['New_Article']; 
$BBS_ADMIN_date_select=$row['date_select']; 
$BBS_ADMIN_name_select=$row['name_select']; 
$BBS_ADMIN_count_select=$row['count_select']; 
$BBS_ADMIN_recommendation_select=$row['recommendation_select']; 
$BBS_ADMIN_secret_select=$row['secret_select']; 
$BBS_ADMIN_write_select=$row['write_select']; 
$BBS_ADMIN_view_select=$row['view_select']; 
$BBS_ADMIN_td_width=$row['td_width'];
$BBS_ADMIN_td_color1=$row['td_color1']; 
$BBS_ADMIN_td_color2=$row['td_color2']; 
$BBS_ADMIN_MAXFSIZE=$row['MAXFSIZE'];
$BBS_ADMIN_PointBoardView=$row['PointBoardView']; 
$BBS_ADMIN_PointBoard=$row['PointBoard']; 
$BBS_ADMIN_PointComent=$row['PointComent'];
$BBS_ADMIN_ComentStyle=$row['ComentStyle'];
$BBS_ADMIN_cate=$row['cate']; 
$BBS_ADMIN_advance=$row['advance'];
$BBS_ADMIN_NoticeStyle=$row['NoticeStyle']; 
$BBS_ADMIN_NoticeStyleSu=$row['NoticeStyleSu']; 
$BBS_ADMIN_BBS_Level=$row['BBS_Level']; 

}

}else{
		echo "<script>
				window.alert('게시판 테이블에 대한 자료가 없습니다.\\n\\n삭제된 자료일수 있으니 확인 해주세요..!!');
		        opener.parent.location.reload();
                window.self.close();
			</script>";
		exit;
}

mysqli_close($db); 
?>


<?php include "../title.php";?>

<style>
body,td,input,select,submit {color:#FFFFFF; font-size:9pt; FONT-FAMILY:굴림; word-break:break-all;}
input,select,submit {color:#330000; font-size:9pt; FONT-FAMILY:굴림;}
textarea{background-color:#FFFFFF; color:green; font-size:9pt; FONT-FAMILY:굴림;}
</style>

<script src="../js/coolbar.js" type="text/javascript"></script>

<script type="text/javascript">

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

function BBSAdminModifyCheckField()
{
var f=document.BBSAdminModify;

if (f.skin.value == "0") {
alert("변경할 게시판의 SKIN을 선택 하여주세요...?");
return false;
}


if (f.title.value == "") {
alert("변경할 게시판의 타이틀(제목)을 입력하여주세요...?");
return false;
}


if (f.pass.value == "") {
alert("변경할 게시판의 비밀번호를 입력하여주세요...?");
return false;
}
if (!TypeCheck(f.pass.value, ALPHA+NUM)) {
alert("변경할 게시판의 비밀번호은 영문자 및 숫자로만 사용할 수 있습니다.");
return false;
}
if ((f.pass.value.length < 4) || (f.pass.value.length > 20)) {
alert("변경할 게시판의 비밀번호은 4자 이상 20자 이하로 해주셔야 합니다.");
return false;
}

if (f.MAXFSIZE.value == "") {
alert("파일첨부의 용량은 필히 입력해 놓아 주셔야 합니다..");
return false;
}
if (!TypeCheck(f.MAXFSIZE.value, NUM)) {
alert("파일첨부의 용량은 숫자로만 입력하셔야 합니다.");
return false;
}

if (f.recnum.value == "") {
alert("변경할 게시판의 페이지 출력 수 를 입력하여주세요...?");
return false;
}
if (!TypeCheck(f.recnum.value, NUM)) {
alert("변경할 게시판의 페이지 출력 수 는 숫자로만 입력하셔야 합니다.");
return false;
}

if (f.lnum.value == "") {
alert("변경할 게시판의 이동메뉴 수 를 입력하여주세요...?");
return false;
}
if (!TypeCheck(f.lnum.value, NUM)) {
alert("변경할 게시판의 이동메뉴 수 는 숫자로만 입력하셔야 합니다.");
return false;
}

if (f.cutlen.value == "") {
alert("변경할 게시판의 제목 글자 수 를 입력하여주세요...?");
return false;
}
if (!TypeCheck(f.cutlen.value, NUM)) {
alert("변경할 게시판의 제목 글자 수 는 숫자로만 입력하셔야 합니다.");
return false;
}

if (f.New_Article.value == "") {
alert("변경할 게시판의 새글표시 기간 를 입력하여주세요...?");
return false;
}
if (!TypeCheck(f.New_Article.value, NUM)) {
alert("변경할 게시판의 새글표시 기간은 숫자로만 입력하셔야 합니다.");
return false;
}

if (f.td_width.value == "") {
alert("변경할 게시판의 게시판의 넓이 을 입력하여주세요...?");
return false;
}

if (f.td_color1.value == "") {
alert("변경할 게시판의 제목바 색 을 입력하여주세요...?");
return false;
}

if (f.td_color2.value == "") {
alert("변경할 게시판의 리스트 색 을 입력하여주세요...?");
return false;
}

}
//////////// NoticeStyleSu ////////////////////////////////////////////////////

function NoticeStyleChick() {
var f=document.BBSAdminModify;

f.NoticeStyleSu.disabled = !f.NoticeStyle['0'].checked;

if (f.NoticeStyle['1'].checked == false) {

f.NoticeStyleSu.focus ();

}

else {
f.NoticeStyleSu.value="<?php echo isset($BBS_ADMIN_NoticeStyleSu) ? $BBS_ADMIN_NoticeStyleSu : ''; ?>";
} 

}
</script>

</head>

<body bgcolor="#D9CEAE" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table border="0" align="center" width="100%" cellpadding="10" cellspacing="0" class="coolBar">
<tr>
<td width="100%" valign="top" height="600">

<!--------------------------------------------------------------------------------------------->
<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1">

<form name="BBSAdminModify" method="post" onsubmit="return BBSAdminModifyCheckField()" action="<?php echo $_SERVER['PHP_SELF']?>?code=ok">
<INPUT TYPE="hidden" NAME="code" VALUE="ok">
<INPUT TYPE="hidden" NAME="no" VALUE="<?php echo $BBS_ADMIN_no?>">

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">게시판형식&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<?php include "BbsAdminCate.php";?>
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">테이블 명&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<?php echo $BBS_ADMIN_id?>
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">게시판 제목&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="40" maxLength="100" NAME="title" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_title, ENT_QUOTES); ?>">
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">비밀번호&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="20" maxLength="20" NAME="pass" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_pass, ENT_QUOTES); ?>">
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">파일첨부&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="file_select" VALUE="yes" <?php if($BBS_ADMIN_file_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE="radio" NAME="file_select" VALUE="no" <?php if($BBS_ADMIN_file_select=="no"){echo("checked");} ?>>NO
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">파일첨부 용량&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="10" maxLength="10" NAME="MAXFSIZE" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_MAXFSIZE, ENT_QUOTES); ?>"> KB
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">관련링크&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="link_select" VALUE="yes" <?php if($BBS_ADMIN_link_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE="radio" NAME="link_select" VALUE="no" <?php if($BBS_ADMIN_link_select=="no"){echo("checked");} ?>>NO
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">페이지 출력수&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="5" maxLength="3" NAME="recnum" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_recnum, ENT_QUOTES); ?>"> 개
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">이동 메뉴수&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="5" maxLength="3" NAME="lnum" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_lnum, ENT_QUOTES); ?>"> 개
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">제목 글자수&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="5" maxLength="3" NAME="cutlen" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_cutlen, ENT_QUOTES); ?>"> 자 (공백을 포함함)
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">새글표시 기간&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="5" maxLength="1" NAME="New_Article" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_New_Article, ENT_QUOTES); ?>"> 일
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">등록일&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="date_select" VALUE="yes" <?php if($BBS_ADMIN_date_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE="radio" NAME="date_select" VALUE="no" <?php if($BBS_ADMIN_date_select=="no"){echo("checked");} ?>>NO
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">등록인&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="name_select" VALUE="yes" <?php if($BBS_ADMIN_name_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE="radio" NAME="name_select" VALUE="no" <?php if($BBS_ADMIN_name_select=="no"){echo("checked");} ?>>NO
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">조회수&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="count_select" VALUE="yes" <?php if($BBS_ADMIN_count_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE="radio" NAME="count_select" VALUE="no" <?php if($BBS_ADMIN_count_select=="no"){echo("checked");} ?>>NO
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">추천수&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="recommendation_select" VALUE="yes" <?php if($BBS_ADMIN_recommendation_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE="radio" NAME="recommendation_select" VALUE="no" <?php if($BBS_ADMIN_recommendation_select=="no"){echo("checked");} ?>>NO
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">공개/비공개&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="secret_select" VALUE="yes" <?php if($BBS_ADMIN_secret_select=="yes"){echo("checked");} ?>>사용함
<INPUT TYPE="radio" NAME="secret_select" VALUE="no" <?php if($BBS_ADMIN_secret_select=="no"){echo("checked");} ?>>사용 안함
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">쓰기 권한&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="write_select" VALUE="member" <?php if($BBS_ADMIN_write_select=="member"){echo("checked");} ?>>회원들
<INPUT TYPE="radio" NAME="write_select" VALUE="guest" <?php if($BBS_ADMIN_write_select=="guest"){echo("checked");} ?>>아무나
<INPUT TYPE="radio" NAME="write_select" VALUE="admin" <?php if($BBS_ADMIN_write_select=="admin"){echo("checked");} ?>>관리자만
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">읽기 권한&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="radio" NAME="view_select" VALUE="member" <?php if($BBS_ADMIN_view_select=="member"){echo("checked");} ?>>회원들
<INPUT TYPE="radio" NAME="view_select" VALUE="guest" <?php if($BBS_ADMIN_view_select=="guest"){echo("checked");} ?>>아무나
<INPUT TYPE="radio" NAME="view_select" VALUE="admin" <?php if($BBS_ADMIN_view_select=="admin"){echo("checked");} ?>>관리자만
</td>
</tr>


<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">레벨 권한&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<SELECT NAME="BBS_Level">
<option value="2" <?php if($BBS_ADMIN_BBS_Level=="2"){echo("selected");} ?>>2 레벨-부운영자</option>
<option value="3" <?php if($BBS_ADMIN_BBS_Level=="3"){echo("selected");} ?>>3 레벨-골드회원</option>
<option value="4" <?php if($BBS_ADMIN_BBS_Level=="4"){echo("selected");} ?>>4 레벨-정회원</option>
<option value="5" <?php if($BBS_ADMIN_BBS_Level=="5"){echo("selected");} ?>>5 레벨-일반회원</option>
</SELECT>
( 쓰기, 읽기 권한이 회원들로 되어있을경우에만 적용됩니다. )
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">게시판의 넓이&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="10" maxLength="20" NAME="td_width" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_td_width, ENT_QUOTES); ?>">
( 예: 800 또는 100% )
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">제목바 색&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="10" maxLength="20" NAME="td_color1" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_td_color1, ENT_QUOTES); ?>">
( 예: FFCCCC 또는 green )
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">리스트 색&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<INPUT TYPE="text" SIZE="10" maxLength="20" NAME="td_color2" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_td_color2, ENT_QUOTES); ?>">
( 예: FFFFFF 또는 white )
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">게시판 상단&nbsp;<BR>HTML 내용&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<font style="font-size:8pt;">* 아래의 내용은 include 보다 위에 위치합니다....</font><BR>
<textarea cols="64" name="header" rows="8"><?php echo htmlspecialchars($BBS_ADMIN_header, ENT_QUOTES); ?></textarea>
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">include경로&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<font style="font-size:8pt;">* 파일의 경로만 입력하세요..( 예: 도메인명/폴더명/test.php )</font><BR>
<INPUT TYPE="text" SIZE="40" NAME="header_include" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_header_include, ENT_QUOTES); ?>">
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">게시판 하단&nbsp;<BR>HTML 내용&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<font style="font-size:8pt;">* 아래의 내용은 include 보다 위에 위치합니다....</font><BR>
<textarea cols="64" name="footer" rows="8"><?php echo htmlspecialchars($BBS_ADMIN_footer, ENT_QUOTES); ?></textarea>
</td>
</tr>

<tr>
<td width="20%" class="coolBar" align="right">
<font style="color:#000000;">include경로&nbsp;</font>
</td>
<td width="80%" bgcolor="#575757">
<font style="font-size:8pt;">* 파일의 경로만 입력하세요..( 예: 도메인명/폴더명/test.php )</font><BR>
<INPUT TYPE="text" SIZE="40" NAME="footer_include" VALUE="<?php echo htmlspecialchars($BBS_ADMIN_footer_include, ENT_QUOTES); ?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>PointView</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>보드의 자료를 볼때 소비되는 포인트</font><BR>
<INPUT TYPE='TEXT' SIZE=5  NAME='PointView'  VALUE="<?php echo $BBS_ADMIN_PointBoardView?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>PointWrite</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>보드에 자료등록시 적립되는 포인트</font><BR>
<INPUT TYPE='TEXT' SIZE=5  NAME='PointWrite'  VALUE="<?php echo $BBS_ADMIN_PointBoard?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>ComentWrite</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>댓글에 자료등록시 적립되는 포인트</font><BR>
<INPUT TYPE='TEXT' SIZE=5  NAME='ComentWrite'  VALUE="<?php echo $BBS_ADMIN_PointComent?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>Coment출력여부</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='ComentStyle'  VALUE="yes" <?php if($BBS_ADMIN_ComentStyle=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='ComentStyle'  VALUE="no" <?php if($BBS_ADMIN_ComentStyle=="no"){echo("checked");} ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>카테고리</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>내용을 입력하지않으면 카테고리출력안댐(분류는 <b>:</b> 해주세요) - 예)사랑:행복:진실</font><BR>
<INPUT TYPE='TEXT' SIZE=60  NAME='cate'  VALUE="<?php echo $BBS_ADMIN_cate?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>미리보기사용여부</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='advance'  VALUE="yes" <?php if($BBS_ADMIN_advance=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='advance'  VALUE="no" <?php if($BBS_ADMIN_advance=="no"){echo("checked");} ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>상위 공지출력 여부</font>
</td>
<td width=80% bgcolor='#575757'>
<?php if($BBS_ADMIN_NoticeStyle=="yes"){?>
<INPUT TYPE="radio" NAME="NoticeStyle" value='yes' checked onClick='NoticeStyleChick();'>YES
<INPUT TYPE="radio" NAME="NoticeStyle" value='no' onClick='NoticeStyleChick();'>NO
<?php } ?>
<?php if($BBS_ADMIN_NoticeStyle=="no"){?>
<INPUT TYPE="radio" NAME="NoticeStyle" value='yes' onClick='NoticeStyleChick();'>YES
<INPUT TYPE="radio" NAME="NoticeStyle" value='no' checked onClick='NoticeStyleChick();'>NO
<?php } ?>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>상위 공지출력 갯수</font>
</td>
<td width=80% bgcolor='#575757'>
<input type="text" name="NoticeStyleSu" size="5" VALUE="<?php echo $BBS_ADMIN_NoticeStyleSu?>">&nbsp;개
<input type="hidden" name="NoticeStyleSuNO" size="5" VALUE="<?php echo $BBS_ADMIN_NoticeStyleSu?>">
</td>
</tr>

</table>
<!--------------------------------------------------------------------------------------------->

<p align="center">
<input type="submit" value=" 자료를 수정합니다.. ">
<BR><BR>
</p>
</form>

</table>
<!--------------------------------------------------------------------------------------------->

</td>
</tr>
</table>



</body>
</html>


<?php 
}

// 수정 처리
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



if($code=="ok"){

// POST 변수 가져오기
$title = isset($_POST['title']) ? $_POST['title'] : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$skin = isset($_POST['skin']) ? $_POST['skin'] : '';
$header = isset($_POST['header']) ? $_POST['header'] : '';
$footer = isset($_POST['footer']) ? $_POST['footer'] : '';
$header_include = isset($_POST['header_include']) ? $_POST['header_include'] : '';
$footer_include = isset($_POST['footer_include']) ? $_POST['footer_include'] : '';
$file_select = isset($_POST['file_select']) ? $_POST['file_select'] : '';
$link_select = isset($_POST['link_select']) ? $_POST['link_select'] : '';
$recnum = isset($_POST['recnum']) ? $_POST['recnum'] : '';
$lnum = isset($_POST['lnum']) ? $_POST['lnum'] : '';
$cutlen = isset($_POST['cutlen']) ? $_POST['cutlen'] : '';
$New_Article = isset($_POST['New_Article']) ? $_POST['New_Article'] : '';
$date_select = isset($_POST['date_select']) ? $_POST['date_select'] : '';
$name_select = isset($_POST['name_select']) ? $_POST['name_select'] : '';
$count_select = isset($_POST['count_select']) ? $_POST['count_select'] : '';
$recommendation_select = isset($_POST['recommendation_select']) ? $_POST['recommendation_select'] : '';
$secret_select = isset($_POST['secret_select']) ? $_POST['secret_select'] : '';
$write_select = isset($_POST['write_select']) ? $_POST['write_select'] : '';
$view_select = isset($_POST['view_select']) ? $_POST['view_select'] : '';
$td_width = isset($_POST['td_width']) ? $_POST['td_width'] : '';
$td_color1 = isset($_POST['td_color1']) ? $_POST['td_color1'] : '';
$td_color2 = isset($_POST['td_color2']) ? $_POST['td_color2'] : '';
$MAXFSIZE = isset($_POST['MAXFSIZE']) ? $_POST['MAXFSIZE'] : '';
$PointView = isset($_POST['PointView']) ? $_POST['PointView'] : '';
$PointWrite = isset($_POST['PointWrite']) ? $_POST['PointWrite'] : '';
$ComentWrite = isset($_POST['ComentWrite']) ? $_POST['ComentWrite'] : '';
$ComentStyle = isset($_POST['ComentStyle']) ? $_POST['ComentStyle'] : '';
$cate = isset($_POST['cate']) ? $_POST['cate'] : '';
$advance = isset($_POST['advance']) ? $_POST['advance'] : '';
$NoticeStyle = isset($_POST['NoticeStyle']) ? $_POST['NoticeStyle'] : '';
$NoticeStyleSu = isset($_POST['NoticeStyleSu']) ? $_POST['NoticeStyleSu'] : '';
$NoticeStyleSuNO = isset($_POST['NoticeStyleSuNO']) ? $_POST['NoticeStyleSuNO'] : '';
$BBS_Level = isset($_POST['BBS_Level']) ? $_POST['BBS_Level'] : '';
$no = isset($_POST['no']) ? $_POST['no'] : '';

if($NoticeStyle=="yes"){$NoticeStyleSuOk=$NoticeStyleSu;}else{$NoticeStyleSuOk=$NoticeStyleSuNO;}

include "../../db.php";
// SQL 인젝션 방지를 위한 데이터 이스케이프
$title = mysqli_real_escape_string($db, $title);
$pass = mysqli_real_escape_string($db, $pass);
$skin = mysqli_real_escape_string($db, $skin);
$header = mysqli_real_escape_string($db, $header);
$footer = mysqli_real_escape_string($db, $footer);
$header_include = mysqli_real_escape_string($db, $header_include);
$footer_include = mysqli_real_escape_string($db, $footer_include);
$no = mysqli_real_escape_string($db, $no);

$query ="UPDATE Mlang_BBS_Admin SET 
title='$title',
pass='$pass',
skin='$skin',
header='$header',
footer='$footer',
header_include='$header_include',
footer_include='$footer_include',
file_select='$file_select',
link_select='$link_select',
recnum='$recnum',
lnum='$lnum',
cutlen='$cutlen',
New_Article='$New_Article',
date_select='$date_select',
name_select='$name_select',
count_select='$count_select',
recommendation_select='$recommendation_select',
secret_select='$secret_select',
write_select='$write_select',
view_select='$view_select',
td_width='$td_width',
td_color1='$td_color1', 
td_color2='$td_color2',
MAXFSIZE='$MAXFSIZE',
PointBoardView='$PointView',  
PointBoard='$PointWrite', 
PointComent='$ComentWrite', 
ComentStyle='$ComentStyle', 
cate='$cate',
advance='$advance',
NoticeStyle='$NoticeStyle',  
NoticeStyleSu='$NoticeStyleSuOk',
BBS_Level='$BBS_Level'
WHERE no='$no'";
$result= mysqli_query($db, $query);
	if(!$result) {
		echo "<script type=\"text/javascript\">
				window.alert(\"DB 접속 에러입니다!\");
				history.go(-1);
			</script>";
		exit;

} else {
	        //opener.parent.location.reload();
	echo "<script type=\"text/javascript\">
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		</script>
<meta http-equiv='Refresh' content='0; URL=".$_SERVER['PHP_SELF']."?code=start&no=".$no."'>";
		exit;

}

mysqli_close($db);

}
?>