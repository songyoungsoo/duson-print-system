<head>
    <script language="JavaScript">
        self.moveTo(0, 0);
        self.resizeTo(availWidth = 350, availHeight = 330);
        
        function Activity(name, list) {
            this.name = name;
            this.list = list;
        }
        
        var acts = [];

        <?php
        include "../../db.php";
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
        $rows = mysqli_num_rows($result);
        if ($rows) {
            $g = 0;
            while ($row = mysqli_fetch_array($result)) {
                ?>
                acts[<?php echo  $g ?>] = new Activity('<?php echo  $row['no'] ?>', [
                <?php
                $result_Two = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE BigNo='{$row['no']}' ORDER BY no ASC");
                $rows_Two = mysqli_num_rows($result_Two);
                if ($rows_Two) {
                    while ($row_Two = mysqli_fetch_array($result_Two)) {
                        echo "'{$row_Two['title']}',";
                    }
                }
                ?>'==================']);
                <?php
                $g++;
            }
        } else {
            echo("<option>자료가 없습니다</option>");
        }
        mysqli_close($db);
        ?>

        var VL = [];

        <?php
        include "../../db.php";
        $db = mysqli_connect("host", "user", "password", "dataname");
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
        $rows = mysqli_num_rows($result);
        if ($rows) {
            $g = 0;
            while ($row = mysqli_fetch_array($result)) {
                ?>
                VL[<?php echo  $g ?>] = new Activity('<?php echo  $row['no'] ?>', [
                <?php
                $result_Two = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE BigNo='{$row['no']}' ORDER BY no ASC");
                $rows_Two = mysqli_num_rows($result_Two);
                if ($rows_Two) {
                    while ($row_Two = mysqli_fetch_array($result_Two)) {
                        echo "'{$row_Two['no']}',";
                    }
                }
                ?>'==================']);
                <?php
                $g++;
            }
        } else {
            echo("<option>자료가 없습니다</option>");
        }
        mysqli_close($db);
        ?>

        var actsmyListTreeSelect = [];

        <?php
        include "../../db.php";
        $db = mysqli_connect("host", "user", "password", "dataname");
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
        $rows = mysqli_num_rows($result);
        if ($rows) {
            $g = 0;
            while ($row = mysqli_fetch_array($result)) {
                ?>
                actsmyListTreeSelect[<?php echo  $g ?>] = new Activity('<?php echo  $row['no'] ?>', [
                <?php
                $result_Two = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE TreeNo='{$row['no']}' ORDER BY no ASC");
                $rows_Two = mysqli_num_rows($result_Two);
                if ($rows_Two) {
                    while ($row_Two = mysqli_fetch_array($result_Two)) {
                        echo "'{$row_Two['title']}',";
                    }
                }
                ?>'==================']);
                <?php
                $g++;
            }
        } else {
            echo("<option>자료가 없습니다</option>");
        }
        mysqli_close($db);
        ?>

        var VLmyListTreeSelect = [];

        <?php
        include "../../db.php";
        $db = mysqli_connect("host", "user", "password", "dataname");
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
        $rows = mysqli_num_rows($result);
        if ($rows) {
            $g = 0;
            while ($row = mysqli_fetch_array($result)) {
                ?>
                VLmyListTreeSelect[<?php echo  $g ?>] = new Activity('<?php echo  $row['no'] ?>', [
                <?php
                $result_Two = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE TreeNo='{$row['no']}' ORDER BY no ASC");
                $rows_Two = mysqli_num_rows($result_Two);
                if ($rows_Two) {
                    while ($row_Two = mysqli_fetch_array($result_Two)) {
                        echo "'{$row_Two['no']}',";
                    }
                }
                ?>'==================']);
                <?php
                $g++;
            }
        } else {
            echo("<option>자료가 없습니다</option>");
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

<form name="myForm" method='post' onsubmit='javascript:return MemberXCheckField()' action='<?php echo  $_SERVER['PHP_SELF'] ?>'>
    <?php if ($code == "Modify") { ?>
        <input type="hidden" name='mode' value='Modify_ok'>
        <input type="hidden" name='no' value='<?php echo  $no ?>'>
    <?php } else { ?>
        <input type="hidden" name='mode' value='form_ok'>
    <?php } ?>
    <input type="hidden" name='Ttable' value='<?php echo  $Ttable ?>'>
    <tr>
        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>인쇄색상&nbsp;&nbsp;</td>
        <td>
        <select name="RadOne" onChange="updateList(this.value)">
    <option value="#">:::::: 선택하세요 ::::::</option>
    <?php
    include "../../db.php";
    $db = mysqli_connect($host, $user, $password, $dataname) 
          or die("DB 연결 실패: " . mysqli_connect_error());

    // Prepared Statement 사용
    $stmt = $db->prepare("SELECT * FROM $GGTABLE WHERE Ttable=? AND BigNo='0' ORDER BY no ASC");
    $stmt->bind_param("s", $Ttable);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $selected = ($code == "Modify" && $MlangPrintAutoFildView_style == $row['no']) 
                        ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" 
                        : "";
            $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
            echo "<option value='{$row['no']}' $selected>$title</option>";
        }
    } else {
        echo "<option>자료가 없습니다</option>";
    }
    mysqli_close($db);
    ?>
</select>
        </td>
    </tr>
    <tr>
        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>종이종류&nbsp;&nbsp;</td>
        <td>
            <select name=myListTreeSelect>
                <option value='#'>:::::: 선택하세요 ::::::</option>
                <?php if ($code == "Modify" && $MlangPrintAutoFildView_TreeSelect) { ?>
                    <option value='<?php echo  $MlangPrintAutoFildView_TreeSelect ?>' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>
                        <?php
                        include "../../db.php";
                        $db = mysqli_connect($host, $user, $password, $dataname);
                        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_TreeSelect'");
                        $row = mysqli_fetch_array($result);
                        if ($row) {
                            echo($row['title']);
                        }
                        mysqli_close($db);
                        ?>
                    </option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>종이규격&nbsp;&nbsp;</td>
        <td>
            <select name=myList>
                <option value='#'>:::::: 선택하세요 ::::::</option>
                <?php if ($code == "Modify" && $MlangPrintAutoFildView_Section) { ?>
                    <option value='<?php echo  $MlangPrintAutoFildView_Section ?>' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>
                        <?php
                        include "../../db.php";
                        $db = mysqli_connect($host, $user, $password, $dataname);
                        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_Section'");
                        $row = mysqli_fetch_array($result);
                        if ($row) {
                            echo($row['title']);
                        }
                        mysqli_close($db);
                        ?>
                    </option>
                <?php } ?>
            </select>
        </td>
    </tr>

