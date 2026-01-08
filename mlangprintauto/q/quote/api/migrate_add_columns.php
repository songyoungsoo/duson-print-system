<?php
/**
 * quotes 테이블에 누락된 컬럼 추가 마이그레이션
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Quotes 테이블 마이그레이션</h2>";

if (!$db) {
    die("DB 연결 실패");
}

$alterQueries = [
    "ALTER TABLE quotes ADD COLUMN IF NOT EXISTS customer_notes TEXT NULL AFTER customer_response",
    "ALTER TABLE quotes ADD COLUMN IF NOT EXISTS responded_at DATETIME NULL AFTER customer_notes",
    "ALTER TABLE quotes ADD COLUMN IF NOT EXISTS response_date DATETIME NULL AFTER responded_at",
    "ALTER TABLE quotes ADD COLUMN IF NOT EXISTS response_notes TEXT NULL AFTER response_date"
];

// MySQL 5.6 이하에서는 IF NOT EXISTS가 지원되지 않으므로 수동 체크
$existingColumns = [];
$result = mysqli_query($db, "DESCRIBE quotes");
while ($row = mysqli_fetch_assoc($result)) {
    $existingColumns[] = $row['Field'];
}

echo "<p><strong>현재 컬럼:</strong> " . implode(', ', $existingColumns) . "</p>";

$columnsToAdd = [
    'customer_notes' => "ALTER TABLE quotes ADD COLUMN customer_notes TEXT NULL",
    'responded_at' => "ALTER TABLE quotes ADD COLUMN responded_at DATETIME NULL",
    'response_date' => "ALTER TABLE quotes ADD COLUMN response_date DATETIME NULL",
    'response_notes' => "ALTER TABLE quotes ADD COLUMN response_notes TEXT NULL"
];

$added = [];
$skipped = [];

foreach ($columnsToAdd as $column => $sql) {
    if (in_array($column, $existingColumns)) {
        $skipped[] = $column;
        echo "<p>⏭️ <strong>$column</strong>: 이미 존재함</p>";
    } else {
        if (mysqli_query($db, $sql)) {
            $added[] = $column;
            echo "<p>✅ <strong>$column</strong>: 추가됨</p>";
        } else {
            echo "<p>❌ <strong>$column</strong>: 실패 - " . mysqli_error($db) . "</p>";
        }
    }
}

echo "<hr>";
echo "<p><strong>추가된 컬럼:</strong> " . (empty($added) ? '없음' : implode(', ', $added)) . "</p>";
echo "<p><strong>건너뛴 컬럼:</strong> " . (empty($skipped) ? '없음' : implode(', ', $skipped)) . "</p>";
echo "<p>✅ 마이그레이션 완료!</p>";
?>
