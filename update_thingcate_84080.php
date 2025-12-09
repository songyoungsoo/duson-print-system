<?php
include "db.php";

$no = 84080;
$correct_filename = "1762493199_등대씨엔충전보관함양면전단_접지없음.jpg";

// 현재 값 확인
$stmt = $db->prepare("SELECT ThingCate FROM mlangorder_printauto WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$old_value = $row['ThingCate'];
$stmt->close();

echo "📋 주문번호: $no\n";
echo "❌ 기존 ThingCate: $old_value\n";
echo "✅ 새로운 ThingCate: $correct_filename\n\n";

// 업데이트 실행
$stmt = $db->prepare("UPDATE mlangorder_printauto SET ThingCate = ? WHERE no = ?");
$stmt->bind_param("si", $correct_filename, $no);

if ($stmt->execute()) {
    echo "✅ ThingCate 업데이트 성공!\n";
    
    // 업데이트 확인
    $stmt2 = $db->prepare("SELECT ThingCate FROM mlangorder_printauto WHERE no = ?");
    $stmt2->bind_param("i", $no);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    echo "✔️ 확인된 값: " . $row2['ThingCate'] . "\n";
    $stmt2->close();
} else {
    echo "❌ 업데이트 실패: " . $stmt->error . "\n";
}

$stmt->close();
$db->close();
?>
