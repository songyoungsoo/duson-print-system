<?php
/**
 * quotes 테이블에 버전 관리 컬럼 추가
 * 웹 서버에서 1회만 실행: ?key=add_version_2025
 */

// 인증된 접근만 허용
$key = $_GET['key'] ?? '';
if ($key !== 'add_version_2025') {
    die('Unauthorized. Use ?key=add_version_2025');
}

require_once __DIR__ . '/../../db.php';

echo "<pre>\n";
echo "=== quotes 테이블 버전 관리 컬럼 추가 ===\n\n";

// 1. 컬럼이 이미 있는지 확인
$check_query = "SHOW COLUMNS FROM quotes LIKE 'original_quote_id'";
$result = mysqli_query($db, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "⚠️ 버전 관리 컬럼이 이미 존재합니다.\n";
    echo "추가 작업이 필요하지 않습니다.\n";
    mysqli_close($db);
    exit;
}

// 2. 컬럼 추가
$sql = "ALTER TABLE quotes
    ADD COLUMN original_quote_id INT NULL COMMENT '원본 견적서 ID (개정판인 경우)' AFTER id,
    ADD COLUMN version INT DEFAULT 1 COMMENT '버전 번호' AFTER original_quote_id,
    ADD COLUMN is_latest TINYINT(1) DEFAULT 1 COMMENT '최신 버전 여부 (1=최신, 0=이전버전)' AFTER version,
    ADD INDEX idx_original_quote (original_quote_id),
    ADD INDEX idx_is_latest (is_latest)";

if (mysqli_query($db, $sql)) {
    echo "✅ 버전 관리 컬럼 추가 완료!\n\n";
    echo "추가된 컬럼:\n";
    echo "  - original_quote_id (원본 견적서 ID)\n";
    echo "  - version (버전 번호)\n";
    echo "  - is_latest (최신 버전 여부)\n\n";
    echo "추가된 인덱스:\n";
    echo "  - idx_original_quote\n";
    echo "  - idx_is_latest\n\n";
} else {
    echo "❌ 오류 발생: " . mysqli_error($db) . "\n";
}

// 3. 결과 확인
echo "\n현재 quotes 테이블 구조:\n";
$columns = mysqli_query($db, "SHOW COLUMNS FROM quotes");
while ($col = mysqli_fetch_assoc($columns)) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}

echo "\n=== 작업 완료 ===\n";
echo "이 파일은 보안상 삭제하는 것을 권장합니다.\n";
echo "</pre>";

mysqli_close($db);
?>
