<?php
if ($mode == "view") {
    include "../title.php";
    include "../../db.php";

    $stmt = $db->prepare("SELECT * FROM MlangOrder WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;

    if ($rows) {
        while ($row = $result->fetch_assoc()) {
            $BBAdminSelect = $row["AdminSelect"];
?>

<style>
    .td1 {
        font-family: 굴림;
        font-size: 9pt;
        color: #FFFFFF;
        font-weight: bold;
        line-height: normal;
    }

    .td2 {
        font-family: 굴림;
        font-size: 9pt;
        color: #008080;
        font-weight: none;
        line-height: 130%;
    }
</style>

<BR>
<table border=0 align=center width=90% cellpadding='0' cellspacing='1' bgcolor='#65B1B1'>
    <tr>
        <td valign=top>
            <table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='#FFFFFF'>
                <tr>
                    <td bgcolor='#65B1B1' width=100 class='td1' align='left'>&nbsp;성 명&nbsp;</td>
                    <td bgcolor='#FFFFFF'><?php echo  htmlspecialchars($row["name"]) ?></td>
                </tr>
                <tr>
                    <td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;나 이&nbsp;</td>
                    <td bgcolor='#FFFFFF' class='td2'><?php echo  htmlspecialchars($row["nai"]) ?></td>
                </tr>
                <tr>
                    <td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;거주지역&nbsp;</td>
                    <td bgcolor='#FFFFFF' class='td2'><?php echo  htmlspecialchars($row["house"]) ?></td>
                </tr>
                <tr>
                    <td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;전화번호&nbsp;</td>
                    <td bgcolor='#FFFFFF' class='td2'><?php echo  htmlspecialchars($row["phone"]) ?></td>
                </tr>
                <tr>
                    <td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;상담가능시간&nbsp;</td>
                    <td bgcolor='#FFFFFF' class='td2'><?php echo  htmlspecialchars($row["si"]) ?></td>
                </tr>
                <tr>
                    <td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;상담 분류&nbsp;</td>
                    <td bgcolor='#FFFFFF' class='td2'><?php echo  htmlspecialchars($row["cont_1"]) ?></td>
                </tr>
                <tr>
                    <td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;상담 내용&nbsp;</td>
                    <td bgcolor='#FFFFFF' class='td2'><?php echo  nl2br(htmlspecialchars($row["cont_2"])) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p align=center>
    <input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
</p>

<?php
        }
    } else {
        echo ("<p align=center><b>등록 자료가 없음.</b></p>");
    }

    $stmt->close();
    $db->close();
}

if ($BBAdminSelect == "no") {
    include "../../db.php";

    $stmt = $db->prepare("UPDATE MlangOrder SET AdminSelect='yes' WHERE no = ?");
    $stmt->bind_param("i", $no);
    $result = $stmt->execute();

    if (!$result || $stmt->affected_rows < 0) {
        echo "
            <script language=javascript>
                window.alert(\"DB 접속 에러입니다!\")
                history.go(-1);
            </script>";
        exit;
    } else {
        echo ("
            <script language=javascript>
                opener.parent.location.reload();
            </script>
        ");
        exit;
    }

    $stmt->close();
    $db->close();
}

if ($mode == "delete") {
    include "../../db.php";

    $stmt = $db->prepare("DELETE FROM MlangOrder WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();

    echo ("
        <script language=javascript>
        alert('\\n정보를 정상적으로 삭제하였습니다.\\n');
        opener.parent.location.reload();
        window.self.close();
        </script>
    ");
    exit;

    $stmt->close();
    $db->close();
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
                var str = '<?php echo  $_SERVER['PHP_SELF'] ?>?no=' + no + '&mode=delete';
                var popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
                popup.document.location.href = str;
                popup.focus();
            }
        }
    </script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
    <tr>
        <td align=left colspan=12>
            <font color=red>*</font>관리자가 자료를 들여다 본자료는 확인으로 자동으로 갱신됩니다.<br>
        </td>
    </tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
    <tr>
        <td align=center>등록번호</td>
        <td align=center>성명</td>
        <td align=center>접수Time</td>
        <td align=center>확인여부</td>
        <td align=center>자세한정보보기</td>
        <td align=center>관리</td>
    </tr>

    <?php
    include "../../db.php";
    $table = "MlangOrder";

    $Mlang_query = "SELECT * FROM $table";
    $result = mysqli_query($db, "$Mlang_query");
    $total = mysqli_num_rows($result);

    $listcut = 12;  // Number of records per page
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    $result = mysqli_query($db, "$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
    $rows = mysqli_num_rows($result);
    if ($rows) {
        while ($row = mysqli_fetch_assoc($result)) {
    ?>

    <tr bgcolor='#575757'>
        <td align=center><font color=white><?php echo  htmlspecialchars($row['no']) ?></font></td>
        <td align=center><font color=white><?php echo  htmlspecialchars($row['name']) ?></font></td>
        <td align=center><font color=white><?php echo  htmlspecialchars($row['date']) ?></font></td>
        <td align=center>
            <?php if ($row['AdminSelect'] == "no"): ?>
                <b><font color=red>미확인</font></b>
            <?php else: ?>
                <font color=white>확인</font>
            <?php endif; ?>
        </td>
        <td align=center><input type='button' onClick="javascript:popup=window.open('<?php echo  $_SERVER['PHP_SELF'] ?>?mode=view&no=<?php echo  $row['no'] ?>', 'MlangOrder','width=600,height=430,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 자세한정보보기 '></td>
        <td align=center>
            <input type='button' onClick="javascript:Member_Admin_Del('<?php echo  $row['no'] ?>');" value=' 삭제 '>
        </td>
    </tr>

    <?php
        }
    } else {
        if (isset($_GET['search']) && $_GET['search'] == "yes") {
            echo "<tr><td colspan=10><p align=center><br><br>관련 검색 자료없음</p></td></tr>";
        } else {
            echo "<tr><td colspan=10><p align=center><br><br>등록 자료없음</p></td></tr>";
        }
    }
    ?>

</table>

<p align='center'>

<?php
if ($rows) {
    $mlang_pagego = "cate=$cate&title_search=$title_search"; // 필드속성들 전달값

    $pagecut = 7;  // Number of pages displayed in pagination
    $one_bbs = $listcut * $pagecut;  // Total number of records in one page set
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  // Start offset for pagination
    $end_offset = intval($total / $one_bbs) * $one_bbs;  // End offset for pagination
    $start_page = intval($start_offset / $listcut) + 1; // Start page number
    $end_page = ($total % $listcut > 0) ? intval($total / $listcut) + 1 : intval($total / $listcut);

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='{$_SERVER['PHP_SELF']}?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;

        if ($offset != $newoffset) {
            echo "&nbsp;<a href='{$_SERVER['PHP_SELF']}?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }

        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='{$_SERVER['PHP_SELF']}?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }

    echo "총목록갯수: $end_page 개";
}

mysqli_close($db);
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php
include "../down.php";
?>