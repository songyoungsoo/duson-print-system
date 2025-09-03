<?php 
// 변수 초기화 추가
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
if (!isset($page)) $page = 1;
if (!isset($PCode)) $PCode = '';

function str_cutting($str, $len){ 
    preg_match('/([\x00-\x7e]|..)*/', substr($str, 0, $len), $rtn); 
    if ( $len < strlen($str) ) $rtn[0].=".."; 
    return $rtn[0]; 
} 

$x = isset($BBS_ADMIN_cutlen) ? $BBS_ADMIN_cutlen : 100;
?>

<!------------------------------------------- 리스트 시작------------------------------------------->
<?php
if(!$BbsDir){$BbsDir = ".";}
if(!$DbDir){$DbDir = "..";}
include "$DbDir/db.php";
$search = isset($_GET['search']) ? $_GET['search'] : '';
if($search){
    if($cate=="title"){$TgCate="Mlang_bbs_title";}
    if($cate=="connent"){$TgCate="Mlang_bbs_connent";}
    if($cate=="id"){$TgCate="Mlang_bbs_member";}
    if($CATEGORY){
        $Mlang_query = "select * from Mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
    }else{
        $Mlang_query = "select * from Mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0'";
    }
}else{
    if($CATEGORY){
        $Mlang_query = "select * from Mlang_{$table}_bbs where Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
    }else{
        $Mlang_query = "select * from Mlang_{$table}_bbs where Mlang_bbs_reply='0'";
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
<style type="text/css">
body {
    margin-left: 1px;
    margin-top: 1px;
    margin-right: 1px;
    margin-bottom: 1px;
}
</style>

<table border=0 align=center width=100% cellpadding='3' cellspacing='1' style='word-break:break-all;'>
<tr>
<td align=left>
<font style='font-size:9pt;'>(<?php if($search){echo("검색자료수: $total");}else{echo("등록자료수: $total");}?>)</font>
</td>
<td align=right>
<!------------ 검색 --------------------------------------------------->
<form name='MlangSearch' method='post' OnSubmit='javascript:return SearchCheckField()' action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>'>
<input type='hidden' name='search' value='yes'>
<input type='hidden' name='table' value='<?php echo $table?>'>
<input type='hidden' name='mode' value='list'>
<input type='hidden' name='page' value='<?php echo $page?>'>
<font style='font-size:9pt;'>
<input type='radio' name='cate' value='title' checked>제목
<input type='radio' name='cate' value='connent'>내용
<input type='radio' name='cate' value='id'>등록인
<input type='text' name='search' size='12' style='background-color:#FFFFFF; color:#000000; border-style:solid; border:1 solid #000000; font-size:9pt;'>
<input type='submit' value=' 검 색' style='background-color:#FFFFFF; color:#000000; border-style:solid; border:1 solid #000000; font-size:9pt;'>
</font>
</form>
</td>
</tr>
</table>
<!------------ 검색 --------------------------------------------------->

<?php
$listcut = 24;
$offset = isset($_REQUEST['offset']) ? intval($_REQUEST['offset']) : 0;

$result = mysqli_query($db, "$Mlang_query order by Mlang_bbs_no desc limit $offset,$listcut");
if (!$result) {
    echo "<p style='color:red;'>데이터베이스 쿼리 오류: " . mysqli_error($db) . "</p>";
    echo "<p>쿼리: $Mlang_query order by Mlang_bbs_no desc limit $offset,$listcut</p>";
} else {
    $rows = mysqli_num_rows($result);
    if ($rows) {
        echo "<table border=0 align=center width=96% cellpadding='0' cellspacing='0'><tr>";

        $i = 1 + $offset;
        $says = $listcut / 6;

        while ($row = mysqli_fetch_array($result)) { 
            $BbsListTitle_1_ok = str_cutting($row['Mlang_bbs_title'], $x);
            $BbsListTitle_1 = htmlspecialchars($BbsListTitle_1_ok);

            if ($search) // 검색 키워드값
            {$BbsListTitle_1 = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $BbsListTitle_1);}

            $BbsListMlang_bbs_style = htmlspecialchars($row['Mlang_bbs_style']);
            $BbsViewMlang_bbs_connent = htmlspecialchars($row['Mlang_bbs_connent']);
            $BbsListMlang_bbs_link = htmlspecialchars($row['Mlang_bbs_link']);
            $BbsListMlang_bbs_file = htmlspecialchars($row['Mlang_bbs_file']);
            ?>

        <td align=center>
        <table border=0 align=center cellpadding='0' cellspacing='0' style="TABLE-LAYOUT: fixed">
        <tr><td width=186 height=257 valign=middle align=center>
        <table border=0 align=center width=184 height=255 cellpadding='1' cellspacing='1' bgcolor="#CCCCCC">
        <tr>
          <td width="182" height="253" valign="middle" bgcolor="#ffffff" cellspacing="0">
            <?php
            $image_path = '';
            $image_alt = $BbsListTitle_1;
            
            if ($BbsViewMlang_bbs_connent) {
                $image_path = '/bbs/upload/' . $table . '/' . $BbsViewMlang_bbs_connent;
                echo "<img src='" . $image_path . "' border=0 width=182 height=253 style='cursor:pointer;' onclick=\"openLightbox('" . $image_path . "', '" . htmlspecialchars($image_alt, ENT_QUOTES) . "')\">";
            } else if ($BbsListMlang_bbs_link) {
                $image_path = $BbsListMlang_bbs_link;
                echo "<img src='" . $image_path . "' border=0 width=182 height=253 style='cursor:pointer;' onclick=\"openLightbox('" . $image_path . "', '" . htmlspecialchars($image_alt, ENT_QUOTES) . "')\">";
            } else {
                echo "<p align=center><font style='font-size:20pt; color:#C9C9C9;'>NO</font><BR><font style='font-size:10pt; color:#C9C9C9;'>Image</font></p>";
            }
            ?></td>
          </tr>
        </table>
        </td></tr>
        <tr><td><img src='/img/12345.gif' width=1 height=3></td></tr>
        <tr><td valign=middle align='center'>
        <table border='0' width=180 align='center' cellpadding='0' cellspacing='0' style="TABLE-LAYOUT: fixed">
        <tr><td><a href='<?php echo "$PHP_SELF?mode=view&table=" . $table . "&no=" . $row['Mlang_bbs_no'] . "&page=" . $page; ?>' class='bbs'><?php echo $BbsListTitle_1; ?></a>
        </td></tr>
        </table>
        </td></tr>
        </table>
        </td>
        <?php
        if ($i % $says == 0) {
            echo "
        </tr>
        <tr><td height=10></td></tr>
        <tr>
        ";
        }
        $i = $i + 1;
        } 
        echo "</tr></table>";
    } else {
        echo "<p align=center><BR><BR><b>등록 자료없음</b></p>";
    }

    // 페이지네이션
    if ($rows) {
        echo "<p align='center'><font style='font-size:10pt;'>";
        
        if ($search) {
            $mlang_pagego = "cate=" . $cate . "&search=" . $search . "&table=" . $table . "&mode=list&page=" . $page . "&PCode=" . $PCode; 
        } else {
            $mlang_pagego = "table=" . $table . "&mode=list&page=" . $page . "&PCode=" . $PCode; 
        }

        $pagecut = isset($BBS_ADMIN_lnum) ? $BBS_ADMIN_lnum : 8;  
        $one_bbs = $listcut * $pagecut; 
        $start_offset = intval($offset / $one_bbs) * $one_bbs;  
        $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
        $start_page = intval($start_offset / $listcut) + 1; 
        $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut); 

        if ($start_offset != 0) { 
            $apoffset = $start_offset - $one_bbs; 
            echo "<a href='$PHP_SELF?offset=" . $apoffset . "&" . $mlang_pagego . "'><img src='$BbsDir/img/left.gif' border=0 align=absmiddle></a>&nbsp;"; 
        } 

        for ($i = $start_page; $i < $start_page + $pagecut; $i++) { 
            $newoffset = ($i - 1) * $listcut; 

            if ($offset != $newoffset) {
                echo "&nbsp;<a href='$PHP_SELF?offset=" . $newoffset . "&" . $mlang_pagego . "'>(" . $i . ")</a>&nbsp;"; 
            } else {
                echo "&nbsp;<font style='font:bold; color:green;'>(" . $i . ")</font>&nbsp;"; 
            }

            if ($i == $end_page) break; 
        } 

        if ($start_offset != $end_offset) { 
            $nextoffset = $start_offset + $one_bbs; 
            echo "&nbsp;<a href='$PHP_SELF?offset=" . $nextoffset . "&" . $mlang_pagego . "'><img src='$BbsDir/img/right.gif' border=0 align=absmiddle></a>"; 
        } 
        echo "&nbsp;&nbsp;총목록갯수: " . $end_page . " 개"; 
        echo "</font></p>";
    }
}

// 글쓰기 버튼
echo "<p align='center'>";
echo "<a href='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?mode=write&table=" . $table . "&page=" . $page . "&PCode=" . $PCode . "'>";
echo "<img src='$BbsDir/img/write.gif' border=0 align=absmiddle>";
echo "</a>";
echo "</p>";

mysqli_close($db);
?>
<!------------------------------------------- 리스트 끝----------------------------------------->