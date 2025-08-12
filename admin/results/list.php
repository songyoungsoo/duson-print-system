<?php
// 데이터베이스 연결
include "../../db.php"; 

$table = "Mlnag_Results_Admin";

// 검색모드일 때 쿼리 설정
if($search){
    $Mlang_query = "SELECT * FROM $table WHERE $bbs_cate LIKE CONCAT('%', ?, '%')";
} else {
    $Mlang_query = "SELECT * FROM $table";
}

// 검색 또는 일반 모드에 따라 쿼리 실행
$stmt = mysqli_prepare($db, $Mlang_query);
if ($search) {
    mysqli_stmt_bind_param($stmt, "s", $search);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$recordsu = mysqli_num_rows($result); // 검색된 레코드 수
$total = mysqli_affected_rows($db);   // 총 레코드 수

$listcut = 15;  // 한 페이지당 보여줄 목록 게시물 수.
if(!$offset) $offset = 0;

// 페이지네이션을 위한 쿼리 실행
$Mlang_query .= " ORDER BY NO DESC LIMIT ?, ?";
$stmt = mysqli_prepare($db, $Mlang_query);
if ($search) {
    mysqli_stmt_bind_param($stmt, "sii", $search, $offset, $listcut);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $offset, $listcut);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows = mysqli_num_rows($result); 

if($rows){
    echo("
    <table border=0 align=center width=100% cellpadding='5' cellspacing='2' class='coolBar'>
    <tr>
    <td align=center width=10%>SKIN</td>    
    <td align=center width=15%>제목</td>    
    <td align=center width=15%>테이블명</td>
    <td align=center width=20%>분류</td>
    <td align=center width=10%>생성일</td>
    <td align=center width=10%>자료수</td>
    <td align=center width=20%>관리기능</td>        
    </tr>
    ");

    $i = 1 + $offset;
    while($row = mysqli_fetch_assoc($result)) {

        if ($search) { // 검색 키워드값 강조
            $row['title'] = str_replace($search, "<b><FONT COLOR=blue>$search</FONT></b>", $row['title']);
            $row['id'] = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $row['id']);
        }

        echo("
        <tr bgcolor='#575757'>
        <form method='post' action='$PHP_SELF'>
        <input type='hidden' name='no' value='{$row['no']}'>
        <input type='hidden' name='mode' value='admin_modify'>
        ");

        // SKIN 선택 옵션
        echo "<td align=center>
        <select name='item'>
            <option value='text' ".($row['item'] == "text" ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "").">기본 텍스트형식</option>
            <option value='photo' ".($row['item'] == "photo" ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "").">기본 PHOTO형식</option>
            <option value='seoulfireworks' ".($row['item'] == "seoulfireworks" ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "").">서울화약 형식</option>
            <option value='seoulfireworks2' ".($row['item'] == "seoulfireworks2" ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "").">서울화약 형식-2</option>
        </select>
        </td>";

        echo("
        <td align=center>&nbsp;<input type='text' name='title' value='{$row['title']}' maxLength='20' size='20'></td>    
        <td>&nbsp;<a href='/results/index.php?table={$row['id']}' target='_blank'><font color=white>{$row['id']}</font></a></td>    
        <td align=center>&nbsp;<input type='text' name='celect' value='{$row['celect']}' maxLength='500' size='35'></td>    
        <td align=center><font color=white>{$row['date']}</font></td>
        ");

        // 자료수 계산
        $total_query = mysqli_query($db, "SELECT * FROM Mlang_{$row['id']}_Results");
        $total_bbs = mysqli_num_rows($total_query);

        echo("<td align=center><font color=#CCFFFF>$total_bbs</font></td>");

        echo("<td align=center>");
        echo("<input type='submit' value='수정' style='width:40; height:22;'>");
        echo("<input type='button' onClick=\"javascript:window.location.href='./data.php?mode=list&id={$row['id']}';\" value='자료관리' style='width:60; height:22;'>");
        echo("<input type='button' onClick=\"javascript:Mlnag_Results_Admin_Del('{$row['id']}');\" value='삭제' style='width:40; height:22;'>");
        echo("<input type='button' onClick=\"javascript:window.open('../bbs/dump.php?TableName=Mlang_{$row['id']}_results', 'bbs_dump','width=567,height=451,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');\" value='빽업' style='width:40; height:22;'>");
        echo("</td></form></tr>");

        $i++;
    }

    echo("</table>");

} else {
    if($search) {
        echo "<p align=center><b>$search 에 대한 게시판 없음</b></p>";
    } else {
        echo "<p align=center><b>생성된 앨범 프로그램이 없습니다.</b></p>";
    }
}
?>

<p align='center'>
<?php
// 페이지네이션 처리
if($rows){
    $mlang_pagego = "mode=list&bbs_cate=$bbs_cate&search=$search"; // 전달할 필드 속성값

    $pagecut = 10;  // 한 페이지당 보여줄 페이지 수
    $one_bbs = $listcut * $pagecut;  // 한 장당 목록(게시물) 수
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  // 각 장의 첫 페이지 $offset값
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;  // 마지막 장의 첫 페이지 $offset값
    $start_page = intval($start_offset / $listcut) + 1;  // 각 장의 첫 페이지 번호
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);  // 마지막 장의 마지막 페이지 번호

    if($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;

        if($offset != $newoffset)
            echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>";
        echo "[$i]";
        if($offset != $newoffset)
            echo "</a>&nbsp;";

        if($i == $end_page) break;
    }

    if($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }

    echo "총 목록 개수: $end_page 개";
}

mysqli_close($db); 
?> 
</p>
