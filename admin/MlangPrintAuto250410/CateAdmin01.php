<?php
// 관리자 로그인 및 환경설정
include "../../db.php";
include "../config.php";
include "../../MlangPrintAuto/ConDb.php";
include "CateAdmin_title.php";

// 요청 변수 초기화
$mode        = $_GET['mode'] ?? $_POST['mode'] ?? '';
$code        = $_GET['code'] ?? $_POST['code'] ?? '';
$Ttable      = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
$TreeSelect  = $_GET['TreeSelect'] ?? $_POST['TreeSelect'] ?? '';
$ACate       = $_GET['ACate'] ?? $_POST['ACate'] ?? '';
$ATreeNo     = $_GET['ATreeNo'] ?? $_POST['ATreeNo'] ?? '';
$no          = $_GET['no'] ?? $_POST['no'] ?? '';
$title       = $_POST['title'] ?? '';
$BigNo       = $_POST['BigNo'] ?? '';

$PHP_SELF    = $_SERVER['PHP_SELF'] ?? '';

// 기본 값 초기화
$View_TtableC = $View_TtableC ?? $Ttable ?? '';
$View_title   = $View_title ?? '';
$Bgcolor1     = $Bgcolor1 ?? '408080';

// 단계 텍스트
$DF_Tatle_1 = "최상위";
$DF_Tatle_2 = "중간단계";
$DF_Tatle_3 = "하위단계";

if ($mode == "form") {
    include "../title.php";
    if ($code == "modify") {
        include "CateView.php";
    }
    ?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <style>
        .Left1 {font-size:10pt; color:#FFFFFF; font-weight:bold;}
    </style>
    <script>
        window.moveTo(screen.width / 5, screen.height / 5);
        function MemberXCheckField() {
            const f = document.FrmUserXInfo;
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
<body class="coolBar" style="margin:0; padding:0;">

<form name="FrmUserXInfo" enctype="multipart/form-data" method="post" onsubmit="return MemberXCheckField()" action="<?php echo  $_SERVER['PHP_SELF'] ?>">
    <input type="hidden" name="Ttable" value="<?php echo  $Ttable ?>">
    <input type="hidden" name="TreeSelect" value="<?php echo  $TreeSelect ?>">
    <?php if ($ACate) { ?><input type="hidden" name="ACate" value="<?php echo  $ACate ?>"><?php } ?>
    <?php if ($ATreeNo) { ?><input type="hidden" name="ATreeNo" value="<?php echo  $ATreeNo ?>"><?php } ?>
    <input type="hidden" name="mode" value="<?php echo  ($code == "modify") ? "modify_ok" : "form_ok" ?>">
    <?php if ($code == "modify") { ?><input type="hidden" name="no" value="<?php echo  $no ?>"><?php } ?>

    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
        <tr>
            <td class="coolBar" colspan="4" height="25">
                <b>&nbsp;&nbsp;(<b><?php echo  $View_TtableC ?></b>)
                <?php
                if (!$TreeSelect) echo $DF_Tatle_1;
                if ($TreeSelect == "1") echo $DF_Tatle_2;
                if ($TreeSelect == "2") echo $DF_Tatle_3;
                ?>
                <?php echo  ($code == "modify") ? "수정" : "입력" ?></b><br>
            </td>
        </tr>

        <tr>
            <td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">상위메뉴&nbsp;&nbsp;</td>
            <td colspan="3">
                <select name="BigNo">
                    <?php
                    if (!$TreeSelect) {
                        echo "<option value='0'>◆ 최상의 TITLE로 등록 ◆</option>";
                    } else {
                        $stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE Ttable=? AND BigNo='0'");
                        $stmt->bind_param("s", $Ttable);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            $selected = "";
                            if ($code == "modify") {
                                if ($ACate == $row['no']) $selected = "style='background-color:green; color:#FFFFFF;' selected";
                                if ($ATreeNo == $row['no']) $selected = "style='background-color:blue; color:#FFFFFF;' selected";
                            }
                            echo "<option value='{$row['no']}' $selected>{$row['title']}</option>";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">TITLE&nbsp;&nbsp;</td>
            <td colspan="3"><input type="text" name="title" size="50" maxlength="80" value="<?php echo  ($code == "modify") ? htmlspecialchars($View_title) : "" ?>"></td>
        </tr>

        <tr>
            <td colspan="4" align="center">
                <input type="submit" value="<?php echo  ($code == "modify") ? "수정" : "저장" ?> 합니다.">
            </td>
        </tr>
    </table>
</form>

</body>
</html>
<?php
}

if ($mode == "delete") {
    $stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE no=?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['BigNo'] === "0") {
        $stmt = $mysqli->prepare("DELETE FROM $GGTABLE WHERE Ttable=? AND BigNo=?");
        $stmt->bind_param("ss", $row['Ttable'], $row['no']);
        $stmt->execute();
    }

    $stmt = $mysqli->prepare("DELETE FROM $GGTABLE WHERE no=?");
    $stmt->bind_param("i", $no);
    $stmt->execute();

    echo "<html><script>alert('$no번 자료를 삭제 처리 하였습니다.'); opener.parent.location.reload(); window.self.close();</script></html>";
    exit;
}

if ($mode == "form_ok") {
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

    echo "<script>alert('CATEGORY[$View_TtableC] 자료를 정상적으로 저장 하였습니다.'); opener.parent.location.reload();</script>";
    echo "<meta http-equiv='Refresh' content='0; URL=" . $_SERVER['PHP_SELF'] . "?mode=form&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo'>";
    exit;
}

if ($mode == "modify_ok") {
    if ($TreeSelect == "2") {
        $stmt = $mysqli->prepare("UPDATE $GGTABLE SET title=?, TreeNo=? WHERE no=?");
        $stmt->bind_param("ssi", $title, $BigNo, $no);
    } else {
        $stmt = $mysqli->prepare("UPDATE $GGTABLE SET BigNo=?, title=? WHERE no=?");
        $stmt->bind_param("ssi", $BigNo, $title, $no);
    }

    if (!$stmt->execute()) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    } else {
        echo "<script>alert('정보를 정상적으로 수정하였습니다.'); opener.parent.location.reload();</script>";
        echo "<meta http-equiv='Refresh' content='0; URL=" . $_SERVER['PHP_SELF'] . "?mode=form&code=modify&no=$no&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo'>";
        exit;
    }
}
?>
