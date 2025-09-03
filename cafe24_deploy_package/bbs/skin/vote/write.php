<?php if($pp=="form"){ 
$Write_Style1="style='background-color:$BBS_ADMIN_td_color2; color:$BBS_ADMIN_td_color1; border-style:solid; border:1 solid #$BBS_ADMIN_td_color1;'";	
?>

<head>
<style>
.write {color:<?php echo $BBS_ADMIN_td_color1?>; font:bold;}
</style>
</head>

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

function board_writeCheckField()
{
var f=document.board_write;

if (f.name.value == "") {
alert("글의 등록인을 입력하여주세요..?");
return false;
}

<?php if($tt=="modify"){ ?>
if (f.pass.value == "") {
alert("글을 수정하시려면 글 작성시의 비밀번호를 입력하셔야 합니다..");
return false;
}
<?php }else{?>
if (f.pass.value == "") {
alert("글을 차후에 수정하시려면 비밀번호를 입력하셔야 합니다..");
return false;
}
<?php }?>

if (f.title.value == "") {
alert("글의 제목을 입력하여주세요..?");
return false;
}

if (f.title.value == "") {
alert("글의 제목을 입력하여주세요..?");
return false;
}

if (f.CONTENT.value.length < 20 ) {
alert("글의 내용을 입력하지 않았거나 너무 짧습니다..");
return false;
}


}

</script>
</head>

<table border=0 align=center width='<?php echo $BBS_ADMIN_td_width?>' cellpadding='5' cellspacing='0' style='word-break:break-all;'>

<form name='board_write' method='post' enctype='multipart/form-data' OnSubmit='javascript:return board_writeCheckField()' action='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>'>
<input type='hidden' name='table' value='<?php echo $table?>'>
<input type='hidden' name='page' value='<?php echo $page?>'>

<?php if($tt=="reply"){ // 답변일경우 //////////////////////////////////////////////////////////////////// ?>
<input type='hidden' name='reply' value='<?php echo $no?>'>
<input type='hidden' name='mode' value='write_ok'>

<?php }else if($tt=="modify"){ // 수정일경우 /////////////////////////////////////////////////////////// ?>
<input type='hidden' name='mode' value='modify_ok'>
<input type='hidden' name='no' value='<?php echo $no?>'>

<?php }else{ // 아무것도 없음 쓰기로 ////////////////////////////////////////////////////////////////// ?>
<input type='hidden' name='reply' value='0'>
<input type='hidden' name='mode' value='write_ok'>

<?php } ?>

<tr>
<td align=right class='write'>등록인&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<input type='text' name='name' size='20' maxLength='20' value='<?php if($tt=="modify"){echo("$BbsViewMlang_bbs_member");}else if($WebtingMemberLogin_id){echo("$WebtingMemberLogin_id");}?>' <?php echo $Write_Style1?>>
</td>	
</tr>

<tr>
<td align=right class='write'>비밀번호&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>' maxLength='20'>
<input type='text' name='pass' size='20' <?php echo $Write_Style1?>> 
<font style='font-size:9pt;'><?php if($tt=="modify"){echo("( 글을 등록할 당시의 비밀번호를 입력해주세요.. )");}else{echo("( 글 수정시 필요해요.. )");}?></font>
</td>	
</tr>

<tr>
<td align=right class='write'>제목&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<input type='text' name='title' style="width:70%;" maxLength='100' value='<?php if($tt=="modify"){echo("$BbsViewMlang_bbs_title");}?>' <?php echo $Write_Style1?>>
</td>	
</tr>

<tr>
<td align=right class='write'>문서형식&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<select name=style <?php echo $Write_Style1?>>
<option value='br' <?php if($BbsViewMlang_bbs_style=="br"){echo("selected");}?>>자동 BR</option>
<option value='text' <?php if($BbsViewMlang_bbs_style=="text"){echo("selected");}?>>TEXT ONLY</option>
<option value='html' <?php if($BbsViewMlang_bbs_style=="html"){echo("selected");}?>>HTML ONLY</option>
</SELECT>
</td>	
</tr>

<?php if($BBS_ADMIN_secret_select=="yes"){ ?>
<tr>
<td align=righ class='write'>공개 여부&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<?php if($tt=="modify"){ ?>

<Input type='radio' name='secret' value='yes' <?php if($BbsViewMlang_bbs_secret=="yes"){echo("checked");}?>><b>공개</b>
<Input type='radio' name='secret' value='no' <?php if($BbsViewMlang_bbs_secret=="no"){echo("checked");}?>><b>비공개</b>
<?php }else{ ?>
<Input type='radio' name='secret' value='yes' checked><b>공개</b>
<Input type='radio' name='secret' value='no'><b>비공개</b>
<?php }?>

<font style='font-size:9pt;'>( 비공개로 체크하시면 관리자만 글을 볼수 있어요.. )</font>
</td>	
</tr>
<?php }?>

<tr>
<td align=right class='write'>내용&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>

<?php if($tt=="reply"){ // 답변일경우 //////////////////////////////////////////////////////////////////// ?>
<TEXTAREA  NAME=CONTENT style="overflow:auto; width:98%; height:300; line-height:130%;" <?php echo $Write_Style1?>>




-------<?php echo $BbsViewMlang_bbs_member?> 님의 (<?php echo $BbsViewMlang_bbs_title?>) - 원본글 ----------------------
<?php echo htmlspecialchars($BbsViewMlang_bbs_connent);?>

-----------------------------------------------------------------
</TEXTAREA>

<?php }else if($tt=="modify"){ // 수정일경우 /////////////////////////////////////////////////////////// ?>
<TEXTAREA  NAME=CONTENT style="overflow:auto; width:98%; height:300; line-height:130%;" <?php echo $Write_Style1?>><?php echo htmlspecialchars($BbsViewMlang_bbs_connent);?></TEXTAREA>

<?php }else{ // 아무것도 없음 쓰기로 ////////////////////////////////////////////////////////////////// ?>
<TEXTAREA  NAME=CONTENT style="overflow:auto; width:98%; height:300; line-height:130%;" <?php echo $Write_Style1?>></TEXTAREA>

<?php } ?>

</td>	
</tr>


<tr>
<td align=right class='write'>이미지 링크&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<input type='text' name='link' style="width:40%;" value='<?php if($tt=="modify"){echo("$BbsViewMlang_bbs_link");}?>' <?php echo $Write_Style1?>> <BR><font style='font-size:9pt;'>( http://를 포함한 주소를 입력하시면 됩니다.. )</font>
</td>	
</tr>


<tr>
<td align=right class='write'>이미지 사진&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<?php if($tt=="modify"){echo("<font style='font-size:9pt;'><input type='checkbox' name='uploadModify' value='yes'>파일을 변경하시려면 체크후 업로드하세요..!!</font>&nbsp;&nbsp;<font style='font-size:9pt; color:$BBS_ADMIN_td_color1;'>저장되어 있는 파일명: $BbsViewMlang_bbs_file</font><BR>");}?>
<input type='file' name='upfile' style="width:70%;" <?php echo $Write_Style1?>>
</td>	
</tr>


<tr><td colspan=2>

<script language="javascript">
function onSpecialChar(SpecialChar)
{
   document.board_write.CONTENT.value=document.board_write.CONTENT.value+SpecialChar;
}
</script>
                   <table WIDTH=90% CELLSPACING=1 CELLPADDING=2 BORDER=0  align=center> 
                      <tr>
                        <TD ALIGN=CENTER WIDTH=90></td><td>
			              <a href="javascript:onSpecialChar('♤');"><font color="gray">♤</font></a> 
                          <a href="javascript:onSpecialChar('♠');"><font color="gray">♠</font></a> 
                          <a href="javascript:onSpecialChar('♡');"><font color="gray">♡</font></a> 
                          <a href="javascript:onSpecialChar('♥');"><font color="gray">♥</font></a> 
                          <a href="javascript:onSpecialChar('♧');"><font color="gray">♧</font></a> 
                          <a href="javascript:onSpecialChar('♣');"><font color="gray">♣</font></a> 
                          <a href="javascript:onSpecialChar('⊙');"><font color="gray">⊙</font></a> 
                          <a href="javascript:onSpecialChar('◈');"><font color="gray">◈</font></a> 
                          <a href="javascript:onSpecialChar('▣');"><font color="gray">▣</font></a> 
                          <a href="javascript:onSpecialChar('◐');"><font color="gray">◐</font></a> 
                          <a href="javascript:onSpecialChar('◑');"><font color="gray">◑</font></a> 
                          <a href="javascript:onSpecialChar('▒');"><font color="gray">▒</font></a> 
                          <a href="javascript:onSpecialChar('▤');"><font color="gray">▤</font></a> 
                          <a href="javascript:onSpecialChar('▥');"><font color="gray">▥</font></a> 
                          <a href="javascript:onSpecialChar('▨');"><font color="gray">▨</font></a> 
                          <a href="javascript:onSpecialChar('▧');"><font color="gray">▧</font></a> 
                          <a href="javascript:onSpecialChar('▦');"><font color="gray">▦</font></a> 
                          <a href="javascript:onSpecialChar('▩');"><font color="gray">▩</font></a> 
                          <a href="javascript:onSpecialChar('♨');"><font color="gray">♨</font></a> 
                          <a href="javascript:onSpecialChar('☏');"><font color="gray">☏</font></a> 
                          <a href="javascript:onSpecialChar('☎');"><font color="gray">☎</font></a> 
                          <a href="javascript:onSpecialChar('☜');"><font color="gray">☜</font></a> 
                          <a href="javascript:onSpecialChar('☞');"><font color="gray">☞</font></a> 
                          <a href="javascript:onSpecialChar('『');"><font color="gray">『</font></a> 
                          <a href="javascript:onSpecialChar('』');"><font color="gray">』</font></a> 
                          <a href="javascript:onSpecialChar('※');"><font color="gray">※</font></a> 
                          <a href="javascript:onSpecialChar('☆');"><font color="gray">☆</font></a> 
                          <a href="javascript:onSpecialChar('★');"><font color="gray">★</font></a> 
                          <a href="javascript:onSpecialChar('○');"><font color="gray">○</font></a> 
                          <a href="javascript:onSpecialChar('●');"><font color="gray">●</font></a> 
                          <a href="javascript:onSpecialChar('◎');"><font color="gray">◎</font></a> 
                          <a href="javascript:onSpecialChar('◇');"><font color="gray">◇</font></a> 
                          <a href="javascript:onSpecialChar('◆');"><font color="gray">◆</font></a> 
                          <a href="javascript:onSpecialChar('□');"><font color="gray">□</font></a> 
                          <a href="javascript:onSpecialChar('■');"><font color="gray">■</font></a> 
                          <a href="javascript:onSpecialChar('△');"><font color="gray">△</font></a> 
                          <a href="javascript:onSpecialChar('▽');"><font color="gray">▽</font></a> 
                          <a href="javascript:onSpecialChar('◁');"><font color="gray">◁</font></a> 
                          <a href="javascript:onSpecialChar('▷');"><font color="gray">▷</font></a> 
                          <a href="javascript:onSpecialChar('▲');"><font color="gray">▲</font></a> 
                          <a href="javascript:onSpecialChar('▼');"><font color="gray">▼</font></a> 
                          <a href="javascript:onSpecialChar('◀');"><font color="gray">◀</font></a> 
                          <a href="javascript:onSpecialChar('▶');"><font color="gray">▶</font></a> 
                          <a href="javascript:onSpecialChar('→');"><font color="gray">→</font></a> 
                          <a href="javascript:onSpecialChar('←');"><font color="gray">←</font></a> 
                          <a href="javascript:onSpecialChar('↑');"><font color="gray">↑</font></a> 
                          <a href="javascript:onSpecialChar('↓');"><font color="gray">↓</font></a> 
                          <a href="javascript:onSpecialChar('↔');"><font color="gray">↔</font></a> 
                          <a href="javascript:onSpecialChar('≒');"><font color="gray">≒</font></a> 
                          <a href="javascript:onSpecialChar('≪');"><font color="gray">≪</font></a> 
                          <a href="javascript:onSpecialChar('≫');"><font color="gray">≫</font></a> 
                          <a href="javascript:onSpecialChar('∵');"><font color="gray">∵</font></a> 
                          <a href="javascript:onSpecialChar('⇒');"><font color="gray">⇒</font></a> 
                          <a href="javascript:onSpecialChar('⇔');"><font color="gray">⇔</font></a> 
                          <a href="javascript:onSpecialChar('¶');"><font color="gray">¶</font></a> 
                          <a href="javascript:onSpecialChar('&trade;');"><font color="gray">&trade;</font></a> 
                          <a href="javascript:onSpecialChar('㏇');"><font color="gray">㏇</font></a> 
                          <a href="javascript:onSpecialChar('↗');"><font color="gray">↗</font></a> 
                          <a href="javascript:onSpecialChar('↙');"><font color="gray">↙</font></a> 
                          <a href="javascript:onSpecialChar('↖');"><font color="gray">↖</font></a> 
                          <a href="javascript:onSpecialChar('↘');"><font color="gray">↘</font></a> 
                          <a href="javascript:onSpecialChar('㉿');"><font color="gray">㉿</font></a> 
                          <a href="javascript:onSpecialChar('㈜');"><font color="gray">㈜</font></a> 
                          <a href="javascript:onSpecialChar('®');"><font color="gray">®</font></a> 
                          <a href="javascript:onSpecialChar('凸');"><font color="gray">凸</font></a> 
                          <a href="javascript:onSpecialChar('^-^');"><font color="gray">^-^</font></a>
			              <a href="javascript:onSpecialChar('o^.^o');"><font color="gray">o^.^o</font></a>
						  <a href="javascript:onSpecialChar('*^o^*');"><font color="gray">*^o^*</font></a>
						  <a href="javascript:onSpecialChar('-_-;');"><font color="gray">-_-;</font></a>
						  <a href="javascript:onSpecialChar('(n. n)');"><font color="gray">(n. n)</font></a>
						  <a href="javascript:onSpecialChar('o(^^o)');"><font color="gray">o(^^o)</font></a>
						  <a href="javascript:onSpecialChar('(⌒ε⌒*)');"><font color="gray">(⌒ε⌒*)</font></a>
						  <a href="javascript:onSpecialChar('づ^0^)づ');"><font color="gray">づ^0^)づ</font></a>
						  <a href="javascript:onSpecialChar('(((**)/ ');"><font color="gray">(((**)/</font></a>
						  <a href="javascript:onSpecialChar('(/*.*)/');"><font color="gray">(/*.*)/</font></a>
						  <a href="javascript:onSpecialChar('(*^.^)♂');"><font color="gray">(*^.^)♂</font></a>
						  <a href="javascript:onSpecialChar('(-_-)a ');"><font color="gray">(-_-)a </font></a>
						  <a href="javascript:onSpecialChar('(*..)(..*)');"><font color="gray">(*..)(..*)</font></a>
						  <a href="javascript:onSpecialChar('(￣∇￣)');"><font color="gray">(￣∇￣)</font></a>
						  <a href="javascript:onSpecialChar('ミ^-^ミ');"><font color="gray">ミ^-^ミ</font></a>
						  <a href="javascript:onSpecialChar('(º◇º)');"><font color="gray">(º◇º)</font></a>
						  <a href="javascript:onSpecialChar('( -,.- )');"><font color="gray">( -,.- )</font></a>
						  <a href="javascript:onSpecialChar('ご,.ご');"><font color="gray">ご,.ご</font></a>
						  <a href="javascript:onSpecialChar('(づ_ど)');"><font color="gray">(づ_ど)</font></a>
						  <a href="javascript:onSpecialChar('(^_-)~♡');"><font color="gray">(^_-)~♡</font></a>
						  <a href="javascript:onSpecialChar('(*`0`*)  ');"><font color="gray">(*`0`*)  </font></a> 
						  
						  </td>
                      </tr> 
                  </table>

</td></tr>

<tr><td colspan=2>
<p align=center>
<?php if($tt=="modify"){ ?>
<input type='submit' value=' 수정 합니다.'>
<?php }else{?>
<input type='submit' value=' 입력 합니다.'>
<?php }?>
<input type='reset' value=' 다시 작성 '>
<input type='button' value=' 목록으로.. ' onClick="javascript:window.location.href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=list&table=<?php echo $table?>&page=<?php echo $page?>';">
</p>
</td></tr>

</form>

</table>


<?php } ?>

<?php if($pp=="modify_ok"){  // 글을 수정 처리한다.. /////////////////////////////////////////////////////////////////////////////////////////
if(!$DbDir){$DbDir="..";}
if(!$BbsDir){$BbsDir=".";}
include "$DbDir/db.php";

$result_pass= mysqli_query($db, "select * from Mlang_{$table}_bbs where Mlang_bbs_no='$no'",$db);
$rows_pass=mysqli_num_rows($result_pass);
if($rows_pass){

while($row_pass= mysqli_fetch_array($result_pass)) 
{ 
         if($row_pass['Mlang_bbs_pass']=="$pass"){}else{
		                echo ("<script language=javascript>
                          window.alert('입력하신 비밀번호가 등록되어 있는 비밀번호의 정보와 틀립니다..');
                          history.go(-1);
                        </script>");
                          exit;
		  }

$Modisy_BBS_OM9="$row_pass['Mlang_bbs_file'];
}

}else{
echo ("<script language=javascript>
window.alert('수정할 자료의 정보가 없습니다.\\n\\n이미 삭제 되었을 수 있습니다..');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list&table=$table&page=$page'>
");
exit;
}

if($uploadModify=="yes"){

unlink("$BbsDir/upload/$table/$Modisy_BBS_OM9");

if ( $upfile ) {include ("$BbsDir/upload.php");}

// 자료를 수정한다..
$query ="UPDATE Mlang_{$table}_bbs SET Mlang_bbs_member='$name', Mlang_bbs_title='$title', Mlang_bbs_style='$style', Mlang_bbs_connent='$CONTENT', Mlang_bbs_link='$link', Mlang_bbs_file='$UPFILENAME', Mlang_bbs_secret='$secret' WHERE   Mlang_bbs_no='$no'";
}else{
// 자료를 수정한다..
$query ="UPDATE Mlang_{$table}_bbs SET Mlang_bbs_member='$name', Mlang_bbs_title='$title', Mlang_bbs_style='$style', Mlang_bbs_connent='$CONTENT', Mlang_bbs_link='$link', Mlang_bbs_secret='$secret' WHERE Mlang_bbs_no='$no'";
}

$result= mysqli_query($db, $query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n정상적으로 정보를 수정 하였습니다.\\n\\n')
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list&table=$table&page=$page'>
		");
		exit;

}

mysqli_close($db);

} ?>


<?php if($pp=="form_ok"){  // 글을 입력 처리한다.. /////////////////////////////////////////////////////////////////////////////////////////

if(!$DbDir){$DbDir="..";}
if(!$BbsDir){$BbsDir=".";}

include "$DbDir/db.php";


if ( $upfile ) {
include ("$BbsDir/upload.php");
}

	$result = mysqli_query($db, "SELECT max(Mlang_bbs_no) FROM Mlang_{$table}_bbs");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysqli_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################
//정보 입력
$date=date("Y-m-d H:i;s");
$dbinsert ="insert into Mlang_{$table}_bbs values('$new_no',
'$name',
'$title',
'$style',
'$CONTENT',
'$link',
'$UPFILENAME',
'$pass',
'0',
'0',
'$secret',
'$reply',
'$date'
)";
$result_insert= mysqli_query($db, $dbinsert,$db);

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n정상적으로 정보가 저장 되었습니다.\\n\\n')
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list&table=$table&page=$page'>
		");
		exit;



} ?>