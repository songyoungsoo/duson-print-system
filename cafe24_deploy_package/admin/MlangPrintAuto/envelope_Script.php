<HEAD>
<SCRIPT LANGUAGE="JavaScript">
self.moveTo(0,0)
self.resizeTo(availWidth=400,availHeight=350)

function Activity(name, list) {
    this.name = name;
    this.list = list;
}

var acts = new Array();

<?php
include "../../db.php";
$db = new mysqli($host, $user, $password, $dataname);
$Ttable = isset($Ttable) ? htmlspecialchars($Ttable) : '';
$query = "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC";
$result = mysqli_query($db, $query);

if ($result) {
    $g = 0;
    while ($row = mysqli_fetch_array($result)) {
        echo "acts[$g] = new Activity('{$row['no']}', [";
        
        $subQuery = "SELECT * FROM $GGTABLE WHERE BigNo='{$row['no']}' ORDER BY no ASC";
        $result_Two = mysqli_query($db, $subQuery);
        if ($result_Two) {
            while ($row_Two = mysqli_fetch_array($result_Two)) {
                echo "'{$row_Two['title']}',";
            }
        }
        if ($result_Two) {
            while ($row_Two = mysqli_fetch_array($result_Two)) {
                echo "'{$row_Two['title']}',";
            }
        }
        
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "console.log('데이터가 없습니다.');";
}

mysqli_close($db);
?>

var VL = new Array();

<?php
include "../../db.php";

$result = mysqli_query($db, $query);

if ($result) {
    $g = 0;
    while ($row = mysqli_fetch_array($result)) {
        echo "VL[$g] = new Activity('{$row['no']}', [";

        $result_Two = mysqli_query($db, $subQuery);

        if ($result_Two) {
            while ($row_Two = mysqli_fetch_array($result_Two)) {
                echo "'{$row_Two['no']}',";
            }
        }
        
        echo "'==================']);\n";
        $g++;
    }
} else {
    echo "console.log('데이터가 없습니다.');";
}

mysqli_close($db);
?>

function updateList(str) {
    var frm = document.myForm;
    var oriLen = frm.myList.length;

    for (var i = 0; i < acts.length; i++) {
        if (str === acts[i].name) {
            var numActs = acts[i].list.length;
            for (var j = 0; j < numActs; j++) {
                frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
            }
            for (var j = numActs; j < oriLen; j++) {
                frm.myList.options[numActs] = null;
            }
        }
    }
}
</SCRIPT>
</HEAD>

<FORM NAME="myForm" method="post" onsubmit="return MemberXCheckField()" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>">

<?php
$code = isset($code) ? $code : '';
$no = isset($no) ? htmlspecialchars($no) : '';
$Ttable = isset($Ttable) ? htmlspecialchars($Ttable) : '';
?>

<?php if ($code == "Modify") { ?>
    <INPUT TYPE="hidden" name="mode" value="Modify_ok">
    <INPUT TYPE="hidden" name="no" value="<?php echo  $no ?>">
<?php } else { ?>
    <INPUT TYPE="hidden" name="mode" value="form_ok">
<?php } ?>

<INPUT TYPE="hidden" name="Ttable" value="<?php echo  $Ttable ?>">

<table>
<tr>
<td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1 ?? 'FFFFFF') ?>" width="100" class="Left1" align="right">구분 &nbsp;&nbsp;</td>
<td>
<select name="RadOne" onchange="updateList(this.value)">
    <option value="#">:::::: 선택하십시오 ::::::</option>
    <?php
    include "../../db.php";
    $result = mysqli_query($db, $query);

    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $selected = ($code == "Modify" && isset($MlangPrintAutoFildView_style) && $MlangPrintAutoFildView_style == $row['no']) ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "";
            echo "<option value='{$row['no']}' $selected>{$row['title']}</option>";
        }
    } else {
        echo "<option>데이터가 없습니다.</option>";
    }

    mysqli_close($db);
    ?>
</select>
</td>
</tr>

<tr>
<td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1 ?? 'FFFFFF') ?>" width="100" class="Left1" align="right">종류 &nbsp;&nbsp;</td>
<td>
<select name="myList">
    <option value="#">:::::: 선택하십시오 ::::::</option>
    <?php
    if ($code == "Modify" && isset($MlangPrintAutoFildView_Section)) {
        include "../../db.php";
        $subQuery = "SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_Section'";
        $result = mysqli_query($db, $subQuery);
        $row = mysqli_fetch_array($result);

        if ($row) {
            echo "<option value='{$MlangPrintAutoFildView_Section}' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>{$row['title']}</option>";
        }

        mysqli_close($db);
    }
    ?>
</select>
</td>
</tr>

