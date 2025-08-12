<?php
// 스킨 이미지의 적용을 할경우 새로운 스킨을 제작 할경우 코드
// skin/$BBS_ADMIN_skin/ 을 bbs/img 중간에
// bbs/skin/$BBS_ADMIN_skin/img 형식으로 삽입을 해준다.
// 모든 게시판의 기본 경로는 bbs.php 기준이나 그앞에서 $BbsDir 이 기준이 된다 $BbsDir 이 없을경우는 ./ 가 기본이다
// $Homedir  은 최상으로 기준을 하며 메인디렉토리의 db.php 에서 그 권환을 설정할수 있다.
?>


<?php
// 기본 디렉토리 경로 설정
$DbDir = isset($DbDir) ? $DbDir : "..";

// HTTP_REFERER 체크 (PHP 슈퍼글로벌 변수 사용)
if(isset($_SERVER['HTTP_REFERER'])) {
    $Point_TT_mode = "chick";
    include "$BbsDir/PointChick.php";
}
?><head>

<table border=0 align=center width='<?php echo $BBS_ADMIN_td_width?>'  cellpadding='0' cellspacing='0' style='word-break:break-all;'>

<tr><td>


<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr>
<td align=left>
<?php if($BBS_ADMIN_advance=="yes"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/WindowView_advance.php?no=<?php echo $no?>&table=<?php echo $table?>', 'BBsNo<?php echo $no?>','width=700,height=580,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/img/Mlang99.gif' border=0 align=absmiddle></a> 
<?php }?>
<?php if($BBS_ADMIN_write_select=="member"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/BbsSingo.php?no=<?php echo $no?>&table=<?php echo $table?>&page=start&title=<?php echo htmlspecialchars($BbsViewMlang_bbs_title);?>', 'BBsSingo<?php echo $no?>','width=600,height=350,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=auto,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/img/Mlang100.gif' border=0 align=absmiddle></a> 
&nbsp;&nbsp;&nbsp;
<?php }?>
<font style='font:bold; color:<?php echo $BBS_ADMIN_td_color1?>;'><?php echo htmlspecialchars($BbsViewMlang_bbs_title);?></font>
</td>
<td align=right>
<font style='color:<?php echo $BBS_ADMIN_td_color1?>;'>
<?php if($BBS_ADMIN_name_select=="yes"){?>등록인: <?php echo $BbsViewMlang_bbs_member?>&nbsp;<?php }?>
</td>
</tr>
</table>


</td></tr>

<tr><td width=100% height=2 bgcolor='#d3d3d3'></td></tr>

<tr><td>

<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr>
<td align=left>
<font style='color:<?php echo $BBS_ADMIN_td_color1?>;'>
<?php if($BBS_ADMIN_recommendation_select=="yes"){?>추천: <?php echo $BbsViewMlang_bbs_rec?>&nbsp;,&nbsp;<?php }?>

<?php if($BBS_ADMIN_date_select=="yes"){?>일자: <?php echo substr($BbsViewMlang_date,0,10)?>&nbsp;&nbsp;<?php }?>
<?php if($BBS_ADMIN_count_select=="yes"){?>조회: <?php echo $BbsViewMlang_bbs_count?><?php }?>
<?php if($BbsViewMlang_bbs_link){?>&nbsp;,&nbsp;전화번호 : <a href='<?php echo $BbsViewMlang_bbs_link?>' target='_blank'><?php echo $BbsViewMlang_bbs_link?></a><?php }?>
<?php if($BbsViewMlang_bbs_file){?>&nbsp;,&nbsp;첨부파일: <a href='<?php echo $Homedir?>/bbs/upload/<?php echo $table?>/<?php echo $BbsViewMlang_bbs_file?>' target='_blank'><?php echo $BbsViewMlang_bbs_file?></a><?php }?>
</font>
</td>
</tr>
<tr><td width=100% height=1 bgcolor='#d3d3d3'></td></tr>
<tr><td width=100% height=10></td></tr>
</table>

<?php
// 첨부파일이 이미지나 플래쉬 동연상일경우 직접 보여준다..
if($BbsViewMlang_bbs_file){ include "$BbsDir/file_extname.php"; }
?>

<?php
// 문법 오류 설명:
// preg_replace의 첫 번째 인자는 패턴(정규식)이어야 하며, 슬래시(/)로 감싸야 합니다.
// 예: preg_replace("/\r\n\r\n/", "<P>", $CONTENT);
// 또한, 따옴표로 감싼 문자열은 정규식이 아닙니다.
// 그리고 preg_replace("<", ...) 등은 잘못된 사용입니다. "<"는 정규식 패턴이 아니므로 슬래시로 감싸야 하며, 특수문자는 이스케이프해야 합니다.

if($BbsViewMlang_bbs_style=="htmlbr"){
    $CONTENT = $BbsViewMlang_bbs_connent;
    $CONTENT = preg_replace("/\r\n\r\n/", "<P>", $CONTENT);
    $CONTENT = preg_replace("/\r\n/", "<BR>", $CONTENT);
    $connent_text = $CONTENT;
}

if($BbsViewMlang_bbs_style=="br"){
    $CONTENT = $BbsViewMlang_bbs_connent;
    $CONTENT = preg_replace("/</", "&lt;", $CONTENT);
    $CONTENT = preg_replace("/>/", "&gt;", $CONTENT);
    $CONTENT = preg_replace('/"/', "&quot;", $CONTENT);
    $CONTENT = preg_replace("/\|/", "&#124;", $CONTENT);
    $CONTENT = preg_replace("/\r\n\r\n/", "<P>", $CONTENT);
    $CONTENT = preg_replace("/\r\n/", "<BR>", $CONTENT);
    $connent_text = $CONTENT;
}
if($BbsViewMlang_bbs_style=="html"){
    $connent_text = $BbsViewMlang_bbs_connent;
}
if($BbsViewMlang_bbs_style=="text"){
    $connent_text = htmlspecialchars($BbsViewMlang_bbs_connent);
}

echo $connent_text;
?>



</td></tr>

<tr><td bgcolor='<?php echo $BBS_ADMIN_td_color2?>' height=50>

<script>
function Del(no){
	var str;
	if (confirm("삭제를 하시겠습니까?")) {
		str='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?no='+no+'&mode=delete&table=<?php echo $table?>&page=<?php echo $page?>&GH_url=<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>';
		location.href=str;
	}
}
</script>

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
$query = "SELECT * FROM Mlang_{$table}_bbs WHERE Mlang_bbs_no < ? AND Mlang_bbs_reply='0' ORDER BY Mlang_bbs_no DESC LIMIT 1";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $NEXT_1_NO);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
if($row){
?>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr><td width=100% height=1 bgcolor='#d3d3d3'></td></tr>
<tr>
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
&nbsp;<font style='font:bold;color:<?php echo $BBS_ADMIN_td_color1?>;'>이전 ▲</font>&nbsp;&nbsp;
<?php
$NEXT_2title=htmlspecialchars($row['Mlang_bbs_title']);
echo("<a href='$PHP_SELF?mode=view&table=$table&no=" . $row['Mlang_bbs_no'] . "&page=$page&PCode=$PCode'>$NEXT_2title</a>");
?>
&nbsp;
</td>
</tr>
</table>
<?php
}else{}
mysqli_close($db); 
?>
<!--------- 이전글 ---------------------------------->

<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr><td width=100% bgcolor='<?php echo $BBS_ADMIN_td_color2?>' height=2></td></tr>
<tr><td width=100% height=1 bgcolor='#d3d3d3'></td></tr>
<tr><td width=100% bgcolor='<?php echo $BBS_ADMIN_td_color2?>' height=2></td></tr>
</table>

<!--------- 다음글 ---------------------------------->
<?php
include "$DbDir/db.php";
$query = "SELECT * FROM Mlang_{$table}_bbs WHERE Mlang_bbs_no > ? AND Mlang_bbs_reply='0' ORDER BY Mlang_bbs_no ASC LIMIT 1";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $NEXT_1_NO);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row= mysqli_fetch_array($result);
if($row){
?>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0' bgcolor='<?php echo $BBS_ADMIN_td_color1?>'>
<tr>
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
&nbsp;<font style='font:bold;color:<?php echo $BBS_ADMIN_td_color1?>;'>다음 ▼</font>&nbsp;&nbsp;
<?php
$NEXT_1title=htmlspecialchars($row['Mlang_bbs_title']);
echo("<a href='$PHP_SELF?mode=view&table=$table&no=" . $row['Mlang_bbs_no'] . "&page=$page&PCode=$PCode'>$NEXT_1title</a>");
?>
&nbsp;
</td>
</tr>
<tr><td width=100% height=1 bgcolor='#d3d3d3'></td></tr>
</table>
<?php
}else{}
mysqli_close($db); 
?>
<!--------- 다음글 ---------------------------------->

</td></tr>
</table><br>
<!-----------------------------메뉴아이콘------------------------------------>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr><td width=100% align=center>


<a href="javascript:Del('<?php echo $no?>');"><img src='<?php echo $Homedir?>/bbs/skin/<?php echo $BBS_ADMIN_skin?>/img/delete.gif' border=0 align=absmiddle></a>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&tt=modify&no=<?php echo $no?>&page=<?php echo $page?>&offset=<?php echo $offset?>&PCode=<?php echo $PCode?>'><img src='<?php echo $Homedir?>/bbs/skin/<?php echo $BBS_ADMIN_skin?>/img/modify.gif' border=0 align=absmiddle></a>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?page=<?php echo $page?>&mode=write&table=<?php echo $table?>&tt=reply&no=<?php if($BbsViewMlang_bbs_reply=="0"){echo("$no");}else{echo("$BbsViewMlang_bbs_reply");}?>'><img src='<?php echo $Homedir?>/bbs/skin/<?php echo $BBS_ADMIN_skin?>/img/reply.gif' border=0 align=absmiddle></a>


<?php
/////////////////////////// 관리자 모드 호출 START //////////////////
if($HTTP_COOKIE_VARS['id_login_ok']=="admin"){
/////////////////////////// 관리자 모드 호출 END    //////////////////
?>

<a href='#' onClick="javascript:window.open('<?php echo $Homedir?>/bbs/bbs_printer.php?table=<?php echo $table?>&no=<?php echo $no?>', 'bbs_printer','width=780,height=600,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');">
<img src='<?php echo $Homedir?>/bbs/skin/<?php echo $BBS_ADMIN_skin?>/img/printer.gif' border=0 align=absmiddle></a>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&page=<?php echo $page?>&offset=<?php echo $offset?>&PCode=<?php echo $PCode?>'><img src='<?php echo $Homedir?>/bbs/skin/<?php echo $BBS_ADMIN_skin?>/img/write.gif' border=0 align=absmiddle></a>


<?php if($BBS_ADMIN_advance=="yes"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/WindowView_advance.php?no=<?php echo $no?>&table=<?php echo $table?>&offset=<?php echo $offset?>', 'BBsNo<?php echo $no?>','width=700,height=580,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/skin/<?php echo $BBS_ADMIN_skin?>/img/Mlang99.gif' border=0 align=absmiddle></a> 
<?php }?>

<?php } /////////////////////////// 관리자 모드 호출 END    ////////////////// ?>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=list&table=<?php echo $table?>&page=<?php echo $page?>&offset=<?php echo $offset?>&PCode=<?php echo $PCode?>'><img src='<?php echo $Homedir?>/bbs/skin/<?php echo $BBS_ADMIN_skin?>/img/list.gif' border=0 align=absmiddle></a>


<?php if($BBS_ADMIN_write_select=="member"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/BbsSingo.php?no=<?php echo $no?>&table=<?php echo $table?>&page=start&title=<?php echo htmlspecialchars($BbsViewMlang_bbs_title);?>', 'BBsSingo<?php echo $no?>','width=600,height=350,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=auto,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/skin/<?php echo $BBS_ADMIN_skin?>/img/Mlang100.gif' border=0 align=absmiddle></a> 
<?php }?>

</td></tr>
</table>

<!-------------------------------메뉴아이콘 끝----------------------------------------------->


<IFRAME WIDTH="0" HEIGHT="0" FRAMEBORDER="NO" SCROLLING="no" SRC="<?php echo $Homedir?>/bbs/count.php?mode=count&table=<?php echo $table?>&no=<?php echo $no?>" MARGINWIDTH="0" MARGINHEIGHT="0" HSPACE="0" VSPACE="0" border="0"></IFRAME>
?>