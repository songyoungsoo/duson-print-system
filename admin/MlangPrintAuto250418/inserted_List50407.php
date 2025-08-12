<?php
include "../../db.php";
$TIO_CODE = "inserted";
$table = "MlangPrintAuto_{$TIO_CODE}";

$mode = $_GET['mode'] ?? null;
$no = $_GET['no'] ?? null;
$offset = $_GET['offset'] ?? 0;
$search = $_GET['search'] ?? null;
$RadOne = $_GET['RadOne'] ?? null;
$myListTreeSelect = $_GET['myListTreeSelect'] ?? null;
$myList = $_GET['myList'] ?? null;
$title_search = $_GET['title_search'] ?? null;
$cate = $_GET['cate'] ?? null;
$PHP_SELF = $_SERVER['PHP_SELF'];

if ($mode == "delete") {
    $stmt = $mysqli->prepare("DELETE FROM $table WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();

    echo ("<script language=javascript>
    window.alert('테이블명: $table - $no 번 자료 삭제 완료');
    opener.parent.location.reload();
    window.self.close();
    </script>");
    exit;
}

$M123 = "..";
include "$M123/top.php";

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
  if (confirm(no + '번 자료을 삭제 처리 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
    let str = '<?php echo $PHP_SELF?>?no=' + no + '&mode=delete';
    let popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
    popup.location.href = str;
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
<td align=right>
<input type='button' onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo $TIO_CODE?>&TreeSelect=ok', '<?php echo $table?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 구분 관리 '>
<input type='button' onClick="javascript:window.open('<?php echo $TIO_CODE?>_admin.php?mode=IncForm', '<?php echo $table?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=' 가격/설명 관리 '>
<input type='button' onClick="javascript:popup=window.open('<?php echo $TIO_CODE?>_admin.php?mode=form&Ttable=<?php echo $TIO_CODE?>', '<?php echo $table?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 신 자료 입력 '>
<BR><BR>
전체자료수-<font style='color:blue;'><b>
<?php
$mysqli = new mysqli($host, $user, $password, $dataname);
if ($search == "yes") {
    $stmt = $mysqli->prepare("SELECT * FROM $table WHERE style = ? AND TreeSelect = ? AND Section = ?");
    $stmt->bind_param("sss", $RadOne, $myListTreeSelect, $myList);
} else {
    $stmt = $mysqli->prepare("SELECT * FROM $table");
}
$stmt->execute();
$result = $stmt->get_result();
$recordsu = $result->num_rows;
echo $recordsu;
?>
</b></font>&nbsp;개&nbsp;&nbsp;
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록번호</td>
<td align=center>인쇄색상</td>
<td align=center>종이종류</td>
<td align=center>종이규격</td>
<td align=center>인쇄면</td>
<td align=center>수량(옆)</td>
<td align=center>가격</td>
<td align=center>디자인비</td>
<td align=center>관리기능</td>
</tr>

<?php
$listcut = 15;
$Mlang_query = $stmt;
$result = $Mlang_query->get_result();

if ($search == "yes") {
    $stmt = $mysqli->prepare("SELECT * FROM $table WHERE style = ? AND TreeSelect = ? AND Section = ? LIMIT ?, ?");
    $stmt->bind_param("ssiii", $RadOne, $myListTreeSelect, $myList, $offset, $listcut);
} else {
    $stmt = $mysqli->prepare("SELECT * FROM $table LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $listcut);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;
if ($search == "yes") {
    $mlang_pagego = "search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList";
} else {
    $mlang_pagego = "cate=$cate&title_search=$title_search";
}
$recordsu = $result->num_rows;
$rows = $result->num_rows;

if ($rows) {
    while ($row = $result->fetch_assoc()) {
?>
<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo $row['no']?></font></td>
<td align=center><font color=white><?php echo getTitle($row['style']); ?></font></td>
<td align=center><font color=white><?php echo getTitle($row['TreeSelect']); ?></font></td>
<td align=center><font color=white><?php echo getTitle($row['Section']); ?></font></td>
<td align=center><font color=white><?php echo ($row['POtype'] == "1" ? "단면" : "양면")?></font></td>
<td align=center><font color=white><?php echo $row['quantity']?>연(<?php echo $row['quantityTwo']?>장)</font></td>
<td align=center><font color=white><?php echo number_format($row['money'])?>원</font></td>
<td align=center><font color=white><?php echo number_format($row['DesignMoney'])?>원</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?php echo $TIO_CODE?>_admin.php?mode=form&code=Modify&no=<?php echo $row['no']?>&Ttable=<?php echo $TIO_CODE?>', '<?php echo $table?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WomanMember_Admin_Del('<?php echo $row['no']?>');" value=' 삭제 '>
</td>
<tr>
<?php
    }
} else {
    echo "<tr><td colspan=10><p align=center><BR><BR>" . ($search ? "관련 검색 자료없음" : "등록 자료없음") . "</p></td></tr>";
}
?>
</table>

<p align='center'>
<?php
if ($rows) {
    $mlang_pagego = ($search == "yes") ? "search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList" : "cate=$cate&title_search=$title_search";
    $pagecut = 7;
    $one_bbs = $listcut * $pagecut;
    $start_offset = intval($offset / $one_bbs) * $one_bbs;
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
    $start_page = intval($start_offset / $listcut) + 1;
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
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
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }
    echo " 총목록갯수: $end_page 개";
}
?>
</p>

<?php
include "../down.php";

function getTitle($no) {
    global $mysqli, $GGTABLE;
    $stmt = $mysqli->prepare("SELECT title FROM $GGTABLE WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->bind_result($title);
    $stmt->fetch();
    $stmt->close();
    return $title ?? "";
}
?>
