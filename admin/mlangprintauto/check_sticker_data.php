<?php
/**
 * 스티커 주문 데이터 진단 스크립트
 * 2026-01-13 - 프로덕션 데이터 상태 확인용
 */

include "../../db.php";
header('Content-Type: text/html; charset=utf-8');

$order_no = $_GET['no'] ?? 84512;

echo "<h2>스티커 주문 데이터 진단 (주문번호: {$order_no})</h2>";

// 1. 테이블 스키마 확인 - 표준 필드 컬럼 존재 여부
echo "<h3>1. 테이블 스키마 확인</h3>";
$schema_check = [
    'spec_type', 'spec_material', 'spec_size', 'spec_sides', 'spec_design',
    'quantity_value', 'quantity_unit', 'quantity_sheets', 'quantity_display',
    'price_supply', 'price_vat', 'price_vat_amount', 'data_version'
];

echo "<table border='1' cellpadding='5'><tr><th>컬럼명</th><th>존재여부</th></tr>";
foreach ($schema_check as $col) {
    $result = mysqli_query($db, "SHOW COLUMNS FROM mlangorder_printauto LIKE '{$col}'");
    $exists = mysqli_num_rows($result) > 0 ? '✅ 존재' : '❌ 없음';
    echo "<tr><td>{$col}</td><td>{$exists}</td></tr>";
}
echo "</table>";

// 2. 특정 주문 데이터 확인
echo "<h3>2. 주문 #{$order_no} 데이터</h3>";
$stmt = $db->prepare("SELECT * FROM mlangorder_printauto WHERE no = ?");
$stmt->bind_param('i', $order_no);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if ($order) {
    echo "<table border='1' cellpadding='5'>";

    // 기본 정보
    echo "<tr><th colspan='2'>기본 정보</th></tr>";
    echo "<tr><td>product_type</td><td>" . htmlspecialchars($order['product_type'] ?? 'NULL') . "</td></tr>";
    echo "<tr><td>data_version</td><td>" . htmlspecialchars($order['data_version'] ?? 'NULL') . "</td></tr>";

    // 표준 필드
    echo "<tr><th colspan='2'>표준 필드 (DB 컬럼)</th></tr>";
    foreach (['spec_type', 'spec_material', 'spec_size', 'spec_sides', 'spec_design', 'quantity_display'] as $field) {
        $value = $order[$field] ?? 'NULL';
        $status = !empty($value) && $value !== 'NULL' ? '✅' : '⚠️ 비어있음';
        echo "<tr><td>{$field}</td><td>{$status} " . htmlspecialchars($value) . "</td></tr>";
    }

    // Type_1 JSON 분석
    echo "<tr><th colspan='2'>Type_1 JSON 분석</th></tr>";
    $type1_raw = $order['Type_1'] ?? '';
    $type1_data = json_decode($type1_raw, true);

    if ($type1_data) {
        // 표준 필드
        echo "<tr><td colspan='2'><strong>표준 필드 (JSON)</strong></td></tr>";
        foreach (['spec_type', 'spec_material', 'spec_size', 'spec_design', 'quantity_display'] as $field) {
            $value = $type1_data[$field] ?? 'NULL';
            $status = !empty($value) && $value !== 'NULL' ? '✅' : '⚠️';
            echo "<tr><td>JSON: {$field}</td><td>{$status} " . htmlspecialchars($value) . "</td></tr>";
        }

        // 레거시 필드 (스티커)
        echo "<tr><td colspan='2'><strong>레거시 필드 (JSON)</strong></td></tr>";
        foreach (['jong', 'garo', 'sero', 'domusong', 'mesu', 'ordertype'] as $field) {
            $value = $type1_data[$field] ?? 'NULL';
            $status = !empty($value) && $value !== 'NULL' ? '✅' : '⚠️ 없음';
            echo "<tr><td>JSON: {$field}</td><td>{$status} " . htmlspecialchars($value) . "</td></tr>";
        }
    } else {
        echo "<tr><td colspan='2'>Type_1 JSON 파싱 실패 또는 비어있음</td></tr>";
    }

    // 원본 Type_1
    echo "<tr><th colspan='2'>Type_1 원본 (앞 500자)</th></tr>";
    echo "<tr><td colspan='2'><pre>" . htmlspecialchars(substr($type1_raw, 0, 500)) . "</pre></td></tr>";

    echo "</table>";
} else {
    echo "<p>주문을 찾을 수 없습니다.</p>";
}

// 3. 최근 스티커 주문 5개 요약
echo "<h3>3. 최근 스티커 주문 5개 요약</h3>";
$recent = mysqli_query($db, "SELECT no, data_version, spec_type, spec_material, spec_size, quantity_display
                             FROM mlangorder_printauto
                             WHERE product_type = 'sticker'
                             ORDER BY no DESC LIMIT 5");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>주문번호</th><th>data_version</th><th>spec_type</th><th>spec_material</th><th>spec_size</th><th>quantity_display</th></tr>";
while ($row = mysqli_fetch_assoc($recent)) {
    echo "<tr>";
    foreach (['no', 'data_version', 'spec_type', 'spec_material', 'spec_size', 'quantity_display'] as $col) {
        $val = $row[$col] ?? '-';
        echo "<td>" . htmlspecialchars($val) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<hr><p><a href='admin.php?mode=OrderView&no={$order_no}'>주문 상세보기로 돌아가기</a></p>";
?>
