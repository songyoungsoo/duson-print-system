<?php
////////////////// 환경 설정 및 데이터베이스 연결 ////////////////////
include "../../db.php";
include "../config.php";
include "../../mlangprintauto/ConDb.php";
include "CateAdmin_title.php";
////////////////////////////////////////////////////
$GGTABLE = isset($_GET['GGTABLE']) ? $_GET['GGTABLE'] : (isset($_POST['GGTABLE']) ? $_POST['GGTABLE'] : '');
?>

<?php
if ($mode == "form") { ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    include "../title.php";
    $Bgcolor1 = "408080";

    if ($code == "modify") {
        include "CateView.php";
    }
    ?>

    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title>폼 수정</title>
        <style>
            .Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
        </style>
        <script language="javascript">
            window.moveTo(screen.width / 5, screen.height / 5);

            function MemberXCheckField() {
                var f = document.FrmUserXInfo;

                if (f.title.value == "") {
                    alert("TITLE을 입력해주세요!!");
                    f.title.focus();
                    return false;
                }
            }
        </script>
        <script src="../js/coolbar.js" type="text/javascript"></script>
    </head>
    <body LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0" class="coolBar">

    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
        <form name="FrmUserXInfo" enctype="multipart/form-data" method="post" onsubmit="return MemberXCheckField()" action="<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
            <input type="hidden" name="Ttable" value="<?php echo  htmlspecialchars($Ttable) ?>">
            <input type="hidden" name="TreeSelect" value="<?php echo  htmlspecialchars($TreeSelect) ?>">
            <?php if ($ACate) { ?><input type="hidden" name="ACate" value="<?php echo  htmlspecialchars($ACate) ?>"><?php } ?>
            <?php if ($ATreeNo) { ?><input type="hidden" name="ATreeNo" value="<?php echo  htmlspecialchars($ATreeNo) ?>"><?php } ?>
            <input type="hidden" name="mode" value="<?php if ($code == "modify") { ?>modify_ok<?php } else { ?>form_ok<?php } ?>">
            <?php if ($code == "modify") { ?><input type="hidden" name="no" value="<?php echo  htmlspecialchars($no) ?>"><?php } ?>

            <tr>
                <td class="coolBar" colspan="4" height="25">
                    <b>&nbsp;&nbsp;(<b><?php echo  htmlspecialchars($View_TtableC) ?></b>)
                        <?php
                        if (!$TreeSelect) {
                            echo htmlspecialchars($DF_Tatle_1);
                        }
                        if ($TreeSelect == "1") {
                            echo htmlspecialchars($DF_Tatle_2);
                        }
                        if ($TreeSelect == "2") {
                            echo htmlspecialchars($DF_Tatle_3);
                        }
                        ?>
                        <?php if ($code == "modify") { ?>수정<?php } else { ?>입력<?php } ?></b><br>
                </td>
            </tr>

            <tr>
                <td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1) ?>" width="100" class="Left1" align="right">상위메뉴&nbsp;&nbsp;</td>
                <td colspan="3">
                    <select name="BigNo">
                        <?php if (!$TreeSelect) { ?>
                            <option value="0">이 항목은 최상위 TITLE입니다.</option>
                        <?php } else { ?>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? AND BigNo = '0'");
                            $stmt->bind_param('s', $Ttable);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                while ($Cate_row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?php echo  $Cate_row['no'] ?>" <?php if ($code == "modify") {
                                        if ($ACate == $Cate_row['no']) {
                                            echo "style='background-color:green; color:#FFFFFF;' selected";
                                        }
                                        if ($ATreeNo == $Cate_row['no']) {
                                            echo "style='background-color:blue; color:#FFFFFF;' selected";
                                        }
                                    } ?>><?php echo  htmlspecialchars($Cate_row['title']) ?></option>
                                    <?php
                                }
                            }
                            $stmt->close();
                            ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1) ?>" width="100" class="Left1" align="right">TITLE&nbsp;&nbsp;</td>
                <td colspan="3"><input type="text" name="title" size="50" maxlength="80" value="<?php if ($code == "modify") { echo htmlspecialchars($View_title); } ?>"></td>
            </tr>

            <tr>
                <td colspan="4" align="center">
                    <input type="submit" value=" <?php if ($code == "modify") { ?>수정<?php } else { ?>등록<?php } ?> 합니다.">
                </td>
            </tr>

        </form>
    </table>

    <?php
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "delete") {
    $stmt = $db->prepare("SELECT * FROM $GGTABLE WHERE no = ?");
    $stmt->bind_param('i', $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['BigNo'] == "0") {
        $stmt = $db->prepare("DELETE FROM $GGTABLE WHERE BigNo = ?");
        $stmt->bind_param('i', $no);
        $stmt->execute();
        $stmt = $db->prepare("DELETE FROM $GGTABLE WHERE no = ?");
        $stmt->bind_param('i', $no);
        $stmt->execute();
    } else {
        $stmt = $db->prepare("DELETE FROM $GGTABLE WHERE no = ?");
        $stmt->bind_param('i', $no);
        $stmt->execute();
    }

    $stmt->close();

    echo ("
    <html>
    <script language=javascript>
    window.alert('$no번 자료가 삭제되었습니다.');
    opener.parent.location.reload();
    window.self.close();
    </script>
    </html>
    ");
    exit;
} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "form_ok") {
    $title = $db->real_escape_string($title);

    if ($TreeSelect == "1") {
        $stmt = $db->prepare("INSERT INTO $GGTABLE (Ttable, BigNo, title, TreeNo) VALUES (?, ?, ?, '')");
        $stmt->bind_param('sis', $Ttable, $BigNo, $title);
    } else if ($TreeSelect == "2") {
        $stmt = $db->prepare("INSERT INTO $GGTABLE (Ttable, BigNo, title, TreeNo) VALUES (?, '', ?, ?)");
        $stmt->bind_param('ssi', $Ttable, $title, $BigNo);
    } else {
        $stmt = $db->prepare("INSERT INTO $GGTABLE (Ttable, BigNo, title, TreeNo) VALUES (?, ?, ?, '')");
        $stmt->bind_param('sis', $Ttable, $BigNo, $title);
    }

    $stmt->execute();
    $stmt->close();

    echo ("
    <script language=javascript>
    alert('\\nCATEGORY[$View_TtableC] 자료를 성공적으로 등록하였습니다.\\n');
    opener.parent.location.reload();
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo'>
    ");
    exit;
} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "modify_ok") {
    $title = $db->real_escape_string($title);

    if ($TreeSelect == "2") {
        $stmt = $db->prepare("UPDATE $GGTABLE SET title = ?, TreeNo = ? WHERE no = ?");
        $stmt->bind_param('ssi', $title, $BigNo, $no);
    } else {
        $stmt = $db->prepare("UPDATE $GGTABLE SET BigNo = ?, title = ? WHERE no = ?");
        $stmt->bind_param('isi', $BigNo, $title, $no);
    }

    $stmt->execute();
    $stmt->close();

    echo ("
    <script language=javascript>
    alert('\\n데이터가 성공적으로 수정되었습니다.\\n');
    opener.parent.location.reload();
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo'>
    ");
    exit;
}
?>
