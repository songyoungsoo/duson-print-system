<?php
/**
 * 파일 업로드 시스템 테스트
 * 경로: MlangPrintAuto/shop/test_file_system.php
 * 
 * 새로 구축된 파일 시스템이 제대로 작동하는지 테스트
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";
include "file_management_helper.php";

$test_results = [];

echo "<h1>📋 파일 업로드 시스템 테스트</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>";

// 1. 데이터베이스 연결 테스트
echo "<h2>1️⃣ 데이터베이스 연결 테스트</h2>";
try {
    check_db_connection($db);
    echo "<p style='color: green;'>✅ 데이터베이스 연결 성공</p>";
    $test_results['db_connection'] = true;
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 데이터베이스 연결 실패: " . $e->getMessage() . "</p>";
    $test_results['db_connection'] = false;
}

// 2. shop_temp 테이블 구조 확인
echo "<h2>2️⃣ shop_temp 테이블 구조 확인</h2>";
try {
    $query = "DESCRIBE shop_temp";
    $result = mysqli_query($db, $query);
    
    $required_fields = ['file_path', 'file_info', 'upload_log', 'log_url', 'log_y', 'log_md', 'log_ip', 'log_time'];
    $existing_fields = [];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>필드명</th><th>타입</th><th>설명</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $existing_fields[] = $row['Field'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Comment'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 필수 필드 확인
    $missing_fields = array_diff($required_fields, $existing_fields);
    if (empty($missing_fields)) {
        echo "<p style='color: green;'>✅ 모든 필수 필드가 존재합니다.</p>";
        $test_results['table_structure'] = true;
    } else {
        echo "<p style='color: red;'>❌ 누락된 필드: " . implode(', ', $missing_fields) . "</p>";
        $test_results['table_structure'] = false;
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 테이블 구조 확인 실패: " . $e->getMessage() . "</p>";
    $test_results['table_structure'] = false;
}

// 3. 파일 관리 함수 테스트
echo "<h2>3️⃣ 파일 관리 함수 테스트</h2>";

// 로그 정보 생성 테스트
try {
    $log_info = generateFileLogInfo('test');
    echo "<p style='color: green;'>✅ 로그 정보 생성 성공</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    print_r($log_info);
    echo "</pre>";
    $test_results['log_generation'] = true;
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 로그 정보 생성 실패: " . $e->getMessage() . "</p>";
    $test_results['log_generation'] = false;
}

// 업로드 디렉토리 생성 테스트
try {
    $upload_dir = createFileUploadDirectory($log_info);
    if (is_dir($upload_dir)) {
        echo "<p style='color: green;'>✅ 업로드 디렉토리 생성 성공: " . htmlspecialchars($upload_dir) . "</p>";
        $test_results['directory_creation'] = true;
    } else {
        echo "<p style='color: red;'>❌ 업로드 디렉토리 생성 실패</p>";
        $test_results['directory_creation'] = false;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 업로드 디렉토리 생성 실패: " . $e->getMessage() . "</p>";
    $test_results['directory_creation'] = false;
}

// 4. 테스트 장바구니 아이템 생성
echo "<h2>4️⃣ 테스트 장바구니 아이템 생성</h2>";
try {
    $session_id = session_id();
    
    // 테스트 데이터 삽입
    $test_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, MY_Fsd, MY_amount, st_price, st_price_vat, regdate) 
                   VALUES (?, 'test', 'test_type', 'test_fsd', '100', 10000, 11000, ?)";
    
    $stmt = mysqli_prepare($db, $test_query);
    $regdate = time();
    mysqli_stmt_bind_param($stmt, 'si', $session_id, $regdate);
    
    if (mysqli_stmt_execute($stmt)) {
        $test_cart_item_no = mysqli_insert_id($db);
        echo "<p style='color: green;'>✅ 테스트 장바구니 아이템 생성 성공 (ID: $test_cart_item_no)</p>";
        $test_results['cart_item_creation'] = true;
        
        // 5. 파일 정보 추가 테스트
        echo "<h2>5️⃣ 파일 정보 추가 테스트</h2>";
        
        $test_file_info = [
            'original_name' => 'test_file.jpg',
            'saved_name' => 'test_' . time() . '_file.jpg',
            'file_size' => 1024000,
            'file_type' => 'image/jpeg',
            'upload_path' => $upload_dir . '/test_file.jpg'
        ];
        
        if (addFileToCartItem($db, $test_cart_item_no, $test_file_info, $log_info)) {
            echo "<p style='color: green;'>✅ 파일 정보 추가 성공</p>";
            $test_results['file_info_addition'] = true;
            
            // 6. 파일 정보 조회 테스트
            echo "<h2>6️⃣ 파일 정보 조회 테스트</h2>";
            
            $files = getCartItemFiles($db, $test_cart_item_no);
            if (!empty($files)) {
                echo "<p style='color: green;'>✅ 파일 정보 조회 성공</p>";
                echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
                print_r($files);
                echo "</pre>";
                $test_results['file_info_retrieval'] = true;
            } else {
                echo "<p style='color: red;'>❌ 파일 정보 조회 실패</p>";
                $test_results['file_info_retrieval'] = false;
            }
            
        } else {
            echo "<p style='color: red;'>❌ 파일 정보 추가 실패</p>";
            $test_results['file_info_addition'] = false;
        }
        
        // 테스트 데이터 정리
        $cleanup_query = "DELETE FROM shop_temp WHERE no = ?";
        $cleanup_stmt = mysqli_prepare($db, $cleanup_query);
        mysqli_stmt_bind_param($cleanup_stmt, 'i', $test_cart_item_no);
        mysqli_stmt_execute($cleanup_stmt);
        mysqli_stmt_close($cleanup_stmt);
        echo "<p style='color: blue;'>🧹 테스트 데이터 정리 완료</p>";
        
    } else {
        echo "<p style='color: red;'>❌ 테스트 장바구니 아이템 생성 실패</p>";
        $test_results['cart_item_creation'] = false;
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 테스트 장바구니 아이템 생성 실패: " . $e->getMessage() . "</p>";
    $test_results['cart_item_creation'] = false;
}

// 7. 전체 테스트 결과 요약
echo "<h2>📊 테스트 결과 요약</h2>";

$total_tests = count($test_results);
$passed_tests = array_sum($test_results);
$success_rate = ($passed_tests / $total_tests) * 100;

echo "<div style='background: " . ($success_rate == 100 ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>총 테스트: {$total_tests}개 | 성공: {$passed_tests}개 | 성공률: " . number_format($success_rate, 1) . "%</h3>";

foreach ($test_results as $test_name => $result) {
    $status = $result ? '✅ 성공' : '❌ 실패';
    $color = $result ? 'green' : 'red';
    echo "<p style='color: $color;'>$status - $test_name</p>";
}
echo "</div>";

if ($success_rate == 100) {
    echo "<h3 style='color: green;'>🎉 모든 테스트가 성공했습니다! 파일 시스템이 정상적으로 구축되었습니다.</h3>";
    echo "<p><strong>다음 단계:</strong> 실제 상품 페이지에 파일 업로드 기능을 적용할 수 있습니다.</p>";
} else {
    echo "<h3 style='color: red;'>⚠️ 일부 테스트가 실패했습니다. 문제를 해결한 후 다시 테스트해주세요.</h3>";
}

echo "</div>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 20px;
    background-color: #f5f5f5;
}

h1, h2 {
    color: #333;
}

table {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

th {
    background-color: #007bff;
    color: white;
    padding: 10px;
}

td {
    padding: 8px;
    border-bottom: 1px solid #ddd;
}

pre {
    overflow-x: auto;
    font-size: 12px;
}
</style>