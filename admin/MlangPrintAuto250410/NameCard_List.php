<?php
include "../../db.php";
$TIO_CODE = "namecard";
$table = "MlangPrintAuto_" . $TIO_CODE;
$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
$cate = isset($cate) ? $cate : '';  // cate 변수를 기본값으로 초기화
$title_search = isset($title_search) ? $title_search : '';  // title_search 변수를 기본값으로 초기화
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_POST['search']) ? $_POST['search'] : '');
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$no = isset($_GET['no']) ? intval($_GET['no']) : (isset($_POST['no']) ? intval($_POST['no']) : 0);
$GGTABLE = isset($GGTABLE) ? $GGTABLE : '';
$Ttable = isset($_GET['Ttable']) ? $_GET['Ttable'] : (isset($_POST['Ttable']) ? $_POST['Ttable'] : '');
$mlang_pagego = isset($mlang_pagego) ? $mlang_pagego : '';
$TreeSelect = isset($_GET['TreeSelect']) ? $_GET['TreeSelect'] : ''; 

// Delete operation
if ($mode == "delete") {
    $db=new mysqli($host,$user,$password,$dataname);
    $stmt = $db->prepare("DELETE FROM $table WHERE no = ?");
    $stmt->bind_param('s', $no);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo ("<script>
        alert('테이블명: $table - $no 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
    </script>");
    exit;
}

// Continue with the rest of the PHP script
$M123 = "..";
include "$M123/top.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";
?>

<head>
<script>
function clearField(field) {
    if (field.value === field.defaultValue) {
        field.value = "";
    }
}

function checkField(field) {
    if (!field.value) {
        field.value = field.defaultValue;
    }
}

function WomanMember_Admin_Del(no) {
    if (confirm(`${no}번 자료을 삭제 처리 하시겠습니까?\n\n한번 삭제한 자료는 복구되지 않으니 신중히 진행해주세요.`)) {
        const str = `<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>?no=${no}&mode=delete`;
        const popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
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
$db=new mysqli($host,$user,$password,$dataname);
$db->set_charset('utf8');

if ($search == "yes") {
    $Mlang_query = "SELECT * FROM $table WHERE style = ? AND TreeSelect = ? AND Section = ?";
    $stmt = $db->prepare($Mlang_query);

    if (!$stmt) {
        die("쿼리 준비 실패: " . $db->error);
    }

    $stmt->bind_param("sss", $RadOne, $myListTreeSelect, $myList);
} else {
    $Mlang_query = "SELECT * FROM $table";
    $stmt = $db->prepare($Mlang_query);

    if (!$stmt) {
        die("쿼리 준비 실패: " . $db->error);
    }
}

if (!$stmt->execute()) {
    die("쿼리 실행 실패: " . $stmt->error);
}

$query = $stmt->get_result();
$recordsu = $query ? $query->num_rows : 0;
$total = $recordsu;

$listcut = 15;
$offset = isset($offset) ? $offset : 0;
?>

<td align="right">
    <input type="button" onClick="window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate','width=600,height=650,top=0,left=0');" value="구분 관리">
    <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1','width=820,height=600,top=0,left=0');" value="가격/설명 관리">
    <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2','width=300,height=250,top=0,left=0');" value="신 자료 입력">
    <br><br>
    전체자료수 - <font style="color:blue;"><b><?php echo  $total ?></b></font> 개
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
<tr>
    <td align="center">등록번호</td>
    <td align="center">종이</td>
    <td align="center">종이종류</td>
    <td align="center">종이규격</td>
    <td align="center">인쇄면</td>
    <td align="center">수량</td>
    <td align="center">가격</td>
    <td align="center">디자인비</td>
    <td align="center">관리기능</td>
</tr>

<?php
    $db=new mysqli($host,$user,$password,$dataname);
$result = $db->query("$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
$rows = $result->num_rows;

if ($rows) {
    while ($row = $result->fetch_assoc()) {
?>

<tr bgcolor="#575757">
    <td align="center"><font color="white"><?php echo  htmlspecialchars($row['no']) ?></font></td>
    <td align="center"><font color="white">
        <?php
        $style_result = $db->query("SELECT title FROM $GGTABLE WHERE no = '{$row['style']}'");
        if ($style_row = $style_result->fetch_assoc()) {
            echo htmlspecialchars($style_row['title']);
        }
        ?>
    </font></td>

    <td align="center"><font color="white">
        <?php
        $section_result = $db->query("SELECT title FROM $GGTABLE WHERE no = '{$row['Section']}'");
        if ($section_row = $section_result->fetch_assoc()) {
            echo htmlspecialchars($section_row['title']);
        }
        ?>
    </font></td>
    <td align="center"><font color="white">
        <?php echo  $row['POtype'] == "1" ? "단면" : "양면" ?>
    </font></td>
    <td align="center"><font color="white"><?php echo  htmlspecialchars($row['quantity']) ?></font></td>
    <td align="center"><font color="white">
        <?php $sum = number_format($row['money']); echo "$sum 원"; ?>
    </font></td>
    <td align="center"><font color="white">
        <?php $sumr = number_format($row['DesignMoney']); echo "$sumr 원"; ?>
    </font></td>
    <td align="center">
        <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250,top=0,left=0');" value="수정">
        <input type="button" onClick="WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value="삭제">
    </td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='10' align='center'><p><br>등록 자료없음</p></td></tr>";
}
?>
</table>

<p align="center">
<?php
$pagecut = 7;
$one_bbs = $listcut * $pagecut;
$start_offset = intval($offset / $one_bbs) * $one_bbs;
$end_offset = intval($recordsu / $one_bbs) * $one_bbs;
$start_page = intval($start_offset / $listcut) + 1;
$end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

if ($start_offset != 0) {
    echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=" . ($start_offset - $one_bbs) . "&$mlang_pagego'>...[이전]</a>&nbsp;";
}

for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
    $newoffset = ($i - 1) * $listcut;
    if ($offset != $newoffset) {
        echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
    } else {
        echo "<font style='font:bold; color:green;'>($i)</font>&nbsp;";
    }
    if ($i == $end_page) break;
}

if ($start_offset != $end_offset) {
    echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=" . ($start_offset + $one_bbs) . "&$mlang_pagego'>[다음]...</a>";
}
echo "총목록갯수: $end_page 개";

$db->close();
?>
</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php include "../down.php"; ?>
