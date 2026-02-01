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

// 변수 초기화
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_POST['search']) ? $_POST['search'] : '');
$cate = isset($_GET['cate']) ? $_GET['cate'] : (isset($_POST['cate']) ? $_POST['cate'] : 'title');
$CATEGORY = isset($_GET['CATEGORY']) ? $_GET['CATEGORY'] : '';
$PHP_SELF = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$CCV = isset($_GET['CCV']) ? $_GET['CCV'] : '';
$CCX = isset($_GET['CCX']) ? $_GET['CCX'] : '';
$PCode = isset($_GET['PCode']) ? $_GET['PCode'] : '';

if($search){ //검색모드일때

if($cate=="title"){$TgCate="Mlang_bbs_title";}
if($cate=="connent"){$TgCate="Mlang_bbs_connent";}
if($cate=="id"){$TgCate="Mlang_bbs_member";}

if($CATEGORY){
$Mlang_query="select * from mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
}else{
$Mlang_query="select * from mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0'";
}

}else{ // 일반모드 일때

if($CATEGORY){
$Mlang_query="select * from mlang_{$table}_bbs where Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
}else{
$Mlang_query="select * from mlang_{$table}_bbs where Mlang_bbs_reply='0'";
}

}

// 테이블 이름 확인 및 수정
if (empty($table)) {
    // 테이블 이름이 비어 있으면 기본값 설정
    $table = "portfolio";
    echo "<p style='color:orange;'>테이블 이름이 비어 있어 기본값 'portfolio'를 사용합니다.</p>";
}

// 쿼리 재구성
if($search){
    if($CATEGORY){
        $Mlang_query="select * from mlang_{$table}_bbs where $TgCate like '%$search%' and Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
    }else{
        $Mlang_query="select * from mlang_{$table}_bbs where $TgCate like '%$search%' and Mlang_bbs_reply='0'";
    }
}else{
    if($CATEGORY){
        $Mlang_query="select * from mlang_{$table}_bbs where Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
    }else{
        $Mlang_query="select * from mlang_{$table}_bbs where Mlang_bbs_reply='0'";
    }
}

$query = mysqli_query($db, "$Mlang_query");
if ($query) {
    $recordsu = mysqli_num_rows($query);
    $total = mysqli_affected_rows($db);
} else {
    // 쿼리 실패 시 오류 메시지 표시 및 기본값 설정
    echo "<p style='color:red;'>데이터베이스 쿼리 오류: " . mysqli_error($db) . "</p>";
    echo "<p>쿼리: $Mlang_query</p>";
    $recordsu = 0;
    $total = 0;
}
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
<input type='hidden' name='page' value='<?php echo $page?>'>
<td>
<font style='font-size:9pt;'>
<input type='radio' name='cate' value='title' checked>제목</option>
<input type='radio' name='cate' value='connent'>내용</option>
<input type='radio' name='cate' value='id'>등록인</option>
<input type='text' name='search' size='12' style='background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>; font-size:9pt;'>
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
$result= mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1");
$row= mysqli_fetch_array($result);
$BBSAdminloginKK=$row['id'];
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){
?>
<script>
function AdminBdgDel(no){
	var str;
		if (confirm("게시판의 자료를 삭제하려 하십니다.\n\n한번 삭제한 자료는 두번다시 복구되지 않습니다.\n\n삭제하시려면 확인을 누르시기 바랍니다.")) {
		str='<?php echo $Homedir?>/admin/int/delete.php?no='+no+'&bbs=del&table=<?php echo("mlang_{$table}_bbs");?>';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}

function AdminBdCount(no){
	var str;
		if (confirm("카운터의 자료를 수정 하시겠습니까...?")) {
		str='<?php echo $Homedir?>/admin/int/delete.php?AdminCode21=form&no='+no+'&table=<?php echo("mlang_{$table}_bbs");?>';
        popup = window.open("","","scrollbars=no,resizable=yes,width=450,height=150,top=120,left=20");
        popup.document.location.href=str;
        popup.focus();
	}
}
</script>
<?php }?>

<?php
//Mlang_bbs_rec =  추천,  Mlang_bbs_count = 조회 &CCV=$CCV&CCX=$CCX
if($CCV=="count"){
    if($CCX=="T"){$CountListPokWW="Mlang_bbs_count asc";}else{$CountListPokWW="Mlang_bbs_count desc";}
}
if($CCV=="rec"){
    if($CCX=="T"){$CountListPokWW="Mlang_bbs_rec asc";}else{$CountListPokWW="Mlang_bbs_rec desc";}
	}
if(!$CCV){
if($CCX=="T"){$CountListPokWW="Mlang_bbs_no asc";}else{$CountListPokWW="Mlang_bbs_no desc";}
}

$listcut= $BBS_ADMIN_recnum;
if(!$offset) $offset=0; 

// 정렬 컬럼 설정
if(!isset($CountListPokWW)) {
    $CountListPokWW = "Mlang_bbs_no desc";
}

$result= mysqli_query($db, "$Mlang_query order by $CountListPokWW limit $offset,$listcut");
$rows = ($result) ? mysqli_num_rows($result) : 0;
if($rows){

echo("
<table border=0 align=center width=$BBS_ADMIN_td_width cellpadding='5' cellspacing='0' bgcolor='#FFFFFF' style='word-break:break-all;'>

<tr><td width=100% height=1 bgcolor='$BBS_ADMIN_td_color1' background='$Homedir/img/left_menu_back_134ko.gif' height=1 colspan=9></td></tr>

<tr>

<td align=center nowrap width=60><font style='font:bold;'>&nbsp;번호&nbsp;</font></td>	
<td align=center width=200>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<font style='font:bold;'>제목</font>&nbsp;<font style='color:#A1A1A1; font-size:8pt;'>[덧글수]</font></td>");

if($BBS_ADMIN_cate){
echo("<td align=center nowrap width=120><font style='font:bold;'>&nbsp;분류&nbsp;</font></td>");	
}


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
$AdminChickTYyj = mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1");
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
while ($row = mysqli_fetch_array($result)) {

    $BbsListTitle_1_ok = str_cutting($row['Mlang_bbs_title'], $x);
    $BbsListTitle_1 = htmlspecialchars($BbsListTitle_1_ok);

    if ($search) { //검색 키워드값
        $BbsListTitle_1 = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $BbsListTitle_1);
    }

    echo("
<tr bgcolor='$BBS_ADMIN_td_color2'>
<td nowrap width=60 align=center>{$row['Mlang_bbs_no']}</td>	
<td><a href='$PHP_SELF?mode=view&table=$table&no={$row['Mlang_bbs_no']}&page=$page&CATEGORY=$CATEGORY&offset=$offset&PCode=$PCode' class='bbs'>");
    ?>

    <?php echo $BbsListTitle_1 ?>
    <?php
    echo("</a>");

    $ComFFCode = "1"; include "$BbsDir/ComentSu.php";

    $BBS_ADMIN_New_Article_Time = "$BBS_ADMIN_New_Article";
    $writedate = $row['Mlang_date'];
    include "$BbsDir/New_Article.php";

    echo("</td>");
    ?>

    <?php
    if ($BBS_ADMIN_cate) {
        echo("<td align=center nowrap width=120>{$row['CATEGORY']}</td>");
    }

    if ($BBS_ADMIN_name_select == "yes") {
        echo("<td align=center nowrap width=100 height=25>");
        echo htmlspecialchars($row['Mlang_bbs_member']);
        echo("</td>");
    }

    if ($BBS_ADMIN_count_select == "yes") {
        echo("<td align=center nowrap width=60>{$row['Mlang_bbs_count']}</td>");
    }

    if ($BBS_ADMIN_recommendation_select == "yes") {
        echo("<td align=center nowrap width=60>{$row['Mlang_bbs_rec']}</td>");
    }

    if ($BBS_ADMIN_date_select == "yes") {
        $date_11 = substr($row['Mlang_date'], 0, 10);
        echo("<td align=center nowrap width=90>$date_11</td>");

        /////////////////////////// 관리자 모드 호출 START //////////////////
        $AdminChickTYyj = mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1");
        $row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
        $BBSAdminloginKK = $row_AdminChickTYyj['id'];
        if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
            echo("<td align=center nowrap>");
            $AdminYdddNo = $row['Mlang_bbs_no'];
            include "$BbsDir/AmdinCount.php";
            echo("</td>");
        }
        /////////////////////////// 관리자 모드 호출 END    //////////////////
    }

    // 답변 글을 호출해준다. ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $result_reply = mysqli_query($db, "select * from mlang_{$table}_bbs where Mlang_bbs_reply='{$row['Mlang_bbs_no']}'");
    $rows_reply = ($result_reply) ? mysqli_num_rows($result_reply) : 0;
    if ($rows_reply) {
        $i_reply = 1 + $offset;
        while ($row_reply = mysqli_fetch_array($result_reply)) {

            $BbsListTitleReply_1_ok = str_cutting($row_reply['Mlang_bbs_title'], $x - 10);
            $BbsListTitleReply_1 = htmlspecialchars($BbsListTitleReply_1_ok);

            if ($search) { //검색 키워드값
                $BbsListTitleReply_1 = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $BbsListTitleReply_1);
            }

            echo("
<tr bgcolor='$BBS_ADMIN_td_color2'>
<td nowrap width=60 align=center><font style='font-size:8pt; color:$BBS_ADMIN_td_color1;'>{$row_reply['Mlang_bbs_reply']}-{$i_reply}</font></td>	
<td><font style='font-size:8pt; color:$BBS_ADMIN_td_color1;'>&nbsp;┗▶RE:</font>&nbsp;<a href='$PHP_SELF?mode=view&table=$table&no={$row_reply['Mlang_bbs_no']}&page=$page&CATEGORY=$CATEGORY&offset=$offset&PCode=$PCode' class='bbs'>");
            ?>

            <?php echo $BbsListTitleReply_1 ?>

            </a>
            <?php $ComFFCode = "2"; include "$BbsDir/ComentSu.php"; ?>
            </td>

            <?php
            if ($BBS_ADMIN_cate) {
                echo("<td align=center nowrap width=100>{$row_reply['CATEGORY']}</td>");
            }

            if ($BBS_ADMIN_name_select == "yes") {
                echo("<td align=center nowrap width=100>");
                echo htmlspecialchars($row_reply['Mlang_bbs_member']);
                echo("</td>");
            }

            if ($BBS_ADMIN_count_select == "yes") {
                echo("<td align=center nowrap width=60>{$row_reply['Mlang_bbs_count']}</td>");
            }

            if ($BBS_ADMIN_recommendation_select == "yes") {
                echo("<td align=center nowrap width=60>{$row_reply['Mlang_bbs_rec']}</td>");
            }

            if ($BBS_ADMIN_date_select == "yes") {
                $date_112 = substr($row_reply['Mlang_date'], 0, 10);
                echo("<td align=center nowrap width=90>$date_112");

                /////////////////////////// 관리자 모드 호출 START //////////////////
                $AdminChickTYyj = mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1");
                $row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
                $BBSAdminloginKK = $row_AdminChickTYyj['id'];
                if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
                    echo("<td align=center nowrap>");
                    $AdminYdddNo = $row_reply['Mlang_bbs_no'];
                    include "$BbsDir/AmdinCount.php";
                    echo("</td>");
                }
                /////////////////////////// 관리자 모드 호출 END    //////////////////

                echo("</td>");
            }

            echo("</tr>");
            $i_reply = $i_reply + 1;
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $i = $i + 1;
}

echo("</table>");

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
$mlang_pagego="cate=$cate&search=$search&table=$table&mode=list&page=$page&CATEGORY=$CATEGORY&CCV=$CCV&CCX=$CCX&PCode=$PCode"; 
}else{
$mlang_pagego="table=$table&mode=list&page=$page&CATEGORY=$CATEGORY&CCV=$CCV&CCX=$CCX&PCode=$PCode"; 
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
<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&page=<?php echo $page?>&CATEGORY=<?php echo $CATEGORY?>&PCode=<?php echo $PCode?>'><img src='<?php echo $BbsDir?>/img/write.gif' border=0 align=absmiddle></a>	
<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=list&table=<?php echo $table?>&page=<?php echo $page?>&PCode=<?php echo $PCode?>'><img src='<?php echo $Homedir?>/bbs/img/list.gif' border=0 align=absmiddle></a>
</p>
<!------------------------------------------- 리스트 끝----------------------------------------->