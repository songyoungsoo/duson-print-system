<?php
if ($mode == "ChickBoxAll") { // 다중 삭제 처리

    include "../../db.php";

    if (!isset($_POST['check']) || count($_POST['check']) == 0) {
        echo ("<script>
            alert('삭제 [처리]할 체크항목이 없습니다.\\n\\n[삭제] 처리할 것을 체크하여 주십시요.');
            history.go(-1);
        </script>");
        exit;
    }

    $check = $_POST['check'];

    foreach ($check as $no) {
        $no = intval($no); // 보안: 정수로 변환
        $stmt = mysqli_prepare($mysqli, "DELETE FROM MlangPrintAuto_MemberOrderOffice WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $no);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // // 폴더 삭제 처리 (필요 시 사용)
        // $uploadDir = "../../MlangOrder_PrintAuto/upload/$no";
        // if (is_dir($uploadDir)) {
        //     $files = glob("$uploadDir/*");
        //     foreach ($files as $file) {
        //         if (is_file($file)) unlink($file);
        //     }
        //     rmdir($uploadDir);
        // }
    }

    mysqli_close($mysqli);

    echo ("<script>
        alert('체크한 항목을 정상적으로 [삭제] 처리 하였습니다.');
        location.href = '$PHP_SELF';
    </script>");
    exit;
}

if ($mode == "delete") {
    include "../../db.php";
    $no = intval($no); // 보안 처리

    $stmt = mysqli_prepare($mysqli, "DELETE FROM MlangPrintAuto_MemberOrderOffice WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_close($mysqli);

    echo ("<script>
        alert('$no 번의 자료를 정상적으로 삭제하였습니다.');
        opener.parent.location.reload();
        window.self.close();
    </script>");
    exit;
}
?>


<?php
$M123 = "..";
include "../top.php";

echo ("<script language=javascript>
window.alert('현페이지 [주문자 접수일보] 의 프로그램은 테스트 해보실수 있습니다.\n\n그러나 현 프로그램은 1차  제작자의 의뢰에 맞추어 제작 하엿음으로\n\n사용여부에 대하여서는 별도로 협의하셔야 합니다.');
</script>");
?>
<head>
<script>
function popUp(L, e) {
  const barron = document.getElementById(L);
  if (barron) {
    barron.style.left = e.pageX + 'px';
    barron.style.top = (e.pageY + 5) + 'px';
    barron.style.visibility = 'visible';
  }
}
function popDown(L) {
  const barron = document.getElementById(L);
  if (barron) {
    barron.style.visibility = 'hidden';
  }
}
function allcheck(form) {
  for (let i = 0; i < form.elements.length; i++) {
    form.elements[i].checked = true;
  }
}
function uncheck(form) {
  for (let i = 0; i < form.elements.length; i++) {
    form.elements[i].checked = false;
  }
}
function DelGCheckField() {
  if (confirm('자료을 삭제처리 하시려 하십니다....\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
    document.MemoPlusecheckForm.action = "<?php echo $_SERVER['PHP_SELF']?>";
    document.MemoPlusecheckForm.submit();
  }
}
</script>
<script src='../js/exchange.js'></script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php $CateFF = "style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;' selected"; ?>
<table border=0 cellpadding=2 cellspacing=0 width=100%>
<tr>
<form method='post' name='TDsearch' onsubmit='return TDsearchCheckField()' action='<?php echo $_SERVER['PHP_SELF']?>'>
<td align=left>
&nbsp;날짜검색 :&nbsp;
<input type='text' name='YearOne' size='10' onclick="Calendar(this);" value='<?php echo $YearOne?>'>
~
<input type='text' name='YearTwo' size='10' onclick="Calendar(this);" value='<?php echo $YearTwo?>'>
&nbsp;&nbsp;
<select name='Type'>
  <option value='One_1' <?php if ($Type == "inserted") echo $CateFF; ?>>작성자</option>
  <option value='One_3' <?php if ($Type == "sticker") echo $CateFF; ?>>업체명</option>
</select>
&nbsp;&nbsp;<b>검색어 :&nbsp;</b>
<input type='text' name='TDsearchValue' size='30'>
<input type='submit' value=' 검 색 '>
<?php if ($Type) { ?>
  <input type='button' onclick="window.location='<?php echo $_SERVER['PHP_SELF']?>';" value='처음으로..'>
<?php } ?>
</td>
</form>
</tr>
</table>
</td>
<td align=right>
<input type='button' onclick="window.open('./MemberOrderOffice/int.php?mode=bizinfo', 'MViertbizinfo','width=450,height=300,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value='사업자정보'>
<input type='button' onclick="window.open('./MemberOrderOffice/admin.php?mode=form', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=' 신자료 입력 '>
</td>
</tr>
</table>

<!-- 리스트 시작 -->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>작성자</td>
<td align=center>업체명</td>
<td align=center>업체구분</td>
<td align=center>등록일</td>
<td align=center>관리</td>
</tr>

<form method='post' name='MemoPlusecheckForm'>
<input type="hidden" name='mode' value='ChickBoxAll'>

<?php
function Error($msg) {
  echo ("<script language=javascript>
  window.alert('$msg');
  history.go(-1);
  </script>");
  exit;
}
?>
<?php
include "../../db.php";
$table = "MlangOrder_PrintAuto";

// Removed duplicate Error function to avoid redeclaration error

if ($Type) {
    if ($YearOne && !$YearTwo) Error("날짜 검색을 하시려면  ~ 이전 의 값을 입력해 주셔야 합니다.");
    if ($YearTwo && !$YearOne) Error("날짜 검색을 하시려면  ~ 이후 의 값을 입력해 주셔야 합니다.");

    if ($YearOne || $YearTwo) {
        $YearOneOk = "$YearOne 00:00:00";
        $YearTwoOk = "$YearTwo 00:00:00";
        $Mlang_query = "SELECT * FROM $table WHERE date > '$YearOneOk' AND date < '$YearTwoOk' AND $Type LIKE '%$TDsearchValue%'";
    } else {
        $Mlang_query = "SELECT * FROM $table WHERE $Type LIKE '%$TDsearchValue%'";
    }
} else {
    $Mlang_query = "SELECT * FROM $table";
}

$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut = 20;
if (!$offset) $offset = 0;

if ($CountWW) {
    $result = mysqli_query($db, "$Mlang_query ORDER BY $CountWW $s LIMIT $offset, $listcut");
} else {
    $result = mysqli_query($db, "$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
}

$rows = mysqli_num_rows($result);
if ($rows) {
    while ($row = mysqli_fetch_array($result)) {
        echo "<tr bgcolor='#575757'>";
        echo "<td align='center'>&nbsp;";
        if ($row['OrderStyle'] != "5") {
            echo "<input type='checkbox' name='check[]' value='{$row['no']}'>";
        }
        echo "<font color='white'>{$row['no']}</font>&nbsp;</td>";
        echo "<td align='center'><font color='white'>" . htmlspecialchars($row['One_1']) . "</font></td>";
        echo "<td align='center'><font color='white'>" . htmlspecialchars($row['One_3']) . "</font></td>";
        echo "<td align='center'><font color='white'>";
        if ($row['One_2'] == "1") echo "신규업체";
        if ($row['One_2'] == "2") echo "거래업체";
        if ($row['One_2'] == "3") echo "하청";
        echo "</font></td>";
        echo "<td align='center'><font color='white'>" . htmlspecialchars($row['date']) . "</font></td>";
        echo "<td align='center'>";
        echo "<input type='button' onclick=\"window.open('./MemberOrderOffice/admin.php?mode=form&code=fff&no={$row['no']}', 'MViertWSubmitr','width=650,height=600');\" value='복제등록'>";
        echo "<input type='button' onclick=\"window.open('./MemberOrderOffice/admin.php?mode=form&code=Print&no={$row['no']}', 'MViertWSubmitr','width=650,height=600');\" value='인쇄모드'>";
        echo "<input type='button' onclick=\"window.open('./MemberOrderOffice/admin.php?mode=form&code=modify&no={$row['no']}', 'MViertWmodify','width=650,height=600');\" value='수정'>";
        echo "</td></tr>";
    }
} else {
    if ($TDsearchValue) {
        echo "<tr><td colspan='10'><p align='center'><br><br>$TDsearch 로 검색되는 $TDsearchValue - 관련 검색 자료없음</p></td></tr>";
    } elseif ($OrderCate) {
        echo "<tr><td colspan='10'><p align='center'><br><br>$cate 로 검색되는 - 관련 검색 자료없음</p></td></tr>";
    } else {
        echo "<tr><td colspan='10'><p align='center'><br><br>등록 자료없음</p></td></tr>";
    }
}

echo "<tr><td colspan='12' height='10'></td></tr></table>";

echo "<table border='0' align='center' width='100%' cellpadding='0' cellspacing='0'><tr><td>";
echo "<input type='button' onclick=\"allcheck(MemoPlusecheckForm);\" value=' 전 체 선 택 '>";
echo "<input type='button' onclick=\"uncheck(MemoPlusecheckForm);\" value=' 선 택 해 제 '>";
echo "<input type='button' onclick=\"DelGCheckField();\" value=' 체크항목 삭 제 '>";
echo "</td></tr></form></table>";

if ($rows) {
    if ($TDsearchValue) {
        $mlang_pagego = "TDsearch=$TDsearch&TDsearchValue=$TDsearchValue";
    } elseif ($OrderStyleYU9OK) {
        $mlang_pagego = "OrderStyleYU9OK=$OrderStyleYU9OK";
    } elseif ($OrderCate) {
        $mlang_pagego = "OrderCate=$OrderCate";
    } else {
        $mlang_pagego = "";
    }

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

    echo "총목록갯수: $end_page 개";
}

mysqli_close($db);
?>
