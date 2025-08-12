<?php
include "../../db.php";

$Ttable = $_REQUEST['Ttable'] ?? '';
$RadOne = $_REQUEST['RadOne'] ?? '';
$myList = $_REQUEST['myList'] ?? '';
$myListTreeSelect = $_REQUEST['myListTreeSelect'] ?? '';

function fetchGroups($treeColumn = 'BigNo') {
    global $db, $GGTABLE, $Ttable;

    $data = [];

    $query = "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC";
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $parentNo = $row['no'];
        $children = [];

        $subQuery = "SELECT * FROM $GGTABLE WHERE $treeColumn='$parentNo' ORDER BY no ASC";
        $subResult = mysqli_query($db, $subQuery);
        while ($subRow = mysqli_fetch_assoc($subResult)) {
            $children[] = [
                'no' => $subRow['no'],
                'title' => $subRow['title']
            ];
        }

        $data[] = [
            'no' => $row['no'],
            'children' => $children
        ];
    }

    return $data;
}

$actsData = fetchGroups('BigNo'); // 제목
$VLData = fetchGroups('BigNo');   // 번호
$actsTreeData = fetchGroups('TreeNo'); // 제목
$VLTreeData = fetchGroups('TreeNo');   // 번호
?>
<!DOCTYPE html>
<html>
<head>
<script>
function Activity(name, list){
    this.name = name;
    this.list = list;
}
///////////////////////////////////////////////////////////////////////////////////////////////////

var acts = [];
var VL = [];
var actsmyListTreeSelect = [];
var VLmyListTreeSelect = [];

// acts (title)
<?php foreach ($actsData as $i => $act): ?>
acts[<?php echo  $i ?>] = new Activity('<?php echo  $act['no'] ?>', [
<?php foreach ($act['children'] as $child): ?>
'<?php echo  addslashes($child['title']) ?>',
<?php endforeach; ?>
'==================']);
<?php endforeach; ?>

// VL (no)
<?php foreach ($VLData as $i => $act): ?>
VL[<?php echo  $i ?>] = new Activity('<?php echo  $act['no'] ?>', [
<?php foreach ($act['children'] as $child): ?>
'<?php echo  $child['no'] ?>',
<?php endforeach; ?>
'==================']);
<?php endforeach; ?>

// Tree (title)
<?php foreach ($actsTreeData as $i => $act): ?>
actsmyListTreeSelect[<?php echo  $i ?>] = new Activity('<?php echo  $act['no'] ?>', [
<?php foreach ($act['children'] as $child): ?>
'<?php echo  addslashes($child['title']) ?>',
<?php endforeach; ?>
'==================']);
<?php endforeach; ?>

// Tree (no)
<?php foreach ($VLTreeData as $i => $act): ?>
VLmyListTreeSelect[<?php echo  $i ?>] = new Activity('<?php echo  $act['no'] ?>', [
<?php foreach ($act['children'] as $child): ?>
'<?php echo  $child['no'] ?>',
<?php endforeach; ?>
'==================']);
<?php endforeach; ?>

function updateList(str){
    let frm = document.myForm;
    let oriLen = frm.myList.length;
    let numActs;

    for (let i = 0; i < acts.length; i++) {
        if (str == acts[i].name) {
            numActs = acts[i].list.length;
            for (let j = 0; j < numActs; j++)
                frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
            for (let j = numActs; j < oriLen; j++)
                frm.myList.options[numActs] = null;
        }
    }

    let myListTreeSelectfrm = document.myForm;
    let oriTreeLen = myListTreeSelectfrm.myListTreeSelect.length;
    let numTreeActs;

    for (let i = 0; i < actsmyListTreeSelect.length; i++) {
        if (str == actsmyListTreeSelect[i].name) {
            numTreeActs = actsmyListTreeSelect[i].list.length;
            for (let j = 0; j < numTreeActs; j++)
                myListTreeSelectfrm.myListTreeSelect.options[j] = new Option(actsmyListTreeSelect[i].list[j], VLmyListTreeSelect[i].list[j]);
            for (let j = numTreeActs; j < oriTreeLen; j++)
                myListTreeSelectfrm.myListTreeSelect.options[numTreeActs] = null;
        }
    }
}
</script>
</head>
<body>

<form name="myForm" method="post" onsubmit="return MemberXCheckField()" action="<?php echo  $_SERVER['PHP_SELF'] ?>">

<!-- RadOne -->
<select name="RadOne" onchange="updateList(this.value)">
    <option value="#">:::::: 선택하세요 ::::::</option>
    <?php
    include "../../db.php";
    $query = "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC";
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $selected = ($RadOne == $row['no']) ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "";
        echo "<option value='{$row['no']}' $selected>{$row['title']}</option>";
    }
    ?>
</select>

<!-- myList -->
<select name="myList">
        <option value='#'>:::::: 선택하세요 ::::::</option>
        <?php if ($myList) { ?>
            <option value='<?php echo  $myList ?>' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>
                <?php
                include "../../db.php";
                $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='$myList'");
                $row = mysqli_fetch_array($result);
                if ($row) {
                    echo($row['title']);
                }
                mysqli_close($db);
                ?>
            </option>
        <?php } ?>
</select> 

<!-- myListTreeSelect -->
<select name="myListTreeSelect">
        <option value='#'>:::::: 선택하세요 ::::::</option>
        <?php if ($myListTreeSelect) { ?>
            <option value='<?php echo  $myListTreeSelect ?>' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>
                <?php
                include "../../db.php";
                $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='$myListTreeSelect'");
                $row = mysqli_fetch_array($result);
                if ($row) {
                    echo($row['title']);
                }
                mysqli_close($db);
                ?>
            </option>
        <?php } ?>
    </select>