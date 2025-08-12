<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Select Form</title>
    <script language="JavaScript">
        // 브라우저 창 크기 설정
        self.moveTo(0, 0);
        self.resizeTo(400, 350);

        function Activity(name, list) {
            this.name = name;
            this.list = list;
        }

        var acts = [];
        var VL = [];

        <?php
        include "../../db.php";
        
        $stmt = $db->prepare("SELECT no, title FROM $GGTABLE WHERE Ttable = ? AND BigNo = '0' ORDER BY no ASC");
        $stmt->bind_param("s", $Ttable);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $g = 0;
            while ($row = $result->fetch_assoc()) {
                echo "acts[$g] = new Activity('" . addslashes($row['no']) . "', [";
                
                // 하위 항목을 배열로 가져와 JavaScript로 전달
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
            echo "document.write('<option>등록된 자료가 없습니다.</option>');";
        }

        $stmt->close();
        $db->close();
        ?>

        // 옵션 목록을 업데이트하는 함수
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

<form name="myForm" method="post" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>">
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
                    $stmt = $db->prepare("SELECT no, title FROM $GGTABLE WHERE Ttable = ? AND BigNo = '0' ORDER BY no ASC");
                    $stmt->bind_param("s", $Ttable);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        $selected = ($code == "Modify" && $MlangPrintAutoFildView_style == $row['no']) ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "";
                        echo "<option value='" . htmlspecialchars($row['no']) . "' {$selected}>" . htmlspecialchars($row['title']) . "</option>";
                    }

                    $stmt->close();
                    $db->close();
                    ?>
                </select>
            </td>
        </tr>
    </table>
</form>

</body>
</html>
