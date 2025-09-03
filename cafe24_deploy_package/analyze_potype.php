<?php
/**
 * 🔍 POtype 값들의 실제 DB 저장 현황 분석
 */

include "db.php";

if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h1>🔍 POtype 값들의 실제 DB 저장 현황 분석</h1>";

// 각 제품 테이블의 POtype 값들 확인
$tables = [
    'mlangprintauto_littleprint' => '포스터',
    'mlangprintauto_inserted' => '전단지',  
    'mlangprintauto_namecard' => '명함',
    'mlangprintauto_merchandisebond' => '쿠폰',
    'mlangprintauto_envelope' => '봉투',
    'mlangprintauto_ncrflambeau' => '양식지',
    'mlangprintauto_cadarok' => '카다록'
];

foreach ($tables as $table => $product_name) {
    echo "<h2>📋 {$product_name} ({$table})</h2>";
    
    // POtype 필드가 있는지 확인
    $check_field = "SHOW COLUMNS FROM {$table} LIKE 'POtype'";
    $field_result = mysqli_query($db, $check_field);
    
    if (mysqli_num_rows($field_result) > 0) {
        // POtype 값들과 해당하는 transactioncate 제목 확인
        $potype_query = "SELECT DISTINCT 
                            t.POtype,
                            COUNT(*) as count,
                            tc.title as transaction_title
                         FROM {$table} t
                         LEFT JOIN mlangprintauto_transactioncate tc ON tc.no = t.POtype
                         WHERE t.POtype IS NOT NULL AND t.POtype != ''
                         GROUP BY t.POtype, tc.title
                         ORDER BY t.POtype";
        
        $potype_result = mysqli_query($db, $potype_query);
        
        if ($potype_result && mysqli_num_rows($potype_result) > 0) {
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>POtype 값</th><th>개수</th><th>transactioncate 제목</th><th>추정 의미</th>";
            echo "</tr>";
            
            while ($row = mysqli_fetch_assoc($potype_result)) {
                // 추정 의미 분석
                $estimated_meaning = '';
                if ($row['transaction_title']) {
                    $estimated_meaning = $row['transaction_title'];
                } else {
                    // 제목이 없으면 일반적인 패턴으로 추정
                    switch ($row['POtype']) {
                        case '1':
                            $estimated_meaning = ($product_name == '포스터' || $product_name == '전단지' || $product_name == '명함') ? '단면' : '1도/기본';
                            break;
                        case '2':
                            $estimated_meaning = ($product_name == '포스터' || $product_name == '전단지' || $product_name == '명함') ? '양면' : '2도/추가';
                            break;
                        case '3':
                            $estimated_meaning = '3도/특수';
                            break;
                        case '4':
                            $estimated_meaning = '4도/컬러';
                            break;
                        default:
                            $estimated_meaning = '기타';
                    }
                }
                
                echo "<tr>";
                echo "<td><strong>{$row['POtype']}</strong></td>";
                echo "<td>{$row['count']}</td>";
                echo "<td>" . ($row['transaction_title'] ? $row['transaction_title'] : '-') . "</td>";
                echo "<td>{$estimated_meaning}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>POtype 데이터가 없습니다.</p>";
        }
    } else {
        echo "<p>❌ POtype 필드가 존재하지 않습니다.</p>";
    }
    echo "<hr>";
}

// transactioncate에서 POtype 관련 항목들 찾기
echo "<h2>🎯 transactioncate에서 POtype 값들 찾기</h2>";

// 1, 2, 3, 4 값에 해당하는 transactioncate 항목들 확인
$potype_values = ['1', '2', '3', '4', '5'];

foreach ($potype_values as $value) {
    echo "<h3>📊 POtype = {$value}에 해당하는 transactioncate 항목들</h3>";
    
    $tc_query = "SELECT no, Ttable, title, BigNo, TreeNo 
                 FROM mlangprintauto_transactioncate 
                 WHERE no = '{$value}' 
                 ORDER BY Ttable";
    
    $tc_result = mysqli_query($db, $tc_query);
    
    if ($tc_result && mysqli_num_rows($tc_result) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>번호</th><th>테이블</th><th>제목</th><th>BigNo</th><th>TreeNo</th>";
        echo "</tr>";
        
        while ($row = mysqli_fetch_assoc($tc_result)) {
            echo "<tr>";
            echo "<td>{$row['no']}</td>";
            echo "<td>{$row['Ttable']}</td>";
            echo "<td><strong>{$row['title']}</strong></td>";
            echo "<td>{$row['BigNo']}</td>";
            echo "<td>{$row['TreeNo']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>POtype = {$value}에 해당하는 transactioncate 항목이 없습니다.</p>";
    }
}

// 추가로 POtype 관련 키워드가 포함된 transactioncate 항목들도 찾기
echo "<h2>🔍 POtype 관련 키워드 검색</h2>";

$keywords = ['단면', '양면', '인쇄', '색상', '도', '코팅', '후가공'];

foreach ($keywords as $keyword) {
    echo "<h4>🔎 '{$keyword}' 포함 항목들</h4>";
    
    $keyword_query = "SELECT no, Ttable, title, BigNo 
                      FROM mlangprintauto_transactioncate 
                      WHERE title LIKE '%{$keyword}%' 
                      ORDER BY Ttable, no 
                      LIMIT 10";
    
    $keyword_result = mysqli_query($db, $keyword_query);
    
    if ($keyword_result && mysqli_num_rows($keyword_result) > 0) {
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($keyword_result)) {
            echo "<li><strong>{$row['no']}</strong>: {$row['title']} ({$row['Ttable']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>'{$keyword}' 관련 항목이 없습니다.</p>";
    }
}

mysqli_close($db);
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    margin: 20px;
    background-color: #f8f9fa;
}

h1, h2, h3, h4 {
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

ul li {
    margin: 5px 0;
    padding: 3px;
    background-color: #f8f9fa;
}
</style>