<?php
// include "../../db.php";
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';  // 절대 경로로 변경

if (!isset($db)) {
    die("Database connection not established.");
}

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$TIO_CODE = "cadarok";
$table = "MlangPrintAuto_{$TIO_CODE}";

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$no = isset($_GET['no']) ? intval($_GET['no']) : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$RadOne = isset($_GET['RadOne']) ? $_GET['RadOne'] : '';
$myListTreeSelect = isset($_GET['myListTreeSelect']) ? $_GET['myListTreeSelect'] : '';
$myList = isset($_GET['myList']) ? $_GET['myList'] : '';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM $table WHERE no=?");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo ("<script language='javascript'>
    window.alert('테이블: " . htmlspecialchars($table) . " - $no 번 데이터가 삭제되었습니다');
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
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php include "ListSearchBox.php"; ?>
</td>

<?php
$db = new mysqli($host, $user, $password, $dataname);
if ($search == "yes") {
    $Mlang_query = "SELECT * FROM $table WHERE style=? AND Section=? AND TreeSelect=?";
    $stmt = $db->prepare($Mlang_query);
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }
    $stmt->bind_param("sss", $RadOne, $myListTreeSelect, $myList);
} else {
    $Mlang_query = "SELECT * FROM $table";
    $stmt = $db->prepare($Mlang_query);
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }
}

$stmt->execute();
$result = $stmt->get_result();
$recordsu = $result->num_rows;
$total = $recordsu;

$listcut = 15;
if (!isset($offset)) $offset = 0;

?>

<td align=right>
<input type='button' onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>&TreeSelect=ok', '<?php echo  htmlspecialchars($table) ?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 카테고리 관리 '>
<input type='button' onClick="javascript:window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=IncForm', '<?php echo  htmlspecialchars($table) ?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=' 텍스트/이미지 관리 '>
<input type='button' onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 새 데이터 입력 '>
<BR><BR>
총 데이터 수 - <font style='color:blue;'><b><?php echo  htmlspecialchars($total) ?></b></font>&nbsp;개&nbsp;&nbsp;
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작 ----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>품목</td>
<td align=center>규격</td>
<td align=center>종이재질</td>
<td align=center>수량</td>
<td align=center>금액</td>
<td align=center>관리</td>
</tr>

<?php
echo "RadOne: " . htmlspecialchars($RadOne) . " / myListTreeSelect: " . htmlspecialchars($myListTreeSelect);

$Mlang_query .= " ORDER BY no DESC LIMIT ?, ?";
$stmt = $db->prepare($Mlang_query);
if ($stmt === false) {
    die("Prepare failed: " . $db->error);
}
if ($search == "yes") {
    $stmt->bind_param("ssssi", $RadOne, $myListTreeSelect, $myList, $offset, $listcut);
} else {
    $stmt->bind_param("ii", $offset, $listcut);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;

if ($rows) {
    while ($row = $result->fetch_assoc()) {
        ?>

        <tr bgcolor='#575757'>
        <td align=center><font color=white><?php echo  htmlspecialchars($row['no']) ?></font></td>
        <td align=center><font color=white>
        <?php
        $result_FGTwo = $db->query("SELECT * FROM $GGTABLE WHERE no='{$row['style']}'");
        $row_FGTwo = $result_FGTwo->fetch_assoc();
        if ($row_FGTwo) {
            echo htmlspecialchars($row_FGTwo['title']);
        }
        ?>
        </font></td>
        <td align=center><font color=white>
        <?php
        $result_FGOne = $db->query("SELECT * FROM $GGTABLE WHERE no='{$row['Section']}'");
        $row_FGOne = $result_FGOne->fetch_assoc();
        if ($row_FGOne) {
            echo htmlspecialchars($row_FGOne['title']);
        }
        ?>
        </font></td>
        <td align=center><font color=white>
        <?php
        $result_FGFree = $db->query("SELECT * FROM $GGTABLE WHERE no='{$row['TreeSelect']}'");
        $row_FGFree = $result_FGFree->fetch_assoc();
        if ($row_FGFree) {
            echo htmlspecialchars($row_FGFree['title']);
        }
        ?>
        </font></td>
        <td align=center><font color=white><?php if ($row['quantity'] == "9999") { echo "기타"; } else { echo htmlspecialchars($row['quantity']) . "개"; } ?></font></td>
        <td align=center><font color=white>
        <?php $sum = number_format($row['money']); echo htmlspecialchars($sum) . "원"; ?>
        </font></td>
        <td align=center>
        <input type='button' onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&code=Modify&no=<?php echo  htmlspecialchars($row['no']) ?>&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
        <input type='button' onClick="javascript:WomanMember_Admin_Del('<?php echo  htmlspecialchars($row['no']) ?>');" value=' 삭제 '>
        </td>
        <tr>

        <?php
    }
} else {
    if ($search) {
        echo "<tr><td colspan=10><p align=center><BR><BR>검색 결과가 없습니다.</p></td></tr>";
    } else {
        echo "<tr><td colspan=10><p align=center><BR><BR>데이터가 없습니다.</p></td></tr>";
    }
}
?>

</table>

<p align='center'>
<?php
// Ensure that the variables are defined before using them
$cate = isset($_GET['cate']) ? $_GET['cate'] : '';  // Default to an empty string if not set
$title_search = isset($_GET['title_search']) ? $_GET['title_search'] : '';  // Default to an empty string if not set

if ($rows) {
    if ($search == "yes") {
        $mlang_pagego = "search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList";
    } else {
        $mlang_pagego = "cate=$cate&title_search=$title_search";
    }
}
    $pagecut = 7;
    $one_bbs = $listcut * $pagecut;
    $start_offset = intval($offset / $one_bbs) * $one_bbs;
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
    $start_page = intval($start_offset / $listcut) + 1;
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

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
    echo "총 페이지 수: " . htmlspecialchars($end_page);

$db->close();
?>
</p>
<!------------------------------------------- 리스트 끝 ----------------------------------------->
<?php
include "../down.php";
?>
