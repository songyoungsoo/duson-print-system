<?php 
session_start(); 
$session_id = session_id();

// 공통 설정 및 DB 연결
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
include "../../db.php";
include "../../includes/functions.php";

// 페이지 설정
$page_title = '📄 두손기획인쇄 - 전단지 컴팩트 견적';
$current_page = 'leaflet';
$page = "inserted";
$GGTABLE = "mlangprintauto_transactioncate";

// UTF-8 설정
if ($db) mysqli_set_charset($db, "utf8");

// 통합 갤러리 시스템 - 제거됨 (새 갤러리 v2.0 사용)
// if (file_exists('../../includes/gallery_helper.php')) {
//     include_once '../../includes/gallery_helper.php';
// }
// if (function_exists("init_gallery_system")) {
//     init_gallery_system("inserted");
// }

// 드롭다운 옵션 로드 함수
function getLeafletOptions($connect, $GGTABLE, $page, $parent_no = 0) {
    $options = [];
    $query = $parent_no > 0 ? "SELECT * FROM $GGTABLE WHERE TreeNo='$parent_no' ORDER BY no ASC" : "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = ['no' => $row['no'], 'title' => $row['title']];
        }
    }
    return $options;
}

$colorOptions = getLeafletOptions($db, $GGTABLE, $page);
$firstColorNo = !empty($colorOptions) ? $colorOptions[0]['no'] : '1';
$paperTypeOptions = getLeafletOptions($db, $GGTABLE, $page, $firstColorNo);
$paperSizeOptions = getLeafletOptions($db, $GGTABLE, $page, $firstColorNo); // Note: This seems to be the same as paper types, might need review.

header("Cache-Control: no-cache, no-store, must-revalidate");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    <meta name="session-id" content="<?php echo htmlspecialchars($session_id); ?>">

    <!-- CSS -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <link rel="stylesheet" href="../../css/product-layout.css">
    <link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/upload-modal-common.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/brand-design-system.css">
    <link rel="stylesheet" href="../../css/additional-options.css">
    <!-- <link rel="stylesheet" href="../../css/unified-gallery.css"> -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">

    <!-- 공통 갤러리 팝업 함수 (샘플더보기 버튼용) -->
    <script src="../../js/common-gallery-popup.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- JS -->
    <script src="../../js/common-unified.js" defer></script>
    <script src="../../js/inserted-logic.js" defer></script>
</head>

<body class="inserted-page">
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>

    <div class="product-container">
        <div class="page-title"><h1>📄 전단지 견적 안내</h1></div>
        <div class="product-content">
            <section class="product-gallery" aria-label="전단지 샘플 갤러리">
                <?php
                // 새 갤러리 시스템 - 한 줄 인클루드 (계산 로직 무관)
                $gallery_product = 'inserted';
                include '../../includes/simple_gallery_include.php';
                ?>
            </section>

            <aside class="product-calculator" aria-label="실시간 견적 계산기">
                <form id="orderForm" method="post">
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">색상</label>
                            <select name="MY_type" id="MY_type" class="inline-select" required>
                                <?php foreach ($colorOptions as $option): ?>
                                <option value="<?php echo htmlspecialchars($option['no']); ?>"><?php echo htmlspecialchars($option['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_Fsd">종류</label>
                            <select name="MY_Fsd" id="MY_Fsd" class="inline-select" required><option value="">먼저 색상을 선택해주세요</option></select>
                        </div>
                        <div class="inline-form-row">
                            <label class="inline-label" for="PN_type">규격</label>
                            <select name="PN_type" id="PN_type" class="inline-select" required><option value="">먼저 색상을 선택해주세요</option></select>
                        </div>
                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄면</label>
                            <select name="POtype" id="POtype" class="inline-select" required>
                                <option value="1" selected>단면</option>
                                <option value="2">양면</option>
                            </select>
                        </div>
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_amount">수량</label>
                            <select name="MY_amount" id="MY_amount" class="inline-select" required><option value="">먼저 규격을 선택해주세요</option></select>
                        </div>
                        <div class="inline-form-row">
                            <label class="inline-label" for="ordertype">편집비</label>
                            <select name="ordertype" id="ordertype" class="inline-select" required>
                                <option value="print" selected>인쇄만 의뢰</option>
                                <option value="total">디자인+인쇄</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="leaflet-premium-options-section" id="premiumOptionsSection">
                        <div class="option-headers-row">
                            <div class="option-checkbox-group"><input type="checkbox" id="coating_enabled" name="coating_enabled" class="option-toggle" value="1"><label for="coating_enabled" class="toggle-label">코팅</label></div>
                            <div class="option-checkbox-group"><input type="checkbox" id="folding_enabled" name="folding_enabled" class="option-toggle" value="1"><label for="folding_enabled" class="toggle-label">접지</label></div>
                            <div class="option-checkbox-group"><input type="checkbox" id="creasing_enabled" name="creasing_enabled" class="option-toggle" value="1"><label for="creasing_enabled" class="toggle-label">오시</label></div>
                            <div class="option-price-display"><span class="option-price-total" id="premiumPriceTotal">(+0원)</span></div>
                        </div>
                        <div class="option-details" id="coating_options" style="display: none;"><select name="coating_type" id="coating_type" class="option-select"><option value="">선택</option><option value="single">단면유광</option><option value="double">양면유광</option><option value="single_matte">단면무광</option><option value="double_matte">양면무광</option></select></div>
                        <div class="option-details" id="folding_options" style="display: none;"><select name="folding_type" id="folding_type" class="option-select"><option value="">선택</option><option value="2fold">2단</option><option value="3fold">3단</option><option value="accordion">병풍</option><option value="gate">대문</option></select></div>
                        <div class="option-details" id="creasing_options" style="display: none;"><select name="creasing_lines" id="creasing_lines" class="option-select"><option value="">선택</option><option value="1">1줄</option><option value="2">2줄</option><option value="3">3줄</option></select></div>
                        <input type="hidden" name="coating_price" id="coating_price" value="0">
                        <input type="hidden" name="folding_price" id="folding_price" value="0">
                        <input type="hidden" name="creasing_price" id="creasing_price" value="0">
                        <input type="hidden" name="additional_options_total" id="additional_options_total" value="0">
                    </div>
                    
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">옵션을 선택하면<br>실시간으로 가격이 계산됩니다</div>
                    </div>

                    <div class="upload-order-button" id="uploadOrderButton" style="display:none;">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">파일 업로드 및 주문하기</button>
                    </div>
                    
                    <input type="hidden" name="price" id="calculated_price" value="">
                    <input type="hidden" name="vat_price" id="calculated_vat_price" value="">
                </form>
            </aside>
        </div>
    </div>

    <?php 
    include "../../includes/upload_modal.php";
    include "../../includes/login_modal.php";
    include "explane_inserted.php";
    include "../../includes/footer.php"; 
    ?>
</body>
</html>

<?php if ($db) mysqli_close($db); ?>