<?php
// 경고 메시지와 페이지 이동
echo ("<script>
window.alert('수동견적은 프로그램 3.0 이상에서만 지원합니다.\\n\\n02-2264-7118로 별도로 문의해 주시기 바랍니다.');
history.go(-1);
</script>");
exit;

// 변수 초기화 및 안전한 방법으로 접근
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$no = isset($_GET['no']) ? intval($_GET['no']) : 0; // 숫자로 처리

if ($mode == "view") {
    include "../title.php";
    include "../../db.php";
    
    $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto_OfferOrder WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $BBAdminSelect = $row['AdminSelect'];
            ?>
            <script>
            window.resizeTo(680, 730);
            </script>

            <style>
            .td1 { font-family: 굴림; font-size: 9pt; color: #FFFFFF; font-weight: bold; line-height: normal; }
            .td2 { font-family: 굴림; font-size: 9pt; color: #008080; font-weight: none; line-height: 130%; }
            </style>

            <table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='#FFFFFF'>
            <tr>
            <td bgcolor='#FFFFFF' class='td2'>
                <?php echo  htmlspecialchars($row['cont_2']); ?>
            </td>
            </tr>
            </table>

            <p align=center>
                <input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
            </p>
            <?php
        }
    } else {
        echo "<p align=center><b>등록 자료가 없음.</b></p>";
    }
    $stmt->close();
    $db->close();

    if ($BBAdminSelect == "no") {
        include "../../db.php";
        $updateStmt = $db->prepare("UPDATE MlangOrder_PrintAuto_OfferOrder SET AdminSelect = 'yes' WHERE no = ?");
        $updateStmt->bind_param("i", $no);
        if (!$updateStmt->execute()) {
            echo "<script>
                window.alert('DB 접속 에러입니다!');
                history.go(-1);
            </script>";
            exit;
        } else {
            echo "<script>
                opener.parent.location.reload();
            </script>";
            exit;
        }
        $updateStmt->close();
        $db->close();
    }
    exit;
}

// delete 모드 처리
if ($mode == "delete") {
    include "../../db.php";
    $deleteStmt = $db->prepare("DELETE FROM MlangOrder_PrintAuto_OfferOrder WHERE no = ?");
    $deleteStmt->bind_param("i", $no);
    $deleteStmt->execute();
    $deleteStmt->close();
    $db->close();

    echo "<script>
        alert('\\n정보를 정상적으로 삭제하였습니다.\\n');
        opener.parent.location.reload();
        window.self.close();
    </script>";
    exit;
}
?>

<?php
$M123 = "..";
include "../top.php";
?>

<head>
<script>
function Member_Admin_Del(no) {
    if (confirm(no + '번 의 상담 자료를 삭제 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
        var str = '<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>?no=' + no + '&mode=delete';
        popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=12>
<font color=red>*</font> 관리자가 자료를 들여다 본 자료는 확인으로 자동으로 갱신됩니다.<br>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록번호</td>
<td align=center>이름</td>
<td align=center>접수Time</td>
<td align=center>확인여부</td>
<td align=center>자세한정보보기</td>
<td align=center>관리</td>
</tr>

<?php
include "../../db.php";
$table = "MlangOrder_PrintAuto_OfferOrder";
$listcut = 12;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$queryStmt = $db->prepare("SELECT * FROM $table ORDER BY no DESC LIMIT ?, ?");
$queryStmt->bind_param("ii", $offset, $listcut);
$queryStmt->execute();
$result = $queryStmt->get_result();

while ($row = $result->fetch_assoc()) {
    ?>
    <tr bgcolor='#575757'>
    <td align=center><font color=white><?php echo  htmlspecialchars($row['no']) ?></font></td>
    <td align=center><font color=white><?php echo  htmlspecialchars($row['name']) ?></font></td>
    <td align=center><font color=white><?php echo  htmlspecialchars($row['date']) ?></font></td>
    <td align=center>
        <?php if ($row['AdminSelect'] == "no") {
            echo "<b><font color=red>미확인</font></b>";
        } else {
            echo "<font color=white>확인</font>";
        } ?>
    </td>
    <td align=center>
        <input type='button' onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>?mode=view&no=<?php echo  $row['no'] ?>', 'MlangOrder_PrintAuto_OfferOrder','width=600,height=430,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 자세한정보보기 '>
    </td>
    <td align=center>
        <input type='button' onClick="javascript:Member_Admin_Del('<?php echo  $row['no'] ?>');" value=' 삭제 '>
    </td>
    </tr>
    <?php
}
$queryStmt->close();
$db->close();
?>

</table>

<p align='center'>
<?php
if ($rows) {
    // 필드 속성들 전달값 설정
    $mlang_pagego = "cate=" . urlencode($cate) . "&title_search=" . urlencode($title_search);

    $pagecut = 7; // 한 장당 보여줄 페이지 수
    $one_bbs = $listcut * $pagecut; // 한 장당 실을 수 있는 목록(게시물) 수
    $start_offset = intval($offset / $one_bbs) * $one_bbs; // 각 장에 처음 페이지의 $offset 값
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs; // 마지막 장의 첫 페이지의 $offset 값
    $start_page = intval($start_offset / $listcut) + 1; // 각 장에 처음 페이지의 값
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut); // 마지막 장의 끝 페이지

    // 페이지 이동 링크 생성
    $self_url = htmlspecialchars($_SERVER['PHP_SELF']);
    
    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$self_url?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;

        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$self_url?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<span style='font-weight: bold; color: green;'>($i)</span>&nbsp;";
        }

        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$self_url?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }
    echo "총 목록 갯수: $end_page 개";
}

// 데이터베이스 연결 종료
$db->close();
?> 

<!------------------------------------------- 리스트 끝 ----------------------------------------->

<?php
include "../down.php";
?>