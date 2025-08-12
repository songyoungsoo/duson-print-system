<?php
session_start();
include "../../db.php";

header('Content-Type: text/html; charset=utf-8');

if (!$db) {
    die("Database connection failed");
}

mysqli_set_charset($db, "utf8");

$category_no = $_GET['category_no'] ?? '590';

echo "<h2>종이종류 디버그 (category_no: $category_no)</h2>";

// 1. 먼저 littleprint 테이블에 해당 style 데이터가 있는지 확인
echo "<h3>1. littleprint 테이블에서 style=$category_no 데이터 확인</h3>";
$query = "SELECT DISTINCT TreeSelect FROM mlangprintauto_littleprint WHERE style='$category_no' ORDER BY TreeSelect";
$result = mysqli_query($db, $query);
$tree_selects = [];
while ($row = mysqli_fetch_array($result)) {
    $tree_selects[] = $row['TreeSelect'];
    echo "TreeSelect: {$row['TreeSelect']}<br>";
}

// 2. transactioncate 테이블에서 해당 번호들의 정보 확인
echo "<h3>2. transactioncate 테이블에서 해당 번호들 확인</h3>";
foreach ($tree_selects as $tree_select) {
    $query = "SELECT no, title FROM mlangprintauto_transactioncate WHERE no='$tree_select'";
    $result = mysqli_query($db, $query);
    if ($row = mysqli_fetch_array($result)) {
        echo "no: {$row['no']}, title: {$row['title']}<br>";
    } else {
        echo "no: $tree_select - 데이터 없음<br>";
    }
}

// 3. JOIN 쿼리 결과 확인
echo "<h3>3. JOIN 쿼리 결과</h3>";
$query = "SELECT DISTINCT t.no, t.title 
          FROM mlangprintauto_transactioncate t
          INNER JOIN mlangprintauto_littleprint l ON t.no = l.TreeSelect
          WHERE l.style = '$category_no'
          ORDER BY t.no ASC";
$result = mysqli_query($db, $query);
$options = [];
while ($row = mysqli_fetch_array($result)) {
    $options[] = [
        'no' => $row['no'],
        'title' => $row['title']
    ];
    echo "no: {$row['no']}, title: {$row['title']}<br>";
}

// 4. JSON 출력
echo "<h3>4. JSON 출력</h3>";
if (empty($options)) {
    $paper_types = [
        ['no' => '604', 'title' => '120아트/스노우'],
        ['no' => '605', 'title' => '150아트/스노우'],
        ['no' => '606', 'title' => '180아트/스노우'],
        ['no' => '607', 'title' => '200아트/스노우'],
        ['no' => '608', 'title' => '250아트/스노우'],
        ['no' => '609', 'title' => '300아트/스노우'],
        ['no' => '679', 'title' => '80모조'],
        ['no' => '680', 'title' => '100모조']
    ];
    $options = $paper_types;
    echo "기본 데이터 사용<br>";
}

echo "<pre>" . json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

mysqli_close($db);
?>