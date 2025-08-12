<?php
// 관리자 로그인 및 환경설정
include "../../db.php";
include "../config.php";
include "../../MlangPrintAuto/ConDb.php";
include "CateAdmin_title.php";

// 변수 초기화
$mode       = $_GET['mode'] ?? $_POST['mode'] ?? '';
$code       = $_GET['code'] ?? $_POST['code'] ?? '';
$Ttable     = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
$TreeSelect = $_GET['TreeSelect'] ?? $_POST['TreeSelect'] ?? '';
$ACate      = $_GET['ACate'] ?? $_POST['ACate'] ?? '';
$ATreeNo    = $_GET['ATreeNo'] ?? $_POST['ATreeNo'] ?? '';
$no         = $_GET['no'] ?? $_POST['no'] ?? '';
$title      = $_POST['title'] ?? '';
$BigNo      = $_POST['BigNo'] ?? $_GET['BigNo'] ?? '0';
$PHP_SELF   = htmlspecialchars($_SERVER['PHP_SELF'] ?? '');
$View_TtableC = htmlspecialchars($Ttable);
// $DF_Tatle_1 = "최상위";
// $DF_Tatle_2 = "종이규격";
// $DF_Tatle_3 = "종이종류";
$DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
$DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
$DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';
$Bgcolor1   = "408080";
$GGTABLE    = "MlangPrintAuto_" . $Ttable;
$View_title = '';

// DB 연결
$mysqli = new mysqli($host, $user, $password, $dataname);
if ($mysqli->connect_error) {
    die("DB 연결 실패: " . $mysqli->connect_error);
}

// ------------------------------------------
// 1. FORM 출력 (수정 or 입력)
// ------------------------------------------
if ($mode === "form") {
    include "../title.php";

    if ($code === "modify") {
        include "CateView.php"; // 여기서 $row['title'] 불러올 수 있음
        $View_title = htmlspecialchars($row['title'] ?? '');
    }
    ?>

    <head>
    <meta charset="UTF-8">
    <style>.Left1 {font-size:10pt; color:#FFFFFF; font-weight:bold;}</style>
    <script>
        window.moveTo(screen.width/5, screen.height/5);
        function MemberXCheckField() {
            var f = document.FrmUserXInfo;
            if (f.title.value === "") {
                alert("TITLE 을 입력해주세요.");
                f.title.focus();
                return false;
            }
            return true;
        }
    </script>
    <script src="../js/coolbar.js"></script>
    </head>

    <body class="coolBar" style="margin:0; padding:0;">
    <form name="FrmUserXInfo" method="post" onsubmit="return MemberXCheckField()" action="<?php echo  $PHP_SELF ?>">
        <input type="hidden" name="Ttable" value="<?php echo  htmlspecialchars($Ttable) ?>">
        <input type="hidden" name="TreeSelect" value="<?php echo  htmlspecialchars($TreeSelect) ?>">
        <?php if ($ACate): ?>
            <input type="hidden" name="ACate" value="<?php echo  htmlspecialchars($ACate) ?>">
        <?php endif; ?>
        <?php if ($ATreeNo): ?>
            <input type="hidden" name="ATreeNo" value="<?php echo  htmlspecialchars($ATreeNo) ?>">
        <?php endif; ?>
        <input type="hidden" name="mode" value="<?php echo  ($code === 'modify') ? 'modify_ok' : 'form_ok' ?>">
        <?php if ($code === "modify"): ?>
            <input type="hidden" name="no" value="<?php echo  (int)$no ?>">
        <?php endif; ?>

        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
        <tr>
            <td class='coolBar' colspan="4">
                <b>&nbsp;&nbsp;(<?php echo  $View_TtableC ?>) 
                <?php
                    if (!$TreeSelect) echo $DF_Tatle_1;
                    elseif ($TreeSelect == "1") echo $DF_Tatle_2;
                    elseif ($TreeSelect == "2") echo $DF_Tatle_3;
                    echo ($code === "modify") ? " 수정" : " 입력";
                ?>
                </b>
            </td>
        </tr>

        <tr>
            <td bgcolor="#<?php echo  $Bgcolor1 ?>" class="Left1" align="right">상위메뉴&nbsp;&nbsp;</td>
            <td colspan="3">
                <select name="BigNo">
                    <?php
                    if (!$TreeSelect) {
                        echo "<option value='0'>◆ 최상의 TITLE로 등록 ◆</option>";
                    } else {
                        $stmt = $mysqli->prepare("SELECT no, title FROM $GGTABLE WHERE Ttable=? AND BigNo='0'");
                        $stmt->bind_param("s", $Ttable);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($Cate_row = $result->fetch_assoc()) {
                            $selected = '';
                            if ($code === "modify") {
                                if ($ACate == $Cate_row['no']) {
                                    $selected = "style='background-color:green; color:#FFF;' selected";
                                } elseif ($ATreeNo == $Cate_row['no']) {
                                    $selected = "style='background-color:blue; color:#FFF;' selected";
                                }
                            }
                            echo "<option value='" . htmlspecialchars($Cate_row['no']) . "' $selected>" . htmlspecialchars($Cate_row['title']) . "</option>";
                        }
                        $stmt->close();
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <td bgcolor="#<?php echo  $Bgcolor1 ?>" class="Left1" align="right">TITLE&nbsp;&nbsp;</td>
            <td colspan="3">
                <input type="text" name="title" size="50" maxlength="80" value="<?php echo  $View_title ?>">
            </td>
        </tr>

        <tr>
            <td colspan="4" align="center">
                <input type="submit" value="<?php echo  ($code === 'modify') ? '수정' : '저장' ?> 합니다.">
            </td>
        </tr>
        </table>
    </form>
    </body>

    <?php
}

// ------------------------------------------
// 2. 삭제 처리
// ------------------------------------------
elseif ($mode === "delete") {
    $stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE no=?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row && $row['BigNo'] === "0") {
        $stmt = $mysqli->prepare("DELETE FROM $GGTABLE WHERE BigNo=?");
        $stmt->bind_param("s", $no);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $mysqli->prepare("DELETE FROM $GGTABLE WHERE no=?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('$no 번 자료를 삭제했습니다.'); opener.parent.location.reload(); window.close();</script>";
    exit;
}

// ------------------------------------------
// 3. 신규 등록 처리
// ------------------------------------------
elseif ($mode === "form_ok") {
    if ($TreeSelect == "1") {
        $stmt = $mysqli->prepare("INSERT INTO $GGTABLE (Ttable, BigNo, title, TreeNo) VALUES (?, ?, ?, '')");
        $stmt->bind_param("sss", $Ttable, $BigNo, $title);
    } elseif ($TreeSelect == "2") {
        $stmt = $mysqli->prepare("INSERT INTO $GGTABLE (Ttable, BigNo, title, TreeNo) VALUES (?, '', ?, ?)");
        $stmt->bind_param("sss", $Ttable, $title, $BigNo);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO $GGTABLE (Ttable, BigNo, title, TreeNo) VALUES (?, ?, ?, '')");
        $stmt->bind_param("sss", $Ttable, $BigNo, $title);
    }
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('CATEGORY [$View_TtableC] 자료를 정상적으로 저장하였습니다.'); opener.parent.location.reload();</script>";
    echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo'>";
    exit;
}

// ------------------------------------------
// 4. 수정 처리
// ------------------------------------------
elseif ($mode === "modify_ok") {
    if ($TreeSelect == "2") {
        $stmt = $mysqli->prepare("UPDATE $GGTABLE SET title=?, TreeNo=? WHERE no=?");
        $stmt->bind_param("ssi", $title, $BigNo, $no);
    } else {
        $stmt = $mysqli->prepare("UPDATE $GGTABLE SET BigNo=?, title=? WHERE no=?");
        $stmt->bind_param("ssi", $BigNo, $title, $no);
    }

    if ($stmt->execute()) {
        echo "<script>alert('정보를 정상적으로 수정하였습니다.'); opener.parent.location.reload();</script>";
        echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo'>";
    } else {
        echo "<script>alert('DB 접속 오류입니다.'); history.go(-1);</script>";
    }
    $stmt->close();
}

$mysqli->close();
?>
