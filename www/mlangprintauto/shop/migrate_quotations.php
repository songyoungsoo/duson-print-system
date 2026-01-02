<?php
/**
 * 견적서 테이블 마이그레이션 스크립트
 * quotations 테이블에 공개 링크 및 고객 응답 관련 컬럼 추가
 *
 * 사용 후 반드시 삭제할 것!
 */

// 간단한 보안 체크
$secret = $_GET['key'] ?? '';
if ($secret !== 'migrate2025') {
    die('Unauthorized');
}

require_once __DIR__ . '/../../db.php';

if (!$db) {
    die('DB 연결 실패');
}

mysqli_set_charset($db, 'utf8mb4');

$results = [];

// 1. public_token 컬럼 추가
$sql1 = "ALTER TABLE quotations ADD COLUMN public_token VARCHAR(64) UNIQUE AFTER quotation_no";
if (mysqli_query($db, $sql1)) {
    $results[] = "✅ public_token 컬럼 추가 완료";
} else {
    $error = mysqli_error($db);
    if (strpos($error, 'Duplicate column') !== false) {
        $results[] = "ℹ️ public_token 컬럼 이미 존재";
    } else {
        $results[] = "❌ public_token 추가 실패: " . $error;
    }
}

// 2. customer_response 컬럼 추가
$sql2 = "ALTER TABLE quotations ADD COLUMN customer_response ENUM('pending','accepted','rejected','negotiate') DEFAULT 'pending' AFTER status";
if (mysqli_query($db, $sql2)) {
    $results[] = "✅ customer_response 컬럼 추가 완료";
} else {
    $error = mysqli_error($db);
    if (strpos($error, 'Duplicate column') !== false) {
        $results[] = "ℹ️ customer_response 컬럼 이미 존재";
    } else {
        $results[] = "❌ customer_response 추가 실패: " . $error;
    }
}

// 3. response_date 컬럼 추가
$sql3 = "ALTER TABLE quotations ADD COLUMN response_date DATETIME NULL AFTER customer_response";
if (mysqli_query($db, $sql3)) {
    $results[] = "✅ response_date 컬럼 추가 완료";
} else {
    $error = mysqli_error($db);
    if (strpos($error, 'Duplicate column') !== false) {
        $results[] = "ℹ️ response_date 컬럼 이미 존재";
    } else {
        $results[] = "❌ response_date 추가 실패: " . $error;
    }
}

// 4. response_notes 컬럼 추가
$sql4 = "ALTER TABLE quotations ADD COLUMN response_notes TEXT NULL AFTER response_date";
if (mysqli_query($db, $sql4)) {
    $results[] = "✅ response_notes 컬럼 추가 완료";
} else {
    $error = mysqli_error($db);
    if (strpos($error, 'Duplicate column') !== false) {
        $results[] = "ℹ️ response_notes 컬럼 이미 존재";
    } else {
        $results[] = "❌ response_notes 추가 실패: " . $error;
    }
}

// 5. converted_order_no 컬럼 추가
$sql5 = "ALTER TABLE quotations ADD COLUMN converted_order_no VARCHAR(50) NULL AFTER response_notes";
if (mysqli_query($db, $sql5)) {
    $results[] = "✅ converted_order_no 컬럼 추가 완료";
} else {
    $error = mysqli_error($db);
    if (strpos($error, 'Duplicate column') !== false) {
        $results[] = "ℹ️ converted_order_no 컬럼 이미 존재";
    } else {
        $results[] = "❌ converted_order_no 추가 실패: " . $error;
    }
}

// 6. pdf_path 컬럼 추가
$sql6 = "ALTER TABLE quotations ADD COLUMN pdf_path VARCHAR(255) NULL AFTER converted_order_no";
if (mysqli_query($db, $sql6)) {
    $results[] = "✅ pdf_path 컬럼 추가 완료";
} else {
    $error = mysqli_error($db);
    if (strpos($error, 'Duplicate column') !== false) {
        $results[] = "ℹ️ pdf_path 컬럼 이미 존재";
    } else {
        $results[] = "❌ pdf_path 추가 실패: " . $error;
    }
}

// 7. 기존 레코드에 토큰 생성
$update_sql = "UPDATE quotations SET public_token = MD5(CONCAT(id, quotation_no, NOW(), RAND())) WHERE public_token IS NULL";
$affected = 0;
if (mysqli_query($db, $update_sql)) {
    $affected = mysqli_affected_rows($db);
    $results[] = "✅ 기존 레코드 {$affected}개에 토큰 생성 완료";
} else {
    $results[] = "❌ 토큰 생성 실패: " . mysqli_error($db);
}

// 결과 출력
echo "<html><head><meta charset='utf-8'><title>마이그레이션 결과</title></head><body>";
echo "<h1>견적서 테이블 마이그레이션</h1>";
echo "<ul>";
foreach ($results as $r) {
    echo "<li>{$r}</li>";
}
echo "</ul>";
echo "<p><strong>완료!</strong> 이 파일을 서버에서 삭제하세요.</p>";
echo "<p>삭제 명령: <code>rm mlangprintauto/shop/migrate_quotations.php</code></p>";
echo "</body></html>";
?>
