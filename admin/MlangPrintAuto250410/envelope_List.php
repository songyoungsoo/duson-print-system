<?php
include "../../db.php";

$TIO_CODE = "envelope";
$table = "MlangPrintAuto_{$TIO_CODE}";

if (isset($_GET['mode']) && $_GET['mode'] == "delete") {
    $no = intval($_GET['no']);
    $stmt = $db->prepare("DELETE FROM $table WHERE no = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }
    $stmt->bind_param('i', $no);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo ("<script language='javascript'>
    window.alert('테이블: " . htmlspecialchars($table) . " - $no 번 자료 삭제 완료');
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
        var str = '<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?no=' + no + '&mode=delete';
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
$db = new mysqli($host, $user, $password, $dataname);
$search = isset($_GET['search']) ? $_GET['search'] : '';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

if ($search == "yes") {
    $RadOne = $_GET['RadOne'];
    $myList = $_GET['myList'];
    $stmt = $db->prepare("SELECT * FROM $table WHERE style = ? AND Section = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }
    $stmt->bind_param('ss', $RadOne, $myList);
} else {
    $stmt = $db->prepare("SELECT * FROM $table");
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }
}

$stmt->execute();
$result = $stmt->get_result();
$recordsu = $result->num_rows;
$total = $stmt->affected_rows;

$listcut = 15;

?>

<td align="right">
<input type="button" onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>&TreeSelect=ok', '<?php echo  htmlspecialchars($table) ?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value="분류 보기">
<input type="button" onClick="javascript:window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=IncForm', '<?php echo  htmlspecialchars($table) ?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value="수정/삭제 하기">
<input type="button" onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value="새 자료 입력">
<br><br>
전체자료-<font style="color:blue;"><b><?php echo  htmlspecialchars($total) ?></b></font>개&nbsp;&nbsp;
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
<td align="center">디자인금액</td>
<td align="center">관리</td>
</tr>

<?php
$Mlang_query = "SELECT * FROM $table ORDER BY no DESC LIMIT ?, ?";
$stmt = $db->prepare($Mlang_query);
if ($stmt === false) {
    die("Prepare failed: " . $db->error);
}
$stmt->bind_param('ii', $offset, $listcut);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;

if ($rows) {
    while ($row = $result->fetch_assoc()) {
        ?>

        <tr bgcolor="#575757">
        <td align="center"><font color="white"><?php echo  htmlspecialchars($row['no']) ?></font></td>
        <td align="center"><font color="white">
        <?php
        $result_FGTwo = $db->query("SELECT * FROM $GGTABLE WHERE no = '{$row['style']}'");
        $row_FGTwo = $result_FGTwo->fetch_assoc();
        if ($row_FGTwo) {
            echo htmlspecialchars($row_FGTwo['title']);
        }
        ?>
        </font></td>
        <td align="center"><font color="white">
        <?php
        $result_FGOne = $db->query("SELECT * FROM $GGTABLE WHERE no = '{$row['Section']}'");
        $row_FGOne = $result_FGOne->fetch_assoc();
        if ($row_FGOne) {
            echo htmlspecialchars($row_FGOne['title']);
        }
        ?>
        </font></td>
        <td align="center"><font color="white">
        <?php
        switch ($row['POtype']) {
            case "1":
                echo "봉투1형";
                break;
            case "2":
                echo "봉투2형";
                break;
            case "3":
                echo "기타4형(특수)";
                break;
        }
        ?>
        </font></td>
        <td align="center"><font color="white"><?php echo  htmlspecialchars($row['quantity']) ?>개</font></td>
        <td align="center"><font color="white"><?php echo  number_format($row['money']) ?>원</font></td>
        <td align="center"><font color="white"><?php echo  number_format($row['DesignMoney']) ?>원</font></td>
        <td align="center">
        <input type="button" onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&code=Modify&no=<?php echo  htmlspecialchars($row['no']) ?>&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value="수정">
        <input type="button" onClick="javascript:WomanMember_Admin_Del('<?php echo  htmlspecialchars($row['no']) ?>');" value="삭제">
        </td>
        </tr>

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
    $mlang_pagego = $search == "yes" ? "search=yes&RadOne=" . urlencode($RadOne) . "&myList=" . urlencode($myList) : "";

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
    echo "총 페이지: " . htmlspecialchars($end_page) . " 페이지";
}

$db->close();
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php include "../down.php"; ?>
</body>
</html>
