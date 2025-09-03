<?php
/**
 * 통합 장바구니 주문 처리
 * 경로: MlangOrder_PrintAuto/ProcessOrder_unified.php
 */

session_start();

// 보안 상수 정의 후 데이터베이스 연결
include "../includes/db_constants.php";
include "../db.php";
$connect = $db;

// 헬퍼 함수 포함
include "../MlangPrintAuto/shop_temp_helper.php";

try {
    // POST 데이터 받기
    $session_id = $_POST['session_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
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
                
                // 스티커 데이터를 JSON 형태로 구조화
                $sticker_data = [
                    'product_type' => 'sticker',
                    'order_details' => [
                        'jong' => $jong,
                        'garo' => $garo,
                        'sero' => $sero,
                        'mesu' => $mesu,
                        'domusong' => $domusong,
                        'uhyung' => $uhyung
                    ],
                    'formatted_display' => "재질: $jong\n" .
                                         "크기: {$garo}mm × {$sero}mm\n" .
                                         "수량: " . number_format($mesu) . "매\n" .
                                         "모양: $domusong\n" .
                                         "편집비: " . number_format($uhyung) . "원",
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
                $product_info .= "수량: " . ($item['MY_amount'] ?? '') . "부\n";
                $product_info .= "주문방법: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;
                
            case 'leaflet':
                $product_type_name = '전단지';
                $color_name = getCategoryName($connect, $item['MY_type']);
                $paper_name = getCategoryName($connect, $item['MY_Fsd']);
                $size_name = getCategoryName($connect, $item['PN_type']);
                $sides = $item['POtype'] == '1' ? '단면' : '양면';
                $product_info = "인쇄색상: $color_name\n";
                $product_info .= "종이종류: $paper_name\n";
                $product_info .= "종이규격: $size_name\n";
                $product_info .= "인쇄면: $sides\n";
                $product_info .= "수량: " . ($item['MY_amount'] ?? '') . "매";
                break;
                
            case 'namecard':
                $product_type_name = '명함';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $paper_name = getCategoryName($connect, $item['MY_Fsd']);
                $sides = $item['POtype'] == '1' ? '단면' : '양면';
                $product_info = "명함종류: $type_name\n";
                $product_info .= "명함재질: $paper_name\n";
                $product_info .= "인쇄면: $sides\n";
                $product_info .= "수량: " . ($item['MY_amount'] ?? '') . "매\n";
                $product_info .= "편집디자인: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;
                
            case 'msticker':
                $product_type_name = '자석스티커';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $size_name = getCategoryName($connect, $item['PN_type']);
                $product_info = "종류: $type_name\n";
                $product_info .= "규격: $size_name\n";
                $product_info .= "수량: " . ($item['MY_amount'] ?? '') . "매\n";
                $product_info .= "편집디자인: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;
                
            case 'ncrflambeau':
                $product_type_name = '양식지/NCR';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $size_name = getCategoryName($connect, $item['MY_Fsd']);
                $color_name = getCategoryName($connect, $item['PN_type']);
                $product_info = "구분: $type_name\n";
                $product_info .= "규격: $size_name\n";
                $product_info .= "색상: $color_name\n";
                $product_info .= "수량: " . ($item['MY_amount'] ?? '') . "권\n";
                $product_info .= "편집디자인: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;
                
            case 'merchandisebond':
                $product_type_name = '상품권/쿠폰';
                $type_name = getCategoryName($connect, $item['MY_type']);
                $sides = $item['POtype'] == '1' ? '단면' : '양면';
                $after_name = getCategoryName($connect, $item['PN_type']);
                $product_info = "종류: $type_name\n";
                $product_info .= "수량: " . ($item['MY_amount'] ?? '') . "매\n";
                $product_info .= "인쇄면: $sides\n";
                $product_info .= "후가공: $after_name\n";
                $product_info .= "편집디자인: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
                break;
                
            default:
                $product_type_name = '기타';
                $product_info = '상품 정보: ' . json_encode($item, JSON_UNESCAPED_UNICODE);
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
        
        // mlangorder_printauto 테이블에 삽입
        $insert_query = "INSERT INTO mlangorder_printauto (
            no, Type, Type_1, money_4, money_5, name, email, zip, zip1, zip2, 
            phone, Hendphone, cont, date, OrderStyle, ThingCate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($connect, $insert_query);
        if (!$stmt) {
            throw new Exception('주문 처리 중 오류가 발생했습니다: ' . mysqli_error($connect));
        }
        
        $order_style = '2'; // 온라인 주문
        
        // 실제 업로드된 파일 정보 가져오기 (데이터베이스에서)
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
        $full_address = $detail_address . ' ' . $extra_address; // 주소 문자열 연결을 변수에 저장
        
        mysqli_stmt_bind_param($stmt, 'issiisssssssssss',
            $new_no, $product_type_name, $product_info, $item['st_price'], $item['st_price_vat'],
            $username, $email, $postcode, $address, $full_address,
            $phone, $hendphone, $final_cont, $date, $order_style, $thing_cate
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $order_numbers[] = $new_no;
            
            // 업로드된 파일들을 주문 번호 폴더로 이동 (중복 방지 개선)
            $final_upload_dir = "../MlangOrder_PrintAuto/upload/" . $new_no;
            if (!is_dir($final_upload_dir)) {
                mkdir($final_upload_dir, 0755, true);
                chmod($final_upload_dir, 0777);
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
                    $temp_file_path = "../MlangOrder_PrintAuto/upload/temp/" . $session_id . "/" . $file_row['file_name'];
                    $final_file_path = $final_upload_dir . "/" . $file_row['file_name'];
                    
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
                    "../MlangPrintAuto/shop/uploads/" . $session_id,
                    "../uploads/" . $session_id,
                    "../MlangPrintAuto/upload/temp/" . $session_id
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
            
            // 5. 임시 폴더 정리
            $temp_upload_dir = "../MlangOrder_PrintAuto/upload/temp/" . $session_id;
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