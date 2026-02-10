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

// CSRF 검증
include_once __DIR__ . '/../includes/csrf.php';
csrf_verify_or_die();

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
require_once __DIR__ . '/../includes/DataAdapter.php';
require_once __DIR__ . '/../includes/ensure_order_table_columns.php';

ensure_order_table_columns($connect);

try {
    // POST 데이터 받기
    $session_id = $_POST['session_id'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $email = $_POST['email'] ?? '';

    // "0"이나 빈 문자열이면 세션 또는 이메일에서 가져오기 시도
    if (empty($username) || $username === '0') {
        // 1. 세션에서 사용자 이름 가져오기
        if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name']) && $_SESSION['user_name'] !== '0') {
            $username = $_SESSION['user_name'];
        }
        // 2. 이메일에서 추출
        elseif ((empty($username) || $username === '0') && !empty($email)) {
            $email_parts = explode('@', $email);
            $username = $email_parts[0];
        }
        // 3. 기본값 사용
        elseif (empty($username) || $username === '0') {
            $username = '주문자';
        }
    }

    $phone = $_POST['phone'] ?? '';
    $hendphone = $_POST['Hendphone'] ?? '';
    $address_option = $_POST['address_option'] ?? 'different';
    $postcode = $_POST['sample6_postcode'] ?? '';
    $address = $_POST['sample6_address'] ?? '';
    $detail_address = $_POST['sample6_detailAddress'] ?? '';
    $extra_address = $_POST['sample6_extraAddress'] ?? '';
    $cont = $_POST['cont'] ?? '';
    $delivery_method = $_POST['delivery_method'] ?? '택배';  // 물품수령방법 (택배/방문/오토바이/다마스)
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
    
    // 결제방법 받기 (bank 컬럼에 저장, bankname은 입금자명)
    $payment_method = $_POST['payment_method'] ?? '계좌이체';
    $bankname = $_POST['bankname'] ?? '';
    
    // 사업자 정보 받기
    $is_business = isset($_POST['is_business']) ? 1 : 0;
    $business_name = $_POST['business_name'] ?? '';  // 상호(회사명)
    $business_number = $_POST['business_number'] ?? '';
    $business_owner = $_POST['business_owner'] ?? '';
    $business_type = $_POST['business_type'] ?? '';
    $business_item = $_POST['business_item'] ?? '';
    $business_address = $_POST['business_address'] ?? '';
    $tax_invoice_email = $_POST['tax_invoice_email'] ?? '';
    
    // 로그인 회원이 사업자 체크를 안 했어도 users 테이블에 사업자 정보가 있으면 자동 반영
    if (!$is_business && isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $user_biz_query = "SELECT business_name, business_number, business_owner, business_type, business_item, business_address, tax_invoice_email FROM users WHERE id = ?";
        $user_biz_stmt = mysqli_prepare($connect, $user_biz_query);
        if ($user_biz_stmt) {
            mysqli_stmt_bind_param($user_biz_stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($user_biz_stmt);
            $user_biz_result = mysqli_stmt_get_result($user_biz_stmt);
            $user_biz = mysqli_fetch_assoc($user_biz_result);
            mysqli_stmt_close($user_biz_stmt);
            
            if ($user_biz && !empty($user_biz['business_number'])) {
                // 회원 DB에 사업자등록번호가 있으면 사업자 정보 자동 반영
                $is_business = 1;
                $business_name = $user_biz['business_name'] ?? '';
                $business_number = $user_biz['business_number'] ?? '';
                $business_owner = $user_biz['business_owner'] ?? '';
                $business_type = $user_biz['business_type'] ?? '';
                $business_item = $user_biz['business_item'] ?? '';
                $business_address = $user_biz['business_address'] ?? '';
                $tax_invoice_email = $user_biz['tax_invoice_email'] ?? '';
                error_log("사업자 정보 자동 반영: user_id=" . $_SESSION['user_id'] . ", business_number=" . $business_number);
            }
        }
    }
    
    // bizname: 사업자 정보 요약 저장 (DB bizname 컬럼 활용)
    // 형식: "상호명 (사업자등록번호)" (관리자 OrderView에서 '사업자명' 필드로 표시)
    $bizname = '';
    if ($is_business) {
        if (!empty($business_name)) {
            $bizname = $business_name;
            if (!empty($business_number)) {
                $bizname .= ' (' . $business_number . ')';
            }
        } elseif (!empty($business_number)) {
            $bizname = $business_number;
        }
    }
    
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

        // ✅ Phase 2: 표준화된 데이터 사용 (Flat JSON 통일)
        // 모든 제품을 동일한 flat 구조로 처리하여 OrderComplete에서 일관성 있게 파싱 가능
        if (isset($item['data_version']) && $item['data_version'] == 2) {
            // 신규 데이터: product_data_json 직접 사용 (이미 표준화됨)
            $product_data = json_decode($item['product_data_json'], true);
            error_log("Phase 2: 신규 데이터 사용 - product_type: {$item['product_type']}, data_version: 2");
        } else {
            // 레거시 데이터: DataAdapter로 변환
            $product_data = DataAdapter::legacyToStandard($item, $item['product_type']);
            error_log("Phase 2: 레거시 데이터 변환 - product_type: {$item['product_type']}, data_version: " . ($item['data_version'] ?? '1'));
        }

        // ✅ Phase 3 FIX: data_version을 명시적으로 JSON에 포함 (OrderComplete에서 필수)
        $product_data['data_version'] = isset($item['data_version']) && $item['data_version'] == 2 ? 2 : 1;
        error_log("Phase 3 FIX: data_version 추가됨 - " . $product_data['data_version']);

        // ✅ 2026-01-13 FIX: Type_1 JSON에 레거시 필드 포함 (OrderView에서 formatSticker() 등 호환)
        // 스티커: jong, garo, sero, domusong, mesu
        if ($item['product_type'] === 'sticker') {
            $product_data['jong'] = $item['jong'] ?? '';
            $product_data['garo'] = $item['garo'] ?? '';
            $product_data['sero'] = $item['sero'] ?? '';
            $product_data['domusong'] = $item['domusong'] ?? '';
            $product_data['mesu'] = $item['mesu'] ?? '';
            $product_data['ordertype'] = $item['ordertype'] ?? 'print';
            error_log("Sticker legacy fields added to product_data: jong={$product_data['jong']}, size={$product_data['garo']}x{$product_data['sero']}");
        }
        // 명함, 봉투, 카다록 등: MY_type, Section, POtype, MY_amount
        elseif (in_array($item['product_type'], ['namecard', 'envelope', 'cadarok', 'littleprint', 'poster', 'merchandisebond', 'msticker', 'ncrflambeau'])) {
            $product_data['MY_type'] = $item['MY_type'] ?? '';
            $product_data['Section'] = $item['Section'] ?? '';
            $product_data['PN_type'] = $item['PN_type'] ?? '';
            $product_data['POtype'] = $item['POtype'] ?? '';
            $product_data['MY_amount'] = $item['MY_amount'] ?? '';
            $product_data['MY_Fsd'] = $item['MY_Fsd'] ?? '';
            $product_data['ordertype'] = $item['ordertype'] ?? 'print';
        }
        // 전단지/리플렛: MY_type, MY_Fsd, PN_type, POtype, MY_amount, mesu
        elseif (in_array($item['product_type'], ['inserted', 'leaflet'])) {
            $product_data['MY_type'] = $item['MY_type'] ?? '';
            $product_data['MY_Fsd'] = $item['MY_Fsd'] ?? '';
            $product_data['PN_type'] = $item['PN_type'] ?? '';
            $product_data['POtype'] = $item['POtype'] ?? '';
            $product_data['MY_amount'] = $item['MY_amount'] ?? '';
            $product_data['mesu'] = $item['mesu'] ?? '';
            $product_data['ordertype'] = $item['ordertype'] ?? 'print';
        }

        // product_type_name 설정 (표시용)
        $product_type_names = [
            'sticker' => '스티커',
            'namecard' => '명함',
            'inserted' => '전단지',
            'leaflet' => '전단지',
            'envelope' => '봉투',
            'cadarok' => '카다록',
            'littleprint' => '포스터',
            'poster' => '포스터',
            'merchandisebond' => '상품권/쿠폰',
            'ncrflambeau' => '양식지/NCR',
            'msticker' => '자석스티커'
        ];
        $product_type_name = $product_type_names[$item['product_type']] ?? '기타';

        // Flat JSON 생성 (모든 제품 통일된 구조 - nested 구조 제거)
        $product_info = json_encode($product_data, JSON_UNESCAPED_UNICODE);

        error_log("Phase 2: {$product_type_name} 주문 처리 완료 - flat JSON 길이: " . strlen($product_info) . " bytes");
        
        // 디자인 여부
        $design_info = ($item['uhyung'] == 1 || $item['ordertype'] === 'design') ? '디자인+인쇄' : '인쇄만';
        
        // 사업자 정보가 있으면 기타사항에 추가
        $final_cont = $cont;
        if ($is_business && !empty($business_number)) {
            $business_info_text = "\n\n=== 사업자 정보 ===\n";
            if (!empty($business_name)) {
                $business_info_text .= "상호(회사명): " . $business_name . "\n";
            }
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
        // ✅ Phase 3: 표준 필드 추가 (spec_*, quantity_*, price_*, data_version)
        $insert_query = "INSERT INTO mlangorder_printauto (
            no, Type, product_type, ImgFolder, uploaded_files, Type_1, money_4, money_5, name, email, zip, zip1, zip2,
            phone, Hendphone, delivery, bizname, bank, bankname, cont, date, OrderStyle, ThingCate,
            coating_enabled, coating_type, coating_price,
            folding_enabled, folding_type, folding_price,
            creasing_enabled, creasing_lines, creasing_price,
            additional_options_total,
            premium_options, premium_options_total,
            envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price,
            envelope_additional_options_total, unit, quantity,
            spec_type, spec_material, spec_size, spec_sides, spec_design,
            quantity_value, quantity_unit, quantity_sheets, quantity_display,
            price_supply, price_vat, price_vat_amount, data_version
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
            // 기존 방식: uploaded_files 테이블 조회 (PHP 8.0+ 예외 처리)
            $thing_cate = '';
            try {
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
            } catch (mysqli_sql_exception $e) {
                // uploaded_files 테이블이 없으면 무시 (PHP 8.0+)
                error_log("uploaded_files 테이블 조회 스킵 (테이블 없음): " . $e->getMessage());
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

        // ✅ Phase 3: 표준 필드 추출 ($product_data에서)
        $spec_type = $product_data['spec_type'] ?? '';
        $spec_material = $product_data['spec_material'] ?? '';
        $spec_size = $product_data['spec_size'] ?? '';
        $spec_sides = $product_data['spec_sides'] ?? '';
        $spec_design = $product_data['spec_design'] ?? '';
        $quantity_value = $product_data['quantity_value'] ?? 0;
        $quantity_unit = $product_data['quantity_unit'] ?? '매';
        $quantity_sheets = $product_data['quantity_sheets'] ?? 0;
        $quantity_display = $product_data['quantity_display'] ?? '';

        // ✅ 2026-01-17 SSOT FIX: 스티커 수량 정규화 보장
        // 스티커의 경우 mesu 필드에서 quantity_value 추출 보장 (NULL 방지)
        if ($item['product_type'] === 'sticker' || $item['product_type'] === 'sticker_new') {
            $mesu = intval($item['mesu'] ?? $product_data['mesu'] ?? 0);
            if ($mesu > 0 && $quantity_value == 0) {
                $quantity_value = $mesu;
                $quantity_unit = '매';
                $quantity_sheets = $mesu;
                $quantity_display = number_format($mesu) . '매';
                error_log("SSOT FIX: Sticker quantity normalized - mesu={$mesu} → quantity_value={$quantity_value}, display={$quantity_display}");
            }
        }

        // ✅ 2026-01-17 SSOT FIX: 명함/봉투 수량 정규화 (10 미만 → ×1000)
        if (in_array($item['product_type'], ['namecard', 'envelope'])) {
            $mesu = intval($item['mesu'] ?? $product_data['mesu'] ?? 0);
            $my_amount = floatval($item['MY_amount'] ?? $product_data['MY_amount'] ?? 0);

            if ($mesu > 0 && $quantity_value == 0) {
                $quantity_value = $mesu;
                $quantity_unit = '매';
                $quantity_sheets = $mesu;
                $quantity_display = number_format($mesu) . '매';
            } elseif ($my_amount > 0 && $my_amount < 10 && $quantity_value == 0) {
                // 10 미만이면 천 단위로 해석
                $quantity_value = intval($my_amount * 1000);
                $quantity_unit = '매';
                $quantity_sheets = $quantity_value;
                $quantity_display = number_format($quantity_value) . '매';
                error_log("SSOT FIX: Namecard/Envelope quantity normalized - MY_amount={$my_amount} → quantity_value={$quantity_value}");
            }
        }

        // ✅ 2026-01-17 SSOT FIX: 전단지 수량 정규화 (연 단위)
        if (in_array($item['product_type'], ['inserted', 'leaflet'])) {
            $my_amount = floatval($item['MY_amount'] ?? $product_data['MY_amount'] ?? 0);
            $mesu = intval($item['mesu'] ?? $product_data['mesu'] ?? 0);

            if ($my_amount > 0 && $quantity_value == 0) {
                $quantity_value = $my_amount;
                $quantity_unit = '연';
                $quantity_sheets = $mesu > 0 ? $mesu : 0;  // DB에서 조회 필요
                if (floor($my_amount) == $my_amount) {
                    $quantity_display = number_format($my_amount) . '연';
                } else {
                    $quantity_display = rtrim(rtrim(number_format($my_amount, 2), '0'), '.') . '연';
                }
                if ($quantity_sheets > 0) {
                    $quantity_display .= ' (' . number_format($quantity_sheets) . '매)';
                }
                error_log("SSOT FIX: Inserted quantity normalized - MY_amount={$my_amount}, mesu={$mesu} → display={$quantity_display}");
            }
        }

        // ✅ 2026-01-17 SSOT FIX: NCR양식지 수량 정규화 (권 단위)
        if ($item['product_type'] === 'ncrflambeau') {
            $my_amount = intval($item['MY_amount'] ?? $product_data['MY_amount'] ?? 0);

            if ($my_amount > 0 && $quantity_value == 0) {
                $quantity_value = $my_amount;
                $quantity_unit = '권';
                // 매수 계산: 권 × 50 × 복사매수(기본 2)
                $multiplier = 2;  // 기본값
                if (!empty($item['MY_Fsd']) || !empty($product_data['MY_Fsd'])) {
                    $materialText = $item['MY_Fsd'] ?? $product_data['MY_Fsd'] ?? '';
                    if (preg_match('/([2-4])매/u', $materialText, $matches)) {
                        $multiplier = intval($matches[1]);
                    }
                }
                $quantity_sheets = $my_amount * 50 * $multiplier;
                $quantity_display = number_format($my_amount) . '권 (' . number_format($quantity_sheets) . '매)';
                error_log("SSOT FIX: NCR quantity normalized - MY_amount={$my_amount}, multiplier={$multiplier} → display={$quantity_display}");
            }
        }

        $price_supply = $product_data['price_supply'] ?? 0;
        $price_vat = $product_data['price_vat'] ?? 0;
        $price_vat_amount = $product_data['price_vat_amount'] ?? 0;
        $data_version = $product_data['data_version'] ?? 1;

        // 34 parameters: i + Type(s) + ImgFolder(s) + uploaded_files(s) + Type_1(s) + money_4(s) + money_5(s) + name(s) + email~ThingCate(10s) + coating(isi) + folding(isi) + creasing(iii) + additional(i) + premium(si) + envelope(iiii)
        // 🔧 FIX: money_4, money_5, name은 varchar이므로 's' 타입 사용 (기존 'iii' → 'sss')
        // 🔧 FIX: 전체 파라미터 개수(34개)와 타입을 정확히 일치시킴
        // 🔧 FIX: mysqli_stmt_bind_param은 참조로 전달되므로 표현식 대신 변수 사용 필수
        $st_price = strval($item['st_price'] ?? 0);
        $st_price_vat = strval($item['st_price_vat'] ?? 0);

        // ✅ 54개 파라미터 타입 문자열 (3번 검증!)
        // 1:no(i) 2:Type(s) 3:product_type(s) 4:ImgFolder(s) 5:uploaded_files(s) 6:Type_1(s) 7:money_4(s) 8:money_5(s)
        // 9:name(s) 10:email(s) 11:zip(s) 12:zip1(s) 13:zip2(s) 14:phone(s) 15:Hendphone(s)
        // 16:delivery(s) 17:bizname(s) 18:bank(s) 19:bankname(s)
        // 20:cont(s) 21:date(s) 22:OrderStyle(s) 23:ThingCate(s)
        // 24:coating_enabled(i) 25:coating_type(s) 26:coating_price(i)
        // 27:folding_enabled(i) 28:folding_type(s) 29:folding_price(i)
        // 30:creasing_enabled(i) 31:creasing_lines(i) 32:creasing_price(i)
        // 33:additional_options_total(i)
        // 34:premium_options(s) 35:premium_options_total(i)
        // 36:envelope_tape_enabled(i) 37:envelope_tape_quantity(i) 38:envelope_tape_price(i) 39:envelope_additional_options_total(i)
        // 40:unit(s) 41:quantity(d)
        // 42:spec_type(s) 43:spec_material(s) 44:spec_size(s) 45:spec_sides(s) 46:spec_design(s)
        // 47:quantity_value(d) 48:quantity_unit(s) 49:quantity_sheets(i) 50:quantity_display(s)
        // 51:price_supply(i) 52:price_vat(i) 53:price_vat_amount(i) 54:data_version(i)
        $type_string = 'issssssssssssssssssssssisiisiiiiisiiiiisdsssssdsisiiii';
        $placeholder_count = substr_count($insert_query, '?');  // 검증 1
        $type_count = strlen($type_string);                      // 검증 2
        $var_count = 54;                                         // 검증 3

        if ($placeholder_count !== $type_count || $type_count !== $var_count) {
            error_log("🔴 bind_param 개수 불일치! placeholder=$placeholder_count, type=$type_count, var=$var_count");
            throw new Exception("bind_param 개수 불일치 발생");
        }

        mysqli_stmt_bind_param($stmt, $type_string,
            $new_no, $product_type_name, $product_type, $img_folder_path, $uploaded_files_json, $product_info, $st_price, $st_price_vat,
            $username, $email, $postcode, $address, $full_address,
            $phone, $hendphone, $delivery_method, $bizname, $payment_method, $bankname, $final_cont, $date, $order_style, $thing_cate,
            $coating_enabled, $coating_type, $coating_price,
            $folding_enabled, $folding_type, $folding_price,
            $creasing_enabled, $creasing_lines, $creasing_price,
            $additional_options_total,
            $premium_options, $premium_options_total,
            $envelope_tape_enabled, $envelope_tape_quantity, $envelope_tape_price,
            $envelope_additional_options_total,
            $unit, $quantity,
            // ✅ Phase 3: 표준 필드 추가 (12개)
            $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
            $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
            $price_supply, $price_vat, $price_vat_amount, $data_version
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $order_numbers[] = $new_no;

            // ✅ Phase 4: Dual-Write - 새 테이블(orders, order_items)에도 저장
            try {
                // 1. orders 테이블 삽입
                $orderSql = "INSERT INTO orders (
                    legacy_no, customer_name, customer_email, customer_phone, customer_mobile,
                    shipping_postcode, shipping_address, shipping_detail,
                    total_supply, total_vat, total_amount,
                    order_date, data_version
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 3)";

                $orderStmt = mysqli_prepare($connect, $orderSql);
                if ($orderStmt) {
                    $o_total_supply = intval($st_price);
                    $o_total_vat = intval($st_price_vat);
                    $o_total_amount = $o_total_vat > 0 ? $o_total_vat : $o_total_supply;

                    mysqli_stmt_bind_param($orderStmt, "isssssssiiis",
                        $new_no, $username, $email, $phone, $hendphone,
                        $postcode, $address, $full_address,
                        $o_total_supply, $o_total_vat, $o_total_amount, $date
                    );

                    if (mysqli_stmt_execute($orderStmt)) {
                        $orderId = mysqli_insert_id($connect);
                        mysqli_stmt_close($orderStmt);

                        // 2. order_items 테이블 삽입
                        $itemSql = "INSERT INTO order_items (
                            order_id, legacy_no, product_type, product_type_display,
                            spec_type, spec_material, spec_size, spec_sides, spec_design,
                            qty_value, qty_unit_code, qty_sheets,
                            price_supply, price_vat, price_unit,
                            img_folder, thing_cate, ordertype, work_memo, legacy_data
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                        $itemStmt = mysqli_prepare($connect, $itemSql);
                        if ($itemStmt) {
                            // QuantityFormatter 단위 코드 매핑
                            require_once __DIR__ . '/../includes/QuantityFormatter.php';
                            $unitCode = QuantityFormatter::getProductUnitCode($product_type);

                            $v_order_id = $orderId;
                            $v_legacy_no = $new_no;
                            $v_product_type = $product_type;
                            $v_product_type_display = $product_type_name;
                            $v_spec_type = $spec_type ?? '';
                            $v_spec_material = $spec_material ?? '';
                            $v_spec_size = $spec_size ?? '';
                            $v_spec_sides = $spec_sides ?? '';
                            $v_spec_design = $spec_design ?? '';
                            $v_qty_value = floatval($quantity_value);
                            $v_qty_unit_code = $unitCode;
                            $v_qty_sheets = intval($quantity_sheets);
                            $v_price_supply = intval($st_price);
                            $v_price_vat = intval($st_price_vat);
                            $v_price_unit = $v_qty_value > 0 ? intval($v_price_supply / $v_qty_value) : 0;
                            $v_img_folder = $img_folder_path ?? '';
                            $v_thing_cate = $thing_cate ?? '';
                            $v_ordertype = $order_style ?? '';
                            $v_work_memo = $final_cont ?? '';
                            $v_legacy_data = json_encode($item, JSON_UNESCAPED_UNICODE);

                            mysqli_stmt_bind_param($itemStmt, "iisssssssdsiiiiissss",
                                $v_order_id, $v_legacy_no, $v_product_type, $v_product_type_display,
                                $v_spec_type, $v_spec_material, $v_spec_size, $v_spec_sides, $v_spec_design,
                                $v_qty_value, $v_qty_unit_code, $v_qty_sheets,
                                $v_price_supply, $v_price_vat, $v_price_unit,
                                $v_img_folder, $v_thing_cate, $v_ordertype, $v_work_memo, $v_legacy_data
                            );

                            if (mysqli_stmt_execute($itemStmt)) {
                                error_log("Dual-Write 성공: 주문 $new_no → orders.order_id=$orderId");
                            } else {
                                error_log("Dual-Write order_items 실패: " . mysqli_stmt_error($itemStmt));
                            }
                            mysqli_stmt_close($itemStmt);
                        }
                    } else {
                        error_log("Dual-Write orders 실패: " . mysqli_stmt_error($orderStmt));
                    }
                }
            } catch (Exception $e) {
                error_log("Dual-Write 예외: " . $e->getMessage());
                // Dual-Write 실패해도 주문은 계속 진행
            }

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
            
            // 1. uploaded_files 테이블에서 파일 정보 조회 (PHP 8.0+ 예외 처리)
            try {
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
            } catch (mysqli_sql_exception $e) {
                // uploaded_files 테이블이 없으면 무시 (PHP 8.0+)
                error_log("uploaded_files 테이블 파일 조회 스킵 (테이블 없음): " . $e->getMessage());
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
            
            // 4. 데이터베이스 정리 (PHP 8.0+ 예외 처리)
            try {
                $cleanup_query = "DELETE FROM uploaded_files WHERE session_id = ? AND product_type = ?";
                $cleanup_stmt = mysqli_prepare($connect, $cleanup_query);
                if ($cleanup_stmt) {
                    mysqli_stmt_bind_param($cleanup_stmt, 'ss', $session_id, $item['product_type']);
                    mysqli_stmt_execute($cleanup_stmt);
                    mysqli_stmt_close($cleanup_stmt);
                }
            } catch (mysqli_sql_exception $e) {
                // uploaded_files 테이블이 없으면 무시 (PHP 8.0+)
                error_log("uploaded_files 정리 스킵 (테이블 없음): " . $e->getMessage());
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