<?php
/**
 * 통합 shop_temp 테이블 헬퍼 함수들
 * 다양한 상품 유형을 지원하는 장바구니 기능
 * 경로: mlangprintauto/shop_temp_helper.php
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
    $formatted = [
        'no' => $item['no'],
        'product_type' => $item['product_type'],
        'st_price' => $item['st_price'],
        'st_price_vat' => $item['st_price_vat'],
        'uhyung' => $item['uhyung'],
        'MY_comment' => $item['MY_comment'] ?? ''
    ];
    
    switch ($item['product_type']) {
        case 'sticker':
            $formatted['name'] = '스티커';
            $formatted['details'] = [
                '종류' => $item['jong'],
                '크기' => $item['garo'] . 'x' . $item['sero'] . 'mm',
                '수량' => number_format($item['mesu']) . '매',
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
            $formatted['name'] = '전단지';
            $formatted['details'] = [
                '색상' => getCategoryName($connect, $item['MY_type']),
                '용지' => getCategoryName($connect, $item['MY_Fsd']),
                '사이즈' => getCategoryName($connect, $item['PN_type']),
                '수량' => $item['MY_amount'],
                '면수' => $item['POtype'] == '1' ? '단면' : '양면',
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        case 'namecard':
            $formatted['name'] = '📇 명함';
            
            // option_details가 있으면 JSON에서 텍스트 정보 가져오기
            $option_details = null;
            if (!empty($item['option_details'])) {
                $option_details = json_decode($item['option_details'], true);
            }
            
            $formatted['details'] = [
                '명함종류' => $option_details['type_text'] ?? getCategoryName($connect, $item['MY_type']),
                '용지종류' => $option_details['paper_text'] ?? getCategoryName($connect, $item['PN_type']),
                '수량' => $option_details['quantity_text'] ?? ($item['MY_amount'] . '매'),
                '인쇄면' => $option_details['sides_text'] ?? ($item['POtype'] == '1' ? '단면' : '양면'),
                '디자인' => $option_details['design_text'] ?? ($item['ordertype'] === 'total' ? '디자인+인쇄' : ($item['ordertype'] === 'design' ? '디자인만' : '인쇄만'))
            ];
            break;
            
        case 'envelope':
            $formatted['name'] = '봉투';
            $formatted['details'] = [
                '타입' => getCategoryName($connect, $item['MY_type']),
                '용지' => getCategoryName($connect, $item['MY_Fsd']),
                '수량' => $item['MY_amount'] . '매',
                '면수' => $item['POtype'] == '1' ? '단면' : '양면',
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        case 'msticker':
            $formatted['name'] = '자석스티커';
            $formatted['details'] = [
                '타입' => getCategoryName($connect, $item['MY_type']),
                '용지' => getCategoryName($connect, $item['MY_Fsd']),
                '수량' => $item['MY_amount'] . '매',
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        case 'merchandisebond':
            $formatted['name'] = '쿠폰';
            $formatted['details'] = [
                '타입' => getCategoryName($connect, $item['MY_type']),
                '용지' => getCategoryName($connect, $item['MY_Fsd']),
                '수량' => $item['MY_amount'] . '매',
                '면수' => $item['POtype'] == '1' ? '단면' : '양면',
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        case 'ncrflambeau':
            $formatted['name'] = '양식지';
            $formatted['details'] = [
                '타입' => getCategoryName($connect, $item['MY_type']),
                '용지' => getCategoryName($connect, $item['MY_Fsd']),
                '규격' => getCategoryName($connect, $item['PN_type']),
                '수량' => $item['MY_amount'],
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
            ];
            break;
            
        case 'littleprint':
            $formatted['name'] = '포스터';
            $formatted['details'] = [
                '타입' => getCategoryName($connect, $item['MY_type']),
                '용지' => getCategoryName($connect, $item['MY_Fsd']),
                '규격' => getCategoryName($connect, $item['PN_type']),
                '수량' => $item['MY_amount'],
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