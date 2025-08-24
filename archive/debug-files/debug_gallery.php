<?php
/**
 * 갤러리 디버깅 도구
 * 이미지 가져오기 문제 원인 분석 및 해결
 */

session_start();
require_once "../db.php";

// 데이터베이스 연결 확인
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
echo "<title>갤러리 디버그 도구</title>";
echo "<style>";
echo "body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".debug-section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f8f9fa; }";
echo ".image-test { max-width: 200px; max-height: 150px; border: 1px solid #ddd; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 갤러리 디버깅 도구</h1>";

// 1. 데이터베이스 연결 확인
echo "<div class='debug-section'>";
echo "<h2>1. 데이터베이스 연결 상태</h2>";
if ($connect) {
    echo "<div class='success'>✅ 데이터베이스 연결 성공</div>";
    echo "<div class='info'>연결 정보: " . mysqli_get_host_info($connect) . "</div>";
} else {
    echo "<div class='error'>❌ 데이터베이스 연결 실패</div>";
    exit;
}
echo "</div>";

// 2. MlangOrder_PrintAuto 테이블 구조 확인
echo "<div class='debug-section'>";
echo "<h2>2. MlangOrder_PrintAuto 테이블 구조</h2>";

$table_check = mysqli_query($connect, "SHOW COLUMNS FROM MlangOrder_PrintAuto");
if ($table_check) {
    echo "<div class='success'>✅ 테이블이 존재합니다</div>";
    echo "<table>";
    echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>키</th><th>기본값</th></tr>";
    while ($col = mysqli_fetch_assoc($table_check)) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ 테이블을 찾을 수 없습니다: " . mysqli_error($connect) . "</div>";
}
echo "</div>";

// 3. 데이터 샘플 확인
echo "<div class='debug-section'>";
echo "<h2>3. 주문 데이터 샘플 (최신 20건)</h2>";

$data_sql = "SELECT No, OrderStyle, Type, ThingCate, name, date 
             FROM MlangOrder_PrintAuto 
             ORDER BY No DESC 
             LIMIT 20";
$data_result = mysqli_query($connect, $data_sql);

if ($data_result) {
    $total_rows = mysqli_num_rows($data_result);
    echo "<div class='info'>총 {$total_rows}개 레코드 발견</div>";
    
    echo "<table>";
    echo "<tr><th>No</th><th>OrderStyle</th><th>Type</th><th>ThingCate</th><th>name</th><th>date</th></tr>";
    while ($row = mysqli_fetch_assoc($data_result)) {
        $style_class = ($row['OrderStyle'] == '8') ? 'success' : 'warning';
        echo "<tr class='{$style_class}'>";
        echo "<td>{$row['No']}</td>";
        echo "<td>{$row['OrderStyle']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['ThingCate']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['date']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ 데이터 조회 실패: " . mysqli_error($connect) . "</div>";
}
echo "</div>";

// 4. 완성된 주문(OrderStyle=8) 확인
echo "<div class='debug-section'>";
echo "<h2>4. 완성된 주문 (OrderStyle=8) 통계</h2>";

$complete_sql = "SELECT OrderStyle, COUNT(*) as count FROM MlangOrder_PrintAuto GROUP BY OrderStyle ORDER BY OrderStyle";
$complete_result = mysqli_query($connect, $complete_sql);

if ($complete_result) {
    echo "<table>";
    echo "<tr><th>OrderStyle</th><th>개수</th><th>상태</th></tr>";
    while ($row = mysqli_fetch_assoc($complete_result)) {
        $status = '';
        switch($row['OrderStyle']) {
            case '8': $status = '완성됨 (갤러리 대상)'; break;
            case '1': $status = '주문접수'; break;
            case '2': $status = '디자인중'; break;
            case '3': $status = '디자인완료'; break;
            case '4': $status = '인쇄중'; break;
            default: $status = '기타'; break;
        }
        echo "<tr>";
        echo "<td>{$row['OrderStyle']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ 통계 조회 실패</div>";
}
echo "</div>";

// 5. ThingCate 필드 분석
echo "<div class='debug-section'>";
echo "<h2>5. ThingCate 필드 분석 (완성된 주문만)</h2>";

$thingcate_sql = "SELECT ThingCate, COUNT(*) as count 
                  FROM MlangOrder_PrintAuto 
                  WHERE OrderStyle = '8' AND ThingCate != '' AND ThingCate IS NOT NULL
                  GROUP BY ThingCate 
                  ORDER BY count DESC 
                  LIMIT 20";
$thingcate_result = mysqli_query($connect, $thingcate_sql);

if ($thingcate_result) {
    echo "<div class='info'>ThingCate 값 분포 (상위 20개)</div>";
    echo "<table>";
    echo "<tr><th>ThingCate 값</th><th>개수</th></tr>";
    while ($row = mysqli_fetch_assoc($thingcate_result)) {
        echo "<tr>";
        echo "<td>{$row['ThingCate']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ ThingCate 분석 실패</div>";
}
echo "</div>";

// 6. 업로드 디렉토리 확인
echo "<div class='debug-section'>";
echo "<h2>6. 업로드 디렉토리 구조 확인</h2>";

$upload_base = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload";
echo "<div class='info'>기본 경로: {$upload_base}</div>";

if (is_dir($upload_base)) {
    echo "<div class='success'>✅ 업로드 디렉토리 존재</div>";
    
    // 디렉토리 목록 확인 (숫자로 시작하는 것들)
    $dirs = glob($upload_base . "/*", GLOB_ONLYDIR);
    $numeric_dirs = [];
    $date_dirs = [];
    
    foreach ($dirs as $dir) {
        $dirname = basename($dir);
        if (is_numeric($dirname)) {
            $numeric_dirs[] = $dirname;
        } elseif (preg_match('/^0\d{4}$/', $dirname)) {
            $date_dirs[] = $dirname;
        }
    }
    
    echo "<div class='info'>숫자 디렉토리 (새 구조): " . count($numeric_dirs) . "개</div>";
    echo "<div class='info'>날짜 디렉토리 (구 구조): " . count($date_dirs) . "개</div>";
    
    // 샘플 디렉토리 내용 확인
    if (!empty($numeric_dirs)) {
        sort($numeric_dirs, SORT_NUMERIC);
        $sample_dirs = array_slice(array_reverse($numeric_dirs), 0, 5);
        echo "<h3>최신 5개 주문 디렉토리 내용:</h3>";
        
        foreach ($sample_dirs as $dir) {
            $dir_path = $upload_base . "/" . $dir;
            $files = glob($dir_path . "/*");
            echo "<div><strong>주문번호 {$dir}:</strong> " . count($files) . "개 파일</div>";
            
            if (!empty($files)) {
                $image_files = array_filter($files, function($file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                });
                
                if (!empty($image_files)) {
                    $first_image = array_values($image_files)[0];
                    $web_path = "/MlangOrder_PrintAuto/upload/{$dir}/" . basename($first_image);
                    echo "<div style='margin-left: 20px;'>";
                    echo "첫 번째 이미지: " . basename($first_image) . "<br>";
                    echo "<img src='{$web_path}' class='image-test' alt='테스트 이미지' onerror='this.style.border=\"2px solid red\"; this.alt=\"이미지 로드 실패\";'>";
                    echo "</div>";
                }
            }
        }
    }
    
} else {
    echo "<div class='error'>❌ 업로드 디렉토리가 존재하지 않습니다</div>";
}
echo "</div>";

// 7. 실제 이미지 테스트
echo "<div class='debug-section'>";
echo "<h2>7. 실제 이미지 파일 매칭 테스트</h2>";

$test_sql = "SELECT No, ThingCate, Type 
             FROM MlangOrder_PrintAuto 
             WHERE OrderStyle = '8' AND ThingCate != '' AND ThingCate IS NOT NULL
             ORDER BY No DESC 
             LIMIT 10";
$test_result = mysqli_query($connect, $test_sql);

if ($test_result) {
    echo "<table>";
    echo "<tr><th>주문번호</th><th>ThingCate</th><th>파일 존재</th><th>이미지 테스트</th></tr>";
    
    while ($row = mysqli_fetch_assoc($test_result)) {
        $order_no = $row['No'];
        $thing_cate = $row['ThingCate'];
        
        echo "<tr>";
        echo "<td>{$order_no}</td>";
        echo "<td>{$thing_cate}</td>";
        
        // 파일 경로 체크
        $found_path = '';
        
        // 새 구조 확인
        $new_path = $upload_base . "/" . $order_no . "/" . $thing_cate;
        if (file_exists($new_path)) {
            $found_path = "/MlangOrder_PrintAuto/upload/{$order_no}/{$thing_cate}";
            echo "<td class='success'>✅ 새 구조</td>";
        } else {
            // 구 구조 확인
            $found = false;
            if (!empty($date_dirs)) {
                foreach ($date_dirs as $date_dir) {
                    $old_path = $upload_base . "/" . $date_dir . "/" . $order_no . "/" . $thing_cate;
                    if (file_exists($old_path)) {
                        $found_path = "/MlangOrder_PrintAuto/upload/{$date_dir}/{$order_no}/{$thing_cate}";
                        echo "<td class='success'>✅ 구 구조 ({$date_dir})</td>";
                        $found = true;
                        break;
                    }
                }
            }
            
            if (!$found) {
                echo "<td class='error'>❌ 파일 없음</td>";
            }
        }
        
        // 이미지 표시
        if ($found_path) {
            echo "<td><img src='{$found_path}' class='image-test' alt='주문번호 {$order_no}' onerror='this.style.border=\"2px solid red\"; this.alt=\"로드 실패\";'></td>";
        } else {
            echo "<td>-</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<div class='error'>❌ 테스트 데이터 조회 실패</div>";
}
echo "</div>";

// 8. 권장 해결 방법
echo "<div class='debug-section'>";
echo "<h2>8. 문제 해결 권장사항</h2>";

echo "<h3>발견된 문제들:</h3>";
echo "<ul>";

// 완성된 주문 개수 확인
$complete_count_result = mysqli_query($connect, "SELECT COUNT(*) as count FROM MlangOrder_PrintAuto WHERE OrderStyle = '8' AND ThingCate != '' AND ThingCate IS NOT NULL");
$complete_count = 0;
if ($complete_count_result) {
    $complete_count = mysqli_fetch_assoc($complete_count_result)['count'];
}

if ($complete_count == 0) {
    echo "<li class='error'>완성된 주문(OrderStyle=8)이 없거나 ThingCate가 비어있음</li>";
} else {
    echo "<li class='success'>완성된 주문 {$complete_count}개 발견</li>";
}

if (!is_dir($upload_base)) {
    echo "<li class='error'>업로드 디렉토리가 존재하지 않음</li>";
} else {
    echo "<li class='success'>업로드 디렉토리 존재함</li>";
}

echo "</ul>";

echo "<h3>해결 방법:</h3>";
echo "<ol>";
echo "<li><strong>데이터 문제인 경우:</strong> OrderStyle을 8로 업데이트하거나 ThingCate 값을 채워넣기</li>";
echo "<li><strong>파일 경로 문제인 경우:</strong> 업로드 디렉토리 권한 확인 (755 권한 필요)</li>";
echo "<li><strong>이미지 파일명 문제:</strong> ThingCate 값이 실제 파일명과 일치하는지 확인</li>";
echo "</ol>";

echo "</div>";

echo "</body>";
echo "</html>";
?>