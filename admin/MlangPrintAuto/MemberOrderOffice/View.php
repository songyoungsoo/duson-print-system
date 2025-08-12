<?php
if (!isset($DbDir) || !$DbDir) {
    $DbDir = "../../..";
}
include "$DbDir/db.php";

$no = (int)($_GET['no'] ?? $_POST['no'] ?? 0);

// 데이터가 없는 경우 처리
if ($no <= 0) {
    // 데이터가 없을 때 기본값 설정
    $row = [];
} else {
    // mysqli 방식으로 데이터 조회
    $stmt = $db->prepare("SELECT * FROM MlangPrintAuto_MemberOrderOffice WHERE no = ?");
    if ($stmt) {
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
    } else {
        // prepare 실패 시 일반 쿼리로 시도
        $result = $db->query("SELECT * FROM MlangPrintAuto_MemberOrderOffice WHERE no = " . $no);
        $row = $result ? $result->fetch_assoc() : [];
    }
    
    if (!$row) {
        $row = []; // 데이터가 없을 때 빈 배열로 설정
    }
}

// 배열 접근 함수
function getRowValue($row, $key) {
    return $row[$key] ?? '';
}

// One 영역
for ($i = 1; $i <= 12; $i++) {
    ${"View_One_$i"} = getRowValue($row, "One_$i");
}
$View_One_12 = getRowValue($row, "One_13"); // 특이한 인덱스 처리

// Two 영역
for ($i = 1; $i <= 58; $i++) {
    ${"View_Two_$i"} = getRowValue($row, "Two_$i");
}

// Tree 영역
for ($i = 1; $i <= 15; $i++) {
    ${"View_Tree_$i"} = getRowValue($row, "Tree_$i");
}

// Four 영역
for ($i = 1; $i <= 12; $i++) {
    ${"View_Four_$i"} = getRowValue($row, "Four_$i");
}

// Five 영역
for ($i = 1; $i <= 29; $i++) {
    ${"View_Five_$i"} = getRowValue($row, "Five_$i");
}

// 기타
$View_cont = getRowValue($row, "cont");
$View_date = getRowValue($row, "date");

// 체크박스 분해
$View_Two_7Ok = explode("-", $View_Two_7);
$View_Two_7_1 = $View_Two_7Ok[0] ?? '';
$View_Two_7_2 = $View_Two_7Ok[1] ?? '';
$View_Two_7_3 = $View_Two_7Ok[2] ?? '';

$View_Two_21Ok = explode("-", $View_Two_21);
$View_Two_21_1 = $View_Two_21Ok[0] ?? '';
$View_Two_21_2 = $View_Two_21Ok[1] ?? '';
$View_Two_21_3 = $View_Two_21Ok[2] ?? '';
$View_Two_21_4 = $View_Two_21Ok[3] ?? '';

$View_Two_33Ok = explode("-", $View_Two_33);
$View_Two_33_1 = $View_Two_33Ok[0] ?? '';
$View_Two_33_2 = $View_Two_33Ok[1] ?? '';
$View_Two_33_3 = $View_Two_33Ok[2] ?? '';

// 마무리
if (isset($db) && $db) {
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>주문 상세 보기</title>
    <style>
        body { font-family: '굴림', sans-serif; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        caption { font-weight: bold; font-size: 11pt; margin-bottom: 10px; }
    </style>
</head>
<body>

<h2 align="center">Mlang 인쇄 주문 상세보기</h2>

<table>
    <caption>▶ One 정보</caption>
    <tbody>
    <?php for ($i = 1; $i <= 12; $i++): ?>
        <tr>
            <th>One_<?php echo  $i ?></th>
            <td><?php echo  htmlspecialchars(${"View_One_$i"}) ?></td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

<table>
    <caption>▶ Two 정보</caption>
    <tbody>
    <?php for ($i = 1; $i <= 58; $i++): ?>
        <tr>
            <th>Two_<?php echo  $i ?></th>
            <td><?php echo  htmlspecialchars(${"View_Two_$i"}) ?></td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

<table>
    <caption>▶ Tree 정보</caption>
    <tbody>
    <?php for ($i = 1; $i <= 15; $i++): ?>
        <tr>
            <th>Tree_<?php echo  $i ?></th>
            <td><?php echo  htmlspecialchars(${"View_Tree_$i"}) ?></td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

<table>
    <caption>▶ Four 정보</caption>
    <tbody>
    <?php for ($i = 1; $i <= 12; $i++): ?>
        <tr>
            <th>Four_<?php echo  $i ?></th>
            <td><?php echo  htmlspecialchars(${"View_Four_$i"}) ?></td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

<table>
    <caption>▶ Five 정보</caption>
    <tbody>
    <?php for ($i = 1; $i <= 29; $i++): ?>
        <tr>
            <th>Five_<?php echo  $i ?></th>
            <td><?php echo  htmlspecialchars(${"View_Five_$i"}) ?></td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

<table>
    <caption>▶ 기타</caption>
    <tr>
        <th>내용 (cont)</th>
        <td><?php echo  nl2br(htmlspecialchars($View_cont)) ?></td>
    </tr>
    <tr>
        <th>작성일 (date)</th>
        <td><?php echo  htmlspecialchars($View_date) ?></td>
    </tr>
</table>

</body>
</html>
