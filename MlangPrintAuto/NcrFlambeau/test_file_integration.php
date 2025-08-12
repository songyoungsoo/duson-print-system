<?php
/**
 * ncrflambeau 품목 파일 업로드 통합 테스트
 * 경로: MlangPrintAuto/ncrflambeau/test_file_integration.php
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";
include "../shop/file_management_helper.php";

echo "<h1>🧪 ncrflambeau 파일 업로드 통합 테스트</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>";

$test_results = [];

// 1. 파일 관리 헬퍼 함수 접근 테스트
echo "<h2>1️⃣ 파일 관리 함수 접근 테스트</h2>";
try {
    $log_info = generateFileLogInfo('ncrflambeau');
    echo "<p style='color: green;'>✅ generateFileLogInfo() 함수 접근 성공</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    print_r($log_info);
    echo "</pre>";
    $test_results['helper_access'] = true;
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 파일 관리 함수 접근 실패: " . $e->getMessage() . "</p>";
    $test_results['helper_access'] = false;
}

// 2. 테스트 장바구니 아이템 생성 및 파일 연동
echo "<h2>2️⃣ 장바구니-파일 연동 테스트</h2>";
try {
    $session_id = session_id();
    
    // ncrflambeau 테스트 데이터
    $test_data = [
        'MY_type' => '475',      // 양식(100매철)
        'MY_Fsd' => '484',       // 계약서(A4)
        'PN_type' => '505',      // 1도
        'MY_amount' => '60',     // 60권
        'ordertype' => 'design', // 디자인+인쇄
        'st_price' => 140000,
        'st_price_vat' => 154000,
        'MY_comment' => 'ncrflambeau 파일 업로드 테스트'
    ];
    
    $insert_query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, 
        ordertype, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'ncrflambeau', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($db, $insert_query);
    $regdate = time();
    
    mysqli_stmt_bind_param($stmt, 'ssssssddsi', 
        $session_id, $test_data['MY_type'], $test_data['MY_Fsd'], 
        $test_data['PN_type'], $test_data['MY_amount'], $test_data['ordertype'],
        $test_data['st_price'], $test_data['st_price_vat'], 
        $test_data['MY_comment'], $regdate
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $cart_item_no = mysqli_insert_id($db);
        echo "<p style='color: green;'>✅ 테스트 장바구니 아이템 생성 성공 (ID: $cart_item_no)</p>";
        
        // 파일 정보 추가 테스트
        $test_file_info = [
            'original_name' => 'ncrflambeau_form.pdf',
            'saved_name' => 'ncrflambeau_' . time() . '_form.pdf',
            'file_size' => 1024000,
            'file_type' => 'application/pdf',
            'upload_path' => createFileUploadDirectory($log_info) . '/ncrflambeau_form.pdf'
        ];
        
        if (addFileToCartItem($db, $cart_item_no, $test_file_info, $log_info)) {
            echo "<p style='color: green;'>✅ 파일 정보 추가 성공</p>";
            
            $files = getCartItemFiles($db, $cart_item_no);
            if (!empty($files)) {
                echo "<p style='color: green;'>✅ 파일 정보 조회 성공</p>";
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

// 테스트 결과
$total_tests = count($test_results);
$passed_tests = array_sum($test_results);
$success_rate = $total_tests > 0 ? ($passed_tests / $total_tests) * 100 : 0;

echo "<h2>📊 테스트 결과</h2>";
echo "<div style='background: " . ($success_rate == 100 ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 5px;'>";
echo "<h3>성공률: " . number_format($success_rate, 1) . "%</h3>";

if ($success_rate == 100) {
    echo "<p style='color: green;'>🎉 ncrflambeau 품목에 파일 업로드 기능 적용 준비 완료!</p>";
} else {
    echo "<p style='color: red;'>⚠️ 일부 테스트 실패. 문제 해결 필요.</p>";
}

echo "</div>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f5f5f5; }
h1, h2 { color: #333; }
pre { overflow-x: auto; font-size: 12px; border-radius: 5px; }
</style>