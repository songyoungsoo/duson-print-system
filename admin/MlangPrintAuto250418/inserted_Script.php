<?php
include "../../db.php";

$code = $_REQUEST['code'] ?? '';
$no = $_REQUEST['no'] ?? '';
$Ttable = $_REQUEST['Ttable'] ?? '';
$Bgcolor1 = "408080";

$MlangPrintAutoFildView_style = $_REQUEST['MlangPrintAutoFildView_style'] ?? '';
$MlangPrintAutoFildView_Section = $_REQUEST['MlangPrintAutoFildView_Section'] ?? '';
$MlangPrintAutoFildView_TreeSelect = $_REQUEST['MlangPrintAutoFildView_TreeSelect'] ?? '';

function fetchGroupData($db, $table, $Ttable, $key = 'BigNo', $value = '0') {
    $data = [];
    $query = "SELECT * FROM {$table} WHERE Ttable='{$Ttable}' AND {$key}='{$value}' ORDER BY no ASC";
    $res = mysqli_query($db, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $row['children'] = [];
        $sub = mysqli_query($db, "SELECT * FROM {$table} WHERE {$key}='{$row['no']}' ORDER BY no ASC");
        while ($child = mysqli_fetch_assoc($sub)) {
            $row['children'][] = $child;
        }
        $data[] = $row;
    }
    return $data;
}

$acts = fetchGroupData($db, $GGTABLE, $Ttable, 'BigNo', '0');
$actsTree = fetchGroupData($db, $GGTABLE, $Ttable, 'TreeNo', '0');
?>
<!DOCTYPE html>
<html>
<head>
<script>
self.moveTo(0,0);
self.resizeTo(350,330);

function Activity(name, list) {
    this.name = name;
    this.list = list;
}

var acts = [], VL = [], actsmyListTreeSelect = [], VLmyListTreeSelect = [];

// JS로 PHP 배열 삽입
<?php foreach ($acts as $i => $act): ?>
acts[<?php echo  $i ?>] = new Activity('<?php echo  $act['no'] ?>', [
<?php foreach ($act['children'] as $c): ?>
'<?php echo  addslashes($c['title']) ?>',
<?php endforeach; ?>
'==================']);

VL[<?php echo  $i ?>] = new Activity('<?php echo  $act['no'] ?>', [
<?php foreach ($act['children'] as $c): ?>
'<?php echo  $c['no'] ?>',
<?php endforeach; ?>
'==================']);
<?php endforeach; ?>

<?php foreach ($actsTree as $i => $act): ?>
actsmyListTreeSelect[<?php echo  $i ?>] = new Activity('<?php echo  $act['no'] ?>', [
<?php foreach ($act['children'] as $c): ?>
'<?php echo  addslashes($c['title']) ?>',
<?php endforeach; ?>
'==================']);

VLmyListTreeSelect[<?php echo  $i ?>] = new Activity('<?php echo  $act['no'] ?>', [
<?php foreach ($act['children'] as $c): ?>
'<?php echo  $c['no'] ?>',
<?php endforeach; ?>
'==================']);
<?php endforeach; ?>

function updateList(str){
    const frm = document.myForm;

    for (let i = 0; i < acts.length; i++) {
        if (str === acts[i].name) {
            const n = acts[i].list.length;
            for (let j = 0; j < n; j++)
                frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
            for (let j = n; j < frm.myList.length; j++)
                frm.myList.options[n] = null;
        }
    }

    for (let i = 0; i < actsmyListTreeSelect.length; i++) {
        if (str === actsmyListTreeSelect[i].name) {
            const n = actsmyListTreeSelect[i].list.length;
            for (let j = 0; j < n; j++)
                frm.myListTreeSelect.options[j] = new Option(actsmyListTreeSelect[i].list[j], VLmyListTreeSelect[i].list[j]);
            for (let j = n; j < frm.myListTreeSelect.length; j++)
                frm.myListTreeSelect.options[n] = null;
        }
    }
}
</script>
</head>

<body>
<form name="myForm" method="post" onsubmit="return MemberXCheckField()" action="<?php echo  $_SERVER['PHP_SELF'] ?>">

<?php if ($code === "Modify"): ?>
    <input type="hidden" name="mode" value="Modify_ok">
    <input type="hidden" name="no" value="<?php echo  htmlspecialchars($no) ?>">
<?php else: ?>
    <input type="hidden" name="mode" value="form_ok">
<?php endif; ?>

<input type="hidden" name="Ttable" value="<?php echo  htmlspecialchars($Ttable) ?>">

<!-- 인쇄색상 -->
<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">인쇄색상&nbsp;&nbsp;</td>
<td>
<select name="RadOne" onchange="updateList(this.value)">
    <option value="#">:::::: 선택하세요 ::::::</option>
    <?php
    $res = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $selected = ($code === "Modify" && $MlangPrintAutoFildView_style == $row['no']) ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "";
        echo "<option value='{$row['no']}' $selected>{$row['title']}</option>";
    }
    ?>
</select>
</td>
</tr>

<!-- 종이종류 -->
<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">종이종류&nbsp;&nbsp;</td>
<td>
<select name="myListTreeSelect">
    <option value="#">:::::: 선택하세요 ::::::</option>
    <?php
    if ($code === "Modify" && $MlangPrintAutoFildView_TreeSelect) {
        $res = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_TreeSelect'");
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            echo "<option value='{$row['no']}' selected style='background-color:#429EB2; color:#FFFFFF;'>{$row['title']}</option>";
        }
    }
    ?>
</select>
</td>
</tr>

<!-- 종이규격 -->
<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">종이규격&nbsp;&nbsp;</td>
<td>
<select name="myList">
    <option value="#">:::::: 선택하세요 ::::::</option>
    <?php
    if ($code === "Modify" && $MlangPrintAutoFildView_Section) {
        $res = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_Section'");
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            echo "<option value='{$row['no']}' selected style='background-color:#429EB2; color:#FFFFFF;'>{$row['title']}</option>";
        }
    }
    ?>
</select>
</td>
</tr>