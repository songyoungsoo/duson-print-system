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

function WomanMember_Admin_Del(no) {
    if (confirm(no + '번 회원을 정말 삭제하시겠습니까?\n\n한번 삭제된 데이터는 복구할 수 없습니다.')) {
        var str = 'admin.php?no=' + no + '&mode=delete';
        var popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<?php
include "../../db.php";
$table = "member_X";

// Prepare the query based on search condition
if (isset($_GET['search']) && $_GET['search'] == "yes") {
    // Replace this comment with the search condition
    $Mlang_query = "SELECT * FROM $table WHERE ..."; 
} else {
    $Mlang_query = "SELECT * FROM $table";
}

$result = $db->query($Mlang_query);
$total = $result->num_rows;

$listcut = 12;  // Number of records per page
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
?>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<font color=red>*</font> 최대 50명까지 회원을 입력할 수 있습니다.<br>
<font color=red>*</font> 회원 등록일로부터 50명 이상 등록할 수 없습니다.<br>
<font color=red>*</font> 등록일 49명 초과시 등록을 중지합니다.<br>
</td>
<td align=right>
총 회원수 - <font style='color:blue;'><b><?php echo  $total ?></b></font>&nbsp;명
<?php if ($total < 50): ?>
<input type='button' onClick="javascript:popup=window.open('admin.php?mode=form', 'Member_X','width=300,height=220,top=50,left=50,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 회원 등록 '>
<?php else: ?>
<br><font color=#FF0099>50명 이상 등록 불가</font>
<?php endif; ?>
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작 ----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>ID</td>
<td align=center>성별</td>
<td align=center>생년</td>
<td align=center>주소</td>
<td align=center>직업</td>
<td align=center>학력</td>
<td align=center>관리</td>
</tr>

<?php
$result = $db->query("$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <tr bgcolor='#575757'>
        <td align=center><font color=white><?php echo  htmlspecialchars($row['id']) ?></font></td>
        <td align=center><?php if ($row['sex'] == "1") { echo "<font color=gold>남성"; } else { echo "<font color=pink>여성"; } ?></font></td>
        <td align=center><font color=white><?php echo  htmlspecialchars($row['year']) ?> 년</font></td>
        <td align=center><font color=white><?php echo  htmlspecialchars($row['map']) ?></font></td>
        <td align=center><font color=white><?php echo  htmlspecialchars($row['job']) ?></font></td>
        <td align=center><font color=white>
        <?php
        switch ($row['school']) {
            case "1": echo "초등학교 졸업"; break;
            case "2": echo "초등학교 중퇴"; break;
            case "3": echo "중학교 졸업"; break;
            case "4": echo "중학교 중퇴"; break;
            case "5": echo "고등학교 졸업"; break;
            case "6": echo "고등학교 중퇴"; break;
            case "7": echo "대학교 졸업"; break;
            case "8": echo "대학교 중퇴"; break;
            case "9": echo "대학원 졸업"; break;
            case "10": echo "대학원 중퇴"; break;
            case "11": echo "박사"; break;
            default: echo "학력 정보 없음";
        }
        ?>
        </font></td>
        <td align=center>
        <input type='button' onClick="javascript:WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value=' 삭제 '>
        </td>
        </tr>
        <?php
    }
} else {
    if (isset($_GET['search']) && $_GET['search'] == "yes") {
        echo "<tr><td colspan=10><p align=center><br><br>검색 결과가 없습니다.</p></td></tr>";
    } else {
        echo "<tr><td colspan=10><p align=center><br><br>데이터가 없습니다.</p></td></tr>";
    }
}
?>

</table>

<p align='center'>
<?php
if ($total > 0) {
    $pagecut = 7;  // Number of pages displayed in pagination
    $one_bbs = $listcut * $pagecut;  // Total number of records in one page set
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  // Start offset for pagination
    $end_offset = intval($total / $one_bbs) * $one_bbs;  // End offset for pagination
    $start_page = intval($start_offset / $listcut) + 1; // Start page number
    $end_page = ($total % $listcut > 0) ? intval($total / $listcut) + 1 : intval($total / $listcut); 

    if ($start_offset != 0) { 
        $apoffset = $start_offset - $one_bbs; 
        echo "<a href='$PHP_SELF?offset=$apoffset'>...[이전]</a>&nbsp;"; 
    } 

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) { 
        $newoffset = ($i - 1) * $listcut; 

        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset'>($i)</a>&nbsp;"; 
        } else {
            echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"); 
        } 

        if ($i == $end_page) break; 
    } 

    if ($start_offset != $end_offset) { 
        $nextoffset = $start_offset + $one_bbs; 
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset'>[다음]...</a>"; 
    } 
    echo "총 페이지 수: $end_page";
}

$db->close();
?> 
</p>
<!------------------------------------------- 리스트 끝 ----------------------------------------->
<?php
include "../down.php";
?>
