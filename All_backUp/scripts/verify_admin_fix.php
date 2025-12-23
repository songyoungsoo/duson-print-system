<?php
/**
 * 관리자 페이지 session_id 문제 해결 검증 스크립트
 * 경로: /var/www/html/scripts/verify_admin_fix.php
 *
 * 실행: http://localhost/scripts/verify_admin_fix.php?no=90057
 */

include __DIR__ . "/../db.php";
include __DIR__ . "/../includes/AdditionalOptionsDisplay.php";

$no = $_GET['no'] ?? 90057;

echo "<h2>🔍 관리자 페이지 수정 검증 (주문번호: $no)</h2>";

// 1. 주문 정보 조회
echo "<h3>1️⃣ mlangorder_printauto 테이블에서 주문 조회</h3>";
$stmt = $db->prepare("SELECT * FROM mlangorder_printauto WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "<p style='color: red;'>❌ 주문을 찾을 수 없습니다.</p>";
    exit;
}

echo "<p style='color: green;'>✅ 주문 정보 조회 성공</p>";
echo "<pre>";
echo "주문번호: " . $row['no'] . "\n";
echo "상품명: " . $row['Type'] . "\n";
echo "ThingCate: " . $row['ThingCate'] . "\n";
echo "ImgFolder: " . ($row['ImgFolder'] ?? '없음') . "\n";
echo "</pre>";

// 2. 추가 옵션 정보 확인
echo "<h3>2️⃣ 추가 옵션 정보 확인</h3>";

echo "<h4>전단지 추가옵션:</h4>";
echo "<pre>";
echo "coating_enabled: " . ($row['coating_enabled'] ?? 0) . "\n";
echo "coating_type: " . ($row['coating_type'] ?? 'N/A') . "\n";
echo "coating_price: " . ($row['coating_price'] ?? 0) . "\n";
echo "folding_enabled: " . ($row['folding_enabled'] ?? 0) . "\n";
echo "folding_type: " . ($row['folding_type'] ?? 'N/A') . "\n";
echo "folding_price: " . ($row['folding_price'] ?? 0) . "\n";
echo "creasing_enabled: " . ($row['creasing_enabled'] ?? 0) . "\n";
echo "creasing_lines: " . ($row['creasing_lines'] ?? 0) . "\n";
echo "creasing_price: " . ($row['creasing_price'] ?? 0) . "\n";
echo "additional_options_total: " . ($row['additional_options_total'] ?? 0) . "\n";
echo "</pre>";

echo "<h4>명함 프리미엄 옵션:</h4>";
echo "<pre>";
echo "premium_options: " . ($row['premium_options'] ?? 'N/A') . "\n";
echo "premium_options_total: " . ($row['premium_options_total'] ?? 0) . "\n";
echo "</pre>";

// 3. AdditionalOptionsDisplay 클래스 테스트
echo "<h3>3️⃣ AdditionalOptionsDisplay 클래스 테스트</h3>";
$optionsDisplay = new AdditionalOptionsDisplay($db);
$optionDetails = $optionsDisplay->getOrderDetails($row);

echo "<pre>";
echo "has_options: " . ($optionDetails['has_options'] ? 'true' : 'false') . "\n";
echo "total_price: " . $optionDetails['total_price'] . "\n";
echo "옵션 개수: " . count($optionDetails['options']) . "\n";
echo "</pre>";

if ($optionDetails['has_options']) {
    echo "<h4>옵션 상세:</h4>";
    foreach ($optionDetails['options'] as $option) {
        echo "<div style='background: #f1f8e9; padding: 8px; margin: 4px; border-radius: 4px;'>";
        echo "📋 <strong>" . htmlspecialchars($option['category']) . "</strong> ";
        echo "(" . htmlspecialchars($option['name']) . ") ";
        echo "<strong>" . htmlspecialchars($option['formatted_price']) . "</strong>";
        echo "</div>";
    }

    echo "<div style='margin-top: 10px; padding: 8px; background: #e8f5e9; border-left: 4px solid #4caf50;'>";
    echo "💰 총액: <strong>" . number_format($optionDetails['total_price']) . "원</strong>";
    echo "</div>";
} else {
    echo "<p style='color: #6c757d;'>선택된 추가옵션이 없습니다.</p>";
}

// 4. 파일 업로드 디렉토리 확인
echo "<h3>4️⃣ 파일 업로드 디렉토리 확인</h3>";

$upload_dirs = [
    "기본 업로드" => "../mlangorder_printauto/upload/$no",
    "통합 주문" => "../uploads/orders/$no",
];

if (!empty($row['ImgFolder'])) {
    $upload_dirs['ImgFolder'] = $row['ImgFolder'];
}

foreach ($upload_dirs as $label => $dir) {
    echo "<h4>$label: $dir</h4>";
    if (is_dir($dir)) {
        $files = scandir($dir);
        $file_count = 0;
        echo "<ul>";
        foreach ($files as $file) {
            if ($file != "." && $file != ".." && is_file("$dir/$file")) {
                $file_count++;
                $file_size = filesize("$dir/$file");
                $file_size_mb = round($file_size / 1024 / 1024, 2);
                echo "<li>📄 $file ({$file_size_mb}MB)</li>";
            }
        }
        echo "</ul>";
        echo "<p style='color: green;'>✅ 총 $file_count 개 파일</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ 디렉토리가 존재하지 않습니다.</p>";
    }
}

// 5. 결론
echo "<h3>5️⃣ 검증 결과</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
echo "<h4 style='color: #155724;'>✅ 수정 사항 요약</h4>";
echo "<ul style='color: #155724;'>";
echo "<li><strong>문제:</strong> admin.php가 shop_order 테이블에서 존재하지 않는 session_id 컬럼을 조회</li>";
echo "<li><strong>원인:</strong> 주문 완료 후 데이터는 mlangorder_printauto에 저장되므로 session_id 불필요</li>";
echo "<li><strong>해결:</strong> mlangorder_printauto에서 이미 조회한 \$row에서 직접 추가옵션 필드 읽기</li>";
echo "<li><strong>적용:</strong> 추가옵션 표시 (라인 757-786) 및 파일 업로드 (라인 642-646)</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='/admin/MlangPrintAuto/admin.php?mode=OrderView&no=$no' target='_blank'>➡️ 실제 관리자 페이지에서 확인하기</a></p>";
?>
