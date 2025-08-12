<?php
/**
 * 주문번호 83225 전용 디버그 도구
 * 스티커 주문 데이터 분석 및 파일 중복 저장 문제 확인
 */

include "../../db.php";

echo "<h2>🔍 주문번호 83225 스티커 주문 디버그 분석</h2>";

if (!$db) {
    die("❌ 데이터베이스 연결 실패");
}

$order_no = 83225;

// 1. 주문 데이터 조회
echo "<h3>📋 1. 주문 기본 정보</h3>";
$stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
$stmt->bind_param("i", $order_no);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; margin: 10px 0;'>";
    echo "<strong>기본 정보:</strong><br>";
    echo "• 주문번호: " . $row['no'] . "<br>";
    echo "• 상품유형: " . $row['Type'] . "<br>";
    echo "• 주문자: " . $row['name'] . "<br>";
    echo "• 주문일시: " . $row['date'] . "<br>";
    echo "• 주문상태: " . $row['OrderStyle'] . "<br>";
    echo "• 첨부파일: " . $row['ThingCate'] . "<br>";
    echo "</div>";
    
    // 2. Type_1 필드 상세 분석 (스티커 데이터)
    echo "<h3>🏷️ 2. 스티커 주문 데이터 분석</h3>";
    $type_1_content = $row['Type_1'];
    
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
    echo "<strong>Type_1 원본 내용:</strong><br>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; font-size: 12px;'>";
    echo htmlspecialchars($type_1_content);
    echo "</pre>";
    echo "<strong>문자 길이:</strong> " . strlen($type_1_content) . "자<br>";
    echo "</div>";
    
    // JSON 파싱 시도
    $json_data = json_decode($type_1_content, true);
    if ($json_data) {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; margin: 10px 0;'>";
        echo "<strong>✅ JSON 파싱 성공:</strong><br>";
        echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc;'>";
        echo json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</pre>";
        
        // 스티커 데이터 추출
        if (isset($json_data['order_details'])) {
            $details = $json_data['order_details'];
            echo "<strong>🔍 스티커 상세 정보:</strong><br>";
            echo "• 재질: " . ($details['jong'] ?? '정보없음') . "<br>";
            echo "• 가로: " . ($details['garo'] ?? '0') . "mm<br>";
            echo "• 세로: " . ($details['sero'] ?? '0') . "mm<br>";
            echo "• 수량: " . ($details['mesu'] ?? '0') . "매<br>";
            echo "• 모양: " . ($details['domusong'] ?? '정보없음') . "<br>";
            echo "• 편집비: " . number_format($details['uhyung'] ?? 0) . "원<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; margin: 10px 0;'>";
        echo "<strong>❌ JSON 파싱 실패</strong><br>";
        echo "JSON 오류: " . json_last_error_msg() . "<br>";
        
        // 스티커 데이터 수동 파싱 시도
        if (strpos($type_1_content, '재질:') !== false) {
            echo "<strong>🔍 텍스트에서 스티커 정보 추출 시도:</strong><br>";
            $lines = explode("\n", $type_1_content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && $line !== '===') {
                    echo "• " . htmlspecialchars($line) . "<br>";
                }
            }
        } else {
            echo "<strong>⚠️ 스티커 정보를 찾을 수 없습니다.</strong><br>";
        }
        echo "</div>";
    }
    
    // 3. 파일 저장 위치 분석
    echo "<h3>📁 3. 파일 저장 위치 분석</h3>";
    
    $file_locations = [
        "주문 폴더" => "../../MlangOrder_PrintAuto/upload/$order_no",
        "임시 폴더" => "../../MlangOrder_PrintAuto/upload/temp/" . session_id(),
        "기존 업로드 폴더" => "../../uploads/$order_no",
        "스티커 전용 폴더" => "../../MlangPrintAuto/shop/uploads/$order_no"
    ];
    
    $total_files = 0;
    $duplicate_files = [];
    
    foreach ($file_locations as $location_name => $path) {
        echo "<div style='background: #e9ecef; padding: 10px; margin: 5px 0; border-left: 4px solid #6c757d;'>";
        echo "<strong>📂 $location_name:</strong> <code>$path</code><br>";
        
        if (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            if (!empty($files)) {
                echo "<span style='color: #28a745;'>✅ 폴더 존재, 파일 " . count($files) . "개</span><br>";
                foreach ($files as $file) {
                    $file_path = "$path/$file";
                    $file_size = filesize($file_path);
                    $file_size_mb = round($file_size / 1024 / 1024, 2);
                    $file_time = date('Y-m-d H:i:s', filemtime($file_path));
                    echo "&nbsp;&nbsp;• 📄 $file ({$file_size_mb}MB, $file_time)<br>";
                    
                    // 중복 파일 체크
                    if (isset($duplicate_files[$file])) {
                        $duplicate_files[$file][] = $path;
                    } else {
                        $duplicate_files[$file] = [$path];
                    }
                    $total_files++;
                }
            } else {
                echo "<span style='color: #ffc107;'>⚠️ 폴더 존재하지만 파일 없음</span><br>";
            }
        } else {
            echo "<span style='color: #6c757d;'>❌ 폴더 없음</span><br>";
        }
        echo "</div>";
    }
    
    // 중복 파일 분석
    echo "<h4>🔍 중복 파일 분석</h4>";
    $has_duplicates = false;
    foreach ($duplicate_files as $filename => $locations) {
        if (count($locations) > 1) {
            $has_duplicates = true;
            echo "<div style='background: #f8d7da; padding: 10px; margin: 5px 0; border-left: 4px solid #dc3545;'>";
            echo "<strong>⚠️ 중복 파일 발견: $filename</strong><br>";
            foreach ($locations as $location) {
                echo "&nbsp;&nbsp;• $location<br>";
            }
            echo "</div>";
        }
    }
    
    if (!$has_duplicates && $total_files > 0) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 5px 0; border-left: 4px solid #28a745;'>";
        echo "<strong>✅ 중복 파일 없음 (총 $total_files 개 파일)</strong>";
        echo "</div>";
    } elseif ($total_files == 0) {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 5px 0; border-left: 4px solid #dc3545;'>";
        echo "<strong>❌ 업로드된 파일이 전혀 없습니다!</strong>";
        echo "</div>";
    }
    
    // 4. 데이터베이스 파일 정보 확인
    echo "<h3>💾 4. 데이터베이스 파일 정보</h3>";
    
    // uploaded_files 테이블 확인
    $file_query = "SELECT * FROM uploaded_files WHERE session_id LIKE '%$order_no%' OR file_name LIKE '%$order_no%' ORDER BY upload_date DESC";
    $file_result = mysqli_query($db, $file_query);
    
    if ($file_result && mysqli_num_rows($file_result) > 0) {
        echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #17a2b8; margin: 10px 0;'>";
        echo "<strong>📋 uploaded_files 테이블 기록:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
        echo "<tr style='background: #f8f9fa;'><th>파일명</th><th>세션ID</th><th>상품타입</th><th>업로드시간</th></tr>";
        
        while ($file_row = mysqli_fetch_assoc($file_result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($file_row['file_name']) . "</td>";
            echo "<td>" . htmlspecialchars($file_row['session_id']) . "</td>";
            echo "<td>" . htmlspecialchars($file_row['product_type']) . "</td>";
            echo "<td>" . htmlspecialchars($file_row['upload_date']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
        echo "<strong>⚠️ uploaded_files 테이블에 관련 기록이 없습니다.</strong>";
        echo "</div>";
    }
    
    // 5. 주문 처리 과정 추적
    echo "<h3>🔄 5. 주문 처리 과정 추적</h3>";
    
    // ProcessOrder_unified.php에서 어떻게 처리되었는지 추적
    echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #007bff; margin: 10px 0;'>";
    echo "<strong>🔍 주문 처리 분석:</strong><br>";
    
    // 스티커 주문인지 확인
    if ($row['Type'] == '스티커' || strpos($type_1_content, '재질:') !== false) {
        echo "• 상품 유형: 스티커 주문으로 확인됨<br>";
        echo "• 예상 처리 경로: MlangPrintAuto/shop/view_modern.php → ProcessOrder_unified.php<br>";
        
        // 스티커 데이터 구조 확인
        if (empty($type_1_content) || trim($type_1_content) == '' || $type_1_content == '\n\n\n\n\n') {
            echo "• <span style='color: #dc3545;'>❌ 문제: Type_1 필드가 비어있거나 의미없는 데이터</span><br>";
            echo "• <span style='color: #17a2b8;'>💡 원인: ProcessOrder_unified.php에서 스티커 데이터 저장 로직 오류</span><br>";
        }
    } else {
        echo "• 상품 유형: " . $row['Type'] . "<br>";
    }
    echo "</div>";
    
    // 6. 문제 해결 방안
    echo "<h3>🛠️ 6. 문제 해결 방안</h3>";
    
    $problems = [];
    $solutions = [];
    
    // Type_1 필드 문제 체크
    if (empty($type_1_content) || trim($type_1_content) == '' || $type_1_content == '\n\n\n\n\n') {
        $problems[] = "주문 상세 정보(Type_1)가 저장되지 않음";
        $solutions[] = "ProcessOrder_unified.php의 스티커 데이터 저장 로직 수정 필요";
    }
    
    // 파일 중복 문제 체크
    if ($has_duplicates) {
        $problems[] = "파일이 여러 위치에 중복 저장됨";
        $solutions[] = "파일 이동 로직을 단일 경로로 통일";
    }
    
    // 파일 없음 문제 체크
    if ($total_files == 0) {
        $problems[] = "업로드된 파일이 전혀 없음";
        $solutions[] = "파일 업로드 및 이동 과정 전체 점검 필요";
    }
    
    if (!empty($problems)) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; margin: 10px 0;'>";
        echo "<strong>❌ 발견된 문제점:</strong><br>";
        foreach ($problems as $i => $problem) {
            echo ($i + 1) . ". " . $problem . "<br>";
        }
        echo "</div>";
        
        echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #17a2b8; margin: 10px 0;'>";
        echo "<strong>💡 권장 해결 방안:</strong><br>";
        foreach ($solutions as $i => $solution) {
            echo ($i + 1) . ". " . $solution . "<br>";
        }
        echo "</div>";
    }
    
    // 7. 즉시 수정 도구
    echo "<h3>🔧 7. 즉시 수정 도구</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
    echo "<strong>⚠️ 스티커 주문 정보 수동 입력</strong><br>";
    echo "<form method='post' style='margin-top: 10px;'>";
    echo "<input type='hidden' name='order_no' value='$order_no'>";
    
    echo "<table style='width: 100%;'>";
    echo "<tr><td>재질:</td><td><input type='text' name='jong' placeholder='예: 아트지유광' style='width: 200px;'></td></tr>";
    echo "<tr><td>가로(mm):</td><td><input type='number' name='garo' placeholder='100' style='width: 200px;'></td></tr>";
    echo "<tr><td>세로(mm):</td><td><input type='number' name='sero' placeholder='100' style='width: 200px;'></td></tr>";
    echo "<tr><td>수량(매):</td><td><input type='number' name='mesu' placeholder='1000' style='width: 200px;'></td></tr>";
    echo "<tr><td>모양:</td><td><input type='text' name='domusong' placeholder='사각' style='width: 200px;'></td></tr>";
    echo "<tr><td>편집비(원):</td><td><input type='number' name='uhyung' placeholder='10000' style='width: 200px;'></td></tr>";
    echo "</table>";
    
    echo "<input type='submit' name='fix_sticker_data' value='스티커 정보 수정' style='margin-top: 10px; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer;'>";
    echo "</form>";
    echo "</div>";
    
} else {
    echo "<p>❌ 주문번호 $order_no 를 찾을 수 없습니다.</p>";
}

// 스티커 데이터 수정 처리
if (isset($_POST['fix_sticker_data'])) {
    $jong = $_POST['jong'] ?? '';
    $garo = $_POST['garo'] ?? 0;
    $sero = $_POST['sero'] ?? 0;
    $mesu = $_POST['mesu'] ?? 0;
    $domusong = $_POST['domusong'] ?? '';
    $uhyung = $_POST['uhyung'] ?? 0;
    
    // 스티커 정보를 JSON 형태로 구성
    $sticker_data = [
        'product_type' => 'sticker',
        'order_details' => [
            'jong' => $jong,
            'garo' => $garo,
            'sero' => $sero,
            'mesu' => $mesu,
            'domusong' => $domusong,
            'uhyung' => $uhyung
        ],
        'formatted_display' => "재질: $jong\n크기: {$garo}mm × {$sero}mm\n수량: " . number_format($mesu) . "매\n모양: $domusong\n편집비: " . number_format($uhyung) . "원",
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $json_content = json_encode($sticker_data, JSON_UNESCAPED_UNICODE);
    
    $update_stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET Type_1 = ?, Type = '스티커' WHERE no = ?");
    $update_stmt->bind_param("si", $json_content, $order_no);
    
    if ($update_stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; margin: 10px 0;'>";
        echo "<strong>✅ 스티커 정보가 성공적으로 업데이트되었습니다!</strong><br>";
        echo "<a href='admin.php?mode=OrderView&no=$order_no' target='_blank'>관리자 페이지에서 확인하기</a>";
        echo "</div>";
        
        // 페이지 새로고침
        echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; margin: 10px 0;'>";
        echo "<strong>❌ 업데이트 실패: " . $update_stmt->error . "</strong>";
        echo "</div>";
    }
    $update_stmt->close();
}

$stmt->close();
$db->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { text-align: left; padding: 8px; }
pre { font-size: 12px; }
input[type="text"], input[type="number"] { padding: 5px; }
</style>