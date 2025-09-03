<?php
if (isset($_POST['mode']) && $_POST['mode'] == "modify") {
    $cont = $_POST['cont'] ?? '';

    $filePath = "../../member/int/stipulation.inc";
    
    // 파일 쓰기
    if (file_put_contents($filePath, $cont) !== false) {
        echo ("<script language='javascript'>
        alert('정상적으로 처리되었습니다..*^^*');
        </script>
        <meta http-equiv='Refresh' content='0; URL=" . $_SERVER['PHP_SELF'] . "'>
        ");
        exit;
    } else {
        echo ("<script language='javascript'>
        alert('파일 쓰기에 실패했습니다.');
        </script>
        <meta http-equiv='Refresh' content='0; URL=" . $_SERVER['PHP_SELF'] . "'>
        ");
        exit;
    }
}

$M123 = "..";
include "../top.php"; 
?>

<BR>
<p align="center">
아래의 박스에 새로운 회원가입 약관을 입력해 주세요. 약관 내용을 작성하신 후 저장을 클릭해 주세요.
</p>

<head>
<meta charset="UTF-8">
<script language="javascript">
function StiCheckField() {
    var f = document.StiInfo;
    if (f.cont.value.length < 20 ) {
        alert("회원가입 약관 내용을 최소 20자 이상 입력해 주세요.");
        return false;
    }
    return true;
}
</script>
</head>

<table border=0 align=center cellpadding='10' cellspacing='10' class='coolBar'>
<form name='StiInfo' method='post' onsubmit='return StiCheckField()' action='<?php echo  $_SERVER['PHP_SELF'] ?>'>
<input type='hidden' name='mode' value='modify'>
<tr><td>
<textarea cols=80 rows=20 name='cont'><?php
$filePath = "../../member/int/stipulation.inc";
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
} else {
    echo "파일을 읽을 수 없습니다.";
}
?></textarea>
</td></tr>
</table>

<p align="center">
<input type='submit' value=' 저장합니다.'>
</p>
</form>

<?php
include "../down.php";
?>