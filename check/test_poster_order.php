<?php
/**
 * 포스터(littleprint) 테스트 주문 생성
 */

session_start();
include "db.php";

echo "<h2>🖼️ 포스터 테스트 주문 생성</h2>";

// 1. 테스트 이미지 파일 생성 (실제 이미지처럼)
$test_filename = "poster_test_" . time() . ".jpg";
$test_file_content = "JPEG 테스트 파일 - 포스터 주문 - " . date('Y-m-d H:i:s');

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

// 추가 파일도 생성 (여러 파일 테스트)
$test_filename2 = "poster_design_" . time() . ".pdf";
file_put_contents($temp_folder . $test_filename2, "PDF 디자인 파일 테스트");
echo "✅ 추가 파일 생성: $test_filename2<br>";

// 2. 주문번호 생성
$result = $db->query("SELECT MAX(no) as max_no FROM mlangorder_printauto");
$row = $result->fetch_assoc();
$order_no = ($row['max_no'] ?? 0) + 1;

echo "<br><h3>📋 포스터 주문 정보</h3>";
echo "주문번호: <strong>$order_no</strong><br>";
echo "품목: <strong>포스터 (littleprint)</strong><br>";

// 3. 임시 폴더를 주문번호 폴더로 변경
$final_folder = $upload_base . $order_no . '/';
if (rename($temp_folder, $final_folder)) {
    echo "✅ 폴더 이동 성공: $temp_folder_name → $order_no<br>";
} else {
    echo "❌ 폴더 이동 실패<br>";
    exit;
}

// 4. DB에 포스터 주문 저장
$img_folder = 'mlangorder_printauto/upload/' . $order_no . '/';
$thing_cate = $test_filename;

// 포스터 상세 정보
$type_1_parts = [
    "구분: 4도",
    "재질: 아트지 150g",
    "규격: A2 (420x594mm)",
    "수량: 100매",
    "인쇄면: 단면",
    "편집: 인쇄만"
];
$type_1 = implode(" | ", $type_1_parts);

$insert_query = "INSERT INTO mlangorder_printauto (
    no, Type, ImgFolder, ThingCate, Type_1,
    name, email, phone, Hendphone, zip, zip1, zip2,
    money_2, money_4, money_5, date, OrderStyle,
    delivery, bank, cont
) VALUES (
    ?, '포스터', ?, ?, ?,
    '포스터테스트', 'poster@test.com', '02-9999-8888', '010-1234-5678', '06234', '서울시 강남구 테헤란로', '123번길 45',
    0, 85000, 93500, NOW(), '2',
    '택배', '무통장입금', '포스터 테스트 주문입니다. 고해상도로 출력 부탁드립니다.'
)";

$stmt = $db->prepare($insert_query);
$stmt->bind_param("isss", $order_no, $img_folder, $thing_cate, $type_1);

if ($stmt->execute()) {
    echo "✅ 포스터 주문 저장 성공<br>";
} else {
    echo "❌ 주문 저장 실패: " . $stmt->error . "<br>";
    exit;
}

$stmt->close();

// 5. 결과 확인
echo "<br><h3>✅ 포스터 테스트 주문 생성 완료!</h3>";
echo "<div style='background: #e8f5e9; padding: 15px; border-left: 4px solid #4caf50; margin: 10px 0;'>";
echo "<p><strong>주문번호:</strong> $order_no</p>";
echo "<p><strong>품목:</strong> 포스터 (littleprint)</p>";
echo "<p><strong>파일 경로:</strong> $img_folder</p>";
echo "<p><strong>대표 파일:</strong> $thing_cate</p>";
echo "<p><strong>주문 상세:</strong> $type_1</p>";
echo "</div>";

// 6. 실제 파일 확인
echo "<br><h3>📁 업로드된 파일 목록</h3>";
if (is_dir($final_folder)) {
    echo "✅ 폴더 존재: <code>$final_folder</code><br><br>";
    $files = scandir($final_folder);
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f5f5f5;'><th>파일명</th><th>크기</th><th>경로</th></tr>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $file_size = filesize($final_folder . $file);
            $file_path = $img_folder . $file;
            echo "<tr>";
            echo "<td><strong>$file</strong></td>";
            echo "<td>" . number_format($file_size) . " bytes</td>";
            echo "<td><code>$file_path</code></td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "❌ 폴더가 존재하지 않습니다<br>";
}

// 7. DB 확인
echo "<br><h3>💾 DB 저장 내용 확인</h3>";
$check_query = "SELECT no, Type, ImgFolder, ThingCate, Type_1, name, money_4, money_5 FROM mlangorder_printauto WHERE no = ?";
$check_stmt = $db->prepare($check_query);
$check_stmt->bind_param("i", $order_no);
$check_stmt->execute();
$result = $check_stmt->get_result();
$order = $result->fetch_assoc();

if ($order) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f5f5f5;'><th>필드</th><th>값</th></tr>";
    foreach ($order as $key => $value) {
        echo "<tr>";
        echo "<td><strong>$key</strong></td>";
        echo "<td>" . htmlspecialchars($value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$check_stmt->close();
$db->close();

// 8. 관리자 페이지 링크
echo "<br><h3>🔗 관리자 페이지에서 확인</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='http://dsp1830.shop/admin/mlangprintauto/admin.php?mode=OrderView&no=$order_no' target='_blank' style='display: inline-block; font-size: 18px; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>📋 포스터 주문 $order_no 보기</a>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
echo "<h4>✅ 확인 사항</h4>";
echo "<ul>";
echo "<li>📎 첨부 파일 섹션에 2개 파일이 표시되는지</li>";
echo "<li>📁 폴더 경로가 <code>mlangorder_printauto/upload/$order_no/</code>로 표시되는지</li>";
echo "<li>📄 파일 다운로드가 작동하는지</li>";
echo "<li>🖼️ 대표 파일($test_filename)에 (대표 파일) 표시가 있는지</li>";
echo "</ul>";
echo "</div>";
?>
