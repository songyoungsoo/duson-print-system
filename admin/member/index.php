<?php
// 변수 초기화
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$TDsearch = isset($_POST['TDsearch']) ? $_POST['TDsearch'] : '';
$TDsearchValue = isset($_POST['TDsearchValue']) ? $_POST['TDsearchValue'] : '';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$CountWW = isset($_GET['CountWW']) ? $_GET['CountWW'] : '';
$s = isset($_GET['s']) ? $_GET['s'] : '';
$cate = isset($_GET['cate']) ? $_GET['cate'] : '';
$title_search = isset($_GET['title_search']) ? $_GET['title_search'] : '';
$code = isset($_GET['code']) ? $_GET['code'] : '';
$no = isset($_GET['no']) ? intval($_GET['no']) : 0;

// 레벨 수정 모드
if ($mode == "LevelModify") {
    include __DIR__ . "/../../db.php";
    include __DIR__ . "/../config.php";

    $stmt = $db->prepare("UPDATE member SET level = ? WHERE no = ?");
    $stmt->bind_param('si', $code, $no);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo ("<script language='javascript'>
        window.alert('회원의 레벨을 조절하였습니다.');
    </script>
    <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=" . $offset . "&TDsearch=" . urlencode($TDsearch) . "&TDsearchValue=" . urlencode($TDsearchValue) . "'>
    ");
    exit;
}


// 포인트 수정 모드
// 변수 초기화
$money = isset($_POST['money']) ? intval($_POST['money']) : 0;
$no = isset($_POST['no']) ? intval($_POST['no']) : 0;
if ($mode == "PointModlfy") {
    include __DIR__ . "/../../db.php";

    $stmt = $db->prepare("UPDATE member SET money = ? WHERE no = ?");
    if ($stmt === false) {
        die("쿼리 준비 실패: " . htmlspecialchars($db->error));
    }

    $stmt->bind_param('ii', $money, $no);

    if (!$stmt->execute()) {
        die("포인트 업데이트 실패: " . htmlspecialchars($stmt->error));
    }

    $stmt->close();
    $db->close();

    echo ("<script language='javascript'>
        window.alert('회원의 포인트를 조절하였습니다.');
    </script>
    <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=" . urlencode($offset) . "&TDsearch=" . urlencode($TDsearch) . "&TDsearchValue=" . urlencode($TDsearchValue) . "'>");
    exit;
}

$M123 = "..";
include __DIR__ . "/../top.php";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
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

        function Member_Admin_Del(no) {
            if (confirm(no + '번 회원을 탈퇴처리 하시겠습니까..?\\n\\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
                const str = 'admin.php?no=' + no + '&mode=delete';
                const popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
                popup.document.location.href = str;
                popup.focus();
            }
        }

        function TDsearchCheckField() {
            var f = document.TDsearch;
            if (f.TDsearchValue.value == "") {
                alert("검색할 검색어 값을 입력해주세요");
                f.TDsearchValue.focus();
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <table border="0" align="center" width="100%" cellpadding="8" cellspacing="3" class="coolBar">
        <tr>
            <td align="left">
                <table border="0" align="center" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <form method="post" name="TDsearch" onsubmit="return TDsearchCheckField()" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                            <td align="left">
                                <b>간단 검색 :&nbsp;</b>
                                <select name="TDsearch">
                                    <option value="id">회원아이디</option>
                                    <option value="name">회원이름</option>
                                    <option value="email">E메일</option>
                                </select>
                                <input type="text" name="TDsearchValue" size="20">
                                <input type="submit" value=" 검 색 ">
                            </td>
                        </form>
                    </tr>
                </table>
            </td>
            <td align="right">
                <input type="button" value="방문수 ↑" onClick="window.location.href='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&cate=<?= $cate ?>&title_search=<?= $title_search ?>&CountWW=Logincount&s=desc';">
                <input type="button" value="방문수 ↓" onClick="window.location.href='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&cate=<?= $cate ?>&title_search=<?= $title_search ?>&CountWW=Logincount&s=asc';">
                <input type="button" value="Point ↑" onClick="window.location.href='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&cate=<?= $cate ?>&title_search=<?= $title_search ?>&CountWW=money&s=desc';">
                <input type="button" value="Point ↓" onClick="window.location.href='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&cate=<?= $cate ?>&title_search=<?= $title_search ?>&CountWW=money&s=asc';">
            </td>
        </tr>
    </table>

    <table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
        <tr>
            <td align="center">번호</td>
            <td align="center">아이디</td>
            <td align="center">회원 이름</td>
            <td align="center">방문수</td>
            <td align="center">최종방문일</td>
            <td align="center">가입날짜</td>
            <td align="center">Point</td>
            <td align="center">Level</td>
            <td align="center">관리기능</td>
        </tr>

        <?php
        include __DIR__ . "/../../db.php";
        $table = "member";

        $listcut = 30;
        if (!$offset) $offset = 0;

        if ($TDsearchValue) {
            $Mlang_query = "SELECT * FROM $table WHERE id LIKE ? OR name LIKE ? OR email LIKE ? ORDER BY no DESC LIMIT ?, ?";
            $stmt = $db->prepare($Mlang_query);
            if ($stmt === false) {
                die("쿼리 준비 중 오류 발생: " . $db->error);
            }
            $searchValue = "%" . $TDsearchValue . "%";
            $stmt->bind_param('ssiii', $searchValue, $searchValue, $searchValue, $offset, $listcut);
        } else {
            $Mlang_query = "SELECT * FROM $table ORDER BY no DESC LIMIT ?, ?";
            $stmt = $db->prepare($Mlang_query);
            if ($stmt === false) {
                die("쿼리 준비 중 오류 발생: " . $db->error);
            }
            $stmt->bind_param('ii', $offset, $listcut);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result !== false) {
                $recordsu = $result->num_rows;
            } else {
                $recordsu = 0;
            }

            if ($CountWW) {
                $orderQuery = "SELECT * FROM $table WHERE id LIKE ? OR name LIKE ? OR email LIKE ? ORDER BY $CountWW $s LIMIT ?, ?";
                $stmt = $db->prepare($orderQuery);
                if ($stmt === false) {
                    die("쿼리 준비 중 오류 발생: " . $db->error);
                }
                $stmt->bind_param('ssiii', $searchValue, $searchValue, $searchValue, $offset, $listcut);
            }

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $rows = $result->num_rows;

                if ($rows) {
                    while ($row = $result->fetch_assoc()) {
                        ?>

                        <tr bgcolor='#575757'>
                            <td align="center"><font color="white"><?= htmlspecialchars($row['no']) ?></font></td>
                            <td><a href="#" onClick="window.open('MemberImail.php?no=<?= $row['no'] ?>&code=1', 'member_iemail','width=600,height=500,top=10,left=10,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><font color="white"><?= htmlspecialchars($row['id']) ?></font></a></td>
                            <td><font color="white"><?= htmlspecialchars($row['name']) ?></font></td>
                            <td align="center"><font color="white"><?= htmlspecialchars($row['Logincount']) ?></font></td>
                            <td align="center"><font color="white"><?= htmlspecialchars($row['EndLogin']) ?></font></td>
                            <td align="center"><font color="white"><?= htmlspecialchars($row['date']) ?></font></td>
                            <td align="center">
                                <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&TDsearch=<?= $TDsearch ?>&TDsearchValue=<?= $TDsearchValue ?>">
                                        <input type="hidden" name="mode" value="PointModlfy">
                                        <input type="hidden" name="no" value="<?= $row['no'] ?>">
                                        <tr>
                                            <td align="center"><input type="text" name="money" size="7" value="<?= htmlspecialchars($row['money']) ?>"><input type="submit" value="수정"></td>
                                        </tr>
                                    </form>
                                </table>
                            </td>
                            <td align="center">
                            <script>
    function LevelModify(no, selObj) {
        var targetUrl = selObj.options[selObj.selectedIndex].value;
        parent.location.href = targetUrl; // parent window로 URL 변경
        selObj.selectedIndex = 0; // 선택된 옵션 초기화
    }
</script>
<select onChange="LevelModify(<?= $row['no'] ?>, this)">
    <option value='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&TDsearch=<?= htmlspecialchars($TDsearch) ?>&TDsearchValue=<?= htmlspecialchars($TDsearchValue) ?>&mode=LevelModify&code=2&no=<?= $row['no'] ?>' <?= $row['level'] == "2" ? "selected" : "" ?>>2 레벨-부운영자</option>
    <option value='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&TDsearch=<?= htmlspecialchars($TDsearch) ?>&TDsearchValue=<?= htmlspecialchars($TDsearchValue) ?>&mode=LevelModify&code=3&no=<?= $row['no'] ?>' <?= $row['level'] == "3" ? "selected" : "" ?>>3 레벨-골드회원</option>
    <option value='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&TDsearch=<?= htmlspecialchars($TDsearch) ?>&TDsearchValue=<?= htmlspecialchars($TDsearchValue) ?>&mode=LevelModify&code=4&no=<?= $row['no'] ?>' <?= $row['level'] == "4" ? "selected" : "" ?>>4 레벨-정회원</option>
    <option value='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?offset=<?= $offset ?>&TDsearch=<?= htmlspecialchars($TDsearch) ?>&TDsearchValue=<?= htmlspecialchars($TDsearchValue) ?>&mode=LevelModify&code=5&no=<?= $row['no'] ?>' <?= $row['level'] == "5" ? "selected" : "" ?>>5 레벨-일반회원</option>
</select>

                            </td>
                            <td align="center">
                                <input type="button" onClick="popup=window.open('admin.php?mode=view&no=<?= $row['no'] ?>', 'MemberModify','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes'); popup.focus();" value="회원정보보기">
                                <input type="button" onClick="Member_Admin_Del('<?= $row['no'] ?>');" value=" 탈퇴 ">
                            </td>
                        </tr>

                        <?php
                    }
                } else {
                    if ($search) {
                        echo "<tr><td colspan='10'><p align='center'><br><br>관련 검색 자료 없음</p></td></tr>";
                    } else if ($TDsearchValue) {
                        echo "<tr><td colspan='10'><p align='center'><br><br>$TDsearch 로 검색되는 $TDsearchValue - 관련 검색 자료 없음</p></td></tr>";
                    } else {
                        echo "<tr><td colspan='10'><p align='center'><br><br>등록 자료 없음</p></td></tr>";
                    }
                }
            } else {
                // 쿼리 실행 실패 시 오류 처리
                echo "쿼리 실행 오류: " . $db->error;
            }

            $stmt->close();
        } else {
            // 쿼리 실행 실패 시 오류 처리
            echo "쿼리 실행 오류: " . $db->error;
        }
        ?>

    </table>

    <p align="center">
        <?php
        if (isset($rows) && $rows) {
            $mlang_pagego = "CountWW=$CountWW&s=$s&cate=$cate&title_search=$title_search";
            $pagecut = 7;
            $one_bbs = $listcut * $pagecut;
            $start_offset = intval($offset / $one_bbs) * $one_bbs;
            $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
            $start_page = intval($start_offset / $listcut) + 1;
            $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

            if ($start_offset != 0) {
                $apoffset = $start_offset - $one_bbs;
                echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
            }

            for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
                $newoffset = ($i - 1) * $listcut;

                if ($offset != $newoffset) {
                    echo "&nbsp;<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
                } else {
                    echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
                }

                if ($i == $end_page) break;
            }

            if ($start_offset != $end_offset) {
                $nextoffset = $start_offset + $one_bbs;
                echo "&nbsp;<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
            }
            echo "총목록갯수: $end_page 개";
        }

        $db->close();
        ?>
    </p>

    <?php include __DIR__ . "/../down.php"; ?>
</body>
</html>
