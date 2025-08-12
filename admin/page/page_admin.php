<?php
if ($mode == "delete") {
    include "../../db.php";
    include "../config.php";

    // Prepared Statements로 보안 강화
    $stmt = mysqli_prepare($db, "DELETE FROM $page_table WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);  // $no 변수는 정수형으로 처리
    mysqli_stmt_execute($stmt);

    mysqli_close($db);

    echo ("<script language='javascript'>
        window.alert('자료를 삭제하였습니다. [확인]을 누르세요.');
        opener.parent.location='./page_page_list.php'; 
        window.self.close();
    </script>");
    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "modify") {
    $M123 = "..";
    include "../top.php";

    // Prepared Statements로 보안 강화
    $stmt = mysqli_prepare($db, "SELECT * FROM $page_table WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);  // $no 변수는 정수형으로 처리
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = mysqli_num_rows($result);

    if ($rows) {
        while ($row = mysqli_fetch_assoc($result)) {
            $TT_no = $row['no'];
            $TT_title = $row['title'];
            $TT_style = $row['style'];
            $TT_connent = $row['connent'];
            $TT_cate = $row['cate'];
        }
    } else {
        echo ("<script language='javascript'>
            alert('이미 삭제되었거나 존재하지 않는 자료입니다.');
            history.go(-1);
        </script>");
        exit;
    }

    mysqli_close($db);
?>

<script src="../js/coolbar.js" type="text/javascript"></script>

<table border="0" align="center" width="600" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <br><br>
            * 수정하려는 항목을 입력하시고 저장을 누르세요.<br>
            * 수정된 내용은 자동으로 저장됩니다.<br>

            <!------------------------------------------------------------->
            <table border="0" cellpadding="5" cellspacing="0" width="561" class='coolBar'>
                <tr>
                    <td>
                        <?php
                        // $code에 따라 다른 파일을 포함하여 사용
                        if ($code == "br") {
                            include "./html_edit_modify_br.php";
                        } elseif ($code == "html") {
                            include "./html_edit_modify_html.php";
                        } elseif ($code == "file") {
                            include "./html_edit_modify_file.php";
                        } elseif ($code == "edit") {
                            include "./html_edit_modify_edit.php";
                        } else {
                            include "./html_edit_modify_$TT_style.php";
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <!------------------------------------------------------------->
            <br><br>
        </td>
    </tr>
</table>

<?php
}
?>
