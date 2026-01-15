<?php
/**
 * 통합 장바구니 주문 처리
 * 경로: mlangorder_printauto/ProcessOrder_unified.php
 */

// 에러 표시 끄기 (출력 버퍼에 에러 메시지가 포함되지 않도록)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// 보안 상수 정의 후 데이터베이스 연결
include "../includes/db_constants.php";
include "../db.php";
$connect = $db;

// 🔧 FIX: 명시적으로 UTF-8 charset 설정 (인코딩 깨짐 방지)
mysqli_set_charset($connect, 'utf8mb4');

// 헬퍼 함수 포함
include "../mlangprintauto/shop_temp_helper.php";
include "../includes/upload_config.php";
require_once __DIR__ . '/../includes/StandardUploadHandler.php';
// ✅ 2026-01-16: QuantityFormatter SSOT 추가
if (!class_exists('QuantityFormatter')) {
    include $_SERVER['DOCUMENT_ROOT'] . "/includes/QuantityFormatter.php";
}
// upload_path_manager.php는 사용하지 않음 (안전 모드)

try {
    // POST 데이터 받기
    $session_id = $_POST['session_id'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $email = $_POST['email'] ?? '';

    // 상세 디버그 로깅
    error_log("=== 주문 처리 시작 - POST 데이터 ===");
    error_log("받은 username (raw): [" . ($_POST['username'] ?? 'NOT SET') . "]");
    error_log("받은 username (trimmed): [" . $username . "]");
    error_log("받은 email: [" . $email . "]");
    error_log("세션 user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    error_log("세션 user_name: " . ($_SESSION['user_name'] ?? 'NOT SET'));
    error_log("세션 username: " . ($_SESSION['username'] ?? 'NOT SET'));

    // empty() 체크 결과 로깅
    error_log("empty(\$username) = " . (empty($username) ? 'true' : 'false'));
    error_log("\$username === '0' = " . ($username === '0' ? 'true' : 'false'));

    // "0"이나 빈 문자열이면 세션 또는 이메일에서 가져오기 시도
    if (empty($username) || $username === '0') {
        error_log("조건 충족: username이 비어있거나 '0'임 - 폴백 로직 시작");

        // 1. 세션에서 사용자 이름 가져오기
        if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name']) && $_SESSION['user_name'] !== '0') {
            $old_username = $username;
            $username = $_SESSION['user_name'];
            error_log("1단계 성공 - 세션에서 username 복구: [$old_username] → [$username]");
        }
        // 2. 이메일에서 추출 (username이 여전히 비어있을 때만)
        elseif ((empty($username) || $username === '0') && !empty($email)) {
            $old_username = $username;
            $email_parts = explode('@', $email);
            $username = $email_parts[0];
            error_log("2단계 - 이메일에서 username 생성: [$old_username] → [$username]");
        }
        // 3. 기본값 사용 (username이 여전히 비어있을 때만)
        elseif (empty($username) || $username === '0') {
            $old_username = $username;
            $username = '주문자';
            error_log("3단계 - 기본값 사용: [$old_username] → [주문자]");
        }
    } else {
        error_log("조건 불충족: username을 그대로 사용 [$username]");
    }

    error_log("최종 저장될 username: [$username]");
    error_log("====================================");
    $phone = $_POST['phone'] ?? '';
    $hendphone = $_POST['Hendphone'] ?? '';
    $address_option = $_POST['address_option'] ?? 'different';
    $postcode = $_POST['sample6_postcode'] ?? '';
    $address = $_POST['sample6_address'] ?? '';
    $detail_address = $_POST['sample6_detailAddress'] ?? '';
    $extra_address = $_POST['sample6_extraAddress'] ?? '';
    $cont = $_POST['cont'] ?? '';
    $total_price = (float)($_POST['total_price'] ?? 0);
    $total_price_vat = (float)($_POST['total_price_vat'] ?? 0);
    $items_count = (int)($_POST['items_count'] ?? 0);
    
    // 회원 주소 사용 시 회원 정보에서 주소 가져오기
    if ($address_option === 'member' && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $user_query = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare($connect, $user_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $user_info = mysqli_fetch_assoc($result);
                // 회원 정보에서 주소 가져오기
                if (empty($address) && !empty($user_info['address'])) {
                    $postcode = $user_info['postcode'] ?? '';
                    $address = $user_info['address'] ?? '';
                    $detail_address = $user_info['detail_address'] ?? '';
                    $extra_address = $user_info['extra_address'] ?? '';
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // 사업자 정보 받기
    $is_business = isset($_POST['is_business']) ? 1 : 0;
    $business_number = $_POST['business_number'] ?? '';
    $business_owner = $_POST['business_owner'] ?? '';
    $business_type = $_POST['business_type'] ?? '';
    $business_item = $_POST['business_item'] ?? '';
    $business_address = $_POST['business_address'] ?? '';
    $tax_invoice_email = $_POST['tax_invoice_email'] ?? '';
    
    // 필수 필드 검증
    if (empty($username) || empty($email) || empty($phone) || empty($address)) {
        throw new Exception('필수 정보를 모두 입력해주세요.');
    }
    
    // 직접 주문인지 장바구니 주문인지 확인
    $is_direct_order = isset($_POST['is_direct_order']) && $_POST['is_direct_order'] == '1';
    $cart_items = [];
    
    if ($is_direct_order) {
        // 직접 주문 데이터 처리
        $direct_item = [
            'product_type' => $_POST['direct_product_type'] ?? 'leaflet',
            'MY_type' => $_POST['direct_MY_type'] ?? '',
            'MY_Fsd' => $_POST['direct_MY_Fsd'] ?? '',
            'PN_type' => $_POST['direct_PN_type'] ?? '',
            'POtype' => $_POST['direct_POtype'] ?? '',
            'MY_amount' => $_POST['direct_MY_amount'] ?? '',
            'ordertype' => $_POST['direct_ordertype'] ?? '',
            'color_text' => $_POST['direct_color_text'] ?? '',
            'paper_type_text' => $_POST['direct_paper_type_text'] ?? '',
            'paper_size_text' => $_POST['direct_paper_size_text'] ?? '',
            'sides_text' => $_POST['direct_sides_text'] ?? '',
            'quantity_text' => $_POST['direct_quantity_text'] ?? '',
            'design_text' => $_POST['direct_design_text'] ?? '',
            'st_price' => intval($_POST['direct_price'] ?? 0),
            'st_price_vat' => intval($_POST['direct_vat_price'] ?? 0),
            'MY_comment' => ''
        ];
        $cart_items[] = $direct_item;
    } else {
        // 장바구니 아이템 조회
        $cart_result = getCartItems($connect, $session_id);

        if ($cart_result) {
            while ($item = mysqli_fetch_assoc($cart_result)) {
                // 🆕 JSON 방식 추가 옵션 파싱 (전단지/카다록/포스터)
                if (!empty($item['additional_options'])) {
                    $additional_options = json_decode($item['additional_options'], true);
                    if ($additional_options && is_array($additional_options)) {
                        // JSON 데이터를 개별 필드로 변환하여 기존 코드와 호환
                        $item['coating_enabled'] = $additional_options['coating_enabled'] ?? 0;
                        $item['coating_type'] = $additional_options['coating_type'] ?? '';
                        $item['coating_price'] = $additional_options['coating_price'] ?? 0;
                        $item['folding_enabled'] = $additional_options['folding_enabled'] ?? 0;
                        $item['folding_type'] = $additional_options['folding_type'] ?? '';
                        $item['folding_price'] = $additional_options['folding_price'] ?? 0;
                        $item['creasing_enabled'] = $additional_options['creasing_enabled'] ?? 0;
                        $item['creasing_lines'] = $additional_options['creasing_lines'] ?? 0;
                        $item['creasing_price'] = $additional_options['creasing_price'] ?? 0;
                    }
                }

                // 스티커 데이터 디버깅 로그
                if ($item['product_type'] == 'sticker') {
                    error_log("스티커 장바구니 데이터: " . json_encode($item, JSON_UNESCAPED_UNICODE));
                }
                $cart_items[] = $item;
            }
        }

        if (empty($cart_items)) {
            throw new Exception('장바구니가 비어있습니다.');
        }
    }
    
    // 각 장바구니 아이템을 개별 주문으로 처리
    $order_numbers = [];
    $date = date("Y-m-d H:i:s");

    // 💎 FIX: is_member 플래그 설정 (세션에 user_id가 있으면 회원)
    $is_member_flag = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) ? 1 : 0;
    
    foreach ($cart_items as $item) {
        // 새 주문 번호 생성
        $max_result = mysqli_query($connect, "SELECT MAX(no) as max_no FROM mlangorder_printauto");
        $max_row = mysqli_fetch_assoc($max_result);
        $new_no = ($max_row['max_no'] ?? 0) + 1;
        
        // 상품 타입별 정보 구성
        $product_info = '';
        $product_type_name = '';
        
        switch ($item['product_type']) {
            case 'sticker':
                $product_type_name = '스티커';
                
                // 스티커 데이터 디버깅
                error_log("스티커 주문 처리 - 원본 데이터: " . json_encode($item, JSON_UNESCAPED_UNICODE));
                
                // 스티커 데이터 추출 (안전한 방식)
                $jong = !empty($item['jong']) ? $item['jong'] : '정보없음';
                $garo = !empty($item['garo']) ? intval($item['garo']) : 0;
                $sero = !empty($item['sero']) ? intval($item['sero']) : 0;
                $mesu = !empty($item['mesu']) ? intval($item['mesu']) : 0;
                $domusong = !empty($item['domusong']) ? $item['domusong'] : '정보없음';
                $uhyung = !empty($item['uhyung']) ? intval($item['uhyung']) : 0;
                
                // ✅ 스티커 데이터를 JSON 형태로 구조화 (평탄화된 구조)
                $sticker_data = [
                    'product_type' => 'sticker',
                    'jong' => $jong,
                    'garo' => $garo,
                    'sero' => $sero,
                    'mesu' => $mesu,
                    'domusong' => $domusong,
                    'uhyung' => $uhyung,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $product_info = json_encode($sticker_data, JSON_UNESCAPED_UNICODE);
                error_log("스티커 주문 처리 - 최종 JSON: " . $product_info);
                break;
                
            case 'cadarok':
                $product_type_name = '카다록';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $style_name = getCategoryName($connect, $item['MY_Fsd']);
                $section_name = getCategoryName($connect, $item['PN_type']);
                $product_info = "구분: $type_name\n";
                $product_info .= "규격: $style_name\n";
                $product_info .= "종이종류: $section_name\n";
                $product_info .= "수량: " . number_format(intval($item['MY_amount'] ?? 0), 0) . "부\n";
                $product_info .= "주문방법: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;
                
            case 'leaflet':
            case 'inserted':
                $product_type_name = '전단지';
                $color_name = getCategoryName($connect, $item['MY_type']);
                $paper_name = getCategoryName($connect, $item['MY_Fsd']);
                $size_name = getCategoryName($connect, $item['PN_type']);
                $sides = $item['POtype'] == '1' ? '단면' : '양면';
                $design = ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');

                // 두손기획 비즈니스 규칙: 전단지 수량 표시 "X연 (Y매)" 형식
                // quantityTwo 또는 mesu에서 매수 가져오기
                $reams = floatval($item['MY_amount'] ?? 0);
                $sheets = intval($item['quantityTwo'] ?? $item['mesu'] ?? 0);

                // 0.5연만 소수점, 나머지 정수
                if ($reams == 0.5) {
                    $yeon_display = '0.5';
                } else {
                    $yeon_display = number_format(intval($reams), 0);
                }

                if ($sheets > 0) {
                    $qty_display = $yeon_display . "연 (" . number_format($sheets, 0) . "매)";
                } else {
                    // 매수 정보가 없으면 연 수만 표시
                    $qty_display = $yeon_display . "연";
                }

                // 🔧 FIX: JSON 형식으로 저장하여 OrderComplete에서 일관되게 처리
                $leaflet_data = [
                    'product_type' => 'inserted',
                    'MY_type' => $item['MY_type'],
                    'MY_Fsd' => $item['MY_Fsd'],
                    'PN_type' => $item['PN_type'],
                    'POtype' => $item['POtype'],
                    'MY_amount' => $item['MY_amount'], // Ream count
                    'mesu' => $sheets,  // Sheet count is stored as 'mesu' now! (FIXED)
                    'ordertype' => $item['ordertype'],
                    'formatted_display' => // "인쇄색상: $color_name\n" . // REMOVED as per user request
                                          "용지: $paper_name\n" .
                                          "규격: $size_name\n" .
                                          "인쇄면: $sides\n" .
                                          "수량: $qty_display\n" .
                                          "디자인: $design",
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $product_info = json_encode($leaflet_data, JSON_UNESCAPED_UNICODE);
                break;
                
            case 'namecard':
                $product_type_name = '명함';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $paper_name = getCategoryName($connect, $item['MY_Fsd']);
                $sides = $item['POtype'] == '1' ? '단면' : '양면';
                $product_info = "명함종류: $type_name\n";
                $product_info .= "명함재질: $paper_name\n";
                $product_info .= "인쇄면: $sides\n";
                $product_info .= "수량: " . number_format(intval($item['MY_amount'] ?? 0), 0) . ($item['unit'] ?? '매') . "\n";
                $product_info .= "편집디자인: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;

            case 'envelope':
                $product_type_name = '봉투';

                // 🔧 FIX: Use pre-stored *_name fields from shop_temp instead of getCategoryName
                $type_name = $item['MY_type_name'] ?? getCategoryName($connect, $item['MY_type']);
                $paper_name = $item['Section_name'] ?? getCategoryName($connect, $item['Section'] ?? $item['MY_Fsd']);
                $print_name = $item['POtype_name'] ?? getCategoryName($connect, $item['POtype']);
                $design = ($item['ordertype'] === 'total' ? '디자인+인쇄' : '인쇄만');

                // 🔧 FIX: JSON 형식으로 저장 (Korean names included)
                $envelope_data = [
                    'product_type' => 'envelope',
                    'MY_type' => $item['MY_type'],
                    'MY_type_name' => $type_name,
                    'Section' => $item['Section'] ?? $item['MY_Fsd'],
                    'Section_name' => $paper_name,
                    'MY_amount' => $item['MY_amount'],
                    'POtype' => $item['POtype'],
                    'POtype_name' => $print_name,
                    'ordertype' => $item['ordertype'],
                    'formatted_display' => "타입: $type_name\n" .
                                          "용지: $paper_name\n" .
                                          "수량: " . number_format($item['MY_amount']) . ($item['unit'] ?? '매') . "\n" .
                                          "인쇄: $print_name\n" .
                                          "디자인: $design",
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $product_info = json_encode($envelope_data, JSON_UNESCAPED_UNICODE);
                break;

            case 'msticker':
                $product_type_name = '자석스티커';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $size_name = getCategoryName($connect, $item['PN_type']);
                $product_info = "종류: $type_name\n";
                $product_info .= "규격: $size_name\n";
                $product_info .= "수량: " . number_format(intval($item['MY_amount'] ?? 0), 0) . ($item['unit'] ?? '매') . "\n";
                $product_info .= "편집디자인: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;
                
            case 'ncrflambeau':
                $product_type_name = '양식지/NCR';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $size_name = getCategoryName($connect, $item['MY_Fsd']);
                $color_name = getCategoryName($connect, $item['PN_type']);

                // ✅ 2026-01-16: NCR양식지 매수 계산 SSOT 적용
                $ncr_qty = intval($item['MY_amount'] ?? 0);
                $ncr_sheets = intval($item['quantity_sheets'] ?? 0);
                if ($ncr_sheets <= $ncr_qty && class_exists('QuantityFormatter')) {
                    $multiplier = QuantityFormatter::extractNcrMultiplier($item);
                    $ncr_sheets = QuantityFormatter::calculateNcrSheets($ncr_qty, $multiplier);
                }
                $ncr_qty_display = number_format($ncr_qty) . '권';
                if ($ncr_sheets > 0) {
                    $ncr_qty_display .= ' (' . number_format($ncr_sheets) . '매)';
                }

                $product_info = "구분: $type_name\n";
                $product_info .= "규격: $size_name\n";
                $product_info .= "색상: $color_name\n";
                $product_info .= "수량: $ncr_qty_display\n";
                $product_info .= "편집디자인: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;
                
            case 'merchandisebond':
                $product_type_name = '상품권/쿠폰';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $sides = $item['POtype'] == '1' ? '단면' : '양면';
                $after_name = getCategoryName($connect, $item['PN_type']);
                $product_info = "종류: $type_name\n";
                $product_info .= "수량: " . number_format(intval($item['MY_amount'] ?? 0), 0) . ($item['unit'] ?? '매') . "\n";
                $product_info .= "인쇄면: $sides\n";
                $product_info .= "후가공: $after_name\n";
                $product_info .= "편집디자인: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;

            case 'littleprint':
            case 'poster':  // 레거시 호환 (기존 poster로 저장된 데이터 처리)
                $product_type_name = '포스터';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $paper_name = getCategoryName($connect, $item['Section'] ?? $item['MY_Fsd']);
                $size_name = getCategoryName($connect, $item['PN_type']);
                $design = ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');

                // 🔧 FIX: JSON 형식으로 저장하여 OrderFormOrderTree.php에서 일관되게 처리
                $poster_qty = floatval($item['MY_amount'] ?? 0);
                $littleprint_data = [
                    'product_type' => 'littleprint',  // 항상 littleprint로 정규화
                    'MY_type' => $item['MY_type'],
                    'Section' => $item['Section'] ?? $item['MY_Fsd'],
                    'PN_type' => $item['PN_type'],
                    'MY_amount' => $item['MY_amount'],
                    'quantity' => $poster_qty,  // 🔧 수량 필드 추가
                    'unit' => '매',  // 🔧 단위 필드 추가
                    'ordertype' => $item['ordertype'],
                    'formatted_display' => "구분: $type_name\n" .
                                          "용지: $paper_name\n" .
                                          "규격: $size_name\n" .
                                          "수량: " . number_format($poster_qty) . "매\n" .
                                          "디자인: $design",
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $product_info = json_encode($littleprint_data, JSON_UNESCAPED_UNICODE);
                break;

            default:
                $product_type_name = '기타';
                // 🔧 FIX: "상품 정보:" 텍스트 제거 - JSON만 저장 (OrderComplete에서 파싱 가능하도록)
                $product_info = json_encode($item, JSON_UNESCAPED_UNICODE);
        }
        
        // 디자인 여부
        $design_info = ($item['uhyung'] == 1 || $item['ordertype'] === 'design') ? '디자인+인쇄' : '인쇄만';
        
        // 사업자 정보가 있으면 기타사항에 추가
        $final_cont = $cont;
        if ($is_business && !empty($business_number)) {
            $business_info_text = "\n\n=== 사업자 정보 ===\n";
            $business_info_text .= "사업자등록번호: " . $business_number . "\n";
            if (!empty($business_owner)) {
                $business_info_text .= "대표자명: " . $business_owner . "\n";
            }
            if (!empty($business_type)) {
                $business_info_text .= "업태: " . $business_type . "\n";
            }
            if (!empty($business_item)) {
                $business_info_text .= "종목: " . $business_item . "\n";
            }
            if (!empty($business_address)) {
                $business_info_text .= "사업장주소: " . $business_address . "\n";
            }
            if (!empty($tax_invoice_email)) {
                $business_info_text .= "세금계산서 발행 이메일: " . $tax_invoice_email . "\n";
            }
            $business_info_text .= "세금계산서 발행 요청";
            
            $final_cont .= $business_info_text;
        }
        
        // mlangorder_printauto 테이블에 삽입 (ImgFolder 필드 포함)
        $insert_query = "INSERT INTO mlangorder_printauto (
            no, Type, ImgFolder, uploaded_files, Type_1, money_4, money_5, name, email, zip, zip1, zip2,
            phone, Hendphone, cont, date, OrderStyle, ThingCate,
            coating_enabled, coating_type, coating_price,
            folding_enabled, folding_type, folding_price,
            creasing_enabled, creasing_lines, creasing_price,
            additional_options_total,
            premium_options, premium_options_total,
            envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price,
            envelope_additional_options_total, unit, quantity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($connect, $insert_query);
        if (!$stmt) {
            throw new Exception('주문 처리 중 오류가 발생했습니다: ' . mysqli_error($connect));
        }

        $order_style = '2'; // 온라인 주문

        // 🆕 Phase 4: shop_temp에서 ImgFolder와 ThingCate 가져오기 (안전 모드)
        $img_folder_from_cart = isset($item['ImgFolder']) ? $item['ImgFolder'] : '';
        $thing_cate_from_cart = isset($item['ThingCate']) ? $item['ThingCate'] : '';

        // 레거시 경로 형식인지 확인
        $is_legacy_path = !empty($img_folder_from_cart) && 
                         strpos($img_folder_from_cart, '_MlangPrintAuto_') === 0;

        // ImgFolder와 ThingCate 설정
        if ($is_legacy_path) {
            // 레거시 경로 형식이면 shop_temp 값 그대로 사용
            $img_folder_path = $img_folder_from_cart;
            $thing_cate = !empty($thing_cate_from_cart) ? $thing_cate_from_cart : 'default.jpg';
            
            error_log("레거시 경로 사용 - ImgFolder: {$img_folder_path}, ThingCate: {$thing_cate}");
        } else {
            // 기존 방식: uploaded_files 테이블 조회
            $thing_cate = '';
            $file_query = "SELECT file_name FROM uploaded_files WHERE session_id = ? AND product_type = ? ORDER BY upload_date DESC LIMIT 1";
            $file_stmt = mysqli_prepare($connect, $file_query);

            if ($file_stmt) {
                mysqli_stmt_bind_param($file_stmt, 'ss', $session_id, $item['product_type']);
                mysqli_stmt_execute($file_stmt);
                $file_result = mysqli_stmt_get_result($file_stmt);

                if ($file_row = mysqli_fetch_assoc($file_result)) {
                    $thing_cate = $file_row['file_name'];
                }

                mysqli_stmt_close($file_stmt);
            }

            // 파일이 없으면 기본값 설정
            if (empty($thing_cate)) {
                $thing_cate = $product_type_name . '_' . date('YmdHis') . '.jpg';
            }

            // 기본 경로 설정
            $img_folder_path = "uploads/orders/" . $new_no . "/";

            error_log("기본 경로 사용 - ImgFolder: {$img_folder_path}, ThingCate: {$thing_cate}");
        }

        $full_address = $detail_address . ' ' . $extra_address; // 주소 문자열 연결을 변수에 저장

        // 추가 옵션 데이터 가져오기 (전단지용)
        $coating_enabled = $item['coating_enabled'] ?? 0;
        $coating_type = $item['coating_type'] ?? '';
        $coating_price = $item['coating_price'] ?? 0;
        $folding_enabled = $item['folding_enabled'] ?? 0;
        $folding_type = $item['folding_type'] ?? '';
        $folding_price = $item['folding_price'] ?? 0;
        $creasing_enabled = $item['creasing_enabled'] ?? 0;
        $creasing_lines = $item['creasing_lines'] ?? 0;
        $creasing_price = $item['creasing_price'] ?? 0;
        $additional_options_total = $item['additional_options_total'] ?? 0;

        // 프리미엄 옵션 데이터 가져오기 (명함용)
        $premium_options = $item['premium_options'] ?? '';
        $premium_options_total = $item['premium_options_total'] ?? 0;

        // 🔧 봉투 양면테이프 옵션 데이터 가져오기
        $envelope_tape_enabled = $item['envelope_tape_enabled'] ?? 0;
        $envelope_tape_quantity = $item['envelope_tape_quantity'] ?? 0;
        $envelope_tape_price = $item['envelope_tape_price'] ?? 0;
        $envelope_additional_options_total = $item['envelope_additional_options_total'] ?? 0;

        // 🆕 단위 정보 가져오기 (unit 필드)
        // 기본값: '매' (sheets) - 대부분의 제품이 매수 단위 사용
        $unit = $item['unit'] ?? '매';

        // 📎 Phase 3: uploaded_files JSON 데이터 가져오기 (StandardUploadHandler)
        $uploaded_files_json = $item['uploaded_files'] ?? null;

        // 🔧 수량 및 단위 추가 (제품별 분기 처리)
        $product_type = $item['product_type'] ?? 'unknown';
        if (in_array($product_type, ['inserted', 'leaflet'])) {
            // 전단지/리플렛: quantity는 연수, unit은 '연'
            $quantity = floatval($item['quantity'] ?? $item['MY_amount'] ?? 1.0);
            $unit = $item['unit'] ?? '연';
        } elseif (in_array($product_type, ['littleprint', 'poster'])) {
            // 포스터: MY_amount가 실제 수량, unit은 '매'
            $quantity = floatval($item['MY_amount'] ?? $item['quantity'] ?? 1.0);
            $unit = '매';
        } else {
            // 기타 제품: 기본값 사용
            $quantity = floatval($item['quantity'] ?? 1.0);
            $unit = $item['unit'] ?? '개';
        }

        // 🔍 INSERT 직전 최종 확인 로깅
        error_log("=== INSERT 직전 변수 확인 ===");
        $debug_vars = [
            'new_no' => $new_no,
            'product_type_name' => $product_type_name,
            'img_folder_path' => $img_folder_path,
            'uploaded_files_json' => $uploaded_files_json,
            'product_info' => $product_info,
            'st_price' => $item['st_price'],
            'st_price_vat' => $item['st_price_vat'],
            'username' => $username,
            'email' => $email,
            'postcode' => $postcode,
            'address' => $address,
            'full_address' => $full_address,
            'phone' => $phone,
            'hendphone' => $hendphone,
            'final_cont' => $final_cont,
            'date' => $date,
            'order_style' => $order_style,
            'thing_cate' => $thing_cate,
            'coating_enabled' => $coating_enabled,
            'coating_type' => $coating_type,
            'coating_price' => $coating_price,
            'folding_enabled' => $folding_enabled,
            'folding_type' => $folding_type,
            'folding_price' => $folding_price,
            'creasing_enabled' => $creasing_enabled,
            'creasing_lines' => $creasing_lines,
            'creasing_price' => $creasing_price,
            'additional_options_total' => $additional_options_total,
            'premium_options' => $premium_options,
            'premium_options_total' => $premium_options_total,
            'envelope_tape_enabled' => $envelope_tape_enabled,
            'envelope_tape_quantity' => $envelope_tape_quantity,
            'envelope_tape_price' => $envelope_tape_price,
            'envelope_additional_options_total' => $envelope_additional_options_total
        ];
        error_log("BIND PARAM VARS: " . json_encode($debug_vars, JSON_UNESCAPED_UNICODE));
        error_log("============================");

        // 34 parameters: i + Type(s) + ImgFolder(s) + uploaded_files(s) + Type_1(s) + money_4(s) + money_5(s) + name(s) + email~ThingCate(10s) + coating(isi) + folding(isi) + creasing(iii) + additional(i) + premium(si) + envelope(iiii)
        // 🔧 FIX: money_4, money_5, name은 varchar이므로 's' 타입 사용 (기존 'iii' → 'sss')
        // 🔧 FIX: 전체 파라미터 개수(34개)와 타입을 정확히 일치시킴
        // 🔧 FIX: mysqli_stmt_bind_param은 참조로 전달되므로 표현식 대신 변수 사용 필수
        $st_price = strval($item['st_price'] ?? 0);
        $st_price_vat = strval($item['st_price_vat'] ?? 0);

        // 35개 파라미터 타입 문자열 (손가락으로 하나씩 세기!)
        // 1:no(i) 2:Type(s) 3:ImgFolder(s) 4:uploaded_files(s) 5:Type_1(s) 6:money_4(s) 7:money_5(s)
        // 8:name(s) 9:email(s) 10:zip(s) 11:zip1(s) 12:zip2(s) 13:phone(s) 14:Hendphone(s)
        // 15:cont(s) 16:date(s) 17:OrderStyle(s) 18:ThingCate(s)
        // 19:coating_enabled(i) 20:coating_type(s) 21:coating_price(i)
        // 22:folding_enabled(i) 23:folding_type(s) 24:folding_price(i)
        // 25:creasing_enabled(i) 26:creasing_lines(i) 27:creasing_price(i)
        // 28:additional_options_total(i)
        // 29:premium_options(s) 30:premium_options_total(i)
        // 31:envelope_tape_enabled(i) 32:envelope_tape_quantity(i) 33:envelope_tape_price(i) 34:envelope_additional_options_total(i)
        // 35:unit(s) - 🆕 단위 필드 추가
        // 타입: i(1)+s(17)+isi+isi+iii+i+si+iiii+s+d = 1+17+3+3+3+1+2+4+1+1 = 36
        // 36개 파라미터 타입 문자열 (정확한 검증 완료!)
        // 1:no(i) 2-7:Type~money_5(s×6) 8-18:name~ThingCate(s×11)
        // 19-21:coating(isi) 22-24:folding(isi) 25-27:creasing(iii) 28:additional(i)
        // 29-30:premium(si) 31-34:envelope(iiii) 35:unit(s) 36:quantity(d)
        $type_string = 'isssssssssssssssssisiisiiiiisiiiiisd';
        $type_count = strlen($type_string); // 36

        mysqli_stmt_bind_param($stmt, $type_string,
            $new_no, $product_type_name, $img_folder_path, $uploaded_files_json, $product_info, $st_price, $st_price_vat,
            $username, $email, $postcode, $address, $full_address,
            $phone, $hendphone, $final_cont, $date, $order_style, $thing_cate,
            $coating_enabled, $coating_type, $coating_price,
            $folding_enabled, $folding_type, $folding_price,
            $creasing_enabled, $creasing_lines, $creasing_price,
            $additional_options_total,
            $premium_options, $premium_options_total,
            $envelope_tape_enabled, $envelope_tape_quantity, $envelope_tape_price,
            $envelope_additional_options_total,
            $unit,      // 35번째: 단위 필드
            $quantity   // 36번째: 수량 필드 (포스터=MY_amount, 전단지=연수)
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $order_numbers[] = $new_no;

            // ✅ Phase 3: StandardUploadHandler로 파일 복사
            if (!empty($item['uploaded_files'])) {
                $copy_result = StandardUploadHandler::copyFilesForOrder(
                    $new_no,
                    $img_folder_from_cart,
                    $item['uploaded_files']
                );

                if ($copy_result['success']) {
                    error_log("주문 $new_no: " . count($copy_result['copied_files']) . "개 파일 복사 완료");
                } else {
                    error_log("주문 $new_no 파일 복사 실패: " . $copy_result['error']);
                    // 파일 복사 실패는 주문을 중단하지 않음 (경고만)
                }
            }

            // 새로운 통합 업로드 시스템 사용 - 임시 파일을 주문 폴더로 이동
            $final_upload_dir = getOrderUploadPath($new_no);
            if (!createUploadDirectory($final_upload_dir)) {
                throw new Exception('주문 파일 디렉토리 생성에 실패했습니다.');
            }
            
            $moved_files = [];
            $first_file_name = '';
            
            // 1. uploaded_files 테이블에서 파일 정보 조회
            $move_files_query = "SELECT * FROM uploaded_files WHERE session_id = ? AND product_type = ? ORDER BY upload_date ASC";
            $move_stmt = mysqli_prepare($connect, $move_files_query);
            
            if ($move_stmt) {
                mysqli_stmt_bind_param($move_stmt, 'ss', $session_id, $item['product_type']);
                mysqli_stmt_execute($move_stmt);
                $move_result = mysqli_stmt_get_result($move_stmt);
                
                while ($file_row = mysqli_fetch_assoc($move_result)) {
                    $temp_file_path = getTempUploadPath($session_id) . $file_row['file_name'];
                    $final_file_path = $final_upload_dir . $file_row['file_name'];
                    
                    // 파일 존재 확인 및 이동
                    if (file_exists($temp_file_path)) {
                        // 중복 파일명 처리
                        $counter = 1;
                        $original_final_path = $final_file_path;
                        while (file_exists($final_file_path)) {
                            $path_info = pathinfo($original_final_path);
                            $final_file_path = $path_info['dirname'] . '/' . $path_info['filename'] . '_' . $counter . '.' . $path_info['extension'];
                            $counter++;
                        }
                        
                        if (rename($temp_file_path, $final_file_path)) {
                            $moved_files[] = basename($final_file_path);
                            if (empty($first_file_name)) {
                                $first_file_name = basename($final_file_path);
                            }
                            
                            // 로그 기록
                            error_log("파일 이동 성공: $temp_file_path -> $final_file_path");
                        } else {
                            error_log("파일 이동 실패: $temp_file_path -> $final_file_path");
                        }
                    } else {
                        error_log("임시 파일 없음: $temp_file_path");
                    }
                }
                
                mysqli_stmt_close($move_stmt);
            }
            
            // 2. 스티커 주문의 경우 추가 파일 경로 확인
            if ($item['product_type'] == 'sticker') {
                $sticker_upload_paths = [
                    "../mlangprintauto/shop/uploads/" . $session_id,
                    "../uploads/" . $session_id,
                    "../mlangprintauto/upload/temp/" . $session_id
                ];
                
                foreach ($sticker_upload_paths as $sticker_path) {
                    if (is_dir($sticker_path)) {
                        $files = array_diff(scandir($sticker_path), ['.', '..']);
                        foreach ($files as $file) {
                            $source_path = $sticker_path . '/' . $file;
                            $dest_path = $final_upload_dir . '/' . $file;
                            
                            // 중복 파일명 처리
                            $counter = 1;
                            $original_dest_path = $dest_path;
                            while (file_exists($dest_path)) {
                                $path_info = pathinfo($original_dest_path);
                                $dest_path = $path_info['dirname'] . '/' . $path_info['filename'] . '_' . $counter . '.' . $path_info['extension'];
                                $counter++;
                            }
                            
                            if (is_file($source_path) && rename($source_path, $dest_path)) {
                                $moved_files[] = basename($dest_path);
                                if (empty($first_file_name)) {
                                    $first_file_name = basename($dest_path);
                                }
                                error_log("스티커 파일 이동 성공: $source_path -> $dest_path");
                            }
                        }
                        
                        // 빈 폴더 삭제
                        if (count(scandir($sticker_path)) == 2) {
                            rmdir($sticker_path);
                        }
                    }
                }
            }
            
            // 3. ThingCate 필드 업데이트 (첫 번째 파일로)
            if (!empty($first_file_name)) {
                $update_query = "UPDATE mlangorder_printauto SET ThingCate = ? WHERE no = ?";
                $update_stmt = mysqli_prepare($connect, $update_query);
                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, 'si', $first_file_name, $new_no);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }
            }
            
            // 4. 데이터베이스 정리
            $cleanup_query = "DELETE FROM uploaded_files WHERE session_id = ? AND product_type = ?";
            $cleanup_stmt = mysqli_prepare($connect, $cleanup_query);
            if ($cleanup_stmt) {
                mysqli_stmt_bind_param($cleanup_stmt, 'ss', $session_id, $item['product_type']);
                mysqli_stmt_execute($cleanup_stmt);
                mysqli_stmt_close($cleanup_stmt);
            }
            
            // 5. 스티커 파일 이동 처리 (uploads/sticker_new -> uploads/orders)
            if ($item['product_type'] === 'sticker' || strpos($item['product_type'], 'sticker') !== false) {
                $sticker_base_dir = "../uploads/sticker_new/";
                if (is_dir($sticker_base_dir)) {
                    $sticker_dirs = scandir($sticker_base_dir);
                    foreach ($sticker_dirs as $dir) {
                        // 세션 ID가 포함된 디렉토리 찾기
                        if ($dir != "." && $dir != ".." && strpos($dir, $session_id) !== false) {
                            $source_dir = $sticker_base_dir . $dir;
                            if (is_dir($source_dir)) {
                                $files = scandir($source_dir);
                                foreach ($files as $file) {
                                    if ($file != "." && $file != "..") {
                                        $source_file = $source_dir . "/" . $file;
                                        $dest_file = $final_upload_dir . "/" . $file;

                                        // 중복 파일명 처리
                                        if (file_exists($dest_file)) {
                                            $info = pathinfo($file);
                                            $basename = $info['filename'];
                                            $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
                                            $counter = 1;
                                            while (file_exists($dest_file)) {
                                                $dest_file = $final_upload_dir . "/" . $basename . "_" . $counter . $extension;
                                                $counter++;
                                            }
                                        }

                                        if (copy($source_file, $dest_file)) {
                                            $moved_files[] = $file;
                                            unlink($source_file); // 원본 파일 삭제
                                        }
                                    }
                                }
                                // 빈 디렉토리 삭제
                                if (count(scandir($source_dir)) == 2) {
                                    rmdir($source_dir);
                                }
                            }
                        }
                    }
                }
            }

            // 6. 임시 폴더 정리 (기존 시스템)
            $temp_upload_dir = "../mlangorder_printauto/upload/temp/" . $session_id;
            if (is_dir($temp_upload_dir) && count(scandir($temp_upload_dir)) == 2) {
                rmdir($temp_upload_dir);
            }
            
            // 이동된 파일 로그
            if (!empty($moved_files)) {
                error_log("주문 $new_no: " . count($moved_files) . "개 파일 이동 완료 - " . implode(', ', $moved_files));
            }
        } else {
            throw new Exception('주문 저장 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // 장바구니 주문인 경우에만 장바구니 비우기
    if (!$is_direct_order) {
        clearCart($connect, $session_id);
    }
    
    // 주문 완료 페이지로 리다이렉트
    $order_list = implode(',', $order_numbers);
    header("Location: OrderComplete_unified.php?orders=" . urlencode($order_list) . "&email=" . urlencode($email) . "&name=" . urlencode($username));
    exit;
    
} catch (Exception $e) {
    echo "<script>alert('주문 처리 중 오류가 발생했습니다: " . addslashes($e->getMessage()) . "'); history.back();</script>";
}

if ($connect) {
    mysqli_close($connect);
}
?>