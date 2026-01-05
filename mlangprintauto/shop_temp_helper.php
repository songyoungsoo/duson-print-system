<?php
/**
 * 통합 shop_temp 테이블 헬퍼 함수들
 * 다양한 상품 유형을 지원하는 장바구니 기능
 * 경로: MlangPrintAuto/shop_temp_helper.php
 * 
 * 사용법:
 * include "shop_temp_helper.php";
 * $connect는 db.php에서 $db 변수를 사용
 */

/**
 * 스티커를 장바구니에 추가
 */
function addStickerToCart($connect, $session_id, $data) {
    $regdate = time();
    
    $query = "INSERT INTO shop_temp (
        session_id, product_type, jong, garo, sero, mesu, domusong, uhyung,
        st_price, st_price_vat, regdate
    ) VALUES (?, 'sticker', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, 'ssssssiddi', 
        $session_id, $data['jong'], $data['garo'], $data['sero'], 
        $data['mesu'], $data['domusong'], $data['uhyung'],
        $data['st_price'], $data['st_price_vat'], $regdate
    );
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * 명함을 장바구니에 추가
 */
function addNamecardToCart($db, $data) {
    $session_id = session_id();
    
    // 필수 파라미터 확인
    $required_fields = ['MY_type', 'Section', 'POtype', 'MY_amount', 'ordertype', 'price', 'vat_price'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => "필수 항목({$field})이 누락되었습니다."];
        }
    }

    // shop_temp 테이블에 맞게 필드 매핑
    $product_type = 'namecard';
    $MY_type = $data['MY_type']; // 명함 종류 (e.g., 275)
    $PN_type = $data['Section']; // 명함 재질 (e.g., 276)
    $POtype = $data['POtype']; // 인쇄면 (1 or 2)
    $MY_amount = $data['MY_amount']; // 수량
    $ordertype = $data['ordertype']; // 편집디자인
    $st_price = $data['price'];
    $st_price_vat = $data['vat_price'];
    $MY_comment = $data['comment'] ?? '';
    $regdate = time();

    $query = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, POtype, MY_amount, ordertype, st_price, st_price_vat, MY_comment, regdate)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        return ['success' => false, 'message' => '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db)];
    }

    mysqli_stmt_bind_param($stmt, 'ssssssdsssi', 
        $session_id, 
        $product_type, 
        $MY_type, 
        $PN_type, 
        $POtype, 
        $MY_amount, 
        $ordertype, 
        $st_price, 
        $st_price_vat, 
        $MY_comment, 
        $regdate
    );

    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => '장바구니에 추가되었습니다.'];
    } else {
        return ['success' => false, 'message' => '장바구니 추가에 실패했습니다: ' . mysqli_stmt_error($stmt)];
    }
}

/**
 * 봉투를 장바구니에 추가
 */
function addEnvelopeToCart($connect, $session_id, $data) {
    $regdate = time();
    
    $query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, MY_amount, 
        POtype, ordertype, uhyung, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'envelope', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return false;
    }
    
    $uhyung = ($data['ordertype'] === 'design') ? 1 : 0;
    
    mysqli_stmt_bind_param($stmt, 'ssssssiddsi', 
        $session_id, $data['MY_type'], $data['MY_Fsd'], $data['MY_amount'], 
        $data['POtype'], $data['ordertype'], $uhyung,
        $data['st_price'], $data['st_price_vat'], $data['MY_comment'], $regdate
    );
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * 자석스티커를 장바구니에 추가
 */
function addMstickerToCart($db, $session_id, $data) {
    // 필수 파라미터 확인 (msticker 품목별 개발 가이드 참조)
    $required_fields = ['MY_type', 'PN_type', 'MY_amount', 'ordertype', 'price', 'vat_price'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') { // empty() 대신 isset()과 빈 문자열 체크
            return ['success' => false, 'message' => "필수 항목({$field})이 누락되었습니다."];
        }
    }

    $product_type = 'msticker';
    $MY_type = $data['MY_type']; // 종류
    $PN_type = $data['PN_type']; // 규격
    $MY_amount = $data['MY_amount']; // 수량
    $ordertype = $data['ordertype']; // 편집비
    $st_price = $data['price'];
    $st_price_vat = $data['vat_price'];
    $MY_comment = $data['comment'] ?? '';
    $regdate = time();

    // 파일 업로드 정보 처리
    $uploaded_files_info_json = $data['uploaded_files_info'] ?? '[]';
    $uploaded_files_array = json_decode($uploaded_files_info_json, true);

    $img = ''; // 원본 파일명들을 콤마로 구분
    $file_path = ''; // 첫 번째 파일의 업로드 경로
    $file_info = $uploaded_files_info_json; // 전체 파일 상세 정보를 JSON 문자열로
    $upload_log = ''; // 현재는 빈 JSON

    if (!empty($uploaded_files_array)) {
        $original_names = [];
        foreach ($uploaded_files_array as $file) {
            $original_names[] = $file['original_name'] ?? '';
        }
        $img = implode(',', $original_names);
        $file_path = $uploaded_files_array[0]['upload_path'] ?? '';
    }

    $query = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_amount, ordertype, st_price, st_price_vat, MY_comment, img, file_path, file_info, upload_log, regdate)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        return ['success' => false, 'message' => '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db)];
    }

    mysqli_stmt_bind_param($stmt, 'ssssssddssssssi', 
        $session_id, 
        $product_type, 
        $MY_type, 
        $PN_type, 
        $MY_amount, 
        $ordertype, 
        $st_price, 
        $st_price_vat, 
        $MY_comment, 
        $img, 
        $file_path, 
        $file_info, 
        $upload_log, 
        $regdate
    );

    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => '장바구니에 추가되었습니다.'];
    } else {
        return ['success' => false, 'message' => '장바구니 추가에 실패했습니다: ' . mysqli_stmt_error($stmt)];
    }
}

/**
 * 쿠폰을 장바구니에 추가
 */
function addMerchandisebondToCart($connect, $session_id, $data) {
    $regdate = time();
    
    $query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, MY_amount, 
        POtype, ordertype, uhyung, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'merchandisebond', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return false;
    }
    
    $uhyung = ($data['ordertype'] === 'design') ? 1 : 0;
    
    mysqli_stmt_bind_param($stmt, 'ssssssiddsi', 
        $session_id, $data['MY_type'], $data['MY_Fsd'], $data['MY_amount'], 
        $data['POtype'], $data['ordertype'], $uhyung,
        $data['st_price'], $data['st_price_vat'], $data['MY_comment'], $regdate
    );
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * 양식지를 장바구니에 추가
 */
function addNcrflambeauToCart($connect, $session_id, $data) {
    $regdate = time();
    
    $query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, 
        ordertype, uhyung, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'ncrflambeau', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return false;
    }
    
    $uhyung = ($data['ordertype'] === 'design') ? 1 : 0;
    
    mysqli_stmt_bind_param($stmt, 'ssssssiddsi', 
        $session_id, $data['MY_type'], $data['MY_Fsd'], $data['PN_type'], 
        $data['MY_amount'], $data['ordertype'], $uhyung,
        $data['st_price'], $data['st_price_vat'], $data['MY_comment'], $regdate
    );
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * 포스터를 장바구니에 추가
 */
function addLittleprintToCart($connect, $session_id, $data) {
    $regdate = time();
    
    $query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, 
        ordertype, uhyung, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'littleprint', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return false;
    }
    
    $uhyung = ($data['ordertype'] === 'design') ? 1 : 0;
    
    mysqli_stmt_bind_param($stmt, 'ssssssiddsi', 
        $session_id, $data['MY_type'], $data['MY_Fsd'], $data['PN_type'], 
        $data['MY_amount'], $data['ordertype'], $uhyung,
        $data['st_price'], $data['st_price_vat'], $data['MY_comment'], $regdate
    );
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * 장바구니 아이템 조회 (통합)
 */
function getCartItems($connect, $session_id) {
    $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * 장바구니 아이템 삭제
 */
function removeCartItem($connect, $session_id, $item_no) {
    $query = "DELETE FROM shop_temp WHERE no = ? AND session_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, 'is', $item_no, $session_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * 장바구니 비우기
 */
function clearCart($connect, $session_id) {
    $query = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * 카테고리 번호로 한글명 조회
 */
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
        mysqli_stmt_close($stmt);
        return $row['title'];
    }
    
    mysqli_stmt_close($stmt);
    return $category_no; // 찾지 못하면 번호 그대로 반환
}

/**
 * 장바구니 아이템을 표시용 데이터로 변환
 */
function formatCartItemForDisplay($connect, $item) {
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

    $formatted = [
        'no' => $item['no'],
        'product_type' => $item['product_type'],
        'st_price' => $item['st_price'],
        'st_price_vat' => $item['st_price_vat'],
        'uhyung' => $item['uhyung'],
        'MY_comment' => $item['MY_comment'] ?? '',
        // 기존 추가 옵션 데이터 포함 (전단지용)
        'coating_enabled' => $item['coating_enabled'] ?? 0,
        'coating_type' => $item['coating_type'] ?? '',
        'coating_price' => $item['coating_price'] ?? 0,
        'folding_enabled' => $item['folding_enabled'] ?? 0,
        'folding_type' => $item['folding_type'] ?? '',
        'folding_price' => $item['folding_price'] ?? 0,
        'creasing_enabled' => $item['creasing_enabled'] ?? 0,
        'creasing_lines' => $item['creasing_lines'] ?? 0,
        'creasing_price' => $item['creasing_price'] ?? 0,
        'additional_options_total' => $item['additional_options_total'] ?? 0,
        // 🆕 명함 프리미엄 옵션 데이터 포함
        'premium_options' => $item['premium_options'] ?? '',
        'premium_options_total' => $item['premium_options_total'] ?? 0,
        // 🆕 봉투 양면테이프 옵션 데이터 포함
        'envelope_tape_enabled' => $item['envelope_tape_enabled'] ?? 0,
        'envelope_tape_quantity' => $item['envelope_tape_quantity'] ?? 0,
        'envelope_tape_price' => $item['envelope_tape_price'] ?? 0,
        'envelope_additional_options_total' => $item['envelope_additional_options_total'] ?? 0,
        // 🔧 ProductSpecFormatter를 위한 원본 필드 포함
        'MY_type' => $item['MY_type'] ?? '',
        'MY_Fsd' => $item['MY_Fsd'] ?? '',
        'PN_type' => $item['PN_type'] ?? '',
        'Section' => $item['Section'] ?? '',
        'POtype' => $item['POtype'] ?? '',
        'MY_amount' => $item['MY_amount'] ?? '',
        'mesu' => $item['mesu'] ?? '',
        'ordertype' => $item['ordertype'] ?? '',
        // 🔧 모든 제품의 한글명 필드 포함
        'MY_type_name' => $item['MY_type_name'] ?? '',
        'MY_Fsd_name' => $item['MY_Fsd_name'] ?? '',
        'PN_type_name' => $item['PN_type_name'] ?? '',
        'Section_name' => $item['Section_name'] ?? '',
        'POtype_name' => $item['POtype_name'] ?? '',
        // ✅ Phase 3: 표준 필드 보존 (ProductSpecFormatter용)
        'spec_type' => $item['spec_type'] ?? '',
        'spec_material' => $item['spec_material'] ?? '',
        'spec_size' => $item['spec_size'] ?? '',
        'spec_sides' => $item['spec_sides'] ?? '',
        'spec_design' => $item['spec_design'] ?? '',
        'quantity_display' => $item['quantity_display'] ?? '',
        'data_version' => $item['data_version'] ?? 1
    ];
    
    switch ($item['product_type']) {
        case 'sticker':
            $formatted['name'] = '스티커';
            // 양식지(ncrflambeau)는 "권" 단위 사용
            $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';
            $formatted['details'] = [
                '종류' => $item['jong'],
                '크기' => $item['garo'] . 'x' . $item['sero'] . 'mm',
                '수량' => number_format($item['mesu'], 0) . $unit,
                '옵션' => $item['domusong']
            ];
            break;
            
        case 'cadarok':
            $formatted['name'] = '카다록';
            $formatted['details'] = [
                '타입' => getCategoryName($connect, $item['MY_type']),
                '스타일' => getCategoryName($connect, $item['MY_Fsd']),
                '섹션' => getCategoryName($connect, $item['PN_type']),
                '수량' => $item['MY_amount'],
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        case 'leaflet':
            $formatted['name'] = '📄 전단지';
            $formatted['details'] = [
                '색상' => getCategoryName($connect, $item['MY_type']),
                '용지' => getCategoryName($connect, $item['MY_Fsd']),
                '사이즈' => getCategoryName($connect, $item['PN_type']),
                '수량' => $item['MY_amount'],
                '면수' => $item['POtype'] == '1' ? '단면' : '양면',
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];

            // 🆕 추가 옵션 정보 표시 (통일된 방식)
            $option_details = [];

            if (!empty($item['coating_enabled']) && $item['coating_enabled'] == 1) {
                $coating_type_name = '';
                switch ($item['coating_type'] ?? '') {
                    case 'single': $coating_type_name = '단면유광코팅'; break;
                    case 'double': $coating_type_name = '양면유광코팅'; break;
                    case 'single_matte': $coating_type_name = '단면무광코팅'; break;
                    case 'double_matte': $coating_type_name = '양면무광코팅'; break;
                    default: $coating_type_name = $item['coating_type'] ?? '코팅';
                }
                $coating_price = $item['coating_price'] ?? 0;
                if ($coating_price > 0) {
                    $option_details[] = '✨ ' . $coating_type_name . ' (+' . number_format($coating_price) . '원)';
                }
            }

            if (!empty($item['folding_enabled']) && $item['folding_enabled'] == 1) {
                $folding_type_name = '';
                switch ($item['folding_type'] ?? '') {
                    case '2fold': $folding_type_name = '2단접지'; break;
                    case '3fold': $folding_type_name = '3단접지'; break;
                    case 'accordion': $folding_type_name = '병풍접지'; break;
                    case 'gate': $folding_type_name = '대문접지'; break;
                    default: $folding_type_name = $item['folding_type'] ?? '접지';
                }
                $folding_price = $item['folding_price'] ?? 0;
                if ($folding_price > 0) {
                    $option_details[] = '📄 ' . $folding_type_name . ' (+' . number_format($folding_price) . '원)';
                }
            }

            if (!empty($item['creasing_enabled']) && $item['creasing_enabled'] == 1) {
                $creasing_lines = $item['creasing_lines'] ?? 0;
                $creasing_price = $item['creasing_price'] ?? 0;
                if ($creasing_price > 0) {
                    $option_details[] = '📏 오시: ' . $creasing_lines . '줄 (+' . number_format($creasing_price) . '원)';
                }
            }

            if (!empty($option_details)) {
                $formatted['details']['추가옵션'] = implode(', ', $option_details);

                $total_options_price = $item['additional_options_total'] ?? 0;
                if ($total_options_price > 0) {
                    $formatted['additional_options_summary'] = '총 +' . number_format($total_options_price) . '원';
                }
            }
            break;

        case 'inserted':
            $formatted['name'] = '📄 전단지';

            // 수량 표시 로직 수정
            $quantity_text = '';
            if (!empty($item['MY_amount'])) {
                $yeonsu = floatval($item['MY_amount']);
                // 1.0 -> 1, 0.5 -> 0.5 와 같이 소수점 뒤 0을 제거
                $quantity_text .= rtrim(rtrim(sprintf('%.1f', $yeonsu), '0'), '.') . '연';
            }
            if (!empty($item['mesu'])) {
                // 연수 표시가 있을 때만 괄호와 함께 매수 추가
                if (!empty($quantity_text)) {
                    $quantity_text .= ' (' . number_format($item['mesu']) . '매)';
                } else {
                    $quantity_text = number_format($item['mesu']) . '매';
                }
            }
            // 만약 둘 다 비어있으면 기존 MY_amount 값이라도 사용 (폴백)
            if (empty(trim($quantity_text))) {
                $quantity_text = $item['MY_amount'];
            }

            $formatted['details'] = [
                '색상' => getCategoryName($connect, $item['MY_type']),
                '종류' => getCategoryName($connect, $item['MY_Fsd']),
                '규격' => getCategoryName($connect, $item['PN_type']),
                '인쇄' => $item['POtype'] == '1' ? '단면' : '양면',
                '타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만',
                '수량' => $quantity_text
            ];

            // 🔍 디버그: 추가 옵션 데이터 확인
            error_log("=== 전단지 장바구니 표시 디버그 ===");
            error_log("coating_enabled: " . ($item['coating_enabled'] ?? 'null'));
            error_log("folding_enabled: " . ($item['folding_enabled'] ?? 'null'));
            error_log("creasing_enabled: " . ($item['creasing_enabled'] ?? 'null'));
            error_log("additional_options_total: " . ($item['additional_options_total'] ?? 'null'));

            // 🆕 추가 옵션 정보 표시 (봉투/명함과 동일한 방식)
            $option_details = [];

            if (!empty($item['coating_enabled']) && $item['coating_enabled'] == 1) {
                $coating_type_name = '';
                switch ($item['coating_type'] ?? '') {
                    case 'single': $coating_type_name = '단면유광코팅'; break;
                    case 'double': $coating_type_name = '양면유광코팅'; break;
                    case 'single_matte': $coating_type_name = '단면무광코팅'; break;
                    case 'double_matte': $coating_type_name = '양면무광코팅'; break;
                    default: $coating_type_name = $item['coating_type'] ?? '코팅';
                }
                $coating_price = $item['coating_price'] ?? 0;
                if ($coating_price > 0) {
                    $option_details[] = '✨ ' . $coating_type_name . ' (+' . number_format($coating_price) . '원)';
                }
            }

            if (!empty($item['folding_enabled']) && $item['folding_enabled'] == 1) {
                $folding_type_name = '';
                switch ($item['folding_type'] ?? '') {
                    case '2fold': $folding_type_name = '2단접지'; break;
                    case '3fold': $folding_type_name = '3단접지'; break;
                    case 'accordion': $folding_type_name = '병풍접지'; break;
                    case 'gate': $folding_type_name = '대문접지'; break;
                    default: $folding_type_name = $item['folding_type'] ?? '접지';
                }
                $folding_price = $item['folding_price'] ?? 0;
                if ($folding_price > 0) {
                    $option_details[] = '📄 ' . $folding_type_name . ' (+' . number_format($folding_price) . '원)';
                }
            }

            if (!empty($item['creasing_enabled']) && $item['creasing_enabled'] == 1) {
                $creasing_lines = $item['creasing_lines'] ?? 0;
                $creasing_price = $item['creasing_price'] ?? 0;
                if ($creasing_price > 0) {
                    $option_details[] = '📏 오시: ' . $creasing_lines . '줄 (+' . number_format($creasing_price) . '원)';
                }
            }

            if (!empty($option_details)) {
                $formatted['details']['추가옵션'] = implode(', ', $option_details);

                $total_options_price = $item['additional_options_total'] ?? 0;
                if ($total_options_price > 0) {
                    $formatted['additional_options_summary'] = '총 +' . number_format($total_options_price) . '원';
                }
            }
            break;

        case 'namecard':
            $formatted['name'] = '📇 명함';

            // option_details가 있으면 JSON에서 텍스트 정보 가져오기
            $option_details_json = null;
            if (!empty($item['option_details'])) {
                $option_details_json = json_decode($item['option_details'], true);
            }

            // 양식지(ncrflambeau)는 "권" 단위 사용
            $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';
            $formatted['details'] = [
                '명함종류' => $option_details_json['type_text'] ?? getCategoryName($connect, $item['MY_type']),
                '용지종류' => $option_details_json['paper_text'] ?? getCategoryName($connect, $item['Section'] ?? $item['PN_type']),
                '수량' => $option_details_json['quantity_text'] ?? ($item['MY_amount'] . $unit),
                '인쇄면' => $option_details_json['sides_text'] ?? ($item['POtype'] == '1' ? '단면' : '양면'),
                '디자인' => $option_details_json['design_text'] ?? ($item['ordertype'] === 'total' ? '디자인+인쇄' : ($item['ordertype'] === 'design' ? '디자인만' : '인쇄만'))
            ];

            // 🆕 프리미엄 옵션 정보 추가 (봉투와 동일한 방식)
            $option_details = [];

            if (!empty($item['premium_options'])) {
                $premium_options = json_decode($item['premium_options'], true);
                if ($premium_options) {
                    if (!empty($premium_options['foil_enabled'])) {
                        $foil_type_name = '';
                        switch ($premium_options['foil_type'] ?? '') {
                            case 'gold': $foil_type_name = '금박'; break;
                            case 'silver': $foil_type_name = '은박'; break;
                            case 'red': $foil_type_name = '적박'; break;
                            case 'blue': $foil_type_name = '청박'; break;
                            default: $foil_type_name = $premium_options['foil_type'] ?? '박';
                        }
                        $foil_price = $premium_options['foil_price'] ?? 0;
                        if ($foil_price > 0) {
                            $option_details[] = '✨ ' . $foil_type_name . ' (+' . number_format($foil_price) . '원)';
                        }
                    }

                    if (!empty($premium_options['numbering_enabled'])) {
                        $numbering_type_name = '';
                        switch ($premium_options['numbering_type'] ?? '') {
                            case 'sequential': $numbering_type_name = '일련번호'; break;
                            case 'custom': $numbering_type_name = '지정번호'; break;
                            default: $numbering_type_name = $premium_options['numbering_type'] ?? '넘버링';
                        }
                        $numbering_price = $premium_options['numbering_price'] ?? 0;
                        if ($numbering_price > 0) {
                            $option_details[] = '🔢 ' . $numbering_type_name . ' (+' . number_format($numbering_price) . '원)';
                        }
                    }

                    if (!empty($premium_options['perforation_enabled'])) {
                        $perforation_type_name = '';
                        switch ($premium_options['perforation_type'] ?? '') {
                            case 'straight': $perforation_type_name = '직선미싱'; break;
                            case 'dotted': $perforation_type_name = '점선미싱'; break;
                            default: $perforation_type_name = $premium_options['perforation_type'] ?? '미싱';
                        }
                        $perforation_price = $premium_options['perforation_price'] ?? 0;
                        if ($perforation_price > 0) {
                            $option_details[] = '✂️ ' . $perforation_type_name . ' (+' . number_format($perforation_price) . '원)';
                        }
                    }

                    if (!empty($premium_options['rounding_enabled'])) {
                        $rounding_price = $premium_options['rounding_price'] ?? 0;
                        if ($rounding_price > 0) {
                            $option_details[] = '🔄 귀돌이 (+' . number_format($rounding_price) . '원)';
                        }
                    }

                    if (!empty($premium_options['creasing_enabled'])) {
                        $creasing_type_name = '';
                        switch ($premium_options['creasing_type'] ?? '') {
                            case 'vertical': $creasing_type_name = '세로오시'; break;
                            case 'horizontal': $creasing_type_name = '가로오시'; break;
                            default: $creasing_type_name = $premium_options['creasing_type'] ?? '오시';
                        }
                        $creasing_price = $premium_options['creasing_price'] ?? 0;
                        if ($creasing_price > 0) {
                            $option_details[] = '📏 ' . $creasing_type_name . ' (+' . number_format($creasing_price) . '원)';
                        }
                    }
                }
            }

            // 옵션 정보가 있으면 details에 추가
            if (!empty($option_details)) {
                $formatted['details']['프리미엄옵션'] = implode(', ', $option_details);

                // 총 옵션 가격
                $total_premium_price = $item['premium_options_total'] ?? 0;
                if ($total_premium_price > 0) {
                    $formatted['additional_options_summary'] = '총 +' . number_format($total_premium_price) . '원';
                }
            }
            break;
            
        case 'envelope':
            $formatted['name'] = '📨 봉투';
            // 양식지(ncrflambeau)는 "권" 단위 사용
            $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';

            // 한글명 필드 우선 사용, 없으면 DB 조회
            $type_name = $item['MY_type_name'] ?? getCategoryName($connect, $item['MY_type']);
            $section_name = $item['Section_name'] ?? getCategoryName($connect, $item['Section']);
            $potype_name = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');

            $formatted['details'] = [
                '타입' => $type_name,
                '용지' => $section_name,
                '수량' => $item['MY_amount'] . $unit,
                '면수' => $potype_name,
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];

            // 🆕 추가 옵션 정보 표시 (명함과 동일한 방식)
            $option_details = [];

            // 양면테이프 옵션
            if (!empty($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == 1) {
                $tape_quantity = $item['envelope_tape_quantity'] ?? 0;
                $tape_price = $item['envelope_tape_price'] ?? 0;
                if ($tape_price > 0) {
                    $option_details[] = '📎 양면테이프: ' . number_format($tape_quantity) . '개 (+' . number_format($tape_price) . '원)';
                }
            }

            // 코팅 옵션
            if (!empty($item['coating_enabled']) && $item['coating_enabled'] == 1) {
                $coating_type_name = '';
                switch ($item['coating_type'] ?? '') {
                    case 'single': $coating_type_name = '단면유광코팅'; break;
                    case 'double': $coating_type_name = '양면유광코팅'; break;
                    case 'single_matte': $coating_type_name = '단면무광코팅'; break;
                    case 'double_matte': $coating_type_name = '양면무광코팅'; break;
                    default: $coating_type_name = $item['coating_type'] ?? '코팅';
                }
                $coating_price = $item['coating_price'] ?? 0;
                if ($coating_price > 0) {
                    $option_details[] = '✨ ' . $coating_type_name . ' (+' . number_format($coating_price) . '원)';
                }
            }

            // 접지 옵션
            if (!empty($item['folding_enabled']) && $item['folding_enabled'] == 1) {
                $folding_type_name = '';
                switch ($item['folding_type'] ?? '') {
                    case '2fold': $folding_type_name = '2단접지'; break;
                    case '3fold': $folding_type_name = '3단접지'; break;
                    case 'accordion': $folding_type_name = '병풍접지'; break;
                    case 'gate': $folding_type_name = '대문접지'; break;
                    default: $folding_type_name = $item['folding_type'] ?? '접지';
                }
                $folding_price = $item['folding_price'] ?? 0;
                if ($folding_price > 0) {
                    $option_details[] = '📄 ' . $folding_type_name . ' (+' . number_format($folding_price) . '원)';
                }
            }

            // 오시 옵션
            if (!empty($item['creasing_enabled']) && $item['creasing_enabled'] == 1) {
                $creasing_lines = $item['creasing_lines'] ?? 0;
                $creasing_price = $item['creasing_price'] ?? 0;
                if ($creasing_price > 0) {
                    $option_details[] = '📏 오시: ' . $creasing_lines . '줄 (+' . number_format($creasing_price) . '원)';
                }
            }

            // 옵션 정보가 있으면 details에 추가
            if (!empty($option_details)) {
                $formatted['details']['추가옵션'] = implode(', ', $option_details);

                // 총 옵션 가격 계산 (envelope_additional_options_total 또는 additional_options_total 사용)
                $total_options_price = $item['envelope_additional_options_total'] ?? $item['additional_options_total'] ?? 0;
                if ($total_options_price > 0) {
                    $formatted['additional_options_summary'] = '총 +' . number_format($total_options_price) . '원';
                }
            }
            break;
            
        case 'msticker':
            $formatted['name'] = '🧲 자석스티커';
            $unit = '매';

            // 한글명 필드 우선 사용, 없으면 DB 조회
            $type_name = $item['MY_type_name'] ?? getCategoryName($connect, $item['MY_type']);
            $section_name = $item['Section_name'] ?? getCategoryName($connect, $item['Section']);
            $potype_name = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');

            $formatted['details'] = [
                '종류' => $type_name,
                '규격' => $section_name,  // Section = 규격/재질
                '수량' => number_format($item['MY_amount'], 0) . $unit,
                '면수' => $potype_name,
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        case 'merchandisebond':
            $formatted['name'] = '🎫 상품권';
            $unit = '매';

            // 한글명 필드 우선 사용, 없으면 DB 조회
            // 상품권은 Section을 재질로 사용
            $type_name = $item['MY_type_name'] ?? getCategoryName($connect, $item['MY_type']);
            $section_name = $item['Section_name'] ?? getCategoryName($connect, $item['Section']);
            $potype_name = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');

            $formatted['details'] = [
                '타입' => $type_name,
                '용지' => $section_name,  // Section 사용
                '수량' => $item['MY_amount'] . $unit,
                '면수' => $potype_name,
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;

        case 'cadarok':
            $formatted['name'] = '📘 카달로그';
            $unit = '매';

            // 한글명 필드 우선 사용, 없으면 DB 조회
            $type_name = $item['MY_type_name'] ?? getCategoryName($connect, $item['MY_type']);
            $section_name = $item['Section_name'] ?? getCategoryName($connect, $item['Section']);
            $potype_name = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');

            $formatted['details'] = [
                '타입' => $type_name,
                '용지' => $section_name,
                '수량' => number_format($item['MY_amount'], 0) . $unit,
                '면수' => $potype_name,
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;

        case 'ncrflambeau':
            $formatted['name'] = '📋 양식지';
            $unit = '권';

            // 한글명 필드 우선 사용, 없으면 DB 조회
            // NCR 필드 매핑: MY_type=도수, PN_type=타입, MY_Fsd=용지
            $type_name = $item['PN_type_name'] ?? getCategoryName($connect, $item['PN_type']);  // 타입
            $fsd_name = $item['MY_Fsd_name'] ?? getCategoryName($connect, $item['MY_Fsd']);     // 용지
            $mytype_name = $item['MY_type_name'] ?? getCategoryName($connect, $item['MY_type']); // 도수

            $formatted['details'] = [
                '타입' => $type_name,
                '용지' => $fsd_name,
                '도수' => $mytype_name,  // MY_type이 도수 정보
                '수량' => $item['MY_amount'] . $unit,
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        case 'littleprint':
        case 'poster':  // 레거시 호환
            $formatted['name'] = '🖼️ 포스터';
            $unit = '매';

            // 한글명 필드 우선 사용, 없으면 DB 조회
            // 포스터는 Section을 용지로 사용
            $type_name = $item['MY_type_name'] ?? getCategoryName($connect, $item['MY_type']);
            $section_name = $item['Section_name'] ?? getCategoryName($connect, $item['Section']);  // Section = 용지
            $pntype_name = $item['PN_type_name'] ?? getCategoryName($connect, $item['PN_type']);
            $potype_name = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');

            $formatted['details'] = [
                '타입' => $type_name,
                '용지' => $section_name,  // Section을 용지로 사용
                '규격' => $pntype_name,
                '수량' => $item['MY_amount'] . $unit,
                '면수' => $potype_name,
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        default:
            $formatted['name'] = '기타 상품';
            $formatted['details'] = [];
    }
    
    return $formatted;
}

/**
 * 장바구니 총액 계산
 */
function calculateCartTotal($connect, $session_id) {
    $query = "SELECT SUM(st_price) as total, SUM(st_price_vat) as total_vat, COUNT(*) as count 
              FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return ['total' => 0, 'total_vat' => 0, 'count' => 0];
    }
    
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $data;
}
?>