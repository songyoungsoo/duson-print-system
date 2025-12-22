<?php
// 변수 초기화 및 검증
$View_temp = isset($View_temp) ? $View_temp : '';
$CONTENT_OK = isset($CONTENT_OK) ? $CONTENT_OK : '';

// $View_temp가 변수명인 경우 해당 변수의 값을 가져옴
if (!empty($View_temp) && isset($$View_temp)) {
    $CONTENT = $$View_temp;
} elseif (!empty($CONTENT_OK)) {
    $CONTENT = $CONTENT_OK;
} else {
    $CONTENT = '';
}

// HTML 특수문자 처리
$CONTENT = preg_replace("/</i", "&lt;", $CONTENT);
$CONTENT = preg_replace("/>/i", "&gt;", $CONTENT);
$CONTENT = preg_replace("/\"/i", "&quot;", $CONTENT);
$CONTENT = preg_replace("/\|/i", "&#124;", $CONTENT);
$CONTENT = preg_replace("/\r\n\r\n/i", "<P>", $CONTENT);
$CONTENT = preg_replace("/\r\n/i", "<BR>", $CONTENT);
$connent_text = $CONTENT;
?>

<font color='#0071BC'><?php echo $connent_text; ?></font>