<?php
$M123 = "..";
include "../top.php";

if (!isset($db)) {
    include "../../db.php";
}

$table = "member_T";

if ($search == "yes") {
    // Handle search case if needed
    $Mlang_query = "SELECT * FROM $table WHERE ..."; // Example of search query
} else {
    // Default query without search
    $Mlang_query = "SELECT * FROM $table";
}

$result = mysqli_query($db, $Mlang_query);
$rows = mysqli_num_rows($result);

$listcut = 12;  // Number of records per page

if (!$offset) {
    $offset = 0;
}

?>

<!-- HTML and JavaScript content -->
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
    if (confirm(no + '번 회원을 삭제하시겠습니까?')) {
        var str = 'admin.php?no=' + no + '&mode=delete';
        var popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<table border="0" align="center" width="100%" cellpadding="8" cellspacing="3" class="coolBar">
<tr>
<td align="left">
<font color="red">*</font> 추가적인 설명이 필요한 문구
</td>
<td align="right">
회원수 - <font style="color:blue;"><b><?php echo  $rows ?></b></font>&nbsp;명
<?php if ($rows < 10): ?>
    <input type="button" onClick="javascript:popup=window.open('admin.php?mode=form', 'Member_T','width=400,height=220,top=50,left=50,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value="회원 추가">
<?php else: ?>
    <BR><font color="#FF0099">10명 이상 추가할 수 없습니다.</font>
<?php endif; ?>
</td>
</tr>
</table>

<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
<tr>
<td align="center">사진</td>
<td align="center">이름</td>
<td align="center">년도</td>
<td align="center">지역</td>
<td align="center">직업</td>
<td align="center">작업</td>
</tr>

<?php
// Fetch and display records
$query = "$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut";
$result = mysqli_query($db, $query);
$rows = mysqli_num_rows($result);

if ($rows > 0) {
    while ($row = mysqli_fetch_array($result)) {
        echo "<tr bgcolor='#575757'>";
        echo "<td align='center'><img src='../../IndexSoft/member_T/upload/{$row['photo']}' width='50'></td>";
        echo "<td align='center'><font color='white'>{$row['name']}</font></td>";
        echo "<td align='center'><font color='white'>{$row['year']} 년</font></td>";
        echo "<td align='center'><font color='white'>{$row['map']}</font></td>";
        echo "<td align='center'><font color='white'>{$row['job']}</font></td>";
        echo "<td align='center'>";
        echo "<input type='button' onClick=\"javascript:popup=window.open('admin.php?mode=form&code=modify&no={$row['no']}', 'Member_T_Modify','width=400,height=220,top=50,left=50,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();\" value='수정'>";
        echo "<input type='button' onClick=\"javascript:WomanMember_Admin_Del('{$row['no']}');\" value='삭제'>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    // No records found
    if ($search) {
        echo "<tr><td colspan='6'><p align='center'><BR><BR>해당 검색 결과가 없습니다.</p></td></tr>";
    } else {
        echo "<tr><td colspan='6'><p align='center'><BR><BR>현재 데이터가 없습니다.</p></td></tr>";
    }
}

mysqli_close($db);
?>

</table>

<p align="center">
<?php
// Pagination logic
if ($rows > 0) {
    $mlang_pagego = "cate=$cate&title_search=$title_search"; // Additional parameters if needed

    $pagecut = 7;  // Number of pages in pagination
    $one_bbs = $listcut * $pagecut;  // Number of records per page

    $start_offset = intval($offset / $one_bbs) * $one_bbs;
    $end_offset = intval($rows / $one_bbs) * $one_bbs;

    $start_page = intval($start_offset / $listcut) + 1;
    $end_page = ($rows % $listcut > 0) ? intval($rows / $listcut) + 1 : intval($rows / $listcut);

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

    echo "총 페이지: $end_page 개";
}
?>
</p>

<?php include "../down.php"; ?>