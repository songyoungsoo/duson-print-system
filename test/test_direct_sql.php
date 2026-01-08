<?php
include "db.php";

$test_session_id = 'test_sql_' . time();
$additional_options_json = '{"coating_enabled":1,"coating_type":"single_gloss"}';
$additional_options_total = 35000;

echo "<h2>Direct SQL Test</h2>";
echo "<p>Session: " . htmlspecialchars($test_session_id) . "</p>";
echo "<p>JSON: " . htmlspecialchars($additional_options_json) . "</p>";
echo "<p>Total: " . $additional_options_total . "</p>";

// Direct INSERT without prepared statement
$sql = "INSERT INTO shop_temp
        (session_id, product_type, additional_options, additional_options_total, regdate)
        VALUES (
            '" . mysqli_real_escape_string($db, $test_session_id) . "',
            'inserted',
            '" . mysqli_real_escape_string($db, $additional_options_json) . "',
            " . intval($additional_options_total) . ",
            " . time() . "
        )";

echo "<h3>SQL:</h3><pre>" . htmlspecialchars($sql) . "</pre>";

if (mysqli_query($db, $sql)) {
    echo "<p style='color: green;'>✅ Direct SQL 성공</p>";

    // 조회
    $result = mysqli_query($db, "SELECT additional_options, additional_options_total
                                  FROM shop_temp
                                  WHERE session_id = '" . mysqli_real_escape_string($db, $test_session_id) . "'");
    $row = mysqli_fetch_assoc($result);

    echo "<h3>저장된 데이터:</h3>";
    echo "<pre>additional_options: " . htmlspecialchars($row['additional_options']) . "</pre>";
    echo "<pre>additional_options_total: " . $row['additional_options_total'] . "</pre>";
} else {
    echo "<p style='color: red;'>❌ Direct SQL 실패: " . htmlspecialchars(mysqli_error($db)) . "</p>";
}

mysqli_close($db);
?>
