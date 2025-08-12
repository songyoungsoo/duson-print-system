<?php
/**
 * 🔍 실제 데이터베이스 구조 상세 분석
 */

include "db.php";

if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h1>🔍 실제 데이터베이스 구조 상세 분석</h1>";

// 실제 테이블명들 (소문자)
$tables = [
    'mlangprintauto_littleprint',  // 포스터
    'mlangprintauto_inserted',     // 전단지  
    'mlangprintauto_namecard',     // 명함
    'mlangprintauto_merchandisebond', // 쿠폰
    'mlangprintauto_envelope',     // 봉투
    'mlangprintauto_ncrflambeau',  // 양식지
    'mlangprintauto_msticker',     // 자석스티커
    'mlangprintauto_cadarok',      // 카다록
    'mlangprintauto_transactioncate' // 카테고리
];

foreach ($tables as $table) {
    echo "<h2>🗂️ {$table} 테이블 분석</h2>";
    
    // 테이블 존재 여부 확인
    $check_query = "SHOW TABLES LIKE '{$table}'";
    $check_result = mysqli_query($db, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        echo "<p style='color: red;'>❌ 테이블이 존재하지 않습니다.</p>";
        continue;
    }
    
    // 테이블 구조 조회
    $structure_query = "DESCRIBE {$table}";
    $structure_result = mysqli_query($db, $structure_query);
    
    if ($structure_result) {
        echo "<h3>📋 테이블 구조</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>기본값</th>";
        echo "</tr>";
        
        $fields = [];
        while ($field = mysqli_fetch_assoc($structure_result)) {
            $fields[] = $field['Field'];
            echo "<tr>";
            echo "<td><strong>{$field['Field']}</strong></td>";
            echo "<td>{$field['Type']}</td>";
            echo "<td>{$field['Null']}</td>";
            echo "<td>{$field['Key']}</td>";
            echo "<td>{$field['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 스마트 컴포넌트 관련 필드 존재 여부 확인
        $smart_fields = ['MY_type', 'PN_type', 'MY_Fsd', 'POtype', 'MY_amount', 'ordertype'];
        echo "<h3>🎯 스마트 컴포넌트 필드 매핑 상태</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>스마트 필드</th><th>존재 여부</th><th>실제 필드값 샘플</th>";
        echo "</tr>";
        
        foreach ($smart_fields as $field) {
            $exists = in_array($field, $fields);
            echo "<tr>";
            echo "<td><strong>{$field}</strong></td>";
            echo "<td>" . ($exists ? "✅ 존재" : "❌ 없음") . "</td>";
            
            if ($exists) {
                // 실제 필드값 샘플 조회
                $sample_query = "SELECT DISTINCT {$field} FROM {$table} WHERE {$field} IS NOT NULL AND {$field} != '' LIMIT 5";
                $sample_result = mysqli_query($db, $sample_query);
                
                $samples = [];
                if ($sample_result) {
                    while ($row = mysqli_fetch_assoc($sample_result)) {
                        $samples[] = $row[$field];
                    }
                }
                echo "<td>" . implode(', ', $samples) . "</td>";
            } else {
                echo "<td>-</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // 데이터 행 수 확인
        $count_query = "SELECT COUNT(*) as total FROM {$table}";
        $count_result = mysqli_query($db, $count_query);
        if ($count_result) {
            $count_row = mysqli_fetch_assoc($count_result);
            echo "<p><strong>📊 총 데이터 행 수:</strong> {$count_row['total']}개</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ 테이블 구조 조회 실패: " . mysqli_error($db) . "</p>";
    }
    
    echo "<hr>";
}

// transactioncate 테이블 특별 분석 (카테고리 정보)
echo "<h2>🎯 mlangprintauto_transactioncate 카테고리 분석</h2>";
$cate_query = "SELECT page, CV_title, COUNT(*) as count FROM mlangprintauto_transactioncate GROUP BY page, CV_title ORDER BY page, count DESC";
$cate_result = mysqli_query($db, $cate_query);

if ($cate_result) {
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>페이지</th><th>카테고리 제목</th><th>개수</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($cate_result)) {
        echo "<tr>";
        echo "<td><strong>{$row['page']}</strong></td>";
        echo "<td>{$row['CV_title']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    margin: 20px;
    background-color: #f8f9fa;
}

h1, h2, h3 {
    color: #495057;
}

table {
    background-color: white;
    margin: 10px 0;
    width: 100%;
    max-width: 1000px;
}

th {
    background-color: #e9ecef !important;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

hr {
    margin: 30px 0;
}

p {
    margin: 10px 0;
}
</style>