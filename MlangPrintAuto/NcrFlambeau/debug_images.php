<?php
// 양식지 이미지 디버그 파일
echo "<h2>🔍 양식지 이미지 디버그</h2>";

// 데이터베이스 연결
include "../../db.php";

echo "<h3>1. 데이터베이스 연결 상태</h3>";
if ($db) {
    echo "✅ 데이터베이스 연결 성공<br>";
} else {
    echo "❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "<br>";
    exit;
}

echo "<h3>2. 포트폴리오 테이블 존재 확인</h3>";
$table_check = mysqli_query($db, "SHOW TABLES LIKE 'Mlang_portfolio_bbs'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ Mlang_portfolio_bbs 테이블 존재<br>";
} else {
    echo "❌ Mlang_portfolio_bbs 테이블이 존재하지 않음<br>";
}

echo "<h3>3. 서식/양식/상장 카테고리 데이터 조회</h3>";
$query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link, CATEGORY 
          FROM Mlang_portfolio_bbs 
          WHERE Mlang_bbs_reply='0' AND CATEGORY='서식/양식/상장'
          ORDER BY Mlang_bbs_no DESC 
          LIMIT 10";

echo "<strong>실행 쿼리:</strong><br>";
echo "<code>" . htmlspecialchars($query) . "</code><br><br>";

$result = mysqli_query($db, $query);

if (!$result) {
    echo "❌ 쿼리 실행 오류: " . mysqli_error($db) . "<br>";
} else {
    $count = mysqli_num_rows($result);
    echo "✅ 쿼리 실행 성공, 결과 수: {$count}개<br><br>";
    
    if ($count > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr><th>번호</th><th>제목</th><th>이미지파일(connent)</th><th>링크(link)</th><th>카테고리</th><th>이미지 미리보기</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['Mlang_bbs_no'] . "</td>";
            echo "<td>" . htmlspecialchars($row['Mlang_bbs_title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Mlang_bbs_connent']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Mlang_bbs_link']) . "</td>";
            echo "<td>" . htmlspecialchars($row['CATEGORY']) . "</td>";
            
            // 이미지 미리보기
            $image_path = '';
            if (!empty($row['Mlang_bbs_connent'])) {
                $image_path = '/bbs/upload/portfolio/' . $row['Mlang_bbs_connent'];
            } else if (!empty($row['Mlang_bbs_link'])) {
                $image_path = $row['Mlang_bbs_link'];
            }
            
            if ($image_path) {
                echo "<td><img src='" . $image_path . "' style='max-width: 100px; max-height: 100px;' onerror=\"this.src='/img/no-image.png'; this.alt='이미지 로드 실패';\"></td>";
            } else {
                echo "<td>이미지 없음</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ 해당 카테고리에 데이터가 없습니다.</p>";
    }
}

echo "<h3>4. 전체 카테고리 목록 확인</h3>";
$category_query = "SELECT DISTINCT CATEGORY, COUNT(*) as count 
                   FROM Mlang_portfolio_bbs 
                   WHERE Mlang_bbs_reply='0' 
                   GROUP BY CATEGORY 
                   ORDER BY count DESC";

$category_result = mysqli_query($db, $category_query);
if ($category_result) {
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr><th>카테고리</th><th>개수</th></tr>";
    while ($cat_row = mysqli_fetch_assoc($category_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($cat_row['CATEGORY']) . "</td>";
        echo "<td>" . $cat_row['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>5. 실제 이미지 파일 존재 확인</h3>";
$file_check_query = "SELECT Mlang_bbs_connent 
                     FROM Mlang_portfolio_bbs 
                     WHERE Mlang_bbs_reply='0' AND CATEGORY='서식/양식/상장' 
                     AND Mlang_bbs_connent IS NOT NULL AND Mlang_bbs_connent != ''
                     LIMIT 5";

$file_result = mysqli_query($db, $file_check_query);
if ($file_result) {
    while ($file_row = mysqli_fetch_assoc($file_result)) {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/bbs/upload/portfolio/' . $file_row['Mlang_bbs_connent'];
        $web_path = '/bbs/upload/portfolio/' . $file_row['Mlang_bbs_connent'];
        
        echo "<p>";
        echo "<strong>파일:</strong> " . htmlspecialchars($file_row['Mlang_bbs_connent']) . "<br>";
        echo "<strong>서버 경로:</strong> " . $file_path . "<br>";
        echo "<strong>웹 경로:</strong> " . $web_path . "<br>";
        echo "<strong>파일 존재:</strong> " . (file_exists($file_path) ? "✅ 존재" : "❌ 없음") . "<br>";
        if (file_exists($file_path)) {
            echo "<strong>파일 크기:</strong> " . number_format(filesize($file_path)) . " bytes<br>";
        }
        echo "</p>";
    }
}

mysqli_close($db);
?>