<?php
include "../db.php";
// include "config.php"; // 세션 로그인
require_once __DIR__ . '/../config.php';

header('Content-Type: text/html; charset=UTF-8');

// Initialize variables
$mode = isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : '');
$search = isset($_POST['search']) ? $_POST['search'] : (isset($_GET['search']) ? $_GET['search'] : '');
$bbs_cate = isset($_POST['bbs_cate']) ? $_POST['bbs_cate'] : (isset($_GET['bbs_cate']) ? $_GET['bbs_cate'] : 'title');
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : (isset($_GET['offset']) ? intval($_GET['offset']) : 0);

?>
<head>
<script language="javascript">

var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.charAt(i)) < 0) {
            return false;
        }
    }
    return true;
}

function BbsAdminCheckField() {
    var f = document.BbsAdmin;

    if (f.skin.value == "0") {
        alert("게시판 SKIN을 선택하세요.");
        return false;
    }

    if (f.title.value == "") {
        alert("게시판 제목을 입력하세요.");
        return false;
    }

    if (f.table.value == "") {
        alert("게시판 테이블명을 입력하세요.");
        return false;
    }
    if (!TypeCheck(f.table.value, ALPHA + NUM)) {
        alert("게시판 테이블명은 영문과 숫자로만 입력 가능합니다.");
        return false;
    }
    if (f.table.value.length < 2 || f.table.value.length > 20) {
        alert("게시판 테이블명은 2자 이상 20자 이하로 입력하세요.");
        return false;
    }

    if (f.pass.value == "") {
        alert("게시판 비밀번호를 입력하세요.");
        return false;
    }
    if (!TypeCheck(f.pass.value, ALPHA + NUM)) {
        alert("게시판 비밀번호는 영문과 숫자로만 입력 가능합니다.");
        return false;
    }
    if (f.pass.value.length < 4 || f.pass.value.length > 20) {
        alert("게시판 비밀번호는 4자 이상 20자 이하로 입력하세요.");
        return false;
    }
}

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

function BBS_Admin_Del(id) {
    if (confirm("게시판 데이터를 삭제하시겠습니까?\n\n게시판의 모든 데이터(첨부파일, 데이터 등)가 삭제됩니다.\n\n한번 삭제된 데이터는 복구가 불가능합니다.")) {
        location.href = './bbs/delete.php?id=' + id;
    }
}

function BbsAdminSearchCheckField() {
    var f = document.BbsAdminSearch;

    if (f.search.value == "") {
        alert("검색할 게시판 제목 또는 테이블명을 입력하세요.");
        return false;
    }
}
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
<font color=red>*</font> 게시판 비밀번호는 해당 게시판의 관리와 접근을 위한 Admin 비밀번호입니다.<BR>
<font color=red>*</font> 게시판의 중요한 데이터를 안전하게 관리하기 위해 비밀번호 설정을 권장합니다.
</td>
</tr>
<tr>
<form name='BbsAdmin' method='post' OnSubmit='javascript:return BbsAdminCheckField()' action='<?php echo $_SERVER['PHP_SELF']?>'>
<input type='hidden' name='mode' value='submit'>
<td align=left>
<?php
$BbsAdminCateUrl = "..";
include "./bbs/BbsAdminCate.php";
?>
<INPUT TYPE='TEXT' SIZE=20 maxLength='100' NAME='title' VALUE="게시판 제목" onBlur="checkField(this);" onFocus="clearField(this);">
<INPUT TYPE='TEXT' SIZE=20 maxLength='20' NAME='table' VALUE="테이블명" onBlur="checkField(this);" onFocus="clearField(this);">
<INPUT TYPE='TEXT' SIZE=14 maxLength='20' NAME='pass' VALUE="비밀번호" onBlur="checkField(this);" onFocus="clearField(this);">
<INPUT TYPE=SUBMIT VALUE='게시판 생성'>
</td>
</form>

<form name='BbsAdminSearch' method='post' OnSubmit='javascript:return BbsAdminSearchCheckField()' action='<?php echo $_SERVER['PHP_SELF']?>'>
<input type='hidden' name='mode' value='list'>
<td align=right>
<select name='bbs_cate'>
<option value='title'>게시판 제목</option>
<option value='id'>테이블명</option>
</select>
<INPUT TYPE='TEXT' SIZE=18 NAME='search' onBlur="checkField(this);" onFocus="clearField(this);">
<INPUT TYPE=SUBMIT VALUE='검색'>
</td>
</form>
</tr>
</table>

<!------------------------------------------- 리스트 출력----------------------------------------->
<?php
include "../db.php";
$table = " Mlang_BBS_Admin";

if ($search) {
    $Mlang_query = "SELECT * FROM $table WHERE $bbs_cate LIKE '%$search%'";
} else {
    $Mlang_query = "SELECT * FROM $table";
}

$query = mysqli_query($db, $Mlang_query);

if ($query) {
    $recordsu = mysqli_num_rows($query);
    $total = mysqli_num_rows($query);
} else {
    die("Query Failed: " . mysqli_error($db));
}

$listcut = 15;
$offset = $offset ?? 0;

$result = mysqli_query($db, "$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
if ($result) {
    $rows = mysqli_num_rows($result);
} else {
    die("Query Failed: " . mysqli_error($db));
}

if ($rows) {

echo("
<table border=0 align=center width=100% cellpadding='5' cellspacing='2' class='coolBar'>
<tr>
<td align=center width=25%>게시판 제목</td>
<td align=center width=12%>SKIN</td>
<td align=center width=21%>테이블명</td>
<td align=center width=10%>비밀번호</td>
<td align=center width=10%>생성일</td>
<td align=center width=10%>데이터</td>
<td align=center width=17%>관리</td>		
</tr>
");

$i = 1 + $offset;
while ($row = mysqli_fetch_array($result)) {

    $row['title'] = $search ? str_replace($search, "<b><FONT COLOR=blue>$search</FONT></b>", $row['title']) : $row['title'];
    $row['id'] = $search ? str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $row['id']) : $row['id'];

    echo("
    <tr bgcolor='#575757'>
    <td>&nbsp;<a href='$Homedir/bbs/bbs.php?table=$row[id]&mode=list' target='_blank'><font color=white>$row[title]</font></a></td>
    <td>&nbsp;<font color=white>$row[skin]</font></td>	
    <td>&nbsp;<font color=white>$row[id]</font></td>	
    <td>&nbsp;<font color=white>$row[pass]</font></td>	
    <td align=center><font color=white>$row[date]</font></td>");

    echo("<td align=center>");

    $total_query = mysqli_query($db, "SELECT * FROM Mlang_{$row['id']}_bbs");
    if ($total_query) {
        $total_bbs = mysqli_num_rows($total_query);
        echo("<font color=#CCFFFF>$total_bbs</font></td>");
    } else {
        echo("<font color=red>Error: " . mysqli_error($db) . "</font></td>");
    }

    echo("<td align=center>");

    echo("<input type='button' onClick=\"javascript:popup=window.open('./bbs/AdminModify.php?code=start&no=$row[no]', 'bbs_modisy','width=650,height=600,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();\" value='수정' style='width:40; height:20;'>");
    echo("<input type='button' onClick=\"javascript:BBS_Admin_Del('$row[id]');\" value='삭제' style='width:40; height:20;'>");
    echo("<input type='button' onClick=\"javascript:window.open('./bbs/dump.php?TableName=Mlang_{$row['id']}_bbs', 'bbs_dump','width=567,height=451,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');\" value='백업' style='width:40; height:20;'>");

    echo("</td></tr>");

    $i++;
}

echo("</table>");

} else {
    echo $search ? "<p align=center><b>$search 검색 결과가 없습니다.</b></p>" : "<p align=center><b>등록된 게시판이 없습니다.</b></p>";
}
?>

<p align='center'>

<?php
if ($rows) {

$mlang_pagego = "mode=list&bbs_cate=$bbs_cate&search=$search"; 

$pagecut = 10;
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
        echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>[$i]</a>&nbsp;"; 
    } else {
        echo "&nbsp;<font style='font:bold; color:green;'>[$i]</font>&nbsp;";
    }

    if ($i == $end_page) break; 
} 

if ($start_offset != $end_offset) { 
    $nextoffset = $start_offset + $one_bbs; 
    echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>"; 
} 
echo "총 페이지 수: $end_page 페이지"; 

}

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->
