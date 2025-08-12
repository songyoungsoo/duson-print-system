<?php
include "../../db.php";

// 변수 초기화
$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$YearOne = isset($_GET['YearOne']) ? $_GET['YearOne'] : (isset($_POST['YearOne']) ? $_POST['YearOne'] : '');
$YearTwo = isset($_GET['YearTwo']) ? $_GET['YearTwo'] : (isset($_POST['YearTwo']) ? $_POST['YearTwo'] : '');
$Type = isset($_GET['Type']) ? $_GET['Type'] : (isset($_POST['Type']) ? $_POST['Type'] : '');
$TDsearchValue = isset($_GET['TDsearchValue']) ? $_GET['TDsearchValue'] : (isset($_POST['TDsearchValue']) ? $_POST['TDsearchValue'] : '');
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$CountWW = isset($_GET['CountWW']) ? $_GET['CountWW'] : '';

// 일괄 처리 모드
if ($mode == "ChickBoxAll") { 
    if (empty($_POST['check'])) {
        echo ("<script language=javascript>
        window.alert('처리할 항목을 선택하세요.\\n\\n[확인]을 눌러서 다시 시도하세요.');
        history.go(-1);
        </script>");
        exit;
    }

    foreach ($_POST['check'] as $item) {
        $qry = "DELETE FROM MlangPrintAuto_MemberOrderOffice WHERE no='$item'";
        mysqli_query($db, $qry);
    }

    mysqli_close($db);

    echo ("<script language=javascript>
    window.alert('선택한 항목들이 삭제되었습니다.');
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>");
    exit;
}

// 개별 삭제 모드
if ($mode == "delete") {
    $result = mysqli_query($db, "DELETE FROM MlangPrintAuto_MemberOrderOffice WHERE no='$no'");

    echo ("
        <script language=javascript>
        alert('$no 번 항목이 삭제되었습니다.');
        opener.parent.location.reload();
        window.self.close();
        </script>
    ");
    mysqli_close($db);
    exit;
}

include "../top.php"; 

echo ("<script language=javascript>
window.alert('이 페이지는 테스트를 위해 준비 중입니다.\\n\\n1분 후에 다시 시도해 주세요.');
</script>");
?>

<head>
<script>
function popUp(L, e) {
    var barron;
    if (document.layers) {
        barron = document.layers[L];
        barron.left = e.pageX;
        barron.top = e.pageY + 5;
        barron.visibility = "visible";
    } else if (document.all) {
        barron = document.all[L];
        barron.style.pixelLeft = event.clientX + document.body.scrollLeft;
        barron.style.pixelTop = event.clientY + document.body.scrollTop + 5;
        barron.style.visibility = "visible";
    }
}
function popDown(L) {
    if (document.layers) {
        document.layers[L].visibility = "hidden";
    } else if (document.all) {
        document.all[L].style.visibility = "hidden";
    }
}
function allcheck(form) {
    for (var i = 0; i < form.elements.length; i++) {
        form.elements[i].checked = true;
    }
}
function uncheck(form) {
    for (var i = 0; i < form.elements.length; i++) {
        form.elements[i].checked = false;
    }
}
function DelGCheckField() {
    if (confirm('삭제하시겠습니까?')) {
        document.MemoPlusecheckForm.action = "<?php echo $PHP_SELF?>";
        document.MemoPlusecheckForm.submit();
    }
}
</script>
<SCRIPT LANGUAGE=JAVASCRIPT src='../js/exchange.js'></SCRIPT>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php
$CateFF = "style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;' selected";
?>
<table border=0 cellpadding=2 cellspacing=0 width=100%> 
<tr>
<form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?php echo $PHP_SELF?>'>
<td align=left>
    &nbsp;날짜 검색 :&nbsp;
    <input type='text' name='YearOne' size='10' onClick="Calendar(this);" value='<?php echo $YearOne?>'>
    ~
    <input type='text' name='YearTwo' size='10' onClick="Calendar(this);" value='<?php echo $YearTwo?>'>
    &nbsp;&nbsp;
    <select name='Type'>
        <option value='One_1' <?php if ($Type == "inserted") echo $CateFF; ?>>입력일</option>
        <option value='One_3' <?php if ($Type == "sticker") echo $CateFF; ?>>스티커</option>
    </select>
    &nbsp;&nbsp;<b>검색어 :&nbsp;</b>
    <input type='text' name='TDsearchValue' size='30'>
    <input type='submit' value=' 검색 '>
    <?php if ($Type) { ?>
        <input type='button' onClick="javascript:window.location='<?php echo $PHP_SELF?>';" value='초기화'>
    <?php } ?>
</td>
</form>
</tr>
</table>
</td>
<td align=right>
    <input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/int.php?mode=bizinfo', 'MViertbizinfo','width=450,height=300,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='회원 정보'>
    <input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/admin.php?mode=form', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 데이터 입력 '>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>입력일</td>
<td align=center>스티커</td>
<td align=center>스티커 종류</td>
<td align=center>등록일</td>
<td align=center>액션</td>
</tr>

<form method='post' name='MemoPlusecheckForm'>
<INPUT TYPE="hidden" name='mode' value='ChickBoxAll'>

<?php
function Error($msg) {
    echo ("<script language=javascript>
    window.alert('$msg');
    history.go(-1);
    </script>");
    exit;
}

$table = "MlangOrder_PrintAuto";

if ($Type) { // 검색 조건이 있을 때
    if ($YearOne && !$YearTwo) {
        $msg = "날짜 검색을 위해 시작일과 종료일을 모두 입력하세요.";
        Error($msg);
    }
    if ($YearTwo && !$YearOne) {
        $msg = "날짜 검색을 위해 시작일과 종료일을 모두 입력하세요.";
        Error($msg);
    }

    if ($YearOne && $YearTwo) {
        $YearOneOk = $YearOne . " 00:00:00";
        $YearTwoOk = $YearTwo . " 00:00:00";
        $Mlang_query = "SELECT * FROM $table WHERE date > '$YearOneOk' AND date < '$YearTwoOk' AND $Type LIKE '%$TDsearchValue%'";
    } else {
        $Mlang_query = "SELECT * FROM $table WHERE $Type LIKE '%$TDsearchValue%'";
    }
} else { // 일반 조회
    $Mlang_query = "SELECT * FROM $table";
}

$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$total = mysqli_num_rows($query);

$listcut = 20;  // 한 페이지에 보여줄 데이터 개수

if ($CountWW) {
    $result = mysqli_query($db, "$Mlang_query ORDER BY $CountWW $s LIMIT $offset, $listcut");
} else {
    $result = mysqli_query($db, "$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
}

$rows = mysqli_num_rows($result);
if ($rows) {
    while ($row = mysqli_fetch_array($result)) { 
?>
<tr bgcolor='#575757'>
<td align=center>
    &nbsp;
    <?php if ($row['OrderStyle'] != "5") { ?>
        <input type="checkbox" name="check[]" value="<?php echo $row['no']?>">
    <?php } ?>
    <font color=white><?php echo $row['no']?></font>
    &nbsp;
</td>
<td align=center><font color=white><?php echo htmlspecialchars($row['One_1']);?></font></td>
<td align=center><font color=white><?php echo htmlspecialchars($row['One_3']);?></font></td>
<td align=center><font color=white>
    <?php if ($row['One_2'] == "1") echo "유형1"; ?>
    <?php if ($row['One_2'] == "2") echo "유형2"; ?>
    <?php if ($row['One_2'] == "3") echo "기타"; ?>
</font></td>
<td align=center><font color=white><?php echo htmlspecialchars($row['date']);?></font></td>
<td align=center>
    <input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/admin.php?mode=form&code=fff&no=<?php echo $row['no']?>', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value='상세보기'>
    <input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/admin.php?mode=form&code=Print&no=<?php echo $row['no']?>', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value='출력'>
    <input type='button' onClick="javascript:popup=window.open('./MemberOrderOffice/admin.php?mode=form&code=modify&no=<?php echo $row['no']?>', 'MViertWmodify','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='수정'>
</td>
</tr>
<?php
    }
} else {
    if ($TDsearchValue) {
        echo "<tr><td colspan=10><p align=center><BR><BR>검색어 $TDsearchValue - 결과 없음</p></td></tr>";
    } else {
        echo "<tr><td colspan=10><p align=center><BR><BR>데이터가 없습니다</p></td></tr>";
    }
}
?>
<tr><td colspan=12 height=10></td></tr>
</table>

<table border=0 align=center width=100% cellpadding=0 cellspacing=0>
<tr>
<td>
    <input type='button' onClick="javascript:allcheck(MemoPlusecheckForm);" value=' 모두 선택 '>
    <input type='button' onClick="javascript:uncheck(MemoPlusecheckForm);" value=' 선택 해제 '>
    <input type='button' onClick="javascript:DelGCheckField();" value=' 선택 삭제 '>
</td>
</tr>
</form>
</table>

<p align='center'>

<?php
if ($rows) {
    if ($TDsearchValue) {
        $mlang_pagego = "TDsearch=$TDsearch&TDsearchValue=$TDsearchValue";
    } else if ($OrderStyleYU9OK) {
        $mlang_pagego = "OrderStyleYU9OK=$OrderStyleYU9OK";
    } else if ($OrderCate) {
        $mlang_pagego = "OrderCate=$OrderCate";
    } else {
        $mlang_pagego = "";
    }

    $pagecut = 7;  // 한 화면에 보여줄 페이지 수
    $one_bbs = $listcut * $pagecut;  // 한 번에 보여줄 게시물 수
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  // 시작 offset
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;  // 끝 offset
    $start_page = intval($start_offset / $listcut) + 1;  // 시작 페이지
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);  // 끝 페이지

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
    echo "총 페이지 수: $end_page";
}

mysqli_close($db);
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->
<?php
include "../down.php";
?>
