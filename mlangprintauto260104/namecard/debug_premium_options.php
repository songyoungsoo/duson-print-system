<?php
/**
 * 프리미엄 옵션 데이터 흐름 디버깅 스크립트
 * index_01.php → 장바구니 → 주문까지 전체 과정 검증
 */
session_start();
include "../../db.php";
include "../../includes/functions.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>🔍 명함 프리미엄 옵션 디버깅</h2>";

// 1. 세션 정보 확인
echo "<h3>1. 세션 정보</h3>";
echo "세션 ID: " . session_id() . "<br>";

// 2. shop_temp 테이블 구조 확인
echo "<h3>2. shop_temp 테이블 구조</h3>";
$columns_query = "SHOW COLUMNS FROM shop_temp";
$columns_result = mysqli_query($db, $columns_query);

$has_premium_options = false;
$has_premium_total = false;

while ($column = mysqli_fetch_assoc($columns_result)) {
    echo "컬럼: {$column['Field']} - {$column['Type']}<br>";
    if ($column['Field'] == 'premium_options') $has_premium_options = true;
    if ($column['Field'] == 'premium_options_total') $has_premium_total = true;
}

if (!$has_premium_options) {
    echo "<div style='color: red; font-weight: bold;'>❌ premium_options 컬럼이 없습니다!</div>";
    
    // 컬럼 추가
    $add_premium_options = "ALTER TABLE shop_temp ADD COLUMN premium_options TEXT";
    if (mysqli_query($db, $add_premium_options)) {
        echo "<div style='color: green;'>✅ premium_options 컬럼 추가 완료</div>";
        $has_premium_options = true;
    } else {
        echo "<div style='color: red;'>❌ premium_options 컬럼 추가 실패: " . mysqli_error($db) . "</div>";
    }
}

if (!$has_premium_total) {
    echo "<div style='color: red; font-weight: bold;'>❌ premium_options_total 컬럼이 없습니다!</div>";
    
    // 컬럼 추가
    $add_premium_total = "ALTER TABLE shop_temp ADD COLUMN premium_options_total INT(11) DEFAULT 0";
    if (mysqli_query($db, $add_premium_total)) {
        echo "<div style='color: green;'>✅ premium_options_total 컬럼 추가 완료</div>";
        $has_premium_total = true;
    } else {
        echo "<div style='color: red;'>❌ premium_options_total 컬럼 추가 실패: " . mysqli_error($db) . "</div>";
    }
}

if ($has_premium_options && $has_premium_total) {
    echo "<div style='color: green; font-weight: bold;'>✅ 프리미엄 옵션 컬럼이 모두 존재합니다!</div>";
}

// 3. 현재 장바구니 데이터 확인
echo "<h3>3. 현재 장바구니 데이터</h3>";
$session_id = session_id();
$cart_query = "SELECT no, product_type, MY_type, Section, MY_amount, premium_options, premium_options_total 
              FROM shop_temp WHERE session_id = ?";
$stmt = mysqli_prepare($db, $cart_query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>No</th><th>상품타입</th><th>타입</th><th>재질</th><th>수량</th><th>프리미엄 옵션</th><th>옵션 총액</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['no']}</td>";
            echo "<td>{$row['product_type']}</td>";
            echo "<td>{$row['MY_type']}</td>";
            echo "<td>{$row['Section']}</td>";
            echo "<td>{$row['MY_amount']}</td>";
            
            if (!empty($row['premium_options'])) {
                $options = json_decode($row['premium_options'], true);
                echo "<td style='max-width: 300px; word-break: break-all;'>";
                if ($options) {
                    foreach ($options as $key => $value) {
                        if ($value && $key != 'premium_options_total') {
                            echo "<strong>$key:</strong> $value<br>";
                        }
                    }
                } else {
                    echo "JSON 파싱 오류";
                }
                echo "</td>";
            } else {
                echo "<td style='color: red;'>프리미엄 옵션 없음</td>";
            }
            
            echo "<td>" . number_format($row['premium_options_total']) . "원</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='color: orange;'>현재 세션에 장바구니 데이터가 없습니다.</div>";
    }
    mysqli_stmt_close($stmt);
}

// 4. 테스트용 프리미엄 옵션 데이터 삽입
echo "<h3>4. 테스트 데이터 삽입</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='insert_test_data'>테스트 데이터 삽입</button>";
echo "</form>";

if (isset($_POST['insert_test_data'])) {
    $test_premium_options = [
        'foil_enabled' => 1,
        'foil_type' => 'gold_matte',
        'foil_price' => 30000,
        'numbering_enabled' => 1,
        'numbering_type' => 'single',
        'numbering_price' => 60000,
        'premium_options_total' => 90000
    ];
    
    $test_options_json = json_encode($test_premium_options, JSON_UNESCAPED_UNICODE);
    
    $insert_test = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype, st_price, st_price_vat, premium_options, premium_options_total)
                   VALUES (?, 'namecard', '1', '1', '1', '500', 'print', 50000, 55000, ?, 90000)";
    
    $stmt = mysqli_prepare($db, $insert_test);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $session_id, $test_options_json);
        if (mysqli_stmt_execute($stmt)) {
            echo "<div style='color: green;'>✅ 테스트 데이터 삽입 완료!</div>";
            echo "<div><a href='/shop/cart.php' target='_blank'>장바구니에서 확인하기 →</a></div>";
        } else {
            echo "<div style='color: red;'>❌ 테스트 데이터 삽입 실패: " . mysqli_stmt_error($stmt) . "</div>";
        }
        mysqli_stmt_close($stmt);
    }
}

// 5. JavaScript 테스트
echo "<h3>5. JavaScript 함수 테스트</h3>";
echo "<div id='jsTest'>JavaScript 함수를 테스트합니다...</div>";

?>

<script>
// index_01.php의 프리미엄 옵션 JavaScript 테스트
window.addEventListener('DOMContentLoaded', function() {
    const testDiv = document.getElementById('jsTest');
    
    // 테스트용 가상 데이터
    const testData = {
        foil_enabled: true,
        foil_type: 'gold_matte',
        foil_price: 30000,
        numbering_enabled: true,
        numbering_type: 'single', 
        numbering_price: 60000,
        premium_options_total: 90000
    };
    
    let html = '<h4>JavaScript 테스트 결과:</h4>';
    html += '<ul>';
    
    // FormData 생성 테스트
    try {
        const formData = new FormData();
        Object.keys(testData).forEach(key => {
            formData.append(key, testData[key]);
        });
        html += '<li style="color: green;">✅ FormData 생성 성공</li>';
        
        // FormData 내용 확인
        html += '<li>FormData 내용:<ul>';
        for (let [key, value] of formData.entries()) {
            html += `<li>${key}: ${value}</li>`;
        }
        html += '</ul></li>';
        
    } catch (error) {
        html += '<li style="color: red;">❌ FormData 생성 실패: ' + error.message + '</li>';
    }
    
    // JSON 변환 테스트  
    try {
        const jsonData = JSON.stringify(testData);
        html += '<li style="color: green;">✅ JSON 변환 성공: ' + jsonData + '</li>';
    } catch (error) {
        html += '<li style="color: red;">❌ JSON 변환 실패: ' + error.message + '</li>';
    }
    
    html += '</ul>';
    testDiv.innerHTML = html;
});
</script>

<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
h2, h3 { color: #333; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
</style>