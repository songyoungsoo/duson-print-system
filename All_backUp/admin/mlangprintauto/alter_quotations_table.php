<?php
/**
 * quotations 테이블 수정 스크립트
 * 견적서 시스템 개선을 위한 컬럼 추가 및 수정
 *
 * 실행 방법: http://localhost/admin/mlangprintauto/alter_quotations_table.php
 * 주의: 한 번만 실행하세요!
 */

session_start();
require_once __DIR__ . '/../../db.php';

echo "<!DOCTYPE html>
<html lang='ko'>
<head>
    <meta charset='UTF-8'>
    <title>quotations 테이블 수정</title>
    <style>
        body { font-family: 'Noto Sans', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .info { color: #0066cc; padding: 10px; background: #e7f3ff; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white;
               text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 quotations 테이블 수정</h1>";

// 1. quotations 테이블 존재 확인
echo "<h2>1️⃣ quotations 테이블 확인</h2>";
$check_sql = "SHOW TABLES LIKE 'quotations'";
$result = mysqli_query($db, $check_sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<div class='error'>";
    echo "❌ quotations 테이블이 존재하지 않습니다.<br>";
    echo "먼저 quotations 테이블을 생성해주세요.";
    echo "</div>";
    echo "</div></body></html>";
    exit;
}

echo "<div class='success'>✅ quotations 테이블이 존재합니다.</div>";

// 2. 현재 테이블 구조 확인
echo "<h2>2️⃣ 현재 테이블 구조</h2>";
$describe_sql = "DESCRIBE quotations";
$desc_result = mysqli_query($db, $describe_sql);

$existing_columns = [];
if ($desc_result) {
    echo "<pre>";
    printf("%-25s %-30s %-10s\n", "Field", "Type", "Null");
    echo str_repeat("-", 70) . "\n";
    while ($row = mysqli_fetch_assoc($desc_result)) {
        printf("%-25s %-30s %-10s\n",
               $row['Field'],
               $row['Type'],
               $row['Null']);
        $existing_columns[] = $row['Field'];
    }
    echo "</pre>";
}

// 3. 수정 작업 실행
echo "<h2>3️⃣ 테이블 수정 작업</h2>";

$alterations = [];
$errors = [];

// 3-1. status ENUM에 'converted' 추가
if (in_array('status', $existing_columns)) {
    echo "<div class='info'>🔄 status 컬럼에 'converted' 값 추가...</div>";

    $alter_status = "ALTER TABLE quotations
                     MODIFY COLUMN status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired', 'converted')
                     DEFAULT 'draft'
                     COMMENT '견적서 상태'";

    if (mysqli_query($db, $alter_status)) {
        $alterations[] = "✅ status 컬럼 수정 성공 ('converted' 추가)";
    } else {
        $errors[] = "status 컬럼 수정 실패: " . mysqli_error($db);
    }
}

// 3-2. payment_terms 컬럼 추가
if (!in_array('payment_terms', $existing_columns)) {
    echo "<div class='info'>➕ payment_terms 컬럼 추가...</div>";

    $add_payment = "ALTER TABLE quotations
                    ADD COLUMN payment_terms VARCHAR(100) DEFAULT '발행일로부터 7일'
                    COMMENT '결제조건'
                    AFTER delivery_price";

    if (mysqli_query($db, $add_payment)) {
        $alterations[] = "✅ payment_terms 컬럼 추가 성공";
    } else {
        $errors[] = "payment_terms 컬럼 추가 실패: " . mysqli_error($db);
    }
} else {
    $alterations[] = "ℹ️  payment_terms 컬럼이 이미 존재합니다";
}

// 3-3. delivery_address 컬럼 추가
if (!in_array('delivery_address', $existing_columns)) {
    echo "<div class='info'>➕ delivery_address 컬럼 추가...</div>";

    $add_address = "ALTER TABLE quotations
                    ADD COLUMN delivery_address TEXT
                    COMMENT '배송지 주소'
                    AFTER payment_terms";

    if (mysqli_query($db, $add_address)) {
        $alterations[] = "✅ delivery_address 컬럼 추가 성공";
    } else {
        $errors[] = "delivery_address 컬럼 추가 실패: " . mysqli_error($db);
    }
} else {
    $alterations[] = "ℹ️  delivery_address 컬럼이 이미 존재합니다";
}

// 3-4. 인덱스 추가 (status, customer_response)
echo "<div class='info'>🔍 인덱스 추가...</div>";

$index_check = "SHOW INDEX FROM quotations WHERE Key_name = 'idx_status_response'";
$index_result = mysqli_query($db, $index_check);

if (!$index_result || mysqli_num_rows($index_result) === 0) {
    $add_index = "ALTER TABLE quotations
                  ADD INDEX idx_status_response (status, customer_response)";

    if (mysqli_query($db, $add_index)) {
        $alterations[] = "✅ idx_status_response 인덱스 추가 성공";
    } else {
        $errors[] = "인덱스 추가 실패: " . mysqli_error($db);
    }
} else {
    $alterations[] = "ℹ️  idx_status_response 인덱스가 이미 존재합니다";
}

// 4. 결과 출력
echo "<h2>4️⃣ 수정 결과</h2>";

if (count($alterations) > 0) {
    echo "<div class='success'>";
    echo "<h3>✅ 수정 완료</h3>";
    echo "<ul>";
    foreach ($alterations as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (count($errors) > 0) {
    echo "<div class='error'>";
    echo "<h3>❌ 오류 발생</h3>";
    echo "<ul>";
    foreach ($errors as $err) {
        echo "<li>$err</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// 5. 수정된 테이블 구조 확인
echo "<h2>5️⃣ 수정된 테이블 구조</h2>";
$final_describe = "DESCRIBE quotations";
$final_result = mysqli_query($db, $final_describe);

if ($final_result) {
    echo "<pre>";
    printf("%-25s %-35s %-10s %-15s\n", "Field", "Type", "Null", "Default");
    echo str_repeat("-", 90) . "\n";

    while ($row = mysqli_fetch_assoc($final_result)) {
        printf("%-25s %-35s %-10s %-15s\n",
               $row['Field'],
               $row['Type'],
               $row['Null'],
               $row['Default'] ?? 'NULL');
    }
    echo "</pre>";
}

// 6. 인덱스 확인
echo "<h2>6️⃣ 생성된 인덱스</h2>";
$show_indexes = "SHOW INDEX FROM quotations";
$indexes_result = mysqli_query($db, $show_indexes);

if ($indexes_result) {
    echo "<pre>";
    printf("%-30s %-25s\n", "Key Name", "Column");
    echo str_repeat("-", 60) . "\n";

    while ($row = mysqli_fetch_assoc($indexes_result)) {
        printf("%-30s %-25s\n",
               $row['Key_name'],
               $row['Column_name']);
    }
    echo "</pre>";
}

echo "<div class='info' style='margin-top: 30px;'>";
echo "<strong>📝 변경 사항 요약:</strong><br><ul>";
echo "<li><strong>status</strong>: 'converted' 값 추가 (견적→주문 전환 시 사용)</li>";
echo "<li><strong>payment_terms</strong>: 결제조건 저장 (기본값: 발행일로부터 7일)</li>";
echo "<li><strong>delivery_address</strong>: 배송지 주소 저장</li>";
echo "<li><strong>idx_status_response</strong>: status, customer_response 복합 인덱스 (검색 최적화)</li>";
echo "</ul></div>";

echo "<div class='warning' style='margin-top: 20px;'>";
echo "<strong>⚠️ 보안 권장사항:</strong><br>";
echo "이 스크립트는 한 번만 실행하면 됩니다.<br>";
echo "실행 후 FTP에서 <code>/admin/mlangprintauto/alter_quotations_table.php</code> 파일을 삭제하세요.";
echo "</div>";

echo "<a href='/admin/mlangprintauto/admin.php' class='btn'>← 관리자 페이지로 돌아가기</a>";

echo "</div>
</body>
</html>";

mysqli_close($db);
?>
