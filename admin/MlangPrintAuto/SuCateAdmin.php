<?php
include "../../db.php";
include "../../config.php";
include "../../mlangprintauto/ConDb.php";
?>

<?php
if ($mode == "form") {

    include "../../title.php";
    $Bgcolor1 = "408080";
    if ($code == "modify") {
        include "SuCateView.php";
    }
?>
<head>
    <style>
        .Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
    </style>
    <script>
        window.moveTo(screen.width/5, screen.height/5);
        function MemberXCheckField() {
            var f = document.FrmUserXInfo;
            if (f.title.value == "") {
                alert("수량을 입력하여주세요!!");
                f.title.focus();
                return false;
            }
        }
    </script>
    <script src="../../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' onsubmit='return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
<input type="hidden" name="Ttable" value="<?php echo $Ttable?>">
<input type="hidden" name="mode" value='<?php echo ($code == "modify") ? "modify_ok" : "form_ok"; ?>'>
<?php if ($code == "modify") { ?><input type="hidden" name="no" value="<?php echo $no?>"><?php } ?>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<tr>
    <td class='coolBar' colspan=4 height=25>
        <b>&nbsp;&nbsp;(<?php echo $View_TtableC?>) 수량 <?php echo ($code == "modify") ? "수정" : "입력"; ?></b><br>
    </td>
</tr>
<tr>
    <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>수량&nbsp;&nbsp;</td>
    <td colspan=3><input type="text" name="title" size="50" maxlength="80" value='<?php if ($code == "modify") echo $View_title; ?>'></td>
</tr>
<tr>
    <td colspan=4 align=center>
        <input type='submit' value='<?php echo ($code == "modify") ? "수정" : "저장"?> 합니다.'>
    </td>
</tr>
</table>
</form>
</body>
<?php } ?>

<?php
if ($mode == "delete") {
    include "../secure_db.php";
    
    // Secure SELECT with prepared statement
    $stmt = mysqli_prepare($db, "SELECT BigNo FROM $GGTABLESu WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result);
    mysqli_stmt_close($stmt);

    if ($row && $row['BigNo'] == "0") {
        // Delete child records first
        $stmt1 = mysqli_prepare($db, "DELETE FROM $GGTABLESu WHERE BigNo = ?");
        mysqli_stmt_bind_param($stmt1, "i", $no);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);
        
        // Delete parent record
        $stmt2 = mysqli_prepare($db, "DELETE FROM $GGTABLESu WHERE no = ?");
        mysqli_stmt_bind_param($stmt2, "i", $no);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    } else {
        // Delete single record
        $stmt = mysqli_prepare($db, "DELETE FROM $GGTABLESu WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $no);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_close($db);
    echo ("<script>
        alert('" . SecurityHelper::escape($no) . " 번 자료를 삭제 처리 하였습니다.');
        opener.parent.location.reload();
        window.self.close();
    </script>");
    exit;
}
?>

<?php
if ($mode == "form_ok") {
    include "../secure_db.php";
    
    // Secure INSERT with prepared statement
    $stmt = mysqli_prepare($db, "INSERT INTO $GGTABLESu (Ttable, BigNo, title) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sis", $Ttable, $BigNo, $title);
    $result_insert = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo ("<script>
        alert('CATEGORY [" . SecurityHelper::escape($View_TtableC) . "] 자료를 정상적으로 저장하였습니다.');
        opener.parent.location.reload();
    </script>
    <meta http-equiv='Refresh' content='0; URL=" . SecurityHelper::escape($PHP_SELF) . "?mode=form&Ttable=" . SecurityHelper::escape_url($Ttable) . "'>");
    exit;
}
?>

<?php
if ($mode == "modify_ok") {
    include "../secure_db.php";
    
    // Secure UPDATE with prepared statement
    $stmt = mysqli_prepare($db, "UPDATE $GGTABLESu SET BigNo = ?, title = ? WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "isi", $BigNo, $title, $no);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    } else {
        echo ("<script>
            alert('정보를 정상적으로 수정하였습니다.');
            opener.parent.location.reload();
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no&Ttable=$Ttable'>");
        exit;
    }
    mysqli_close($db);
}
?>
