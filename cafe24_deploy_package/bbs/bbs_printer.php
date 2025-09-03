<?php
include "view_fild.php";
include "admin_fild.php";
?>


<head>
<style>
.write {color:<?php echo $BBS_ADMIN_td_color1?>; font:bold;}
input,select,submit,TEXTAREA {background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>;}
</style>
</head>


<table border=0 align=center width='<?php echo $BBS_ADMIN_td_width?>' bgcolor='<?php echo $BBS_ADMIN_td_color1?>' cellpadding='5' cellspacing='0' style='word-break:break-all;'>

<tr><td>


<table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
<tr>
<td align=left>
<font style='color:<?php echo $BBS_ADMIN_td_color2?>;'>제목:</font>&nbsp;
<font style='font:bold; color:<?php echo $BBS_ADMIN_td_color2?>;'> <?php echo htmlspecialchars($BbsViewMlang_bbs_title);?></font>
</td>
<td align=right>
<font style='color:<?php echo $BBS_ADMIN_td_color2?>;'>
자료출처: (스크립트네꺼) 
</font>
</td>
</tr>
</table>


</td></tr>

<tr><td bgcolor='<?php echo $BBS_ADMIN_td_color2?>' height=500 valign=top>

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

<tr><td bgcolor='<?php echo $BBS_ADMIN_td_color1?>' height=1></td></tr>

</table>

<p align=center>
Copyright ⓒ 2003 <?php echo $admin_url?>, Mlang Web 관리프로그램3.0 Corp. All rights reserved. 
</p>
?>