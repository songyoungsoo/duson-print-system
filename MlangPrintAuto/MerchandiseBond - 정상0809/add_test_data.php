<?php
// 테스트 데이터 추가
include "../../db_auto.php";

echo "<h2>상품권 테스트 데이터 추가</h2>";

// 현재 브라우저에서 전달하는 값에 맞는 테스트 데이터 추가
$test_data = [
    ['61461', '5', 500, '35000', '15000', '1'],
    ['61461', '5', 1000, '70000', '15000', '1'],
    ['61461', '5', 1500, '100000', '15000', '1'],
    ['61461', '5', 2000, '130000', '15000', '1'],
    ['61461', '5', 2500, '160000', '15000', '1'],
    ['61461', '5', 3000, '190000', '15000', '1'],
    ['61461', '5', 500, '40000', '25000', '2'],
    ['61461', '5', 1000, '80000', '25000', '2'],
    ['61461', '5', 1500, '110000', '25000', '2'],
    ['61461', '5', 2000, '150000', '25000', '2'],
    ['61461', '5', 2500, '180000', '25000', '2'],
    ['61461', '5', 3000, '210000', '25000', '2']
];

echo "<h3>추가할 테스트 데이터:</h3>";
echo "<table border='1'>";
echo "<tr><th>style</th><th>Section</th><th>quantity</th><th>money</th><th>DesignMoney</th><th>POtype</th></tr>";

$success_count = 0;
$error_count = 0;

foreach ($test_data as $data) {
    list($style, $section, $quantity, $money, $design_money, $potype) = $data;
    
    echo "<tr>";
    echo "<td>$style</td>";
    echo "<td>$section</td>";
    echo "<td>$quantity</td>";
    echo "<td>$money</td>";
    echo "<td>$design_money</td>";
    echo "<td>$potype</td>";
    echo "</tr>";
    
    // 중복 확인
    $check_query = "SELECT COUNT(*) as cnt FROM MlangPrintAuto_MerchandiseBond WHERE style=? AND Section=? AND quantity=? AND POtype=?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'ssss', $style, $section, $quantity, $potype);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['cnt'] == 0) {
        // 데이터 삽입
        $insert_query = "INSERT INTO MlangPrintAuto_MerchandiseBond (style, Section, quantity, money, DesignMoney, POtype) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($db, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'ssssss', $style, $section, $quantity, $money, $design_money, $potype);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $success_count++;
        } else {
            $error_count++;
            echo "<p style='color:red'>삽입 실패: $style, $section, $quantity, $potype</p>";
        }
    }
}

echo "</table>";

echo "<h3>결과:</h3>";
echo "<p>성공: $success_count 개</p>";
echo "<p>실패: $error_count 개</p>";

// 추가된 데이터 확인
echo "<h3>추가된 데이터 확인:</h3>";
$verify_query = "SELECT * FROM MlangPrintAuto_MerchandiseBond WHERE style='61461' AND Section='5' ORDER BY quantity, POtype";
$verify_result = mysqli_query($db, $verify_query);

if (mysqli_num_rows($verify_result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>no</th><th>style</th><th>Section</th><th>quantity</th><th>money</th><th>DesignMoney</th><th>POtype</th></tr>";
    while ($row = mysqli_fetch_assoc($verify_result)) {
        echo "<tr>";
        echo "<td>{$row['no']}</td>";
        echo "<td>{$row['style']}</td>";
        echo "<td>{$row['Section']}</td>";
        echo "<td>{$row['quantity']}</td>";
        echo "<td>{$row['money']}</td>";
        echo "<td>{$row['DesignMoney']}</td>";
        echo "<td>{$row['POtype']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>추가된 데이터가 없습니다.</p>";
}

mysqli_close($db);
?>

<p><strong>다음 단계:</strong></p>
<ol>
<li>이 페이지를 실행하여 테스트 데이터를 추가하세요</li>
<li>상품권 페이지로 돌아가서 가격 계산이 작동하는지 확인하세요</li>
<li>작동하면 다른 상품권 종류와 후가공 조합도 추가하세요</li>
</ol>