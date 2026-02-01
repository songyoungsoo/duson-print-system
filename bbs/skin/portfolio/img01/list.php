<?php 
 function str_cutting($str, $len){ 
       preg_match('/([\x00-\x7e]|..)*/', substr($str, 0, $len), $rtn); 
       if ( $len < strlen($str) ) $rtn[0].=".."; 
        return $rtn[0]; 
    } 

$x="$BBS_ADMIN_cutlen";
?> 

<!------------------------------------------- 리스트 시작----------------------------------------->
<?php
if(!$BbsDir){$BbsDir=".";}
if(!$DbDir){$DbDir="..";}
include "$DbDir/db.php";

if($search){ //검색모드일때

if($cate=="title"){$TgCate="Mlang_bbs_title";}
if($cate=="connent"){$TgCate="Mlang_bbs_connent";}
if($cate=="id"){$TgCate="Mlang_bbs_member";}

if($CATEGORY){
$Mlang_query="select * from Mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
}else{
$Mlang_query="select * from Mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0'";
}

}else{ // 일반모드 일때

if($CATEGORY){
$Mlang_query="select * from Mlang_{$table}_bbs where Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
}else{
$Mlang_query="select * from Mlang_{$table}_bbs where Mlang_bbs_reply='0'";
}

}

$query= mysqli_query($db, "$Mlang_query",$db);
$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);
?>


<table border=0 align=center width=<?php echo $BBS_ADMIN_td_width?> cellpadding='3' cellspacing='1' style='word-break:break-all;'>
<tr>
<td align=left>
<td align=left>
<?php if($BBS_ADMIN_cate){?>
<script language="JavaScript">
function BBS_CATE(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
<?php $CateCodeUgt="1";  include "$BbsDir/BBS_CATE.php";}?>
<font style='font-size:9pt;'>(<?php if($search){echo("검색자료수: $total");}else{echo("등록자료수: $total");}?>)</font>
</td>
<td align=right>
<!------------ 검색 --------------------------------------------------->

<head>

<?php
$result= mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1",$db);
$row= mysqli_fetch_array($result);
$BBSAdminloginKK=$row['id'];
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){
?>
<script>
function AdminBdgDel(no){
	var str;
		if (confirm("게시판의 자료를 삭제하려 하십니다.\n\n한번 삭제한 자료는 두번다시 복구되지 않습니다.\n\n삭제하시려면 확인을 누르시기 바랍니다.")) {
		str='/admin/int/delete.php?no='+no+'&bbs=del&table=<?php echo("Mlang_{$table}_bbs");?>';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}

function AdminBdCount(no){
	var str;
		if (confirm("카운터의 자료를 수정 하시겠습니까...?")) {
		str='/admin/int/delete.php?AdminCode21=form&no='+no+'&table=<?php echo("Mlang_{$table}_bbs");?>';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=20,top=120,left=20");
        popup.document.location.href=str;
        popup.focus();
	}
}
</script>
<?php }?>

<script language=javascript>
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

function SearchCheckField()
{
var f=document.MlangSearch;


if (f.title_search.value == "") {
alert("검색할 검색어를 입력하여 주세요..!!");
return false;
}

}

</script>
</head>
<!--
<table border=0 align=right cellpadding='0' cellspacing='10'>
<tr>
<form name='MlangSearch' method='post' OnSubmit='javascript:return SearchCheckField()' action='<?php echo("$PHP_SELF");?>'>
<input type='hidden' name='search' value='yes'>
<input type='hidden' name='table' value='<?php echo $table?>'>
<input type='hidden' name='mode' value='list'>
<td>
<font style='font-size:9pt;'>
<input type='hidden' name='cate' value='title'>
제목
<input type='text' name='search' size='20' style='background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>; font-size:9pt;'>
<input type='submit' value=' 검 색' style='background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>; font-size:9pt;'>
</font>
</td>
</form>
</tr>
</table>
-->
</td>
</tr>
</table>
<!------------ 검색 --------------------------------------------------->


<?php
$listcut = 12;
if (!isset($offset) || !$offset) $offset = 0;

$result = mysqli_query($db, "$Mlang_query order by Mlang_bbs_no desc limit $offset,$listcut");
$rows = mysqli_num_rows($result);
if ($rows) {

    echo "<table border=0 align=center width=96% cellpadding='0' cellspacing='0'><tr>";

    $i = 1 + $offset;

    $says = $listcut / 4;

    while ($row = mysqli_fetch_array($result)) {

        $BbsListTitle_1_ok = str_cutting($row['Mlang_bbs_title'], $x);
        $BbsListTitle_1 = htmlspecialchars($BbsListTitle_1_ok);

        if (!empty($search)) { //검색 키워드값
            $BbsListTitle_1 = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $BbsListTitle_1);
        }

        $BbsListMlang_bbs_style = htmlspecialchars($row['Mlang_bbs_style']);
        $BbsViewMlang_bbs_connent = htmlspecialchars($row['Mlang_bbs_connent']);
        $BbsListMlang_bbs_link = htmlspecialchars($row['Mlang_bbs_link']);
        $BbsListMlang_bbs_file = htmlspecialchars($row['Mlang_bbs_file']);
        ?>

        <td align="center">

        <table border=0 align=center cellpadding='0' cellspacing='0' style="TABLE-LAYOUT: fixed">

        <tr><td width=186 height=257 valign=middle align=center>

        <table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
        <tr><td colspan=3>&nbsp;</td></tr>
        <tr>
        <td>&nbsp;</td>
        <td width=182 height=253 bgcolor="#CCCCCC" cellspacing='0'>
        <a href='<?php echo $PHP_SELF; ?>?mode=view&table=<?php echo $table; ?>&no=<?php echo $row['Mlang_bbs_no']; ?>&page=<?php echo $page; ?>&PCode=<?php echo $PCode; ?>' class='bbs'>
        <?php
        if ($BbsViewMlang_bbs_connent) {
            echo "<img src='/bbs/upload/$table/$BbsViewMlang_bbs_connent' border=0 width=182 height=253>";
        } else if ($BbsListMlang_bbs_link) {
            echo "<img src='$BbsListMlang_bbs_link' border=0 width=182 height=253>";
        } else {
            echo "<p align=center><font style='font-size:20pt; color:#C9C9C9;'>NO</font><BR><font style='font-size:10pt; color:#C9C9C9;'>Image</font></p>";
        }
        ?>
        </a>
        </td>
        <td>&nbsp;</td>
        </tr>
        <tr><td colspan=3>&nbsp;</td></tr>
        </table>

        </td></tr>
        <tr><td><img src='/img/12345.gif' width=1 height=3></td></tr>
        <tr><td background='./img/sajin_back_1.gif' valign=middle align=center>

        <table border=0 width=180 align=left cellpadding='0' cellspacing='0' style="TABLE-LAYOUT: fixed">
        <tr><td>
        <font style='line-height:130%;'>
        <a href='<?php echo $PHP_SELF; ?>?mode=view&table=<?php echo $table; ?>&no=<?php echo $row['Mlang_bbs_no']; ?>&page=<?php echo $page; ?>' class='bbs'><?php echo $BbsListTitle_1; ?></a>
        </td></tr>
        </table>

        </td></tr>
        <?php
        /////////////////////////// 관리자 모드 호출 START //////////////////
        $AdminChickTYyj = mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1");
        $row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
        $BBSAdminloginKK = $row_AdminChickTYyj['id'];
        if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
            echo "<tr><td align=center>";
            $AdminYdddNo = $row['Mlang_bbs_no'];
            include "$BbsDir/AmdinCount.php";
            echo "</td></tr>";
        }
        /////////////////////////// 관리자 모드 호출 END    //////////////////
        ?>
        </table>
        </td>

        <?php
        if ($i % $says == 0) {
            echo "
            <!--------- 라인분류 if -------------->
            </tr>
            <tr><td height=10></td></tr>
            <tr>
            <!--------- 라인분류 if -------------->
            ";
        }

        $i = $i + 1;
    }

    echo "</tr></table>";

} else {
    echo "<table border=0 align=center width=96% cellpadding='0' cellspacing='0'><tr><td><p align=center><BR><BR><b>등록 자료없음</b></p></td></tr></table>";
}
?>

<p align='center'>
<font style='font-size:10pt;'>
<?php
if ($rows) {

    if (!empty($search)) {
        $mlang_pagego = "cate=$cate&search=$search&table=$table&mode=list&page=$page&PCode=$PCode";
    } else {
        $mlang_pagego = "table=$table&mode=list&page=$page&PCode=$PCode";
    }

    $pagecut = $BBS_ADMIN_lnum;
    $one_bbs = $listcut * $pagecut;
    $start_offset = intval($offset / $one_bbs) * $one_bbs;
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
    $start_page = intval($start_offset / $listcut) + 1;
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'><img src='$BbsDir/img/left.gif' border=0 align=absmiddle></a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;

        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }

        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'><img src='$BbsDir/img/right.gif' border=0 align=absmiddle></a>";
    }
    echo "&nbsp;&nbsp;총목록갯수: $end_page 개";
}

mysqli_close($db);
?>
</font>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?mode=write&table=<?php echo $table; ?>&page=<?php echo $page; ?>&PCode=<?php echo $PCode; ?>'><img src='<?php echo $BbsDir; ?>/skin/<?php echo $BBS_ADMIN_skin; ?>/img/write.gif' border=0 align=absmiddle></a>
</p>
<!------------------------------------------- 리스트 끝----------------------------------------->