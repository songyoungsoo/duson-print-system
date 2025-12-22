<?php
// 변수 초기화 (Notice 에러 방지)
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$page = isset($_GET['page']) ? $_GET['page'] : '';
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$HTTP_REFERER = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// BBS 관련 변수들 초기화
$BBS_ADMIN_td_width = isset($BBS_ADMIN_td_width) ? $BBS_ADMIN_td_width : '100%';
$BBS_ADMIN_td_color1 = isset($BBS_ADMIN_td_color1) ? $BBS_ADMIN_td_color1 : '#000000';
$BBS_ADMIN_td_color2 = isset($BBS_ADMIN_td_color2) ? $BBS_ADMIN_td_color2 : '#FFFFFF';
$BBS_ADMIN_advance = isset($BBS_ADMIN_advance) ? $BBS_ADMIN_advance : '';
$BBS_ADMIN_write_select = isset($BBS_ADMIN_write_select) ? $BBS_ADMIN_write_select : '';
$BBS_ADMIN_name_select = isset($BBS_ADMIN_name_select) ? $BBS_ADMIN_name_select : 'yes';
$BBS_ADMIN_count_select = isset($BBS_ADMIN_count_select) ? $BBS_ADMIN_count_select : 'yes';
$BBS_ADMIN_recommendation_select = isset($BBS_ADMIN_recommendation_select) ? $BBS_ADMIN_recommendation_select : 'yes';
$BBS_ADMIN_view_select = isset($BBS_ADMIN_view_select) ? $BBS_ADMIN_view_select : '';

// 게시글 내용 관련 변수들 초기화
$BbsViewMlang_bbs_title = isset($BbsViewMlang_bbs_title) ? $BbsViewMlang_bbs_title : '';
$BbsViewMlang_bbs_member = isset($BbsViewMlang_bbs_member) ? $BbsViewMlang_bbs_member : '';
$BbsViewMlang_bbs_count = isset($BbsViewMlang_bbs_count) ? $BbsViewMlang_bbs_count : 0;
$BbsViewMlang_bbs_rec = isset($BbsViewMlang_bbs_rec) ? $BbsViewMlang_bbs_rec : 0;
$BbsViewMlang_bbs_link = isset($BbsViewMlang_bbs_link) ? $BbsViewMlang_bbs_link : '';
$BbsViewMlang_bbs_file = isset($BbsViewMlang_bbs_file) ? $BbsViewMlang_bbs_file : '';
$BbsViewMlang_bbs_style = isset($BbsViewMlang_bbs_style) ? $BbsViewMlang_bbs_style : 'br';
$BbsViewMlang_bbs_connent = isset($BbsViewMlang_bbs_connent) ? $BbsViewMlang_bbs_connent : '';
$BbsViewMlang_bbs_secret = isset($BbsViewMlang_bbs_secret) ? $BbsViewMlang_bbs_secret : 'yes';

// 기타 변수들 초기화
$Homedir = isset($Homedir) ? $Homedir : '';
$BbsDir = isset($BbsDir) ? $BbsDir : '.';
$DbDir = isset($DbDir) ? $DbDir : '..';

if(!$DbDir){$DbDir="..";}
// 포트폴리오는 포인트 체크 제외 (무료 조회)
if($HTTP_REFERER && $table !== 'portfolio'){$Point_TT_mode="chick";include "$BbsDir/PointChick.php";}
?>

<head>
<style>
a.bbs{font-family:굴림; font-size: 10pt;; color:#<?php echo $BBS_ADMIN_td_color1?>; text-decoration:none}
a.bbs:link, a.menuLink1:visited{font-family:굴림; font-size: 10pt;; color:#<?php echo $BBS_ADMIN_td_color1?>; text-decoration:none}
a.bbs:hover, a.menuLink1:active{font-family:굴림; font-size: 10pt;; color:#6600CC; text-decoration:underline}
</style>

</head>


<table border=0 align=center width='<?php echo $BBS_ADMIN_td_width?>'  cellpadding='5' cellspacing='0' style='word-break:break-all;'>

<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>

<tr><td>


<table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
<tr>
<td align=left>
<?php if($BBS_ADMIN_advance=="yes"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/WindowView_advance.php?no=<?php echo $no?>&table=<?php echo $table?>', 'BBsNo<?php echo $no?>','width=700,height=580,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/img/Mlang99.gif' border=0 align=absmiddle></a> 
<?php }?>
<?php if($BBS_ADMIN_write_select=="member"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/BbsSingo.php?no=<?php echo $no?>&table=<?php echo $table?>&page=start&title=<?php echo htmlspecialchars($BbsViewMlang_bbs_title);?>', 'BBsSingo<?php echo $no?>','width=600,height=350,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=auto,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/img/Mlang100.gif' border=0 align=absmiddle></a> 
&nbsp;&nbsp;&nbsp;
<?php }?>
<font style='color:<?php echo $BBS_ADMIN_td_color1?>;'>제목:</font>&nbsp;
<font style='font:bold; color:<?php echo $BBS_ADMIN_td_color1?>;'><?php echo htmlspecialchars($BbsViewMlang_bbs_title);?></font>
</td>
<td align=right>
<font style='color:<?php echo $BBS_ADMIN_td_color1?>;'>
<?php if($BBS_ADMIN_name_select=="yes"){?>등록인: <?php echo $BbsViewMlang_bbs_member?>&nbsp;<?php }?>
</td>
</tr>
</table>


</td></tr>

<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>

<tr><td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>

<table border=0 align=center width=100% cellpadding='0' cellspacing='6'>
<tr>
<td align=right>
<font style='color:<?php echo $BBS_ADMIN_td_color1?>;'>
<?php if($BBS_ADMIN_recommendation_select=="yes"){?>추천: <?php echo $BbsViewMlang_bbs_rec?>&nbsp;,&nbsp;<?php }?>
<?php if($BBS_ADMIN_count_select=="yes"){?>조회: <?php echo $BbsViewMlang_bbs_count?><?php }?>
<?php if($BbsViewMlang_bbs_link){?>&nbsp;,&nbsp;관련 링크 : <a href='<?php echo $BbsViewMlang_bbs_link?>' target='_blank'><?php echo $BbsViewMlang_bbs_link?></a><?php }?>
<?php if($BbsViewMlang_bbs_file){?>&nbsp;,&nbsp;첨부파일: <a href='<?php echo $Homedir?>/bbs/upload/<?php echo $table?>/<?php echo $BbsViewMlang_bbs_file?>' target='_blank'><?php echo $BbsViewMlang_bbs_file?></a><?php }?>
</font>
</td>
</tr>
</table>

<?php
// 문법에러 있음: preg_replace의 첫 번째 인자는 패턴이어야 하며, 슬래시(/)로 감싸야 함.
// 또한, preg_replace 대신 str_replace를 사용하는 것이 더 적합한 경우가 있음.
// 아래는 문법에러를 수정한 코드입니다.

if ($BbsViewMlang_bbs_style == "br") {
    $CONTENT = $BbsViewMlang_bbs_connent;
    $CONTENT = str_replace("<", "&lt;", $CONTENT);
    $CONTENT = str_replace(">", "&gt;", $CONTENT);
    $CONTENT = str_replace("\"", "&quot;", $CONTENT);
    $CONTENT = str_replace("|", "&#124;", $CONTENT);
    $CONTENT = str_replace("\r\n\r\n", "<P>", $CONTENT);
    $CONTENT = str_replace("\r\n", "<BR>", $CONTENT);
    $connent_text = $CONTENT;
} elseif ($BbsViewMlang_bbs_style == "html") {
    $connent_text = $BbsViewMlang_bbs_connent;
} elseif ($BbsViewMlang_bbs_style == "text") {
    $connent_text = htmlspecialchars($BbsViewMlang_bbs_connent);
} else {
    $connent_text = $BbsViewMlang_bbs_connent;
}

echo $connent_text;
?>

<?php
// 첨부파일이 이미지나 플래쉬 동연상일경우 직접 보여준다..
if($BbsViewMlang_bbs_file){ include "file_extname.php"; }
?>

</td></tr>

<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>

<tr><td bgcolor='<?php echo $BBS_ADMIN_td_color2?>' height=50>

<script>
function Del(no){
	var str;
	if (confirm("삭제를 하시겠습니까?")) {	Delete.document.location.href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?no='+no+'&mode=delete&table=<?php echo $table?>&page=<?php echo $page?>&GH_url=<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>';
	}
}
</script>

<p align='center'>


<?php if($BBS_ADMIN_count_select=="yes"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $Homedir?>/bbs/count.php?mode=req&table=<?php echo $table?>&no=<?php echo $no?>', 'bbs_count','width=200,height=200,top=2000,left=2000,menubar=yes,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();"><img src='<?php echo $Homedir?>/bbs/img/req.gif' border=0 align=absmiddle></a>
<?php }?>

<a href='#' onClick="javascript:window.open('<?php echo $Homedir?>/bbs/bbs_printer.php?table=<?php echo $table?>&no=<?php echo $no?>', 'bbs_printer','width=780,height=600,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/img/printer.gif' border=0 align=absmiddle></a>

<a href="javascript:Del('<?php echo $no?>');"><img src='<?php echo $Homedir?>/bbs/img/delete.gif' border=0 align=absmiddle></a>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?page=<?php echo $page?>&mode=write&table=<?php echo $table?>&tt=reply&no=<?php if($BbsViewMlang_bbs_reply=="0"){echo("$no");}else{echo("$BbsViewMlang_bbs_reply");}?>'><img src='<?php echo $Homedir?>/bbs/img/reply.gif' border=0 align=absmiddle></a>
<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&tt=modify&no=<?php echo $no?>&page=<?php echo $page?>&offset=<?php echo $offset?>&PCode=<?php echo $PCode?>'><img src='<?php echo $Homedir?>/bbs/img/modify.gif' border=0 align=absmiddle></a>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&page=<?php echo $page?>&offset=<?php echo $offset?>&PCode=<?php echo $PCode?>'><img src='<?php echo $Homedir?>/bbs/img/write.gif' border=0 align=absmiddle></a>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=list&table=<?php echo $table?>&page=<?php echo $page?>&offset=<?php echo $offset?>&PCode=<?php echo $PCode?>'><img src='<?php echo $Homedir?>/bbs/img/list.gif' border=0 align=absmiddle></a>

<?php if($BBS_ADMIN_advance=="yes"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/WindowView_advance.php?no=<?php echo $no?>&table=<?php echo $table?>&offset=<?php echo $offset?>', 'BBsNo<?php echo $no?>','width=700,height=580,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/img/Mlang99.gif' border=0 align=absmiddle></a> 
<?php }?>

<?php if($BBS_ADMIN_write_select=="member"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/BbsSingo.php?no=<?php echo $no?>&table=<?php echo $table?>&page=start&title=<?php echo htmlspecialchars($BbsViewMlang_bbs_title);?>', 'BBsSingo<?php echo $no?>','width=600,height=350,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=auto,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/img/Mlang100.gif' border=0 align=absmiddle></a> 
<?php }?>

</p>

<?php
if($BBS_ADMIN_ComentStyle=="yes"){
include "$BbsDir/Coment.php";
}
?>

</td></tr>

<tr><td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
<!--------- 이전글 ---------------------------------->
<?php
$NEXT_1_NO=$BbsViewMlang_bbs_no;

include "$DbDir/db.php";
$result= mysqli_query($db, "select * from mlang_{$table}_bbs where Mlang_bbs_no < '$NEXT_1_NO' and  Mlang_bbs_reply='0' order by Mlang_bbs_no desc limit 0, 1",$db);
$row= mysqli_fetch_array($result);
if($row){
?>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>
<tr>
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
&nbsp;<font style='font:bold;color:<?php echo $BBS_ADMIN_td_color1?>;'>이전 ▲</font>&nbsp;&nbsp;
<?php
$NEXT_2title = htmlspecialchars($row['Mlang_bbs_title']);
$next_no = $row['Mlang_bbs_no'];
echo "<a href='$PHP_SELF?mode=view&table=$table&no=$next_no&page=$page&PCode=$PCode' class='bbs'>$NEXT_2title</a>";
?>
&nbsp;
</td>
</tr>
<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>
</table>
<?php
}else{}
mysqli_close($db); 
?>
<!--------- 이전글 ---------------------------------->

<table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
<tr><td width=100% bgcolor='<?php echo $BBS_ADMIN_td_color2?>' height=5></td></tr>
</table>

<!--------- 다음글 ---------------------------------->
<?php
include "$DbDir/db.php";
$result= mysqli_query($db, "select * from mlang_{$table}_bbs where Mlang_bbs_no > '$NEXT_1_NO' and  Mlang_bbs_reply='0' order by Mlang_bbs_no asc limit 0, 1",$db);
$row= mysqli_fetch_array($result);
if($row){
?>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0' bgcolor='<?php echo $BBS_ADMIN_td_color1?>'>
<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>
<tr>
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
&nbsp;<font style='font:bold;color:<?php echo $BBS_ADMIN_td_color1?>;'>다음 ▼</font>&nbsp;&nbsp;
<?php
$NEXT_1title = htmlspecialchars($row['Mlang_bbs_title']);
echo "<a href='{$PHP_SELF}?mode=view&table={$table}&no={$row['Mlang_bbs_no']}&page={$page}&PCode={$PCode}' class='bbs'>{$NEXT_1title}</a>";
?>
&nbsp;
</td>
</tr>
<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>
</table>
<?php
}else{}
mysqli_close($db); 
?>
<!--------- 다음글 ---------------------------------->

</td></tr>

</table>

<IFRAME WIDTH="0" HEIGHT="0" FRAMEBORDER="NO" SCROLLING="no" SRC="<?php echo $Homedir?>/bbs/count.php?mode=count&table=<?php echo $table?>&no=<?php echo $no?>" MARGINWIDTH="0" MARGINHEIGHT="0" HSPACE="0" VSPACE="0" border="0"></IFRAME>

<iframe name=Delete frameborder=0 width=0 height=0></iframe>
