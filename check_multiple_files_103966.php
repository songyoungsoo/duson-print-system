<?php
// 주문 #103966의 파일 첨부 상황 확인
require_once __DIR__ . '/db.php';

$no = 103966;

echo "<pre>";
echo "=== 주문 #$no 상세 파일 정보 ===\n\n";

$query = "SELECT no, Type, ImgFolder, uploaded_files
          FROM mlangorder_printauto
          WHERE no = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && $row = mysqli_fetch_assoc($result)) {
    echo "주문번호: " . $row['no'] . "\n";
    echo "제품타입: " . $row['Type'] . "\n\n";

    // uploaded_files 원본 JSON 출력
    echo "=== uploaded_files 원본 JSON ===\n";
    $uploaded_files_json = $row['uploaded_files'];
    echo "길이: " . strlen($uploaded_files_json) . " bytes\n";
    echo "내용: ";
    if (empty($uploaded_files_json) || $uploaded_files_json === '0') {
        echo "(empty)\n";
    } else {
        echo "\n" . $uploaded_files_json . "\n";
    }

    // JSON 파싱
    echo "\n=== JSON 파싱 결과 ===\n";
    if (!empty($uploaded_files_json) && $uploaded_files_json !== '0') {
        $files = json_decode($uploaded_files_json, true);
        if (is_array($files)) {
            echo "파일 개수: " . count($files) . "\n\n";

            foreach ($files as $idx => $file) {
                echo "파일 #" . ($idx + 1) . ":\n";
                echo "  original_name: " . ($file['original_name'] ?? 'N/A') . "\n";
                echo "  saved_name: " . ($file['saved_name'] ?? 'N/A') . "\n";
                echo "  path: " . ($file['path'] ?? 'N/A') . "\n";
                echo "  size: " . ($file['size'] ?? 'N/A') . "\n";
                echo "\n";
            }

            // 같은 original_name 체크
            $original_names = array_column($files, 'original_name');
            $duplicates = array_count_values($original_names);

            echo "=== 파일명 중복 체크 ===\n";
            foreach ($duplicates as $name => $count) {
                if ($count > 1) {
                    echo "⚠️ '$name' - {$count}번 첨부됨\n";
                } else {
                    echo "✅ '$name' - 1번 첨부됨\n";
                }
            }
        } else {
            echo "JSON 파싱 실패\n";
            echo "JSON 오류: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "uploaded_files가 비어있습니다.\n";
    }

    // ImgFolder 디렉토리 확인
    echo "\n=== ImgFolder 디렉토리 ===\n";
    echo "ImgFolder: " . ($row['ImgFolder'] ?: '(empty)') . "\n";

    if (!empty($row['ImgFolder'])) {
        $img_dir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $row['ImgFolder'];
        echo "전체 경로: $img_dir\n";

        if (is_dir($img_dir)) {
            $dir_files = array_diff(scandir($img_dir), ['.', '..']);
            echo "디렉토리 파일 개수: " . count($dir_files) . "\n";
            foreach ($dir_files as $file) {
                echo "  - $file\n";
            }
        } else {
            echo "디렉토리 없음\n";
        }
    }

} else {
    echo "❌ 주문을 찾을 수 없습니다.\n";
}

echo "</pre>";
mysqli_close($db);
?>
