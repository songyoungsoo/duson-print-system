<?php 
$M123 = "..";
include "../top.php"; 
?>

<script language="JavaScript">
// 메뉴 선택 시 페이지 이동
function MM_jumpMenu(targ, selObj, restore) {
    eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
    if (restore) selObj.selectedIndex = 0;
}

// 삭제 확인 메시지 및 삭제 실행
function delete_Mlang(no) {
    if (confirm("한 번 삭제된 데이터는 복구할 수 없습니다.\n\n정말 삭제하시겠습니까?")) {
        var str = './page_admin.php?mode=delete&no=' + no;
        var popup = window.open(str, "pop_mlang", "width=500,height=200,left=2000,top=2000");
        popup.focus();
    }
}
</script>

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
<tr><td align="center">

    <!-- 카테고리 선택 및 검색 -->
    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="100%" height="40" class='coolBar' align="right">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="20"></td>
                        <td>
                            <?php
                            include "../../db.php";
                            $result = mysqli_query($db, "SELECT * FROM $page_big_table");
                            $rows = mysqli_num_rows($result);

                            if ($rows) {
                                echo "<select name='cate' onchange=\"MM_jumpMenu('parent', this, 0)\">
                                    <option>-카테고리 선택-</option>";
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='$PHP_SELF?cate={$row['no']}'>{$row['title']}</option>";
                                }
                                echo "</select>";
                            } else {
                                echo "&nbsp;&nbsp;&nbsp;<b>등록된 카테고리가 없습니다.(관리자에게 문의하세요!!)</b>";
                            }

                            mysqli_close($db);
                            ?>
                        </td>

                        <td>
                            <!-- 검색 기능 -->
                            <form name='SrarchInfo' method='post' onsubmit='return SrarchCheckField()' action='<?php echo $PHP_SELF; ?>'>
                                &nbsp;&nbsp;&nbsp;메뉴명 검색
                                <input type='text' name='search' size='25'>
                                <input type='submit' value='검색'>
                            </form>
                        </td>
                        <td>
                            <!-- 전체 목록 보기 및 새로고침 -->
                            &nbsp;&nbsp;
                            <input type='button' onclick="window.location.href='<?php echo $PHP_SELF; ?>';" value='전체목록보기' style='width:100px;'>
                            <input type='button' onclick="window.location.reload();" value='새로고침' style='width:80px;'>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!------------------------------------------- 목록 출력 ----------------------------------------->

    <?php
    include "../../db.php";

    $table = "$page_table";
    $listcut = 15; // 한 페이지당 보여줄 게시물 수

    // 검색 또는 카테고리 필터링
    if ($cate) {
        $Mlang_query = "SELECT * FROM $table WHERE cate=?";
    } elseif ($search) {
        $Mlang_query = "SELECT * FROM $table WHERE title LIKE ?";
        $search = "%$search%"; // LIKE 구문을 위한 서식 지정
    } else {
        $Mlang_query = "SELECT * FROM $table";
    }

    // SQL 준비
    $stmt = mysqli_prepare($db, $Mlang_query);
    if ($cate) {
        mysqli_stmt_bind_param($stmt, "i", $cate);
    } elseif ($search) {
        mysqli_stmt_bind_param($stmt, "s", $search);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = mysqli_num_rows($result);

    if ($rows) {
        echo("
        <table border='0' align='center' width='100%' cellpadding='5' cellspacing='1' bgcolor='#666600'>
        <tr>
            <td align='center' width='20%' height='30'><font color='white'>번호</font></td>
            <td align='center' width='30%'><font color='white'>카테고리</font></td>
            <td align='center' width='30%'><font color='white'>메뉴 이름</font></td>
            <td align='center' width='20%'><font color='white'>관리</font></td>
        </tr>");

        while ($row = mysqli_fetch_assoc($result)) {
            // 카테고리 정보 가져오기
            $stmt_cate = mysqli_prepare($db, "SELECT * FROM $page_big_table WHERE no=?");
            mysqli_stmt_bind_param($stmt_cate, "i", $row['cate']);
            mysqli_stmt_execute($stmt_cate);
            $result_cate = mysqli_stmt_get_result($stmt_cate);
            $row_cate = mysqli_fetch_assoc($result_cate);

            // 검색어 강조 처리
            if ($search) {
                $row['title'] = str_replace($search, "<b><font color='red'>$search</font></b>", $row['title']);
            }

            echo("
            <tr bgcolor='#FFFFFF'>
                <td>{$row['no']}</td>
                <td>{$row_cate['title']}</td>
                <td>{$row['title']}</td>
                <td align='center'>
                    <input type='button' onclick=\"window.location.href='./page_admin.php?mode=modify&no={$row['no']}';\" value='수정' style='width:50px;'>
                    <input type='button' onclick=\"delete_Mlang({$row['no']});return false;\" value='삭제' style='width:50px;'>
                </td>
            </tr>");
        }
        echo "</table>";
    } else {
        echo "<p align='center'><b>등록된 자료가 없습니다.</b></p>";
    }
    ?>

    <p align='center'>
    <?php
    if ($rows) {
        $recordsu = mysqli_num_rows($result);
        $pagecut = 7; // 한 장당 보여줄 페이지 수
        $one_bbs = $listcut * $pagecut; // 한 장당 실을 수 있는 게시물 수
        $start_offset = intval($offset / $one_bbs) * $one_bbs; // 각 장에 처음 페이지의 offset값
        $end_offset = intval($recordsu / $one_bbs) * $one_bbs; // 마지막 장의 첫 페이지의 offset값
        $start_page = intval($start_offset / $listcut) + 1; // 각 장에 처음 페이지의 값
        $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut); // 마지막 장의 끝 페이지

        if ($start_offset != 0) {
            $apoffset = $start_offset - $one_bbs;
            echo "<a href='$PHP_SELF?offset=$apoffset&cate=$cate&search=$search'>...[이전]</a>&nbsp;";
        }

        for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
            $newoffset = ($i - 1) * $listcut;
            if ($offset != $newoffset) echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&cate=$cate&search=$search'>";
            echo "[$i]";
            if ($offset != $newoffset) echo "</a>&nbsp;";
            if ($i == $end_page) break;
        }

        if ($start_offset != $end_offset) {
            $nextoffset = $start_offset + $one_bbs;
            echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&cate=$cate&search=$search'>[다음]...</a>";
        }

        echo "총 페이지 수: $end_page";
    }

    mysqli_close($db);
    ?>
    </p>

<!------------------------------------------- 목록 끝 ----------------------------------------->

</td></tr>
</table>

<?php include "../down.php"; ?>
