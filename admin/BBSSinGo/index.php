<?php
// GET이나 POST 요청에서 'mode' 변수를 가져오고, 없으면 기본값을 빈 문자열로 설정
$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');

$search = isset($_POST['search']) ? $_POST['search'] : '';
$TDsearchValue = isset($_POST['TDsearchValue']) ? $_POST['TDsearchValue'] : '';
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;

if ($mode == "delete") {
    include "../../db.php";

    $stmt = $db->prepare("DELETE FROM BBS_Singo WHERE no = ?");
    $stmt->bind_param("s", $no);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo "
    <script language='javascript'>
    alert('성공적으로 - $no - 삭제되었습니다.');
    opener.parent.location.reload();
    window.self.close();
    </script>
    ";
    exit;
}
?>

<?php
if ($mode == "view") {
    include "../../db.php";

    $stmt = $db->prepare("SELECT * FROM BBS_Singo WHERE no = ?");
    $stmt->bind_param("s", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['AdminSelect'] == "1") {
        $stmt_update = $db->prepare("UPDATE BBS_Singo SET AdminSelect = '2' WHERE no = ?");
        $stmt_update->bind_param("s", $no);
        $stmt_update->execute();
        $stmt_update->close();

        echo "
        <script language='javascript'>
        opener.parent.location.reload();
        </script>
        ";
    }
    ?>

    <?php echo  htmlspecialchars($row['Cont']); ?>

    <p align="center">
        <input type='button' onClick='javascript:window.close();' value='닫기 - CLOSE' style='background-color:#FFFFFF; color:#539D26; border-width:1; border-style:solid; height:21px; border:1 solid #539D26;'>
    </p>

    <?php
    $stmt->close();
    $db->close();
    exit;
}
?>

<?php
$M123 = "..";
include "../top.php";
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

function Member_Admin_Del(no) {
    if (confirm(no + '번 데이터를 삭제 하시겠습니까?\n\n삭제된 데이터는 복구할 수 없습니다.')) {
        str = '<?php echo $PHP_SELF?>?no=' + no + '&mode=delete';
        popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}

function TDsearchCheckField() {
    var f = document.TDsearch;

    if (f.TDsearchValue.value == "") {
        alert("검색어를 입력해 주세요.");
        f.TDsearchValue.focus();
        return false;
    }
}
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=right>
    <table border=0 align=center width=100% cellpadding=2 cellspacing=0>
        <tr>
            <form method='post' name='TDsearch' onSubmit='javascript:return TDsearchCheckField()' action='<?php echo $PHP_SELF?>'>
            <td align=left>
                <b>검색 :</b>&nbsp;
                <select name='TDsearch'>
                    <option value='id'>회원아이디</option>
                    <option value='name'>회원이름</option>
                </select>
                <input type='text' name='TDsearchValue' size='20'>
                <input type='submit' value=' 검색 '>
            </td>
            </form>
        </tr>
    </table>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
    <td align=center>번호</td>
    <td align=center>신고회원</td>
    <td align=center>신고날짜</td>
    <td align=center>게시판, 번호</td>
    <td align=center>확인여부</td>
    <td align=center>관리</td>
</tr>

<?php
include "../../db.php";
$table = "BBS_Singo";

if ($search == "yes") {
    function ERROR($msg) {
        echo "<script language='javascript'>
        window.alert('$msg');
        history.go(-1);
        </script>";
        exit;
    }
} else if ($TDsearchValue) {
    $Mlang_query = "SELECT * FROM $table WHERE $TDsearch LIKE ?";
    $TDsearchValue = "%" . $TDsearchValue . "%";
} else {
    $Mlang_query = "SELECT * FROM $table";
}

$stmt = $db->prepare($Mlang_query);
if ($TDsearchValue) {
    $stmt->bind_param("s", $TDsearchValue);
}
$stmt->execute();
$query = $stmt->get_result();
$recordsu = $query->num_rows;
$total = $db->affected_rows;

$listcut = 12;
if (!$offset) $offset = 0;

$Mlang_query .= " ORDER BY no DESC LIMIT ?, ?";
$stmt_paging = $db->prepare($Mlang_query);
if ($TDsearchValue) {
    $stmt_paging->bind_param("sii", $TDsearchValue, $offset, $listcut);
} else {
    $stmt_paging->bind_param("ii", $offset, $listcut);
}
$stmt_paging->execute();
$result = $stmt_paging->get_result();
$rows = $result->num_rows;

if ($rows) {
    while ($row = $result->fetch_assoc()) {
        ?>

        <tr bgcolor='#575757'>
            <td align=center><font color=white><?php echo $row['no']?></font></td>
            <td><font color=white><?php echo  htmlspecialchars($row['Member_id']); ?></font></td>
            <td align=center><font color=white><?php echo  htmlspecialchars($row['date']); ?></font></td>
            <td align=center><a href='<?php echo $Homedir?>/bbs/bbs.php?table=<?php echo $row['BBS_table']?>&mode=list' target='_blank'><font color=white><?php echo $row['BBS_table']?> - <?php echo $row['BBS_no']?></font></a></td>
            <td align=center><font color=white><?php if ($row['AdminSelect'] == "1") { echo "<font color=red>확인</font>"; } else { echo "<font color=#FFFFFF>미확인</font>"; } ?></font></td>
            <td align=center>
                <input type='button' onClick="javascript:popup=window.open('<?php echo $PHP_SELF?>?mode=view&no=<?php echo $row['no']?>', 'afcas12s','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='상세보기'>
                <input type='button' onClick="javascript:Member_Admin_Del('<?php echo $row['no']?>');" value=' 삭제 '>
            </td>
        </tr>

        <?php
    }
} else {
    if ($search) {
        echo "<tr><td colspan=10><p align=center><BR><BR>검색 결과가 없습니다</p></td></tr>";
    } else if ($TDsearchValue) {
        echo "<tr><td colspan=10><p align=center><BR><BR>$TDsearch 검색어에 대한 $TDsearchValue - 검색 결과가 없습니다</p></td></tr>";
    } else {
        echo "<tr><td colspan=10><p align=center><BR><BR>등록된 데이터가 없습니다</p></td></tr>";
    }
}
?>

</table>

<p align='center'>
<?php
if ($rows) {
    $mlang_pagego = "cate=$cate&title_search=$title_search";

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
    echo "총 페이지: $end_page 페이지";
}

$db->close();
?>
</p>

<?php include "../down.php"; ?>
