<?php
// 스킨 이미지의 적용을 할경우 새로운 스킨을 제작 할경우 코드
// skin/$BBS_ADMIN_skin/ 을 bbs/img 중간에
// bbs/skin/$BBS_ADMIN_skin/img 형식으로 삽입을 해준다.
// 모든 게시판의 기본 경로는 bbs.php 기준이나 그앞에서 $BbsDir 이 기준이 된다 $BbsDir 이 없을경우는 ./ 가 기본이다
// $Homedir  은 최상으로 기준을 하며 메인디렉토리의 db.php 에서 그 권환을 설정할수 있다.
?>

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


if($CATEGORY){ // 가테고리모드일때

if($search){ //검색모드일때

if($cate=="title"){$TgCate="Mlang_bbs_title";}
if($cate=="connent"){$TgCate="Mlang_bbs_connent";}
if($cate=="id"){$TgCate="Mlang_bbs_member";}

$Mlang_query="select * from Mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0'  and CATEGORY='$CATEGORY'";}else{ // 일반모드 일때
$Mlang_query="select * from Mlang_{$table}_bbs where Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
}


}else{ // 카테고리 아닐때

if($search){ //검색모드일때

if($cate=="title"){$TgCate="Mlang_bbs_title";}
if($cate=="connent"){$TgCate="Mlang_bbs_connent";}
if($cate=="id"){$TgCate="Mlang_bbs_member";}

$Mlang_query="select * from Mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0'";}else{ // 일반모드 일때
$Mlang_query="select * from Mlang_{$table}_bbs where Mlang_bbs_reply='0'";
}


}

$query= mysqli_query($db, "$Mlang_query",$db);
$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);
?>


<table border=0 align=center width=<?php echo $BBS_ADMIN_td_width?> cellpadding='5' cellspacing='1' style='word-break:break-all;'>
<tr>
<td align=left>
<?php if($BBS_ADMIN_cate){?>
<script language="JavaScript">
function BBS_CATE(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
<?php $CateCodeUgt="1";  include "$BbsDir/BBS_CATE.php";}?>
<?php if($search){echo("검색자료수: $total");}else{echo("등록자료수: $total");}?>
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
<input type='radio' name='cate' value='title' checked>제목</option>
<input type='radio' name='cate' value='connent'>내용</option>
<input type='radio' name='cate' value='id'>등록인</option>
<input type='text' name='search' size='12'>
<input type='submit' value=' 검 색'>
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
<table border=0 align=center width=$BBS_ADMIN_td_width cellpadding='3' cellspacing='0' bgcolor='#FFFFFF' style='word-break:break-all;'>

<tr><td width=100% height=2 bgcolor='#d3d3d3' colspan=13></td></tr>
<tr bgcolor='#fafafa'>
<td align=center nowrap width=50><font style='font:bold;'>&nbsp;번호&nbsp;</font></td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>
<td align=center width=260><font style='font:bold;'>제목</font></td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");

if($BBS_ADMIN_cate){
echo("<td align=center nowrap width=120><font style='font:bold;'>&nbsp;분류&nbsp;</font></td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");	
}

if($BBS_ADMIN_name_select=="yes"){
echo("<td align=center nowrap width=100><font style='font:bold;'>&nbsp;등록인&nbsp;</font></td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");	
}

if($BBS_ADMIN_count_select=="yes"){
echo("<td align=center nowrap width=50><font style='font:bold;'>&nbsp;조회&nbsp;</font></td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");	
}
	
if($BBS_ADMIN_recommendation_select=="yes"){
echo("<td align=center nowrap width=60><font style='font:bold;'>&nbsp;추천수&nbsp;</font></td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");	
}

if($BBS_ADMIN_date_select=="yes"){
echo("<td align=center nowrap width=70><font style='font:bold;'>&nbsp;날짜&nbsp;</font></td>");	
}

/////////////////////////// 관리자 모드 호출 START //////////////////
$AdminChickTYyj= mysqli_query($db, "select * from member where no='1'");
$row_AdminChickTYyj= mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK="$row_AdminChickTYyj['id'];
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){
echo("<td align=center nowrap>Aamin</td>");
}
/////////////////////////// 관리자 모드 호출 END    //////////////////

echo("</tr>

<tr><td width=100% height=1 bgcolor='#d3d3d3' colspan=13></td></tr>
");

$i=1+$offset;
while($row= mysqli_fetch_array($result)) 
{ 

$BbsListTitle_1_ok=str_cutting("$row['Mlang_bbs_title']",$x);
$BbsListTitle_1=htmlspecialchars($BbsListTitle_1_ok);

if ($search) //검색 키워드값
{$BbsListTitle_1 = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $BbsListTitle_1);}

echo("
<tr bgcolor='$BBS_ADMIN_td_color2'>
<td nowrap width=50 align=center>$row['Mlang_bbs_no']</td>
<td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>
<td width=260><a href='$PHP_SELF?mode=view&table=$table&no=$row['Mlang_bbs_no']&page=$page&CATEGORY=$CATEGORY&offset=$offset&PCode=$PCode' class='bbs'>");
?>

	
<?php echo $BbsListTitle_1?>
	
<?php
echo("</a>");

$BBS_ADMIN_New_Article_Time="$BBS_ADMIN_New_Article";
$writedate=$row['Mlang_date'];
include "$BbsDir/New_Article.php";

echo("</td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");
?>

<?php
if($BBS_ADMIN_cate){
echo("<td align=center nowrap width=120>$row['CATEGORY']</td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");	
}

if($BBS_ADMIN_name_select=="yes"){
echo("<td align=center nowrap width=100 height=25>");?>
<?php echo htmlspecialchars($row['Mlang_bbs_member']);?>
<?php echo("</td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");}

if($BBS_ADMIN_count_select=="yes"){
echo("<td align=center nowrap width=50>$row['Mlang_bbs_count']</td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");	
}
	
if($BBS_ADMIN_recommendation_select=="yes"){
echo("<td align=center nowrap width=60>$row['Mlang_bbs_rec']</td><td width=2><img src='$BbsDir/skin/$BBS_ADMIN_skin/img/top_line_point.gif' border=0 align=absmiddle></td>");	
}

if($BBS_ADMIN_date_select=="yes"){
$date_11 = substr($row['Mlang_date'], 0,10);
echo("<td align=center nowrap width=70>$date_11</td>");

/////////////////////////// 관리자 모드 호출 START //////////////////
$AdminChickTYyj= mysqli_query($db, "select * from member where no='1'");
$row_AdminChickTYyj= mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK="$row_AdminChickTYyj['id'];
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){
echo("<td align=center nowrap>");
$AdminYdddNo=$row['Mlang_bbs_no'];
include "$BbsDir/AmdinCount.php";
echo("</td>");
}
/////////////////////////// 관리자 모드 호출 END    //////////////////

}

// 답변 글을 호출해준다. ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$result_reply= mysqli_query($db, "select * from Mlang_{$table}_bbs where Mlang_bbs_reply='$row['Mlang_bbs_no']'",$db);
$rows_reply=mysqli_num_rows($result_reply);
if($rows_reply){
$i_reply=1+$offset;
while($row_reply= mysqli_fetch_array($result_reply)) 
{ 

$BbsListTitleReply_1_ok=str_cutting("$row_reply['Mlang_bbs_title']",$x-10);
$BbsListTitleReply_1=htmlspecialchars($BbsListTitleReply_1_ok);

if ($search) //검색 키워드값
{$BbsListTitleReply_1 = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $BbsListTitleReply_1);}

echo("
<tr bgcolor='$BBS_ADMIN_td_color2'>
<td nowrap width=60 align=center><font style='font-size:8pt; color:$BBS_ADMIN_td_color1;'>$row_reply['Mlang_bbs_reply']-$i_reply</font></td>	
<td><font style='font-size:8pt; color:$BBS_ADMIN_td_color1;'>&nbsp;┗▶RE:</font>&nbsp;<a href='$PHP_SELF?mode=view&table=$table&no=$row_reply['Mlang_bbs_no']&page=$page&CATEGORY=$CATEGORY&offset=$offset&PCode=$PCode' class='bbs'>");
?>
	
<?php echo $BbsListTitleReply_1?>

</a>
<?php $ComFFCode="2"; include "$BbsDir/ComentSu.php";?>
</td>
	

<?php
if($BBS_ADMIN_name_select=="yes"){
echo("<td align=center nowrap width=100>");?>
<?php echo htmlspecialchars($row_reply['Mlang_bbs_member']);?>
<?php echo("</td>");}

if($BBS_ADMIN_count_select=="yes"){
echo("<td align=center nowrap width=60>$row_reply['Mlang_bbs_count']</td>");	
}
	
if($BBS_ADMIN_recommendation_select=="yes"){
echo("<td align=center nowrap width=60>$row_reply['Mlang_bbs_rec']</td>");	
}

if($BBS_ADMIN_date_select=="yes"){
$date_112 = substr($row_reply['Mlang_date'], 0,10);
echo("<td align=center nowrap width=90>$date_112</td>");
}

/////////////////////////// 관리자 모드 호출 START //////////////////
$AdminChickTYyj= mysqli_query($db, "select * from member where no='1'");
$row_AdminChickTYyj= mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK="$row_AdminChickTYyj['id'];
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){
echo("<td align=center nowrap>");
$AdminYdddNo="$row_reply['Mlang_bbs_no'];
include "$BbsDir/AmdinCount.php";
echo("</td>");
}
/////////////////////////// 관리자 모드 호출 END    //////////////////


echo("</tr><tr><td width=100% height=1 bgcolor='#E1E1E1' colspan=13></td></tr>");
		$i_reply=$i_reply+1;
}

}else{}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$i=$i+1;
} 

echo("<tr><td width=100% height=1 bgcolor='#E1E1E1' colspan=13></td></tr>");

}else{

if($search){
echo"<p align=center><b>$search 에 검색자료 자료없음</b></p>";
}else{
echo"<p align=center><b>등록 자료없음</b></p>";
}

}

if($rows){ echo("</table>"); }
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
<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&page=<?php echo $page?>'><img src='<?php echo $BbsDir?>/skin/<?php echo $BBS_ADMIN_skin?>/img/write.gif' border=0 align=absmiddle>
</a>	
</p>
<!------------------------------------------- 리스트 끝----------------------------------------->
?>