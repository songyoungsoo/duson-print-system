<?php
session_start();
$session_id = session_id();

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

    // 배열을 결과셋처럼 사용할 수 있도록 변환
    if (empty($items)) {
        return false;
    }

    // 임시로 배열을 전역 변수에 저장하여 mysqli_fetch_assoc처럼 사용
    global $cart_items_array;
    $cart_items_array = $items;

    return true; // 성공 표시
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

                                        if (!empty($item['mesu'])) {
                                            echo number_format($item['mesu']) . $unit;
                                        } elseif (!empty($item['MY_amount'])) {
                                            echo htmlspecialchars($item['MY_amount']) . $unit;
                                        } else {
                                            echo '1' . $unit;
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

<!-- 📄 견적서 섹션 (기본적으로 숨겨짐) -->
<div id="quotationSection" style="display: none; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; box-sizing: border-box; max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 1rem;">

    <!-- 견적서 헤더 -->
    <div style="text-align: center; border-bottom: 3px solid #2c3e50; padding-bottom: 20px; margin-bottom: 30px;">
        <h1 style="font-size: 36px; margin: 0; color: #2c3e50; font-weight: 700;">견 적 서</h1>
        <div style="margin-top: 10px; font-size: 14px; color: #666;">
            두손기획인쇄 | 사업자등록번호: 201-10-69847<br>
            TEL: 02-2632-1830 | FAX: 02-2632-1831
        </div>
    </div>

    <!-- 견적일자 -->
    <div style="text-align: right; margin-bottom: 20px; font-size: 14px;">
        <strong>견적일자:</strong> <?php echo date('Y년 m월 d일'); ?>
    </div>

    <!-- 고객 인사말 -->
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; color: #2c3e50;">고객님께</h3>
        <p>아래와 같이 견적을 제출합니다.</p>
    </div>

    <?php if (!empty($cart_items)): ?>
        <!-- 견적서 테이블 -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 13px;">
            <thead>
                <tr style="background: #34495e; color: white;">
                    <th style="padding: 12px; text-align: center; font-weight: normal; border: 1px solid #2c3e50;">NO</th>
                    <th style="padding: 12px; text-align: center; font-weight: normal; border: 1px solid #2c3e50;">상품명</th>
                    <th style="padding: 12px; text-align: center; font-weight: normal; border: 1px solid #2c3e50;">규격/옵션</th>
                    <th style="padding: 12px; text-align: center; font-weight: normal; border: 1px solid #2c3e50;">수량</th>
                    <th style="padding: 12px; text-align: center; font-weight: normal; border: 1px solid #2c3e50;">단가</th>
                    <th style="padding: 12px; text-align: center; font-weight: normal; border: 1px solid #2c3e50;">부가세포함</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $quote_total = 0;
                $quote_total_vat = 0;
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

                    $quote_total += $final_price;
                    $quote_total_vat += $final_price_vat;

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
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $index + 1; ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $product_name; ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: left; font-size: 11px;">
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
                            <?php else: ?>
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
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                            <?php
                            // 양식지(ncrflambeau)는 "권" 단위 사용
                            $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';

                            if (!empty($item['mesu'])) {
                                echo number_format($item['mesu']) . $unit;
                            } elseif (!empty($item['MY_amount'])) {
                                echo htmlspecialchars($item['MY_amount']) . $unit;
                            } else {
                                echo '1' . $unit;
                            }
                            ?>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">
                            <strong><?php echo number_format($final_price); ?>원</strong>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">
                            <strong><?php echo number_format($final_price_vat); ?>원</strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 합계 정보 -->
        <div style="background: #ecf0f1; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 16px;">
                <span>공급가액 (VAT 제외):</span>
                <span><?php echo number_format($quote_total); ?>원</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 16px;">
                <span>부가세(10%):</span>
                <span><?php echo number_format($quote_total_vat - $quote_total); ?>원</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: bold; color: #2c3e50; border-top: 2px solid #34495e; padding-top: 10px; margin-top: 10px;">
                <span>총 합계금액 (VAT 포함):</span>
                <span><?php echo number_format($quote_total_vat); ?>원</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- 회사 정보 -->
    <div style="border-top: 2px solid #34495e; padding-top: 20px; color: #666; font-size: 14px;">
        <div>
            <strong>두손기획인쇄</strong><br>
            서울특별시 영등포구 영등포로 36길 9 송호빌딩 1층<br>
            전화: 02-2632-1830 | 팩스: 02-2632-1831<br>
            이메일: dsp1830@naver.com
        </div>

        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 15px;">
            <strong>입금계좌 안내</strong><br>
            국민은행: 123-456-789012 (예금주: 두손기획인쇄)<br>
            신한은행: 987-654-321098 (예금주: 두손기획인쇄)
        </div>

        <p style="margin-top: 20px; font-size: 12px; color: #999;">
            ※ 본 견적서의 유효기간은 발행일로부터 30일입니다.<br>
            ※ 상기 금액은 부가세가 포함된 금액입니다.<br>
            ※ 디자인 수정 및 추가 작업 시 별도 비용이 발생할 수 있습니다.
        </p>
    </div>

    <!-- 견적서 전용 버튼 -->
    <div style="text-align: center; margin-top: 30px; display: flex; gap: 15px; justify-content: center;">
        <button onclick="printQuotation()" style="padding: 12px 30px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(52,152,219,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(52,152,219,0.4)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 10px rgba(52,152,219,0.3)'">
            🖨️ 견적서 인쇄
        </button>
        <button onclick="hideQuotation()" style="padding: 12px 30px; background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%); color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(149,165,166,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(149,165,166,0.4)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 10px rgba(149,165,166,0.3)'">
            ⬅️ 장바구니로 돌아가기
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
                </style>
            </head>
            <body>
                ${quotationContent.replace(/onclick="[^"]*"/g, '').replace(/onmouseover="[^"]*"/g, '').replace(/onmouseout="[^"]*"/g, '')}
                <style>.no-print { display: none; }</style>
                <script>
                    // 버튼들 숨기기
                    const buttons = document.querySelectorAll('button');
                    buttons.forEach(btn => btn.style.display = 'none');

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