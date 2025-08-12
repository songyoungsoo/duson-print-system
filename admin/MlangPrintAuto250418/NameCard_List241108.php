<?php
include "../../db.php"; // $db 변수를 초기화합니다.

$TIO_CODE = "NameCard";
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
    window.alert('테이블명: $table - $no 번 자료 삭제 완료');
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
        str = '<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?no=' + no + '&mode=delete';
        popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>

</head>

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

$listcut = 15;  // 한 페이지당 보여줄 목록 게시물 수

?>

<td align="right">
<input type="button" onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>&TreeSelect=ok', '<?php echo  htmlspecialchars($table) ?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value="구분 관리">
<input type="button" onClick="javascript:window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=IncForm', '<?php echo  htmlspecialchars($table) ?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value="가격/설명 관리">
<input type="button" onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value="신 자료 입력">
<br><br>
전체자료수-<font style="color:blue;"><b><?php echo  htmlspecialchars($total) ?></b></font>개&nbsp;&nbsp;
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
<tr>
<td align="center">등록번호</td>
<td align="center">명함종류</td>
<td align="center">명함재질</td>
<td align="center">인쇄면</td>
<td align="center">수량</td>
<td align="center">가격</td>
<td align="center">디자인비</td>
<td align="center">관리기능</td>
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
        if ($row['POtype'] == "1") {
            echo "단면";
        } elseif ($row['POtype'] == "2") {
            echo "양면";
        }
        ?>
        </font></td>
        <td align="center"><font color="white"><?php echo  htmlspecialchars($row['quantity']) ?>매</font></td>
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
        echo "<tr><td colspan='10'><p align='center'><br><br>관련 검색 자료없음</p></td></tr>";
    } else {
        echo "<tr><td colspan='10'><p align='center'><br><br>등록 자료없음</p></td></tr>";
    }
}
?>

</table>



<p align="center">

<?php
if ($rows) {
    $mlang_pagego = $search == "yes" ? "search=$search&RadOne=" . urlencode($RadOne) . "&myList=" . urlencode($myList) : "";

    $pagecut = 7;  // 한 장당 보여줄 페이지수
    $one_bbs = $listcut * $pagecut;  // 한 장당 실을 수 있는 목록(게시물)수
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  // 각 장에 처음 페이지의 $offset값
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;  // 마지막 장의 첫페이지의 $offset값
    $start_page = intval($start_offset / $listcut) + 1; // 각 장에 처음 페이지의 값
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut); 
    // 마지막 장의 끝 페이지
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
    echo "총목록갯수: " . htmlspecialchars($end_page) . " 개";
}

$db->close(); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php include "../down.php"; ?>
