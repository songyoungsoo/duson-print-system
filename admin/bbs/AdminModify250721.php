<?php
include "../../db.php";
include "../config.php"; // �?리자 로그??
$code = $_REQUEST['code'] ?? '';
$no = $_REQUEST['no'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'];
if($code=="start"){

include "../../db.php";
$result= mysqli_query($db, "select * from  Mlang_BBS_Admin where no='$no'");
$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) 
{ 
//  no       : 게시??번호
//  title      : 게시???�목
//  id        : 게시??ID
//  pass    : 게시??비�?번호
//  header  : ??html ?�용
//  footer   : ?�래 html ?�용
//  header_include  : ??INCLUDE ?�일
//  footer_include   : ?�래 INCLUDE ?�일    
//  file_select  : ?�일??받을 건�????�택?��?
//  link_select  : 링크????건�????�택?��?
//  recnum : ?�페?��???출력??//  lnum    : ?�이�??�동 메뉴??//  cutlen  :  ?�목�??�수 ?�기
//  New_Article   : ?��??�시 ?��?기간
//  date_select    : ?�록??출력?��?
//  name_select   : ?�름 출력?��?
//  count_select   : 조회??출력?��?
//  recommendation_select   : 추천??출력?��?
//  secret_select   : 공개 비공�?출력?��?
//  write_select     : ?�기 권한 - member(?�원??, guest(?�무??, admin(�?리자�?
//  view_select      : ?�기 권한 - member(?�원??, guest(?�무??, admin(�?리자�?
//  td_width            : 게시?�의 ?�이
//  td_color1          : ?�목 ??.. ?�단??//  td_color2          : 리스??목록??
$BBS_ADMIN_no="$row[no]";
$BBS_ADMIN_title="$row[title]"; 
$BBS_ADMIN_id="$row[id]"; 
$BBS_ADMIN_pass="$row[pass]"; 
$BBS_ADMIN_skin="$row[skin]"; 
$BBS_ADMIN_header="$row[header]"; 
$BBS_ADMIN_footer="$row[footer]"; 
$BBS_ADMIN_header_include="$row[header_include]"; 
$BBS_ADMIN_footer_include="$row[footer_include]";   
$BBS_ADMIN_file_select="$row[file_select]"; 
$BBS_ADMIN_link_select="$row[link_select]"; 
$BBS_ADMIN_recnum="$row[recnum]"; 
$BBS_ADMIN_lnum="$row[lnum]"; 
$BBS_ADMIN_cutlen="$row[cutlen]"; 
$BBS_ADMIN_New_Article="$row[New_Article]"; 
$BBS_ADMIN_date_select="$row[date_select]"; 
$BBS_ADMIN_name_select="$row[name_select]"; 
$BBS_ADMIN_count_select="$row[count_select]"; 
$BBS_ADMIN_recommendation_select="$row[recommendation_select]"; 
$BBS_ADMIN_secret_select="$row[secret_select]"; 
$BBS_ADMIN_write_select="$row[write_select]"; 
$BBS_ADMIN_view_select="$row[view_select]"; 
$BBS_ADMIN_td_width="$row[td_width]";
$BBS_ADMIN_td_color1="$row[td_color1]"; 
$BBS_ADMIN_td_color2="$row[td_color2]"; 
$BBS_ADMIN_MAXFSIZE="$row[MAXFSIZE]";
$BBS_ADMIN_PointBoardView="$row[PointBoardView]"; 
$BBS_ADMIN_PointBoard="$row[PointBoard]"; 
$BBS_ADMIN_PointComent="$row[PointComent]";
$BBS_ADMIN_ComentStyle="$row[ComentStyle]";
$BBS_ADMIN_cate="$row[cate]"; 
$BBS_ADMIN_advance="$row[advance]";
$BBS_ADMIN_NoticeStyle="$row[NoticeStyle]"; 
$BBS_ADMIN_NoticeStyleSu="$row[NoticeStyleSu]"; 
$BBS_ADMIN_BBS_Level="$row[BBS_Level]"; 

}

}else{
		echo "
			<script>
				window.alert('게시???�이블에 ?????�료�? ?�습?�다.\\n\\n??��???�료?�수 ?�으???�인 ?�주?�요..!!');
		        opener.parent.location.reload();
                window.self.close();
			</script>";
		exit;
}

mysqli_close($db); 
?>


<?include"../title.php";?>

<style>
body,td,input,select,submit {color:#FFFFFF; font-size:9pt; FONT-FAMILY:굴림; word-break:break-all;}
input,select,submit {color:#330000; font-size:9pt; FONT-FAMILY:굴림;}
textarea{background-color:#FFFFFF; color:green; font-size:9pt; FONT-FAMILY:굴림;}
</style>

<script src="../js/coolbar.js" type="text/javascript"></script>

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

function BBSAdminModifyCheckField()
{
var f=document.BBSAdminModify;

if (f.skin.value == "0") {
alert("�?경할 게시?�의 SKIN???�택 ?�여주세??..?");
return false;
}


if (f.title.value == "") {
alert("�?경할 게시?�의 ???��?(?�목)???�력?�여주세??..?");
return false;
}


if (f.pass.value == "") {
alert("�?경할 게시?�의 비�?번호�??�력?�여주세??..?");
return false;
}
if (!TypeCheck(f.pass.value, ALPHA+NUM)) {
alert("�?경할 게시?�의 비�?번호?? ?�문??�??�자로만 ?�용?????�습?�다.");
return false;
}
if ((f.pass.value.length < 4) || (f.pass.value.length > 20)) {
alert("�?경할 게시?�의 비�?번호?? 4???�상 20???�하�??�주?�야 ?�니??");
return false;
}

if (f.MAXFSIZE.value == "") {
alert("?�일첨�????�량?? ?�히 ?�력???�아 주셔???�니??.");
return false;
}
if (!TypeCheck(f.MAXFSIZE.value, NUM)) {
alert("?�일첨�????�량?? ?�자로만 ?�력?�셔???�니??");
return false;
}

if (f.recnum.value == "") {
alert("�?경할 게시?�의 ?�이�? 출력 ??�??�력?�여주세??..?");
return false;
}
if (!TypeCheck(f.recnum.value, NUM)) {
alert("�?경할 게시?�의 ?�이�? 출력 ?????�자로만 ?�력?�셔???�니??");
return false;
}

if (f.lnum.value == "") {
alert("�?경할 게시?�의 ?�동메뉴 ??�??�력?�여주세??..?");
return false;
}
if (!TypeCheck(f.lnum.value, NUM)) {
alert("�?경할 게시?�의 ?�동메뉴 ?????�자로만 ?�력?�셔???�니??");
return false;
}

if (f.cutlen.value == "") {
alert("�?경할 게시?�의 ?�목 �?????�??�력?�여주세??..?");
return false;
}
if (!TypeCheck(f.cutlen.value, NUM)) {
alert("�?경할 게시?�의 ?�목 �????????�자로만 ?�력?�셔???�니??");
return false;
}

if (f.New_Article.value == "") {
alert("�?경할 게시?�의 ?��??�시 기간 �??�력?�여주세??..?");
return false;
}
if (!TypeCheck(f.New_Article.value, NUM)) {
alert("�?경할 게시?�의 ?��??�시 기간?? ?�자로만 ?�력?�셔???�니??");
return false;
}

if (f.td_width.value == "") {
alert("�?경할 게시?�의 게시?�의 ?�이 ???�력?�여주세??..?");
return false;
}

if (f.td_color1.value == "") {
alert("�?경할 게시?�의 ?�목�??????�력?�여주세??..?");
return false;
}

if (f.td_color2.value == "") {
alert("�?경할 게시?�의 리스???????�력?�여주세??..?");
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
f.NoticeStyleSu.value="<?php echo $BBS_ADMIN_NoticeStyleSu?>";
} 

}
</script>

</head>

<body bgcolor='#D9CEAE' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 align=center width=100% cellpadding=10 cellspacing=0 class='coolBar'>
<tr>
<td  width=100% valign=top height=600>

<!--------------------------------------------------------------------------------------------->
<table border=0 align=center width=100% cellpadding=5 cellspacing=1>

<form name='BBSAdminModify' method='post' OnSubmit='javascript:return BBSAdminModifyCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE='hidden' NAME='code'  VALUE="ok">
<INPUT TYPE='hidden' NAME='no'  VALUE="<?php echo $BBS_ADMIN_no?>">

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>게시?�형??nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<?include"BbsAdminCate.php";?>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�이�?�?nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<?php echo $BBS_ADMIN_id?>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>게시???�목&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=40 maxLength='100' NAME='title'  VALUE="<?php echo $BBS_ADMIN_title?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>비�?번호&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=20 maxLength='20' NAME='pass'  VALUE="<?php echo $BBS_ADMIN_pass?>">
</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�일첨�?&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='file_select'  VALUE="yes" <? if($BBS_ADMIN_file_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='file_select'  VALUE="no" <? if($BBS_ADMIN_file_select=="no"){echo("checked");} ?>>NO
</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�일첨�? ?�량&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=10 maxLength='10' NAME='MAXFSIZE'  VALUE="<?php echo $BBS_ADMIN_MAXFSIZE?>"> KB
</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>�??�링??nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='link_select'  VALUE="yes" <? if($BBS_ADMIN_link_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='link_select'  VALUE="no" <? if($BBS_ADMIN_link_select=="no"){echo("checked");} ?>>NO
</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�이�? 출력??nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=5 maxLength='3' NAME='recnum'  VALUE="<?php echo $BBS_ADMIN_recnum?>"> �?</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�동 메뉴??nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=5 maxLength='3' NAME='lnum'  VALUE="<?php echo $BBS_ADMIN_lnum?>"> �?</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�목 �??�수&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=5 maxLength='3' NAME='cutlen'  VALUE="<?php echo $BBS_ADMIN_cutlen?>"> ??(공백???�함??
</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?��??�시 기간&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=5 maxLength='1' NAME='New_Article'  VALUE="<?php echo $BBS_ADMIN_New_Article?>"> ??</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�록??nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='date_select'  VALUE="yes" <? if($BBS_ADMIN_date_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='date_select'  VALUE="no" <? if($BBS_ADMIN_date_select=="no"){echo("checked");} ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�록??nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='name_select'  VALUE="yes" <? if($BBS_ADMIN_name_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='name_select'  VALUE="no" <? if($BBS_ADMIN_name_select=="no"){echo("checked");} ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>조회??nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='count_select'  VALUE="yes" <? if($BBS_ADMIN_count_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='count_select'  VALUE="no" <? if($BBS_ADMIN_count_select=="no"){echo("checked");} ?>>NO
</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>추천??nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='recommendation_select'  VALUE="yes" <? if($BBS_ADMIN_recommendation_select=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='recommendation_select'  VALUE="no" <? if($BBS_ADMIN_recommendation_select=="no"){echo("checked");} ?>>NO
</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>공개/비공�?nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='secret_select'  VALUE="yes" <? if($BBS_ADMIN_secret_select=="yes"){echo("checked");} ?>>?�용??<INPUT TYPE='radio' NAME='secret_select'  VALUE="no" <? if($BBS_ADMIN_secret_select=="no"){echo("checked");} ?>>?�용 ?�함
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�기 권한&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='write_select'  VALUE="member" <? if($BBS_ADMIN_write_select=="member"){echo("checked");} ?>>?�원??<INPUT TYPE='radio' NAME='write_select'  VALUE="guest" <? if($BBS_ADMIN_write_select=="guest"){echo("checked");} ?>>?�무??<INPUT TYPE='radio' NAME='write_select'  VALUE="admin" <? if($BBS_ADMIN_write_select=="admin"){echo("checked");} ?>>�?리자�?</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�기 권한&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='view_select'  VALUE="member" <? if($BBS_ADMIN_view_select=="member"){echo("checked");} ?>>?�원??<INPUT TYPE='radio' NAME='view_select'  VALUE="guest" <? if($BBS_ADMIN_view_select=="guest"){echo("checked");} ?>>?�무??<INPUT TYPE='radio' NAME='view_select'  VALUE="admin" <? if($BBS_ADMIN_view_select=="admin"){echo("checked");} ?>>�?리자�?</td>
</tr>


<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�벨 권한&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<SELECT NAME="BBS_Level">
<option value='2' <? if($BBS_ADMIN_BBS_Level=="2"){echo("selected");} ?>>2 ?�벨-�??�영??/option>
<option value='3' <? if($BBS_ADMIN_BBS_Level=="3"){echo("selected");} ?>>3 ?�벨-골드?�원</option>
<option value='4' <? if($BBS_ADMIN_BBS_Level=="4"){echo("selected");} ?>>4 ?�벨-?�회??/option>
<option value='5' <? if($BBS_ADMIN_BBS_Level=="5"){echo("selected");} ?>>5 ?�벨-?�반?�원</option>
</SELECT>
( ?�기, ?�기 권한???�원?�로 ?�어?�을경우?�만 ?�용?�니?? )
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>게시?�의 ?�이&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=10 maxLength='20' NAME='td_width'  VALUE="<?php echo $BBS_ADMIN_td_width?>">
( ?? 800 ?�는 100% )
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�목�???nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=10 maxLength='20' NAME='td_color1'  VALUE="<?php echo $BBS_ADMIN_td_color1?>">
( ?? FFCCCC ?�는 green )
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>리스????nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='TEXT' SIZE=10 maxLength='20' NAME='td_color2'  VALUE="<?php echo $BBS_ADMIN_td_color2?>">
( ?? FFFFFF ?�는 white )
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>게시???�단&nbsp;<BR>HTML ?�용&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>* ?�래???�용?? include 보다 ?�에 ?�치?�니??...</font><BR>
<textarea cols=64 name=header rows=8><?php echo $BBS_ADMIN_header?></textarea>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>include경로&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>* ?�일??경로�??�력?�세??.( ?? ?�메?�명/?�더�?test.htm )</font><BR>
<INPUT TYPE='TEXT' SIZE=40 NAME='header_include'  VALUE="<?php echo $BBS_ADMIN_header_include?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>게시???�단&nbsp;<BR>HTML ?�용&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>* ?�래???�용?? include 보다 ?�에 ?�치?�니??...</font><BR>
<textarea cols=64 name=footer rows=8><?php echo $BBS_ADMIN_footer?></textarea>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>include경로&nbsp;</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>* ?�일??경로�??�력?�세??.( ?? ?�메?�명/?�더�?test.htm )</font><BR>
<INPUT TYPE='TEXT' SIZE=40  NAME='footer_include'  VALUE="<?php echo $BBS_ADMIN_footer_include?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>PointView</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>보드???�료�?볼때 ?�비?�는 ?�인??/font><BR>
<INPUT TYPE='TEXT' SIZE=5  NAME='PointView'  VALUE="<?php echo $BBS_ADMIN_PointBoardView?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>PointWrite</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>보드???�료?�록???�립?�는 ?�인??/font><BR>
<INPUT TYPE='TEXT' SIZE=5  NAME='PointWrite'  VALUE="<?php echo $BBS_ADMIN_PointBoard?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>ComentWrite</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>?��????�료?�록???�립?�는 ?�인??/font><BR>
<INPUT TYPE='TEXT' SIZE=5  NAME='ComentWrite'  VALUE="<?php echo $BBS_ADMIN_PointComent?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>Coment출력?��?</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='ComentStyle'  VALUE="yes" <? if($BBS_ADMIN_ComentStyle=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='ComentStyle'  VALUE="no" <? if($BBS_ADMIN_ComentStyle=="no"){echo("checked");} ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>카테고리</font>
</td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>?�용???�력?��??�으�?카테고리출력?�댐(분류??<b>:</b> ?�주?�요) - ???�랑:?�복:진실</font><BR>
<INPUT TYPE='TEXT' SIZE=60  NAME='cate'  VALUE="<?php echo $BBS_ADMIN_cate?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>미리보기?�용?��?</font>
</td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='advance'  VALUE="yes" <? if($BBS_ADMIN_advance=="yes"){echo("checked");} ?>>YES
<INPUT TYPE='radio' NAME='advance'  VALUE="no" <? if($BBS_ADMIN_advance=="no"){echo("checked");} ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�위 공�?출력 ?��?</font>
</td>
<td width=80% bgcolor='#575757'>
<?php if ($BBS_ADMIN_NoticeStyle=="yes"){?>
<INPUT TYPE="radio" NAME="NoticeStyle" value='yes' checked onClick='NoticeStyleChick();'>YES
<INPUT TYPE="radio" NAME="NoticeStyle" value='no' onClick='NoticeStyleChick();'>NO
<?php } ?>
<?php if ($BBS_ADMIN_NoticeStyle=="no"){?>
<INPUT TYPE="radio" NAME="NoticeStyle" value='yes' onClick='NoticeStyleChick();'>YES
<INPUT TYPE="radio" NAME="NoticeStyle" value='no' checked onClick='NoticeStyleChick();'>NO
<?php } ?>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'>
<font style='color:#000000;'>?�위 공�?출력 �?��</font>
</td>
<td width=80% bgcolor='#575757'>
<input type="text" name="NoticeStyleSu" size="5" VALUE="<?php echo $BBS_ADMIN_NoticeStyleSu?>">&nbsp;�?<input type="hidden" name="NoticeStyleSuNO" size="5" VALUE="<?php echo $BBS_ADMIN_NoticeStyleSu?>">
</td>
</tr>

</table>
<!--------------------------------------------------------------------------------------------->

<p align=center>
<input type='submit' value=' ?�료�??�정?�니??. '>
<BR><BR>
</p>

</td>
</tr>
</table>



</body>
</html>


<?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



if($code=="ok"){

if($NoticeStyle=="yes"){$NoticeStyleSuOk="$NoticeStyleSu";}else{$NoticeStyleSuOk="$NoticeStyleSuNO";}

include "../../db.php";
$query ="UPDATE  Mlang_BBS_Admin SET 
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
    echo "
        <script language=javascript>
            window.alert(\"DB ���� �����Դϴ�!\")
            history.go(-1);
        </script>";
    exit;
} else {
    //opener.parent.location.reload();
    echo ("
        <script language=javascript>
        alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?code=start&no=$no'>
    ");
    exit;
}

mysqli_close($db);
}
?>
