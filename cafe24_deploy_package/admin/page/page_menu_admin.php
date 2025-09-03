<?php
if ($mode == "delete") {

    include "../../db.php";
    include "../config.php";

    // Prepared Statement로 SQL 인젝션 방지
    $stmt = mysqli_prepare($db, "DELETE FROM $page_big_table WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);  // no는 정수형으로 처리
    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($db);

    echo ("<script language='javascript'>
    window.alert('자료를 성공적으로 삭제하였습니다!');
    opener.parent.location=\"./page_menu_list.php\"; 
    window.self.close();
    </script>");
    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "modify") {

    include "../../db.php";
    include "../config.php";
    include "../title.php";

    // Prepared Statement로 SQL 인젝션 방지
    $stmt = mysqli_prepare($db, "SELECT * FROM $page_big_table WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = mysqli_num_rows($result);

    if ($rows) {
        while ($row = mysqli_fetch_assoc($result)) {
            $TT_no = $row['no'];
            $TT_title = $row['title'];
        }
    } else {
        echo ("<script language='javascript'>
        alert('이미 삭제되었거나 존재하지 않는 자료입니다.');
        opener.parent.location.reload();
        window.self.close();
        </script>");
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);
?>

<script language="javascript">
function AdminCheckField() {
    var f = document.AdminInfo;

    if (f.menu.value == "") {
        alert("메뉴 이름을 입력해 주세요.");
        return false;
    }
}
</script>

<table border="0" align="center" width="600" cellpadding="0" cellspacing="0">
<tr><td align="center">
<BR><BR>
* 메뉴명을 입력해 주세요 (30자 이하)
<BR><BR>

<form name="AdminInfo" method="post" onsubmit="return AdminCheckField()" action="<?php echo "$PHP_SELF"; ?>">
    <input type="hidden" name="mode" value="modify_ok">
    <input type="hidden" name="no" value="<?php echo $TT_no; ?>">

    <table border="0" align="center" width="420" cellpadding="10" cellspacing="1" bgcolor="#000000">
        <tr>
            <td bgcolor="#393839" width="100" align="center">
                <font color="#FFFFFF">메뉴명 입력:</font>
            </td>
            <td bgcolor="#FFFFFF">
                <input type="text" size="40" name="menu" maxlength="30" value="<?php echo $TT_title; ?>">
            </td>
        </tr>
    </table>

    <p align="center">
        <input type="submit" value=" 수정 완료 ">
    </p>
</form>
<BR><BR>
</td></tr>
</table>

<?php
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "modify_ok") {

    include "../../db.php";
    include "../config.php";

    // Prepared Statement로 업데이트 쿼리 실행
    $stmt = mysqli_prepare($db, "UPDATE $page_big_table SET title = ? WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "si", $menu, $no);  // title은 문자열, no는 정수형
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo "<script language='javascript'>
            window.alert('DB 업데이트에 실패했습니다!');
            history.go(-1);
        </script>";
        exit;
    } else {
        echo ("<script language='javascript'>
        alert('메뉴가 성공적으로 수정되었습니다.');
        opener.parent.location=\"./page_menu_list.php\";
        window.self.close();
        </script>");
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);
}
?>
