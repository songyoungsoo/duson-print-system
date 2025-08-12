<?php

$BbsAdminCateUrl = $BbsAdminCateUrl ?? "../..";

$dir_path =  $BbsAdminCateUrl.'/bbs/skin';
$dir_handle = opendir($dir_path);

$RRT = "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";

echo "<select name='skin'>";
echo "<option value='0'>SKIN을 선택하세요</option>";

while (($tmp = readdir($dir_handle)) !== false) {

    if ($tmp != "." && $tmp != "..") {

        $fullPath = $dir_path . '/' . $tmp;

        if (is_dir($fullPath)) {

            $safe_tmp = htmlspecialchars($tmp, ENT_QUOTES, 'UTF-8');

            $is_selected = ($BBS_ADMIN_skin == $tmp) ? $RRT : "";

            echo "<option value='$safe_tmp' $is_selected>$safe_tmp</option>";
        }
    }
}

echo "</select>";

closedir($dir_handle);

?>
