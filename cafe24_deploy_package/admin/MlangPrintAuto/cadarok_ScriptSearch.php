<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>쇼핑몰 관리 시스템</title>
<script>
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
    echo "alert('등록자료 없음');";
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
    echo "alert('등록자료 없음');";
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
    echo "alert('등록자료 없음');";
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
    echo "alert('등록자료 없음');";
}
?>

function updateList(str) {
    var frm = document.myForm;
    var oriLen = frm.myList.length;
    var numActs;

    for (var i = 0; i < acts.length; i++) {
        if (str == acts[i].name) {
            numActs = acts[i].list.length;
            for (var j = 0; j < numActs; j++)
                frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
            for (var j = numActs; j < oriLen; j++)
                frm.myList.options[numActs] = null;
        }
    }

    var myListTreeSelectfrm = document.myForm;
    var myListTreeSelectoriLen = myListTreeSelectfrm.myListTreeSelect.length;
    var nummyListTreeSelectActs;

    for (var i = 0; i < actsmyListTreeSelect.length; i++) {
        if (str == actsmyListTreeSelect[i].name) {
            nummyListTreeSelectActs = actsmyListTreeSelect[i].list.length;
            for (var j = 0; j < nummyListTreeSelectActs; j++)
                myListTreeSelectfrm.myListTreeSelect.options[j] = new Option(actsmyListTreeSelect[i].list[j], VLmyListTreeSelect[i].list[j]);
            for (var j = nummyListTreeSelectActs; j < myListTreeSelectoriLen; j++)
                myListTreeSelectfrm.myListTreeSelect.options[nummyListTreeSelectActs] = null;
        }
    }
}
</script>
</head>

<body>
<form name="myForm" method="post" onsubmit="return MemberXCheckField()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

    <select name="RadOne" onchange="updateList(this.value)">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        include "../../db.php";
        $result = $db->query("SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['no']}'";
                if ($RadOne == $row['no']) {
                    echo " selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";
                }
                echo ">{$row['title']}</option>";
            }
        } else {
            echo "<option>등록자료 없음</option>";
        }
        ?>
    </select>

    <select name="myListTreeSelect">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        if (isset($myListTreeSelect)) {
            echo "<option value='$myListTreeSelect' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";
            include "../../db.php";
            $result = $db->query("SELECT * FROM $GGTABLE WHERE no='$myListTreeSelect'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo $row['title'];
            }
            echo "</option>";
        }
        ?>
    </select>

    <select name="myList">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        if (isset($myList)) {
            echo "<option value='$myList' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";
            include "../../db.php";
            $result = $db->query("SELECT * FROM $GGTABLE WHERE no='$myList'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo $row['title'];
            }
            echo "</option>";
        }
        ?>
    </select>

