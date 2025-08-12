<HEAD>
<SCRIPT LANGUAGE="JavaScript">
self.moveTo(0,0)
self.resizeTo(availWidth=400,availHeight=300)

function Activity(name, list) {
    this.name = name;
    this.list = list;
}

var acts = [];
<?php
include "../../db.php";
$result = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE Ttable='{$Ttable}' AND BigNo='0' ORDER BY no ASC");
$rows = mysqli_num_rows($result);
if ($rows) {
    $g = 0;
    while ($row = mysqli_fetch_array($result)) {
?>
acts[<?php echo $g?>] = new Activity('<?php echo htmlspecialchars($row['no'])?>', [
    <?php
    $result_Two = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE  BigNo='{$row['no']}' ORDER BY no ASC");
    $rows_Two = mysqli_num_rows($result_Two);
    if ($rows_Two) {
        while ($row_Two = mysqli_fetch_array($result_Two)) {
            echo "'".htmlspecialchars($row_Two['title'])."',";
        }
    }
    ?>'==================']);
<?php
        $g++;
    }
} else {
    echo ("<option>등록자료  없음</option>");
}
mysqli_close($db);
?>

var VL = [];
<?php
include "../../db.php";
$result = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE Ttable='{$Ttable}' AND BigNo='0' ORDER BY no ASC");
$rows = mysqli_num_rows($result);
if ($rows) {
    $g = 0;
    while ($row = mysqli_fetch_array($result)) {
?>
VL[<?php echo $g?>] = new Activity('<?php echo htmlspecialchars($row['no'])?>', [
    <?php
    $result_Two = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE BigNo='{$row['no']}' ORDER BY no ASC");
    $rows_Two = mysqli_num_rows($result_Two);
    if ($rows_Two) {
        while ($row_Two = mysqli_fetch_array($result_Two)) {
            echo "'".htmlspecialchars($row_Two['no'])."',";
        }
    }
    ?>'==================']);
<?php
        $g++;
    }
} else {
    echo ("<option>데이터 없음</option>");
}
mysqli_close($db);
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

}
</SCRIPT>
</HEAD>

<FORM NAME="myForm" method="post" OnSubmit="javascript:return MemberXCheckField()" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>">
<?php if (isset($code) && $code == "Modify") { ?>
    <INPUT TYPE="hidden" name="mode" value="Modify_ok">
    <INPUT TYPE="hidden" name="no" value="<?php echo htmlspecialchars($no)?>">
<?php } else { ?>
    <INPUT TYPE="hidden" name="mode" value="form_ok">
<?php } ?>

<INPUT TYPE="hidden" name="Ttable" value="<?php echo htmlspecialchars($Ttable)?>">

<tr>
    <td bgcolor="#<?php echo htmlspecialchars($Bgcolor1)?>" width=100 class="Left1" align=right>스티카종류&nbsp;&nbsp;</td>
    <td>
        <select name="RadOne" onChange="updateList(this.value)">
            <option value="#">:::::: 선택하세요 ::::::</option>
            <?php
            include "../../db.php";
            $result = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE Ttable='{$Ttable}' AND BigNo='0' ORDER BY no ASC");
            $rows = mysqli_num_rows($result);
            if ($rows) {
                $r=0;
                while ($row = mysqli_fetch_array($result)) {
            ?>
            <option value="<?php echo htmlspecialchars($row['no'])?>" <?php if (isset($code) && $code == "Modify" && isset($MlangPrintAutoFildView_style) && $MlangPrintAutoFildView_style == $row['no']) { echo "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'"; } ?>><?php echo htmlspecialchars($row['title'])?></option>
            <?php
            	$r++;
                }
            } else {
                echo "<option>등록자료  없음</option>";
            }
            mysqli_close($db);
            ?>
        </select>
    </td>
</tr>

<tr>
    <td bgcolor="#<?php echo htmlspecialchars($Bgcolor1)?>" width=100 class="Left1" align=right>인쇄규격&nbsp;&nbsp;</td>
    <td>
        <select name="myList">
            <option value="#">:::::: 선택하세요 ::::::</option>
            <?php if (isset($code) && $code == "Modify" && isset($MlangPrintAutoFildView_Section)) { ?>
            <option value="<?php echo htmlspecialchars($MlangPrintAutoFildView_Section)?>" selected style="font-size:10pt; background-color:#429EB2; color:#FFFFFF;">
                <?php
                include "../../db.php";
                $result = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE no='{$MlangPrintAutoFildView_Section}'");
                $row = mysqli_fetch_array($result);
                if ($row) { echo htmlspecialchars($row['title']); }
                mysqli_close($db);
                ?>
            </option>
            <?php } ?>
        </select>
    </td>
</tr>
