<?php
/**
 * 주문번호 83224 디버그 도구
 * 주문 데이터 상태를 확인하고 문제점을 파악합니다.
 */

include "../../db.php";

echo "<h2>🔍 주문번호 83224 디버그 분석</h2>";

// 데이터베이스 연결 확인
if (!$db) {
    die("❌ 데이터베이스 연결 실패");
}

$order_no = 83224;

// 1. 주문 데이터 조회
echo "<h3>📋 1. 주문 기본 정보</h3>";
$stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
$stmt->bind_param("i", $order_no);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th style='background: #f0f0f0; padding: 8px;'>필드명</th><th style='background: #f0f0f0; padding: 8px;'>값</th><th style='background: #f0f0f0; padding: 8px;'>상태</th></tr>";
    
    foreach ($row as $field => $value) {
        $status = "";
        if ($field == 'Type_1') {
            if (empty($value)) {
                $status = "❌ 비어있음";
            } elseif (trim($value) == '') {
                $status = "⚠️ 공백만 있음";
            } elseif (strlen($value) < 10) {
                $status = "⚠️ 내용이 너무 짧음";
            } else {
                $status = "✅ 데이터 있음";
            }
        } elseif ($field == 'Type') {
            $status = empty($value) ? "❌ 비어있음" : "✅ " . $value;
        } elseif ($field == 'ThingCate') {
            $status = empty($value) ? "❌ 파일 정보 없음" : "✅ " . $value;
        }
        
        $display_value = $value;
        if (strlen($value) > 100) {
            $display_value = substr($value, 0, 100) . "... (총 " . strlen($value) . "자)";
        }
        
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . htmlspecialchars($field) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($display_value) . "</td>";
        echo "<td style='padding: 8px;'>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Type_1 필드 상세 분석
    echo "<h3>🔍 2. Type_1 필드 상세 분석</h3>";
    $type_1_content = $row['Type_1'];
    
    echo "<div style='background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin: 10px 0;'>";
    echo "<strong>원본 내용:</strong><br>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($type_1_content);
    echo "</pre>";
    echo "</div>";
    
    // JSON 형태인지 확인
    $json_data = json_decode($type_1_content, true);
    if ($json_data) {
        echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4CAF50; margin: 10px 0;'>";
        echo "<strong>✅ JSON 형태로 파싱 성공:</strong><br>";
        echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc;'>";
        echo json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
        echo "<strong>⚠️ JSON 파싱 실패 - 일반 텍스트로 처리</strong><br>";
        echo "JSON 오류: " . json_last_error_msg();
        echo "</div>";
    }
    
    // 3. 파일 정보 확인
    echo "<h3>📁 3. 업로드 파일 정보</h3>";
    $upload_dir = "../../MlangOrder_PrintAuto/upload/$order_no";
    
    if (is_dir($upload_dir)) {
        $files = array_diff(scandir($upload_dir), ['.', '..']);
        if (!empty($files)) {
            echo "<ul>";
            foreach ($files as $file) {
                $file_path = "$upload_dir/$file";
                $file_size = filesize($file_path);
                $file_size_mb = round($file_size / 1024 / 1024, 2);
                echo "<li>📄 $file ({$file_size_mb}MB)</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>📂 폴더는 존재하지만 파일이 없습니다.</p>";
        }
    } else {
        echo "<p>❌ 업로드 폴더가 존재하지 않습니다: $upload_dir</p>";
    }
    
    // 4. 주문 처리 과정 추적
    echo "<h3>🔄 4. 주문 처리 과정 분석</h3>";
    
    // 주문 날짜와 상태 확인
    echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #007bff; margin: 10px 0;'>";
    echo "<strong>주문 정보:</strong><br>";
    echo "• 주문 날짜: " . $row['date'] . "<br>";
    echo "• 주문 상태: " . $row['OrderStyle'] . " (";
    switch($row['OrderStyle']) {
        case '1': echo '주문접수'; break;
        case '2': echo '신규주문'; break;
        case '3': echo '확인완료'; break;
        case '6': echo '시안'; break;
        case '7': echo '교정'; break;
        default: echo '상태미정';
    }
    echo ")<br>";
    echo "• 상품 유형: " . $row['Type'] . "<br>";
    echo "• 주문자: " . $row['name'] . "<br>";
    echo "</div>";
    
    // 5. 문제 진단 및 해결 방안
    echo "<h3>🔧 5. 문제 진단 및 해결 방안</h3>";
    
    $problems = [];
    $solutions = [];
    
    if (empty($type_1_content) || trim($type_1_content) == '') {
        $problems[] = "Type_1 필드가 비어있음";
        $solutions[] = "주문 데이터를 수동으로 재구성하거나 주문자에게 재주문 요청";
    }
    
    if (strpos($type_1_content, '\n\n\n\n\n') !== false) {
        $problems[] = "Type_1 필드에 의미없는 개행 문자만 있음";
        $solutions[] = "주문 처리 시스템의 데이터 저장 로직 점검 필요";
    }
    
    if (!empty($problems)) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; margin: 10px 0;'>";
        echo "<strong>❌ 발견된 문제점:</strong><br>";
        foreach ($problems as $problem) {
            echo "• " . $problem . "<br>";
        }
        echo "</div>";
        
        echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #17a2b8; margin: 10px 0;'>";
        echo "<strong>💡 권장 해결 방안:</strong><br>";
        foreach ($solutions as $solution) {
            echo "• " . $solution . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; margin: 10px 0;'>";
        echo "<strong>✅ 특별한 문제가 발견되지 않았습니다.</strong><br>";
        echo "OrderFormOrderTree.php의 표시 로직을 점검해보세요.";
        echo "</div>";
    }
    
} else {
    echo "<p>❌ 주문번호 $order_no 를 찾을 수 없습니다.</p>";
}

$stmt->close();

// 6. 즉시 수정 도구
echo "<h3>🛠️ 6. 즉시 수정 도구</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
echo "<strong>⚠️ 주의: 이 도구는 테스트 목적으로만 사용하세요</strong><br>";
echo "<form method='post' style='margin-top: 10px;'>";
echo "<input type='hidden' name='order_no' value='$order_no'>";
echo "<label>새로운 Type_1 내용:</label><br>";
echo "<textarea name='new_type_1' rows='5' cols='80' placeholder='주문 상세 정보를 입력하세요...'></textarea><br>";
echo "<input type='submit' name='update_type_1' value='Type_1 필드 업데이트' style='margin-top: 10px; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer;'>";
echo "</form>";
echo "</div>";

// 수정 처리
if (isset($_POST['update_type_1']) && isset($_POST['new_type_1'])) {
    $new_content = $_POST['new_type_1'];
    $update_stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET Type_1 = ? WHERE no = ?");
    $update_stmt->bind_param("si", $new_content, $order_no);
    
    if ($update_stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; margin: 10px 0;'>";
        echo "<strong>✅ Type_1 필드가 성공적으로 업데이트되었습니다!</strong><br>";
        echo "<a href='admin.php?mode=OrderView&no=$order_no' target='_blank'>관리자 페이지에서 확인하기</a>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; margin: 10px 0;'>";
        echo "<strong>❌ 업데이트 실패: " . $update_stmt->error . "</strong>";
        echo "</div>";
    }
    $update_stmt->close();
}

$db->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { text-align: left; }
pre { font-size: 12px; }
</style>