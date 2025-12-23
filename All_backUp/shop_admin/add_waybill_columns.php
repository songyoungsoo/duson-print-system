<?php
/**
 * mlangorder_printauto 테이블에 운송장 관련 컬럼 추가
 * 한 번만 실행하면 됩니다.
 * 실행 후 이 파일은 삭제하셔도 됩니다.
 */

require_once __DIR__ . '/../db.php';

echo "<h2>운송장 컬럼 추가 스크립트</h2>";
echo "<p>mlangorder_printauto 테이블에 다음 컬럼을 추가합니다:</p>";
echo "<ul>";
echo "<li>waybill_no (운송장번호) - VARCHAR(50)</li>";
echo "<li>waybill_date (운송장 등록일시) - DATETIME</li>";
echo "<li>delivery_company (택배사) - VARCHAR(50)</li>";
echo "</ul>";

// 기존 컬럼 확인
$check_sql = "SHOW COLUMNS FROM mlangorder_printauto LIKE 'waybill_no'";
$check_result = mysqli_query($db, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    echo "<div style='color: orange; padding: 10px; border: 2px solid orange; margin: 10px 0;'>";
    echo "<strong>⚠️ 이미 컬럼이 존재합니다!</strong><br>";
    echo "waybill_no 컬럼이 이미 테이블에 있습니다. 추가 작업이 필요하지 않습니다.";
    echo "</div>";
} else {
    echo "<hr>";
    echo "<p><strong>컬럼 추가 시작...</strong></p>";

    $sql = "ALTER TABLE mlangorder_printauto
            ADD COLUMN waybill_no VARCHAR(50) DEFAULT NULL COMMENT '운송장번호',
            ADD COLUMN waybill_date DATETIME DEFAULT NULL COMMENT '운송장 등록일시',
            ADD COLUMN delivery_company VARCHAR(50) DEFAULT NULL COMMENT '택배사'";

    if (mysqli_query($db, $sql)) {
        echo "<div style='color: green; padding: 10px; border: 2px solid green; margin: 10px 0;'>";
        echo "<strong>✅ 성공!</strong><br>";
        echo "운송장 관련 컬럼 3개가 성공적으로 추가되었습니다.";
        echo "</div>";

        // 확인
        echo "<p><strong>추가된 컬럼 확인:</strong></p>";
        $verify_sql = "SHOW COLUMNS FROM mlangorder_printauto WHERE Field IN ('waybill_no', 'waybill_date', 'delivery_company')";
        $verify_result = mysqli_query($db, $verify_sql);

        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>기본값</th><th>설명</th></tr>";
        while ($row = mysqli_fetch_assoc($verify_result)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['Comment'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<hr>";
        echo "<p style='color: blue;'><strong>다음 단계:</strong></p>";
        echo "<ol>";
        echo "<li>이 페이지를 닫으세요</li>";
        echo "<li>운송장 업로드 기능을 테스트하세요</li>";
        echo "<li>정상 작동 확인 후 이 파일(add_waybill_columns.php)을 삭제하세요</li>";
        echo "</ol>";

    } else {
        echo "<div style='color: red; padding: 10px; border: 2px solid red; margin: 10px 0;'>";
        echo "<strong>❌ 오류 발생!</strong><br>";
        echo "컬럼 추가 중 오류가 발생했습니다:<br>";
        echo mysqli_error($db);
        echo "</div>";

        echo "<p><strong>해결 방법:</strong></p>";
        echo "<p>PhpMyAdmin이나 MySQL 관리 도구에서 다음 SQL을 직접 실행해주세요:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo htmlspecialchars($sql);
        echo "</pre>";
    }
}

mysqli_close($db);
?>
