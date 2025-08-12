<?php
header('Content-Type: text/html; charset=utf-8');

include "../../db.php";
include "../config.php";
include "../../MlangPrintAuto/ConDb.php";

$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
$code = isset($_GET['code']) ? $_GET['code'] : (isset($_POST['code']) ? $_POST['code'] : '');
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$Ttable = isset($_GET['Ttable']) ? $_GET['Ttable'] : (isset($_POST['Ttable']) ? $_POST['Ttable'] : '');
$BigNo = isset($_POST['BigNo']) ? $_POST['BigNo'] : '';
$title = isset($_POST['title']) ? $_POST['title'] : '';

$db = new mysqli($host, $user, $password, $dataname);
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}
$db->set_charset("utf8");

if ($mode == "form") {
    include "../../title.php";

    $Bgcolor1 = "408080";

    if ($code == "modify") {
        include "SuCateView.php";
    }
    ?>

    <head>
        <style>
            .Left1 {
                font-size: 10pt;
                color: #FFFFFF;
                font: bold;
            }
        </style>
        <script language="javascript">
            window.moveTo(screen.width / 5, screen.height / 5);

            function MemberXCheckField() {
                var f = document.FrmUserXInfo;

                if (f.title.value == "") {
                    alert("제목을 입력해주세요!!");
                    f.title.focus();
                    return false;
                }
            }
        </script>
        <script src="../../js/coolbar.js" type="text/javascript"></script>
    </head>

    <body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

        <table border=0 align=center width=100% cellpadding=0 cellspacing=5>
            <form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>'>
                <INPUT TYPE="hidden" name='Ttable' value='<?php echo  htmlspecialchars($Ttable) ?>'>
                <INPUT TYPE="hidden" name='mode' value='<?php echo  $code == "modify" ? "modify_ok" : "form_ok" ?>'>
                <?php if ($code == "modify") { ?><INPUT TYPE="hidden" name='no' value='<?php echo  htmlspecialchars($no) ?>'><?php } ?>

                <tr>
                    <td class='coolBar' colspan=4 height=25>
                        <b>&nbsp;&nbsp;(<b><?php echo  htmlspecialchars($View_TtableC) ?></b>) 제목 <?php echo  $code == "modify" ? "수정" : "입력" ?></b><BR>
                    </td>
                </tr>

                <tr>
                    <td bgcolor='#<?php echo  htmlspecialchars($Bgcolor1) ?>' width=100 class='Left1' align=right>제목&nbsp;&nbsp;</td>
                    <td colspan=3><INPUT TYPE="text" NAME="title" size=50 maxLength='80' value='<?php if ($code == "modify") {
                                                                                                        echo htmlspecialchars($View_title);
                                                                                                    } ?>'></td>
                </tr>

                <tr>
                    <td colspan=4 align=center>
                        <input type='submit' value=' <?php echo  $code == "modify" ? "수정" : "등록" ?> 합니다.'>
                    </td>
                </tr>

        </table>

<?php }

if ($mode == "delete") {
    $stmt = $db->prepare("SELECT * FROM $GGTABLESu WHERE no=?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['BigNo'] == "0") {
        $stmt = $db->prepare("DELETE FROM $GGTABLESu WHERE BigNo=?");
        $stmt->bind_param("i", $no);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM $GGTABLESu WHERE no=?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
    } else {
        $stmt = $db->prepare("DELETE FROM $GGTABLESu WHERE no=?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
    }
    $stmt->close();
    $db->close();

    echo ("
<html>
<script language=javascript>
window.alert('" . htmlspecialchars($no) . " 데이터를 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
    ");
    exit;
}

if ($mode == "form_ok") {
    $stmt = $db->prepare("INSERT INTO $GGTABLESu (Ttable, BigNo, title) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $Ttable, $BigNo, $title);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo ("
    <script language=javascript>
    alert('\\nCATEGORY[" . htmlspecialchars($View_TtableC) . "] 데이터를 정상적으로 저장하였습니다.\\n');
    opener.parent.location.reload();
    </script>
<meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=form&Ttable=" . htmlspecialchars($Ttable) . "'>
    ");
    exit;
}

if ($mode == "modify_ok") {
    $stmt = $db->prepare("UPDATE $GGTABLESu SET BigNo=?, title=? WHERE no=?");
    $stmt->bind_param("isi", $BigNo, $title, $no);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo ("
    <script language=javascript>
    alert('\\n정보를 정상적으로 수정하였습니다.\\n');
    opener.parent.location.reload();
    </script>
<meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=form&code=modify&no=" . htmlspecialchars($no) . "&Ttable=" . htmlspecialchars($Ttable) . "'>
    ");
    exit;
}
?>
