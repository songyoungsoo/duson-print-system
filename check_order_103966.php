<?php
// 주문 #103966 파일 정보 확인
require_once __DIR__ . '/db.php';

$no = 103966;

echo "<pre>";
echo "=== 주문 #$no 파일 정보 확인 ===\n\n";

$query = "SELECT no, Type, ImgFolder, uploaded_files, date
          FROM mlangorder_printauto
          WHERE no = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && $row = mysqli_fetch_assoc($result)) {
    echo "주문번호: " . $row['no'] . "\n";
    echo "제품타입: " . $row['Type'] . "\n";
    echo "주문날짜: " . $row['date'] . "\n";
    echo "ImgFolder: " . ($row['ImgFolder'] ?: '(empty)') . "\n\n";

    // uploaded_files JSON 파싱
    echo "uploaded_files:\n";
    if (!empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
        $files = json_decode($row['uploaded_files'], true);
        if (is_array($files) && count($files) > 0) {
            echo "파싱 성공 (" . count($files) . "개 파일):\n";
            foreach ($files as $idx => $file) {
                echo "\n파일 " . ($idx + 1) . ":\n";
                echo "  original_name: " . ($file['original_name'] ?? 'N/A') . "\n";
                echo "  saved_name: " . ($file['saved_name'] ?? 'N/A') . "\n";
                echo "  path: " . ($file['path'] ?? 'N/A') . "\n";
                echo "  web_url: " . ($file['web_url'] ?? 'N/A') . "\n";

                if (isset($file['path'])) {
                    $exists = file_exists($file['path']) ? 'YES ✅' : 'NO ❌';
                    echo "  실제 존재: $exists\n";
                }
            }
        } else {
            echo "JSON 파싱 실패 또는 빈 배열\n";
        }
    } else {
        echo "(empty)\n";
    }

    // ImgFolder 디렉토리 스캔
    if (!empty($row['ImgFolder'])) {
        echo "\n=== ImgFolder 디렉토리 스캔 ===\n";
        $img_dir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $row['ImgFolder'];
        echo "경로: $img_dir\n";

        if (is_dir($img_dir)) {
            echo "디렉토리 존재: YES ✅\n";
            $files = array_diff(scandir($img_dir), ['.', '..']);
            echo "파일 개수: " . count($files) . "\n";
            if (count($files) > 0) {
                echo "파일 목록:\n";
                foreach ($files as $file) {
                    echo "  - $file\n";
                }
            }
        } else {
            echo "디렉토리 존재: NO ❌\n";
        }
    }

    // 중복 확인
    echo "\n=== 중복 파일 검사 ===\n";
    $json_files = [];
    $dir_files = [];

    if (!empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
        $files = json_decode($row['uploaded_files'], true);
        if (is_array($files)) {
            foreach ($files as $file) {
                $json_files[] = $file['saved_name'] ?? $file['original_name'] ?? '';
            }
        }
    }

    if (!empty($row['ImgFolder'])) {
        $img_dir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $row['ImgFolder'];
        if (is_dir($img_dir)) {
            $dir_files = array_diff(scandir($img_dir), ['.', '..']);
        }
    }

    echo "JSON 파일 목록: " . implode(', ', $json_files) . "\n";
    echo "디렉토리 파일 목록: " . implode(', ', $dir_files) . "\n";

    $duplicates = array_intersect($json_files, $dir_files);
    if (count($duplicates) > 0) {
        echo "\n⚠️ 중복 파일 발견: " . implode(', ', $duplicates) . "\n";
        echo "→ admin.php가 이 파일들을 2번 표시할 가능성 있음\n";
    } else {
        echo "\n✅ 중복 없음\n";
    }

} else {
    echo "❌ 주문을 찾을 수 없습니다.\n";
}

echo "</pre>";
mysqli_close($db);
?>
