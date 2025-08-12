<?php
include "../../db.php";
$TIO_CODE = "LittlePrint";
$table = "MlangPrintAuto_{$TIO_CODE}";
$GGTABLE = "MlangPrintAuto_{$TIO_CODE}_Cate";

$mode             = isset($_REQUEST['mode'])             ? trim($_REQUEST['mode'])             : '';
$no               = isset($_REQUEST['no'])               ? (int)$_REQUEST['no']                : 0;
$search           = isset($_REQUEST['search'])           ? trim($_REQUEST['search'])           : '';
$RadOne           = isset($_REQUEST['RadOne'])           ? trim($_REQUEST['RadOne'])           : '';
$myListTreeSelect = isset($_REQUEST['myListTreeSelect']) ? trim($_REQUEST['myListTreeSelect']) : '';
$myList           = isset($_REQUEST['myList'])           ? trim($_REQUEST['myList'])           : '';
$offset           = isset($_REQUEST['offset'])           ? (int)$_REQUEST['offset']            : 0;
$cate             = isset($_REQUEST['cate'])             ? (int)$_REQUEST['cate']              : 0;
$title_search     = isset($_REQUEST['title_search'])     ? trim($_REQUEST['title_search'])     : '';

if ($mysqli->connect_error) {
    die("DB 연결 실패: " . $mysqli->connect_error);
}
// 삭제 모드 처리
include "../../db.php";
$db = new mysqli($host, $user, $password, $dataname);
if (isset($mode) && $mode === "delete" && isset($no)) {
    $stmt = $db->prepare("DELETE FROM {$table} WHERE no = ?");
    $stmt->bind_param("i", $no);

    if ($stmt->execute()) {
        echo ("<script language='javascript'>
            window.alert('테이블명: {$table} - {$no} 번 자료 삭제 완료');
            opener.parent.location.reload();
            window.self.close();
        </script>");
    } else {
        echo "<script>alert('삭제 실패.');</script>";
    }
    $stmt->close();
    $db->close();
    exit;
}

$M123 = "..";
include "../top.php";

$T_DirUrl = "../../MlangPrintAuto";
include "{$T_DirUrl}/ConDb.php";
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
            if (confirm(no + '번 자료를 삭제 처리 하시겠습니까? 삭제 후에는 복구할 수 없습니다!')) {
                const str = '<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>?no=' + no + '&mode=delete';
                const popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50");
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
                include "../../db.php";
                $db = new mysqli($host, $user, $password, $dataname);
        $Mlang_query = "SELECT * FROM {$table}";

        // 검색 모드일 때
        $total = 0; // 상단에 기본값 설정
        if ($search === "yes") {
            $sql = "SELECT * FROM {$table} WHERE style = ? AND TreeSelect = ? AND Section = ? ORDER BY no DESC LIMIT ?, ?";
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssssi", $RadOne, $myListTreeSelect, $myList, $offset, $listcut);
            }
        } else {
            $sql = "SELECT * FROM {$table} ORDER BY no DESC LIMIT ?, ?";
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $offset, $listcut);
            }
        }
        $query = mysqli_query($db, $Mlang_query);
        $recordsu = mysqli_num_rows($query);
        $total = mysqli_affected_rows($db);

        $listcut = 15;
        $offset = isset($offset) ? $offset : 0;
        ?>

        <td align="right">
            <input type="button" onClick="window.open('CateList.php?Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>&TreeSelect=ok', '<?php echo  htmlspecialchars($table) ?>_FormCate','width=600,height=650');" value=" 구분 관리 ">
            <input type="button" onClick="window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=IncForm', '<?php echo  htmlspecialchars($table) ?>_Form1','width=820,height=600');" value=" 가격/설명 관리 ">
            <input type="button" onClick="window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2','width=300,height=250');" value=" 신 자료 입력 ">
            <br><br>
            전체자료수 - <font style="color:blue;"><b><?php echo  $total ?></b></font> 개&nbsp;&nbsp;
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
    include "../../db.php";
    $db = new mysqli($host, $user, $password, $dataname);
    $stmt = $db->prepare("{$Mlang_query} ORDER BY no DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $listcut);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;

    // Pass the number of rows to the front-end
    echo "<script>console.log('Number of rows: {$rows}');</script>";

    if ($rows) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <tr bgcolor="#575757">
                <td align="center"><font color="white"><?php echo  htmlspecialchars($row['no']) ?></font></td>
                <td align="center"><font color="white">
                    <?php
                    $result_FGTwo = $db->query("SELECT title FROM {$GGTABLE} WHERE no = '{$row['style']}'");
                    if ($row_FGTwo = $result_FGTwo->fetch_assoc()) {
                        echo htmlspecialchars($row_FGTwo['title']);
                    }
                    ?>
                </font></td>
                <td align="center"><font color="white">
                    <?php
                    $result_FGFree = $db->query("SELECT title FROM {$GGTABLE} WHERE no = '{$row['TreeSelect']}'");
                    if ($row_FGFree = $result_FGFree->fetch_assoc()) {
                        echo htmlspecialchars($row_FGFree['title']);
                    }
                    ?>
                </font></td>
                <td align="center"><font color="white">
                    <?php
                    $result_FGOne = $db->query("SELECT title FROM {$GGTABLE} WHERE no = '{$row['Section']}'");
                    if ($row_FGOne = $result_FGOne->fetch_assoc()) {
                        echo htmlspecialchars($row_FGOne['title']);
                    }
                    ?>
                </font></td>
                <td align="center"><font color="white">
                    <?php echo  ($row['POtype'] === "1") ? "단면" : (($row['POtype'] === "2") ? "양면" : "") ?>
                </font></td>
                <td align="center"><font color="white"><?php echo  htmlspecialchars($row['quantity']) ?></font></td>
                <td align="center"><font color="white"><?php echo  number_format($row['money']) ?> 원</font></td>
                <td align="center"><font color="white"><?php echo  number_format($row['DesignMoney']) ?> 원</font></td>
                <td align="center">
                    <input type="button" onClick="window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2Modify','width=300,height=250');" value=" 수정 ">
                    <input type="button" onClick="WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value=" 삭제 ">
                </td>
            </tr>
            <?php
        }
    } else {
        if ($search) {
            echo "<tr><td colspan='10' align='center'><br><br>관련 검색 자료없음</td></tr>";
        } else {
            echo "<tr><td colspan='10' align='center'><br><br>등록 자료없음</td></tr>";
        }
    }
    ?>
</table>

<p align="center">

<?php
if (isset($rows) && $rows > 0) {
    $mlang_pagego = isset($search) && $search === "yes"
        ? "search=" . urlencode($search) . "&cate=" . urlencode($cate) . "&title_search=" . urlencode($title_search) . "&RadOne=" . urlencode($RadOne) . "&myListTreeSelect=" . urlencode($myListTreeSelect) . "&myList=" . urlencode($myList)
        : "cate=" . urlencode($cate) . "&title_search=" . urlencode($title_search);

    $pagecut = 7; // 한 장당 보여줄 페이지수
    $one_bbs = $listcut * $pagecut; // 한 장당 실을 수 있는 목록(게시물) 수
    $start_offset = intval($offset / $one_bbs) * $one_bbs; // 각 장에 처음 페이지의 offset 값
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs; // 마지막 장의 첫 페이지의 offset 값
    $start_page = intval($start_offset / $listcut) + 1; // 각 장에 처음 페이지의 값
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut); // 마지막 장의 끝 페이지

    // 이전 페이지 링크
    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset={$apoffset}&{$mlang_pagego}'>...[이전]</a>&nbsp;";
    }

    // 페이지 번호 링크 생성
    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;

        if ($offset != $newoffset) {
            echo "&nbsp;<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset={$newoffset}&{$mlang_pagego}'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }

        if ($i == $end_page) break;
    }

    // 다음 페이지 링크
    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset={$nextoffset}&{$mlang_pagego}'>[다음]...</a>";
    }

    echo "총목록갯수: {$end_page} 개";
}

mysqli_close($db);
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php
include "../down.php";
?>
