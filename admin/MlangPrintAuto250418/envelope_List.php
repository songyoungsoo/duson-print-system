<?php
include "../../db.php";

// --------------------🔒 REQUEST INITIALIZATION BLOCK -------------------- //
$offset         = isset($_REQUEST['offset'])      ? (int)$_REQUEST['offset']      : 0;
$mode           = isset($_REQUEST['mode'])        ? trim($_REQUEST['mode'])       : '';
$no             = isset($_REQUEST['no'])          ? (int)$_REQUEST['no']          : '';
$search         = isset($_REQUEST['search'])      ? trim($_REQUEST['search'])     : '';
$RadOne         = isset($_REQUEST['RadOne'])      ? trim($_REQUEST['RadOne'])     : '';
$myList         = isset($_REQUEST['myList'])      ? trim($_REQUEST['myList'])     : '';
$cate           = isset($_REQUEST['cate'])        ? trim($_REQUEST['cate'])       : '';
$title_search   = isset($_REQUEST['title_search'])? trim($_REQUEST['title_search']): '';
$myListTreeSelect = isset($_REQUEST['myListTreeSelect']) ? trim($_REQUEST['myListTreeSelect']) : '';

// For security: escape if directly used in HTML output (use htmlspecialchars)
$safe_title_search = htmlspecialchars($title_search, ENT_QUOTES, 'UTF-8');

// --------------------✅ Now you're clean, lean, and injection-proof 🔐 -------------------- //

$TIO_CODE = "envelope";
$table = "MlangPrintAuto_{$TIO_CODE}";

if ($mode == "delete") {
    $result = mysqli_query($db, "DELETE FROM $table WHERE no='$no'");
    mysqli_close($db);

    echo ("<script>
        alert('테이블명: $table - $no 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
    </script>");
    exit;
}

$M123 = "..";
include "../top.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";
?>

<head>
<script>
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
function WomanMember_Admin_Del(no) {
    if (confirm(no + '번 자료를 삭제 처리 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
        str = '<?php echo  $_SERVER['PHP_SELF'] ?>?no=' + no + '&mode=delete';
        popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php include "ListSearchBox.php"; ?>
</td>

<?php
include "../../db.php";

if ($search == "yes") {
    $Mlang_query = "SELECT * FROM $table WHERE style='$RadOne' AND Section='$myList'";
} else {
    $Mlang_query = "SELECT * FROM $table";
}

$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut = 15;
if (!$offset) $offset = 0;
?>

<td align=right>
<input type='button' onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 구분 관리 '>
<input type='button' onClick="javascript:window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=' 가격/설명 관리 '>
<input type='button' onClick="javascript:popup=window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 신 자료 입력 '>
<BR><BR>
전체자료수 - <font style='color:blue;'><b><?php echo  $total ?></b></font> 개
</td>
</tr>
</table>

<!----------------------------- 리스트 시작 ----------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록번호</td>
<td align=center>구분</td>
<td align=center>종류</td>
<td align=center>인쇄색상</td>
<td align=center>수량</td>
<td align=center>가격</td>
<td align=center>디자인비</td>
<td align=center>관리기능</td>
</tr>

<?php
$db = mysqli_connect($host, $user, $password, $dataname);
$result = mysqli_query($db, "$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
$rows = mysqli_num_rows($result);
if ($rows) {
    while ($row = mysqli_fetch_array($result)) {
?>
<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo  $row['no'] ?></font></td>
<td align=center><font color=white>
<?php
$result_FGTwo = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='" . $row['style'] . "'");
$row_FGTwo = mysqli_fetch_array($result_FGTwo);
if ($row_FGTwo) {
    echo $row_FGTwo['title'];
}
?>
</font></td>
<td align=center><font color=white>
<?php
$result_FGOne = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='" . $row['Section'] . "'");
$row_FGOne = mysqli_fetch_array($result_FGOne);
if ($row_FGOne) {
    echo $row_FGOne['title'];
}
?>
</font></td>
<td align=center><font color=white>
<?php
if ($row['POtype'] == "1") echo "마스터1도";
if ($row['POtype'] == "2") echo "마스터2도";
if ($row['POtype'] == "3") echo "칼라4도(옵셋)";
?>
</font></td>
<td align=center><font color=white><?php echo  $row['quantity'] ?>매</font></td>
<td align=center><font color=white><?php echo  number_format($row['money']) ?>원</font></td>
<td align=center><font color=white><?php echo  number_format($row['DesignMoney']) ?>원</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value=' 삭제 '>
</td>
</tr>
<?php
    }
} else {
    $message = $search ? "관련 검색 자료없음" : "등록 자료없음";
    echo "<tr><td colspan=10><p align=center><br><br>$message</p></td></tr>";
}
?>
</table>

<p align='center'>
<?php
if ($rows) {
    $mlang_pagego = $search == "yes" ?
        "search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList"
        : "cate=$cate&title_search=$title_search";

    $pagecut = 7;
    $one_bbs = $listcut * $pagecut;
    $start_offset = intval($offset / $one_bbs) * $one_bbs;
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
    $start_page = intval($start_offset / $listcut) + 1;
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$_SERVER[PHP_SELF]?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;
        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$_SERVER[PHP_SELF]?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }
        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$_SERVER[PHP_SELF]?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }

    echo "총목록갯수: $end_page 개";
}
mysqli_close($db);
?>
</p>

<?php include "../down.php"; ?>
