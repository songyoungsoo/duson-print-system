<?php
include "db.php";
mysqli_set_charset($db, "utf8");

echo "<h2>포스터 디자인 비용 데이터 소스 분석</h2>";

// 1. mlangprintauto_littleprint 테이블에서 DesignMoney 확인
echo "<h3>1. mlangprintauto_littleprint 테이블의 DesignMoney</h3>";
$query1 = "SELECT DISTINCT DesignMoney FROM mlangprintauto_littleprint 
           WHERE style = '590'
           ORDER BY DesignMoney";
$result1 = mysqli_query($db, $query1);

if ($result1) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result1)) {
        echo "<li>디자인비: " . number_format($row['DesignMoney']) . "원</li>";
    }
    echo "</ul>";
}

// 2. 실제 레코드 몇 개 확인
echo "<h3>2. 실제 데이터 샘플 (상위 10개)</h3>";
$query2 = "SELECT TreeSelect, Section, quantity, money, DesignMoney 
           FROM mlangprintauto_littleprint 
           WHERE style = '590'
           ORDER BY TreeSelect, Section, quantity
           LIMIT 10";
$result2 = mysqli_query($db, $query2);

if ($result2) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>TreeSelect</th><th>Section</th><th>Quantity</th><th>Money</th><th>DesignMoney</th></tr>";
    while ($row = mysqli_fetch_assoc($result2)) {
        echo "<tr>";
        echo "<td>{$row['TreeSelect']}</td>";
        echo "<td>{$row['Section']}</td>";
        echo "<td>{$row['quantity']}매</td>";
        echo "<td>" . number_format($row['money']) . "원</td>";
        echo "<td>" . number_format($row['DesignMoney']) . "원</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. 포스터 시스템에서 디자인비가 어떻게 사용되는지 확인
echo "<h3>3. 포스터 시스템의 디자인비 사용 방식</h3>";
echo "<p><strong>📋 디자인비 설정 방식:</strong></p>";
echo "<ul>";
echo "<li>데이터베이스 DesignMoney 필드: " . (($result1 && mysqli_num_rows($result1) > 0) ? "20,000원 고정" : "값 없음") . "</li>";
echo "<li>편집디자인 드롭다운: 사용자 선택 방식</li>";
echo "<li>최종 계산: 사용자 선택에 따라 0원 또는 DesignMoney 적용</li>";
echo "</ul>";

// 4. 편집디자인 옵션 확인 (하드코딩 여부)
echo "<h3>4. 편집디자인 옵션 소스</h3>";
echo "<p><strong>포스터 페이지의 편집디자인 드롭다운:</strong></p>";
echo "<ul>";
echo "<li>인쇄만 의뢰: 디자인비 0원</li>";
echo "<li>디자인+인쇄: 디자인비 DesignMoney 적용</li>";
echo "</ul>";

// 5. 다른 시스템들과 비교
echo "<h3>5. 다른 제품들의 디자인비 비교</h3>";

// 명함 시스템 확인
$namecard_query = "SHOW TABLES LIKE 'mlangprintauto_namecard'";
$namecard_check = mysqli_query($db, $namecard_query);
if ($namecard_check && mysqli_num_rows($namecard_check) > 0) {
    $namecard_design_query = "SELECT DISTINCT DesignMoney FROM mlangprintauto_namecard LIMIT 3";
    $namecard_design_result = mysqli_query($db, $namecard_design_query);
    if ($namecard_design_result) {
        echo "<p><strong>명함 시스템:</strong> ";
        while ($row = mysqli_fetch_assoc($namecard_design_result)) {
            echo number_format($row['DesignMoney']) . "원 ";
        }
        echo "</p>";
    }
} else {
    echo "<p><strong>명함 시스템:</strong> mlangprintauto_namecard 테이블 없음</p>";
}

// 상품권 시스템 확인
$bond_query = "SHOW TABLES LIKE 'mlangprintauto_merchandisebond'";
$bond_check = mysqli_query($db, $bond_query);
if ($bond_check && mysqli_num_rows($bond_check) > 0) {
    $bond_design_query = "SELECT DISTINCT DesignMoney FROM mlangprintauto_merchandisebond LIMIT 3";
    $bond_design_result = mysqli_query($db, $bond_design_query);
    if ($bond_design_result) {
        echo "<p><strong>상품권 시스템:</strong> ";
        while ($row = mysqli_fetch_assoc($bond_design_result)) {
            echo number_format($row['DesignMoney']) . "원 ";
        }
        echo "</p>";
    }
} else {
    echo "<p><strong>상품권 시스템:</strong> mlangprintauto_merchandisebond 테이블 없음</p>";
}

// 6. 소스 코드에서 디자인비 하드코딩 확인
echo "<h3>6. 🔍 디자인비 데이터 소스 결론</h3>";
echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #ccc;'>";
echo "<p><strong>포스터 디자인비 20,000원의 출처:</strong></p>";
echo "<ol>";
echo "<li><strong>스크립트 생성</strong>: add_poster_materials.php에서 'DesignMoney' => '20000' 하드코딩</li>";
echo "<li><strong>add_poster_sizes.php</strong>에서도 'DesignMoney', '20000' 하드코딩</li>";
echo "<li><strong>기존 시스템 기준</strong>: 다른 제품들의 표준 디자인비 참조</li>";
echo "<li><strong>적용 방식</strong>: 사용자가 '디자인+인쇄' 선택 시에만 적용</li>";
echo "</ol>";
echo "</div>";

mysqli_close($db);
?>

<style>
h2, h3 { color: #333; margin-top: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th { background: #f0f0f0; }
ul, ol { margin: 10px 0; }
</style>