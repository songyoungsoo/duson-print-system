<?php
// HTML 에디터 사용 중일 경우 경고 및 리다이렉트 처리
if($TT_style == "edit"){
    echo ("
    <script type='text/javascript'>
    alert('Html 에디터로 작성된 파일은 html 코드만 수정 가능합니다.');
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=$mode&code=html&no=$no'>
    ");
    exit;
}
?>

<!-- JavaScript와 에디터 불러오기 -->
<script type="text/javascript" src='./editor/editor.js'></script>

<!-- 폼 시작 -->
<form name="mailsendform" method="POST" enctype="multipart/form-data">
<input type="hidden" name="return_url" value="<?php echo "$PHP_SELF?mode=$mode&code=edit"; ?>">
<input type="hidden" name="no" value="<?php echo $no; ?>">
<input type="hidden" name="CONTENT" value="">

<!-- 테이블 시작 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" class='coolBar'>
    <!-- 입력 방식 선택 -->
    <tr>
        <td align="center">&nbsp;형식 :&nbsp;</td>
        <td>
            <input type="radio" onClick="javascript:window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=br&no=$no"; ?>';" name="style" value="br">BR 자동입력
            <input type="radio" onClick="javascript:window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=html&no=$no"; ?>';" name="style" value="html">HTML 직접입력
            <input type="radio" onClick="javascript:window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=edit&no=$no"; ?>';" name="style" value="edit" <?php if($code=="edit" || $TT_style=="edit") { echo "checked"; } ?>>HTML 에디터 사용
            <input type="radio" onClick="javascript:window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=file&no=$no"; ?>';" name="style" value="file">파일(첨부)로 입력
        </td>
    </tr>

    <!-- 카테고리 및 제목 입력 -->
    <tr>
        <td align="center">&nbsp;카테고리 :&nbsp;</td>
        <td>
        <?php
        include "../../db.php";
        $stmt = mysqli_prepare($db, "SELECT * FROM $page_big_table");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = mysqli_num_rows($result);

        if ($rows > 0) {
            echo "<select name='cate'><option value='0'>-선택해 주세요-</option>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['no']}' ".($row['no'] == $TT_cate ? "selected" : "").">{$row['title']}</option>";
            }
            echo "</select>";
        } else {
            echo "&nbsp;&nbsp;&nbsp;<b>등록된 카테고리가 없습니다.(관리자에게 문의하세요!)</b>";
        }

        mysqli_close($db);
        ?>
        &nbsp;&nbsp;&nbsp;&nbsp;제목 :&nbsp;
        <input type="text" name="SUBJECT" size="40" maxlength="20" value="<?php echo $TT_title; ?>">
        </td>
    </tr>

    <!-- 에디터 영역 -->
    <tr>
        <td colspan="2" align="center">
            <iframe name="editor" src="editor/editor.html" marginheight="0" marginwidth="0" frameborder="0" width="100%" height="450" scrolling="yes"></iframe>
        </td>
    </tr>

    <!-- 버튼 -->
    <tr>
        <td colspan="2" align="center"><br>
            <input type="hidden" name="mode" value="저장하기">
            <input type="button" value="저장하기" onClick="javascript:return jsSubmit('');">
            <input type="button" value="Edit에서 미리보기" onClick="javascript:return jsPreview();">
            <input type="reset" value=" 다시 작성 ">
            <input type="button" value="뒤로 가기" onClick="javascript:history.back();">
            <br><br>
        </td>
    </tr>
</table>
</form>
