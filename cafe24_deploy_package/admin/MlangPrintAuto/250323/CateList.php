<?php
// 필요한 파일 포함
include "../title.php";
include "../../mlangprintauto/ConDb.php";
include "CateAdmin_title.php";
include "../../db.php";

// 데이터베이스 연결 (MySQLi 사용)
$mysqli = new mysqli($host, $user, $password, $dataname);
if ($mysqli->connect_error) {
    die("데이터베이스 연결 실패: " . $mysqli->connect_error);
}

// GET 변수 초기화 (경고 방지)
$ACate = isset($_GET['ACate']) ? $_GET['ACate'] : null;
$ATreeNo = isset($_GET['ATreeNo']) ? $_GET['ATreeNo'] : null;
$Ttable = isset($_GET['Ttable']) ? $_GET['Ttable'] : null;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$listcut = 30; // 페이지당 게시물 수

// SQL 쿼리 작성 (조건에 따라 동적 변경)
if ($ACate) {
    $query = "SELECT * FROM $GGTABLE WHERE Ttable=? AND BigNo=?";
    $params = [$Ttable, $ACate];
} elseif ($ATreeNo) {
    $query = "SELECT * FROM $GGTABLE WHERE Ttable=? AND TreeNo=?";
    $params = [$Ttable, $ATreeNo];
} else {
    $query = "SELECT * FROM $GGTABLE WHERE Ttable=?";
    $params = [$Ttable];
}

// 준비된 쿼리 실행
$stmt = $mysqli->prepare($query);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
$total_rows = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>카테고리 목록</title>
</head>
<body>
    <table border="1" align="center" width="100%" cellpadding="5" cellspacing="3">
        <tr>
            <td align="center">등록 NO</td>
            <td align="center">상위 CATEGORY</td>
            <td align="center">TITLE</td>
            <td align="center">관리 기능</td>
        </tr>

        <?php
        // 데이터 출력
        if ($total_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td align="center"><?php echo  $row['no'] ?></td>
                    <td align="center"><?php echo  $row['BigNo'] ?></td>
                    <td align="center"><?php echo  $row['title'] ?></td>
                    <td align="center">
                        <a href="./CateAdmin.php?mode=form&code=modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $Ttable ?>">수정</a>
                        <a href="javascript:void(0);" onclick="WebOffice_customer_Del('<?php echo  $row['no'] ?>')">삭제</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='4' align='center'>등록된 자료가 없습니다.</td></tr>";
        }
        ?>
    </table>

    <p align="center">
        <?php
        // 페이지네이션 처리
        if ($total_rows > 0) {
            $pagecut = 7;
            $one_bbs = $listcut * $pagecut;
            $start_offset = intval($offset / $one_bbs) * $one_bbs;
            $end_offset = intval($total_rows / $one_bbs) * $one_bbs;
            $start_page = intval($start_offset / $listcut) + 1;
            $end_page = ($total_rows % $listcut > 0) ? intval($total_rows / $listcut) + 1 : intval($total_rows / $listcut);

            if ($start_offset != 0) {
                echo "<a href='$PHP_SELF?offset=" . ($start_offset - $one_bbs) . "'>...[이전]</a>&nbsp;";
            }

            for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
                $newoffset = ($i - 1) * $listcut;
                if ($offset != $newoffset) {
                    echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset'>($i)</a>&nbsp;";
                } else {
                    echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
                }
                if ($i == $end_page) break;
            }

            if ($start_offset != $end_offset) {
                echo "&nbsp;<a href='$PHP_SELF?offset=" . ($start_offset + $one_bbs) . "'>[다음]...</a>";
            }
            echo " 총 목록 개수: $end_page 개";
        }
        ?>
    </p>

    <script>
        function WebOffice_customer_Del(no) {
            if (confirm(no + "번 자료를 삭제하시겠습니까?")) {
                window.location.href = "./CateAdmin.php?no=" + no + "&mode=delete";
            }
        }
    </script>
</body>
</html>

<?php
// 데이터베이스 연결 종료
$stmt->close();
$mysqli->close();
?>
