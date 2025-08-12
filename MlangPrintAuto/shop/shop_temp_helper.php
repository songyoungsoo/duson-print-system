<?php
/**
 * 통합 shop_temp 테이블 헬퍼 함수들
 * 다양한 상품 유형을 지원하는 장바구니 기능
 * 경로: MlangPrintAuto/shop/shop_temp_helper.php
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
    mysqli_stmt_bind_param($stmt, 'ssssssiddi', 
        $session_id, $data['jong'], $data['garo'], $data['sero'], 
        $data['mesu'], $data['domusong'], $data['uhyung'],
        $data['st_price'], $data['st_price_vat'], $regdate
    );
    
    return mysqli_stmt_execute($stmt);
}

/**
 * 카다록을 장바구니에 추가
 */
function addCadarokToCart($connect, $session_id, $data) {
    $regdate = time();
    
    $query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, 
        ordertype, uhyung, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'cadarok', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    $uhyung = ($data['ordertype'] === 'design') ? 1 : 0;
    
    mysqli_stmt_bind_param($stmt, 'ssssssiddsi', 
        $session_id, $data['MY_type'], $data['MY_Fsd'], $data['PN_type'], 
        $data['MY_amount'], $data['ordertype'], $uhyung,
        $data['st_price'], $data['st_price_vat'], $data['MY_comment'], $regdate
    );
    
    return mysqli_stmt_execute($stmt);
}

/**
 * 전단지를 장바구니에 추가
 */
function addLeafletToCart($connect, $session_id, $data) {
    $regdate = time();
    
    $query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, 
        POtype, ordertype, uhyung, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'leaflet', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    $uhyung = ($data['ordertype'] === 'design') ? 1 : 0;
    
    mysqli_stmt_bind_param($stmt, 'sssssssiddsi', 
        $session_id, $data['MY_type'], $data['MY_Fsd'], $data['PN_type'], 
        $data['MY_amount'], $data['POtype'], $data['ordertype'], $uhyung,
        $data['st_price'], $data['st_price_vat'], $data['MY_comment'], $regdate
    );
    
    return mysqli_stmt_execute($stmt);
}

/**
 * 명함을 장바구니에 추가
 */
function addNamecardToCart($connect, $session_id, $data) {
    $regdate = time();
    
    $query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, MY_amount, 
        POtype, ordertype, uhyung, st_price, st_price_vat, MY_comment, regdate
    ) VALUES (?, 'namecard', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    $uhyung = ($data['ordertype'] === 'design') ? 1 : 0;
    
    mysqli_stmt_bind_param($stmt, 'ssssssiddsi', 
        $session_id, $data['MY_type'], $data['MY_Fsd'], $data['MY_amount'], 
        $data['POtype'], $data['ordertype'], $uhyung,
        $data['st_price'], $data['st_price_vat'], $data['MY_comment'], $regdate
    );
    
    return mysqli_stmt_execute($stmt);
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
    $uhyung = ($data['ordertype'] === 'design') ? 1 : 0;
    
    mysqli_stmt_bind_param($stmt, 'ssssssiddsi', 
        $session_id, $data['MY_type'], $data['MY_Fsd'], $data['MY_amount'], 
        $data['POtype'], $data['ordertype'], $uhyung,
        $data['st_price'], $data['st_price_vat'], $data['MY_comment'], $regdate
    );
    
    return mysqli_stmt_execute($stmt);
}

/**
 * 장바구니 아이템 조회 (통합)
 */
function getCartItems($connect, $session_id) {
    $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    
    return mysqli_stmt_get_result($stmt);
}

/**
 * 장바구니 아이템 삭제
 */
function removeCartItem($connect, $session_id, $item_no) {
    $query = "DELETE FROM shop_temp WHERE no = ? AND session_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'is', $item_no, $session_id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * 장바구니 비우기
 */
function clearCart($connect, $session_id) {
    $query = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * 카테고리 번호로 한글명 조회
 */
function getCategoryName($connect, $category_no) {
    if (!$category_no) return '';
    
    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $category_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['title'];
    }
    
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
        'MY_comment' => $item['MY_comment']
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
            $formatted['name'] = '명함';
            $formatted['details'] = [
                '타입' => getCategoryName($connect, $item['MY_type']),
                '용지' => getCategoryName($connect, $item['MY_Fsd']),
                '수량' => $item['MY_amount'] . '매',
                '면수' => $item['POtype'] == '1' ? '단면' : '양면',
                '주문타입' => $item['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'
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
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}
?>