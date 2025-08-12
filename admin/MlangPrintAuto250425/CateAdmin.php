<?php
// 📌 GET 값 초기화
$code            = $_GET['code']         ?? $_POST['code']         ?? '';
$ACate           = $_GET['ACate']        ?? $_POST['ACate']        ?? '';
$ATreeNo         = $_GET['ATreeNo']      ?? $_POST['ATreeNo']      ?? '';
$TreeSelect      = $_GET['TreeSelect']   ?? $_POST['TreeSelect']   ?? '';
$mode            = $_GET['mode']         ?? $_POST['mode']         ?? '';
$Cate            = $_GET['Cate']         ?? $_POST['Cate']         ?? '';
$PageCode        = $_GET['PageCode']     ?? $_POST['PageCode']     ?? '';
$Ttable          = $_GET['Ttable']       ?? $_POST['Ttable']       ?? '';
$TIO_CODE        = $_GET['TIO_CODE']     ?? $_POST['TIO_CODE']     ?? '';
$Ttable          = $Ttable ?: $TIO_CODE; // fallback 설정
$search          = $_GET['search']       ?? $_POST['search']       ?? '';
$RadOne          = $_GET['RadOne']       ?? $_POST['RadOne']       ?? '';
$myListTreeSelect= $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList          = $_GET['myList']       ?? $_POST['myList']       ?? '';
$offset          = isset($_GET['offset']) ? (int)$_GET['offset'] : (isset($_POST['offset']) ? (int)$_POST['offset'] : 0);
$no              = isset($_GET['no']) ? (int)$_GET['no'] : (isset($_POST['no']) ? (int)$_POST['no'] : 0);
$PHP_SELF        = htmlspecialchars($_SERVER['PHP_SELF'] ?? '');


function getTtableTitle($code) {
    $titles = [
        "inserted" => "전단지",
        "NameCard" => "명함",
        "cadarok" => "리플렛",
        "msticker" => "스티커",
        "MerchandiseBond" => "상품권",
        "envelope" => "봉투",
        "NcrFlambeau" => "양식지",
        "LittlePrint" => "소량인쇄",
        "cadarokTwo" => "카다로그",
        "hakwon" => "학원",
        "food" => "음식",
        "company" => "기업체",
        "cloth" => "의류",
        "commerce" => "상업",
        "church" => "교회",
        "nonprofit" => "비영리",
        "etc" => "기타"
    ];
    return $titles[$code] ?? $code;
}

include "../title.php";
include "../../MlangPrintAuto/ConDb.php";


$View_TtableB = $Ttable;

$View_TtableC = getTtableTitle($Ttable); // 이건 실제 테이블 한글명이라면 따로 정의 필요
$PageCode = "Category";
$DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
$DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
$DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';



//타이틀 변수 초기화
$DF_Tatle_1 = $DF_Tatle_2 = $DF_Tatle_3 = '';

if (isset($TtableTitles[$Ttable])) {
    $DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
    $DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
    $DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';
}


// 관리자 로그인
include "../../db.php";
include "../config.php";
include "../../MlangPrintAuto/ConDb.php";
include "CateAdmin_title.php";

if ($mode === "form") {
    include "../title.php";
    $Bgcolor1 = "408080";

    if ($code === "modify") include "CateView.php";
?>
<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font-weight:bold;}
</style>
<script>
window.moveTo(screen.width/5, screen.height/5);

function MemberXCheckField() {
    var f = document.FrmUserXInfo;
    if (f.title.value.trim() === "") {
        alert("TITLE 을 입력하여주세요!!");
        f.title.focus();
        return false;
    }
    return true;
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body class='coolBar' style="margin:0">
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' onsubmit='return MemberXCheckField()' action='<?php echo $_SERVER["PHP_SELF"]; ?>'>
<input type="hidden" name="Ttable" value="<?php echo htmlspecialchars($Ttable); ?>">
<input type="hidden" name="TreeSelect" value="<?php echo htmlspecialchars($TreeSelect); ?>">
<?php if ($ACate): ?><input type="hidden" name="ACate" value="<?php echo htmlspecialchars($ACate); ?>"><?php endif; ?>
<?php if ($ATreeNo): ?><input type="hidden" name="ATreeNo" value="<?php echo htmlspecialchars($ATreeNo); ?>"><?php endif; ?>

<input type="hidden" name="mode" value="<?php echo $code === 'modify' ? 'modify_ok' : 'form_ok'; ?>">
<?php if ($code === "modify"): ?><input type="hidden" name="no" value="<?php echo htmlspecialchars($no); ?>"><?php endif; ?>

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
<tr>
<td class='coolBar' colspan="4" height="25">
<b>&nbsp;&nbsp;(<?php echo $View_TtableC; ?>)
<?php
echo !$TreeSelect ? $DF_Tatle_1 : ($TreeSelect === "1" ? $DF_Tatle_2 : $DF_Tatle_3);
echo $code === "modify" ? "수정" : "입력";
?>
</b><br>
</td>
</tr>

<tr>
<td bgcolor="#<?php echo $Bgcolor1; ?>" width="100" class="Left1" align="right">상위메뉴&nbsp;&nbsp;</td>
<td colspan="3">
<select name="BigNo">
<?php if (!$TreeSelect): ?>
    <option value="0">◆ 최상의 TITLE로 등록 ◆</option>
<?php else:
    $db = mysqli_connect($host, $user, $password, $dataname);
    // $CAT_TABLE = 'mlangprintauto_transactioncate';
    $stmt = mysqli_prepare($db, "SELECT no, title FROM $GGTABLE WHERE Ttable=? AND BigNo='0'");
    mysqli_stmt_bind_param($stmt, "s", $Ttable);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $selStyle = '';
        if ($code === 'modify') {               // 수정 모드
            if ($ACate   == $row['no']) $selStyle = "selected style='background-color:green; color:#FFF;'";
            if ($ATreeNo == $row['no']) $selStyle = "selected style='background-color:blue; color:#FFF;'";
        } else {                                // 신규 입력(form) 모드
            if ($TreeSelect == $row['no']) $selStyle = "selected";
        }
        /* ------------------------------------------------------ */
        
        echo "<option value='{$row['no']}' {$selStyle}>"
           .  htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8')
           .  "</option>";
    }
    mysqli_stmt_close($stmt);
endif;
?>
</select>
</td>
</tr>

<tr>
<td bgcolor="#<?php echo $Bgcolor1; ?>" width="100" class="Left1" align="right">TITLE&nbsp;&nbsp;</td>
<td colspan="3">
<input type="text" name="title" size="50" maxlength="80" value="<?php echo $code === "modify" ? htmlspecialchars($View_title) : ''; ?>">
</td>
</tr>

<tr>
<td colspan="4" align="center">
<input type="submit" value="<?php echo $code === "modify" ? "수정" : "저장"; ?> 합니다.">
</td>
</tr>
</table>
</form>
</body>
<?php
} // end of form mode

elseif ($mode === "delete") {
    $stmt = mysqli_prepare($db, "SELECT BigNo FROM $GGTABLE WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "s", $no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row && $row['BigNo'] == "0") {
        $stmt1 = mysqli_prepare($db, "DELETE FROM $GGTABLE WHERE BigNo = ?");
        mysqli_stmt_bind_param($stmt1, "s", $no);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);
    }

    $stmt2 = mysqli_prepare($db, "DELETE FROM $GGTABLE WHERE no = ?");
    mysqli_stmt_bind_param($stmt2, "s", $no);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    mysqli_close($db);

    echo <<<HTML
<script>
    alert("{$no}번 자료를 삭제 처리 하였습니다.");
    opener.parent.location.reload();
    window.close();
</script>
HTML;
    exit;
}

elseif ($mode === "form_ok") {
    $stmt = mysqli_prepare($db, "INSERT INTO $GGTABLE (Ttable, BigNo, title, TreeNo) VALUES (?, ?, ?, ?)");

    if ($TreeSelect === "1") {
        $TreeNo = '';
        mysqli_stmt_bind_param($stmt, "ssss", $Ttable, $BigNo, $title, $TreeNo);
    } elseif ($TreeSelect === "2") {
        $BigNoEmpty = '';
        mysqli_stmt_bind_param($stmt, "ssss", $Ttable, $BigNoEmpty, $title, $BigNo);
    } else {
        $TreeNo = '';
        mysqli_stmt_bind_param($stmt, "ssss", $Ttable, $BigNo, $title, $TreeNo);
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo <<<HTML
<script>
    alert("\\nCATEGORY [$View_TtableC] 자료를 정상적으로 저장 하였습니다.\\n");
    opener.parent.location.reload();
</script>
<meta http-equiv="refresh" content="0;URL={$_SERVER['PHP_SELF']}?mode=form&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo">
HTML;
    exit;
}

elseif ($mode === "modify_ok") {
    if ($TreeSelect === "2") {
        $stmt = mysqli_prepare($db, "UPDATE $GGTABLE SET title = ?, TreeNo = ? WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "sss", $title, $BigNo, $no);
    } else {
        $stmt = mysqli_prepare($db, "UPDATE $GGTABLE SET BigNo = ?, title = ? WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "sss", $BigNo, $title, $no);
    }

    if (!mysqli_stmt_execute($stmt)) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);

    echo <<<HTML
<script>
    alert("\\n정보를 정상적으로 수정하였습니다.\\n");
    opener.parent.location.reload();
</script>
<meta http-equiv="refresh" content="0;URL={$_SERVER['PHP_SELF']}?mode=form&code=modify&no=$no&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo">
HTML;
    exit;
}
?>
