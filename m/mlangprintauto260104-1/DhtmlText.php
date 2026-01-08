<?php
// 텍스트 처리 함수
if (!function_exists('processTextContent')) {
    function processTextContent($content) {
        if (empty($content)) return '';
        
        $content = preg_replace("/#/", "&nbsp;", $content);
        $content = preg_replace("/\r\n\r\n/", "<P>", $content);
        $content = preg_replace("/\r\n/", "<BR>", $content);
        
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }
}

// 이미지 태그 생성 함수
if (!function_exists('generateImageTag')) {
    function generateImageTag($imageName) {
        if (empty($imageName)) return '';
        
        $imagePath = htmlspecialchars("./upload/" . $imageName, ENT_QUOTES, 'UTF-8');
        return '<td align="center" width="80"><img src="' . $imagePath . '" width="80" height="95" border="0" alt="업로드 이미지"></td>';
    }
}

// 프린트 박스 생성 함수
if (!function_exists('generatePrintBox')) {
    function generatePrintBox($id, $section, $image, $left, $top, $width, $height, $zIndex) {
        $content = processTextContent($section ?? '');
        $imageTag = generateImageTag($image ?? '');
        $tableWidth = (!empty($image)) ? $width : $width + 90;
        
        return '
<div id="print' . sprintf('%02d', $id) . '" style="position:absolute; left:' . $left . 'px; top:' . $top . 'px; width:' . $width . 'px; height:' . $height . 'px; z-index:' . $zIndex . '; visibility: hidden; font-family: \'Noto Sans KR\', sans-serif;">
    <table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
        <tr>
            <td width="' . $tableWidth . '" height="' . $height . '" valign="top" style="font-family: \'Noto Sans KR\', sans-serif;">
                ' . $content . '
            </td>
            ' . $imageTag . '
        </tr>
    </table>
</div>';
    }
}
?>

<?= generatePrintBox(1, $SectionOne ?? '', $ImgOne ?? '', $PrintTextBox_left, $PrintTextBox_top, $PrintTextBox_width, $PrintTextBox_height, 1) ?>

<?= generatePrintBox(2, $SectionTwo ?? '', $ImgTwo ?? '', $PrintTextBox_left, $PrintTextBox_top, $PrintTextBox_width, $PrintTextBox_height, 2) ?>

<?= generatePrintBox(3, $SectionTree ?? '', $ImgTree ?? '', $PrintTextBox_left, $PrintTextBox_top, $PrintTextBox_width, $PrintTextBox_height, 3) ?>

<?= generatePrintBox(4, $SectionFour ?? '', $ImgFour ?? '', $PrintTextBox_left, $PrintTextBox_top, $PrintTextBox_width, $PrintTextBox_height, 4) ?>

<?= generatePrintBox(5, $SectionFive ?? '', $ImgFive ?? '', $PrintTextBox_left, $PrintTextBox_top, $PrintTextBox_width, $PrintTextBox_height, 5) ?>
