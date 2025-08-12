<?php 
if ($mode == "form") { // 메뉴 입력 폼
    include "../title.php"; 
?>

<script language="javascript">
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

// 문자열의 각 문자가 허용된 범위 내에 있는지 확인
function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i + 1)) < 0) {
            return false;
        }
    }
    return true;
}

// 입력 필드 유효성 검사
function AdminCheckField() {
    var f = document.AdminInfo;

    if (f.menu.value == "") {
        alert("메뉴 이름을 입력해 주세요.");
        return false;
    }

    return true;
}
</script>

<table border="0" align="center" width="600" cellpadding="0" cellspacing="0">
    <tr><td align="center">
        <br><br>
        * 메뉴 이름을 입력하세요 (30자 이내)
        <br><br>
        
        <form name="AdminInfo" method="post" onsubmit="return AdminCheckField()" action="<?php echo $PHP_SELF; ?>">
            <input type="hidden" name="mode" value="ok">

            <table border="0" align="center" width="420" cellpadding="10" cellspacing="1" bgcolor="#000000">
                <tr>
                    <td bgcolor="#393839" width="100" align="center">
                        <font color="#FFFFFF">메뉴 이름 입력:</font>
                    </td>
                    <td bgcolor="#FFFFFF">
                        <input type="text" size="40" name="menu" maxlength="30">
                    </td>
                </tr>
            </table>

            <p align="center">
                <input type="submit" value=" 저장 ">
            </p>
        </form>
        <br><br>
    </td></tr>
</table>

<?php 
} // 끝 if(mode == "form")

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "ok") { // 메뉴 저장 처리

    include "../../db.php";

    // 최대 번호 조회
    $query = "SELECT MAX(no) FROM $page_big_table";
    $result = mysqli_query($db, $query);

    if (!$result) {
        echo "<script>
                window.alert('DB 조회 오류입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    $row = mysqli_fetch_row($result);
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 데이터 삽입 준비
    $stmt = mysqli_prepare($db, "INSERT INTO $page_big_table (no, title) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $new_no, $menu); // 번호는 정수, 메뉴명은 문자열

    $result_insert = mysqli_stmt_execute($stmt);

    if (!$result_insert) {
        echo "<script>
                window.alert('DB 저장 오류입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    // 성공 메시지 및 페이지 이동
    echo "<script language='javascript'>
            alert('\\n메뉴가 성공적으로 저장되었습니다.\\n\\n');
            opener.parent.location = './page_menu_list.php'; 
            window.self.close();
          </script>";
    exit;
}
?>
