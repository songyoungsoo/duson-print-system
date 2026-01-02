<?php
/**
 * 포스터 주문 수정 스크립트
 * Type='기타'이고 product_type='poster'인 주문들을 올바른 형식으로 수정
 */

include "/var/www/html/db.php";
$connect = $db;

// UTF-8 설정
mysqli_set_charset($connect, 'utf8mb4');

// getCategoryName 함수
function getCategoryName($connect, $category_no) {
    if (!$category_no) return '';

    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return $category_no;
    }

    mysqli_stmt_bind_param($stmt, 's', $category_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        return $row['title'];
    }

    return $category_no;
}

// Type='기타'인 주문 조회
$query = "SELECT no, Type, Type_1 FROM mlangorder_printauto WHERE Type = '기타' ORDER BY no DESC";
$result = mysqli_query($connect, $query);

$fixed_count = 0;
$skip_count = 0;

echo "=== 포스터 주문 수정 시작 ===\n\n";

while ($row = mysqli_fetch_assoc($result)) {
    $no = $row['no'];
    $type_1 = $row['Type_1'];

    // JSON 파싱
    $data = json_decode($type_1, true);

    // product_type이 poster 또는 littleprint가 아니면 건너뛰기
    if (!$data || !isset($data['product_type']) ||
        ($data['product_type'] !== 'poster' && $data['product_type'] !== 'littleprint')) {
        $skip_count++;
        continue;
    }

    echo "주문 #$no 수정 중...\n";

    // 필요한 정보 추출
    $my_type = $data['MY_type'] ?? '';
    $section = $data['Section'] ?? $data['MY_Fsd'] ?? '';
    $pn_type = $data['PN_type'] ?? '';
    $my_amount = $data['MY_amount'] ?? '0';
    $ordertype = $data['ordertype'] ?? 'print';

    // 카테고리 이름 조회
    $type_name = getCategoryName($connect, $my_type);
    $paper_name = getCategoryName($connect, $section);
    $size_name = getCategoryName($connect, $pn_type);
    $design = ($ordertype == 'total') ? '디자인+인쇄' : '인쇄만';

    // 올바른 JSON 생성
    $correct_data = [
        'product_type' => 'littleprint',  // 정규화
        'MY_type' => $my_type,
        'Section' => $section,
        'PN_type' => $pn_type,
        'MY_amount' => $my_amount,
        'ordertype' => $ordertype,
        'formatted_display' => "구분: $type_name\n" .
                              "용지: $paper_name\n" .
                              "규격: $size_name\n" .
                              "수량: " . number_format(floatval($my_amount)) . "매\n" .
                              "디자인: $design",
        'created_at' => date('Y-m-d H:i:s')
    ];

    $correct_json = json_encode($correct_data, JSON_UNESCAPED_UNICODE);

    echo "  - 구분: $type_name\n";
    echo "  - 용지: $paper_name\n";
    echo "  - 규격: $size_name\n";
    echo "  - 수량: " . number_format(floatval($my_amount)) . "매\n";
    echo "  - 디자인: $design\n";

    // 업데이트
    $update_query = "UPDATE mlangorder_printauto SET Type = '포스터', Type_1 = ? WHERE no = ?";
    $update_stmt = mysqli_prepare($connect, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'si', $correct_json, $no);

    if (mysqli_stmt_execute($update_stmt)) {
        echo "  ✅ 수정 완료\n\n";
        $fixed_count++;
    } else {
        echo "  ❌ 수정 실패: " . mysqli_error($connect) . "\n\n";
    }

    mysqli_stmt_close($update_stmt);
}

echo "=== 수정 완료 ===\n";
echo "수정된 주문: $fixed_count 개\n";
echo "건너뛴 주문: $skip_count 개\n";

mysqli_close($connect);
?>
