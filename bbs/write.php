<?php 
// 변수 초기화 (Notice 에러 방지)
$pp = isset($_GET['pp']) ? $_GET['pp'] : (isset($_POST['pp']) ? $_POST['pp'] : '');
$tt = isset($_GET['tt']) ? $_GET['tt'] : (isset($_POST['tt']) ? $_POST['tt'] : '');
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$uploadModify = isset($_POST['uploadModify']) ? $_POST['uploadModify'] : '';
$NoticeStyle = isset($_POST['NoticeStyle']) ? $_POST['NoticeStyle'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';
$check_num = isset($_POST['check_num']) ? $_POST['check_num'] : '';

// BBS 관련 변수들 초기화
$BBS_ADMIN_td_color1 = isset($BBS_ADMIN_td_color1) ? $BBS_ADMIN_td_color1 : '#000000';
$BBS_ADMIN_td_color2 = isset($BBS_ADMIN_td_color2) ? $BBS_ADMIN_td_color2 : '#FFFFFF';
$BBS_ADMIN_write_select = isset($BBS_ADMIN_write_select) ? $BBS_ADMIN_write_select : '';
$BBS_ADMIN_secret_select = isset($BBS_ADMIN_secret_select) ? $BBS_ADMIN_secret_select : '';
$BBS_ADMIN_link_select = isset($BBS_ADMIN_link_select) ? $BBS_ADMIN_link_select : '';
$BBS_ADMIN_file_select = isset($BBS_ADMIN_file_select) ? $BBS_ADMIN_file_select : '';
$BBS_ADMIN_NoticeStyle = isset($BBS_ADMIN_NoticeStyle) ? $BBS_ADMIN_NoticeStyle : '';
$BBS_ADMIN_pass = isset($BBS_ADMIN_pass) ? $BBS_ADMIN_pass : '';

// 게시글 수정 관련 변수들 초기화
$BbsViewMlang_bbs_member = isset($BbsViewMlang_bbs_member) ? $BbsViewMlang_bbs_member : '';
$BbsViewMlang_bbs_title = isset($BbsViewMlang_bbs_title) ? $BbsViewMlang_bbs_title : '';
$BbsViewMlang_bbs_style = isset($BbsViewMlang_bbs_style) ? $BbsViewMlang_bbs_style : '';
$BbsViewMlang_bbs_secret = isset($BbsViewMlang_bbs_secret) ? $BbsViewMlang_bbs_secret : '';
$BbsViewMlang_bbs_link = isset($BbsViewMlang_bbs_link) ? $BbsViewMlang_bbs_link : '';
$BbsViewMlang_bbs_file = isset($BbsViewMlang_bbs_file) ? $BbsViewMlang_bbs_file : '';
$BbsViewMlang_bbs_connent = isset($BbsViewMlang_bbs_connent) ? $BbsViewMlang_bbs_connent : '';
$BbsViewMlang_bbs_NoticeStyle = isset($BbsViewMlang_bbs_NoticeStyle) ? $BbsViewMlang_bbs_NoticeStyle : '';

// 회원 관련 변수들 초기화
$WebtingMemberLogin_id = isset($WebtingMemberLogin_id) ? $WebtingMemberLogin_id : '';
$BBSAdminloginKK = isset($BBSAdminloginKK) ? $BBSAdminloginKK : '';
$BBSAdminloginKPass = isset($BBSAdminloginKPass) ? $BBSAdminloginKPass : '';

// 기타 변수들 초기화
$GGHtime = isset($GGHtime) ? $GGHtime : time();
$PHP_SELF = $_SERVER['PHP_SELF'];

if($pp=="form"){ 
$Write_Style1="style='background-color:$BBS_ADMIN_td_color2; color:$BBS_ADMIN_td_color1; border-style:solid; border:1 solid #$BBS_ADMIN_td_color1;'";	

  $end=2547;
  $num=rand(0,$end);
?><head>
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
f.name.focus();
return false;
}

    <?php if($BBS_ADMIN_write_select=="member"){?>
    <?php }else{?>

<?php if($tt=="modify"){ ?>
if (f.pass.value == "") {
alert("글을 수정하시려면 글 작성시의 비밀번호를 입력하셔야 합니다..");
f.pass.focus();
return false;
}
<?php }else{?>
if (f.pass.value == "") {
alert("글을 차후에 수정하시려면 비밀번호를 입력하셔야 합니다..");
f.pass.focus();
return false;
}
<?php }?>
	<?php }?>

if (f.title.value == "") {
alert("글의 제목을 입력하여주세요..?");
f.title.focus();
return false;
}

if (f.link.value == "") {
alert("전화번호를 입력하여주세요..?");
f.link.focus();
return false;
}

<?php if($BBS_ADMIN_cate){?>
if (f.TX_cate.value == "0") {
alert("<?php echo $BBS_ADMIN_title?>을 선택하여주세요..?");
f.TX_cate.focus();
return false;
}
<?php }?>

if (f.CONTENT.value.length < 10 ) {
alert("글의 내용을 입력하지 않았거나 너무 짧습니다..");
f.CONTENT.focus();
return false;
}


}

</script>
</head>



<table border=0 align=center width='<?php echo $BBS_ADMIN_td_width?>' cellpadding='5' cellspacing='0' style='word-break:break-all;'>

<form name='board_write' method='post' enctype='multipart/form-data' OnSubmit='javascript:return board_writeCheckField()' action='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>'>
<input type='hidden' name='table' value='<?php echo $table?>'>
<input type='hidden' name='page' value='<?php echo $page?>'>
<input type='hidden' name='offset' value='<?php echo $offset?>'>

<?php
$GGtime=date("H, i, s, d, m, Y"); 
$GGHtime=mktime($GGtime);
?>
<input type='hidden' name='WriteTime' value='<?php echo $GGHtime+20?>'>

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
<?php if($BBS_ADMIN_write_select=="member"){if($WebtingMemberLogin_id){?>
<input type='hidden' name='name' value='<?php echo $WebtingMemberLogin_id?>'><?php echo $WebtingMemberLogin_id?>
<?php } }else{?>
<input type='text' name='name' size='20' maxLength='20' value='<?php if($tt=="modify"){echo("$BbsViewMlang_bbs_member");}?>' <?php echo $Write_Style1?>>
<?php }?>
</td>	
</tr>

<?php if($BBS_ADMIN_write_select=="member"){?>
<?php }else{?>
<tr>
<td align=right class='write'>비밀번호&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>' maxLength='20'>
<input type='text' name='pass' size='20' <?php echo $Write_Style1?>> 
<font style='font-size:9pt;'><?php if($tt=="modify"){echo("( 글을 등록할 당시의 비밀번호를 입력해주세요.. )");}else{echo("( 글 수정시 필요해요.. )");}?></font>
</td>	
</tr>
<?php }?>

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

<?php if($BBS_ADMIN_cate){?>
<tr>
<td align=right class='write'>카테고리&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<?php $CateCodeUgt="2";  include "$BbsDir/BBS_CATE.php";?>
</td>	
</tr>
<?php }?>

<?php if($BBS_ADMIN_secret_select=="yes"){ ?>
<tr>
<td align=right class='write'>공개 여부&nbsp;</td>	
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
<td align=right class='write'><p>주문내용</p>
  </td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>

<?php if($tt=="reply"){ // 답변일경우 //////////////////////////////////////////////////////////////////// ?>
<TEXTAREA  NAME=CONTENT style="overflow:auto; width:98%; height:300; line-height:130%;" <?php echo $Write_Style1?>>




-------<?php echo $BbsViewMlang_bbs_member?> 님의 (<?php echo $BbsViewMlang_bbs_title?>) - 원본글 ----------------------
<?php echo htmlspecialchars($BbsViewMlang_bbs_connent);?>

-----------------------------------------------------------------
</TEXTAREA>

<?php }else if($tt=="modify"){ // 수정일경우 /////////////////////////////////////////////////////////// ?>
<TEXTAREA  NAME=CONTENT style="overflow:auto; width:98%; height:300; line-height:130%;" <?php echo $Write_Style1?>><?php echo htmlspecialchars($BbsViewMlang_bbs_connent);?></TEXTAREA>

<?php }else{ // 아무것도 없음 쓰기로 ////////////////////////////////////////////////////////////////////( http://를 포함한 주소를 입력하시면 됩니다.. )*/ ?>
<TEXTAREA  NAME=CONTENT style="overflow:auto; width:98%; height:300; line-height:130%;" <?php echo $Write_Style1?>></TEXTAREA>

<?php } ?>

</td>	
</tr>

<?php if($BBS_ADMIN_link_select=="yes"){ ?>
<tr>
<td align=right class='write'>전화번호&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<input type='text' name='link' style="width:40%;" value='<?php if($tt=="modify"){echo("$BbsViewMlang_bbs_link");}?>' <?php echo $Write_Style1?>> <BR><font style='font-size:9pt;'> </font>
</td>	
</tr>
<?php }?>


<?php if($BBS_ADMIN_file_select=="yes"){ ?>
<tr>
<td align=right class='write'>첨부파일&nbsp;</td>	
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<?php if($tt=="modify"){echo("<font style='font-size:9pt;'><input type='checkbox' name='uploadModify' value='yes'>파일을 변경하시려면 체크후 업로드하세요..!!</font>&nbsp;&nbsp;<font style='font-size:9pt; color:$BBS_ADMIN_td_color1;'>저장되어 있는 파일명: $BbsViewMlang_bbs_file</font><BR>");}?>
<input type='file' name='upfile' style="width:70%;" <?php echo $Write_Style1?>>
</td>	
</tr>
<?php }?>

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

<?php // 현 기능은 관리자만 사용할수 있게 호출 해준다..
/////////////////////////// 관리자 모드 호출 START //////////////////
include "$DbDir/db.php";
$AdminChickTYyj = mysqli_query($db, "select * from member where no='1'");
$row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK = $row_AdminChickTYyj['id'];
if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
?>
<?php if (!$tt) { if ($BBS_ADMIN_NoticeStyle == "yes") { ?>
<tr>
<td align=right class='write'>공지출력여부&nbsp;</td>
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>

<?php if ($BbsViewMlang_bbs_NoticeStyle == "yes") { ?>
<Input type='radio' name='NoticeStyle' value='yes' checked><b>YES</b>
<Input type='radio' name='NoticeStyle' value='no'><b>NO</b>
<?php } else { ?>
<Input type='radio' name='NoticeStyle' value='yes'><b>YES</b>
<Input type='radio' name='NoticeStyle' value='no' checked><b>NO</b>
<?php } ?>

<font style='font-size:9pt;'>( Yes로 체크를 하시면 게시판 상단의 공지 글로 호출 됩니다.)</font>
</td>
</tr>
<?php } } ?>
<?php mysqli_close($db); } /////////////////////////// 관리자 모드 호출 END    ////////////////// ?>
<tr> 
   <td class='write' align=right>글쓰기 확인&nbsp;</td>
   <td><p style="margin-left:10;"> 
          <input name="check_num" type="text" size="15" >
          <input name="num" value='<?php echo $num ?>' type="hidden" size="15">
<font style='font-size:10pt; color:2865A8'>우측 번호를 입력하세요</font>&nbsp;&nbsp;&nbsp; 
<style>
#cssfont {width:150; height:20; color:#0099CC; font-size:20pt; font:bold; line-height:100%;} 
</style>
<span id=cssfont  onSelectStart="return false" onDragStart="return false" style="Filter: Blur(Add=1, Direction=225, Strength=7)">
<i><?php echo $num ?></i></span>
   </td>
</tr>

<tr><td colspan=2>
<p align=center>
<?php if ($tt == "modify") { ?>
<input type='submit' value=' 수정 합니다.'>
<?php } else { ?>
<input type='submit' value=' 입력 합니다.'>
<?php } ?>
<input type='reset' value=' 다시 작성 '>
<input type='button' value=' 목록으로.. ' onClick="javascript:window.location.href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=list&table=<?php echo $table?>&page=<?php echo $page?>';">
</p>
<P ALIGN=CENTER><font style='color:#585858'>현 게시판은 불법 스팸 게시판 등록프로그램의 불법 게시글을 방지하기 위하여<BR>입력시간이 10초이상이어야 글을 등록하실수 있습니다... 
</font></P>
</td></tr>

</form>

</table>


<?php } ?>

<?php if ($pp == "modify_ok") {  // 글을 수정 처리한다.. /////////////////////////////////////////////////////////////////////////////////////////
if (!$DbDir) { $DbDir = ".."; }
if (!$BbsDir) { $BbsDir = "."; }
include "$DbDir/db.php";

$result_pass = mysqli_query($db, "select * from mlang_{$table}_bbs where Mlang_bbs_no='$no'");
$rows_pass = mysqli_num_rows($result_pass);
if ($rows_pass) {

while ($row_pass = mysqli_fetch_array($result_pass)) { 

//------------------- 비번 제어 시작 ------------------------- 

/////////////////////////// 관리자 모드 호출 START //////////////////
$AdminChickTYyj = mysqli_query($db, "select * from member where no='1'");
$row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK = $row_AdminChickTYyj['id'];
/////////////////////////// 관리자 모드 호출 END    //////////////////

if ($BBS_ADMIN_write_select == "member") {
    if ($row_pass['Mlang_bbs_member'] == "$WebtingMemberLogin_id") {
        // 등록자 본인
    } else if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
        // 관리자
    } else {
        echo ("<script language=javascript>
            window.alert('$WebtingMemberLogin_id 님께서는 글의 등록자가 아님으로\\n\\n본 글을 [수정]할 권환이 없습니다.');
            history.go(-1);
        </script>");
        exit;
    }
} else {				 
    if ($row_pass['Mlang_bbs_pass'] == "$pass") {  // 보드 입력 비번과 동일 하다 통과
    } else if ($BBSAdminloginKPass == "$pass") {  // 관리자 비번 임으로 통과
    } else if ($BBS_ADMIN_pass == "$pass") {  // 보드 관리자 비번 임으로 통과
    } else {
        echo ("<script language=javascript>
            window.alert('입력하신 비밀번호가 등록되어 있는 비밀번호의 정보와 틀립니다..');
            history.go(-1);
        </script>");
        exit;
    }	 
}
//------------------- 비번 제어 끄읕 -------------------------

$Modisy_BBS_OM9 = $row_pass['Mlang_bbs_file'];
}

} else {
    echo ("<script language=javascript>
window.alert('수정할 자료의 정보가 없습니다.\\n\\n이미 삭제 되었을 수 있습니다..');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list&table=$table&page=$page&offset=$offset'>
");
    exit;
}

if ($uploadModify == "yes") {

    unlink("$BbsDir/upload/$table/$Modisy_BBS_OM9");

    if ($upfile) { include ("$BbsDir/upload.php"); }

    // 자료를 수정한다..
    $query = "UPDATE mlang_{$table}_bbs SET Mlang_bbs_member='$name', Mlang_bbs_title='$title', Mlang_bbs_style='$style', Mlang_bbs_connent='$CONTENT', Mlang_bbs_link='$link', Mlang_bbs_file='$UPFILENAME', Mlang_bbs_secret='$secret', CATEGORY='$TX_cate' WHERE   Mlang_bbs_no='$no'";
} else {
    // 자료를 수정한다..
    $query = "UPDATE mlang_{$table}_bbs SET Mlang_bbs_member='$name', Mlang_bbs_title='$title', Mlang_bbs_style='$style', Mlang_bbs_connent='$CONTENT', Mlang_bbs_link='$link', Mlang_bbs_secret='$secret', CATEGORY='$TX_cate' WHERE Mlang_bbs_no='$no'";
}

$result = mysqli_query($db, $query);
if (!$result) {
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
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list&table=$table&page=$page&CATEGORY=$CATEGORY&offset=$offset'>
        ");
    exit;

}

mysqli_close($db);

} ?>


<?php if($pp=="form_ok"){  // 글을 입력 처리한다.. /////////////////////////////////////////////////////////////////////////////////////////


////////////  글쓰기 시간 제어 //////////
//include "$BbsDir/Mtime.php";
////////////////////////////////////////

if($num==$check_num)
{

if(!$DbDir){$DbDir="..";}
if(!$BbsDir){$BbsDir=".";}

include "$DbDir/db.php";


if ( $upfile ) {
include ("$BbsDir/upload.php");
}

	$result = mysqli_query($db, "SELECT max(Mlang_bbs_no) FROM mlang_{$table}_bbs");
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
if($NoticeStyle=="yes"){ $NoticeStyleTOk="yes";  // yes이기땜시 BBS_TOP 에 테이블과 번호를 저장 해준다.
	$BBS_TOPresult = mysqli_query($db, "SELECT max(no) FROM BBS_TOP");
	if (!$BBS_TOPresult) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$BBS_TOProw = mysqli_fetch_row($BBS_TOPresult);

	if($BBS_TOProw[0]) {
	   $BBS_TOPnew_no = $BBS_TOProw[0] + 1;
	} else {
	   $BBS_TOPnew_no = 1;
	}   
$BBS_TOPdbinsert ="insert into BBS_TOP values('$BBS_TOPnew_no',
'$table',
'$new_no'
)";
$BBS_TOPresult_insert= mysqli_query($db, $BBS_TOPdbinsert);
}else{$NoticeStyleTOk="no";}

$date=date("Y-m-d H:i;s");
$dbinsert ="insert into mlang_{$table}_bbs values('$new_no',
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
'$date',
'$TX_cate',
'$NoticeStyleTOk'
)";
$result_insert= mysqli_query($db,$dbinsert);

// 글씨기 완료후 포인트 적립 ㅋㅋ
$Point_TT_mode="BoardPointWrite";
include "$BbsDir/PointChick.php";

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n정상적으로 정보가 저장 되었습니다.\\n\\n')
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list&table=$table&page=$page&CATEGORY=$CATEGORY&offset=$offset'>
		");
		exit;

}else{

echo ("<script language=javascript>
window.alert('글쓰기 확인 번호가 정상적이지 않습니다.\\n\\n정상적으로 입력하여 글을 올려주시기 바랍니다..');
history.go(-1);
</script>
");
exit;

}

} ?>