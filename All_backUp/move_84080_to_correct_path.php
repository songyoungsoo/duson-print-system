<?php
// 84080 주문 파일을 구버전 호환 경로로 이동

$source_dir = "uploads/orders/84080/";
$target_dir = "mlangorder_printauto/upload/84080/";

echo "<h2>84080 주문 파일 이동 (구버전 호환 경로)</h2>";

// 대상 디렉토리가 없으면 생성
if (!is_dir($target_dir)) {
    if (mkdir($target_dir, 0755, true)) {
        echo "✅ 디렉토리 생성: $target_dir<br>";
    } else {
        echo "❌ 디렉토리 생성 실패: $target_dir<br>";
        exit;
    }
}

// 소스 디렉토리 확인
if (!is_dir($source_dir)) {
    echo "❌ 소스 디렉토리를 찾을 수 없습니다: $source_dir<br>";
    exit;
}

// 파일 이동
$files = scandir($source_dir);
$moved_count = 0;

foreach ($files as $file) {
    if ($file != "." && $file != "..") {
        $source_file = $source_dir . $file;
        $target_file = $target_dir . $file;
        
        if (is_file($source_file)) {
            if (rename($source_file, $target_file)) {
                echo "✅ 파일 이동 성공: $file<br>";
                echo "   원본: $source_file<br>";
                echo "   대상: $target_file<br>";
                $moved_count++;
            } else {
                echo "❌ 파일 이동 실패: $file<br>";
            }
        }
    }
}

echo "<br>📊 총 $moved_count 개 파일 이동 완료<br>";

// DB 업데이트
include "db.php";

$no = 84080;
$new_img_folder = "mlangorder_printauto/upload/84080/";

$stmt = $db->prepare("UPDATE mlangorder_printauto SET ImgFolder = ? WHERE no = ?");
$stmt->bind_param("si", $new_img_folder, $no);

if ($stmt->execute()) {
    echo "<br>✅ DB ImgFolder 업데이트 성공: $new_img_folder<br>";
} else {
    echo "<br>❌ DB 업데이트 실패: " . $stmt->error . "<br>";
}

$stmt->close();
$db->close();

// 빈 폴더 삭제
if (is_dir($source_dir)) {
    if (rmdir($source_dir)) {
        echo "<br>✅ 빈 소스 폴더 삭제: $source_dir<br>";
    }
}

echo "<br><h3>✅ 완료! 이제 구버전과 동일한 경로 구조를 사용합니다.</h3>";
echo "<p>경로: <code>mlangorder_printauto/upload/84080/</code></p>";
?>
