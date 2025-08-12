<?php
/**
 * Envelope 가격 계산 API 테스트
 * 
 * calculate_envelope_price.php의 기능을 테스트합니다.
 */

// 기본 설정 로드
require_once 'bootstrap.php';

echo "<h2>Envelope 가격 계산 API 테스트</h2>";

// 테스트 데이터 준비 (실제 데이터베이스 데이터 기반)
$testCases = [
    [
        'name' => '정상 케이스 - 전체 주문 (소봉투)',
        'params' => [
            'MY_type' => '282',    // 소봉투 카테고리
            'PN_type' => '283',    // 소봉투(100모조 220*105)
            'MY_amount' => '1000',
            'POtype' => '1',       // 마스터1도
            'ordertype' => 'total'
        ]
    ],
    [
        'name' => '정상 케이스 - 인쇄만 (소봉투)',
        'params' => [
            'MY_type' => '282',
            'PN_type' => '283',
            'MY_amount' => '1000', 
            'POtype' => '2',       // 마스터2도
            'ordertype' => 'print'
        ]
    ],
    [
        'name' => '정상 케이스 - 디자인만 (소봉투)',
        'params' => [
            'MY_type' => '282',
            'PN_type' => '283',
            'MY_amount' => '1000',
            'POtype' => '3',       // 칼라4도
            'ordertype' => 'design'
        ]
    ],
    [
        'name' => '정상 케이스 - 대봉투 전체 주문',
        'params' => [
            'MY_type' => '466',    // 대봉투 카테고리
            'PN_type' => '473',    // 대봉투330*243(120g모조)
            'MY_amount' => '1000',
            'POtype' => '3',       // 칼라4도
            'ordertype' => 'total'
        ]
    ],
    [
        'name' => '에러 케이스 - 필수 파라미터 누락',
        'params' => [
            'MY_type' => '282',
            'PN_type' => '283'
            // MY_amount, POtype, ordertype 누락
        ]
    ],
    [
        'name' => '에러 케이스 - 잘못된 POtype',
        'params' => [
            'MY_type' => '282',
            'PN_type' => '283',
            'MY_amount' => '1000',
            'POtype' => '5',       // 잘못된 값 (1,2,3만 허용)
            'ordertype' => 'total'
        ]
    ],
    [
        'name' => '에러 케이스 - 존재하지 않는 카테고리',
        'params' => [
            'MY_type' => '999',    // 존재하지 않는 카테고리
            'PN_type' => '283',
            'MY_amount' => '1000',
            'POtype' => '1',
            'ordertype' => 'total'
        ]
    ]
];

// 컨트롤러 초기화
try {
    $controller = new AjaxController($db);
    
    foreach ($testCases as $index => $testCase) {
        echo "<h3>테스트 " . ($index + 1) . ": " . $testCase['name'] . "</h3>";
        echo "<strong>입력 파라미터:</strong><br>";
        echo "<pre>" . json_encode($testCase['params'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        
        try {
            // 가격 계산 실행
            $result = $controller->calculatePrice($testCase['params']);
            
            echo "<strong>결과:</strong><br>";
            echo "<pre style='background-color: #e8f5e8; padding: 10px;'>";
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo "</pre>";
            
        } catch (Exception $e) {
            echo "<strong>에러:</strong><br>";
            echo "<pre style='background-color: #ffe8e8; padding: 10px;'>";
            echo "에러 메시지: " . $e->getMessage();
            echo "</pre>";
        }
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #ffe8e8; padding: 15px; border: 1px solid #ff0000;'>";
    echo "<strong>초기화 에러:</strong> " . $e->getMessage();
    echo "</div>";
}

// 데이터베이스 상태 확인
echo "<h3>데이터베이스 상태 확인</h3>";

try {
    $dbManager = new DatabaseManager($db);
    
    // envelope 테이블 데이터 확인
    echo "<h4>Envelope 테이블 샘플 데이터:</h4>";
    $sampleData = $dbManager->executeQuery("SELECT * FROM " . ENVELOPE_TABLE . " LIMIT 5");
    
    if (!empty($sampleData)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($sampleData[0]) as $column) {
            echo "<th style='padding: 5px; background-color: #f0f0f0;'>{$column}</th>";
        }
        echo "</tr>";
        
        foreach ($sampleData as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>Envelope 테이블에 데이터가 없습니다.</p>";
    }
    
    // 카테고리 테이블 데이터 확인
    echo "<h4>카테고리 테이블 샘플 데이터 (envelope 관련):</h4>";
    $categoryData = $dbManager->executeQuery(
        "SELECT * FROM " . CATEGORY_TABLE . " WHERE Ttable = ? LIMIT 10", 
        [PRODUCT_TYPE]
    );
    
    if (!empty($categoryData)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($categoryData[0]) as $column) {
            echo "<th style='padding: 5px; background-color: #f0f0f0;'>{$column}</th>";
        }
        echo "</tr>";
        
        foreach ($categoryData as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>카테고리 테이블에 envelope 관련 데이터가 없습니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #ffe8e8; padding: 15px; border: 1px solid #ff0000;'>";
    echo "<strong>데이터베이스 확인 에러:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<h3>테스트 완료</h3>";
echo "<p>위 결과를 확인하여 API가 올바르게 작동하는지 검증하세요.</p>";
?>