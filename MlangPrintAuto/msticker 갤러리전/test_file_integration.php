<?php
/**
 * msticker 품목 파일 업로드 통합 테스트
 * 경로: MlangPrintAuto/msticker/test_file_integration.php
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";
include "../shop/file_management_helper.php";

echo "<h1>🧪 msticker 파일 업로드 통합 테스트</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>";

$test_results = [];

// 1. 파일 관리 헬퍼 함수 접근 테스트
echo "<h2>1️⃣ 파일 관리 함수 접근 테스트</h2>";
try {
    $log_info = generateFileLogInfo('msticker');
    echo "<p style='color: green;'>✅ generateFileLogInfo() 함수 접근 성공</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    print_r($log_info);
    echo "</pre>";
    $test_results['helper_access'] = true;
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 파일 관리 함수 접근 실패: " . $e->getMessage() . "</p>";
    $test_results['helper_access'] = false;
}

// 2. 경로 확인 테스트
echo "<h2>2️⃣ 상대 경로 확인 테스트</h2>";
$paths_to_check = [
    '../../db.php' => 'DB 연결 파일',
    '../../includes/functions.php' => '공통 함수 파일',
    '../shop/file_management_helper.php' => '파일 관리 헬퍼',
    '../../ImgFolder' => 'ImgFolder 디렉토리'
];

foreach ($paths_to_check as $path => $description) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✅ $description: $path</p>";
    } else {
        echo "<p style='color: red;'>❌ $description: $path (존재하지 않음)</p>";
    }
}

// 3. 테스트 장바구니 아이템 생성 및 파일 연동
echo "<h2>3️⃣ 장바구니-파일 연동 테스트</h2>";
try {
    $session_id = session_id();
    
    // 테스트 장바구니 아이템 생성
    $test_data = [
        'MY_type' => '742',
        'MY_Fsd' => '743', 
        'MY_amount' => '1000',
        'ordertype' => 'design',
        'st_price' => 20000,
        'st_price_vat' => 22000,
        'MY_comment' => '파일 업로드 테스트'
    ];
    
    $insert_query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, MY_amount, 
        ordertype, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'msticker', ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($db, $insert_query);
    $regdate = time();
    
    mysqli_stmt_bind_param($stmt, 'sssssddsi', 
        $session_id, $test_data['MY_type'], $test_data['MY_Fsd'], 
        $test_data['MY_amount'], $test_data['ordertype'],
        $test_data['st_price'], $test_data['st_price_vat'], 
        $test_data['MY_comment'], $regdate
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $cart_item_no = mysqli_insert_id($db);
        echo "<p style='color: green;'>✅ 테스트 장바구니 아이템 생성 성공 (ID: $cart_item_no)</p>";
        
        // 4. 파일 정보 추가 테스트
        echo "<h2>4️⃣ 파일 정보 추가 테스트</h2>";
        
        $test_file_info = [
            'original_name' => 'msticker_design.jpg',
            'saved_name' => 'msticker_' . time() . '_design.jpg',
            'file_size' => 2048000,
            'file_type' => 'image/jpeg',
            'upload_path' => createFileUploadDirectory($log_info) . '/msticker_design.jpg'
        ];
        
        if (addFileToCartItem($db, $cart_item_no, $test_file_info, $log_info)) {
            echo "<p style='color: green;'>✅ 파일 정보 추가 성공</p>";
            
            // 5. 파일 정보 조회 테스트
            echo "<h2>5️⃣ 파일 정보 조회 테스트</h2>";
            $files = getCartItemFiles($db, $cart_item_no);
            
            if (!empty($files)) {
                echo "<p style='color: green;'>✅ 파일 정보 조회 성공</p>";
                echo "<pre style='background: #f5f5f5; padding: 10px;'>";
                print_r($files);
                echo "</pre>";
                $test_results['file_integration'] = true;
            } else {
                echo "<p style='color: red;'>❌ 파일 정보 조회 실패</p>";
                $test_results['file_integration'] = false;
            }
        } else {
            echo "<p style='color: red;'>❌ 파일 정보 추가 실패</p>";
            $test_results['file_integration'] = false;
        }
        
        // 테스트 데이터 정리
        $cleanup_query = "DELETE FROM shop_temp WHERE no = ?";
        $cleanup_stmt = mysqli_prepare($db, $cleanup_query);
        mysqli_stmt_bind_param($cleanup_stmt, 'i', $cart_item_no);
        mysqli_stmt_execute($cleanup_stmt);
        mysqli_stmt_close($cleanup_stmt);
        echo "<p style='color: blue;'>🧹 테스트 데이터 정리 완료</p>";
        
    } else {
        echo "<p style='color: red;'>❌ 테스트 장바구니 아이템 생성 실패</p>";
        $test_results['file_integration'] = false;
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 장바구니-파일 연동 테스트 실패: " . $e->getMessage() . "</p>";
    $test_results['file_integration'] = false;
}

// 6. 전체 테스트 결과
echo "<h2>📊 테스트 결과 요약</h2>";

$total_tests = count($test_results);
$passed_tests = array_sum($test_results);
$success_rate = $total_tests > 0 ? ($passed_tests / $total_tests) * 100 : 0;

echo "<div style='background: " . ($success_rate == 100 ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>총 테스트: {$total_tests}개 | 성공: {$passed_tests}개 | 성공률: " . number_format($success_rate, 1) . "%</h3>";

foreach ($test_results as $test_name => $result) {
    $status = $result ? '✅ 성공' : '❌ 실패';
    $color = $result ? 'green' : 'red';
    echo "<p style='color: $color;'>$status - $test_name</p>";
}
echo "</div>";

if ($success_rate == 100) {
    echo "<h3 style='color: green;'>🎉 msticker 품목에 파일 업로드 기능을 적용할 준비가 완료되었습니다!</h3>";
    echo "<p><strong>다음 단계:</strong> 실제 add_to_basket.php와 index.php 파일을 수정하여 파일 업로드 기능을 적용하세요.</p>";
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

pre {
    overflow-x: auto;
    font-size: 12px;
    border-radius: 5px;
}
</style>