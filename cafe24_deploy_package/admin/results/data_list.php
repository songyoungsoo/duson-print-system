<!------------------------------------------- 리스트 출력 ----------------------------------------->
<?php
include "$M123/../db.php"; // 데이터베이스 연결

$table = "Mlang_${id}_Results"; // 테이블 이름 설정

// 검색 조건에 따라 SQL 쿼리 생성
if ($search) {
    $Mlang_query = "SELECT * FROM $table WHERE $search_cate LIKE '%$search%'";
} else {
    $Mlang_query = "SELECT * FROM $table";
}

// 쿼리 실행
$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$total = mysqli_affected_rows($db);
?>

<table border=0 align=center width=100% cellpadding=0 cellspacing=0  class='coolBar'>
<tr>
<td width=20></td>
<td>
테이블: <b><?php echo $DataAdminFild_title?></b>&nbsp;(<?php echo $total?>)&nbsp;&nbsp;
<input type='button' onClick="javascript:popup=window.open('data_submit.php?id=<?php echo $id?>&mode=submit', 'data_submit','width=600,height=300,top=10,left=10,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='데이터 입력창 열기'>
</td>
<td height=40 align=right>

<table border=0 cellpadding=0 cellspacing=0>
<tr>
<td width=20></td>
<head>
<script language=javascript>
// 검색 필드 확인 함수
function SrarchCheckField() {
    var f = document.SrarchInfo;
    if (f.search.value == "") {
        alert("검색어를 입력하세요!!");
        return false;
    }
}

// 데이터 삭제 확인 함수
function ResultsDelTT(no) {
    var str;
    if (confirm("정말로 데이터를 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.")) {
        str = '/admin/int/delete.php?no=' + no + '&table=<?php echo $table?>&bbs=del&file=ok&id=<?php echo $id?>&style=results';
        popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<form name='SrarchInfo' method='post' OnSubmit='javascript:return SrarchCheckField()' action='<?php echo "$PHP_SELF"; ?>'>
<td>
<select name=search_cate>
    <option value='Mlang_bbs_title'>제목</option>
    <option value='Mlang_bbs_connent'>내용</option>
</select>
<input type='hidden' name='mode' value='<?php echo $mode?>'>
<input type='hidden' name='id' value='<?php echo $id?>'>
<input type='text' name='search' size='25'>
<input type='submit' value='검색'>
</td>
</form>
<td>
&nbsp;&nbsp;
<input type='button' onClick="javascript:window.location.href='<?php echo "$PHP_SELF"; ?>?mode=list&id=<?php echo $id?>';" value='전체 보기' style='width:80;'>
<input type='button' onClick="javascript:window.location.reload();" value='새로고침' style='width:60;'>
&nbsp;
</tr></table>

</td></tr>
</table>


<?php
$listcut = 15; // 한 페이지에 표시할 게시물 수
if (!$offset) $offset = 0; 

// 쿼리 실행 (페이징 처리)
$result = mysqli_query($db, "$Mlang_query ORDER BY Mlang_bbs_no DESC LIMIT $offset,$listcut");
$rows = mysqli_num_rows($result);
if ($rows) {

echo("
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#666600'>
<tr>
<td align=center height=30><font color=white>번호</font></td>");

if ($DataAdminFild_celect) {
    echo("<td align=center><font color=white>링크</font></td>");
}

if ($DataAdminFild_item == "text") {
    echo("<td align=center><font color=white>제목</font></td>
    <td align=center><font color=white>내용</font></td>
    <td align=center><font color=white>관리</font></td>
    </tr>");
} else {
    echo("
    <td align=center><font color=white>제목</font></td>
    <td align=center><font color=white>관리</font></td>
    </tr>");
}

$i = 1 + $offset;
while ($row = mysqli_fetch_array($result)) { 
    echo("<tr bgcolor='#FFFFFF'>
    <td>&nbsp;&nbsp;{$row['Mlang_bbs_no']}&nbsp;</td>");

    if ($DataAdminFild_celect) {
        echo("<td align=center>{$row['Mlang_bbs_link']}</td>");
    }

    // 검색된 항목 하이라이트
    if ($search) {
        $row['Mlang_bbs_title'] = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $row['Mlang_bbs_title']);
        $row['Mlang_bbs_connent'] = str_replace($search, "<b><FONT COLOR=blue>$search</FONT></b>", $row['Mlang_bbs_connent']);
    }

    if ($row['Mlang_bbs_title']) {
        echo("<td><a href='/results/index.php?table=$id&mode=view&no={$row['Mlang_bbs_no']}' target='_blank'>{$row['Mlang_bbs_title']}</a></td>");
    } else {
        echo("<td><a href='/results/index.php?table=$id&mode=view&no={$row['Mlang_bbs_no']}' target='_blank'>내용없음</a></td>");
    }

    echo("
    <td align=center>
    <input type='button' onClick=\"javascript:popup=window.open('data_submit.php?id=$id&mode=modify&no={$row['Mlang_bbs_no']}', 'data_submit','width=600,height=300,top=10,left=10,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();\" value='수정' style='width:50;'>
    <input type='button' onClick=\"javascript:ResultsDelTT('{$row['Mlang_bbs_no']}');\" value='삭제' style='width:50;'>
    </td>
    </tr>");
    $i++;
}

echo("</table>");

} else {
    if ($search) {
        echo "<p align=center><b>$search</b> 검색된 결과가 없습니다.</p>";
    } else {
        echo "<p align=center><b>데이터가 없습니다.</b></p>";
    }
}
?>

<p align='center'>
<?php
if ($rows) {

    if ($search) {
        $mlang_pagego = "mode=$mode&id=$id&search_cate=$search_cate&search=$search"; // 검색 결과
    } else {
        $mlang_pagego = "mode=$mode&id=$id"; // 기본 페이지
    }

    $pagecut = 7;  // 한 번에 표시할 페이지 수
    $one_bbs = $listcut * $pagecut;  // 한 페이지에 표시할 게시물 수
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
            echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>";
        }
        echo "[$i]";
        if ($offset != $newoffset) {
            echo "</a>&nbsp;";
        }

        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }
    echo "전체 페이지 수: $end_page 페이지";
}

mysqli_close($db); 
?>
</p>
<!------------------------------------------- 리스트 끝 ----------------------------------------->

</td></tr>
</table>
