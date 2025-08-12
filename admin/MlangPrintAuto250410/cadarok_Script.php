<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>Document</title>
<script>
self.moveTo(0, 0);
self.resizeTo(screen.availWidth, screen.availHeight);

function Activity(name, list) {
    this.name = name;
    this.list = list;
}

var acts = new Array();

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';  // 절대 경로로 변경
$GGTABLE = $db->real_escape_string($GGTABLE);
$Ttable = $db->real_escape_string($Ttable);

$result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
if ($result->num_rows > 0) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
        echo "acts[$g] = new Activity('{$row['no']}', [";
        $result_Two = $db->query("SELECT * FROM $GGTABLE WHERE TreeNo='{$row['no']}' ORDER BY no ASC");
        if ($result_Two->num_rows > 0) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo "'{$row_Two['title']}',";
            }
        }
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "alert('데이터가 없습니다');";
}
?>

var VL = new Array();

<?php
$result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
if ($result->num_rows > 0) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
        echo "VL[$g] = new Activity('{$row['no']}', [";
        $result_Two = $db->query("SELECT * FROM $GGTABLE WHERE TreeNo='{$row['no']}' ORDER BY no ASC");
        if ($result_Two->num_rows > 0) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo "'{$row_Two['no']}',";
            }
        }
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "alert('데이터가 없습니다');";
}
?>

var actsmyListTreeSelect = new Array();

<?php
$result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
if ($result->num_rows > 0) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
        echo "actsmyListTreeSelect[$g] = new Activity('{$row['no']}', [";
        $result_Two = $db->query("SELECT * FROM $GGTABLE WHERE BigNo='{$row['no']}' ORDER BY no ASC");
        if ($result_Two->num_rows > 0) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo "'{$row_Two['title']}',";
            }
        }
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "alert('데이터가 없습니다');";
}
?>

var VLmyListTreeSelect = new Array();

<?php
$result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
if ($result->num_rows > 0) {
    $g = 0;
    while ($row = $result->fetch_assoc()) {
        echo "VLmyListTreeSelect[$g] = new Activity('{$row['no']}', [";
        $result_Two = $db->query("SELECT * FROM $GGTABLE WHERE BigNo='{$row['no']}' ORDER BY no ASC");
        if ($result_Two->num_rows > 0) {
            while ($row_Two = $result_Two->fetch_assoc()) {
                echo "'{$row_Two['no']}',";
            }
        }
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "alert('데이터가 없습니다');";
}
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
                frm.myList.options[j] = null;
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
                myListTreeSelectfrm.myListTreeSelect.options[j] = null;
            }
        }
    }
}
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

    <table>
        <tr>
            <td bgcolor="#<?php echo htmlspecialchars($Bgcolor1); ?>" width="100" class="Left1" align="right">분류&nbsp;&nbsp;</td>
            <td>
                <select name="RadOne" onchange="updateList(this.value)">
                    <option value="#">:::::: 선택하십시오 ::::::</option>
                    <?php
                    $result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['no']}'";
                            if ($code == "Modify" && $MlangPrintAutoFildView_style == $row['no']) {
                                echo " selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";
                            }
                            echo ">{$row['title']}</option>";
                        }
                    } else {
                        echo "<option>데이터가 없습니다</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td bgcolor="#<?php echo htmlspecialchars($Bgcolor1); ?>" width="100" class="Left1" align="right">세부항목&nbsp;&nbsp;</td>
            <td>
                <select name="myListTreeSelect">
                    <option value="#">:::::: 선택하십시오 ::::::</option>
                    <?php
                    if ($code == "Modify" && $MlangPrintAutoFildView_Section) {
                        echo "<option value='$MlangPrintAutoFildView_TreeSelect' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";
                        $result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_Section'");
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            echo $row['title'];
                        }
                        echo "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td bgcolor="#<?php echo htmlspecialchars($Bgcolor1); ?>" width="100" class="Left1" align="right">세부항목 2&nbsp;&nbsp;</td>
            <td>
                <select name="myList">
                    <option value="#">:::::: 선택하십시오 ::::::</option>
                    <?php
                    if ($code == "Modify" && $MlangPrintAutoFildView_TreeSelect) {
                        echo "<option value='$MlangPrintAutoFildView_Section' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";
                        $result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_TreeSelect'");
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            echo $row['title'];
                        }
                        echo "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

