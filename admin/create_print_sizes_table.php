<?php
/**
 * 인쇄 규격 테이블 생성 및 초기 데이터 입력
 *
 * 실행: php admin/create_print_sizes_table.php
 * 또는 브라우저에서 직접 접근
 *
 * @date 2025-12-03
 */

require_once __DIR__ . '/../db.php';

echo "<pre style='font-family: monospace; padding: 20px;'>\n";
echo "=== 인쇄 규격 테이블 생성 ===\n\n";

// 1. 테이블 생성 (MySQL/MariaDB 호환)
$create_table = "
CREATE TABLE IF NOT EXISTS print_sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL COMMENT '규격명 (A4, B5 등)',
    width INT NOT NULL COMMENT '가로 크기 (mm)',
    height INT NOT NULL COMMENT '세로 크기 (mm)',
    jeolsu INT NOT NULL COMMENT '절수 (2, 4, 8 등)',
    series CHAR(1) NOT NULL DEFAULT 'A' COMMENT '시리즈 (A 또는 B)',
    sheets_per_yeon INT NOT NULL DEFAULT 500 COMMENT '1연당 매수 (500 * jeolsu)',
    sort_order INT DEFAULT 0 COMMENT '정렬 순서',
    is_active TINYINT(1) DEFAULT 1 COMMENT '활성화 여부',
    description VARCHAR(100) DEFAULT NULL COMMENT '설명',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name),
    INDEX idx_series (series),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='인쇄 규격 관리 테이블'
";

if (mysqli_query($db, $create_table)) {
    echo "✅ print_sizes 테이블 생성 완료\n";
} else {
    echo "❌ 테이블 생성 실패: " . mysqli_error($db) . "\n";
    exit;
}

// 2. 기존 데이터 확인
$check = mysqli_query($db, "SELECT COUNT(*) as cnt FROM print_sizes");
$row = mysqli_fetch_assoc($check);

if ($row['cnt'] > 0) {
    echo "ℹ️  이미 {$row['cnt']}개의 규격 데이터가 있습니다.\n";
    echo "   초기화하려면 ?reset=1 파라미터를 추가하세요.\n";

    if (isset($_GET['reset']) && $_GET['reset'] == '1') {
        mysqli_query($db, "TRUNCATE TABLE print_sizes");
        echo "🔄 테이블 초기화 완료\n";
    } else {
        echo "\n=== 완료 ===\n";
        echo "</pre>";
        exit;
    }
}

// 3. 초기 데이터 입력
// [name, width, height, jeolsu, series, sort_order, description]
// sheets_per_yeon = 500 * jeolsu (PHP에서 계산)
$initial_data = [
    // A 시리즈 (ISO)
    ['A1', 594, 841, 1, 'A', 1, 'A 시리즈 전지'],
    ['A2', 420, 594, 2, 'A', 2, 'A 시리즈 2절'],
    ['A3', 297, 420, 4, 'A', 3, 'A 시리즈 4절'],
    ['A4', 210, 297, 8, 'A', 4, 'A 시리즈 8절 (일반 복사용지)'],
    ['A5', 148, 210, 16, 'A', 5, 'A 시리즈 16절'],
    ['A6', 105, 148, 32, 'A', 6, 'A 시리즈 32절'],

    // B 시리즈 (JIS - 한국 사용)
    ['B1', 728, 1030, 1, 'B', 11, 'B 시리즈 전지 (JIS)'],
    ['B2', 515, 728, 2, 'B', 12, 'B 시리즈 2절'],
    ['B3', 364, 515, 4, 'B', 13, 'B 시리즈 4절'],
    ['B4', 257, 364, 8, 'B', 14, 'B 시리즈 8절'],
    ['B5', 182, 257, 16, 'B', 15, 'B 시리즈 16절 (한국 교과서)'],
    ['B6', 128, 182, 32, 'B', 16, 'B 시리즈 32절'],
];

$insert_sql = "INSERT INTO print_sizes (name, width, height, jeolsu, series, sheets_per_yeon, sort_order, description)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($db, $insert_sql);

$success_count = 0;
foreach ($initial_data as $data) {
    $sheets_per_yeon = 500 * $data[3];  // 500 * jeolsu
    mysqli_stmt_bind_param($stmt, "siiisiis",
        $data[0], $data[1], $data[2], $data[3], $data[4], $sheets_per_yeon, $data[5], $data[6]
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "✅ {$data[0]} ({$data[1]}×{$data[2]}mm, {$data[3]}절) 추가\n";
        $success_count++;
    } else {
        echo "❌ {$data[0]} 추가 실패: " . mysqli_error($db) . "\n";
    }
}

mysqli_stmt_close($stmt);

echo "\n=== 완료 ===\n";
echo "총 {$success_count}개 규격 데이터 입력 완료\n";
echo "\n관리자 페이지: /admin/print_sizes.php\n";
echo "</pre>";

mysqli_close($db);
?>
