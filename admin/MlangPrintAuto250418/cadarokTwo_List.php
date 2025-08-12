<?php
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';  // 절대 경로로 변경
$TIO_CODE = "cadarokTwo";
$table = "MlangPrintAuto_$TIO_CODE";

if (isset($_GET['mode']) && $_GET['mode'] == "delete") {
    $no = intval($_GET['no']);
    $stmt = $db->prepare("DELETE FROM $table WHERE no = ?");
    $stmt->bind_param('i', $no);
    $stmt->execute();
    $stmt->close();

    echo ("<script language='javascript'>
    window.alert('테이블: $table - $no 번 자료 삭제 완료');
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

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>관리 페이지</title>
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
    if (confirm(no + '번 자료를 정말 삭제하시겠습니까?\n\n한 번 삭제하면 복구할 수 없습니다.')) {
        var str = '<?php echo $PHP_SELF?>?no=' + no + '&mode=delete';
        var popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<body>
<table border="0" align="center" width="100%" cellpadding="8" cellspacing="3" class="coolBar">
<tr>
<td align="left">
<?php include "ListSearchBox.php"; ?>
</td>

<?php
$search = isset($_GET['search']) ? $_GET['search'] : "";
$RadOne = isset($_GET['RadOne']) ? $_GET['RadOne'] : "";
$myListTreeSelect = isset($_GET['myListTreeSelect']) ? $_GET['myListTreeSelect'] : "";
$myList = isset($_GET['myList']) ? $_GET['myList'] : "";
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

if ($search == "yes") { // 검색 모드일 때
    $Mlang_query = "SELECT * FROM $table WHERE style = ? AND Section = ? AND TreeSelect = ?";
    $stmt = $db->prepare($Mlang_query);
    $stmt->bind_param('sss', $RadOne, $myListTreeSelect, $myList);
} else { // 일반 모드일 때
    $Mlang_query = "SELECT * FROM $table";
    $stmt = $db->prepare($Mlang_query);
}
$stmt->execute();
$result = $stmt->get_result();
$recordsu = $result->num_rows;
$total = $recordsu;

$listcut = 15;  // 페이지당 게시물 수
?>

<td align="right">
<input type="button" onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=" 분류 보기 ">
<input type="button" onClick="javascript:window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=" 수정/삭제 하기 ">
<input type="button" onClick="javascript:popup=window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=" 새 자료 입력 ">
<br><br>
전체자료-<font style="color:blue;"><b><?php echo  $total ?></b></font>개&nbsp;&nbsp;
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
<tr>
<td align="center">번호</td>
<td align="center">스타일</td>
<td align="center">구분</td>
<td align="center">규격</td>
<td align="center">수량</td>
<td align="center">금액</td>
<td align="center">관리</td>
</tr>

<?php
$Mlang_query .= " ORDER BY no DESC LIMIT ?, ?";
$stmt = $db->prepare($Mlang_query);
if ($search == "yes") {
    $stmt->bind_param('sssii', $RadOne, $myListTreeSelect, $myList, $offset, $listcut);
} else {
    $stmt->bind_param('ii', $offset, $listcut);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;

if ($rows) {
    while ($row = $result->fetch_assoc()) {
?>

<tr bgcolor="#575757">
<td align="center"><font color="white"><?php echo  $row['no'] ?></font></td>
<td align="center"><font color="white">
<?php 
$result_FGTwo = $db->query("SELECT * FROM $GGTABLE WHERE no = '{$row['style']}'");
$row_FGTwo = $result_FGTwo->fetch_assoc();
if ($row_FGTwo) {
    echo $row_FGTwo['title'];
}
?>
</font></td>
<td align="center"><font color="white">
<?php 
$result_FGOne = $db->query("SELECT * FROM $GGTABLE WHERE no = '{$row['Section']}'");
$row_FGOne = $result_FGOne->fetch_assoc();
if ($row_FGOne) {
    echo $row_FGOne['title'];
}
?>
</font></td> 
<td align="center"><font color="white">
<?php 
$result_FGFree = $db->query("SELECT * FROM $GGTABLE WHERE no = '{$row['TreeSelect']}'");
$row_FGFree = $result_FGFree->fetch_assoc();
if ($row_FGFree) {
    echo $row_FGFree['title'];
}
?>
</font></td> 
<td align="center"><font color="white"><?php echo  $row['quantity'] == "9999" ? "특수" : $row['quantity'] . "개" ?></font></td>
<td align="center"><font color="white"><?php echo  number_format($row['money']) ?>원</font></td>
<td align="center">
<input type="button" onClick="javascript:popup=window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=" 수정 ">
<input type="button" onClick="javascript:WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value=" 삭제 ">
</td>
<tr>

<?php
    }
} else {
    if ($search) {
        echo "<tr><td colspan='10'><p align='center'><br><br>검색 결과가 없습니다.</p></td></tr>";
    } else {
        echo "<tr><td colspan='10'><p align='center'><br><br>등록된 자료가 없습니다.</p></td></tr>";
    }
}
?>

</table>

<p align="center">
<?php
if ($rows) {
    $mlang_pagego = $search == "yes" ? "search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList" : "cate=$cate&title_search=$title_search";

    $pagecut = 7;  // 한 페이지당 표시할 페이지 수
    $one_bbs = $listcut * $pagecut;  // 한 페이지당 최대 게시물 수
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  // 시작 페이지 오프셋
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;  // 종료 페이지 오프셋
    $start_page = intval($start_offset / $listcut) + 1;  // 시작 페이지 번호
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
    echo "총 페이지: $end_page 페이지";
}

$db->close();
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php include "../down.php"; ?>
</body>
</html>
