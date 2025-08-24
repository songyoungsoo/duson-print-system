<?php
/**
 * 파일 스캔 디버그 도구
 * 실제 이미지 파일이 있는 디렉토리 찾기
 */

session_start();
require_once dirname(__DIR__) . "/db.php";

if (!isset($connect) && isset($db)) {
    $connect = $db;
}

if ($connect) {
    mysqli_set_charset($connect, "utf8");
}

echo "<!DOCTYPE html>";
echo "<html lang='ko'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>파일 스캔 디버그</title>";
echo "<style>";
echo "body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; }";
echo ".found { color: green; font-weight: bold; }";
echo ".empty { color: #999; }";
echo ".error { color: red; }";
echo "table { border-collapse: collapse; width: 100%; margin: 20px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f8f9fa; }";
echo ".image-preview { max-width: 100px; max-height: 100px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 실제 파일 스캔 결과</h1>";

$upload_base = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload";
echo "<p><strong>스캔 경로:</strong> {$upload_base}</p>";

// 실제로 디렉토리가 있는 주문 번호 찾기
echo "<h2>실제 디렉토리 스캔</h2>";
$dirs = glob($upload_base . "/*", GLOB_ONLYDIR);
$numeric_dirs = [];
foreach ($dirs as $dir) {
    $dirname = basename($dir);
    if (is_numeric($dirname)) {
        $numeric_dirs[] = intval($dirname);
    }
}
sort($numeric_dirs);
echo "<p>발견된 디렉토리 범위: " . min($numeric_dirs) . " ~ " . max($numeric_dirs) . " (총 " . count($numeric_dirs) . "개)</p>";

// 디렉토리가 실제로 존재하는 주문만 조회
$dir_list = implode(',', array_slice($numeric_dirs, -100)); // 최신 100개 디렉토리
$sql = "SELECT No, Type, name, date 
        FROM MlangOrder_PrintAuto 
        WHERE OrderStyle IN ('2', '3', '7', '8')
        AND No IN ({$dir_list})
        ORDER BY No DESC 
        LIMIT 100";

$result = mysqli_query($connect, $sql);
$found_images = [];
$empty_dirs = 0;
$checked_dirs = 0;

echo "<h2>스캔 진행 상황</h2>";
echo "<table>";
echo "<tr><th>주문번호</th><th>타입</th><th>고객명</th><th>디렉토리 상태</th><th>파일</th><th>이미지 미리보기</th></tr>";

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $order_no = $row['No'];
        $order_dir = $upload_base . "/" . $order_no;
        $checked_dirs++;
        
        echo "<tr>";
        echo "<td>{$order_no}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['name']}</td>";
        
        if (is_dir($order_dir)) {
            $files = glob($order_dir . "/*");
            $image_files = [];
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $image_files[] = basename($file);
                    }
                }
            }
            
            if (!empty($image_files)) {
                echo "<td class='found'>✅ " . count($image_files) . "개 이미지</td>";
                echo "<td>" . implode(', ', array_slice($image_files, 0, 3)) . 
                     (count($image_files) > 3 ? '...' : '') . "</td>";
                
                // 첫 번째 이미지 미리보기
                $first_image = $image_files[0];
                $web_path = "/MlangOrder_PrintAuto/upload/{$order_no}/{$first_image}";
                echo "<td><img src='{$web_path}' class='image-preview' alt='미리보기'></td>";
                
                $found_images[] = [
                    'order_no' => $order_no,
                    'type' => $row['Type'],
                    'name' => $row['name'],
                    'date' => $row['date'],
                    'files' => $image_files,
                    'web_path' => $web_path
                ];
            } else {
                echo "<td class='empty'>📁 빈 디렉토리 (" . count($files) . "개 파일)</td>";
                echo "<td>" . (count($files) > 0 ? implode(', ', array_map('basename', array_slice($files, 0, 3))) : '파일 없음') . "</td>";
                echo "<td>-</td>";
                $empty_dirs++;
            }
        } else {
            echo "<td class='error'>❌ 디렉토리 없음</td>";
            echo "<td>-</td>";
            echo "<td>-</td>";
        }
        
        echo "</tr>";
        
        // 10개 찾으면 중단
        if (count($found_images) >= 10) {
            break;
        }
    }
}

echo "</table>";

echo "<h2>📊 스캔 결과 요약</h2>";
echo "<ul>";
echo "<li><strong>검사한 디렉토리:</strong> {$checked_dirs}개</li>";
echo "<li><strong>이미지 발견:</strong> " . count($found_images) . "개 주문</li>";
echo "<li><strong>빈 디렉토리:</strong> {$empty_dirs}개</li>";
echo "</ul>";

if (!empty($found_images)) {
    echo "<h2>🎯 실제 갤러리 표시 가능한 항목</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    foreach (array_slice($found_images, 0, 6) as $item) {
        echo "<div style='border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: #fff;'>";
        echo "<img src='{$item['web_path']}' style='width: 100%; height: 200px; object-fit: cover; border-radius: 4px; margin-bottom: 10px;'>";
        echo "<div><strong>주문번호:</strong> {$item['order_no']}</div>";
        echo "<div><strong>타입:</strong> {$item['type']}</div>";
        echo "<div><strong>고객:</strong> {$item['name']}</div>";
        echo "<div><strong>파일:</strong> " . count($item['files']) . "개</div>";
        echo "</div>";
    }
    
    echo "</div>";
    
    echo "<h2>🔧 권장 해결 방법</h2>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<p><strong>갤러리 수정 방향:</strong></p>";
    echo "<ol>";
    echo "<li>먼저 실제 이미지가 있는 주문을 찾기</li>";
    echo "<li>해당 주문들만 갤러리에 표시</li>";
    echo "<li>더 많은 이미지를 찾기 위해 검색 범위 확대</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #ffe8e8; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<p><strong>⚠️ 이미지를 찾을 수 없습니다</strong></p>";
    echo "<p>최근 100개 주문에서 실제 이미지 파일이 발견되지 않았습니다.</p>";
    echo "<p>더 오래된 주문을 검사하거나 다른 디렉토리 구조를 확인해야 합니다.</p>";
    echo "</div>";
}

echo "</body>";
echo "</html>";
?>