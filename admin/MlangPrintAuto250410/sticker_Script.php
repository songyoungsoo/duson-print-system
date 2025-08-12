<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>Activity Selection</title>
<SCRIPT LANGUAGE="JavaScript">
self.moveTo(0,0);
self.resizeTo(screen.availWidth, screen.availHeight);

function Activity(name, list) {
    this.name = name;
    this.list = list;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

var acts = new Array();

<?php
include "../../db.php";
$result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
$rows = $result->num_rows;
if ($rows) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
?>
acts[<?php echo $g?>] = new Activity('<?php echo $row['no']?>', [<?php
        $result_Two = $db->query("SELECT * FROM $GGTABLE WHERE BigNo='{$row['no']}' ORDER BY no ASC");
        $rows_Two = $result_Two->num_rows;
        if ($rows_Two) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo("'{$row_Two['title']}',");
            }
        }
    ?>'==================']);
<?php
        $g++;
    }
} else {
    echo("alert('등록자료 없음');");
}
$db->close();
?>

var VL = new Array();

<?php
include "../../db.php";
$result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
$rows = $result->num_rows;
if ($rows) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
?>
VL[<?php echo $g?>] = new Activity('<?php echo $row['no']?>', [<?php
        $result_Two = $db->query("SELECT * FROM $GGTABLE WHERE BigNo='{$row['no']}' ORDER BY no ASC");
        $rows_Two = $result_Two->num_rows;
        if ($rows_Two) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo("'{$row_Two['no']}',");
            }
        }
    ?>'==================']);
<?php
        $g++;
    }
} else {
    echo("alert('등록자료 없음');");
}
$db->close();
?>

function updateList(str) {
    var frm = document.myForm;
    var oriLen = frm.myList.length;
    var numActs;

    for (var i = 0; i < acts.length; i++) {
        if (str == acts[i].name) {
            numActs = acts[i].list.length;
            for (var j = 0; j < numActs; j++) {
                frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
            }
            for (var j = numActs; j < oriLen; j++) {
                frm.myList.options[numActs] = null;
            }
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
</SCRIPT>
</head>

<body>
<FORM NAME="myForm" method="post" action="<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>">

<?php if ($code == "Modify") { ?>
<INPUT TYPE="hidden" name="mode" value="Modify_ok">
<INPUT TYPE="hidden" name="no" value="<?php echo $no?>">
<?php } else { ?>
<INPUT TYPE="hidden" name="mode" value="form_ok">
<?php } ?>

<INPUT TYPE="hidden" name="Ttable" value="<?php echo $Ttable?>">

<table border="0">
<tr>
<td>카테고리 선택&nbsp;&nbsp;</td>
<td>
<select name="RadOne" onChange="updateList(this.value)">
<option value="#">:::::: 선택하세요 ::::::</option>
<?php
include "../../db.php";
$result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
$rows = $result->num_rows;
if ($rows) {
    $r = 0;
    while ($row = $result->fetch_assoc()) { 
?>
<option value='<?php echo $row['no']?>' <?php if ($code == "Modify" && $MlangPrintAutoFildView_style == $row['no']) { echo "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'"; } ?>><?php echo $row['title']?></option>
<?php
        $r++;
    }
} else {
    echo("<option>등록자료 없음</option>");
}
$db->close();
?>
</select>
</td>
</tr>

<tr>
<td>섹션 선택&nbsp;&nbsp;</td>
<td>
<select name="myList">
<option value="#">:::::: 선택하세요 ::::::</option>
<?php
if ($code == "Modify" && $MlangPrintAutoFildView_Section) {
    echo "<option value='$MlangPrintAutoFildView_Section' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";
    include "../../db.php";
    $result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_Section'");
    $row = $result->fetch_assoc();
    if ($row) {
        echo $row['title'];
    }
    $db->close();
    echo "</option>";
}
?>
</select>
</td>
</tr>
</table>
</FORM>
</body>
</html>
