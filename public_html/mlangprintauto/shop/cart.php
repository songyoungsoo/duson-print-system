<?php
session_start();
$session_id = session_id();

// 관리자 로그인 상태 확인
$is_admin = isset($_SESSION['user_id']);
$admin_name = $_SESSION['user_name'] ?? '';

// 경로 수정: MlangPrintAuto/shop/에서 루트의 db.php 접근
include "../../db.php";
include "../../includes/AdditionalOptionsDisplay.php";
$connect = $db; // db.php에서 $db 변수 사용

error_log("Attempting to connect to database");

// UTF-8 설정과 연결 확인
if ($connect) {
    error_log("Database connection successful");
    if (!mysqli_set_charset($connect, 'utf8')) {
        error_log("Error setting UTF-8 charset: " . mysqli_error($connect));
    }
}

// ID로 한글명 가져오기 함수
function getKoreanName($connect, $id)
{
    if (!$connect || !$id) {
        return $id; // 연결이 없거나 ID가 없으면 원본 반환
    }

    // ID가 이미 한글이면 그대로 반환
    if (preg_match('/[가-힣]/u', $id)) {
        return $id;
    }

    // 숫자와 문자열 모두 처리
    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? OR title = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        // 쿼리 실패시 로그
        error_log("getKoreanName prepare failed: " . mysqli_error($connect));
        return $id;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $id, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }

    mysqli_stmt_close($stmt);

    // 못 찾으면 로그 남기고 원본 반환
    error_log("getKoreanName: No match found for ID: " . $id);
    return $id; // 찾지 못하면 원본 ID 반환
}

// 장바구니 내용 가져오기 (통합 버전)
function getCartItems($connect, $session_id)
{
    if (!$connect) {
        error_log("Database connection failed");
        return false;
    }

    $items = [];

    // shop_temp 테이블에서 모든 상품 데이터 가져오기
    $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
    $stmt = mysqli_prepare($connect, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            // 🆕 JSON 방식 추가 옵션 파싱 (전단지/카다록/포스터)
            if (!empty($row['additional_options'])) {
                $additional_options = json_decode($row['additional_options'], true);
                if ($additional_options && is_array($additional_options)) {
                    // JSON 데이터를 개별 필드로 변환하여 기존 코드와 호환
                    $row['coating_enabled'] = $additional_options['coating_enabled'] ?? 0;
                    $row['coating_type'] = $additional_options['coating_type'] ?? '';
                    $row['coating_price'] = $additional_options['coating_price'] ?? 0;
                    $row['folding_enabled'] = $additional_options['folding_enabled'] ?? 0;
                    $row['folding_type'] = $additional_options['folding_type'] ?? '';
                    $row['folding_price'] = $additional_options['folding_price'] ?? 0;
                    $row['creasing_enabled'] = $additional_options['creasing_enabled'] ?? 0;
                    $row['creasing_lines'] = $additional_options['creasing_lines'] ?? 0;
                    $row['creasing_price'] = $additional_options['creasing_price'] ?? 0;
                }
            }

            $items[] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    // 임시로 배열을 전역 변수에 저장하여 mysqli_fetch_assoc처럼 사용
    global $cart_items_array;
    $cart_items_array = $items;

    // 빈 장바구니도 정상 결과로 반환 (false는 실제 오류일 때만)
    return true;
}

// 장바구니 아이템 삭제 (통합 버전)
if (isset($_GET['delete'])) {
    $item_no = $_GET['delete'];

    if (is_numeric($item_no)) {
        $delete_query = "DELETE FROM shop_temp WHERE no = ? AND session_id = ?";
        $stmt = mysqli_prepare($connect, $delete_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'is', $item_no, $session_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: cart.php');
    exit;
}

// 장바구니 비우기 (통합 버전)
if (isset($_GET['clear'])) {
    $clear_query = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($connect, $clear_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('Location: cart.php');
    exit;
}

error_log("Starting to get cart items for session: " . $session_id);

$cart_result = getCartItems($connect, $session_id);
$cart_items = [];
$optionsDisplay = getAdditionalOptionsDisplay($connect);

if ($cart_result === false) {
    $error_message = "장바구니 정보를 불러오는데 실패했습니다. ";
    if ($connect) {
        $error_message .= "DB 오류: " . mysqli_error($connect);
    } else {
        $error_message .= "데이터베이스 연결 실패";
    }
    error_log($error_message);
    echo "<script>alert('" . addslashes($error_message) . "');</script>";
} else {
    // 전역 변수에서 아이템 가져오기
    global $cart_items_array;
    $cart_items = $cart_items_array ?? [];
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 통합 장바구니</title>
    <link rel="stylesheet" href="../../css/style250801.css">
</head>

<body>
    <div class="container" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; box-sizing: border-box; max-width: 1200px; margin: 0 auto; padding: 15px 20px 0px 20px;">
        <!-- 헤더 섹션 -->
        <div class="hero-section" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 0.5rem 0; text-align: center; margin-bottom: 5px; border-radius: 10px;">
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">🛒 통합 장바구니</h1>
            <p style="font-size: 1rem; opacity: 0.9;">모든 인쇄 상품을 한 번에 주문하세요</p>
        </div>

        <!-- 통합 네비게이션 사용 -->
        <?php if (!empty($cart_items)): ?>
            <!-- 장바구니에 상품이 있을 때 -->
            <div style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; margin: 0; box-sizing: border-box; margin-bottom: 0.3rem; padding: 5px 10px; background: white; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center; min-height: auto;">
                <?php include "../../includes/nav.php"; ?>
            </div>
    </div>
<?php else: ?>
    <!-- 빈 장바구니일 때 - 더 유용한 정보와 기능 제공 -->
    <!-- 품목 네비게이션 -->
    <?php include '../../includes/nav.php'; ?>

    <div style="margin-bottom: 5px;"></div>
<?php endif; ?>

<!-- 장바구니 메인 콘텐츠 -->
<div id="cartContent" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; box-sizing: border-box; max-width: 1145px; margin: 0 auto; background: #fdfdfd; border-radius: 8px; padding: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 1rem; border: 1px solid #f0f0f0; width: 1150px;">
    <?php if (!empty($cart_items)): ?>
        <form method="post" action="../../mlangorder_printauto/OnlineOrder_unified.php" id="orderForm">
            <input type="hidden" name="SubmitMode" value="OrderOne">
            <?php
            $total_price = 0;
            $total_vat = 0;
            $items_data = array();
            ?>

            <!-- 파스텔 표 형식 장바구니 -->
            <div style="background: linear-gradient(135deg, #fafbff 0%, #fff9f9 100%); border-radius: 8px; overflow: hidden; border: 1px solid #e8eaed; max-width: 1100px; margin: 0 auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f4ff 0%, #fff0f5 100%); border-bottom: 2px solid #e1d5e7;">
                            <th style="padding: 8px 12px; text-align: left; font-weight: 600; color: #4a5568; border-right: 1px solid #e8eaed; font-size: 13px;">상품정보</th>
                            <th style="padding: 8px 12px; text-align: center; font-weight: 600; color: #4a5568; border-right: 1px solid #e8eaed; min-width: 120px; font-size: 13px;">규격/옵션</th>
                            <th style="padding: 8px 12px; text-align: center; font-weight: 600; color: #4a5568; border-right: 1px solid #e8eaed; min-width: 80px; font-size: 13px;">수량</th>
                            <th style="padding: 8px 12px; text-align: right; font-weight: 600; color: #4a5568; border-right: 1px solid #e8eaed; min-width: 100px; font-size: 13px;">단가</th>
                            <th style="padding: 8px 12px; text-align: right; font-weight: 600; color: #4a5568; border-right: 1px solid #e8eaed; min-width: 120px; font-size: 13px;">총액</th>
                            <th style="padding: 8px 12px; text-align: center; font-weight: 600; color: #4a5568; min-width: 60px; font-size: 13px;">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $index => $item):
                            // 추가 옵션 가격 계산
                            $base_price = intval($item['st_price']);
                            $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $item);
                            $final_price = $price_with_options['total_price'];
                            $final_price_vat = $price_with_options['total_vat'];

                            $total_price += $final_price;
                            $total_vat += $final_price_vat;
                            $items_data[] = $item;

                            // 상품명 매핑
                            $product_info = [
                                'cadarok' => ['name' => '카달로그', 'icon' => '📖', 'color' => '#e3f2fd'],
                                'sticker' => ['name' => '스티커', 'icon' => '🏷️', 'color' => '#f3e5f5'],
                                'msticker' => ['name' => '자석스티커', 'icon' => '🧲', 'color' => '#e8f5e8'],
                                'leaflet' => ['name' => '전단지', 'icon' => '📄', 'color' => '#fff3e0'],
                                'inserted' => ['name' => '전단지', 'icon' => '📄', 'color' => '#fff3e0'],
                                'namecard' => ['name' => '명함', 'icon' => '💼', 'color' => '#fce4ec'],
                                'envelope' => ['name' => '봉투', 'icon' => '✉️', 'color' => '#e0f2f1'],
                                'merchandisebond' => ['name' => '상품권', 'icon' => '🎫', 'color' => '#f1f8e9'],
                                'littleprint' => ['name' => '포스터', 'icon' => '🎨', 'color' => '#e8eaf6']
                            ];

                            $product = $product_info[$item['product_type']] ?? ['name' => '상품', 'icon' => '📦', 'color' => '#f5f5f5'];
                            $row_bg = $index % 2 == 0 ? '#fdfdfd' : '#f9f9fb';
                        ?>
                            <tr style="background: <?php echo $row_bg; ?>; border-bottom: 1px solid #e8eaed; transition: background-color 0.2s ease;" onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background='<?php echo $row_bg; ?>'">
                                <!-- 상품정보 -->
                                <td style="padding: 12px; border-right: 1px solid #e8eaed; vertical-align: top;">
                                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                                        <div style="background: <?php echo $product['color']; ?>; padding: 8px; border-radius: 6px; font-size: 18px; line-height: 1; min-width: 36px; text-align: center;">
                                            <?php echo $product['icon']; ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: #2d3748; margin-bottom: 4px; font-size: 15px;"><?php echo $product['name']; ?></div>
                                            <div style="color: #718096; font-size: 12px;">상품번호: #<?php echo $item['no']; ?></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- 규격/옵션 -->
                                <td style="padding: 12px; border-right: 1px solid #e8eaed; vertical-align: top; text-align: center;">
                                    <div style="font-size: 13px; line-height: 1.4;">
                                        <?php if ($item['product_type'] == 'sticker'): ?>
                                            <?php if (!empty($item['jong'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #805ad5; font-weight: 500;">재질:</span> <?php echo htmlspecialchars($item['jong']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['garo']) && !empty($item['sero'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #805ad5; font-weight: 500;">크기:</span> <?php echo htmlspecialchars($item['garo']); ?>×<?php echo htmlspecialchars($item['sero']); ?>mm</div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['domusong'])): ?>
                                                <div style="color: #4a5568;"><span style="color: #805ad5; font-weight: 500;">모양:</span> <?php echo htmlspecialchars($item['domusong']); ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($item['product_type'] == 'msticker'): ?>
                                            <?php if (!empty($item['MY_type'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #38a169; font-weight: 500;">종류:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['Section'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #38a169; font-weight: 500;">규격:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['Section'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['POtype'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #38a169; font-weight: 500;">인쇄:</span> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['ordertype'])): ?>
                                                <div style="color: #4a5568;"><span style="color: #38a169; font-weight: 500;">타입:</span> <?php echo $item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만'; ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($item['product_type'] == 'namecard'): ?>
                                            <?php if (!empty($item['MY_type'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #d69e2e; font-weight: 500;">타입:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['Section'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #d69e2e; font-weight: 500;">재질:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['Section'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['POtype'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #d69e2e; font-weight: 500;">인쇄:</span> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></div>
                                            <?php endif; ?>

                                            <!-- 🆕 명함 프리미엄 옵션 표시 -->
                                            <?php if (!empty($item['premium_options'])): ?>
                                                <?php
                                                $premium_options = json_decode($item['premium_options'], true);
                                                if ($premium_options && isset($premium_options['premium_options_total']) && $premium_options['premium_options_total'] > 0):
                                                    $selected_options = [];

                                                    if (isset($premium_options['foil_enabled']) && $premium_options['foil_enabled']) {
                                                        $foil_type_code = $premium_options['foil_type'] ?? '';
                                                        // 영문 코드를 한글로 변환
                                                        $foil_types = [
                                                            'gold_matte' => '금박무광',
                                                            'gold_gloss' => '금박유광',
                                                            'silver_matte' => '은박무광',
                                                            'silver_gloss' => '은박유광',
                                                            'blue_gloss' => '청박유광',
                                                            'red_gloss' => '적박유광',
                                                            'green_gloss' => '녹박유광',
                                                            'black_gloss' => '먹박유광'
                                                        ];
                                                        $foil_type = $foil_types[$foil_type_code] ?? $foil_type_code;
                                                        $selected_options[] = '박(' . $foil_type . ')';
                                                    }
                                                    if (isset($premium_options['numbering_enabled']) && $premium_options['numbering_enabled']) {
                                                        $numbering_count = $premium_options['numbering_count'] ?? '1개';
                                                        $selected_options[] = '넘버링(' . $numbering_count . ')';
                                                    }
                                                    if (isset($premium_options['perforation_enabled']) && $premium_options['perforation_enabled']) {
                                                        $perforation_count = $premium_options['perforation_count'] ?? '1개';
                                                        $selected_options[] = '미싱(' . $perforation_count . ')';
                                                    }
                                                    if (isset($premium_options['rounding_enabled']) && $premium_options['rounding_enabled']) {
                                                        $selected_options[] = '귀돌이';
                                                    }
                                                    if (isset($premium_options['creasing_enabled']) && $premium_options['creasing_enabled']) {
                                                        $creasing_lines = $premium_options['creasing_lines'] ?? '';
                                                        if (!empty($creasing_lines)) {
                                                            $selected_options[] = '미싱(' . $creasing_lines . '줄)';
                                                        } else {
                                                            $selected_options[] = '미싱( )';
                                                        }
                                                    }

                                                    if (!empty($selected_options)):
                                                ?>
                                                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                                            <div style="color: #d69e2e; font-weight: 600; font-size: 12px; margin-bottom: 4px;">✨ 프리미엄 옵션</div>
                                                            <div style="color: #2d3748; font-size: 11px; line-height: 1.4;">
                                                                <?php echo implode(', ', $selected_options); ?>
                                                                <span style="color: #38a169; font-weight: 600; margin-left: 4px;">(+<?php echo number_format($premium_options['premium_options_total'] ?? 0); ?>원)</span>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                        <?php elseif ($item['product_type'] == 'merchandisebond'): ?>
                                            <?php if (!empty($item['MY_type'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #e91e63; font-weight: 500;">타입:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['Section'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #e91e63; font-weight: 500;">재질:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['Section'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['POtype'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #e91e63; font-weight: 500;">인쇄:</span> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></div>
                                            <?php endif; ?>

                                            <!-- 🆕 상품권 프리미엄 옵션 표시 -->
                                            <?php if (!empty($item['premium_options'])): ?>
                                                <?php
                                                $premium_options = json_decode($item['premium_options'], true);
                                                if ($premium_options && isset($premium_options['premium_options_total']) && $premium_options['premium_options_total'] > 0):
                                                    $selected_options = [];

                                                    if (isset($premium_options['foil_enabled']) && $premium_options['foil_enabled']) {
                                                        $foil_type_code = $premium_options['foil_type'] ?? '';
                                                        // 영문 코드를 한글로 변환
                                                        $foil_types = [
                                                            'gold_matte' => '금박무광',
                                                            'gold_gloss' => '금박유광',
                                                            'silver_matte' => '은박무광',
                                                            'silver_gloss' => '은박유광',
                                                            'blue_gloss' => '청박유광',
                                                            'red_gloss' => '적박유광',
                                                            'green_gloss' => '녹박유광',
                                                            'black_gloss' => '먹박유광'
                                                        ];
                                                        $foil_type = $foil_types[$foil_type_code] ?? $foil_type_code;
                                                        $selected_options[] = '박(' . $foil_type . ')';
                                                    }
                                                    if (isset($premium_options['numbering_enabled']) && $premium_options['numbering_enabled']) {
                                                        $numbering_count = $premium_options['numbering_count'] ?? '1개';
                                                        $selected_options[] = '넘버링(' . $numbering_count . ')';
                                                    }
                                                    if (isset($premium_options['perforation_enabled']) && $premium_options['perforation_enabled']) {
                                                        $perforation_count = $premium_options['perforation_count'] ?? '1개';
                                                        $selected_options[] = '미싱(' . $perforation_count . ')';
                                                    }
                                                    if (isset($premium_options['rounding_enabled']) && $premium_options['rounding_enabled']) {
                                                        $selected_options[] = '귀돌이';
                                                    }
                                                    if (isset($premium_options['creasing_enabled']) && $premium_options['creasing_enabled']) {
                                                        $creasing_lines = $premium_options['creasing_lines'] ?? '';
                                                        if (!empty($creasing_lines)) {
                                                            $selected_options[] = '미싱(' . $creasing_lines . '줄)';
                                                        } else {
                                                            $selected_options[] = '미싱( )';
                                                        }
                                                    }

                                                    if (!empty($selected_options)):
                                                ?>
                                                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                                            <div style="color: #e91e63; font-weight: 600; font-size: 12px; margin-bottom: 4px;">✨ 프리미엄 옵션</div>
                                                            <div style="color: #2d3748; font-size: 11px; line-height: 1.4;">
                                                                <?php echo implode(', ', $selected_options); ?>
                                                                <span style="color: #38a169; font-weight: 600; margin-left: 4px;">(+<?php echo number_format($premium_options['premium_options_total'] ?? 0); ?>원)</span>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php elseif ($item['product_type'] == 'ncrflambeau'): ?>
                                            <?php if (!empty($item['MY_type'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #9333ea; font-weight: 500;">색상:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['MY_Fsd'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #9333ea; font-weight: 500;">종류:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_Fsd'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['PN_type'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #9333ea; font-weight: 500;">규격:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['ordertype'])): ?>
                                                <div style="color: #4a5568;"><span style="color: #9333ea; font-weight: 500;">타입:</span> <?php echo $item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])); ?></div>
                                            <?php endif; ?>

                                            <!-- 🆕 양식지 옵션 표시 (넘버링 + 미싱) -->
                                            <?php if (!empty($item['premium_options'])): ?>
                                                <?php
                                                $premium_options = json_decode($item['premium_options'], true);
                                                if ($premium_options && isset($premium_options['additional_options_total']) && $premium_options['additional_options_total'] > 0):
                                                    $selected_options = [];

                                                    // 넘버링 (folding_enabled로 저장됨)
                                                    if (isset($premium_options['folding_enabled']) && $premium_options['folding_enabled']) {
                                                        $folding_type = $premium_options['folding_type'] ?? '';
                                                        $folding_price = intval($premium_options['folding_price'] ?? 0);
                                                        if (!empty($folding_type)) {
                                                            if ($folding_type === 'numbering') {
                                                                $selected_options[] = '넘버링 (전화문의 1688-2384)';
                                                            } else {
                                                                // 넘버링 타입을 한글로 변환
                                                                $folding_types = [
                                                                    '1' => '넘버링 1줄',
                                                                    '2' => '넘버링 2줄',
                                                                    '3' => '넘버링 3줄'
                                                                ];
                                                                $folding_label = $folding_types[$folding_type] ?? getKoreanName($connect, $folding_type);
                                                                if ($folding_price > 0) {
                                                                    $selected_options[] = $folding_label . ' (+' . number_format($folding_price) . '원)';
                                                                } else {
                                                                    $selected_options[] = $folding_label;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // 미싱 (creasing_enabled로 저장됨)
                                                    if (isset($premium_options['creasing_enabled']) && $premium_options['creasing_enabled']) {
                                                        $creasing_lines = $premium_options['creasing_lines'] ?? '';
                                                        $creasing_price = intval($premium_options['creasing_price'] ?? 0);
                                                        if (!empty($creasing_lines)) {
                                                            // 미싱 줄수 직접 표시 (1, 2, 3)
                                                            $selected_options[] = '미싱 ' . $creasing_lines . '줄 (+' . number_format($creasing_price) . '원)';
                                                        }
                                                    }

                                                    if (!empty($selected_options)):
                                                ?>
                                                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                                            <div style="color: #9333ea; font-weight: 600; font-size: 12px; margin-bottom: 4px;">📎 추가옵션</div>
                                                            <div style="color: #2d3748; font-size: 11px; line-height: 1.4;">
                                                                <?php echo implode(', ', $selected_options); ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                        <?php elseif ($item['product_type'] == 'envelope'): ?>
                                            <?php if (!empty($item['MY_type_name']) || !empty($item['MY_type'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #00b4d8; font-weight: 500;">종류:</span> <?php echo htmlspecialchars($item['MY_type_name'] ?: getKoreanName($connect, $item['MY_type'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['Section_name']) || !empty($item['Section'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #00b4d8; font-weight: 500;">재질:</span> <?php echo htmlspecialchars($item['Section_name'] ?: getKoreanName($connect, $item['Section'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['POtype_name']) || !empty($item['POtype'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #00b4d8; font-weight: 500;">인쇄:</span> <?php echo htmlspecialchars($item['POtype_name'] ?: ($item['POtype'] == '1' ? '단면' : '양면')); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['ordertype'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #00b4d8; font-weight: 500;">타입:</span> <?php echo $item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])); ?></div>
                                            <?php endif; ?>

                                            <!-- 🆕 봉투 양면테이프 옵션 표시 -->
                                            <?php if (!empty($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == '1'): ?>
                                                <div style="background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%); padding: 8px; border-radius: 6px; margin-top: 8px; border: 1px solid #90ee90;">
                                                    <div style="font-weight: 600; color: #2e7d32; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                                                        <span>📎</span> 양면테이프 옵션
                                                    </div>
                                                    <?php if (!empty($item['envelope_tape_quantity'])): ?>
                                                        <div style="font-size: 12px; color: #388e3c;">수량: <?php echo htmlspecialchars($item['envelope_tape_quantity']); ?>매</div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['envelope_tape_price'])): ?>
                                                        <div style="font-size: 12px; color: #388e3c;">가격: <?php echo number_format($item['envelope_tape_price']); ?>원</div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (!empty($item['MY_type'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #3182ce; font-weight: 500;">색상:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['MY_Fsd'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #3182ce; font-weight: 500;">종류:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_Fsd'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['PN_type'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #3182ce; font-weight: 500;">규격:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['Section'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #3182ce; font-weight: 500;">재질:</span> <?php echo htmlspecialchars(getKoreanName($connect, $item['Section'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['POtype'])): ?>
                                                <div style="margin-bottom: 6px; color: #4a5568;"><span style="color: #3182ce; font-weight: 500;">인쇄:</span> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['ordertype'])): ?>
                                                <div style="color: #4a5568;"><span style="color: #3182ce; font-weight: 500;">타입:</span> <?php echo $item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])); ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- 📎 추가 옵션 정보 표시 -->
                                        <?php
                                        $options_details = $optionsDisplay->getOrderDetails($item);
                                        if (!empty($options_details['options'])):
                                        ?>
                                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                                <div style="color: #e53e3e; font-weight: 600; font-size: 12px; margin-bottom: 4px;">📎 추가옵션</div>
                                                <?php foreach ($options_details['options'] as $option): ?>
                                                    <div style="color: #2d3748; font-size: 11px; margin-bottom: 2px;">
                                                        <span style="color: #e53e3e; font-weight: 500;"><?php echo $option['category']; ?>:</span>
                                                        <?php echo $option['name']; ?>
                                                        <span style="color: #38a169; font-weight: 600;">(+<?php echo $option['formatted_price']; ?>)</span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- 수량 -->
                                <td style="padding: 12px; border-right: 1px solid #e8eaed; vertical-align: middle; text-align: center;">
                                    <div style="font-weight: 600; color: #2d3748; font-size: 15px;">
                                        <?php
                                        // 양식지(ncrflambeau)는 "권" 단위 사용
                                        $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';

                                        // 우선순위: quantity_display > mesu > MY_amount > 에러
                                        if (!empty($item['quantity_display'])) {
                                            // Phase 3 표준 필드 우선 사용
                                            echo htmlspecialchars($item['quantity_display']);
                                        } elseif (isset($item['mesu']) && $item['mesu'] !== '' && $item['mesu'] !== '0' && $item['mesu'] !== 0) {
                                            // 스티커 전용 필드
                                            echo number_format(floatval($item['mesu'])) . $unit;
                                        } elseif (isset($item['MY_amount']) && $item['MY_amount'] !== '' && $item['MY_amount'] !== '0' && $item['MY_amount'] !== 0) {
                                            // 봉투/명함 전용 필드
                                            $qty = floatval($item['MY_amount']);
                                            // ⚠️ 사용자 요구: 계산 금지, DB 값 그대로 표시
                                            echo number_format($qty) . $unit;
                                        } else {
                                            // 마지막 fallback - 에러 표시 및 로깅
                                            error_log("cart.php ERROR: 수량 필드 없음 - no={$item['no']}, product_type={$item['product_type']}, session_id=" . ($item['session_id'] ?? 'N/A'));
                                            echo '<span style="color:red; font-weight:bold;">수량 미지정</span>';
                                        }
                                        ?>
                                    </div>
                                </td>

                                <!-- 단가 -->
                                <td style="padding: 12px; border-right: 1px solid #e8eaed; vertical-align: middle; text-align: right;">
                                    <div style="color: #4a5568; font-size: 13px; margin-bottom: 2px;">부가세별도</div>
                                    <div style="font-weight: 600; color: #2d3748; font-size: 14px;"><?php echo number_format($final_price); ?>원</div>
                                </td>

                                <!-- 총액 -->
                                <td style="padding: 12px; border-right: 1px solid #e8eaed; vertical-align: middle; text-align: right;">
                                    <div style="color: #4a5568; font-size: 13px; margin-bottom: 2px;">부가세포함</div>
                                    <div style="font-weight: 700; color: #e53e3e; font-size: 16px;"><?php echo number_format($final_price_vat); ?>원</div>
                                </td>

                                <!-- 관리 -->
                                <td style="padding: 12px; vertical-align: middle; text-align: center;">
                                    <a href="?delete=<?php echo $item['no']; ?>"
                                        onclick="return confirm('이 상품을 삭제하시겠습니까?')"
                                        style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #fed7d7; color: #e53e3e; text-decoration: none; border-radius: 6px; font-size: 14px; transition: all 0.2s ease; border: 1px solid #feb2b2;"
                                        onmouseover="this.style.background='#fc8181'; this.style.color='white'; this.style.transform='scale(1.1)'"
                                        onmouseout="this.style.background='#fed7d7'; this.style.color='#e53e3e'; this.style.transform='scale(1)'">
                                        ✕
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 요약 정보 -->
            <div style="margin-top: 20px; background: linear-gradient(135deg, #f7faff 0%, #fdf2f8 100%); border-radius: 8px; padding: 20px; border: 1px solid #e2e8f0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div style="color: #4a5568; font-weight: 600; font-size: 16px;">📋 주문 요약</div>
                    <div style="color: #718096; font-size: 13px;">총 <?php echo count($cart_items); ?>개 상품</div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div style="text-align: center; padding: 12px; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="color: #718096; font-size: 12px; margin-bottom: 4px;">상품금액</div>
                        <div style="color: #2d3748; font-weight: 600; font-size: 15px;"><?php echo number_format($total_price); ?>원</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="color: #718096; font-size: 12px; margin-bottom: 4px;">부가세</div>
                        <div style="color: #2d3748; font-weight: 600; font-size: 15px;"><?php echo number_format($total_vat - $total_price); ?>원</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 6px; color: white;">
                        <div style="opacity: 0.9; font-size: 12px; margin-bottom: 4px;">총 결제금액</div>
                        <div style="font-weight: 700; font-size: 18px;"><?php echo number_format($total_vat); ?>원</div>
                    </div>
                </div>

                <!-- Hidden 필드들 -->
                <?php foreach ($items_data as $index => $item): ?>
                    <input type="hidden" name="product_type[]" value="<?php echo htmlspecialchars($item['product_type']); ?>">
                    <input type="hidden" name="price[]" value="<?php echo htmlspecialchars($item['st_price']); ?>">
                    <input type="hidden" name="price_vat[]" value="<?php echo htmlspecialchars($item['st_price_vat']); ?>">
                <?php endforeach; ?>

                <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                <input type="hidden" name="total_price_vat" value="<?php echo $total_vat; ?>">
                <input type="hidden" name="items_count" value="<?php echo count($items_data); ?>">

                <!-- 컴팩트 버튼 그룹 -->
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" onclick="continueShopping()" class="btn-continue" style="padding: 10px 20px; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(72,187,120,0.3);" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(72,187,120,0.4)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 4px rgba(72,187,120,0.3)'">
                        🛍️ 계속 쇼핑
                    </button>
                    <button type="button" onclick="showQuotation()" class="btn-quote" style="padding: 10px 20px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(66,153,225,0.3);" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(66,153,225,0.4)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 4px rgba(66,153,225,0.3)'">
                        📄 견적서 받기
                    </button>
                    <button type="submit" class="btn-order" style="padding: 12px 32px; background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; border: none; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 3px 6px rgba(245,101,101,0.4);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(245,101,101,0.5)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 6px rgba(245,101,101,0.4)'">
                        📋 주문하기
                    </button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <!-- 빈 장바구니 상태 - 개선된 UI -->
        <div style="text-align: center; padding: 3rem 2rem;">
            <div style="font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.8;">📭</div>
            <h3 style="font-size: 1.8rem; margin-bottom: 1rem; color: #495057; font-weight: 600;">장바구니가 비어있습니다</h3>
            <p style="margin-bottom: 3rem; color: #6c757d; font-size: 1.1rem;">원하시는 인쇄물을 선택해서 주문을 시작해보세요!</p>

            <!-- 도움말 및 정보 섹션 -->
            <div style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); padding: 2rem; border-radius: 15px; margin-bottom: 3rem; border: 1px solid #e1bee7;">
                <h4 style="color: #6a1b9a; margin-bottom: 1rem; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
                    <span style="margin-right: 10px;">💡</span>두손기획인쇄 이용 안내
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; text-align: left;">
                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <h5 style="color: #1976d2; margin-bottom: 0.8rem; font-size: 1rem; display: flex; align-items: center;">
                            <span style="margin-right: 8px;">🏆</span>품질 보장
                        </h5>
                        <ul style="margin: 0; padding-left: 1.2rem; color: #555; font-size: 0.9rem; line-height: 1.6;">
                            <li>20년 이상의 인쇄 경험</li>
                            <li>고품질 인쇄 장비 사용</li>
                            <li>전문 디자이너 상주</li>
                        </ul>
                    </div>

                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <h5 style="color: #388e3c; margin-bottom: 0.8rem; font-size: 1rem; display: flex; align-items: center;">
                            <span style="margin-right: 8px;">🚚</span>빠른 배송
                        </h5>
                        <ul style="margin: 0; padding-left: 1.2rem; color: #555; font-size: 0.9rem; line-height: 1.6;">
                            <li>당일 출고 (품목에 따라 오전 11시 이전 주문)</li>
                            <li>전국 택배 배송</li>
                            <li>방문 수령 가능</li>
                        </ul>
                    </div>

                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <h5 style="color: #f57c00; margin-bottom: 0.8rem; font-size: 1rem; display: flex; align-items: center;">
                            <span style="margin-right: 8px;">💰</span>합리적 가격
                        </h5>
                        <ul style="margin: 0; padding-left: 1.2rem; color: #555; font-size: 0.9rem; line-height: 1.6;">
                            <li>실시간 가격 계산</li>
                            <li>대량 주문 할인</li>
                            <li>투명한 가격 정책</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 연락처 정보 -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; border-left: 4px solid #007bff;">
                <h4 style="color: #007bff; margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">
                    <span style="margin-right: 8px;">📞</span>문의사항이 있으시면 언제든 연락하세요
                </h4>
                <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; align-items: center;">
                    <div style="display: flex; align-items: center; color: #495057;">
                        <span style="margin-right: 8px; font-size: 1.2rem;">☎️</span>
                        <span style="font-weight: 600;">02-2632-1830</span>
                    </div>
                    <div style="display: flex; align-items: center; color: #495057;">
                        <span style="margin-right: 8px; font-size: 1.2rem;">📱</span>
                        <span style="font-weight: 600;">1688-2384</span>
                    </div>
                    <div style="display: flex; align-items: center; color: #495057;">
                        <span style="margin-right: 8px; font-size: 1.2rem;">🕘</span>
                        <span>평일 09:00~18:00</span>
                    </div>
                </div>
            </div>

            <!-- 쇼핑 시작 버튼 -->
            <div style="text-align: center; margin-top: 2rem;">
                <button onclick="continueShopping()" style="padding: 18px 40px; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; border: none; border-radius: 50px; font-size: 1.2rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,123,255,0.3); min-width: 200px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,123,255,0.4)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 4px 15px rgba(0,123,255,0.3)'">
                    🛍️ 인쇄 주문 시작하기
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 📄 견적서 섹션 (기본적으로 숨겨짐) - A4 공식 양식 -->
<?php
// 한글 금액 변환 함수
$korean_num = ['', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];
$korean_unit = ['', '십', '백', '천'];

function convertUnder10000($num) {
    global $korean_num, $korean_unit;
    if ($num == 0) return '';

    $result = '';
    $unit_idx = 0;

    while ($num > 0) {
        $digit = $num % 10;
        if ($digit > 0) {
            if ($digit == 1 && $unit_idx > 0) {
                $result = $korean_unit[$unit_idx] . $result;
            } else {
                $result = $korean_num[$digit] . $korean_unit[$unit_idx] . $result;
            }
        }
        $num = floor($num / 10);
        $unit_idx++;
    }
    return $result;
}

function numberToKorean($num) {
    global $korean_num, $korean_unit;
    if ($num == 0) return '영';

    $eok = floor($num / 100000000);
    $man = floor(($num % 100000000) / 10000);
    $rest = $num % 10000;

    $result = '';

    if ($eok > 0) {
        $result .= convertUnder10000($eok) . '억';
    }
    if ($man > 0) {
        $result .= convertUnder10000($man) . '만';
    }
    if ($rest > 0) {
        $result .= convertUnder10000($rest);
    }

    return $result;
}
?>
<style>
/* 화면: 인쇄용 span 숨김 */
.print-value { display: none; }

/* A4 인쇄용 스타일 */
@media print {
    body * { visibility: hidden; }
    #quotationSection, #quotationSection * { visibility: visible; }
    #quotationSection {
        position: absolute;
        left: 0;
        top: 0;
        width: 210mm;
        padding: 10mm;
        margin: 0;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    .no-print { display: none !important; }

    /* 인쇄 시: 담당자/택배선불 입력필드 완전히 숨김 */
    #quotationSection #customerNameInput,
    #quotationSection #deliveryType {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
    }
    .print-value { display: inline !important; font-weight: normal; }

    /* 입력 필드가 인쇄 시 값이 보이도록 (담당자/택배제외) */
    #quotationSection input[type="text"]:not(#customerNameInput),
    #quotationSection input[type="number"],
    #quotationSection select:not(#deliveryType) {
        border: none !important;
        background: transparent !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        box-shadow: none !important;
        padding: 0 !important;
        font-size: inherit !important;
        color: #000 !important;
    }
    #quotationSection select:not(#deliveryType) {
        background-image: none !important;
    }
}
</style>
<div id="quotationSection" style="display: none; font-family: 'Malgun Gothic', '맑은 고딕', sans-serif; line-height: 1.4; box-sizing: border-box; max-width: 210mm; margin: 0 auto; background: white; padding: 10mm; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 1rem;">

    <div style="position: relative; top: 0; left: 0; font-size: 10px; color: #666;">No.</div>

    <!-- 견적서 헤더 -->
    <div style="text-align: center; margin: 15px 0 10px 0;">
        <h1 style="font-size: 28px; margin: 0; letter-spacing: 12px; font-weight: bold;">견 적 서</h1>
    </div>

    <?php if (!empty($cart_items)): ?>
        <?php
        // 먼저 합계 계산
        $quote_total = 0;
        $quote_total_vat = 0;
        foreach ($cart_items as $item) {
            $base_price = intval($item['st_price']);
            $has_additional_options = isset($item['coating_price']) || isset($item['folding_price']) || isset($item['creasing_price']);

            if ($has_additional_options) {
                $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $item);
                $quote_total += $price_with_options['total_price'];
                $quote_total_vat += $price_with_options['total_vat'];
            } else {
                $quote_total += $base_price;
                $quote_total_vat += intval($item['st_price_vat']);
            }
        }
        ?>

        <!-- 상단 정보 테이블 (공급자 정보 포함) -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 13px;">
            <tr>
                <td style="border: 1px solid #000; padding: 6px; width: 12%; font-weight: bold; background: #f0f0f0;">견적일</td>
                <td style="border: 1px solid #000; padding: 6px; width: 28%;"><?php echo date('Y년 m월 d일'); ?></td>
                <td rowspan="5" colspan="4" style="border: 1px solid #000; padding: 0; width: 45%; vertical-align: top;">
                    <table style="width: 100%; height: 100%; border-collapse: collapse; font-size: 13px;">
                        <tr>
                            <td colspan="4" style="border-bottom: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; background: #f0f0f0;">공 급 자</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px; width: 22%;">등록번호</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px; width: 28%; font-weight: bold;">107-06-45106</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px; width: 22%;">대표자</td>
                            <td style="border-bottom: 1px solid #000; padding: 3px; width: 28%;">차경선(직인생략)</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px;">상 호</td>
                            <td colspan="3" style="border-bottom: 1px solid #000; padding: 3px; font-weight: bold;">두손기획인쇄</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px;">주 소</td>
                            <td colspan="3" style="border-bottom: 1px solid #000; padding: 3px;">서울 영등포구 영등포로36길9 송호빌딩 1층</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px;">연락처</td>
                            <td colspan="3" style="border-bottom: 1px solid #000; padding: 3px;">02-2632-1830</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; padding: 3px;">업 태</td>
                            <td style="border-right: 1px solid #000; padding: 3px;">제조</td>
                            <td style="border-right: 1px solid #000; padding: 3px;">종 목</td>
                            <td style="padding: 3px;">인쇄업외</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 6px; font-weight: bold; background: #f0f0f0;">담당자</td>
                <td style="border: 1px solid #000; padding: 6px;">
                    <input type="text" id="customerNameInput" placeholder="담당자명" oninput="updatePrintValues()"
                           style="border: 1px solid #ccc; padding: 2px 5px; width: 80px; font-size: 13px;"><span class="print-value" id="customerNamePrint"></span> 귀하
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 6px; font-weight: bold; background: #f0f0f0;">유효기간</td>
                <td style="border: 1px solid #000; padding: 6px;">발행일로부터 7일</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="2" style="border: 1px solid #000; padding: 6px; text-align: center; font-weight: bold; font-size: 16px; vertical-align: middle; background: #f8f8f8;">
                    합계금액<br>(부가세포함)
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 10px; text-align: center; font-size: 14px;" id="koreanAmountCell">
                    일금 <span id="koreanAmountText"><?php echo numberToKorean($quote_total_vat); ?></span>원정<br>( ₩<span id="numericAmountText"><?php echo number_format($quote_total_vat); ?></span> )
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 10px; text-align: center; font-size: 22px; font-weight: bold;" id="topTotalAmount">
                    <?php echo number_format($quote_total_vat); ?> 원
                </td>
            </tr>
        </table>

        <!-- 품목 테이블 -->
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th style="border: 1px solid #000; padding: 5px; width: 5%;">NO</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 15%;">품 목</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 25%;">규격 및 사양</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 10%;">수량</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 7%;">단위</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 13%;">단가</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 15%;">공급가액(VAT별도)</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 10%;">비 고</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($cart_items as $index => $item):
                    // 가격 계산 (장바구니와 동일한 로직)
                    $base_price = intval($item['st_price']);
                    $has_additional_options = isset($item['coating_price']) || isset($item['folding_price']) || isset($item['creasing_price']);

                    if ($has_additional_options) {
                        $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $item);
                        $final_price = $price_with_options['total_price'];
                        $final_price_vat = $price_with_options['total_vat'];
                    } else {
                        $final_price = $base_price;
                        $final_price_vat = intval($item['st_price_vat']);
                    }

                    $product_info = [
                        'cadarok' => '카달로그',
                        'sticker' => '스티커',
                        'msticker' => '자석스티커',
                        'leaflet' => '전단지',
                        'namecard' => '명함',
                        'envelope' => '봉투',
                        'merchandisebond' => '상품권',
                        'littleprint' => '포스터',
                        'ncrflambeau' => '양식지',
                        'inserted' => '전단지'
                    ];
                    $product_name = $product_info[$item['product_type']] ?? '인쇄상품';
                ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;"><?php echo $index + 1; ?></td>
                        <td style="border: 1px solid #000; padding: 4px;"><?php echo $product_name; ?></td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                            <?php if ($item['product_type'] == 'envelope'): ?>
                                <?php if (!empty($item['MY_type_name']) || !empty($item['MY_type'])): ?>
                                    <div><strong>종류:</strong> <?php echo htmlspecialchars($item['MY_type_name'] ?: getKoreanName($connect, $item['MY_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['Section_name']) || !empty($item['Section'])): ?>
                                    <div><strong>재질:</strong> <?php echo htmlspecialchars($item['Section_name'] ?: getKoreanName($connect, $item['Section'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['POtype_name']) || !empty($item['POtype'])): ?>
                                    <div><strong>인쇄:</strong> <?php echo htmlspecialchars($item['POtype_name'] ?: ($item['POtype'] == '1' ? '단면' : '양면')); ?></div>
                                <?php endif; ?>
                            <?php elseif ($item['product_type'] == 'ncrflambeau'): ?>
                                <?php if (!empty($item['MY_type'])): ?>
                                    <div><strong>색상:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['MY_Fsd'])): ?>
                                    <div><strong>종류:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_Fsd'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['PN_type'])): ?>
                                    <div><strong>규격:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['ordertype'])): ?>
                                    <div><strong>타입:</strong> <?php echo $item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])); ?></div>
                                <?php endif; ?>

                                <!-- 🆕 양식지 추가옵션 (견적서용) -->
                                <?php if (!empty($item['premium_options'])): ?>
                                    <?php
                                    $premium_options = json_decode($item['premium_options'], true);
                                    if ($premium_options && isset($premium_options['additional_options_total']) && $premium_options['additional_options_total'] > 0):
                                        $selected_options = [];

                                        // 넘버링 (folding_enabled로 저장됨)
                                        if (isset($premium_options['folding_enabled']) && $premium_options['folding_enabled']) {
                                            $folding_type = $premium_options['folding_type'] ?? '';
                                            $folding_price = intval($premium_options['folding_price'] ?? 0);
                                            if (!empty($folding_type)) {
                                                if ($folding_type === 'numbering') {
                                                    $selected_options[] = '넘버링 (전화문의 1688-2384)';
                                                } else {
                                                    $folding_types = [
                                                        '1' => '넘버링 1줄',
                                                        '2' => '넘버링 2줄',
                                                        '3' => '넘버링 3줄'
                                                    ];
                                                    $folding_label = $folding_types[$folding_type] ?? getKoreanName($connect, $folding_type);
                                                    if ($folding_price > 0) {
                                                        $selected_options[] = $folding_label . ' (+' . number_format($folding_price) . '원)';
                                                    } else {
                                                        $selected_options[] = $folding_label;
                                                    }
                                                }
                                            }
                                        }

                                        // 미싱 (creasing_enabled로 저장됨)
                                        if (isset($premium_options['creasing_enabled']) && $premium_options['creasing_enabled']) {
                                            $creasing_lines = $premium_options['creasing_lines'] ?? '';
                                            $creasing_price = intval($premium_options['creasing_price'] ?? 0);
                                            if (!empty($creasing_lines)) {
                                                // 미싱 줄수 직접 표시 (1, 2, 3)
                                                $selected_options[] = '미싱 ' . $creasing_lines . '줄 (+' . number_format($creasing_price) . '원)';
                                            }
                                        }

                                        if (!empty($selected_options)):
                                    ?>
                                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                                <strong style="color: #9333ea;">추가옵션:</strong><br>
                                                <?php echo implode(', ', $selected_options); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php elseif ($item['product_type'] == 'sticker'): ?>
                                <!-- 스티커: jong, garo, sero, domusong 필드 사용 -->
                                <?php
                                    $jong = $item['jong'] ?? '';
                                    // "jil " 접두어 제거
                                    $jong = preg_replace('/^jil\s*/i', '', $jong);
                                    if (!empty($jong)):
                                ?>
                                    <div><strong>재질:</strong> <?php echo htmlspecialchars($jong); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['garo']) && !empty($item['sero'])): ?>
                                    <div><strong>크기:</strong> <?php echo htmlspecialchars($item['garo']); ?>mm × <?php echo htmlspecialchars($item['sero']); ?>mm</div>
                                <?php endif; ?>
                                <?php
                                    $domusong = $item['domusong'] ?? '';
                                    // "00000 사각" → "사각", "0" → 빈값
                                    $domusong = preg_replace('/^[0\s]+/', '', $domusong);
                                    if (!empty($domusong)):
                                ?>
                                    <div><strong>모양:</strong> <?php echo htmlspecialchars($domusong); ?></div>
                                <?php endif; ?>
                            <?php elseif ($item['product_type'] == 'msticker'): ?>
                                <!-- 자석스티커: MY_type, Section, POtype 필드 사용 -->
                                <?php if (!empty($item['MY_type'])): ?>
                                    <div><strong>종류:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['Section'])): ?>
                                    <div><strong>규격:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['Section'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['POtype'])): ?>
                                    <div><strong>인쇄:</strong> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- 기타 제품: MY_type, PN_type, Section, POtype -->
                                <?php if (!empty($item['MY_type'])): ?>
                                    <div><strong>종류:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['PN_type'])): ?>
                                    <div><strong>규격:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['Section'])): ?>
                                    <div><strong>재질:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['Section'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['POtype'])): ?>
                                    <div><strong>인쇄:</strong> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- 추가 옵션 정보 표시 (일반 제품용) -->
                            <?php if ($has_additional_options && $item['product_type'] != 'ncrflambeau'): ?>
                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                    <strong style="color: #e53e3e;">추가옵션:</strong><br>
                                    <?php echo $optionsDisplay->getCartColumnHtml($item); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            <?php
                            // 우선순위: quantity_display > mesu > MY_amount > 에러
                            // Note: 단위는 다음 컬럼에 별도 표시됨 (line 1119)
                            if (!empty($item['quantity_display'])) {
                                // Phase 3 표준 필드 - 단위 포함된 경우 그대로 표시
                                echo htmlspecialchars($item['quantity_display']);
                            } elseif (isset($item['mesu']) && $item['mesu'] !== '' && $item['mesu'] !== '0' && $item['mesu'] !== 0) {
                                // 스티커 전용 필드
                                echo number_format(floatval($item['mesu']));
                            } elseif (isset($item['MY_amount']) && $item['MY_amount'] !== '' && $item['MY_amount'] !== '0' && $item['MY_amount'] !== 0) {
                                // 봉투/명함 전용 필드
                                $qty = floatval($item['MY_amount']);
                                // ⚠️ 사용자 요구: 계산 금지, DB 값 그대로 표시
                                echo number_format($qty);
                            } else {
                                // 마지막 fallback - 에러 표시 및 로깅
                                error_log("cart.php PRINT ERROR: 수량 필드 없음 - no={$item['no']}, product_type={$item['product_type']}");
                                echo '<span style="color:red; font-weight:bold;">수량 미지정</span>';
                            }
                            ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            <?php echo ($item['product_type'] == 'ncrflambeau') ? '권' : '매'; ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: right;">
                            <?php echo number_format($final_price); ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: right;">
                            <?php echo number_format($final_price); ?> 원
                        </td>
                        <td style="border: 1px solid #000; padding: 4px;"></td>
                    </tr>
                <?php endforeach; ?>

                <!-- 택배선불 행 -->
                <tr id="deliveryRow">
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">-</td>
                    <td style="border: 1px solid #000; padding: 4px;">택배선불</td>
                    <td style="border: 1px solid #000; padding: 4px;">
                        <select id="deliveryType" onchange="updateQuoteTotals(); updatePrintValues();" style="padding: 2px; font-size: 11px;">
                            <option value="">선택</option>
                            <option value="택배">택배</option>
                            <option value="퀵">퀵</option>
                            <option value="다마스">다마스</option>
                            <option value="방문">방문</option>
                        </select><span class="print-value" id="deliveryTypePrint"></span>
                    </td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">식</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;">
                        <input type="number" id="deliveryPrice" value="0" onchange="updateQuoteTotals()"
                               style="width: 70px; text-align: right; border: 1px solid #ccc; padding: 2px;">
                    </td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;" id="deliverySupplyCell">0 원</td>
                    <td style="border: 1px solid #000; padding: 4px;"></td>
                </tr>

                <!-- 추가 항목 컨테이너 -->
                <tbody id="customItemsContainer"></tbody>

                <!-- 추가 항목 버튼 행 -->
                <tr class="no-print">
                    <td colspan="8" style="border: 1px solid #000; padding: 5px; text-align: center; background: #f8f8f8;">
                        <button type="button" onclick="addCustomRow()" style="padding: 5px 15px; cursor: pointer; font-size: 12px;">
                            ➕ 항목 추가
                        </button>
                    </td>
                </tr>

                <!-- A4 인쇄용 빈 공란 행 (6행) -->
                <tbody id="emptyRowsContainer">
                <?php for ($i = 0; $i < 6; $i++): ?>
                <tr class="empty-row">
                    <td style="border: 1px solid #000; padding: 4px; text-align: center; height: 24px;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px;">&nbsp;</td>
                </tr>
                <?php endfor; ?>
                </tbody>

                <!-- 합계 행들 -->
                <tr style="background: #f0f0f0;">
                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; font-size: 11px;">공급가액 합계</td>
                    <td colspan="2" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; font-size: 11px;" id="quoteTotalSupply">
                        <?php echo number_format($quote_total); ?> 원
                    </td>
                </tr>
                <tr style="background: #f8f8f8;">
                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; font-size: 11px;">세 액 (VAT)</td>
                    <td colspan="2" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; font-size: 11px;" id="quoteTotalVat">
                        <?php echo number_format($quote_total_vat - $quote_total); ?> 원
                    </td>
                </tr>
                <tr style="background: #e0e0e0;">
                    <td colspan="6" style="border: 1px solid #000; padding: 10px; text-align: center; font-weight: bold; font-size: 13px;">합 계(부가세 포함)</td>
                    <td colspan="2" style="border: 1px solid #000; padding: 10px; text-align: right; font-weight: bold; font-size: 13px;" id="quoteTotalPrice">
                        <?php echo number_format($quote_total_vat); ?> 원
                    </td>
                </tr>

                <!-- 숨겨진 기본 금액 저장 -->
                <input type="hidden" id="baseSupply" value="<?php echo $quote_total; ?>">
                <input type="hidden" id="baseVat" value="<?php echo $quote_total_vat - $quote_total; ?>">
            </tbody>
        </table>
    <?php endif; ?>

    <!-- 하단 정보 -->
    <div style="margin-top: 12px; font-size: 13px; line-height: 1.8;">
        <p style="margin: 4px 0;"><strong>▶ 입금 계좌번호 :</strong> 국민 999-1688-2384 / 신한 110-342-543507 / 농협 301-2632-1829 예금주: 두손기획인쇄 차경선</p>
        <p style="margin: 4px 0;"><strong>▶ 담당자 :</strong></p>
        <p style="margin: 4px 0;"><strong>▶ 비 고 :</strong> 택배는 착불기준입니다</p>
    </div>

    <!-- 견적서 전용 버튼 (인쇄 시 숨김) -->
    <div class="no-print" style="text-align: center; margin-top: 30px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
        <button onclick="printQuotation()" style="padding: 12px 25px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(52,152,219,0.3);">
            🖨️ 견적서 출력
        </button>
        <button onclick="saveQuotation()" style="padding: 12px 25px; background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(39,174,96,0.3);">
            💾 견적서 저장
        </button>
        <?php if ($is_admin): ?>
        <button onclick="sendQuotationEmail()" style="padding: 12px 25px; background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(155,89,182,0.3);">
            📧 이메일 보내기
        </button>
        <?php endif; ?>
        <button onclick="hideQuotation()" style="padding: 12px 25px; background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(149,165,166,0.3);">
            ⬅️ 돌아가기
        </button>
    </div>
</div>
</div>

<script>
    // 📄 견적서 표시 함수
    function showQuotation() {
        // 장바구니 내용 숨기기
        document.getElementById('cartContent').style.display = 'none';

        // 견적서 표시
        document.getElementById('quotationSection').style.display = 'block';

        // 부드러운 스크롤 효과로 견적서 위치로 이동
        document.getElementById('quotationSection').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // 페이지 제목 변경
        document.title = '📄 견적서 - 두손기획인쇄';
    }

    // 🛒 장바구니로 돌아가기 함수
    function hideQuotation() {
        // 견적서 숨기기
        document.getElementById('quotationSection').style.display = 'none';

        // 장바구니 내용 표시
        document.getElementById('cartContent').style.display = 'block';

        // 부드러운 스크롤 효과로 장바구니 위치로 이동
        document.getElementById('cartContent').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // 페이지 제목 복원
        document.title = '🛒 통합 장바구니 - 두손기획인쇄';
    }

    // 🖨️ 견적서 인쇄 함수
    function printQuotation() {
        // 견적서만 인쇄하기 위한 새 창 열기
        const quotationContent = document.getElementById('quotationSection').innerHTML;
        const printWindow = window.open('', '_blank');

        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="ko">
            <head>
                <meta charset="UTF-8">
                <title>견적서 - 두손기획인쇄</title>
                <style>
                    @media print {
                        body { margin: 0; font-family: 'Malgun Gothic', Arial, sans-serif; }
                        .no-print { display: none !important; }
                    }
                    body {
                        font-family: 'Malgun Gothic', Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                    th { background: #f8f9fa; font-weight: bold; }
                    .no-print { display: none; }
                    /* 담당자/택배선불 입력필드 숨김 */
                    #customerNameInput, #deliveryType { display: none !important; }
                    .print-value { display: inline !important; }
                </style>
            </head>
            <body>
                ${quotationContent.replace(/onclick="[^"]*"/g, '').replace(/onmouseover="[^"]*"/g, '').replace(/onmouseout="[^"]*"/g, '')}
                <style>.no-print { display: none; }</style>
                <script>
                    // 버튼들 숨기기
                    const buttons = document.querySelectorAll('button');
                    buttons.forEach(btn => btn.style.display = 'none');

                    // 담당자/택배선불 입력필드 숨기고 print-value 표시
                    const customerInput = document.getElementById('customerNameInput');
                    const deliverySelect = document.getElementById('deliveryType');
                    if (customerInput) customerInput.style.display = 'none';
                    if (deliverySelect) deliverySelect.style.display = 'none';

                    // print-value 스팬 표시
                    document.querySelectorAll('.print-value').forEach(span => {
                        span.style.display = 'inline';
                    });

                    // 자동 인쇄 실행
                    window.onload = function() {
                        setTimeout(() => {
                            window.print();
                            window.close();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);

        printWindow.document.close();
    }

    // 장바구니 비우기
    function clearCart() {
        if (confirm('장바구니를 비우시겠습니까?')) {
            window.location.href = '?clear=1';
        }
    }

    // 계속 쇼핑하기 함수
    function continueShopping() {
        // 이전 페이지가 있고, 같은 도메인이면 이전 페이지로
        if (document.referrer && document.referrer.includes(window.location.hostname)) {
            // 장바구니 페이지가 아닌 경우에만 이전 페이지로
            if (!document.referrer.includes('cart.php')) {
                window.location.href = document.referrer;
                return;
            }
        }

        // 기본적으로 메인 쇼핑 페이지들 중 하나로 이동
        // 최근에 장바구니에 추가된 상품 타입에 따라 결정
        const lastProductType = getLastProductType();

        switch (lastProductType) {
            case 'leaflet':
                window.location.href = '../inserted/index.php';
                break;
            case 'cadarok':
                window.location.href = '../cadarok/index.php';
                break;
            case 'namecard':
                window.location.href = '../namecard/index.php';
                break;
            case 'sticker':
                window.location.href = 'view_modern.php';
                break;
            case 'envelope':
                window.location.href = '../envelope/index.php';
                break;
            case 'merchandisebond':
                window.location.href = '../merchandisebond/index.php';
                break;
            case 'littleprint':
                window.location.href = '../littleprint/index.php';
                break;
            default:
                // 기본값: 전단지 페이지
                window.location.href = '../inserted/index.php';
        }
    }

    // 마지막 상품 타입 가져오기 (장바구니에서 첫 번째 아이템)
    function getLastProductType() {
        <?php if (!empty($cart_items)): ?>
            return '<?php echo $cart_items[0]['product_type'] ?? 'leaflet'; ?>';
        <?php else: ?>
            return 'leaflet';
        <?php endif; ?>
    }

    // PDF 견적서 생성 함수 (고객 정보 모달 열기)
    function generateQuotePDF() {
        <?php if (empty($cart_items)): ?>
            alert('장바구니가 비어있습니다. 상품을 추가한 후 견적서를 생성해주세요.');
            return;
        <?php endif; ?>

        // 고객 정보 입력 모달 열기
        openCustomerModal();
    }

    // 버튼 호버 효과
    document.addEventListener('DOMContentLoaded', function() {
        const continueBtn = document.querySelector('.btn-continue');
        if (continueBtn) {
            continueBtn.addEventListener('mouseenter', function() {
                this.style.background = '#218838';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 15px rgba(40, 167, 69, 0.3)';
            });

            continueBtn.addEventListener('mouseleave', function() {
                this.style.background = '#28a745';
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }

        const quoteBtn = document.querySelector('.btn-quote');
        if (quoteBtn) {
            quoteBtn.addEventListener('mouseenter', function() {
                this.style.background = '#138496';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 15px rgba(23, 162, 184, 0.3)';
            });

            quoteBtn.addEventListener('mouseleave', function() {
                this.style.background = '#17a2b8';
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }

        const orderBtn = document.querySelector('.btn-order');
        if (orderBtn) {
            orderBtn.addEventListener('mouseenter', function() {
                this.style.background = '#c82333';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 15px rgba(231, 76, 60, 0.3)';
            });

            orderBtn.addEventListener('mouseleave', function() {
                this.style.background = '#e74c3c';
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }
    });

    // ======================================
    // 견적서 추가 항목 관련 함수들
    // ======================================

    let customRowCount = 0;

    // 숫자를 한글 금액으로 변환 (상단에 정의하여 호출 시 확실히 사용 가능)
    function numberToKoreanJS(num) {
        if (num === 0) return '영';
        const units = ['', '만', '억', '조'];
        const digits = ['', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];
        const subUnits = ['', '십', '백', '천'];

        let result = '';
        let unitIndex = 0;

        while (num > 0) {
            let chunk = num % 10000;
            num = Math.floor(num / 10000);

            if (chunk > 0) {
                let chunkStr = '';
                let subIndex = 0;
                while (chunk > 0) {
                    let digit = chunk % 10;
                    chunk = Math.floor(chunk / 10);
                    if (digit > 0) {
                        let digitStr = (digit === 1 && subIndex > 0) ? '' : digits[digit];
                        chunkStr = digitStr + subUnits[subIndex] + chunkStr;
                    }
                    subIndex++;
                }
                result = chunkStr + units[unitIndex] + result;
            }
            unitIndex++;
        }
        return result;
    }

    // 인쇄용 값 업데이트 (담당자, 택배선불)
    function updatePrintValues() {
        const customerName = document.getElementById('customerNameInput')?.value || '';
        const deliveryType = document.getElementById('deliveryType')?.value || '';

        const customerPrint = document.getElementById('customerNamePrint');
        const deliveryPrint = document.getElementById('deliveryTypePrint');

        if (customerPrint) customerPrint.textContent = customerName;
        if (deliveryPrint) deliveryPrint.textContent = deliveryType;
    }

    // 추가 항목 행 추가
    function addCustomRow() {
        customRowCount++;
        const container = document.getElementById('customItemsContainer');
        const row = document.createElement('tr');
        row.className = 'customItemRow';
        row.id = 'customRow_' + customRowCount;
        row.innerHTML = `
            <td style="border: 1px solid #000; padding: 4px; text-align: center;">+</td>
            <td style="border: 1px solid #000; padding: 4px;">
                <input type="text" class="customItem" placeholder="품목명" style="width: 90%; border: 1px solid #ccc; padding: 2px;">
            </td>
            <td style="border: 1px solid #000; padding: 4px;">
                <input type="text" class="customSpec" placeholder="규격/사양" style="width: 90%; border: 1px solid #ccc; padding: 2px;">
            </td>
            <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                <input type="number" class="customQty" value="1" min="1" style="width: 50px; text-align: center; border: 1px solid #ccc; padding: 2px;" onchange="updateQuoteTotals()">
            </td>
            <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                <input type="text" class="customUnit" value="개" style="width: 30px; text-align: center; border: 1px solid #ccc; padding: 2px;">
            </td>
            <td style="border: 1px solid #000; padding: 4px; text-align: right;">
                <input type="number" class="customPrice" value="0" style="width: 70px; text-align: right; border: 1px solid #ccc; padding: 2px;" onchange="updateQuoteTotals()">
            </td>
            <td style="border: 1px solid #000; padding: 4px; text-align: right;" class="customSupplyCell">0 원</td>
            <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                <button type="button" onclick="removeCustomRow(${customRowCount})" style="padding: 2px 8px; cursor: pointer; font-size: 11px; background: #e74c3c; color: white; border: none; border-radius: 3px;">삭제</button>
            </td>
        `;
        container.appendChild(row);
        updateQuoteTotals();
        updateEmptyRows();
    }

    // 추가 항목 행 삭제
    function removeCustomRow(rowId) {
        const row = document.getElementById('customRow_' + rowId);
        if (row) {
            row.remove();
            updateQuoteTotals();
            updateEmptyRows();
        }
    }

    // 공란 행 동적 조정 (A4 사이즈 맞춤)
    function updateEmptyRows() {
        const emptyRows = document.querySelectorAll('#emptyRowsContainer .empty-row');
        const customRows = document.querySelectorAll('.customItemRow');
        const customCount = customRows.length;
        const maxEmptyRows = 6; // 기본 공란 행 수

        emptyRows.forEach((row, index) => {
            // 추가 항목 수만큼 공란 행 숨김
            if (index < (maxEmptyRows - customCount)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // 견적서 합계 재계산
    function updateQuoteTotals() {
        const baseSupply = parseInt(document.getElementById('baseSupply')?.value || 0);
        const baseVat = parseInt(document.getElementById('baseVat')?.value || 0);

        // 배송비
        const deliveryPrice = parseInt(document.getElementById('deliveryPrice')?.value || 0);
        document.getElementById('deliverySupplyCell').textContent = deliveryPrice.toLocaleString() + ' 원';

        // 추가 항목 합계
        let customTotal = 0;
        document.querySelectorAll('.customItemRow').forEach(row => {
            const qty = parseInt(row.querySelector('.customQty')?.value || 1);
            const price = parseInt(row.querySelector('.customPrice')?.value || 0);
            const supply = qty * price;
            customTotal += supply;
            row.querySelector('.customSupplyCell').textContent = supply.toLocaleString() + ' 원';
        });

        // 총 합계 계산
        const totalSupply = baseSupply + deliveryPrice + customTotal;
        const totalVat = Math.round(totalSupply * 0.1);
        const totalPrice = totalSupply + totalVat;

        // 화면 업데이트 (하단)
        document.getElementById('quoteTotalSupply').textContent = totalSupply.toLocaleString() + ' 원';
        document.getElementById('quoteTotalVat').textContent = totalVat.toLocaleString() + ' 원';
        document.getElementById('quoteTotalPrice').textContent = totalPrice.toLocaleString() + ' 원';

        // 상단 합계금액도 업데이트 (한글 금액 + 숫자)
        const koreanText = document.getElementById('koreanAmountText');
        const numericText = document.getElementById('numericAmountText');
        const topTotal = document.getElementById('topTotalAmount');

        if (koreanText) koreanText.innerText = numberToKoreanJS(totalPrice);
        if (numericText) numericText.innerText = totalPrice.toLocaleString();
        if (topTotal) topTotal.innerText = totalPrice.toLocaleString() + ' 원';

        // 인쇄용 값도 업데이트
        updatePrintValues();
    }

    // 견적서 데이터 수집
    function collectQuotationData() {
        const customerName = document.getElementById('customerNameInput')?.value || '';
        const deliveryType = document.getElementById('deliveryType')?.value || '';
        const deliveryPrice = parseInt(document.getElementById('deliveryPrice')?.value || 0);

        // 추가 항목 수집
        const customItems = [];
        document.querySelectorAll('.customItemRow').forEach(row => {
            customItems.push({
                item: row.querySelector('.customItem')?.value || '',
                spec: row.querySelector('.customSpec')?.value || '',
                qty: parseInt(row.querySelector('.customQty')?.value || 1),
                unit: row.querySelector('.customUnit')?.value || '개',
                price: parseInt(row.querySelector('.customPrice')?.value || 0)
            });
        });

        return {
            customerName: customerName,
            deliveryType: deliveryType,
            deliveryPrice: deliveryPrice,
            customItems: customItems,
            totalSupply: document.getElementById('quoteTotalSupply')?.textContent.replace(/[^0-9]/g, '') || 0,
            totalVat: document.getElementById('quoteTotalVat')?.textContent.replace(/[^0-9]/g, '') || 0,
            totalPrice: document.getElementById('quoteTotalPrice')?.textContent.replace(/[^0-9]/g, '') || 0
        };
    }

    // 견적서 저장
    function saveQuotation() {
        const data = collectQuotationData();

        if (!data.customerName) {
            alert('담당자명을 입력해주세요.');
            document.getElementById('customerNameInput').focus();
            return;
        }

        fetch('save_quotation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('견적서가 저장되었습니다.\n견적번호: ' + result.quotation_no);
            } else {
                alert('저장 실패: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
        });
    }

    // 이메일 발송 (관리자 전용)
    function sendQuotationEmail() {
        const data = collectQuotationData();
        const email = prompt('견적서를 받을 이메일 주소를 입력하세요:');

        if (!email) return;

        if (!email.includes('@')) {
            alert('올바른 이메일 주소를 입력해주세요.');
            return;
        }

        data.recipientEmail = email;

        fetch('send_quotation_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('견적서가 이메일로 발송되었습니다.\n수신: ' + email);
            } else {
                alert('발송 실패: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('발송 중 오류가 발생했습니다.');
        });
    }
</script>

<?php
// 고객 정보 입력 모달 포함
include 'customer_info_modal.php';
?>
</body>

</html>

<?php
if ($connect) {
    mysqli_close($connect);
}
?>