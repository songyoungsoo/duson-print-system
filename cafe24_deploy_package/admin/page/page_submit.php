<?php 
if ($mode == "form") { 
    $M123 = "..";
    include "../top.php"; 
?>

<!-- 폼 화면 -->
<table border="0" align="center" width="600" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <br><br>
            * 메뉴 이름을 정확히 입력하세요.<br>
            * 메뉴 내용은 자동으로 BR 태그가 적용되며, HTML을 입력할 때는 HTML 태그를 사용할 수 있습니다.
            <br><br>
        </td>
    </tr>

    <tr>
        <td>
            <!------------------------------------------------------------->
            <table border="0" cellpadding="5" cellspacing="0" width="571" class="coolBar">
                <tr>
                    <td>
                        <?php include "./html_edit.php"; ?>
                    </td>
                </tr>
            </table>
            <!------------------------------------------------------------->
        </td>
    </tr>
</table>

<?php 
    include "../down.php";
} 

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "ok") { 

    include "../db.php";

    // 최대 번호 조회 및 새 번호 설정
    $result = mysqli_query($db, "SELECT MAX(no) FROM $page_big_table");
    if (!$result) {
        echo "
            <script>
                window.alert('DB 조회 오류입니다!');
                history.go(-1);
            </script>";
        exit;
    }

    $row = mysqli_fetch_row($result);
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // Prepared Statement로 데이터 삽입
    $stmt = mysqli_prepare($db, "INSERT INTO $page_big_table (no, title) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $new_no, $menu); // 'no'는 정수, 'menu'는 문자열
    $result_insert = mysqli_stmt_execute($stmt);

    if (!$result_insert) {
        echo "
            <script>
                window.alert('DB 저장 오류입니다!');
                history.go(-1);
            </script>";
        exit;
    }

    // 성공 메시지 및 페이지 이동
    echo "
        <script language='javascript'>
        alert('\\n메뉴가 성공적으로 저장되었습니다.\\n\\n');
        opener.parent.location = './page_menu_list.php'; 
        window.self.close();
        </script>";
    exit;
}
?>
