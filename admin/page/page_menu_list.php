<?php 
$M123 = "..";
include "../top.php"; 
?>

<script>
function PageMenuDel(no) {
    if (confirm("(메뉴를 삭제하시면 PAGE와 관련된 데이터가 모두 사라집니다.)\n\n한 번 삭제된 데이터는 복구할 수 없습니다.\n\n정말 삭제하시겠습니까?")) {
        var str = 'page_menu_admin.php?no=' + no + '&mode=delete';
        var popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
<tr>
    <td align="center">

        <!------------------------------------------- 리스트 시작 ----------------------------------------->

        <?php
        include "../../db.php"; // 데이터베이스 연결

        $table = "$page_big_table";
        $listcut = 15; // 한 페이지당 보여줄 목록 개수

        if (!isset($offset)) $offset = 0; // 페이지 오프셋 초기화

        // 데이터베이스 쿼리 준비
        $Mlang_query = "SELECT * FROM $table ORDER BY no DESC LIMIT ?, ?";
        $stmt = mysqli_prepare($db, $Mlang_query);
        mysqli_stmt_bind_param($stmt, "ii", $offset, $listcut);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // 전체 게시물 수 조회
        $total_query = "SELECT COUNT(*) as total FROM $table";
        $total_result = mysqli_query($db, $total_query);
        $total_row = mysqli_fetch_assoc($total_result);
        $recordsu = $total_row['total'];
        $rows = mysqli_num_rows($result);

        if ($rows) {
            echo("
                <table border='0' align='center' width='100%' cellpadding='5' cellspacing='1' bgcolor='#66CC99'>
                <tr>
                    <td align='center' width='25%' height='30'><font color='white'>번호</font></td>
                    <td align='center' width='50%'><font color='white'>메뉴 이름</font></td>
                    <td align='center' width='25%'><font color='white'>관리</font></td>
                </tr>
            ");

            while ($row = mysqli_fetch_assoc($result)) {
                echo("
                    <tr bgcolor='#FFFFFF'>
                        <td>{$row['no']}</td>
                        <td>{$row['title']}</td>
                        <td align='center'>
                            <input type='button' onClick=\"window.open('./page_menu_admin.php?mode=modify&no={$row['no']}', 'page_menu_admin','width=650,height=200,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');\" value='수정' style='width:50px;'>
                            <input type='button' onClick=\"PageMenuDel('{$row['no']}');\" value='삭제' style='width:50px;'>
                        </td>
                    </tr>
                ");
            }
            echo "</table>";
        } else {
            echo "<p align='center'><br><br><br><big>메뉴</big> - <b>등록된 자료 없음</b></p>";
        }

        ?>

        <p align='center'>
        <?php
        if ($rows) {
            $pagecut = 7;  // 한 장당 보여줄 페이지 수
            $one_bbs = $listcut * $pagecut;  // 한 장당 실을 수 있는 게시물 수
            $start_offset = intval($offset / $one_bbs) * $one_bbs;  // 각 장에 처음 페이지의 offset값
            $end_offset = intval($recordsu / $one_bbs) * $one_bbs;  // 마지막 장의 첫 페이지의 offset값
            $start_page = intval($start_offset / $listcut) + 1;  // 각 장에 처음 페이지의 값
            $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);  // 마지막 장의 끝 페이지
            
            if ($start_offset != 0) {
                $apoffset = $start_offset - $one_bbs;
                echo "<a href='$PHP_SELF?offset=$apoffset'>...[이전]</a>&nbsp;";
            }

            for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
                $newoffset = ($i - 1) * $listcut;

                if ($offset != $newoffset) {
                    echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset'>";
                }
                echo "[$i]";
                if ($offset != $newoffset) {
                    echo "</a>&nbsp;";
                }

                if ($i == $end_page) break;
            }

            if ($start_offset != $end_offset) {
                $nextoffset = $start_offset + $one_bbs;
                echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset'>[다음]...</a>";
            }
            echo "총목록갯수: $end_page 개";
        }

        mysqli_close($db);
        ?>
        </p>

        <!------------------------------------------- 리스트 끝 ----------------------------------------->
    </td>
</tr>
</table>

<?php include "../down.php"; ?>
