<?php
// 변수 초기화 (Notice 에러 방지)
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_POST['search']) ? $_POST['search'] : '');
$cate = isset($_GET['cate']) ? $_GET['cate'] : (isset($_POST['cate']) ? $_POST['cate'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$page = isset($_GET['page']) ? $_GET['page'] : '';
$title_search = isset($_GET['title_search']) ? $_GET['title_search'] : '';
$PHP_SELF = $_SERVER['PHP_SELF'];

// BBS 관련 변수들 초기화
$BBS_ADMIN_td_width = isset($BBS_ADMIN_td_width) ? $BBS_ADMIN_td_width : '100%';
$BBS_ADMIN_td_color1 = isset($BBS_ADMIN_td_color1) ? $BBS_ADMIN_td_color1 : '#000000';
$BBS_ADMIN_td_color2 = isset($BBS_ADMIN_td_color2) ? $BBS_ADMIN_td_color2 : '#FFFFFF';
$BBS_ADMIN_recnum = isset($BBS_ADMIN_recnum) ? $BBS_ADMIN_recnum : 15;
$BBS_ADMIN_lnum = isset($BBS_ADMIN_lnum) ? $BBS_ADMIN_lnum : 8;
$BBS_ADMIN_cutlen = isset($BBS_ADMIN_cutlen) ? $BBS_ADMIN_cutlen : 100;
$BBS_ADMIN_New_Article = isset($BBS_ADMIN_New_Article) ? $BBS_ADMIN_New_Article : 3;
$BBS_ADMIN_date_select = isset($BBS_ADMIN_date_select) ? $BBS_ADMIN_date_select : 'yes';
$BBS_ADMIN_name_select = isset($BBS_ADMIN_name_select) ? $BBS_ADMIN_name_select : 'yes';
$BBS_ADMIN_count_select = isset($BBS_ADMIN_count_select) ? $BBS_ADMIN_count_select : 'yes';
$BBS_ADMIN_recommendation_select = isset($BBS_ADMIN_recommendation_select) ? $BBS_ADMIN_recommendation_select : 'yes';
$BBS_ADMIN_secret_select = isset($BBS_ADMIN_secret_select) ? $BBS_ADMIN_secret_select : 'yes';

if(!$BbsDir){$BbsDir=".";}
if(!$DbDir){$DbDir="..";}
include "$DbDir/db.php";

if($search){ //검색모드일때

if($cate=="title"){$TgCate="Mlang_bbs_title";}
if($cate=="connent"){$TgCate="Mlang_bbs_connent";}
if($cate=="id"){$TgCate="Mlang_bbs_member";}

$Mlang_query="select * from Mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0'";}else{ // 일반모드 일때
$Mlang_query="select * from Mlang_{$table}_bbs where Mlang_bbs_reply='0'";
}

$query= mysqli_query($db, "$Mlang_query",$db);
$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);
?>


<table border=0 align=center width=<?php echo $BBS_ADMIN_td_width?> cellpadding='5' cellspacing='1' style='word-break:break-all;'>
<tr>
<td align=left>
<font style='font-size:9pt;'>(<?php if($search){echo("검색자료수: $total");}else{echo("등록자료수: $total");}?>)</font>
</td>
<td align=right>
<!------------ 검색 --------------------------------------------------->
<head>
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
<table border=0 align=right cellpadding='0' cellspacing='0'>
<tr>
<form name='MlangSearch' method='post' OnSubmit='javascript:return SearchCheckField()' action='<?php echo("$PHP_SELF");?>'>
<input type='hidden' name='search' value='yes'>
<input type='hidden' name='table' value='<?php echo $table?>'>
<input type='hidden' name='mode' value='list'>
<td>
<font style='font-size:9pt;'>
<input type='radio' name='cate' value='title' checked>제목</option>
<input type='radio' name='cate' value='connent'>내용</option>
<input type='radio' name='cate' value='id'>등록인</option>
<input type='text' name='search' size='20' style='background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>; font-size:9pt;'>
<input type='submit' value=' 검 색' style='background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>; font-size:9pt;'>
</font>
</td>
</form>
</tr>
</table>

</td>
</tr>
</table>
<!------------ 검색 --------------------------------------------------->

<?php
$result= mysqli_query($db, "select * from member where no='1'",$db);
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

<?php
$listcut= $BBS_ADMIN_recnum;
if(!$offset) $offset=0; 

$result= mysqli_query($db, "$Mlang_query order by Mlang_bbs_no desc limit $offset,$listcut",$db);
$rows=mysqli_num_rows($result);
if($rows){

echo("
<table border=0 align=center width=$BBS_ADMIN_td_width cellpadding='5' cellspacing='0' bgcolor='#FFFFFF' style='word-break:break-all;'>

<tr><td width=100% height=1 bgcolor='$BBS_ADMIN_td_color1' background='<?php echo $Homedir?>/img/left_menu_back_134ko.gif' height=1 colspan=9></td></tr>

<tr>
<td align=center nowrap width=60><font style='font:bold;'>&nbsp;번호&nbsp;</font></td>	
<td align=center width=200><font style='font:bold;'>제목</font></td>");


if($BBS_ADMIN_name_select=="yes"){
echo("<td align=center nowrap width=100><font style='font:bold;'>&nbsp;등록인&nbsp;</font></td>");	
}

if($BBS_ADMIN_count_select=="yes"){
echo("<td align=center nowrap width=60><font style='font:bold;'>&nbsp;조회수&nbsp;</font></td>");	
}
	
if($BBS_ADMIN_recommendation_select=="yes"){
echo("<td align=center nowrap width=60><font style='font:bold;'>&nbsp;추천수&nbsp;</font></td>");	
}

if($BBS_ADMIN_date_select=="yes"){
echo("<td align=center nowrap width=90><font style='font:bold;'>&nbsp;날짜&nbsp;</font></td>");	
}

/////////////////////////// 관리자 모드 호출 START //////////////////
$AdminChickTYyj= mysqli_query($db, "select * from member where no='1'");
$row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK = $row_AdminChickTYyj['id'];
if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
    echo("<td align=center nowrap>Admin</td>");
}
/////////////////////////// 관리자 모드 호출 END    //////////////////

echo("</tr>

<tr><td width=100% height=1 bgcolor='$BBS_ADMIN_td_color1' background='$Homedir/img/left_menu_back_134ko.gif' height=1 colspan=9></td></tr>
");

$i = 1 + $offset;
while ($row = mysqli_fetch_array($result)) 
{ 

    $BbsListTitle = substr($row['Mlang_bbs_title'], 0, $BBS_ADMIN_cutlen); // 바이트수 자르기
    $BbsListTitle_1 = htmlspecialchars($BbsListTitle);

    if ($search) { //검색 키워드값
        $BbsListTitle_1 = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $BbsListTitle_1);
    }

    echo("
<tr bgcolor='$BBS_ADMIN_td_color2'>
<td nowrap width=60 align=center>{$row['Mlang_bbs_no']}</td>	
<td><a href='$PHP_SELF?mode=view&table=$table&no={$row['Mlang_bbs_no']}&page=$page' class='bbs'>");
?>
	
<?php echo $BbsListTitle_1?>
	
<?php
echo("</a>");

$BBS_ADMIN_New_Article_Time="$BBS_ADMIN_New_Article";
$writedate=$row['Mlang_date'];
include "$BbsDir/New_Article.php";

echo("</td>");
?>

<?php
if($BBS_ADMIN_name_select=="yes"){
echo("<td align=center nowrap width=100 height=25>");?>
<?php echo htmlspecialchars($row['Mlang_bbs_member']);?>
<?php echo("</td>");}

if ($BBS_ADMIN_count_select == "yes") {
    echo "<td align=center nowrap width=60>" . htmlspecialchars($row['Mlang_bbs_count']) . "</td>";
}

if ($BBS_ADMIN_recommendation_select == "yes") {
    echo "<td align=center nowrap width=60>" . htmlspecialchars($row['Mlang_bbs_rec']) . "</td>";
}

if ($BBS_ADMIN_date_select == "yes") {
    $date_11 = substr($row['Mlang_date'], 0, 10);
    echo "<td align=center nowrap width=90>$date_11</td>";

    /////////////////////////// 관리자 모드 호출 START //////////////////
    $AdminChickTYyj = mysqli_query($db, "select * from member where no='1'");
    $row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
    $BBSAdminloginKK = $row_AdminChickTYyj['id'];
    if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
        echo "<td align=center nowrap>";
        $AdminYdddNo = $row['Mlang_bbs_no'];
        include "$BbsDir/AmdinCount.php";
        echo "</td>";
    }
    /////////////////////////// 관리자 모드 호출 END    //////////////////
}

// 답변 글을 호출해준다. ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$result_reply = mysqli_query($db, "select * from Mlang_{$table}_bbs where Mlang_bbs_reply='" . $row['Mlang_bbs_no'] . "'");
$rows_reply = mysqli_num_rows($result_reply);
if ($rows_reply) {
    $i_reply = 1 + $offset;
    while ($row_reply = mysqli_fetch_array($result_reply)) {

        $BbsListTitleReply = substr($row_reply['Mlang_bbs_title'], 0, $BBS_ADMIN_cutlen); // 바이트수 자르기
        $BbsListTitleReply_1 = htmlspecialchars($BbsListTitleReply);

        if ($search) { //검색 키워드값
            $BbsListTitleReply_1 = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $BbsListTitleReply_1);
        }

        echo "
<tr bgcolor='$BBS_ADMIN_td_color2'>
<td nowrap width=60 align=center><font style='font-size:8pt; color:$BBS_ADMIN_td_color1;'>" . htmlspecialchars($row_reply['Mlang_bbs_reply']) . "-$i_reply</font></td>	
<td><font style='font-size:8pt; color:$BBS_ADMIN_td_color1;'>&nbsp;┗▶RE:</font>&nbsp;<a href='$PHP_SELF?mode=view&table=$table&no=" . htmlspecialchars($row_reply['Mlang_bbs_no']) . "&page=$page' class='bbs'>";
?>
	
<?php echo $BbsListTitleReply_1?>

<?php echo("</a></td>");?>
	

<?php
if ($BBS_ADMIN_name_select == "yes") {
    echo "<td align=center nowrap width=100>";
    echo htmlspecialchars($row_reply['Mlang_bbs_member']);
    echo "</td>";
}

if ($BBS_ADMIN_count_select == "yes") {
    echo "<td align=center nowrap width=60>" . htmlspecialchars($row_reply['Mlang_bbs_count']) . "</td>";
}

if ($BBS_ADMIN_recommendation_select == "yes") {
    echo "<td align=center nowrap width=60>" . htmlspecialchars($row_reply['Mlang_bbs_rec']) . "</td>";
}

if ($BBS_ADMIN_date_select == "yes") {
    $date_112 = substr($row_reply['Mlang_date'], 0, 10);
    echo "<td align=center nowrap width=90>$date_112";

    /////////////////////////// 관리자 모드 호출 START //////////////////
    $AdminChickTYyj = mysqli_query($db, "select * from member where no='1'");
    $row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
    $BBSAdminloginKK = $row_AdminChickTYyj['id'];
    if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
        echo "<td align=center nowrap>";
        $AdminYdddNo = $row_reply['Mlang_bbs_no'];
        include "$BbsDir/AmdinCount.php";
        echo "</td>";
    }
    /////////////////////////// 관리자 모드 호출 END    //////////////////

    echo "</td>";
}

echo "</tr>";
$i_reply = $i_reply + 1;
    }
} // end if ($rows_reply)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$i = $i + 1;
}

echo "</table>";

} else {

    if ($search) {
        echo "<p align=center><b>$search 에 검색자료 자료없음</b></p>";
    } else {
        echo "<p align=center><b>등록 자료없음</b></p>";
    }

}

?>

<p align='center'>
<font style='font-size:10pt;'>
<?php
if($rows){

if($search){
$mlang_pagego="cate=$cate&search=$search&table=$table&mode=list&page=$page"; 
}else{
$mlang_pagego="table=$table&mode=list&page=$page"; 
}

$pagecut= $BBS_ADMIN_lnum;  
$one_bbs= $listcut*$pagecut; 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;
$start_page= intval($start_offset/$listcut)+1; 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 

if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'><img src='$BbsDir/img/left.gif' border=0 align=absmiddle></a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset){
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;"; 
}else{echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"); } 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'><img src='$BbsDir/img/right.gif' border=0 align=absmiddle></a>"; 
} 
echo "&nbsp;&nbsp;총목록갯수: $end_page 개"; 


}

mysqli_close($db); 
?> 
</font>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&page=<?php echo $page?>'><img src='<?php echo $BbsDir?>/img/write.gif' border=0 align=absmiddle></a>	
</p>
<!------------------------------------------- 리스트 끝----------------------------------------->