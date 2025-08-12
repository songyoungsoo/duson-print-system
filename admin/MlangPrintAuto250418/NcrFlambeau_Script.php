<?php
include "../../db.php";

$code  = $_REQUEST['code'] ?? '';
$no    = $_REQUEST['no'] ?? '';
$Ttable = $_REQUEST['Ttable'] ?? '';
$Bgcolor1 = "408080";

// 변수 초기화
$MlangPrintAutoFildView_style = $_REQUEST['MlangPrintAutoFildView_style'] ?? '';
$MlangPrintAutoFildView_Section = $_REQUEST['MlangPrintAutoFildView_Section'] ?? '';
$MlangPrintAutoFildView_TreeSelect = $_REQUEST['MlangPrintAutoFildView_TreeSelect'] ?? '';

// 데이터를 1회만 로딩
function getGroups($db, $GGTABLE, $Ttable, $column = 'BigNo', $parent = '0') {
    $data = [];
    $query = "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND $column='$parent' ORDER BY no ASC";
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}
?>
<!DOCTYPE html>
<html>
<head>
<script>
function Activity(name, list){
    this.name = name;
    this.list = list;
}
</script>

<script>
self.moveTo(0,0);
self.resizeTo(350, 330);

// ================== PHP to JS (acts + VL + Tree) ==================
var acts = [], VL = [], actsmyListTreeSelect = [], VLmyListTreeSelect = [];
<?php
$parents = getGroups($db, $GGTABLE, $Ttable);
foreach ($parents as $i => $parent) {
    $children = getGroups($db, $GGTABLE, $Ttable, 'BigNo', $parent['no']);
    echo "acts[$i] = new Activity('{$parent['no']}', [";
    foreach ($children as $c) echo "'".addslashes($c['title'])."',";
    echo "'==================']);\n";

    echo "VL[$i] = new Activity('{$parent['no']}', [";
    foreach ($children as $c) echo "'{$c['no']}',";
    echo "'==================']);\n";
}
$treeParents = getGroups($db, $GGTABLE, $Ttable);
foreach ($treeParents as $i => $parent) {
    $children = getGroups($db, $GGTABLE, $Ttable, 'TreeNo', $parent['no']);
    echo "actsmyListTreeSelect[$i] = new Activity('{$parent['no']}', [";
    foreach ($children as $c) echo "'".addslashes($c['title'])."',";
    echo "'==================']);\n";

    echo "VLmyListTreeSelect[$i] = new Activity('{$parent['no']}', [";
    foreach ($children as $c) echo "'{$c['no']}',";
    echo "'==================']);\n";
}
?>
function updateList(str){
    const frm = document.myForm;
    const oriLen = frm.myList.length;

    for (let i = 0; i < acts.length; i++) {
        if (str == acts[i].name) {
            const numActs = acts[i].list.length;
            for (let j = 0; j < numActs; j++)
                frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
            for (let j = numActs; j < oriLen; j++)
                frm.myList.options[numActs] = null;
        }
    }

    const treeFrm = frm;
    const oriTreeLen = treeFrm.myListTreeSelect.length;

    for (let i = 0; i < actsmyListTreeSelect.length; i++) {
        if (str == actsmyListTreeSelect[i].name) {
            const n = actsmyListTreeSelect[i].list.length;
            for (let j = 0; j < n; j++)
                treeFrm.myListTreeSelect.options[j] = new Option(actsmyListTreeSelect[i].list[j], VLmyListTreeSelect[i].list[j]);
            for (let j = n; j < oriTreeLen; j++)
                treeFrm.myListTreeSelect.options[n] = null;
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

<!-- 구분 -->
<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">구분&nbsp;&nbsp;</td>
<td>
    <select name="RadOne" onchange="updateList(this.value)">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
        while ($row = mysqli_fetch_assoc($result)) {
            $selected = ($code === "Modify" && $MlangPrintAutoFildView_style == $row['no']) ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "";
            echo "<option value='{$row['no']}' $selected>{$row['title']}</option>";
        }
        ?>
    </select>
</td>
</tr>

<!-- 규격 -->
<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">규격&nbsp;&nbsp;</td>
<td>
    <select name="myList">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        if ($code === "Modify" && $MlangPrintAutoFildView_Section) {
            $res = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='{$MlangPrintAutoFildView_Section}'");
            $row = mysqli_fetch_assoc($res);
            if ($row) {
                echo "<option value='{$MlangPrintAutoFildView_Section}' selected style='background-color:#429EB2; color:#FFFFFF;'>{$row['title']}</option>";
            }
        }
        ?>
    </select>
</td>
</tr>

<!-- 색상 및 재질 -->
<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">색상 및 재질&nbsp;&nbsp;</td>
<td>
    <select name="myListTreeSelect">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        if ($code === "Modify" && $MlangPrintAutoFildView_TreeSelect) {
            $res = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='{$MlangPrintAutoFildView_TreeSelect}'");
            $row = mysqli_fetch_assoc($res);
            if ($row) {
                echo "<option value='{$MlangPrintAutoFildView_TreeSelect}' selected style='background-color:#429EB2; color:#FFFFFF;'>{$row['title']}</option>";
            }
        }
        ?>
    </select>
</td>
</tr>