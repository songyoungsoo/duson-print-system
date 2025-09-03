<?php
// 변수 초기화 (Notice 에러 방지)
$CommentWhi = isset($_GET['CommentWhi']) ? $_GET['CommentWhi'] : (isset($_POST['CommentWhi']) ? $_POST['CommentWhi'] : '');
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$ComGoTUrl = isset($_GET['ComGoTUrl']) ? $_GET['ComGoTUrl'] : (isset($_POST['ComGoTUrl']) ? $_POST['ComGoTUrl'] : '');
$ComBBsNo = isset($_POST['ComBBsNo']) ? $_POST['ComBBsNo'] : '';
$ComId = isset($_POST['ComId']) ? $_POST['ComId'] : '';
$ComCont = isset($_POST['ComCont']) ? $_POST['ComCont'] : '';

if ($CommentWhi == "d") {
    // 로그인 체크 및 회원 ID 추출
    if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok']) {
        $WebtingMemberLogin_id = $_COOKIE['id_login_ok'];
    } else {
        $WebtingMemberLogin_id = "";
    }

    include "../db.php";
    $result = mysqli_query($db, "select * from Mlang_{$table}_bbs_coment where Mlang_coment_member_id='$WebtingMemberLogin_id' and Mlang_coment_no='$no'");
    $row = mysqli_fetch_array($result);
    if ($row) {
        $result = mysqli_query($db, "DELETE FROM Mlang_{$table}_bbs_coment WHERE Mlang_coment_no='$no'");
        mysqli_close($db);
        //완료 메세지를 보인후 페이지를 이동 시킨다
        echo "<script language='javascript'>\n alert('\\n덧글자료을 [삭제] 하였습니다.\\n\\n');\n</script>\n";
    } else {
        echo "<script language='javascript'>\n alert('\\n$WebtingMemberLogin_id 님의 덧글이 아님으로 자료를 삭제할수 없습니다.\\n\\n');\n</script>\n";
    }
    // @를 &로 바꾼 URL로 리다이렉트
    $redirectUrl = preg_replace("/@/", "&", $ComGoTUrl);
    echo "<meta http-equiv='Refresh' content='0; URL=$redirectUrl'>";
    return;
}

if ($CommentWhi == "w") {
    include "../member/login_chick.php";
    include "./admin_fild.php";
    include "../db.php";
    $Comdate = date("Y-m-d H:i:s");
    $dbinsert = "insert into Mlang_{$table}_bbs_coment values('',
    '$ComBBsNo',
    '$ComId',
    '$ComCont',
    '$Comdate'
    )";
    $result_insert = mysqli_query($db, $dbinsert);

    if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok']) {
        $WebtingMemberLogin_id = $_COOKIE['id_login_ok'];
    }
    $db_dir = "..";
    include "../member/member_fild_id.php";

    $DbDir = "..";
    $Point_TT_mode = "ComentWrite";
    include "PointChick.php";

    //완료 메세지를 보인후 페이지를 이동 시킨다
    echo "<script language='javascript'>\n alert('\\n덧글을 올려주시어 대단히 감사합니다.\\n\\n');\n</script>\n";
    $redirectUrl = preg_replace("/@/", "&", $ComGoTUrl);
    echo "<meta http-equiv='Refresh' content='0; URL=$redirectUrl'>";
    exit;
}
?>

<?php if ($BBS_ADMIN_write_select == "member") { ?>
<head>
<script language="javascript">
function CommebtYCheckField()
{
    var f = document.CommebtYInfo;
    if (f.ComCont.value.length < 3 ) {
        alert("덧글의 내용을 입력하지 않았거나 너무 짧습니다..");
        f.ComCont.focus();
        return false;
    }
    if (f.ComCont.value.length > 1000 ) {
        alert("덧글은 1000 자 이상 올릴수 없습니다.");
        f.ComCont.focus();
        return false;
    }
}
</script>
</head>

<table border=0 align=center width=96% cellpadding=0 cellspacing=0>
<tr><td bgcolor='#FFFFFF' colspan=2><img src='<?php echo $imgdir?>/12345.gif' width=1 height=10></td></tr>
<form name='CommebtYInfo' method='post' OnSubmit='javascript:return CommebtYCheckField()' action='<?php echo $Homedir?>/bbs/Coment.php'>
<INPUT TYPE="hidden" name='CommentWhi' value='w'>
<INPUT TYPE="hidden" name='ComBBsNo' value='<?php echo $BbsViewMlang_bbs_no ?>'>
<INPUT TYPE="hidden" name='ComId' value='<?php echo $WebtingMemberLogin_id ?>'>
<INPUT TYPE="hidden" name='table' value='<?php echo $table ?>'>
<INPUT TYPE="hidden" name='ComGoTUrl' value='<?php echo preg_replace("/&/", "@", $_SERVER["REQUEST_URI"]); ?>'>
<tr>
<td bgcolor='<?php echo $BBS_ADMIN_td_color1 ?>' width=40% height=20 style="filter:'progid:DXImageTransform.Microsoft.Gradient(GradientType=0, StartColorStr=#<?php echo $BBS_ADMIN_td_color2 ?>, EndColorStr=#<?php echo $BBS_ADMIN_td_color1 ?>)';" valign=bottom>
&nbsp;&nbsp;&nbsp;
<font style='font-size:10pt; color:<?php echo $BBS_ADMIN_td_color2 ?>;'><?php echo $SiteTitle ?> <b>COMMENT!</b></font>
</td>
<td bgcolor='<?php echo $BBS_ADMIN_td_color12 ?>' width=60% align=right>
&nbsp;<font style='font-size:10pt;'>작성인: <font style='color:#<?php echo $BBS_ADMIN_td_color1 ?>; font:bold;'><?php echo $WebtingMemberLogin_id ?></font></font>&nbsp;&nbsp;&nbsp;
</td>
</tr>
<tr><td bgcolor='#<?php echo $BBS_ADMIN_td_color1 ?>' colspan=2><img src='<?php echo $imgdir ?>/12345.gif' width=1 height=2></td></tr>
<tr><td bgcolor='#FFFFFF' colspan=2><img src='<?php echo $imgdir ?>/12345.gif' width=1 height=10></td></tr>
<tr>
<td bgcolor='<?php echo $BBS_ADMIN_td_color2 ?>' width=100% colspan=2>
<TEXTAREA NAME="ComCont" ROWS="3" COLS="50" style='font-size: 9pt; border:1 solid #<?php echo $BBS_ADMIN_td_color1 ?>; color:#<?php echo $BBS_ADMIN_td_color1 ?>; background-color:#<?php echo $BBS_ADMIN_td_color2 ?>;'></TEXTAREA>
<input type=submit value='덧글입력'  style='width=70px; height:45px; font-size: 9pt; border:1 solid #63A0E0; font:bold; color:#<?php echo $BBS_ADMIN_td_color2 ?>; background-color:#<?php echo $BBS_ADMIN_td_color1 ?>;'> 
</td>
</form>
</tr>
<tr><td bgcolor='#FFFFFF' colspan=2><img src='<?php echo $imgdir ?>/12345.gif' width=1 height=10></td></tr>
<?php
include "$DbDir/db.php";
$CommentK_result = mysqli_query($db, "select * from Mlang_{$table}_bbs_coment where Mlang_coment_BBS_no='$BbsViewMlang_bbs_no' order by Mlang_coment_no desc");
$CommentK_rows = mysqli_num_rows($CommentK_result);
echo("<tr><td bgcolor='#FFFFFF' colspan=2 valign=top>");
if ($CommentK_rows) {
    while ($CommentK_row = mysqli_fetch_array($CommentK_result)) { 
?>

<table border=0 align=center width=98% cellpadding=0 cellspacing=0>
<tr><td>

<?php
        $member_id = $CommentK_row['Mlang_coment_member_id'];
        $CommentKIDChick_result = mysqli_query($db, "select * from member where id='$member_id'");
        $CommentKIDChick_row = mysqli_fetch_array($CommentKIDChick_result);
        if ($CommentKIDChick_row) {
            if ($CommentKIDChick_row['job']) {
?>
<font style='color:#006400'><?php echo htmlspecialchars($CommentKIDChick_row['job']); ?></font>&nbsp;
<?php
            } else {
?>
<font style='color:#006400'><?php echo htmlspecialchars($CommentK_row['Mlang_coment_member_id']); ?></font>&nbsp;
<?php
            }
        }
?>

<font style='color:#A4A4A4; font-size:8pt;'>(<?php echo htmlspecialchars($CommentK_row['Mlang_date']); ?>)</font>
<?php if ($WebtingMemberLogin_id == $CommentK_row['Mlang_coment_member_id']) { ?>
<a href='<?php echo $Homedir ?>/bbs/Coment.php?CommentWhi=d&table=<?php echo $table ?>&no=<?php echo $CommentK_row['Mlang_coment_no'] ?>&ComGoTUrl=<?php echo preg_replace("&", "@", $_SERVER["REQUEST_URI"]); ?>'><font style='color:red; font-size:9pt; text-decoration:none'>ⓧ</font></a>
<?php } ?>
</td></tr>
<tr><td>

<?php
        // 문법에러는 없음. 다만, htmlspecialchars()로 이미 특수문자를 변환했으므로
        // <, >, " 등에 대한 str_replace는 중복 처리임. 
        // 실제로는 아래처럼 htmlspecialchars만으로 충분함.
        $CommHtmlCut__text = htmlspecialchars($CommentK_row['Mlang_coment_title']);
        $CommHtmlCut__text = str_replace("|", "&#124;", $CommHtmlCut__text);
        $CommHtmlCut__text = str_replace("\r\n\r\n", "<P>", $CommHtmlCut__text);
        $CommHtmlCut__text = str_replace("\r\n", "<BR>", $CommHtmlCut__text);
?>

<font style='color:#5F5F5F'><?php echo $CommHtmlCut__text ?></font>
</td></tr>
<tr><td colspan=2><img src='<?php echo $imgdir ?>/12345.gif' width=1 height=10></td></tr>
</table>

<?php
    }
} else {
    echo("<p align=center>덧글 등록 자료 없음.</p>");
}
echo("</td></tr>");

mysqli_close($db); 
?>

<tr><td bgcolor='#<?php echo $BBS_ADMIN_td_color1 ?>' colspan=2><img src='<?php echo $imgdir ?>/12345.gif' width=1 height=2></td></tr>
</table>
<?php } ?>