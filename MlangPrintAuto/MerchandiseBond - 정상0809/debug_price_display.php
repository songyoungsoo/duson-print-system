<?php
// 쿠폰 시스템 가격 표시 디버깅 도구
include "../../db_auto.php";

echo "<h2>쿠폰 시스템 가격 표시 디버깅</h2>";

// 1. 데이터베이스 연결 확인
echo "<h3>1. 데이터베이스 연결 상태</h3>";
if ($db) {
    echo "✅ 데이터베이스 연결 성공<br>";
} else {
    echo "❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "<br>";
}

// 2. 테스트 가격 계산
echo "<h3>2. 테스트 가격 계산</h3>";
$test_params = [
    'MY_type' => '61461',  // 첫 번째 상품권 종류
    'MY_amount' => '1000', // 1000매
    'POtype' => '1',       // 단면
    'ordertype' => 'total' // 디자인+인쇄
];

echo "테스트 파라미터:<br>";
foreach ($test_params as $key => $value) {
    echo "- $key: $value<br>";
}

// 매핑 함수
function mapBrowserToDatabase($browser_value, $type) {
    switch ($type) {
        case 'style':
            if (strlen($browser_value) > 3) {
                return substr($browser_value, 0, 3);
            }
            return $browser_value;
        default:
            return $browser_value;
    }
}

$mapped_style = mapBrowserToDatabase($test_params['MY_type'], 'style');
echo "<br>매핑된 style 값: {$test_params['MY_type']} → $mapped_style<br>";

// 데이터베이스 쿼리 실행
$TABLE = "MlangPrintAuto_MerchandiseBond";
$query = "SELECT * FROM $TABLE WHERE style=? AND quantity=? AND POtype=? LIMIT 1";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'sss', $mapped_style, $test_params['MY_amount'], $test_params['POtype']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo "<br>실행된 쿼리: $query<br>";
echo "바인딩된 값: style=$mapped_style, quantity={$test_params['MY_amount']}, POtype={$test_params['POtype']}<br>";

$row = mysqli_fetch_array($result);

if ($row) {
    echo "<br>✅ 데이터베이스에서 가격 정보 찾음:<br>";
    echo "- 인쇄비 (money): " . number_format($row['money']) . "원<br>";
    echo "- 디자인비 (DesignMoney): " . number_format($row['DesignMoney'] ?? 0) . "원<br>";
    
    // 가격 계산
    $Price = $row['money'];
    $DesignMoneyOk = $row['DesignMoney'] ?? 0;
    $Order_PricOk = $Price + $DesignMoneyOk;
    $VAT_PriceOk = $Order_PricOk / 10;
    $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;
    
    echo "<br><strong>계산된 가격:</strong><br>";
    echo "- 인쇄비: " . number_format($Price) . "원<br>";
    echo "- 디자인비: " . number_format($DesignMoneyOk) . "원<br>";
    echo "- 합계: " . number_format($Order_PricOk) . "원<br>";
    echo "- 부가세: " . number_format($VAT_PriceOk) . "원<br>";
    echo "- 총액: " . number_format($Total_PriceOk) . "원<br>";
    
} else {
    echo "<br>❌ 데이터베이스에서 가격 정보를 찾을 수 없음<br>";
    
    // 사용 가능한 데이터 확인
    echo "<br><strong>사용 가능한 데이터 확인:</strong><br>";
    $check_query = "SELECT DISTINCT style, quantity, POtype FROM $TABLE ORDER BY style, quantity, POtype LIMIT 10";
    $check_result = mysqli_query($db, $check_query);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Style</th><th>Quantity</th><th>POtype</th></tr>";
    while ($check_row = mysqli_fetch_array($check_result)) {
        echo "<tr>";
        echo "<td>{$check_row['style']}</td>";
        echo "<td>{$check_row['quantity']}</td>";
        echo "<td>{$check_row['POtype']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. AJAX 응답 시뮬레이션
echo "<h3>3. AJAX 응답 시뮬레이션</h3>";
if ($row) {
    $response_data = [
        'success' => true,
        'data' => [
            'Price' => number_format($Price),
            'DS_Price' => number_format($DesignMoneyOk),
            'Order_Price' => number_format($Order_PricOk),
            'PriceForm' => $Price,
            'DS_PriceForm' => $DesignMoneyOk,
            'Order_PriceForm' => $Order_PricOk,
            'VAT_PriceForm' => $VAT_PriceOk,
            'Total_PriceForm' => $Total_PriceOk,
            'StyleForm' => '상품권',
            'SectionForm' => $test_params['PN_type'] ?? '',
            'QuantityForm' => $test_params['MY_amount'],
            'DesignForm' => '디자인+인쇄'
        ]
    ];
    
    echo "AJAX 응답 데이터:<br>";
    echo "<pre>" . json_encode($response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} else {
    echo "❌ 가격 정보가 없어서 AJAX 응답을 생성할 수 없음<br>";
}

// 4. JavaScript 필드명 확인
echo "<h3>4. JavaScript에서 찾는 필드명들</h3>";
echo "JavaScript에서 업데이트하려는 필드들:<br>";
echo "- form.Price (표시용)<br>";
echo "- form.DS_Price (표시용)<br>";
echo "- form.Order_Price (표시용)<br>";
echo "- form.PriceForm (숨겨진 필드)<br>";
echo "- form.DS_PriceForm (숨겨진 필드)<br>";
echo "- form.Order_PriceForm (숨겨진 필드)<br>";

mysqli_close($db);
?>

<script>
// 5. 실제 폼 필드 존재 여부 확인
window.onload = function() {
    console.log("=== 폼 필드 존재 여부 확인 ===");
    
    // 상위 페이지의 폼에 접근 시도
    try {
        if (window.parent && window.parent.document) {
            var parentForm = window.parent.document.forms["choiceForm"];
            if (parentForm) {
                console.log("✅ 상위 페이지에서 choiceForm 찾음");
                
                var fields = ['Price', 'DS_Price', 'Order_Price', 'PriceForm', 'DS_PriceForm', 'Order_PriceForm'];
                fields.forEach(function(fieldName) {
                    var field = parentForm[fieldName];
                    if (field) {
                        console.log("✅ " + fieldName + " 필드 존재:", field);
                        console.log("   현재 값:", field.value);
                    } else {
                        console.log("❌ " + fieldName + " 필드 없음");
                    }
                });
            } else {
                console.log("❌ 상위 페이지에서 choiceForm을 찾을 수 없음");
            }
        }
    } catch (e) {
        console.log("❌ 상위 페이지 접근 오류:", e.message);
    }
    
    // 현재 페이지에서도 확인
    var currentForm = document.forms["choiceForm"];
    if (currentForm) {
        console.log("✅ 현재 페이지에서 choiceForm 찾음");
    } else {
        console.log("❌ 현재 페이지에서 choiceForm 없음");
    }
};
</script>