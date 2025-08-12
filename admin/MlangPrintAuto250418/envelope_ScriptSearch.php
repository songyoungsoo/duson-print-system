<?php
header('Content-Type: text/html; charset=utf-8');
?>
<head>
    <script language="JavaScript">
        function Activity(name, list) {
            this.name = name;
            this.list = list;
        }

        var acts = [];
        var VL = [];

        <?php
        include "../../db.php";

        // 상위 항목 및 하위 항목들을 JavaScript 배열로 로드
        $stmt = $db->prepare("SELECT no, title FROM $GGTABLE WHERE Ttable = ? AND BigNo = '0' ORDER BY no ASC");
        $stmt->bind_param("s", $Ttable);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $g = 0;
            while ($row = $result->fetch_assoc()) {
                // 상위 항목과 하위 항목 리스트를 JavaScript 변수에 설정
                echo "acts[$g] = new Activity('{$row['no']}', [";
                
                $stmt_two = $db->prepare("SELECT title FROM $GGTABLE WHERE BigNo = ? ORDER BY no ASC");
                $stmt_two->bind_param("s", $row['no']);
                $stmt_two->execute();
                $result_two = $stmt_two->get_result();
                
                $items = [];
                while ($row_two = $result_two->fetch_assoc()) {
                    $items[] = "'" . addslashes($row_two['title']) . "'";
                }
                echo implode(", ", $items) . "]);\n";
                
                // ID 값을 VL 배열에 저장
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

            for (var i = 0; i < acts.length; i++) {
                if (str == acts[i].name) {
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
            echo "<option value='{$row['no']}' {$selected}>" . htmlspecialchars($row['title']) . "</option>";
        }

        $stmt->close();
        $db->close();
        ?>
    </select>

    <select name="myList">
        <option value="#">:::::: 선택하세요 ::::::</option>
        <?php
        if (!empty($myList)) {
            echo "<option value='" . htmlspecialchars($myList) . "' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";

            include "../../db.php";
            $stmt = $db->prepare("SELECT title FROM $GGTABLE WHERE no = ?");
            $stmt->bind_param("s", $myList);
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