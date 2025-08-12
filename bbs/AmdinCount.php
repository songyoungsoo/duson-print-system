<?php
$page = '';
$AdminYdddNo = '';
$PHP_SELF = '';
$table = '';
$offset = '';
$PCode = '';
// 아래 코드는 PHP 태그 밖에서 HTML을 출력해야 문법 오류가 없습니다.
?>
<input type='button' onClick="javascript:AdminBdCount('<?php echo $AdminYdddNo?>');" value='카운터' style='font-size:8pt; width:45; height:17;'>
<input type='button' onClick="javascript:AdminBdgDel('<?php echo $AdminYdddNo?>');" value='삭제' style='font-size:8pt; width:30; height:17;'>
<input type='button' onClick="javascript:window.location='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&tt=modify&no=<?php echo $AdminYdddNo?>&page=<?php echo $page?>&offset=<?php echo $offset?>&PCode=<?php echo $PCode?>';" value='수정' style='font-size:8pt; width:30; height:17;'>