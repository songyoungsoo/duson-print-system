<?php
// 가격 계산 디버깅 테스트
header('Content-Type: text/html; charset=utf-8');

include "../../db_auto.php";

echo "<h2>상품권 가격 계산 디버깅</h2>";

// 1. 테이블 존재 확인
$TABLE = "MlangPrintAuto_MerchandiseBond";
$result = mysqli_query($db, "SHOW TABLES LIKE '$TABLE'");
if (mysqli_num_rows($result) > 0) {
    echo "<p>✅ 테이블 '$TABLE' 존재함</p>";
} else {
    echo "<p>❌ 테이블 '$TABLE' 존재하지 않음</p>";
    exit;
}

// 2. 테이블 구조 확인
echo "<h3>테이블 구조:</h3>";
$structure = mysqli_query($db, "DESCRIBE $TABLE");
echo "<table border='1'>";
echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($structure)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table><br>";

// 3. 샘플 데이터 확인
echo "<h3>샘플 데이터 (처음 10개):</h3>";
$sample = mysqli_query($db, "SELECT * FROM $TABLE LIMIT 10");
if (mysqli_num_rows($sample) > 0) {
    echo "<table border='1'>";
    $first = true;
    while ($row = mysqli_fetch_assoc($sample)) {
        if ($first) {
            echo "<tr>";
            foreach (array_keys($row) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            $first = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "<p>⚠️ 테이블에 데이터가 없습니다.</p>";
}

// 4. 실제 브라우저에서 전달된 파라미터로 테스트
echo "<h3>실제 파라미터로 테스트:</h3>";
$test_params = [
    'MY_type' => '61461',
    'PN_type' => '5', 
    'MY_amount' => '500',
    'POtype' => '1',
    'ordertype' => 'total'
];

echo "<h4>브라우저에서 전달된 파라미터:</h4>";
echo "<pre>" . print_r($test_params, true) . "</pre>";

// 실제 쿼리 실행
$query = "SELECT * FROM $TABLE WHERE style=? AND Section=? AND quantity=? AND POtype=?";
echo "<h4>실행할 쿼리:</h4>";
echo "<p>$query</p>";
echo "<p>파라미터: style={$test_params['MY_type']}, Section={$test_params['PN_type']}, quantity={$test_params['MY_amount']}, POtype={$test_params['POtype']}</p>";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'ssss', $test_params['MY_type'], $test_params['PN_type'], $test_params['MY_amount'], $test_params['POtype']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo "<p><strong>결과 행 수: " . mysqli_num_rows($result) . "</strong></p>";

if ($row = mysqli_fetch_assoc($result)) {
    echo "<h4>✅ 매칭되는 데이터 발견:</h4>";
    echo "<table border='1'>";
    echo "<tr>";
    foreach (array_keys($row) as $key) {
        echo "<th>$key</th>";
    }
    echo "</tr>";
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td>$value</td>";
    }
    echo "</tr>";
    echo "</table>";
} else {
    echo "<h4>❌ 해당 조건의 데이터가 없습니다</h4>";
    
    // 5. 각 조건별로 데이터 존재 여부 확인
    echo "<h3>각 조건별 데이터 존재 여부:</h3>";
    
    // style 조건
    $style_query = "SELECT COUNT(*) as cnt FROM $TABLE WHERE style='{$test_params['MY_type']}'";
    $style_result = mysqli_query($db, $style_query);
    $style_count = mysqli_fetch_assoc($style_result)['cnt'];
    echo "<p>style='{$test_params['MY_type']}' 데이터 개수: $style_count</p>";
    
    if ($style_count > 0) {
        echo "<h5>style='{$test_params['MY_type']}'인 모든 데이터:</h5>";
        $style_data = mysqli_query($db, "SELECT * FROM $TABLE WHERE style='{$test_params['MY_type']}' LIMIT 10");
        echo "<table border='1'>";
        $first = true;
        while ($row = mysqli_fetch_assoc($style_data)) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $key) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Section 조건
    $section_query = "SELECT COUNT(*) as cnt FROM $TABLE WHERE Section='{$test_params['PN_type']}'";
    $section_result = mysqli_query($db, $section_query);
    $section_count = mysqli_fetch_assoc($section_result)['cnt'];
    echo "<p>Section='{$test_params['PN_type']}' 데이터 개수: $section_count</p>";
    
    // quantity 조건
    $quantity_query = "SELECT COUNT(*) as cnt FROM $TABLE WHERE quantity='{$test_params['MY_amount']}'";
    $quantity_result = mysqli_query($db, $quantity_query);
    $quantity_count = mysqli_fetch_assoc($quantity_result)['cnt'];
    echo "<p>quantity='{$test_params['MY_amount']}' 데이터 개수: $quantity_count</p>";
    
    // POtype 조건
    $potype_query = "SELECT COUNT(*) as cnt FROM $TABLE WHERE POtype='{$test_params['POtype']}'";
    $potype_result = mysqli_query($db, $potype_query);
    $potype_count = mysqli_fetch_assoc($potype_result)['cnt'];
    echo "<p>POtype='{$test_params['POtype']}' 데이터 개수: $potype_count</p>";
    
    // 6. 가장 비슷한 데이터 찾기
    echo "<h3>가장 비슷한 데이터 찾기:</h3>";
    
    // style과 quantity가 같은 데이터
    $similar1_query = "SELECT * FROM $TABLE WHERE style='{$test_params['MY_type']}' AND quantity='{$test_params['MY_amount']}' LIMIT 5";
    $similar1_result = mysqli_query($db, $similar1_query);
    if (mysqli_num_rows($similar1_result) > 0) {
        echo "<h5>같은 style + quantity 데이터:</h5>";
        echo "<table border='1'>";
        $first = true;
        while ($row = mysqli_fetch_assoc($similar1_result)) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $key) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 7. 전체 데이터에서 사용 가능한 값들 확인
    echo "<h3>전체 데이터에서 사용 가능한 값들:</h3>";
    
    $distinct_style = mysqli_query($db, "SELECT DISTINCT style FROM $TABLE ORDER BY style");
    echo "<h5>사용 가능한 style 값들:</h5>";
    while ($row = mysqli_fetch_assoc($distinct_style)) {
        echo $row['style'] . ", ";
    }
    echo "<br><br>";
    
    $distinct_section = mysqli_query($db, "SELECT DISTINCT Section FROM $TABLE ORDER BY Section");
    echo "<h5>사용 가능한 Section 값들:</h5>";
    while ($row = mysqli_fetch_assoc($distinct_section)) {
        echo $row['Section'] . ", ";
    }
    echo "<br><br>";
    
    $distinct_quantity = mysqli_query($db, "SELECT DISTINCT quantity FROM $TABLE ORDER BY CAST(quantity AS UNSIGNED)");
    echo "<h5>사용 가능한 quantity 값들:</h5>";
    while ($row = mysqli_fetch_assoc($distinct_quantity)) {
        echo $row['quantity'] . ", ";
    }
    echo "<br><br>";
    
    $distinct_potype = mysqli_query($db, "SELECT DISTINCT POtype FROM $TABLE ORDER BY POtype");
    echo "<h5>사용 가능한 POtype 값들:</h5>";
    while ($row = mysqli_fetch_assoc($distinct_potype)) {
        echo $row['POtype'] . ", ";
    }
}

mysqli_close($db);
?>

<script>
// JavaScript에서 실제 폼 값들 확인
window.onload = function() {
    console.log("=== 현재 폼 값들 ===");
    var form = document.forms["choiceForm"];
    if (form) {
        console.log("MY_type:", form.MY_type ? form.MY_type.value : "없음");
        console.log("PN_type:", form.PN_type ? form.PN_type.value : "없음");
        console.log("MY_amount:", form.MY_amount ? form.MY_amount.value : "없음");
        console.log("POtype:", form.POtype ? form.POtype.value : "없음");
        console.log("ordertype:", form.ordertype ? form.ordertype.value : "없음");
    } else {
        console.log("폼을 찾을 수 없습니다.");
    }
};
</script>