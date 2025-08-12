<?php
// 데이터베이스 연결 (mysqli 사용)
include "../../db.php";
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

    // 업로드 파일 확장자 확인
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

<!-- 폼 시작 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="coolBar">
<form name="EditInfo" method="post" enctype="multipart/form-data" onsubmit="return EditCheckField()" target="POPWIN" action="./editor/submit_ok.php">
    <input type="hidden" name="no" value="<?php echo $no; ?>">

    <!-- 입력 방식 선택 -->
    <tr>
        <td align="center">&nbsp;형식 :&nbsp;</td>
        <td>
            <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=br&no=$no"; ?>';" name="style" value="br">BR 자동입력
            <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=html&no=$no"; ?>';" name="style" value="html">HTML 직접입력
            <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=edit&no=$no"; ?>';" name="style" value="edit">HTML 에디터 사용
            <input type="radio" onclick="window.location.href='<?php echo "$PHP_SELF?mode=$mode&code=file&no=$no"; ?>';" name="style" value="file" <?php if($code=="file" || $TT_style=="file") { echo "checked"; } ?>>파일(첨부)로 입력
        </td>
    </tr>

    <!-- 카테고리 및 제목 입력 -->
    <tr>
        <td align="center">&nbsp;카테고리 :&nbsp;</td>
        <td>
            <?php
            $stmt = mysqli_prepare($db, "SELECT * FROM $page_big_table");
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $rows = mysqli_num_rows($result);

            if ($rows > 0) {
                echo "<select name='cate'><option value='0'>-선택해 주세요-</option>";
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['no'] == $TT_cate) ? "selected" : "";
                    echo "<option value='{$row['no']}' $selected>{$row['title']}</option>";
                }
                echo "</select>";
            } else {
                echo "&nbsp;&nbsp;&nbsp;<b>등록된 카테고리가 없습니다.(관리자에게 문의하세요!!)</b>";
            }

            mysqli_close($db);
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;제목 :&nbsp;
            <input type="text" name="SUBJECT" size="40" maxlength="20" value="<?php echo $TT_title; ?>">
        </td>
    </tr>

    <!-- 파일 업로드 안내 -->
    <tr>
        <td align="center" colspan="2" valign="top">
            <br>* 업로드할 파일의 확장자는 php, php3, html, htm만 가능합니다.
        </td>
    </tr>

    <!-- 파일 업로드 필드 -->
    <tr>
        <td align="center">&nbsp;업로드 파일 :&nbsp;</td>
        <td>
            <input type="file" name="FILELINK" size="50">
            <?php if($TT_style == "file") { echo "<br><br><b>현재 업로드된 파일: $TT_connent</b>"; } ?>
        </td>
    </tr>

    <!-- 버튼 -->
    <tr>
        <td colspan="2" align="center"><br>
            <input type="submit" name="mode" value="저장하기">
            <input type="submit" name="mode" value="파일 업로드 방식으로 저장">
            <input type="reset" value=" 다시 작성 ">
            <input type="button" value="뒤로 가기" onclick="javascript:history.back()">
            <br><br>
        </td>
    </tr>
</form>
</table>
