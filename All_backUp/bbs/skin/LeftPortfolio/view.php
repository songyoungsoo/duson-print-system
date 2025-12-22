<?php
if(!$DbDir){$DbDir="..";}
?>

<head>
<style>
.write {color:<?php echo $BBS_ADMIN_td_color1?>; font:bold;}
input,select,submit,TEXTAREA {background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>;}
</style>

</head>


<table border=0 align=center width='<?php echo $BBS_ADMIN_td_width?>'  cellpadding='5' cellspacing='0' style='word-break:break-all;'>

<tr><td>


<table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
<tr>
<td align=left>
<font style='color:<?php echo $BBS_ADMIN_td_color1?>;'>제목:</font>&nbsp;
<font style='font:bold; color:<?php echo $BBS_ADMIN_td_color1?>;'> <?php echo htmlspecialchars($BbsViewMlang_bbs_title);?></font>
<?php if($BbsViewMlang_bbs_style){$sum = "$BbsViewMlang_bbs_style"; $sum = number_format($sum); ?>
\<?php echo("$sum"); echo("원"); $sum = str_replace(",","",$sum);}?>
</td>
<td align=right>
<font style='color:<?php echo $BBS_ADMIN_td_color1?>;'>
<?php if($BBS_ADMIN_name_select=="yes"){?>등록인: <?php echo $BbsViewMlang_bbs_member?>&nbsp;<?php }?>
<?php if($BBS_ADMIN_recommendation_select=="yes"){?>추천: <?php echo $BbsViewMlang_bbs_rec?>&nbsp;<?php }?>
<?php if($BBS_ADMIN_count_select=="yes"){?>조회: <?php echo $BbsViewMlang_bbs_count?><?php }?>
</font>
</td>
</tr>
</table>


</td></tr>

<tr><td width=100% height=1 bgcolor='<?php echo $BBS_ADMIN_td_color1?>' background='/img/left_menu_back_134ko.gif' height=1></td></tr>

<tr><td bgcolor='<?php echo $BBS_ADMIN_td_color2?>' align=center>


<?php
	if($BbsViewMlang_bbs_file){
     echo("<a href='/bbs/upload/$table/$BbsViewMlang_bbs_file' target='_blank'><img src='/bbs/upload/$table/$BbsViewMlang_bbs_file' border=0 onload=\"if(this.width>550){this.width=550}\"></a><br>");
	}

    if($BbsViewMlang_bbs_link){
     echo("<a href='$BbsViewMlang_bbs_link' target='_blank'><img src='$BbsViewMlang_bbs_link' border=0 onload=\"if(this.width>550){this.width=550}\"></a><br>");
	}

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
$result= mysqli_query($db, "select * from Mlang_{$table}_bbs where Mlang_bbs_no < '$NEXT_1_NO' and  Mlang_bbs_reply='0' order by Mlang_bbs_no desc limit 0, 1",$db);
$row= mysqli_fetch_array($result);
if($row){
?>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0'>
<tr><td width=100% height=1 bgcolor='#d3d3d3'></td</tr>
<tr>
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
&nbsp;<font style='font:bold;color:<?php echo $BBS_ADMIN_td_color1?>;'>이전 ▲</font>&nbsp;&nbsp;
<?php
$NEXT_2title=htmlspecialchars($row['Mlang_bbs_title']);
echo("<a href='$PHP_SELF?mode=view&table=$table&no=$row['Mlang_bbs_no']&page=$page&PCode=$PCode'>$NEXT_2title</a>");
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
$result= mysqli_query($db, "select * from Mlang_{$table}_bbs where Mlang_bbs_no > '$NEXT_1_NO' and  Mlang_bbs_reply='0' order by Mlang_bbs_no asc limit 0, 1",$db);
$row= mysqli_fetch_array($result);
if($row){
?>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0' bgcolor='<?php echo $BBS_ADMIN_td_color1?>'>
<tr>
<td bgcolor='<?php echo $BBS_ADMIN_td_color2?>'>
&nbsp;<font style='font:bold;color:<?php echo $BBS_ADMIN_td_color1?>;'>다음 ▼</font>&nbsp;&nbsp;
<?php
$NEXT_1title=htmlspecialchars($row['Mlang_bbs_title']);
echo("<a href='$PHP_SELF?mode=view&table=$table&no=$row['Mlang_bbs_no']&page=$page&PCode=$PCode'>$NEXT_1title</a>");
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