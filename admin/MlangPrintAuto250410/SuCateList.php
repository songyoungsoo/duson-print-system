<?php
include "../title.php";
include "../../MlangPrintAuto/ConDb.php";
?>

<head>
<script>
self.moveTo(0,0);
self.resizeTo(screen.availWidth=400,screen.availHeight);

function clearField(field) {
    if (field.value == field.defaultValue) {
        field.value = "";
    }
}

function checkField(field) {
    if (!field.value) {
        field.value = field.defaultValue;
    }
}

function WebOffice_customer_Del(no) {
    if (confirm(no + '번 자료를 삭제 하시겠습니까?\n\n최상위 일경우 하위항목까지 삭제가 됩니다.\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요!')) {
        var str = './SuCateAdmin.php?no=' + no + '&mode=delete';
        var popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>

<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<?php
include "../../db.php";

// 변수 초기화
$Cate = isset($Cate) ? $Cate : '';

if ($Cate) { // 검색
    $Mlang_query = "SELECT * FROM $GGTABLESu WHERE Ttable='$View_TtableB' AND BigNo='$Cate'";
} else { // 일반모드
    $Mlang_query = "SELECT * FROM $GGTABLESu WHERE Ttable='$View_TtableB'";
}

$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut = 30;  // 한 페이지당 보여줄 목록 게시물 수
$offset = isset($offset) ? $offset : 0;
?>

<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
    <tr>
        <td align=left colspan=2>
            (<b><?php echo  htmlspecialchars($View_TtableC) ?></b>) 수량 LIST
        </td>
    </tr>
    <tr>
        <td>
            <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                <tr>
                    <td align=left>
                        <script language="JavaScript">
                            function MM_jumpMenu(targ, selObj, restore) {
                                eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
                                if (restore) selObj.selectedIndex = 0;
                            }
                        </script>

                        <select onChange="MM_jumpMenu('parent', this, 0)">
                        <?php
$Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLESu WHERE BigNo='0'");
$Cate_rows = mysqli_num_rows($Cate_result);
if ($Cate_rows) {
    while ($Cate_row = mysqli_fetch_array($Cate_result)) {
        $optionValue = htmlspecialchars($_SERVER['PHP_SELF']) . "?Cate=" . $Cate_row['no'] . "&Ttable=" . htmlspecialchars($Ttable);
        $isSelected = ($Cate == $Cate_row['no']) ? "style='background-color:#429EB2; color:#FFFFFF;' selected" : "";
?>
        <option value='<?php echo $optionValue ?>' <?php echo $isSelected ?>><?php echo htmlspecialchars($Cate_row['title']) ?></option>
<?php
    }
} else {
?>
    <option value='<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>?Ttable=<?php echo htmlspecialchars($Ttable) ?>'>→ 전체자료보기</option>
<?php
}
?>

                        </select>
                    </td>
                </tr>
            </table>
        </td>
        <td align=right valign=bottom>
            <input type='button' onClick="javascript:popup=window.open('./SuCateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>', 'WebOffice_<?php echo  htmlspecialchars($PageCode) ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수량 입력하기 '>
        </td>
    </tr>
</table>

<!------------------------------------------- 리스트 시작 ----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
    <tr>
        <td align=center>등록NO</td>
        <td align=center>수량</td>
        <td align=center>관리기능</td>
    </tr>

<?php
$result = mysqli_query($db, "$Mlang_query ORDER BY no DESC LIMIT $offset,$listcut");
$rows = mysqli_num_rows($result);
if ($rows) {
    while ($row = mysqli_fetch_array($result)) {
?>
<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo  htmlspecialchars($row['no']) ?></font></td>
<td>&nbsp;&nbsp;<font color=white><?php echo  htmlspecialchars($row['title']) ?></font>&nbsp;&nbsp;</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./SuCateAdmin.php?mode=form&code=modify&no=<?php echo  htmlspecialchars($row['no']) ?>&Ttable=<?php echo  htmlspecialchars($Ttable) ?>', 'WebOffice_<?php echo  htmlspecialchars($PageCode) ?>Modify','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?php echo  htmlspecialchars($row['no']) ?>');" value=' 삭제 '>
</td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan=10><p align=center><br><br>등록된 자료가 없습니다.</p></td></tr>";
}
?>
</table>

<p align='center'>

<?php
if ($rows) {

    $mlang_pagego = "table=$table&cate=$cate&$title_search=$title_search"; // 필드 속성들 전달값

    $pagecut = 7;  // 한 장당 보여줄 페이지 수
    $one_bbs = $listcut * $pagecut;  // 한 장당 실을 수 있는 목록(게시물) 수
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  // 각 장에 처음 페이지의 $offset값
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;  // 마지막 장의 첫 페이지의 $offset값
    $start_page = intval($start_offset / $listcut) + 1;  // 각 장에 처음 페이지의 값
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);  // 마지막 장의 끝 페이지

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;

        if ($offset != $newoffset) {
            echo "&nbsp;<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }

        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }
    echo "총목록갯수: $end_page 개";

}

mysqli_close($db);
?>
</p>
<!------------------------------------------- 리스트 끝 ----------------------------------------->
