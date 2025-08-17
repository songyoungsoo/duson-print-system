<?php
/**
 * 자석스티커 시스템용 동적 데이터베이스 구조 설계
 * mlangprintauto_msticker + mlangprintauto_transactioncate 활용
 */

include "../../db.php";
include "../../includes/functions.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>🧲 자석스티커 동적 데이터베이스 구조 설계</h2>";

// 1. 자석스티커 카테고리 데이터 설정
echo "<h3>1. 자석스티커 카테고리 구조 설계</h3>";

$msticker_categories = [
    // 메인 카테고리 (BigNo = 0)
    ['no' => 800, 'title' => '차량용 자석스티커', 'Ttable' => 'msticker', 'BigNo' => 0],
    ['no' => 801, 'title' => '종이자석 스티커', 'Ttable' => 'msticker', 'BigNo' => 0],
    ['no' => 802, 'title' => '전체자석 스티커', 'Ttable' => 'msticker', 'BigNo' => 0],
    
    // 차량용 자석스티커 규격 (BigNo = 800)
    ['no' => 8001, 'title' => '10cm x 10cm', 'Ttable' => 'msticker', 'BigNo' => 800],
    ['no' => 8002, 'title' => '15cm x 10cm', 'Ttable' => 'msticker', 'BigNo' => 800],
    ['no' => 8003, 'title' => '20cm x 10cm', 'Ttable' => 'msticker', 'BigNo' => 800],
    ['no' => 8004, 'title' => '30cm x 20cm', 'Ttable' => 'msticker', 'BigNo' => 800],
    
    // 종이자석 스티커 규격 (BigNo = 801)
    ['no' => 8011, 'title' => '5cm x 5cm', 'Ttable' => 'msticker', 'BigNo' => 801],
    ['no' => 8012, 'title' => '7cm x 5cm', 'Ttable' => 'msticker', 'BigNo' => 801],
    ['no' => 8013, 'title' => '10cm x 7cm', 'Ttable' => 'msticker', 'BigNo' => 801],
    ['no' => 8014, 'title' => '15cm x 10cm', 'Ttable' => 'msticker', 'BigNo' => 801],
    
    // 전체자석 스티커 규격 (BigNo = 802)
    ['no' => 8021, 'title' => '원형 5cm', 'Ttable' => 'msticker', 'BigNo' => 802],
    ['no' => 8022, 'title' => '원형 7cm', 'Ttable' => 'msticker', 'BigNo' => 802],
    ['no' => 8023, 'title' => '사각 10cm x 10cm', 'Ttable' => 'msticker', 'BigNo' => 802],
    ['no' => 8024, 'title' => '사각 15cm x 10cm', 'Ttable' => 'msticker', 'BigNo' => 802],
];

// 2. mlangprintauto_transactioncate 테이블에 데이터 삽입
echo "<h4>2-1. mlangprintauto_transactioncate 테이블 업데이트</h4>";

foreach ($msticker_categories as $category) {
    $check_query = "SELECT COUNT(*) as count FROM mlangprintauto_transactioncate WHERE no = {$category['no']}";
    $check_result = mysqli_query($db, $check_query);
    $exists = mysqli_fetch_assoc($check_result)['count'] > 0;
    
    if (!$exists) {
        $insert_query = "INSERT INTO mlangprintauto_transactioncate (no, title, Ttable, BigNo) 
                        VALUES ({$category['no']}, '{$category['title']}', '{$category['Ttable']}', {$category['BigNo']})";
        
        if (mysqli_query($db, $insert_query)) {
            echo "✅ 추가: {$category['title']} (no: {$category['no']})<br>";
        } else {
            echo "❌ 오류: {$category['title']} - " . mysqli_error($db) . "<br>";
        }
    } else {
        echo "ℹ️ 이미 존재: {$category['title']} (no: {$category['no']})<br>";
    }
}

// 3. 자석스티커 가격 데이터 구조 설계
echo "<h3>3. 자석스티커 가격 데이터 구조</h3>";

$msticker_prices = [
    // 차량용 자석스티커 (style: 800)
    // 10cm x 10cm (Section: 8001)
    ['style' => 800, 'Section' => 8001, 'quantity' => 50, 'money' => 15000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8001, 'quantity' => 100, 'money' => 25000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8001, 'quantity' => 200, 'money' => 45000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8001, 'quantity' => 500, 'money' => 100000, 'DesignMoney' => 10000],
    
    // 15cm x 10cm (Section: 8002) 
    ['style' => 800, 'Section' => 8002, 'quantity' => 50, 'money' => 18000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8002, 'quantity' => 100, 'money' => 32000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8002, 'quantity' => 200, 'money' => 58000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8002, 'quantity' => 500, 'money' => 130000, 'DesignMoney' => 10000],
    
    // 20cm x 10cm (Section: 8003)
    ['style' => 800, 'Section' => 8003, 'quantity' => 50, 'money' => 22000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8003, 'quantity' => 100, 'money' => 38000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8003, 'quantity' => 200, 'money' => 68000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8003, 'quantity' => 500, 'money' => 150000, 'DesignMoney' => 10000],
    
    // 30cm x 20cm (Section: 8004)
    ['style' => 800, 'Section' => 8004, 'quantity' => 20, 'money' => 25000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8004, 'quantity' => 50, 'money' => 55000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8004, 'quantity' => 100, 'money' => 95000, 'DesignMoney' => 10000],
    ['style' => 800, 'Section' => 8004, 'quantity' => 200, 'money' => 180000, 'DesignMoney' => 10000],
    
    // 종이자석 스티커 (style: 801)
    // 5cm x 5cm (Section: 8011)
    ['style' => 801, 'Section' => 8011, 'quantity' => 100, 'money' => 12000, 'DesignMoney' => 8000],
    ['style' => 801, 'Section' => 8011, 'quantity' => 200, 'money' => 20000, 'DesignMoney' => 8000],
    ['style' => 801, 'Section' => 8011, 'quantity' => 500, 'money' => 45000, 'DesignMoney' => 8000],
    ['style' => 801, 'Section' => 8011, 'quantity' => 1000, 'money' => 80000, 'DesignMoney' => 8000],
    
    // 7cm x 5cm (Section: 8012)
    ['style' => 801, 'Section' => 8012, 'quantity' => 100, 'money' => 15000, 'DesignMoney' => 8000],
    ['style' => 801, 'Section' => 8012, 'quantity' => 200, 'money' => 26000, 'DesignMoney' => 8000],
    ['style' => 801, 'Section' => 8012, 'quantity' => 500, 'money' => 58000, 'DesignMoney' => 8000],
    ['style' => 801, 'Section' => 8012, 'quantity' => 1000, 'money' => 105000, 'DesignMoney' => 8000],
    
    // 전체자석 스티커 (style: 802)
    // 원형 5cm (Section: 8021)
    ['style' => 802, 'Section' => 8021, 'quantity' => 50, 'money' => 18000, 'DesignMoney' => 12000],
    ['style' => 802, 'Section' => 8021, 'quantity' => 100, 'money' => 32000, 'DesignMoney' => 12000],
    ['style' => 802, 'Section' => 8021, 'quantity' => 200, 'money' => 58000, 'DesignMoney' => 12000],
    ['style' => 802, 'Section' => 8021, 'quantity' => 500, 'money' => 130000, 'DesignMoney' => 12000],
    
    // 원형 7cm (Section: 8022)
    ['style' => 802, 'Section' => 8022, 'quantity' => 50, 'money' => 22000, 'DesignMoney' => 12000],
    ['style' => 802, 'Section' => 8022, 'quantity' => 100, 'money' => 38000, 'DesignMoney' => 12000],
    ['style' => 802, 'Section' => 8022, 'quantity' => 200, 'money' => 68000, 'DesignMoney' => 12000],
    ['style' => 802, 'Section' => 8022, 'quantity' => 500, 'money' => 150000, 'DesignMoney' => 12000],
];

// 4. mlangprintauto_msticker 테이블에 데이터 삽입
echo "<h4>3-1. mlangprintauto_msticker 테이블 업데이트</h4>";

foreach ($msticker_prices as $price) {
    $check_query = "SELECT COUNT(*) as count FROM mlangprintauto_msticker 
                    WHERE style = '{$price['style']}' AND Section = '{$price['Section']}' 
                    AND quantity = {$price['quantity']}";
    $check_result = mysqli_query($db, $check_query);
    $exists = $check_result && mysqli_fetch_assoc($check_result)['count'] > 0;
    
    if (!$exists) {
        $insert_query = "INSERT INTO mlangprintauto_msticker (style, Section, quantity, money, DesignMoney) 
                        VALUES ('{$price['style']}', '{$price['Section']}', {$price['quantity']}, '{$price['money']}', '{$price['DesignMoney']}')";
        
        if (mysqli_query($db, $insert_query)) {
            echo "✅ 가격 추가: Style {$price['style']}, Section {$price['Section']}, {$price['quantity']}매 - {$price['money']}원 (편집비: {$price['DesignMoney']}원)<br>";
        } else {
            echo "❌ 가격 오류: " . mysqli_error($db) . "<br>";
        }
    } else {
        echo "ℹ️ 가격 존재: Style {$price['style']}, Section {$price['Section']}, {$price['quantity']}매<br>";
    }
}

// 5. 데이터 검증
echo "<h3>4. 데이터 검증</h3>";

// 카테고리 수 확인
$category_count_query = "SELECT COUNT(*) as count FROM mlangprintauto_transactioncate WHERE Ttable = 'msticker'";
$category_count_result = mysqli_query($db, $category_count_query);
$category_count = mysqli_fetch_assoc($category_count_result)['count'];
echo "📊 자석스티커 카테고리 총 개수: {$category_count}개<br>";

// 가격 데이터 수 확인
$price_count_query = "SELECT COUNT(*) as count FROM mlangprintauto_msticker";
$price_count_result = mysqli_query($db, $price_count_query);
$price_count = mysqli_fetch_assoc($price_count_result)['count'];
echo "📊 자석스티커 가격 데이터 총 개수: {$price_count}개<br>";

// 동적 구조 테스트
echo "<h3>5. 동적 구조 테스트</h3>";

// 메인 카테고리 테스트
echo "<h4>5-1. 메인 카테고리 (BigNo = 0)</h4>";
$main_categories_query = "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable = 'msticker' AND BigNo = 0 ORDER BY no";
$main_categories_result = mysqli_query($db, $main_categories_query);
while ($row = mysqli_fetch_assoc($main_categories_result)) {
    echo "🧲 {$row['title']} (no: {$row['no']})<br>";
    
    // 해당 카테고리의 하위 규격 표시
    $sub_categories_query = "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable = 'msticker' AND BigNo = {$row['no']} ORDER BY no";
    $sub_categories_result = mysqli_query($db, $sub_categories_query);
    while ($sub_row = mysqli_fetch_assoc($sub_categories_result)) {
        echo "&nbsp;&nbsp;├─ {$sub_row['title']} (no: {$sub_row['no']})<br>";
        
        // 해당 규격의 수량 옵션 표시
        $quantities_query = "SELECT DISTINCT quantity FROM mlangprintauto_msticker WHERE style = {$row['no']} AND Section = {$sub_row['no']} ORDER BY CAST(quantity AS UNSIGNED)";
        $quantities_result = mysqli_query($db, $quantities_query);
        $quantities = [];
        while ($qty_row = mysqli_fetch_assoc($quantities_result)) {
            $quantities[] = $qty_row['quantity'] . '매';
        }
        if (!empty($quantities)) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;└─ 수량: " . implode(', ', $quantities) . "<br>";
        }
    }
    echo "<br>";
}

mysqli_close($db);

echo "<h3>🎉 자석스티커 동적 데이터베이스 구조 설계 완료!</h3>";
echo "<p><strong>동적 구조 특징:</strong></p>";
echo "<ul>";
echo "<li>📋 <strong>3단계 계층</strong>: 종류 → 규격 → 수량</li>";
echo "<li>🔄 <strong>동적 드롭다운</strong>: 상위 선택에 따라 하위 옵션 자동 로드</li>";
echo "<li>💰 <strong>실시간 가격</strong>: 모든 옵션 선택 완료 시 즉시 가격 계산</li>";
echo "<li>🎯 <strong>확장 가능</strong>: 새로운 자석스티커 종류/규격 쉽게 추가 가능</li>";
echo "</ul>";

echo "<br><a href='index.php' style='background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧲 자석스티커 시스템 테스트하기</a>";
?>