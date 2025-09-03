<?php
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// 페이지 수정 처리
if ($mode == "수정하기") {
    include "../../../db.php"; // DB 연결 파일

    // 스타일이 파일인 경우 파일 업로드 처리
    if ($style == "file") {
        $tty = "..";
        include "../upload/data.php"; // 파일 업로드 처리
        $query = "UPDATE $page_table SET title='$SUBJECT', connent='$FILELINK_ok', style='$style', cate='$cate' WHERE no='$no'";
    } 
    // 에디터로 수정하는 경우
    else if ($style == "edit") {
        $query = "UPDATE $page_table SET title='$SUBJECT', connent='$CONTENT', style='$style', cate='$cate' WHERE no='$no'";
    } 
    // 일반 텍스트 수정하는 경우
    else {
        $query = "UPDATE $page_table SET title='$SUBJECT', connent='$connent', style='$style', cate='$cate' WHERE no='$no'";
    }

    $result = mysqli_query($db, $query); // 쿼리 실행
    if (!$result) { // 오류 처리
        echo "
        <script language=javascript>
            window.alert(\"DB 접속 에러입니다!\")
            history.go(-1);
        </script>";
        exit;
    } else {
        // 에디터로 수정한 경우
        if ($style == "edit") {
            echo ("
            <script language=javascript>
                alert('\\n정보를 정상적으로 [수정]하였습니다.\\n');
            </script>
            <meta http-equiv='Refresh' content='0; URL=../page_admin.php?mode=modify&no=$no'>
            ");
            exit;
        } 
        // 그 외 수정 처리
        else {
            echo ("
            <script language=javascript>
                alert('\\n정보를 정상적으로 [수정]하였습니다.\\n');
                opener.parent.location=\"../page_admin.php?mode=modify&no=$no\"; 
                window.self.close();
            </script>");
            exit;
        }
    }

    mysqli_close($db); // DB 연결 종료
}

// 페이지 저장 처리
if ($mode == "저장하기") { 
    include "../../../db.php"; // DB 연결 파일 포함

    // 새로운 페이지 번호 생성
    $result = mysqli_query($db, "SELECT max(no) FROM $page_table");
    if (!$result) { // 오류 발생 시
        echo "
        <script>
            window.alert(\"DB 접속 에러입니다!\")
            history.go(-1);
        </script>";
        exit;
    }
    $row = mysqli_fetch_row($result);
    $new_no = $row[0] ? $row[0] + 1 : 1; // 새로운 글 번호 생성

    // 스타일이 파일인 경우 파일 업로드 처리
    if ($style == "file") {
        $tty = "..";
        include "../upload/data.php"; // 파일 업로드 처리

        // 파일 정보 저장
        $dbinsert = "insert into $page_table values('$new_no', '$SUBJECT', '$FILELINK_ok', '$style', '$cate')";
        $result_insert = mysqli_query($db, $dbinsert);
    } 
    // 에디터로 저장하는 경우
    else if ($style == "edit") {
        // 에디터 정보 저장
        $dbinsert = "insert into $page_table values('$new_no', '$SUBJECT', '$CONTENT', '$style', '$cate')";
        $result_insert = mysqli_query($db, $dbinsert);

        // 완료 메시지를 보인 후 페이지 이동
        echo ("
        <script language=javascript>
            alert('\\n정상적으로 페이지 정보를 저장 시켰습니다.\\n\\n');
        </script>
        <meta http-equiv='Refresh' content='0; URL=../page_submit.php?mode=form'>
        ");
        exit;
    } 
    // 그 외의 경우
    else {
        // 일반 정보 저장
        $dbinsert = "insert into $page_table values('$new_no', '$SUBJECT', '$connent', '$style', '$cate')";
        $result_insert = mysqli_query($db, $dbinsert);
    }

    // 완료 메시지를 보인 후 페이지 이동
    echo ("
    <script language=javascript>
        alert('\\n정상적으로 페이지 정보를 저장 시켰습니다.\\n\\n');
        opener.parent.location=\"../page_submit.php?mode=form\"; 
        window.self.close();
    </script>");
    exit;
}
?>

<html>
<head>

<?php if ($mode == "BR형식미리보기") { $TTU = "BR자동입력"; } ?>
<?php if ($mode == "HTML형식미리보기") { $TTU = "HTML직접입력"; } ?>
<?php if ($mode == "업로드형식미리보기") { $TTU = "파일(업로드)로 입력"; } ?>

<title><?php echo("$TTU"); ?> - 미리보기페이지</title>
<meta http-equiv='Content-type' content='text/html; charset=euc-kr'>

<?php
$M123 = "../../";
?>

<?php
// 업로드 미리보기의 경우, 미리보기 지원하지 않음
if ($mode == "업로드형식미리보기") {
    echo ("<script language=javascript>
        alert('\\n업로드 미리보기는 디렉토리 경로의 문제로 인해 미리보기를 지원하지않습니다.\\n');
        window.self.close();
    </script>");
    exit;
}
?>

<?php
// HTML 형식 미리보기 처리
if ($mode == "HTML형식미리보기") {
    echo("$connent");
}
?>

<style>
body, td, input, select, submit {color: black; font-size: 9pt; FONT-FAMILY: 굴림; word-break: break-all;}
</style>

</head>

<body bgcolor='#FFFFFF' LEFTMARGIN='10' TOPMARGIN='10' MARGINWIDTH='10' MARGINHEIGHT='10'>

<?php
// BR 형식 미리보기 처리
if ($mode == "BR형식미리보기") {
    $CONTENT = $connent;
    $CONTENT = preg_replace("/</i", "&lt;", $CONTENT);
    $CONTENT = preg_replace("/>/i", "&gt;", $CONTENT);
    $CONTENT = preg_replace("/\"/i", "&quot;", $CONTENT);
    $CONTENT = preg_replace("/\|/i", "&#124;", $CONTENT);
    $CONTENT = preg_replace("/\r\n\r\n/i", "<P>", $CONTENT);
    $CONTENT = preg_replace("/\r\n/i", "<BR>", $CONTENT);
    $connent_text = $CONTENT;

    echo("$connent_text");
}
?>

<p align=center><BR><BR><input type='button' value=' 창 닫기 ' onClick='javascript:window.close();'><BR><BR></p>

</body>

</html>
