<?php
/**
 * 테스트 주문 생성 - 파일 업로드 포함
 */

session_start();
include "db.php";

echo "<h2>📦 테스트 주문 생성</h2>";

// 1. 테스트 파일 생성
$test_file_content = "테스트 파일 내용 - " . date('Y-m-d H:i:s');
$test_filename = "test_file_" . time() . ".txt";

// 임시 업로드 폴더 생성
$session_id = session_id();
$temp_folder_name = 'temp_' . $session_id . '_' . time();
$upload_base = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/';
$temp_folder = $upload_base . $temp_folder_name . '/';

if (!is_dir($temp_folder)) {
    mkdir($temp_folder, 0755, true);
    echo "✅ 임시 폴더 생성: $temp_folder<br>";
}

// 테스트 파일 생성
$test_file_path = $temp_folder . $test_filename;
file_put_contents($test_file_path, $test_file_content);
echo "✅ 테스트 파일 생성: $test_filename<br>";

// 2. 주문번호 생성
$result = $db->query("SELECT MAX(no) as max_no FROM mlangorder_printauto");
$row = $result->fetch_assoc();
$order_no = ($row['max_no'] ?? 0) + 1;

echo "<br><h3>📋 주문 정보</h3>";
echo "주문번호: <strong>$order_no</strong><br>";

// 3. 임시 폴더를 주문번호 폴더로 변경
$final_folder = $upload_base . $order_no . '/';
if (rename($temp_folder, $final_folder)) {
    echo "✅ 폴더 이동 성공: $temp_folder_name → $order_no<br>";
} else {
    echo "❌ 폴더 이동 실패<br>";
    exit;
}

// 4. DB에 주문 저장
$img_folder = 'mlangorder_printauto/upload/' . $order_no . '/';
$thing_cate = $test_filename;

$insert_query = "INSERT INTO mlangorder_printauto (
    no, Type, ImgFolder, ThingCate, Type_1,
    name, email, phone, zip1, zip2,
    money_4, money_5, date, OrderStyle
) VALUES (
    ?, '전단지', ?, ?, '테스트 주문 - A4 단면 500매',
    '테스트고객', 'test@test.com', '02-1234-5678', '서울시 강남구', '테스트동 123',
    50000, 55000, NOW(), '2'
)";

$stmt = $db->prepare($insert_query);
$stmt->bind_param("iss", $order_no, $img_folder, $thing_cate);

if ($stmt->execute()) {
    echo "✅ 주문 저장 성공<br>";
} else {
    echo "❌ 주문 저장 실패: " . $stmt->error . "<br>";
    exit;
}

$stmt->close();

// 5. 결과 확인
echo "<br><h3>✅ 테스트 주문 생성 완료!</h3>";
echo "<p><strong>주문번호:</strong> $order_no</p>";
echo "<p><strong>파일 경로:</strong> $img_folder</p>";
echo "<p><strong>파일명:</strong> $thing_cate</p>";

// 6. 실제 파일 확인
echo "<br><h3>📁 파일 확인</h3>";
if (is_dir($final_folder)) {
    echo "✅ 폴더 존재: $final_folder<br>";
    $files = scandir($final_folder);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $file_size = filesize($final_folder . $file);
            echo "<li>$file (" . $file_size . " bytes)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "❌ 폴더가 존재하지 않습니다<br>";
}

// 7. DB 확인
echo "<br><h3>💾 DB 확인</h3>";
$check_query = "SELECT no, Type, ImgFolder, ThingCate, name FROM mlangorder_printauto WHERE no = ?";
$check_stmt = $db->prepare($check_query);
$check_stmt->bind_param("i", $order_no);
$check_stmt->execute();
$result = $check_stmt->get_result();
$order = $result->fetch_assoc();

if ($order) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>필드</th><th>값</th></tr>";
    foreach ($order as $key => $value) {
        echo "<tr><td>$key</td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
}

$check_stmt->close();
$db->close();

// 8. 관리자 페이지 링크
echo "<br><h3>🔗 관리자 페이지에서 확인</h3>";
echo "<p><a href='http://dsp1830.shop/admin/mlangprintauto/admin.php?mode=OrderView&no=$order_no' target='_blank' style='font-size: 18px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>주문 $order_no 보기</a></p>";

echo "<br><p style='color: #666;'>위 링크를 클릭하여 관리자 페이지에서 파일이 제대로 표시되는지 확인하세요.</p>";
?>
