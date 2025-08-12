<?php
include "../../db.php";

$TIO_CODE = "sticker";
$table = "MlangPrintAuto_{$TIO_CODE}";

$mode = $_GET['mode'] ?? null;
$no = $_GET['no'] ?? null;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$search = $_GET['search'] ?? null;
$RadOne = $_GET['RadOne'] ?? null;
$myListTreeSelect = $_GET['myListTreeSelect'] ?? null;
$myList = $_GET['myList'] ?? null;
$title_search = $_GET['title_search'] ?? null;
$cate = $_GET['cate'] ?? null;
$PHP_SELF = $_SERVER['PHP_SELF'];

$listcut = 15;

$db = new mysqli($host, $user, $password, $dataname);
if ($db->connect_error) {
    die("DB 연결 실패: " . $db->connect_error);
}

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM {$table} WHERE no = ?");
    if ($stmt) {
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $stmt->close();
    } else {
        die("Prepare failed: " . $db->error);
    }
    $db->close();

    echo ("<script language='javascript'>
    window.alert('테이블: {$table} - {$no} 번 데이터 삭제 완료');
    opener.parent.location.reload();
    window.self.close();
    </script>");
    exit;
}

$M123 = "..";
include "$M123/top.php";

$T_DirUrl = "../../MlangPrintAuto";
include "{$T_DirUrl}/ConDb.php";
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

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php include "ListSearchBox.php"; ?>
</td>
<td align=right>
<input type='button' onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate','width=600,height=650'); popup.focus();" value=' 구분 관리 '>
<input type='button' onClick="javascript:window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1','width=820,height=600');" value=' 가격/설명 관리 '>
<input type='button' onClick="javascript:popup=window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2','width=300,height=250'); popup.focus();" value=' 신 자료 입력 '>
<BR>전체자료수-<font style='color:blue;'><b>

<?php
// ✅ DB 연결 (한 번만 실행)
$db = new mysqli($host, $user, $password, $dataname);

// ❗ 연결 확인 필수
if ($db->connect_error) {
    die("DB 연결 실패: " . $db->connect_error);
}

// ✅ 기본 SELECT 쿼리 초기화
$Mlang_query = "";

// ✅ 검색 조건 유무에 따라 쿼리 분기
if ($search === "yes") {
    // 폼 또는 GET으로부터 데이터 안전하게 처리
    $RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
    $myList = $_GET['myList'] ?? $_POST['myList'] ?? '';

    // prepare 실행 전 점검
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE style = ? AND Section = ?");
    if (!$stmt) {
        die("Prepare 실패: " . $db->error);
    }

    $stmt->bind_param("ss", $RadOne, $myList);
    $stmt->execute();
    $result = $stmt->get_result();

    $Mlang_query = "SELECT * FROM {$table} WHERE style = '" . $db->real_escape_string($RadOne) . "' AND Section = '" . $db->real_escape_string($myList) . "'";
} else {
    $stmt = $db->prepare("SELECT * FROM {$table}");
    if (!$stmt) {
        die("Prepare 실패: " . $db->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $Mlang_query = "SELECT * FROM {$table}";
}

// ✅ 총 레코드 수 계산
$recordsu = $result->num_rows;
$total = $recordsu;
?>
</b></font>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록번호</td>
<td align=center>종이종류</td>
<td align=center>종이규격</td>
<td align=center>수량(옆)</td>
<td align=center>가격</td>
<td align=center>디자인비</td>
<td align=center>관리기능</td>
</tr>

<?php
$query = $Mlang_query . " ORDER BY NO DESC LIMIT $offset, $listcut";
$result = mysqli_query($db, $query);
if (!$result) {
    die("쿼리 실패: " . mysqli_error($db));
}

$rows = mysqli_num_rows($result);
if ($rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
?>
<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo  htmlspecialchars($row['no']) ?></font></td>
<td align=center><font color=white>
<?php
$result_FGTwo = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='{$row['style']}'");
$row_FGTwo = mysqli_fetch_assoc($result_FGTwo);
if ($row_FGTwo) echo $row_FGTwo['title'];
?>
</font></td>
<td align=center><font color=white>
<?php
$result_FGOne = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='{$row['Section']}'");
$row_FGOne = mysqli_fetch_assoc($result_FGOne);
if ($row_FGOne) echo $row_FGOne['title'];
?>
</font></td>
<td align=center><font color=white><?php echo  htmlspecialchars($row['quantity']) ?>개</font></td>
<td align=center><font color=white><?php echo  number_format($row['money']) ?>원</font></td>
<td align=center><font color=white><?php echo  number_format($row['DesignMoney']) ?>원</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value=' 삭제 '>
</td>
</tr>
<?php }} else {
    echo "<tr><td colspan='10' align='center'><br><br>등록 자료없음</td></tr>";
} ?>
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
            echo "&nbsp;<font style='font-weight:bold; color:green;'>($i)</font>&nbsp;";
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
<?php include "../down.php"; ?>
