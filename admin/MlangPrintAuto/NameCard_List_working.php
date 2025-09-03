<?php
// 단순하고 확실한 NameCard 리스트 페이지
declare(strict_types=1);
error_reporting(E_ALL);
ini_set("display_errors", "1");

// 변수 설정
$TIO_CODE = "namecard";
$table = "MlangPrintAuto_{$TIO_CODE}";
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : (isset($_POST['offset']) ? (int)$_POST['offset'] : 0);
$PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF']);

// 관리자 인증
include "../config.php";

// 직접 데이터베이스 연결 ($db 변수 사용 - 기존 시스템 호환)
$db = mysqli_connect("localhost", "duson1830", "du1830", "duson1830");
if (!$db) {
    // root 계정으로 재시도
    $db = mysqli_connect("localhost", "root", "", "duson1830");
    if (!$db) {
        die("DB 연결 실패: " . mysqli_connect_error());
    }
}
mysqli_set_charset($db, "utf8");

// 삭제 처리
if ($mode == "delete" && $no) {
    $stmt = mysqli_prepare($db, "DELETE FROM $table WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo "<script>
        alert('테이블명: $table - $no 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
    </script>";
    exit;
}

// ConDb.php 설정
include "../../mlangprintauto/ConDb.php";

// 쿼리 작성
$Mlang_query = "";
if ($search === "yes" && $RadOne && $myList) {
    $Mlang_query = "SELECT * FROM $table WHERE style='" . mysqli_real_escape_string($db, $RadOne) . "' AND Section='" . mysqli_real_escape_string($db, $myList) . "'";
} else {
    $Mlang_query = "SELECT * FROM $table";
}

$query = mysqli_query($db, $Mlang_query);
$recordsu = $query ? mysqli_num_rows($query) : 0;
$total = $recordsu;
$listcut = 15;
if (!$offset) $offset = 0;
?>
<!DOCTYPE html>
<html>
<head>
<title>MlangWeb관리프로그램(3.2) - 명함 관리</title>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<style>
body,td,input,select{color:#000000; font-size:9pt; font-family:굴림; line-height:130%; word-break:break-all;}
a:link    {font-size:9pt; font-family:굴림,Tahoma; text-decoration:none;}
a:visited {font-size:9pt; font-family:굴림,Tahoma; text-decoration:none;}
a:hover   {font-size:9pt; font-family:굴림,Tahoma; text-decoration:underline;}
td, table{border-color:#000000; border-collapse:collapse; color:#000000; font-size:10pt; font-family:굴림; line-height:130%; word-break:break-all;}
.coolBar{background-color:#f5f5f5; border:1px solid #cccccc;}
</style>
<script>
function WomanMember_Admin_Del(no) {
    if (confirm(no + '번 자료를 삭제 처리 하시겠습니까..?\\n\\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
        let str = '<?php echo $PHP_SELF ?>?no=' + no + '&mode=delete';
        let popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php include "ListSearchBox.php"; ?>
</td>
<td align=right>
<input type='button' onclick="window.open('CateList.php?Ttable=<?php echo $TIO_CODE ?>&TreeSelect=ok')" value=' 구분 관리 '>
<input type='button' onclick="window.open('<?php echo $TIO_CODE ?>_admin.php?mode=IncForm')" value=' 가격/설명 관리 '>
<input type='button' onclick="window.open('<?php echo $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo $TIO_CODE ?>')" value=' 신 자료 입력 '>
<br><br>
전체자료수-<b style='color:blue;'><?php echo $total ?></b>개
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<th>등록번호</th>
<th>명함종류</th>
<th>명함재질</th>
<th>인쇄면</th>
<th>수량</th>
<th>가격</th>
<th>디자인비</th>
<th>관리기능</th>
</tr>

<?php
$result = mysqli_query($db, $Mlang_query . " ORDER BY NO DESC LIMIT $offset, $listcut");
if ($result && mysqli_num_rows($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr bgcolor='#575757'>";
        echo "<td align=center><font color=white>{$row['no']}</font></td>";

        $styleTitle = '';
        $resStyle = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['style']}'");
        if ($resStyle && $r = mysqli_fetch_assoc($resStyle)) {
            $styleTitle = $r['title'];
        }

        $sectionTitle = '';
        $resSection = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['Section']}'");
        if ($resSection && $r = mysqli_fetch_assoc($resSection)) {
            $sectionTitle = $r['title'];
        }

        $poType = $row['POtype'] == "2" ? "양면" : "단면";
        $money = number_format((int)$row['money']);
        $designMoney = number_format((int)$row['DesignMoney']);

        echo "<td align=center><font color=white>{$styleTitle}</font></td>";
        echo "<td align=center><font color=white>{$sectionTitle}</font></td>";
        echo "<td align=center><font color=white>{$poType}</font></td>";
        echo "<td align=center><font color=white>{$row['quantity']}매</font></td>";
        echo "<td align=center><font color=white>{$money}원</font></td>";
        echo "<td align=center><font color=white>{$designMoney}원</font></td>";
        echo "<td align=center>";
        echo "<input type='button' value=' 수정 ' onclick=\"window.open('{$TIO_CODE}_admin.php?mode=form&code=Modify&no={$row['no']}&Ttable={$TIO_CODE}')\">";
        echo "<input type='button' value=' 삭제 ' onclick=\"WomanMember_Admin_Del('{$row['no']}')\">";
        echo "</td></tr>";
    }
} else {
    echo "<tr><td colspan=8 align=center><br><br>등록 자료없음</td></tr>";
}
?>
</table>

<p align="center">
<?php
$pagecut = 7;
$one_bbs = $listcut * $pagecut;
$start_offset = intval($offset / $one_bbs) * $one_bbs;
$end_offset = intval($recordsu / $one_bbs) * $one_bbs;
$start_page = intval($start_offset / $listcut) + 1;
$end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

$mlang_pagego = ($search === "yes") ? "search=$search&RadOne=$RadOne&myList=$myList" : "";

if ($start_offset != 0) {
    echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=" . ($start_offset - $one_bbs) . "&$mlang_pagego'>...[이전]</a>&nbsp;";
}

for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
    $newoffset = ($i - 1) * $listcut;
    if ($offset != $newoffset) {
        echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
    } else {
        echo "<font style='font-weight:bold; color:green;'>($i)</font>&nbsp;";
    }
    if ($i == $end_page) break;
}

if ($start_offset != $end_offset) {
    echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=" . ($start_offset + $one_bbs) . "&$mlang_pagego'>[다음]...</a>";
}
echo "총목록갯수: $end_page 개";
?>
</p>

</body>
</html>