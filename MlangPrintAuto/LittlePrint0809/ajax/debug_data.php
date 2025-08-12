<?php
/**
 * LittlePrint 데이터베이스 데이터 확인용 디버그 파일
 */

// 개발 모드 활성화
define('DEVELOPMENT_MODE', true);

// 부트스트랩 로드
require_once __DIR__ . '/bootstrap.php';

try {
    $db = createDatabaseManager();
    
    echo "<h1>LittlePrint 데이터베이스 데이터 확인</h1>";
    
    // 1. 메인 카테고리 확인
    echo "<h2>1. 메인 카테고리 (종류)</h2>";
    $categories = $db->getMainCategories();
    echo "<pre>" . json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";
    
    if (!empty($categories)) {
        $firstCategoryId = $categories[0]['id'];
        
        // 2. 종이종류 확인
        echo "<h2>2. 종이종류 (카테고리 ID: {$firstCategoryId})</h2>";
        $paperTypes = $db->getPaperTypesByCategory($firstCategoryId);
        echo "<pre>" . json_encode($paperTypes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";
        
        // 3. 종이규격 확인
        echo "<h2>3. 종이규격 (카테고리 ID: {$firstCategoryId})</h2>";
        $paperSizes = $db->getPaperSizesByCategory($firstCategoryId);
        echo "<pre>" . json_encode($paperSizes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";
        
        // 4. 가격 테이블 샘플 데이터 확인
        echo "<h2>4. 가격 테이블 샘플 데이터</h2>";
        $query = "SELECT * FROM " . PRICE_TABLE . " LIMIT 5";
        $result = $db->executeQuery($query);
        $priceData = [];
        while ($row = $result->fetch_assoc()) {
            $priceData[] = $row;
        }
        echo "<pre>" . json_encode($priceData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";
        
        // 5. 실제 존재하는 조합 찾기
        if (!empty($paperTypes) && !empty($paperSizes)) {
            $testParams = [
                'style' => $firstCategoryId,
                'section' => $paperSizes[0]['id'],
                'treeSelect' => $paperTypes[0]['id'],
                'quantity' => 100,
                'poType' => 2
            ];
            
            echo "<h2>5. 테스트 파라미터로 가격 조회</h2>";
            echo "<p>테스트 파라미터: " . json_encode($testParams, JSON_UNESCAPED_UNICODE) . "</p>";
            
            try {
                $priceResult = $db->getPriceData($testParams);
                if ($priceResult) {
                    echo "<p style='color: green;'>✅ 가격 데이터 찾음!</p>";
                    echo "<pre>" . json_encode($priceResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";
                    
                    // 올바른 테스트 URL 생성
                    $testUrl = "calculate_price.php?" . http_build_query([
                        'MY_type' => $testParams['style'],
                        'MY_Fsd' => $testParams['treeSelect'],
                        'PN_type' => $testParams['section'],
                        'MY_amount' => $testParams['quantity'],
                        'POtype' => $testParams['poType'],
                        'ordertype' => 'total'
                    ]);
                    
                    echo "<h3>올바른 테스트 URL:</h3>";
                    echo "<p><a href='{$testUrl}' target='_blank'>{$testUrl}</a></p>";
                    
                } else {
                    echo "<p style='color: red;'>❌ 가격 데이터 없음</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ 가격 조회 오류: " . $e->getMessage() . "</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>오류: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>