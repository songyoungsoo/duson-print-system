<?php
if(!$DbDir){$DbDir="..";}
if($HTTP_REFERER){$Point_TT_mode="chick";include "$BbsDir/PointChick.php";}
?>


<head>
<style>
.write {color:<?php echo $BBS_ADMIN_td_color1?>; font:bold;}
input,select,submit,TEXTAREA {background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>;}

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
<font style='color:<?php echo $BBS_ADMIN_td_color1?>;'>이번 토론의 주제:</font>&nbsp;
<font style='font:bold; color:<?php echo $BBS_ADMIN_td_color1?>;'><?php echo htmlspecialchars($BbsViewMlang_bbs_title);?></font>
</td>
<td align=right>
<?php if($BBS_ADMIN_count_select=="yes"){?>조회: <?php echo $BbsViewMlang_bbs_count?><?php }?>
</td>
</tr>
</table>


</td></tr>


<tr><td>



<?php
if($BbsViewMlang_bbs_style=="br"){
        $CONTENT=$BbsViewMlang_bbs_connent;
		$CONTENT = preg_replace("<", "&lt;", $CONTENT);
		$CONTENT = preg_replace(">", "&gt;", $CONTENT);
		$CONTENT = preg_replace("\"", "&quot;", $CONTENT);
		$CONTENT = preg_replace("\|", "&#124;", $CONTENT);
		$CONTENT = preg_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = preg_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;
}
if($BbsViewMlang_bbs_style=="html"){$connent_text="$BbsViewMlang_bbs_connent";}
if($BbsViewMlang_bbs_style=="text"){$connent_text= htmlspecialchars($BbsViewMlang_bbs_connent);}

echo("$connent_text");

?>

</td></tr>

<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>

<tr><td height=50>

<?php
/////////////////////////// 관리자 모드 호출 START //////////////////
include "$DbDir/db.php";
$AdminChickTYyj= mysqli_query($db, "select * from member where no='1'");
$row_AdminChickTYyj= mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK="$row_AdminChickTYyj['id'];
/////////////////////////// 관리자 모드 호출 END    //////////////////	
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){
?>

<p align='center'>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&page=<?php echo $page?>'><img src='<?php echo $Homedir?>/bbs/img/write.gif' border=0 align=absmiddle></a>

<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=list&table=<?php echo $table?>&page=<?php echo $page?>'><img src='<?php echo $Homedir?>/bbs/img/list.gif' border=0 align=absmiddle></a>

<?php if($BBS_ADMIN_advance=="yes"){?>
<a href='#' onClick="javascript:popup=window.open('<?php echo $BbsDir?>/WindowView_advance.php?no=<?php echo $no?>&table=<?php echo $table?>', 'BBsNo<?php echo $no?>','width=700,height=580,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='<?php echo $Homedir?>/bbs/img/Mlang99.gif' border=0 align=absmiddle></a> 
<?php }?>

</p>
<?php }?>

</td></tr>

<tr><td>
<!--------- 이전글 ---------------------------------->
<?php
$NEXT_1_NO=$BbsViewMlang_bbs_no;

include "$DbDir/db.php";
$result= mysqli_query($db, "select * from Mlang_{$table}_bbs where Mlang_bbs_no < '$NEXT_1_NO' and  Mlang_bbs_reply='0' order by Mlang_bbs_no desc limit 0, 1",$db);
$row= mysqli_fetch_array($result);
if($row){
?>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>
<tr>
<td>
&nbsp;<font style='font:bold;color:<?php echo $BBS_ADMIN_td_color1?>;'>이전 토론 ▲</font>&nbsp;&nbsp;
<?php
$NEXT_2title=htmlspecialchars($row['Mlang_bbs_title']);
echo("<a href='$PHP_SELF?mode=view&table=$table&no=$row['Mlang_bbs_no']&page=$page' class='bbs'>$NEXT_2title</a>");
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
<tr><td width=100% height=5></td></tr>
</table>

<!--------- 다음글 ---------------------------------->
<?php
include "$DbDir/db.php";
$result= mysqli_query($db, "select * from Mlang_{$table}_bbs where Mlang_bbs_no > '$NEXT_1_NO' and  Mlang_bbs_reply='0' order by Mlang_bbs_no asc limit 0, 1",$db);
$row= mysqli_fetch_array($result);
if($row){
?>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1></td></tr>
<tr>
<td>
&nbsp;<font style='font:bold;color:<?php echo $BBS_ADMIN_td_color1?>;'>다음 토론 ▼</font>&nbsp;&nbsp;
<?php
$NEXT_1title=htmlspecialchars($row['Mlang_bbs_title']);
echo("<a href='$PHP_SELF?mode=view&table=$table&no=$row['Mlang_bbs_no']&page=$page' class='bbs'>$NEXT_1title</a>");
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

<?php
include "$BbsDir/Coment.php";
?>

<!-----------------------------  끄읕 ----------------------------------------->
</td></tr>
</table>

</td></tr>
<tr><td ><img src="/bbs/skin/vote/down.jpg" width="600" height="46"></td></tr>
</table>

<IFRAME WIDTH="0" HEIGHT="0" FRAMEBORDER="NO" SCROLLING="no" SRC="<?php echo $Homedir?>/bbs/count.php?mode=count&table=<?php echo $table?>&no=<?php echo $no?>" MARGINWIDTH="0" MARGINHEIGHT="0" HSPACE="0" VSPACE="0" border="0"></IFRAME>
?>