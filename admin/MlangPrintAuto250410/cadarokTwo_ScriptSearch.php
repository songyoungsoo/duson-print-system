<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>폼 수정</title>
<script>
function Activity(name, list) {
    this.name = name;
    this.list = list;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

var acts = new Array();

<?php
// include "../../db.php";
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';  // 절대 경로로 변경
$Ttable = $db->real_escape_string($Ttable);
$GGTABLE = $db->real_escape_string($GGTABLE);

$query = "SELECT * FROM $GGTABLE WHERE Ttable=? AND BigNo='0' ORDER BY no ASC";
$stmt = $db->prepare($query);
$stmt->bind_param('s', $Ttable);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;
if ($rows > 0) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
        echo "acts[$g] = new Activity('{$row['no']}', [";
        $stmt_Two = $db->prepare("SELECT * FROM $GGTABLE WHERE TreeNo=? ORDER BY no ASC");
        $stmt_Two->bind_param('i', $row['no']);
        $stmt_Two->execute();
        $result_Two = $stmt_Two->get_result();
        $rows_Two = $result_Two->num_rows;
        if ($rows_Two > 0) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo "'{$row_Two['title']}',";
            }
        }
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "alert('등록자료 없음');";
}
$stmt->close();
?>

var VL = new Array();

<?php
$stmt = $db->prepare($query);
$stmt->bind_param('s', $Ttable);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
        echo "VL[$g] = new Activity('{$row['no']}', [";
        $stmt_Two = $db->prepare("SELECT * FROM $GGTABLE WHERE TreeNo=? ORDER BY no ASC");
        $stmt_Two->bind_param('i', $row['no']);
        $stmt_Two->execute();
        $result_Two = $stmt_Two->get_result();
        if ($result_Two->num_rows > 0) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo "'{$row_Two['no']}',";
            }
        }
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "alert('등록자료 없음');";
}
$stmt->close();
?>

var actsmyListTreeSelect = new Array();

<?php
$stmt = $db->prepare($query);
$stmt->bind_param('s', $Ttable);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
        echo "actsmyListTreeSelect[$g] = new Activity('{$row['no']}', [";
        $stmt_Two = $db->prepare("SELECT * FROM $GGTABLE WHERE BigNo=? ORDER BY no ASC");
        $stmt_Two->bind_param('i', $row['no']);
        $stmt_Two->execute();
        $result_Two = $stmt_Two->get_result();
        if ($result_Two->num_rows > 0) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo "'{$row_Two['title']}',";
            }
        }
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "alert('등록자료 없음');";
}
$stmt->close();
?>

var VLmyListTreeSelect = new Array();

<?php
$stmt = $db->prepare($query);
$stmt->bind_param('s', $Ttable);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
        echo "VLmyListTreeSelect[$g] = new Activity('{$row['no']}', [";
        $stmt_Two = $db->prepare("SELECT * FROM $GGTABLE WHERE BigNo=? ORDER BY no ASC");
        $stmt_Two->bind_param('i', $row['no']);
        $stmt_Two->execute();
        $result_Two = $stmt_Two->get_result();
        if ($result_Two->num_rows > 0) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo "'{$row_Two['no']}',";
            }
        }
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "alert('등록자료 없음');";
}
$stmt->close();
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

    var myListTreeSelectfrm = document.myForm;
    var myListTreeSelectoriLen = myListTreeSelectfrm.myListTreeSelect.length;
    var nummyListTreeSelectActs;

    for (var i = 0; i < actsmyListTreeSelect.length; i++) {
        if (str == actsmyListTreeSelect[i].name) {
            nummyListTreeSelectActs = actsmyListTreeSelect[i].list.length;
            for (var j = 0; j < nummyListTreeSelectActs; j++) {
                myListTreeSelectfrm.myListTreeSelect.options[j] = new Option(actsmyListTreeSelect[i].list[j], VLmyListTreeSelect[i].list[j]);
            }
            for (var j = nummyListTreeSelectActs; j < myListTreeSelectoriLen; j++) {
                myListTreeSelectfrm.myListTreeSelect.options[nummyListTreeSelectActs] = null;
            }
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

</script>
</head>

<body>
<form name="myForm" method="post" onsubmit="return MemberXCheckField()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

<?php if ($code == "Modify") { ?>
    <input type="hidden" name="mode" value="Modify_ok">
    <input type="hidden" name="no" value="<?php echo htmlspecialchars($no); ?>">
<?php } else { ?>
    <input type="hidden" name="mode" value="form_ok">
<?php } ?>

<input type="hidden" name="Ttable" value="<?php echo htmlspecialchars($Ttable); ?>">

<tr>
<td bgcolor="#<?php echo htmlspecialchars($Bgcolor1); ?>" width="100" class="Left1" align="right">구분&nbsp;&nbsp;</td>
<td>
<select name="RadOne" onchange="updateList(this.value)">
<option value="#">:::::: 선택하세요 ::::::</option>
<?php
$stmt = $db->prepare($query);
$stmt->bind_param('s', $Ttable);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['no']}'";
        if ($code == "Modify" && $MlangPrintAutoFildView_style == $row['no']) {
            echo " selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";
        }
        echo ">{$row['title']}</option>";
    }
} else {
    echo "<option>등록자료 없음</option>";
}
$stmt->close();
?>
</select>
</td>
</tr>

<tr>
<td bgcolor="#<?php echo htmlspecialchars($Bgcolor1); ?>" width="100" class="Left1" align="right">규격&nbsp;&nbsp;</td>
<td>
<select name="myListTreeSelect">
<option value="#">:::::: 선택하세요 ::::::</option>
<?php
if ($code == "Modify" && $MlangPrintAutoFildView_Section) {
    echo "<option value='$MlangPrintAutoFildView_TreeSelect' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";
    $stmt = $db->prepare("SELECT * FROM $GGTABLE WHERE no=?");
    $stmt->bind_param('i', $MlangPrintAutoFildView_Section);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        echo $row['title'];
    }
    echo "</option>";
    $stmt->close();
}
?>
</select>
</td>
</tr>

<tr>
<td bgcolor="#<?php echo htmlspecialchars($Bgcolor1); ?>" width="100" class="Left1" align="right">종이종류&nbsp;&nbsp;</td>
<td>
<select name="myList">
<option value="#">:::::: 선택하세요 ::::::</option>
<?php
if ($code == "Modify" && $MlangPrintAutoFildView_TreeSelect) {
    echo "<option value='$MlangPrintAutoFildView_Section' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";
    $stmt = $db->prepare("SELECT * FROM $GGTABLE WHERE no=?");
    $stmt->bind_param('i', $MlangPrintAutoFildView_TreeSelect);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        echo $row['title'];
    }
    echo "</option>";
    $stmt->close();
}
?>
</select>
</td>
</tr>
</form>
</body>
</html>
<?php
$db->close();
?>
