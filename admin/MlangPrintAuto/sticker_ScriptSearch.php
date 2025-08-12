<?php
// 헤더는 이미 출력이 시작된 후에 설정할 수 없으므로 제거
?>
<head>
    <script language="JavaScript">
        function Activity(name, list) {
            this.name = name;
            this.list = list;
        }

        var acts = [];

        <?php
        include "../../db.php";
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' and BigNo='0' ORDER BY no ASC");
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
            echo("<option>등록자료  없음</option>");
        }
        mysqli_close($db);
        ?>

///////////////////////////////////////////////////////////////////////////////////////

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
/////////////////////////////////////////////////////////////////
            // var myListTreeSelectfrm = document.myForm;
            // var myListTreeSelectoriLen = myListTreeSelectfrm.myListTreeSelect.length;
            // var nummyListTreeSelectActs;

            // for (var i = 0; i < actsmyListTreeSelect.length; i++) {
            //     if (str == actsmyListTreeSelect[i].name) {
            //         nummyListTreeSelectActs = actsmyListTreeSelect[i].list.length;
            //         for (var j = 0; j < nummyListTreeSelectActs; j++)
            //             myListTreeSelectfrm.myListTreeSelect.options[j] = new Option(actsmyListTreeSelect[i].list[j], VLmyListTreeSelect[i].list[j]);
            //         for (var j = nummyListTreeSelectActs; j < myListTreeSelectoriLen; j++)
            //             myListTreeSelectfrm.myListTreeSelect.options[nummyListTreeSelectActs] = null;
            //     }
            // }
        }
    </script>
</head>

<form name="myForm" method='post' onsubmit='javascript:return MemberXCheckField()' action='<?php echo  $_SERVER['PHP_SELF'] ?>'>
    <select name="RadOne" onChange='updateList(this.value)'>
        <option value='#'>:::::: 선택하세요 ::::::</option>
        <?php
        include "../../db.php";
        $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
        $rows = mysqli_num_rows($result);
        if ($rows) {
            $r=0;
            while ($row = mysqli_fetch_array($result)) {
                ?>
                <option value='<?php echo  $row['no'] ?>' <?php if ($RadOne == $row['no']) { echo "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'"; } ?>><?php echo  $row['title'] ?></option>
                <?php
                	$r++;
            }
        } else {
            echo("<option>등록자료  없음</option>");
        }
        mysqli_close($db);
        ?>
    </select>


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
<!-- </form> --> 