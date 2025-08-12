<?php
if ($code == "br") { ///////////////////////////////////////////////////////////////////////////////////////////////
?>

<head>
<script type="text/javascript">
// 폼 유효성 검사 함수
function EditCheckField() {
    var f = document.EditInfo;

    // 카테고리 선택 확인
    if (f.cate.value == "0") {
        alert("카테고리를 선택해 주세요!!");
        return false;
    }

    // 제목 입력 확인
    if (f.SUBJECT.value == "") {
        alert("제목을 입력해 주세요!!");
        return false;
    }

    // 내용 입력 확인
    if (f.connent.value == "") {
        alert("내용을 입력해 주세요!!");
        return false;
    }

    // 팝업 창 열기
    var winopts = "width=780,height=590,toolbar=no,location=no,directories=no, status=yes,menubar=no,scrollbars=no,resizable=yes";
    var popup = window.open('', 'POPWIN', winopts);
    popup.focus();
}
</script>
</head>

<!-- BR 자동 입력 폼 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="coolBar">
<form name="EditInfo" method="post" onsubmit="return EditCheckField()" target="POPWIN" action="./editor/submit_ok.php">

<tr>
<td align="center">&nbsp;형식 :&nbsp;</td>
<td>
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=br&no=$no"; ?>';" name="style" value="br" <?php if ($code == "br") { echo "checked"; } ?>>BR 자동입력
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=html&no=$no"; ?>';" name="style" value="html" <?php if ($code == "html") { echo "checked"; } ?>>HTML 직접입력
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=edit&no=$no"; ?>';" name="style" value="edit" <?php if ($code == "edit") { echo "checked"; } ?>>HTML 에디터 사용
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=file&no=$no"; ?>';" name="style" value="file" <?php if ($code == "file") { echo "checked"; } ?>>파일(첨부)로 입력
</td>
</tr>

<tr>
<td align="center">&nbsp;카테고리 :&nbsp;</td>
<td>
    <?php
    include "../../db.php";
    $result = mysqli_query($db, "SELECT * FROM $page_big_table");
    $rows = mysqli_num_rows($result);

    if ($rows) {
        echo "<select name='cate'><option value='0'>-선택해 주세요-</option>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='{$row['no']}'>{$row['title']}</option>";
        }
        echo "</select>";
    } else {
        echo "&nbsp;&nbsp;&nbsp;<b>등록된 카테고리가 없습니다.(관리자에게 문의하세요!!)</b>";
    }

    mysqli_close($db);
    ?>
    &nbsp;&nbsp;&nbsp;&nbsp;제목 :&nbsp;
    <input type="text" name="SUBJECT" size="40" maxlength="20">
</td>
</tr>

<tr>
<td colspan="2" align="center">
    <textarea cols="72" name="connent" rows="30"></textarea>
</td>
</tr>

<tr>
<td colspan="2" align="center"><br>
    <input type="submit" name="mode" value="저장하기">
    <input type="submit" name="mode" value="BR 방식으로 저장">
    <input type="reset" value=" 다시 작성 ">
    <input type="button" value="뒤로 가기" onclick="javascript:history.back();">
    <br><br>
</td>
</tr>

</table>
</form>

<?php
} else if ($code == "html") { ////////////////////////////////////////////////////////////////////////////////////
?>

<head>
<script type="text/javascript">
// 폼 유효성 검사 함수
function EditCheckField() {
    var f = document.EditInfo;

    // 카테고리 선택 확인
    if (f.cate.value == "0") {
        alert("카테고리를 선택해 주세요!!");
        return false;
    }

    // 제목 입력 확인
    if (f.SUBJECT.value == "") {
        alert("제목을 입력해 주세요!!");
        return false;
    }

    // 내용 입력 확인
    if (f.connent.value == "") {
        alert("내용을 입력해 주세요!!");
        return false;
    }

    // 팝업 창 열기
    var winopts = "width=780,height=590,toolbar=no,location=no,directories=no, status=yes,menubar=no,scrollbars=no,resizable=yes";
    var popup = window.open('', 'POPWIN', winopts);
    popup.focus();
}
</script>
</head>

<!-- HTML 직접 입력 폼 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="coolBar">
<form name="EditInfo" method="post" onsubmit="return EditCheckField()" target="POPWIN" action="./editor/submit_ok.php">

<tr>
<td align="center">&nbsp;형식 :&nbsp;</td>
<td>
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=br&no=$no"; ?>';" name="style" value="br" <?php if ($code == "br") { echo "checked"; } ?>>BR 자동입력
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=html&no=$no"; ?>';" name="style" value="html" <?php if ($code == "html") { echo "checked"; } ?>>HTML 직접입력
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=edit&no=$no"; ?>';" name="style" value="edit" <?php if ($code == "edit") { echo "checked"; } ?>>HTML 에디터 사용
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=file&no=$no"; ?>';" name="style" value="file" <?php if ($code == "file") { echo "checked"; } ?>>파일(첨부)로 입력
</td>
</tr>

<tr>
<td align="center">&nbsp;카테고리 :&nbsp;</td>
<td>
    <?php
    include "../../db.php";
    $result = mysqli_query($db, "SELECT * FROM $page_big_table");
    $rows = mysqli_num_rows($result);

    if ($rows) {
        echo "<select name='cate'><option value='0'>-선택해 주세요-</option>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='{$row['no']}'>{$row['title']}</option>";
        }
        echo "</select>";
    } else {
        echo "&nbsp;&nbsp;&nbsp;<b>등록된 카테고리가 없습니다.(관리자에게 문의하세요!!)</b>";
    }

    mysqli_close($db);
    ?>
    &nbsp;&nbsp;&nbsp;&nbsp;제목 :&nbsp;
    <input type="text" name="SUBJECT" size="40" maxlength="20">
</td>
</tr>

<tr>
<td colspan="2" align="center">
    <textarea cols="72" name="connent" rows="30"></textarea>
</td>
</tr>

<tr>
<td colspan="2" align="center"><br>
    <input type="submit" name="mode" value="저장하기">
    <input type="submit" name="mode" value="HTML 방식으로 저장">
    <input type="reset" value=" 다시 작성 ">
    <input type="button" value="뒤로 가기" onclick="javascript:history.back();">
    <br><br>
</td>
</tr>

</table>
</form>

<?php
} else if ($code == "file") { //////////////////////////////////////////////////////////////////////////////////////
?>

<head>
<script type="text/javascript">
// 폼 유효성 검사 함수
function EditCheckField() {
    var f = document.EditInfo;

    // 카테고리 선택 확인
    if (f.cate.value == "0") {
        alert("카테고리를 선택해 주세요!!");
        return false;
    }

    // 제목 입력 확인
    if (f.SUBJECT.value == "") {
        alert("제목을 입력해 주세요!!");
        return false;
    }

    // 파일 업로드 확인
    if (f.FILELINK.value == "") {
        alert("업로드할 파일을 선택해 주세요!!");
        return false;
    }

    // 파일 확장자 확인
    var allowedExtensions = [".php", ".php3", ".htm", ".html"];
    var fileExtension = f.FILELINK.value.substring(f.FILELINK.value.lastIndexOf('.')).toLowerCase();
    if (!allowedExtensions.includes(fileExtension)) {
        alert("업로드할 파일의 확장자가 허용되지 않습니다.\n\n다시 선택해 주세요.");
        return false;
    }

    // 팝업 창 열기
    var winopts = "width=780,height=590,toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
    var popup = window.open('', 'POPWIN', winopts);
    popup.focus();
}
</script>
</head>

<!-- 파일 첨부 입력 폼 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="coolBar">
<form name="EditInfo" method="post" enctype="multipart/form-data" onsubmit="return EditCheckField()" target="POPWIN" action="./editor/submit_ok.php">

<tr>
<td align="center">&nbsp;형식 :&nbsp;</td>
<td>
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=br&no=$no"; ?>';" name="style" value="br" <?php if ($code == "br") { echo "checked"; } ?>>BR 자동입력
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=html&no=$no"; ?>';" name="style" value="html" <?php if ($code == "html") { echo "checked"; } ?>>HTML 직접입력
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=edit&no=$no"; ?>';" name="style" value="edit" <?php if ($code == "edit") { echo "checked"; } ?>>HTML 에디터 사용
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=file&no=$no"; ?>';" name="style" value="file" <?php if ($code == "file") { echo "checked"; } ?>>파일(첨부)로 입력
</td>
</tr>

<tr>
<td align="center">&nbsp;카테고리 :&nbsp;</td>
<td>
    <?php
    include "../../db.php";
    $result = mysqli_query($db, "SELECT * FROM $page_big_table");
    $rows = mysqli_num_rows($result);

    if ($rows) {
        echo "<select name='cate'><option value='0'>-선택해 주세요-</option>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='{$row['no']}'>{$row['title']}</option>";
        }
        echo "</select>";
    } else {
        echo "&nbsp;&nbsp;&nbsp;<b>등록된 카테고리가 없습니다.(관리자에게 문의하세요!!)</b>";
    }

    mysqli_close($db);
    ?>
    &nbsp;&nbsp;&nbsp;&nbsp;제목 :&nbsp;
    <input type="text" name="SUBJECT" size="40" maxlength="20">
</td>
</tr>

<tr>
<td align="center" colspan="2"><br>
    * 업로드할 파일의 확장자는 php, php3, html, htm만 가능합니다.
</td>
</tr>

<tr>
<td align="center">&nbsp;업로드 파일 :&nbsp;</td>
<td>
    <input type="file" name="FILELINK" size="50">
</td>
</tr>

<tr>
<td colspan="2" align="center"><br>
    <input type="submit" name="mode" value="저장하기">
    <input type="submit" name="mode" value="파일 업로드 방식으로 저장">
    <input type="reset" value=" 다시 작성 ">
    <input type="button" value="뒤로 가기" onclick="javascript:history.back();">
    <br><br>
</td>
</tr>

</table>
</form>

<?php
} else if ($code == "edit") {  /////////////////////////////////////////////////////////////////////////////////////
?>

<!-- 에디터 사용 입력 폼 -->
<script type="text/javascript" src="./editor/editor.js"></script>

<form name="mailsendform" method="post" enctype="multipart/form-data">
<input type="hidden" name="return_url" value="/admin/page_submit.php?mode=form&code=edit">
<input type="hidden" name="CONTENT" value="">

<table border="0" cellpadding="0" cellspacing="0" width="100%" class="coolBar">
<tr>
<td align="center">&nbsp;형식 :&nbsp;</td>
<td>
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=br&no=$no"; ?>';" name="style" value="br" <?php if ($code == "br") { echo "checked"; } ?>>BR 자동입력
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=html&no=$no"; ?>';" name="style" value="html" <?php if ($code == "html") { echo "checked"; } ?>>HTML 직접입력
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=edit&no=$no"; ?>';" name="style" value="edit" <?php if ($code == "edit") { echo "checked"; } ?>>HTML 에디터 사용
    <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=file&no=$no"; ?>';" name="style" value="file" <?php if ($code == "file") { echo "checked"; } ?>>파일(첨부)로 입력
</td>
</tr>

<tr>
<td align="center">&nbsp;카테고리 :&nbsp;</td>
<td>
    <?php
    include "../../db.php";
    $result = mysqli_query($db, "SELECT * FROM $page_big_table");
    $rows = mysqli_num_rows($result);

    if ($rows) {
        echo "<select name='cate'><option value='0'>-선택해 주세요-</option>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='{$row['no']}'>{$row['title']}</option>";
        }
        echo "</select>";
    } else {
        echo "&nbsp;&nbsp;&nbsp;<b>등록된 카테고리가 없습니다.(관리자에게 문의하세요!!)</b>";
    }

    mysqli_close($db);
    ?>
    &nbsp;&nbsp;&nbsp;&nbsp;제목 :&nbsp;
    <input type="text" name="SUBJECT" size="40" maxlength="20">
</td>
</tr>

<tr>
<td colspan="2" align="center">
    <iframe name="editor" src="editor/editor.html" marginheight="0" marginwidth="0" frameborder="0" width="100%" height="450" scrolling="yes"></iframe>
</td>
</tr>

<tr>
<td colspan="2" align="center"><br>
    <input type="hidden" name="mode" value="저장하기">
    <input type="button" value="저장하기" onclick="javascript:return jsSubmit('');">
    <input type="button" value="미리보기" onclick="javascript:return jsPreview();">
    <input type="reset" value=" 다시 작성 ">
    <input type="button" value="뒤로 가기" onclick="javascript:history.back();">
    <br><br>
</td>
</tr>

</table>
</form>

<?php
} else {
    echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=$mode&code=br'>";
}
?>
