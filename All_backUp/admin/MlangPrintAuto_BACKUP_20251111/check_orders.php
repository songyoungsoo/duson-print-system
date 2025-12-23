<?php
/**
 * 실제 주문 데이터 확인
 */

// 현재 환경의 데이터베이스 연결
include "../../db.php";

echo "<h2>🔍 실제 주문 데이터 확인</h2>";

// 1. 주문 테이블 존재 확인
echo "<h3>1. 테이블 존재 확인</h3>";
$result = mysqli_query($db, "SHOW TABLES LIKE 'mlangorder_printauto'");
if ($result && mysqli_num_rows($result) > 0) {
    echo "✅ mlangorder_printauto 테이블: 존재<br>";
} else {
    echo "❌ mlangorder_printauto 테이블: 없음<br>";
    echo "사용 가능한 테이블들:<br>";
    $tables = mysqli_query($db, "SHOW TABLES");
    while ($table = mysqli_fetch_row($tables)) {
        if (strpos(strtolower($table[0]), 'order') !== false || strpos(strtolower($table[0]), 'mlang') !== false) {
            echo "- " . $table[0] . "<br>";
        }
    }
}

// 2. 주문 데이터 확인
echo "<h3>2. 주문 데이터 현황</h3>";
$result = mysqli_query($db, "SELECT COUNT(*) as total FROM mlangorder_printauto");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "📊 총 주문 수: " . $row['total'] . "개<br>";

    if ($row['total'] > 0) {
        // 실제 주문 번호들 가져오기
        echo "<h4>📋 실제 존재하는 주문 번호들 (최근 10개):</h4>";
        $result = mysqli_query($db, "SELECT no, name, Type, date, OrderStyle FROM mlangorder_printauto ORDER BY no DESC LIMIT 10");
        if ($result) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'><th>번호</th><th>이름</th><th>품목</th><th>날짜</th><th>상태</th><th>테스트 링크</th></tr>";
            while ($order = mysqli_fetch_assoc($result)) {
                $status_names = [
                    '1' => '견적접수',
                    '2' => '주문접수',
                    '3' => '접수완료',
                    '4' => '입금대기',
                    '5' => '시안제작중',
                    '6' => '시안',
                    '7' => '교정',
                    '8' => '작업완료',
                    '9' => '작업중',
                    '10' => '교정작업중'
                ];
                $status = $status_names[$order['OrderStyle']] ?? $order['OrderStyle'];

                echo "<tr>";
                echo "<td>" . htmlspecialchars($order['no'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($order['name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($order['Type'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($order['date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($status) . "</td>";
                echo "<td><a href='admin_74_simple.php?mode=OrderView&no=" . $order['no'] . "' target='_blank' style='color: #007bff;'>📋 테스트</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        // 주문 번호 범위 확인
        echo "<h4>📊 주문 번호 범위:</h4>";
        $result = mysqli_query($db, "SELECT MIN(no) as min_no, MAX(no) as max_no FROM mlangorder_printauto");
        if ($result) {
            $range = mysqli_fetch_assoc($result);
            echo "최소 주문번호: " . $range['min_no'] . "<br>";
            echo "최대 주문번호: " . $range['max_no'] . "<br>";
        }
    } else {
        echo "<p style='color: #dc3545;'>❌ 주문 데이터가 없습니다.</p>";
        echo "<p>테스트를 위해 샘플 데이터를 생성해야 합니다.</p>";
    }
} else {
    echo "❌ 데이터 조회 실패: " . mysqli_error($db) . "<br>";
}

// 3. 테이블 구조 확인
echo "<h3>3. 테이블 구조 확인</h3>";
$result = mysqli_query($db, "DESCRIBE mlangorder_printauto");
if ($result) {
    echo "<details><summary>테이블 구조 보기 (클릭)</summary>";
    echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr style='background: #f0f0f0;'><th>필드</th><th>타입</th><th>NULL</th><th>키</th><th>기본값</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</details>";
} else {
    echo "❌ 테이블 구조를 가져올 수 없습니다: " . mysqli_error($db) . "<br>";
}

mysqli_close($db);
?>
<style>
    body {
        font-family: 'Noto Sans KR', Arial, sans-serif;
        margin: 20px;
        background: #f8f9fa;
    }

    table {
        margin: 10px 0;
        font-size: 14px;
    }

    th, td {
        padding: 8px;
        text-align: left;
    }

    details {
        margin: 10px 0;
    }

    summary {
        cursor: pointer;
        background: #e9ecef;
        padding: 8px;
        border-radius: 4px;
    }

    a {
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
</style>