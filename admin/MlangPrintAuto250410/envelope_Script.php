<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Form</title>
    <script language="JavaScript">
        self.moveTo(0,0);
        self.resizeTo(350, 250);

        function Activity(name, list) {
            this.name = name;
            this.list = list;
        }

        var acts = [];
        var VL = [];

        <?php
        include "../../db.php";

        // 최상위 항목 가져오기
        $stmt = $db->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? AND BigNo = '0' ORDER BY no ASC");
        $stmt->bind_param("s", $Ttable);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $g = 0;
            while ($row = $result->fetch_assoc()) {
                echo "acts[$g] = new Activity('{$row['no']}', [";

                // 하위 항목 가져오기
                $stmt_two = $db->prepare("SELECT * FROM $GGTABLE WHERE BigNo = ? ORDER BY no ASC");
                $stmt_two->bind_param("s", $row['no']);
                $stmt_two->execute();
                $result_two = $stmt_two->get_result();

                $items = [];
                while ($row_two = $result_two->fetch_assoc()) {
                    $items[] = "'" . addslashes($row_two['title']) . "'";
                }
                echo implode(", ", $items) . "]);\n";

                echo "VL[$g] = new Activity('{$row['no']}', [" . implode(", ", array_column($result_two->fetch_all(MYSQLI_ASSOC), 'no')) . "]);\n";
                $g++;
            }
        } else {
            echo "document.write('<option>등록자료 없음</option>');";
        }

        $stmt->close();
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
    </script>
</head>
<body>

<form name="myForm" method="post" onsubmit="javascript:return MemberXCheckField()" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>">
    <?php if ($code == "Modify") { ?>
        <input type="hidden" name="mode" value="Modify_ok">
        <input type="hidden" name="no" value="<?php echo  htmlspecialchars($no) ?>">
    <?php } else { ?>
        <input type="hidden" name="mode" value="form_ok">
    <?php } ?>

    <input type="hidden" name="Ttable" value="<?php echo  htmlspecialchars($Ttable) ?>">

    <table>
        <tr>
            <td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1) ?>" width="100" class="Left1" align="right">구분&nbsp;&nbsp;</td>
            <td>
                <select name="RadOne" onchange="updateList(this.value)">
                    <option value="#">:::::: 선택하세요 ::::::</option>
                    <?php
                    include "../../db.php";
                    $stmt = $db->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? AND BigNo = '0' ORDER BY no ASC");
                    $stmt->bind_param("s", $Ttable);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        $selected = ($code == "Modify" && $MlangPrintAutoFildView_style == $row['no']) ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "";
                        echo "<option value='{$row['no']}' {$selected}>" . htmlspecialchars($row['title']) . "</option>";
                    }
                    $stmt->close();
                    $db->close();
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1) ?>" width="100" class="Left1" align="right">종류&nbsp;&nbsp;</td>
            <td>
                <select name="myList">
                    <option value="#">:::::: 선택하세요 ::::::</option>
                    <?php
                    if ($code == "Modify" && !empty($MlangPrintAutoFildView_Section)) {
                        echo "<option value='" . htmlspecialchars($MlangPrintAutoFildView_Section) . "' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";

                        include "../../db.php";
                        $stmt = $db->prepare("SELECT title FROM $GGTABLE WHERE no = ?");
                        $stmt->bind_param("s", $MlangPrintAutoFildView_Section);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($row = $result->fetch_assoc()) {
                            echo htmlspecialchars($row['title']);
                        }
                        echo "</option>";

                        $stmt->close();
                        $db->close();
                    }
                    ?>
                </select>
            </td>
        </tr>