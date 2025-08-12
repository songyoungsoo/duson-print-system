<?php
/**
 * 주문번호 83226 스티커 주문 데이터 추적 디버그
 * 스티커 주문 과정에서 데이터가 어디서 손실되는지 확인
 */

include "../../db.php";

echo "<h2>🔍 주문번호 83226 스티커 데이터 추적 분석</h2>";

if (!$db) {
    die("❌ 데이터베이스 연결 실패");
}

$order_no = 83226;

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
    echo "• 이메일: " . $row['email'] . "<br>";
    echo "• 주문일시: " . $row['date'] . "<br>";
    echo "• 주문상태: " . $row['OrderStyle'] . "<br>";
    echo "• 첨부파일: " . $row['ThingCate'] . "<br>";
    echo "</div>";
    
    // 2. Type_1 필드 JSON 분석
    echo "<h3>🔍 2. JSON 데이터 분석</h3>";
    $type_1_content = $row['Type_1'];
    
    $json_data = json_decode($type_1_content, true);
    if ($json_data) {
        echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
        echo "<strong>⚠️ JSON 파싱 성공하지만 데이터가 비어있음:</strong><br>";
        echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc;'>";
        echo json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</pre>";
        
        if (isset($json_data['order_details'])) {
            $details = $json_data['order_details'];
            echo "<strong>🔍 비어있는 필드들:</strong><br>";
            foreach ($details as $key => $value) {
                $status = empty($value) || $value == 0 ? "❌ 비어있음" : "✅ 데이터 있음";
                echo "• $key: '$value' - $status<br>";
            }
        }
        echo "</div>";
    }
    
    // 3. 장바구니 데이터 추적
    echo "<h3>🛒 3. 장바구니 데이터 추적</h3>";
    
    // shop_temp 테이블에서 관련 데이터 찾기
    $cart_query = "SELECT * FROM shop_temp WHERE session_id LIKE '%83226%' OR order_id = '83226' ORDER BY regdate DESC LIMIT 10";
    $cart_result = mysqli_query($db, $cart_query);
    
    if ($cart_result && mysqli_num_rows($cart_result) > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; margin: 10px 0;'>";
        echo "<strong>✅ 장바구니 데이터 발견:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
        echo "<tr style='background: #f8f9fa;'><th>세션ID</th><th>상품타입</th><th>재질</th><th>가로</th><th>세로</th><th>수량</th><th>모양</th><th>편집비</th><th>등록일</th></tr>";
        
        while ($cart_row = mysqli_fetch_assoc($cart_result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($cart_row['session_id'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cart_row['product_type'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cart_row['jong'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cart_row['garo'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cart_row['sero'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cart_row['mesu'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cart_row['domusong'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cart_row['uhyung'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cart_row['regdate'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; margin: 10px 0;'>";
        echo "<strong>❌ 장바구니 데이터를 찾을 수 없습니다.</strong><br>";
        echo "이는 다음 중 하나의 원인일 수 있습니다:<br>";
        echo "• 장바구니를 거치지 않고 직접 주문한 경우<br>";
        echo "• 주문 완료 후 장바구니 데이터가 삭제된 경우<br>";
        echo "• 세션 ID가 일치하지 않는 경우<br>";
        echo "</div>";
    }
    
    // 4. 스티커 주문 경로 분석
    echo "<h3>🔄 4. 스티커 주문 경로 분석</h3>";
    
    echo "<div style='background: #e9ecef; padding: 15px; border: 1px solid #6c757d; margin: 10px 0;'>";
    echo "<strong>🔍 예상 주문 경로:</strong><br>";
    echo "1. MlangPrintAuto/shop/view_modern.php (스티커 주문 페이지)<br>";
    echo "2. MlangPrintAuto/shop/add_to_basket.php (장바구니 추가)<br>";
    echo "3. MlangOrder_PrintAuto/OnlineOrder_unified.php (주문 정보 입력)<br>";
    echo "4. MlangOrder_PrintAuto/ProcessOrder_unified.php (주문 처리)<br>";
    echo "<br>";
    echo "<strong>🚨 문제 발생 지점 추정:</strong><br>";
    echo "• 스티커 데이터가 장바구니에 저장되지 않음<br>";
    echo "• 또는 장바구니에서 주문 처리로 데이터 전달 실패<br>";
    echo "</div>";
    
    // 5. 스티커 주문 페이지 확인
    echo "<h3>🏷️ 5. 스티커 주문 페이지 데이터 흐름 확인</h3>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
    echo "<strong>⚠️ 확인 필요 사항:</strong><br>";
    echo "1. <strong>view_modern.php</strong>에서 폼 데이터가 올바르게 전송되는가?<br>";
    echo "2. <strong>add_to_basket.php</strong>에서 스티커 데이터를 올바르게 처리하는가?<br>";
    echo "3. <strong>OnlineOrder_unified.php</strong>에서 장바구니 데이터를 올바르게 읽어오는가?<br>";
    echo "4. <strong>ProcessOrder_unified.php</strong>에서 스티커 필드를 올바르게 참조하는가?<br>";
    echo "</div>";
    
    // 6. 문제 해결 방안
    echo "<h3>🛠️ 6. 문제 해결 방안</h3>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #17a2b8; margin: 10px 0;'>";
    echo "<strong>💡 즉시 해결 방안:</strong><br>";
    echo "1. <strong>스티커 주문 페이지 점검</strong>: view_modern.php의 폼 필드명 확인<br>";
    echo "2. <strong>장바구니 추가 로직 점검</strong>: add_to_basket.php의 스티커 데이터 처리<br>";
    echo "3. <strong>주문 처리 로직 점검</strong>: ProcessOrder_unified.php의 데이터 매핑<br>";
    echo "4. <strong>테스트 주문</strong>: 새로운 스티커 주문으로 데이터 흐름 확인<br>";
    echo "</div>";
    
    // 7. 수동 데이터 입력 도구
    echo "<h3>🔧 7. 수동 데이터 입력 도구</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
    echo "<strong>⚠️ 주문번호 83226 스티커 정보 수동 입력</strong><br>";
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