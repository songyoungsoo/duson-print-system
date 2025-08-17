<?php
include "db.php";

echo "<h2>📋 테이블 구조 비교</h2>";

// member 테이블 구조
echo "<h3>🔍 member 테이블 구조:</h3>";
$member_desc = mysqli_query($db, "DESCRIBE member");
if ($member_desc) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>필드명</th><th>타입</th><th>NULL</th><th>키</th><th>기본값</th></tr>";
    while ($row = mysqli_fetch_assoc($member_desc)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ member 테이블을 찾을 수 없습니다.</p>";
}

echo "<br>";

// users 테이블 구조
echo "<h3>🔍 users 테이블 구조:</h3>";
$users_desc = mysqli_query($db, "DESCRIBE users");
if ($users_desc) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>필드명</th><th>타입</th><th>NULL</th><th>키</th><th>기본값</th></tr>";
    while ($row = mysqli_fetch_assoc($users_desc)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ users 테이블을 찾을 수 없습니다.</p>";
}

// 샘플 데이터 확인
echo "<h3>📊 member 테이블 샘플 데이터:</h3>";
$sample = mysqli_query($db, "SELECT * FROM member LIMIT 3");
if ($sample) {
    $first_row = true;
    while ($row = mysqli_fetch_assoc($sample)) {
        if ($first_row) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            $first_row = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>