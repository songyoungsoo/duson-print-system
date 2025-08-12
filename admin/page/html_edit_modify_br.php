<?php
// 데이터베이스 연결
include "../../db.php"; 

// 카테고리 선택 쿼리 실행
$stmt = mysqli_prepare($db, "SELECT * FROM $page_big_table");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows = mysqli_num_rows($result);

// 폼 처리
?>

<head>
<script type="text/javascript">
// 폼 검증 함수
function EditCheckField() {
    var f = document.EditInfo;

    if (f.cate.value == "0") {
        alert("카테고리를 선택해 주세요!");
        return false;
    }
    if (f.SUBJECT.value == "") {
        alert("제목을 입력해 주세요!");
        return false;
    }
    if (f.connent.value == "") {
        alert("내용을 입력해 주세요!");
        return false;
    }

    var winopts = "width=780,height=590,toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
    var popup = window.open('', 'POPWIN', winopts);
    popup.focus();
}
</script>
</head>

<table border="0" cellpadding="0" cellspacing="0" width="100%" class='coolBar'>
<form name='EditInfo' method='post' onSubmit='return EditCheckField()' target="POPWIN" action='./editor/submit_ok.php'>
    <input type='hidden' name='no' value='<?php echo $no; ?>'>

    <!-- 입력 방식 선택 -->
    <tr>
        <td align="center">&nbsp;형식 :&nbsp;</td>
        <td>
            <input type="radio" onClick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=br&no=$no"; ?>';" name='style' value='br' <?php if($code=="br" || $TT_style=="br") { echo "checked"; } ?>> BR 자동입력
            <input type="radio" onClick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=html&no=$no"; ?>';" name='style' value='html'> HTML 직접입력
            <input type="radio" onClick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=edit&no=$no"; ?>';" name='style' value='edit'> HTML 에디터 사용
            <input type="radio" onClick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=file&no=$no"; ?>';" name='style' value='file'> 파일(첨부)로 입력
        </td>
    </tr>

    <!-- 카테고리 및 제목 입력 -->
    <tr>
        <td align="center">&nbsp;카테고리 :&nbsp;</td>
        <td>
            <?php
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
            <input type="text" name="SUBJECT" size="40" maxlength="20" value='<?php echo $TT_title; ?>'>
        </td>
    </tr>

    <!-- 내용 입력 -->
    <tr>
        <td colspan="2" align="center">
            <textarea cols="72" name="connent" rows="30"><?php
            if ($TT_style == "br") {
                echo $TT_connent;
            }
            ?></textarea>
        </td>
    </tr>

    <!-- 버튼 -->
    <tr>
        <td colspan="2" align="center">
            <br>
            <input type="submit" name="mode" value="저장하기">
            <input type="submit" name="mode" value="BR 방식으로 저장">
            <input type="reset" value=" 다시 작성 ">
            <input type="button" value="뒤로 가기" onClick="javascript:history.back()">
            <br><br>
        </td>
    </tr>
</form>
</table>
