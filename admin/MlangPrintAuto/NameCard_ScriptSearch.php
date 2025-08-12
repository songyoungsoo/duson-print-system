<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <script language="JavaScript">
        function Activity(name, list) {
            this.name = name;
            this.list = list;
        }

        var acts = [];
        var VL = [];

        <?php
        include "../../db.php";

        // 상위 항목을 가져오고 하위 항목을 JavaScript 배열로 전달
        $stmt = $db->prepare("SELECT no, title FROM $GGTABLE WHERE Ttable = ? AND BigNo = '0' ORDER BY no ASC");
        $stmt->bind_param("s", $Ttable);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $g = 0;
            while ($row = $result->fetch_assoc()) {
                echo "acts[$g] = new Activity('" . addslashes($row['no']) . "', [";

                $stmt_two = $db->prepare("SELECT title, no FROM $GGTABLE WHERE BigNo = ? ORDER BY no ASC");
                $stmt_two->bind_param("s", $row['no']);
                $stmt_two->execute();
                $result_two = $stmt_two->get_result();

                $titles = [];
                $ids = [];
                while ($row_two = $result_two->fetch_assoc()) {
                    $titles[] = "'" . addslashes($row_two['title']) . "'";
                    $ids[] = "'" . addslashes($row_two['no']) . "'";
                }

                echo implode(", ", $titles) . "]);\n";
                echo "VL[$g] = new Activity('" . addslashes($row['no']) . "', [" . implode(", ", $ids) . "]);\n";
                $g++;
            }
        } else {
            echo "document.write('<option>등록자료 없음</option>');";
        }

        $stmt->close();
        $db->close();
        ?>

        function updateList(selectedValue) {
            var frm = document.myForm;
            var oriLen = frm.myList.length;

            for (var i = 0; i < acts.length; i++) {
                if (selectedValue == acts[i].name) {
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
    </script>
</head>

<body>

<form name="myForm" method="post" onsubmit="javascript:return MemberXCheckField()" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>">
    <select name="RadOne" onchange="updateList(this.value)">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        include "../../db.php";
        $stmt = $db->prepare("SELECT no, title FROM $GGTABLE WHERE Ttable = ? AND BigNo = '0' ORDER BY no ASC");
        $stmt->bind_param("s", $Ttable);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $selected = ($RadOne == $row['no']) ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "";
            echo "<option value='" . htmlspecialchars($row['no']) . "' {$selected}>" . htmlspecialchars($row['title']) . "</option>";
        }

        $stmt->close();
        $db->close();
        ?>
    </select>

    <select name="myList">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        if (!empty($myList)) {
            include "../../db.php";
            $stmt = $db->prepare("SELECT title FROM $GGTABLE WHERE no = ?");
            $stmt->bind_param("s", $myList);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($myList) . "' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>" . htmlspecialchars($row['title']) . "</option>";
            }

            $stmt->close();
            $db->close();
        }
        ?>
    </select>
