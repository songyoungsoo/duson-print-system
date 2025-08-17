<?php
include "db.php";
mysqli_set_charset($db, "utf8");

header("Content-Type: text/html; charset=utf-8");

echo "<h2>🎨 포스터 시스템 데이터 소스 분석</h2>";

// 1. 포스터 시스템이 사용하는 데이터 테이블 확인
echo "<h3>1. 포스터 시스템이 사용하는 데이터 테이블</h3>";
echo "<ul>";
echo "<li><strong>mlangprintauto_transactioncate</strong>: 카테고리 정보 (포스터 종류, 재질명, 규격명)</li>";
echo "<li><strong>mlangprintauto_littleprint</strong>: 가격 및 수량 데이터</li>";
echo "</ul>";

// 2. mlangprintauto_transactioncate에서 LittlePrint 관련 데이터 확인
echo "<h3>2. mlangprintauto_transactioncate - 포스터 카테고리 데이터</h3>";
$query1 = "SELECT no, title, BigNo FROM mlangprintauto_transactioncate 
           WHERE Ttable='LittlePrint' AND BigNo='0'
           ORDER BY no ASC";
$result1 = mysqli_query($db, $query1);

if ($result1) {
    echo "<div style='background: #f0f8ff; padding: 10px; border: 1px solid #ccc; margin: 10px 0;'>";
    echo "<strong>🏷️ 포스터 종류 (BigNo=0인 주 카테고리):</strong><br>";
    while ($row = mysqli_fetch_assoc($result1)) {
        echo "[{$row['no']}] {$row['title']}<br>";
    }
    echo "</div>";
} else {
    echo "<p style='color:red;'>오류: " . mysqli_error($db) . "</p>";
}

// 3. 재질 데이터 확인 (TreeSelect)
echo "<h3>3. 재질 데이터 (TreeSelect)</h3>";
$query2 = "SELECT no, title FROM mlangprintauto_transactioncate 
           WHERE Ttable='LittlePrint' AND BigNo!='0'
           ORDER BY no ASC LIMIT 20";
$result2 = mysqli_query($db, $query2);

if ($result2) {
    echo "<div style='background: #f0fff0; padding: 10px; border: 1px solid #ccc; margin: 10px 0;'>";
    echo "<strong>📄 재질 옵션 (BigNo!=0인 하위 카테고리, 상위 20개):</strong><br>";
    while ($row = mysqli_fetch_assoc($result2)) {
        echo "[{$row['no']}] {$row['title']}<br>";
    }
    echo "</div>";
}

// 4. mlangprintauto_littleprint 테이블 구조 확인
echo "<h3>4. mlangprintauto_littleprint 테이블 구조</h3>";
$query3 = "DESCRIBE mlangprintauto_littleprint";
$result3 = mysqli_query($db, $query3);

if ($result3) {
    echo "<div style='background: #fff8f0; padding: 10px; border: 1px solid #ccc; margin: 10px 0;'>";
    echo "<strong>🗃️ 테이블 구조:</strong><br>";
    while ($row = mysqli_fetch_assoc($result3)) {
        echo "• <strong>{$row['Field']}</strong>: {$row['Type']}<br>";
    }
    echo "</div>";
}

// 5. 실제 데이터 샘플 확인
echo "<h3>5. mlangprintauto_littleprint 데이터 샘플</h3>";
$query4 = "SELECT style, TreeSelect, Section, POtype, quantity, money 
           FROM mlangprintauto_littleprint 
           ORDER BY style, TreeSelect, Section, POtype, quantity
           LIMIT 20";
$result4 = mysqli_query($db, $query4);

if ($result4) {
    echo "<div style='background: #f8f0ff; padding: 10px; border: 1px solid #ccc; margin: 10px 0;'>";
    echo "<strong>💰 가격 데이터 샘플 (상위 20개):</strong><br>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #ddd;'><th>style</th><th>TreeSelect</th><th>Section</th><th>POtype</th><th>quantity</th><th>money</th></tr>";
    while ($row = mysqli_fetch_assoc($result4)) {
        echo "<tr>";
        echo "<td>{$row['style']}</td>";
        echo "<td>{$row['TreeSelect']}</td>";
        echo "<td>{$row['Section']}</td>";
        echo "<td>{$row['POtype']}</td>";
        echo "<td>{$row['quantity']}</td>";
        echo "<td>" . number_format($row['money']) . "원</td>";
        echo "</tr>";
    }
    echo "</table></div>";
}

// 6. 데이터 매핑 예시
echo "<h3>6. 🔄 데이터 매핑 구조</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; border: 1px solid #999; margin: 10px 0;'>";
echo "<h4>포스터 시스템의 동적 데이터 흐름:</h4>";
echo "<ol>";
echo "<li><strong>포스터 종류 선택</strong><br>";
echo "   → mlangprintauto_transactioncate (Ttable='LittlePrint', BigNo='0')</li>";
echo "<li><strong>재질 옵션 로드</strong><br>";
echo "   → mlangprintauto_littleprint에서 선택된 style의 TreeSelect 찾기<br>";
echo "   → mlangprintauto_transactioncate에서 TreeSelect ID의 title 가져오기</li>";
echo "<li><strong>규격 옵션 로드</strong><br>";
echo "   → mlangprintauto_littleprint에서 선택된 TreeSelect의 Section 찾기<br>";
echo "   → mlangprintauto_transactioncate에서 Section ID의 title 가져오기</li>";
echo "<li><strong>수량 옵션 로드</strong><br>";
echo "   → mlangprintauto_littleprint에서 조건에 맞는 quantity 찾기</li>";
echo "<li><strong>가격 계산</strong><br>";
echo "   → mlangprintauto_littleprint에서 정확한 조건의 money 가져오기</li>";
echo "</ol>";
echo "</div>";

// 7. 실제 API 호출 테스트 예시
echo "<h3>7. 🔧 API 엔드포인트 확인</h3>";

// 첫 번째 포스터 종류 찾기
$first_style_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                      WHERE Ttable='LittlePrint' AND BigNo='0' 
                      ORDER BY no ASC LIMIT 1";
$first_style_result = mysqli_query($db, $first_style_query);
$first_style = mysqli_fetch_assoc($first_style_result);

if ($first_style) {
    echo "<div style='background: #e6f3ff; padding: 10px; border: 1px solid #0066cc; margin: 10px 0;'>";
    echo "<strong>🧪 테스트 가능한 API 엔드포인트:</strong><br>";
    echo "<ul>";
    echo "<li><a href='MlangPrintAuto/Poster/get_paper_types.php?style={$first_style['no']}' target='_blank'>";
    echo "재질 옵션 가져오기 (style={$first_style['no']})</a></li>";
    
    // 첫 번째 재질 찾기
    $first_material_query = "SELECT DISTINCT TreeSelect FROM mlangprintauto_littleprint 
                           WHERE style='{$first_style['no']}' 
                           ORDER BY TreeSelect ASC LIMIT 1";
    $first_material_result = mysqli_query($db, $first_material_query);
    $first_material = mysqli_fetch_assoc($first_material_result);
    
    if ($first_material) {
        echo "<li><a href='MlangPrintAuto/Poster/get_paper_sizes.php?section={$first_material['TreeSelect']}' target='_blank'>";
        echo "규격 옵션 가져오기 (section={$first_material['TreeSelect']})</a></li>";
    }
    
    echo "<li><a href='MlangPrintAuto/Poster/index_compact.php' target='_blank'>";
    echo "포스터 메인 페이지</a></li>";
    echo "</ul>";
    echo "</div>";
}

// 8. 데이터 소스 요약
echo "<h3>8. 📊 데이터 소스 요약</h3>";
echo "<div style='background: #fff; padding: 15px; border: 2px solid #333; margin: 10px 0;'>";
echo "<h4>🎯 포스터 시스템은 기존 데이터를 재사용합니다:</h4>";
echo "<ul>";
echo "<li><strong>데이터 생성 불필요</strong>: 새로운 데이터를 만들지 않음</li>";
echo "<li><strong>LittlePrint 데이터 공유</strong>: 전단지와 동일한 테이블 사용</li>";
echo "<li><strong>필터링 방식</strong>: 포스터 관련 카테고리만 선별적으로 표시</li>";
echo "<li><strong>동적 로딩</strong>: 데이터베이스에서 실시간으로 옵션 생성</li>";
echo "<li><strong>확장성</strong>: 새로운 포스터 데이터 추가 시 자동 반영</li>";
echo "</ul>";
echo "</div>";

mysqli_close($db);
?>

<style>
h2, h3 { color: #333; margin-top: 20px; }
div { margin: 10px 0; }
table { margin: 10px 0; }
a { color: #0066cc; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>