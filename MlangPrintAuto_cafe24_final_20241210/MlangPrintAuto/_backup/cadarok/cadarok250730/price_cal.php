<?php
// 카다록 가격 계산 (원래 iframe 방식)
include "inc.php";
include "../../db_xampp.php";

$TABLE = "MlangPrintAuto_cadarok";
include "../ConDb.php";

// 디버깅을 위한 에러 표시 활성화
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 카다록 시스템 전용 파라미터 처리 (GET 방식)
$ordertype = $_GET['ordertype'] ?? 'print';  // 카다록은 인쇄만 의뢰만 있음
$MY_type = $_GET['MY_type'] ?? '';           // 구분
$PN_type = $_GET['PN_type'] ?? '';           // 종이종류  
$MY_Fsd = $_GET['MY_Fsd'] ?? '';             // 규격
$MY_amount = $_GET['MY_amount'] ?? '';       // 수량

// 카다록은 단면/양면이 없으므로 기본값 설정
$POtype = '1';  // 카다록은 항상 단면으로 처리

// 브라우저 값을 데이터베이스 값으로 매핑하는 함수
function mapCadarokBrowserToDatabase($browser_value, $type) {
    switch ($type) {
        case 'style':
            // 카다록은 항상 style=691 고정
            return '691';
            
        case 'section':
            // transactionCate의 no 값을 cadarok의 Section 값으로 변환
            // 예: 69361 → 693, 69461 → 694
            if (strlen($browser_value) > 3) {
                return substr($browser_value, 0, 3);
            }
            return $browser_value;
            
        case 'quantity':
            // 수량은 그대로 사용
            return $browser_value;
            
        case 'treeselect':
            // transactionCate의 no 값을 cadarok의 TreeSelect 값으로 변환
            // 예: 69961 → 699, 70061 → 700, 70161 → 701
            if (strlen($browser_value) > 3) {
                return substr($browser_value, 0, 3);
            }
            return $browser_value;
            
        default:
            return $browser_value;
    }
}

echo "<script>";
echo "console.log('=== 카다록 가격 계산 시작 ===');";
echo "console.log('파라미터 확인:');";
echo "console.log('ordertype: $ordertype');";
echo "console.log('MY_type: $MY_type');";
echo "console.log('PN_type: $PN_type');";
echo "console.log('MY_Fsd: $MY_Fsd');";
echo "console.log('MY_amount: $MY_amount');";
echo "console.log('POtype: $POtype (카다록은 항상 단면)');";

// 데이터베이스 연결 상태 확인
if ($db) {
    echo "console.log('✅ 데이터베이스 연결 성공');";
} else {
    echo "console.error('❌ 데이터베이스 연결 실패');";
}

// 가격 계산 로직 (카다록 전용)
if ($MY_type && $PN_type && $MY_Fsd && $MY_amount) {
    // 브라우저 값을 DB 값으로 매핑
    $mapped_style = '691'; // 카다록은 항상 691
    $mapped_section = mapCadarokBrowserToDatabase($MY_Fsd, 'section');
    $mapped_quantity = mapCadarokBrowserToDatabase($MY_amount, 'quantity');
    $mapped_treeselect = mapCadarokBrowserToDatabase($PN_type, 'treeselect');
    
    echo "console.log('매핑된 값들:');";
    echo "console.log('style: $mapped_style');";
    echo "console.log('section: $mapped_section');";
    echo "console.log('quantity: $mapped_quantity');";
    echo "console.log('treeselect: $mapped_treeselect');";
    
    // 카다록 가격 계산 쿼리
    $price_query = "SELECT * FROM $TABLE WHERE 
                    style='$mapped_style' AND 
                    Section='$mapped_section' AND 
                    quantity='$mapped_quantity' AND 
                    TreeSelect='$mapped_treeselect'";
    
    echo "console.log('카다록 가격 쿼리: $price_query');";
    
    $price_result = mysqli_query($db, $price_query);
    
    if ($price_result && mysqli_num_rows($price_result) > 0) {
        $price_row = mysqli_fetch_array($price_result);
        
        // 카다록 가격 정보
        $print_price = $price_row['money'] ?? 0;
        $design_price = ($ordertype == 'total') ? ($price_row['DesignMoney'] ?? 0) : 0;
        $subtotal = $print_price + $design_price;
        $vat = round($subtotal * 0.1);
        $total = $subtotal + $vat;
        
        // 부모 창의 폼 필드 업데이트
        echo "if (parent.document.choiceForm) {";
        echo "  parent.document.choiceForm.Price.value = '" . number_format($print_price) . "';";
        echo "  parent.document.choiceForm.Order_Price.value = '" . number_format($total) . "';";
        
        // 숨겨진 폼 필드들 업데이트
        echo "  parent.document.choiceForm.StyleForm.value = '카다록';";
        echo "  parent.document.choiceForm.SectionForm.value = parent.document.choiceForm.MY_type.options[parent.document.choiceForm.MY_type.selectedIndex].text;";
        echo "  parent.document.choiceForm.QuantityForm.value = parent.document.choiceForm.MY_amount.options[parent.document.choiceForm.MY_amount.selectedIndex].text;";
        echo "  parent.document.choiceForm.DesignForm.value = parent.document.choiceForm.ordertype.options[parent.document.choiceForm.ordertype.selectedIndex].text;";
        
        echo "  parent.document.choiceForm.PriceForm.value = '$print_price';";
        echo "  parent.document.choiceForm.DS_PriceForm.value = '$design_price';";
        echo "  parent.document.choiceForm.Order_PriceForm.value = '$subtotal';";
        echo "  parent.document.choiceForm.VAT_PriceForm.value = '$vat';";
        echo "  parent.document.choiceForm.Total_PriceForm.value = '$total';";
        echo "}";
        
        echo "console.log('카다록 가격 계산 완료');";
        echo "console.log('인쇄비: " . number_format($print_price) . "원');";
        echo "console.log('디자인비: " . number_format($design_price) . "원');";
        echo "console.log('총액: " . number_format($total) . "원');";
    } else {
        echo "console.error('카다록 가격 정보를 찾을 수 없습니다.');";
        echo "console.log('쿼리: $price_query');";
        
        // 디버깅을 위해 테이블 구조 확인
        echo "console.log('테이블 구조 확인 중...');";
        $debug_query = "SELECT * FROM $TABLE LIMIT 5";
        $debug_result = mysqli_query($db, $debug_query);
        if ($debug_result) {
            echo "console.log('테이블 샘플 데이터:');";
            while ($debug_row = mysqli_fetch_array($debug_result)) {
                echo "console.log('style: " . $debug_row['style'] . ", Section: " . $debug_row['Section'] . ", quantity: " . $debug_row['quantity'] . ", TreeSelect: " . $debug_row['TreeSelect'] . "');";
            }
        }
    }
} else {
    echo "console.error('카다록 가격 계산에 필요한 파라미터가 부족합니다.');";
    echo "console.log('MY_type: $MY_type, PN_type: $PN_type, MY_Fsd: $MY_Fsd, MY_amount: $MY_amount');";
}

echo "</script>";
?>