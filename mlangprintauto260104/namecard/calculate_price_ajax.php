<?php
header("Content-Type: application/json");
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 받기 (GET 또는 POST 모두 지원)
$style = $_POST['MY_type'] ?? $_GET['MY_type'] ?? '';
$section = $_POST['Section'] ?? $_GET['Section'] ?? '';
$potype = $_POST['POtype'] ?? $_GET['POtype'] ?? '';
$quantity = $_POST['MY_amount'] ?? $_GET['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? $_GET['ordertype'] ?? '';

// 프리미엄 옵션 받기 (total 값 또는 개별 필드)
// 방법 1: premium_options_total이 직접 전송되는 경우 (명함 페이지)
$premium_total_direct = isset($_POST['premium_options_total']) ? intval($_POST['premium_options_total']) :
                        (isset($_GET['premium_options_total']) ? intval($_GET['premium_options_total']) : 0);

// 방법 2: JSON으로 전송되는 경우
$premium_options_json = $_POST['premium_options'] ?? $_GET['premium_options'] ?? '';
$premium_options = [];
if (!empty($premium_options_json)) {
    $decoded = json_decode($premium_options_json, true);
    if (is_array($decoded)) {
        $premium_options = $decoded;
    }
}

if (empty($style) || empty($section) || empty($potype) || empty($quantity) || empty($ordertype)) {
    error_response('모든 옵션을 선택해주세요.');
}

$TABLE = "mlangprintauto_namecard";

// 데이터베이스에서 가격 정보 조회
$query = "SELECT money, DesignMoney
          FROM $TABLE
          WHERE style='" . mysqli_real_escape_string($db, $style) . "'
          AND Section='" . mysqli_real_escape_string($db, $section) . "'
          AND POtype='" . mysqli_real_escape_string($db, $potype) . "'
          AND quantity='" . mysqli_real_escape_string($db, $quantity) . "'";

$result = mysqli_query($db, $query);
$row = null;

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    // 혹시 POtype 없이 데이터가 있는지 확인
    $query_fallback = "SELECT money, DesignMoney
                       FROM $TABLE
                       WHERE style='" . mysqli_real_escape_string($db, $style) . "'
                       AND Section='" . mysqli_real_escape_string($db, $section) . "'
                       AND quantity='" . mysqli_real_escape_string($db, $quantity) . "'";

    $result_fallback = mysqli_query($db, $query_fallback);
    if ($result_fallback && mysqli_num_rows($result_fallback) > 0) {
        $row = mysqli_fetch_assoc($result_fallback);
    }
}

if (!$row) {
    error_response('해당 조건의 가격 정보를 찾을 수 없습니다.');
}

// 기본 가격 계산
$base_price = (int)$row['money'];
$design_price_db = (int)$row['DesignMoney'];
$design_price = ($ordertype === 'total') ? $design_price_db : 0;

// 프리미엄 옵션 가격 계산
$premium_total = 0;
$premium_details = [];

// 방법 1: premium_options_total이 직접 전송된 경우 (명함 페이지)
if ($premium_total_direct > 0) {
    $premium_total = $premium_total_direct;
    // 상세 내역은 개별 필드에서 추출 가능하지만 여기서는 총액만 사용
} else {
    // 방법 2: JSON으로 전송된 경우 - 개별 옵션 계산
    // 박 (Foil)
    if (!empty($premium_options['foil'])) {
        $foil_price = 30000; // 예시 가격
        $premium_total += $foil_price;
        $premium_details[] = ['name' => '박', 'price' => $foil_price];
    }

    // 넘버링 (Numbering)
    if (!empty($premium_options['numbering'])) {
        $numbering_price = 60000; // 예시 가격
        $premium_total += $numbering_price;
        $premium_details[] = ['name' => '넘버링', 'price' => $numbering_price];
    }

    // 미싱 (Perforation)
    if (!empty($premium_options['perforation'])) {
        $perforation_price = 20000; // 예시 가격
        $premium_total += $perforation_price;
        $premium_details[] = ['name' => '미싱', 'price' => $perforation_price];
    }

    // 귀돌이 (Rounding)
    if (!empty($premium_options['rounding'])) {
        $rounding_price = 20000; // 예시 가격
        $premium_total += $rounding_price;
        $premium_details[] = ['name' => '귀돌이', 'price' => $rounding_price];
    }

    // 오시 (Creasing)
    if (!empty($premium_options['creasing'])) {
        $creasing_price = 20000; // 예시 가격
        $premium_total += $creasing_price;
        $premium_details[] = ['name' => '오시', 'price' => $creasing_price];
    }
}

// 최종 가격 계산
$total_price = $base_price + $design_price + $premium_total;
$total_with_vat = round($total_price * 1.1);

$response_data = [
    'success' => true,
    'base_price' => $base_price,
    'design_price' => $design_price,
    'premium_total' => $premium_total,
    'premium_details' => $premium_details,
    'total_price' => $total_price,
    'total_with_vat' => $total_with_vat,  // JavaScript와 일치
    'vat_price' => $total_with_vat        // 레거시 호환
];

success_response($response_data, '가격 계산 완료');

mysqli_close($db);
?>