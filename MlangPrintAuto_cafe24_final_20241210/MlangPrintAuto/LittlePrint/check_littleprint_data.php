<?php
include "../../db.php";
mysqli_set_charset($db, "utf8");

header("Content-Type: text/html; charset=utf-8");

echo "<h2>mlangprintauto_littleprint 테이블 분석</h2>";

// 각 재질별 사용 가능한 규격 확인
$materials = [
    '604' => '120아트/스노우',
    '605' => '150아트/스노우',
    '606' => '180아트/스노우',
    '607' => '200아트/스노우',
    '608' => '250아트/스노우',
    '609' => '300아트/스노우',
    '679' => '80모조',
    '680' => '100모조',
    '958' => '200g아트/스노우지'
];

foreach ($materials as $id => $name) {
    echo "<h3>재질: $name ($id)</h3>";
    
    $query = "SELECT DISTINCT Section FROM MlangPrintAuto_LittlePrint WHERE TreeSelect = '$id' ORDER BY Section";
    $result = mysqli_query($db, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $sections = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row['Section'];
        }
        
        // 규격 이름 가져오기
        if (!empty($sections)) {
            $section_ids_str = "'" . implode("','", $sections) . "'";
            $query2 = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE no IN ($section_ids_str)";
            $result2 = mysqli_query($db, $query2);
            
            echo "<ul>";
            while ($row2 = mysqli_fetch_assoc($result2)) {
                echo "<li>[{$row2['no']}] {$row2['title']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color:red;'>사용 가능한 규격 없음</p>";
    }
}

// 전체 데이터 구조 확인
echo "<h2>전체 데이터 매핑 (상위 10개)</h2>";
$query = "SELECT l.*, 
          t1.title as style_name,
          t2.title as section_name,
          t3.title as treeselect_name
          FROM MlangPrintAuto_LittlePrint l
          LEFT JOIN MlangPrintAuto_transactionCate t1 ON l.style = t1.no
          LEFT JOIN MlangPrintAuto_transactionCate t2 ON l.Section = t2.no
          LEFT JOIN MlangPrintAuto_transactionCate t3 ON l.TreeSelect = t3.no
          ORDER BY l.TreeSelect, l.Section, l.POtype, l.quantity
          LIMIT 20";

$result = mysqli_query($db, $query);
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>style</th><th>style_name</th><th>TreeSelect</th><th>treeselect_name</th><th>Section</th><th>section_name</th><th>POtype</th><th>quantity</th><th>money</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['style']}</td>";
        echo "<td>{$row['style_name']}</td>";
        echo "<td>{$row['TreeSelect']}</td>";
        echo "<td>{$row['treeselect_name']}</td>";
        echo "<td>{$row['Section']}</td>";
        echo "<td>{$row['section_name']}</td>";
        echo "<td>{$row['POtype']}</td>";
        echo "<td>{$row['quantity']}</td>";
        echo "<td>{$row['money']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>