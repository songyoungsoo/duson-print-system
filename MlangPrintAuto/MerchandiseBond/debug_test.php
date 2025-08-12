<?php
// 디버깅용 테스트 파일
include "../../db_auto.php";

echo "<h2>상품권 데이터베이스 구조 확인</h2>";

// 1. 테이블 존재 확인
$tables = ['MlangPrintAuto_MerchandiseBond', 'MlangPrintAuto_transactionCate'];

foreach ($tables as $table) {
    $result = mysqli_query($db, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p>✅ 테이블 '$table' 존재함</p>";
        
        // 테이블 구조 확인
        $structure = mysqli_query($db, "DESCRIBE $table");
        echo "<h3>$table 구조:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = mysqli_fetch_assoc($structure)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // 샘플 데이터 확인
        $sample = mysqli_query($db, "SELECT * FROM $table LIMIT 5");
        if (mysqli_num_rows($sample) > 0) {
            echo "<h3>$table 샘플 데이터:</h3>";
            echo "<table border='1'>";
            $first = true;
            while ($row = mysqli_fetch_assoc($sample)) {
                if ($first) {
                    echo "<tr>";
                    foreach (array_keys($row) as $key) {
                        echo "<th>$key</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>$value</td>";
                }
                echo "</tr>";
            }
            echo "</table><br>";
        } else {
            echo "<p>⚠️ $table에 데이터가 없습니다.</p>";
        }
    } else {
        echo "<p>❌ 테이블 '$table' 존재하지 않음</p>";
    }
}

// 2. 상품권 카테고리 확인
echo "<h2>상품권 카테고리 확인</h2>";
$cate_result = mysqli_query($db, "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='MerchandiseBond' AND BigNo='0' ORDER BY no ASC");
if (mysqli_num_rows($cate_result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>no</th><th>title</th><th>BigNo</th><th>Ttable</th></tr>";
    while ($row = mysqli_fetch_assoc($cate_result)) {
        echo "<tr>";
        echo "<td>{$row['no']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['BigNo']}</td>";
        echo "<td>{$row['Ttable']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ 상품권 카테고리 데이터가 없습니다.</p>";
}

// 3. 첫 번째 카테고리의 하위 옵션 확인
$first_cate = mysqli_query($db, "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='MerchandiseBond' AND BigNo='0' ORDER BY no ASC LIMIT 1");
if ($first_row = mysqli_fetch_assoc($first_cate)) {
    $first_no = $first_row['no'];
    echo "<h2>첫 번째 카테고리 ({$first_row['title']})의 하위 옵션</h2>";
    
    $sub_result = mysqli_query($db, "SELECT * FROM MlangPrintAuto_transactionCate WHERE BigNo='$first_no' ORDER BY no ASC");
    if (mysqli_num_rows($sub_result) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>no</th><th>title</th><th>BigNo</th></tr>";
        while ($row = mysqli_fetch_assoc($sub_result)) {
            echo "<tr>";
            echo "<td>{$row['no']}</td>";
            echo "<td>{$row['title']}</td>";
            echo "<td>{$row['BigNo']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>⚠️ 하위 옵션이 없습니다.</p>";
    }
}

mysqli_close($db);
?>