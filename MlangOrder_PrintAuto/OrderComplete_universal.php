<?php
/**
 * 🌟 통합 주문완료 시스템 - Universal OrderComplete
 * 모든 제품의 주문완료를 처리하는 공통 시스템
 * 경로: MlangOrder_PrintAuto/OrderComplete_universal.php
 * 
 * 기능:
 * - 모든 제품 타입 지원 (sticker, namecard, envelope 등)
 * - 마지막 주문 제품으로 "계속 쇼핑하기" 이동
 * - 반응형 디자인 지원
 * - 다양한 주문 형태 지원 (단건/다건/장바구니)
 */

session_start();

// 데이터베이스 연결
include "../db.php";
$connect = $db;

// ===========================================
// 🔧 공통 함수들
// ===========================================

/**
 * 카테고리 번호로 한글명 조회
 */
function getCategoryName($connect, $category_no) {
    if (!$category_no) return '';
    
    $query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) return $category_no;
    
    mysqli_stmt_bind_param($stmt, 's', $category_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }
    
    mysqli_stmt_close($stmt);
    return $category_no;
}

/**
 * 마지막 주문 품목 페이지 URL 생성
 * 핵심 기능: 계속 쇼핑하기를 마지막 주문 제품으로 연결
 */
function getLastOrderProductUrl($order_list) {
    if (empty($order_list)) {
        return '../MlangPrintAuto/shop/cart.php';
    }
    
    // 가장 최근 주문 (첫 번째 주문)
    $latest_order = $order_list[0];
    $product_type = $latest_order['Type'] ?? '';
    
    // 주문 데이터에서 상품 타입 확인
    $type_data = $latest_order['Type_1'] ?? '';
    $json_data = json_decode($type_data, true);
    
    // JSON 데이터에서 product_type 추출
    if ($json_data && isset($json_data['product_type'])) {
        $product_type_key = $json_data['product_type'];
    } else {
        // Type 필드에서 상품 타입 추정
        $product_type_key = detectProductType($product_type);
    }
    
    // 상품 타입별 URL 매핑
    $product_urls = getProductUrlMapping();
    
    return $product_urls[$product_type_key] ?? '../MlangPrintAuto/shop/cart.php';
}

/**
 * 상품 타입 자동 감지
 */
function detectProductType($product_type) {
    $product_type_lower = strtolower($product_type);
    $type_mapping = [
        'sticker' => ['sticker', '스티커'],
        'namecard' => ['namecard', '명함'],
        'envelope' => ['envelope', '봉투'],
        'littleprint' => ['poster', '포스터', 'little', '소형인쇄'],
        'inserted' => ['leaflet', '전단', 'flyer', '리플렛'],
        'cadarok' => ['catalog', '카다록', '카탈로그'],
        'merchandisebond' => ['bond', '상품권', '쿠폰'],
        'ncrflambeau' => ['ncr', '전표', 'form'],
        'msticker' => ['magnetic', '자석', 'magnet']
    ];
    
    foreach ($type_mapping as $key => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($product_type_lower, $keyword) !== false) {
                return $key;
            }
        }
    }
    
    return 'sticker'; // 기본값
}

/**
 * 제품별 URL 매핑
 */
function getProductUrlMapping() {
    return [
        'sticker' => '../MlangPrintAuto/shop/view_modern.php',
        'namecard' => '../MlangPrintAuto/NameCard/index.php',
        'envelope' => '../MlangPrintAuto/envelope/index.php',
        'littleprint' => '../MlangPrintAuto/LittlePrint/index.php',
        'inserted' => '../MlangPrintAuto/inserted/index.php',
        'cadarok' => '../MlangPrintAuto/cadarok/index.php',
        'merchandisebond' => '../MlangPrintAuto/MerchandiseBond/index.php',
        'ncrflambeau' => '../MlangPrintAuto/NcrFlambeau/index.php',
        'msticker' => '../MlangPrintAuto/msticker/index.php'
    ];
}

/**
 * 제품 상세 정보 표시
 */
function displayProductDetails($connect, $order) {
    if (empty($order['Type_1'])) return '';
    
    $type_data = $order['Type_1'];
    $json_data = json_decode($type_data, true);
    
    $html = '<div class="product-options">';
    
    if ($json_data && is_array($json_data)) {
        // JSON 데이터 처리
        $product_type = $json_data['product_type'] ?? '';
        
        switch($product_type) {
            case 'sticker':
                $details = $json_data['order_details'] ?? $json_data;
                if (isset($details['jong'])) $html .= '<span class="option-item">재질: ' . htmlspecialchars($details['jong']) . '</span>';
                if (isset($details['garo']) && isset($details['sero'])) {
                    $html .= '<span class="option-item">크기: ' . htmlspecialchars($details['garo']) . '×' . htmlspecialchars($details['sero']) . 'mm</span>';
                }
                if (isset($details['mesu'])) $html .= '<span class="option-item">수량: ' . number_format($details['mesu']) . '매</span>';
                if (isset($details['uhyung'])) $html .= '<span class="option-item">편집: ' . htmlspecialchars($details['uhyung']) . '</span>';
                if (isset($details['domusong'])) $html .= '<span class="option-item">모양: ' . htmlspecialchars($details['domusong']) . '</span>';
                break;
                
            case 'envelope':
                if (isset($json_data['MY_type'])) $html .= '<span class="option-item">타입: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                if (isset($json_data['MY_Fsd'])) $html .= '<span class="option-item">용지: ' . getCategoryName($connect, $json_data['MY_Fsd']) . '</span>';
                if (isset($json_data['MY_amount'])) $html .= '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '매</span>';
                if (isset($json_data['POtype'])) $html .= '<span class="option-item">인쇄: ' . ($json_data['POtype'] == '1' ? '단면' : '양면') . '</span>';
                break;
                
            case 'namecard':
                if (isset($json_data['MY_type'])) $html .= '<span class="option-item">타입: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                if (isset($json_data['Section'])) $html .= '<span class="option-item">용지: ' . getCategoryName($connect, $json_data['Section']) . '</span>';
                if (isset($json_data['MY_amount'])) $html .= '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '매</span>';
                if (isset($json_data['POtype'])) $html .= '<span class="option-item">인쇄: ' . ($json_data['POtype'] == '1' ? '단면' : '양면') . '</span>';
                break;
                
            case 'merchandisebond':
                if (isset($json_data['MY_type'])) $html .= '<span class="option-item">구분: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                if (isset($json_data['MY_Fsd'])) $html .= '<span class="option-item">종류: ' . getCategoryName($connect, $json_data['MY_Fsd']) . '</span>';
                if (isset($json_data['MY_amount'])) $html .= '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '매</span>';
                break;
                
            case 'cadarok':
                if (isset($json_data['MY_type'])) $html .= '<span class="option-item">타입: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                if (isset($json_data['MY_Fsd'])) $html .= '<span class="option-item">스타일: ' . getCategoryName($connect, $json_data['MY_Fsd']) . '</span>';
                if (isset($json_data['MY_amount'])) $html .= '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '</span>';
                break;
                
            case 'littleprint':
                if (isset($json_data['MY_type'])) $html .= '<span class="option-item">타입: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                if (isset($json_data['MY_Fsd'])) $html .= '<span class="option-item">용지: ' . getCategoryName($connect, $json_data['MY_Fsd']) . '</span>';
                if (isset($json_data['MY_amount'])) $html .= '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '</span>';
                break;
                
            default:
                // 기타 제품 타입 처리
                foreach ($json_data as $key => $value) {
                    if (!empty($value) && $key != 'product_type') {
                        $display_key = ucfirst($key);
                        $display_value = is_numeric($value) && in_array($key, ['MY_type', 'MY_Fsd', 'PN_type']) 
                            ? getCategoryName($connect, $value) 
                            : $value;
                        $html .= '<span class="option-item">' . htmlspecialchars($display_key) . ': ' . htmlspecialchars($display_value) . '</span>';
                    }
                }
                break;
        }
    } else {
        // 일반 텍스트 데이터 처리 (전단지 등)
        $lines = explode("\n", $type_data);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $html .= '<span class="option-item">' . htmlspecialchars($line) . '</span>';
            }
        }
    }
    
    $html .= '</div>';
    
    // 요청사항 표시
    if (!empty($order['cont'])) {
        $html .= '<div class="request-note">';
        $html .= '<strong>💬 요청사항:</strong><br>';
        $html .= nl2br(htmlspecialchars($order['cont']));
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * 수량 추출
 */
function extractQuantity($order) {
    if (empty($order['Type_1'])) return '1';
    
    $json_data = json_decode($order['Type_1'], true);
    if ($json_data && is_array($json_data)) {
        // JSON 데이터에서 수량 추출
        $details = $json_data['order_details'] ?? $json_data;
        if (isset($details['MY_amount'])) {
            return number_format($details['MY_amount']);
        } elseif (isset($details['mesu'])) {
            return number_format($details['mesu']);
        }
    } else {
        // 일반 텍스트에서 수량 추출
        if (preg_match('/수량:\s*([0-9.]+)매/', $order['Type_1'], $matches)) {
            return number_format(floatval($matches[1]));
        }
    }
    
    return '1';
}

// ===========================================
// 🎯 메인 로직 시작
// ===========================================

// GET 파라미터에서 데이터 가져오기
$orders = $_GET['orders'] ?? '';
$email = $_GET['email'] ?? '';
$name = $_GET['name'] ?? '';

if (empty($orders)) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='../MlangPrintAuto/shop/cart.php';</script>";
    exit;
}

// 주문 번호들을 배열로 변환
$order_numbers = explode(',', $orders);
$order_list = [];
$total_amount = 0;
$total_amount_vat = 0;

// 각 주문 정보 조회
foreach ($order_numbers as $order_no) {
    $order_no = trim($order_no);
    if (!empty($order_no)) {
        $query = "SELECT * FROM MlangOrder_PrintAuto WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($connect, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $order_list[] = $row;
                $total_amount += $row['money_4'];
                $total_amount_vat += $row['money_5'];
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if (empty($order_list)) {
    echo "<script>alert('주문 정보를 찾을 수 없습니다.'); location.href='../MlangPrintAuto/shop/cart.php';</script>";
    exit;
}

// 첫 번째 주문의 고객 정보 사용
$first_order = $order_list[0];

// 페이지 설정
$page_title = '🎉 주문 완료 - Universal System';
$current_page = 'order_complete';

// 공통 헤더 포함
include "../includes/header.php";
include "../includes/nav.php";
?>

<!-- 📱 Universal OrderComplete 스타일 -->
<style>
/* Universal Design System - 모든 제품 지원 */
:root {
    --primary-blue: #667eea;
    --primary-purple: #764ba2;
    --success-green: #27ae60;
    --warning-orange: #f39c12;
    --error-red: #e74c3c;
    --pastel-blue: #E6F3FF;
    --pastel-lavender: #F0E6FF;
    --pastel-mint: #E6FFF0;
    --pastel-peach: #FFE6E6;
    --pastel-yellow: #FFFCE6;
    --text-primary: #2c3e50;
    --text-secondary: #566a7e;
    --border-light: #e1e8ed;
    --shadow-light: 0 2px 8px rgba(0,0,0,0.08);
    --shadow-medium: 0 4px 15px rgba(0,0,0,0.1);
}

.universal-container {
    max-width: 1200px;
    margin: 10px auto;
    padding: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow-medium);
    font-family: 'Noto Sans KR', sans-serif;
}

/* 헤더 관련 CSS 제거됨 */

/* 📊 주문 테이블 */
.order-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow-light);
}

.order-table thead th {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-purple) 100%);
    color: white;
    font-weight: 600;
    padding: 15px 12px;
    text-align: center;
    font-size: 0.9rem;
}

.order-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid var(--border-light);
}

.order-table tbody tr:nth-child(even) {
    background: var(--pastel-blue);
}

.order-table tbody tr:hover {
    background: var(--pastel-mint) !important;
    transform: scale(1.01);
    box-shadow: var(--shadow-medium);
}

.order-table td {
    padding: 15px 12px;
    vertical-align: top;
    font-size: 0.9rem;
}

/* 테이블 컬럼 스타일 */
.col-order-no {
    width: 10%;
    text-align: center;
    font-weight: 600;
    color: var(--primary-blue);
}

.col-product {
    width: 20%;
    font-weight: 600;
    color: var(--text-primary);
}

.col-details {
    width: 35%;
}

.col-quantity {
    width: 10%;
    text-align: center;
    font-weight: 600;
    color: var(--warning-orange);
}

.col-price {
    width: 15%;
    text-align: right;
    font-weight: 700;
    color: var(--error-red);
    font-size: 1rem;
}

/* 가격 컨테이너 스타일 */
.price-container {
    text-align: right;
    line-height: 1.3;
}

.price-supply {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 2px;
}

.price-total {
    margin: 3px 0;
}

.price-vat {
    font-size: 0.75rem;
    color: #888;
    margin-top: 2px;
}

.col-status {
    width: 10%;
    text-align: center;
}

/* 상품 옵션 스타일 */
.product-options {
    margin-top: 8px;
    padding: 10px;
    background: rgba(255,255,255,0.8);
    border-radius: 8px;
    border-left: 3px solid var(--primary-blue);
}

.option-item {
    display: inline-block;
    margin: 2px 8px 2px 0;
    padding: 4px 8px;
    background: linear-gradient(135deg, var(--pastel-lavender) 0%, var(--pastel-blue) 100%);
    border-radius: 15px;
    font-size: 0.8rem;
    color: var(--text-secondary);
    font-weight: 500;
}

/* 요청사항 스타일 */
.request-note {
    margin-top: 8px;
    padding: 10px;
    background: var(--pastel-yellow);
    border-left: 4px solid var(--warning-orange);
    border-radius: 8px;
    font-size: 0.85rem;
    color: #856404;
}

/* 정보 카드들 */
.info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.info-card {
    background: linear-gradient(135deg, var(--pastel-mint) 0%, var(--pastel-blue) 100%);
    border-radius: 8px;
    padding: 15px;
    border: 2px solid rgba(255,255,255,0.5);
    backdrop-filter: blur(5px);
}

.info-card h3 {
    margin: 0 0 15px 0;
    font-size: 1.2rem;
    color: var(--text-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-row {
    display: flex;
    margin-bottom: 10px;
    align-items: center;
}

/* 기존 중복 정의 제거됨 */

.info-value {
    flex: 1;
    color: #2c3e50 !important;
    font-weight: 500;
}

.info-label {
    width: 100px;
    font-weight: 600;
    color: #2c3e50 !important;
    font-size: 0.9rem;
}

/* 📄 인쇄용 스타일 */
@media print {
    /* 헤더, 푸터, 네비게이션 숨김 */
    header, footer, nav, .nav, .navbar, .header, .footer,
    .action-section {
        display: none !important;
    }
    
    /* 페이지 여백 최소화 */
    @page {
        margin: 0.5in;
        size: A4;
    }
    
    body {
        margin: 0;
        padding: 0;
        font-size: 12pt;
        line-height: 1.3;
        color: black !important;
        background: white !important;
    }
    
    .universal-container {
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 10px !important;
        background: white !important;
    }
    
    /* 색상 제거 - 흑백 인쇄용 */
    .info-card {
        background: white !important;
        border: 1px solid #333 !important;
        border-radius: 5px !important;
        page-break-inside: avoid;
        margin-bottom: 15px !important;
    }
    
    .order-table {
        border: 1px solid #333 !important;
        background: white !important;
    }
    
    .order-table th {
        background: #f0f0f0 !important;
        color: black !important;
        border: 1px solid #333 !important;
    }
    
    .order-table td {
        border: 1px solid #333 !important;
        color: black !important;
    }
    
    /* 가격 강조 유지 */
    .price-supply span {
        font-size: 14pt !important;
        font-weight: bold !important;
    }
    
    /* 인쇄용 헤더 스타일 */
    .print-header {
        display: block !important;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 3px solid #333;
    }
    
    .print-company-info {
        text-align: center;
        margin-bottom: 15px;
    }
    
    .print-company-info h1 {
        font-size: 24pt !important;
        font-weight: bold !important;
        margin: 0 0 8px 0 !important;
        color: black !important;
        letter-spacing: 2px;
    }
    
    .company-details p {
        margin: 2px 0 !important;
        font-size: 9pt !important;
        color: #666 !important;
    }
    
    .print-doc-title {
        text-align: center;
        margin: 15px 0;
        padding: 10px 0;
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
    }
    
    .print-doc-title h2 {
        font-size: 18pt !important;
        font-weight: bold !important;
        margin: 0 0 5px 0 !important;
        color: black !important;
        letter-spacing: 1px;
    }
    
    .print-date {
        font-size: 10pt !important;
        color: #666 !important;
        margin: 0 !important;
    }
    
    .print-customer-info {
        margin: 15px 0;
    }
    
    .customer-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1px solid #333 !important;
    }
    
    .customer-table td {
        padding: 8px 12px !important;
        border: 1px solid #666 !important;
        font-size: 10pt !important;
        color: black !important;
        background: #f9f9f9 !important;
    }
    
    .customer-table strong {
        color: black !important;
        font-weight: bold !important;
    }
    
    .print-footer {
        display: block !important;
        page-break-inside: avoid;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #333;
    }
    
    .print-payment-info {
        text-align: center;
    }
    
    .print-payment-info h3 {
        font-size: 14pt !important;
        font-weight: bold !important;
        margin: 0 0 10px 0 !important;
        color: black !important;
        letter-spacing: 1px;
    }
    
    .payment-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 2px solid #333 !important;
        margin: 10px 0 !important;
    }
    
    .payment-table td {
        padding: 8px 12px !important;
        border: 1px solid #666 !important;
        font-size: 10pt !important;
        color: black !important;
        text-align: center !important;
        background: white !important;
    }
    
    .payment-table strong {
        color: black !important;
        font-weight: bold !important;
    }
    
    .print-contact-notice {
        text-align: center;
        margin-top: 15px;
        padding: 8px;
        border: 1px solid #999;
        background: #f5f5f5 !important;
    }
    
    .print-contact-notice p {
        font-size: 9pt !important;
        color: #333 !important;
        margin: 0 !important;
    }
}

/* 🎬 액션 버튼 구역 */
.action-section {
    background: linear-gradient(135deg, var(--pastel-peach) 0%, var(--pastel-yellow) 100%);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    margin: 20px 0;
}

.action-section h3 {
    margin: 0 0 20px 0;
    font-size: 1.3rem;
    color: var(--text-primary);
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 15px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    position: relative;
    border: none;
    cursor: pointer;
    overflow: hidden;
}

.btn-continue {
    background: linear-gradient(135deg, var(--success-green) 0%, #2ecc71 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
}

.btn-print {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-purple) 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.2);
}

.btn-action::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-action:hover::before {
    left: 100%;
}

/* 🎨 상태 배지 */
.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
}

.status-pending {
    background: var(--pastel-yellow);
    color: #856404;
    border: 1px solid var(--warning-orange);
}

.status-processing {
    background: var(--pastel-blue);
    color: var(--primary-blue);
    border: 1px solid var(--primary-blue);
}

.status-completed {
    background: var(--pastel-mint);
    color: var(--success-green);
    border: 1px solid var(--success-green);
}

/* 📱 반응형 디자인 */
@media (max-width: 768px) {
    .universal-container {
        margin: 10px;
        padding: 15px;
    }
    
    .success-header h1 {
        font-size: 1.8rem;
    }
    
    .success-stats {
        gap: 20px;
    }
    
    .order-table {
        font-size: 0.8rem;
    }
    
    .order-table td {
        padding: 10px 8px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-action {
        min-width: 200px;
    }
}

/* 🖨️ 세련된 인쇄 스타일 */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    
    body {
        font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        color: #000 !important;
        background: white !important;
        margin: 0;
        padding: 15mm;
    }
    
    .action-section,
    .btn-action,
    .success-header {
        display: none !important;
    }
    
    .universal-container {
        box-shadow: none !important;
        padding: 0 !important;
        max-width: none !important;
        margin: 0 !important;
        background: white !important;
    }
    
    /* 🏢 회사 헤더 - 고급스러운 디자인 */
    .print-header {
        display: block !important;
        page-break-inside: avoid;
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 3px double #000;
        position: relative;
    }
    
    .print-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #666, #000, #666);
    }
    
    .print-company-info {
        text-align: center;
        margin-bottom: 20px;
        position: relative;
    }
    
    .print-company-info h1 {
        font-size: 28pt !important;
        font-weight: 900 !important;
        margin: 10px 0 !important;
        color: #000 !important;
        letter-spacing: 3px;
        text-shadow: 1px 1px 0px #ccc;
        position: relative;
    }
    
    .print-company-info h1::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: #000;
    }
    
    .company-details {
        margin-top: 15px;
        padding: 10px;
        background: #f8f9fa !important;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
    
    .company-details p {
        margin: 3px 0 !important;
        font-size: 10pt !important;
        color: #495057 !important;
        font-weight: 500;
    }
    
    /* 📋 문서 제목 - 전문적인 스타일 */
    .print-doc-title {
        text-align: center;
        margin: 25px 0;
        padding: 15px 0;
        border: 2px solid #000;
        border-radius: 10px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        position: relative;
    }
    
    .print-doc-title::before {
        content: '✓';
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        background: #000;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16pt;
        font-weight: bold;
    }
    
    .print-doc-title h2 {
        font-size: 22pt !important;
        font-weight: 800 !important;
        margin: 0 0 8px 0 !important;
        color: #000 !important;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    
    .print-date {
        font-size: 11pt !important;
        color: #495057 !important;
        margin: 0 !important;
        font-weight: 600;
        background: #fff !important;
        padding: 3px 15px;
        border-radius: 15px;
        display: inline-block;
        border: 1px solid #dee2e6;
    }
    
    /* 👤 고객 정보 - 세련된 테이블 */
    .print-customer-info {
        margin: 25px 0;
        page-break-inside: avoid;
    }
    
    .customer-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 2px solid #000 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
    }
    
    .customer-table td {
        padding: 12px 15px !important;
        border: 1px solid #495057 !important;
        font-size: 11pt !important;
        color: #000 !important;
        background: #ffffff !important;
        position: relative;
    }
    
    .customer-table td:first-child {
        background: #f8f9fa !important;
        font-weight: 700;
        border-right: 2px solid #000 !important;
    }
    
    .customer-table strong {
        color: #000 !important;
        font-weight: 800 !important;
    }
    
    /* 📊 주문 테이블 - 프로페셔널 디자인 */
    .order-table {
        display: table !important;
        width: 100% !important;
        border-collapse: collapse !important;
        border: 2px solid #000 !important;
        margin: 20px 0 !important;
        page-break-inside: avoid;
    }
    
    .order-table thead {
        display: table-header-group !important;
        background: #000 !important;
    }
    
    .order-table thead th {
        padding: 15px 10px !important;
        border: 1px solid #fff !important;
        font-size: 11pt !important;
        font-weight: 800 !important;
        color: #fff !important;
        text-align: center !important;
        background: #000 !important;
    }
    
    .order-table tbody {
        display: table-row-group !important;
    }
    
    .order-table tbody tr {
        display: table-row !important;
        page-break-inside: avoid;
    }
    
    .order-table tbody td {
        display: table-cell !important;
        padding: 12px 10px !important;
        border: 1px solid #495057 !important;
        font-size: 10pt !important;
        color: #000 !important;
        background: #fff !important;
        vertical-align: top !important;
    }
    
    .order-row {
        display: table-row !important;
        opacity: 1 !important;
        transform: none !important;
        animation: none !important;
    }
    
    .order-table .col-order-no {
        text-align: center !important;
        font-weight: 700 !important;
        background: #f8f9fa !important;
    }
    
    .order-table .col-product {
        font-weight: 700 !important;
        color: #000 !important;
    }
    
    .order-table .col-quantity {
        text-align: center !important;
        font-weight: 700 !important;
    }
    
    .order-table .col-price {
        text-align: right !important;
    }
    
    .price-supply span {
        font-size: 12pt !important;
        font-weight: 800 !important;
        color: #000 !important;
    }
    
    .price-total span {
        font-size: 10pt !important;
        color: #495057 !important;
    }
    
    .price-vat {
        font-size: 8pt !important;
        color: #6c757d !important;
    }
    
    .status-badge {
        background: #000 !important;
        color: #fff !important;
        padding: 5px 10px !important;
        border-radius: 15px !important;
        font-size: 9pt !important;
        font-weight: 700 !important;
    }
    
    /* 💳 결제 정보 푸터 - 우아한 디자인 */
    .print-footer {
        display: block !important;
        page-break-inside: avoid;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 3px double #000;
        position: relative;
    }
    
    .print-footer::before {
        content: '';
        position: absolute;
        top: -2px;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #000, transparent);
    }
    
    .print-payment-info {
        text-align: center;
        position: relative;
    }
    
    .print-payment-info h3 {
        font-size: 16pt !important;
        font-weight: 800 !important;
        margin: 0 0 15px 0 !important;
        color: #000 !important;
        letter-spacing: 2px;
        position: relative;
        display: inline-block;
    }
    
    .print-payment-info h3::before,
    .print-payment-info h3::after {
        content: '◆';
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10pt;
        color: #495057;
    }
    
    .print-payment-info h3::before {
        left: -25px;
    }
    
    .print-payment-info h3::after {
        right: -25px;
    }
    
    .payment-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 2px solid #000 !important;
        margin: 15px 0 !important;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1) !important;
    }
    
    .payment-table td {
        padding: 12px 15px !important;
        border: 1px solid #495057 !important;
        font-size: 11pt !important;
        color: #000 !important;
        text-align: center !important;
        background: #fff !important;
    }
    
    .payment-table td:first-child {
        background: #f8f9fa !important;
        font-weight: 800 !important;
        border-right: 2px solid #000 !important;
    }
    
    .payment-table strong {
        color: #000 !important;
        font-weight: 800 !important;
    }
    
    .print-contact-notice {
        text-align: center;
        margin-top: 20px;
        padding: 15px;
        border: 2px solid #495057;
        border-radius: 10px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        position: relative;
    }
    
    .print-contact-notice::before {
        content: '📞';
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        background: #000;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12pt;
    }
    
    .print-contact-notice p {
        font-size: 10pt !important;
        color: #000 !important;
        margin: 5px 0 !important;
        font-weight: 600;
    }
}

/* ✨ 로딩 애니메이션 */
.order-row {
    opacity: 0;
    transform: translateY(20px);
    animation: slideInUp 0.5s ease forwards;
}

@keyframes slideInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<div class="universal-container">
    <!-- 인쇄용 헤더 (화면에서는 숨김, 인쇄시에만 표시) -->
    <div class="print-header" style="display: none;">
        <div class="print-company-info">
            <h1>두손기획인쇄</h1>
            <div class="company-details">
                <p>서울 영등포구 영등포로36길 9, 송호빌딩 1층</p>
                <p>TEL: 02-2632-1830 | FAX: 02-2632-1831 | www.dsp114.com</p>
            </div>
        </div>
        <div class="print-doc-title">
            <h2>주문 확인서</h2>
            <div class="print-date">발행일: <?php echo date('Y년 m월 d일'); ?></div>
        </div>
        <div class="print-customer-info">
            <table class="customer-table">
                <tr>
                    <td><strong>고객명:</strong> <?php echo htmlspecialchars($name ?: $first_order['name']); ?></td>
                    <td><strong>주문일:</strong> <?php echo htmlspecialchars($first_order['date'] ?? date('Y-m-d')); ?></td>
                </tr>
                <tr>
                    <td><strong>연락처:</strong> <?php echo htmlspecialchars($first_order['phone'] ?? $first_order['Hendphone'] ?? '정보없음'); ?></td>
                    <td><strong>이메일:</strong> <?php echo htmlspecialchars($email ?: $first_order['email'] ?: '정보없음'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 📊 주문 테이블 -->
    <table class="order-table">
        <thead>
            <tr>
                <th class="col-order-no">주문번호</th>
                <th class="col-product">상품명</th>
                <th class="col-details">상세 정보</th>
                <th class="col-quantity">수량</th>
                <th class="col-price">금액</th>
                <th class="col-status">상태</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_list as $index => $order): ?>
            <tr class="order-row" style="animation-delay: <?php echo $index * 0.1; ?>s">
                <!-- 주문번호 -->
                <td class="col-order-no">
                    #<?php echo htmlspecialchars($order['no']); ?>
                </td>
                
                <!-- 상품명 -->
                <td class="col-product">
                    <?php echo htmlspecialchars($order['Type']); ?>
                </td>
                
                <!-- 상세 정보 -->
                <td class="col-details">
                    <?php echo displayProductDetails($connect, $order); ?>
                </td>
                
                <!-- 수량 -->
                <td class="col-quantity">
                    <?php echo extractQuantity($order); ?>
                </td>
                
                <!-- 금액 -->
                <td class="col-price">
                    <div class="price-container">
                        <div class="price-supply">공급가: <span style="font-size: 1.5rem; font-weight: 700; color: #27ae60;"><?php echo number_format($order['money_4']); ?>원</span></div>
                        <div class="price-total">합계금액: <span style="font-size: 1.1rem; font-weight: 600; color: #666;"><?php echo number_format($order['money_5']); ?>원</span></div>
                        <div class="price-vat">(VAT <?php echo number_format($order['money_5'] - $order['money_4']); ?>원 포함)</div>
                    </div>
                </td>
                
                <!-- 상태 -->
                <td class="col-status">
                    <span class="status-badge status-pending">입금대기</span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- 📋 정보 카드들 -->
    <div class="info-cards">
        <!-- 고객 정보 -->
        <div class="info-card">
            <h3>👤 고객 정보</h3>
            <div class="info-row">
                <div class="info-label">성명:</div>
                <div class="info-value"><?php echo htmlspecialchars($name ?: $first_order['name'] ?: '정보없음'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">이메일:</div>
                <div class="info-value"><?php echo htmlspecialchars($email ?: $first_order['email'] ?: '정보없음'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">연락처:</div>
                <div class="info-value">
                    <?php 
                    // 휴대폰이 우선, 없으면 일반전화, 둘 다 없으면 정보없음
                    $phone_display = '';
                    if (!empty($first_order['Hendphone'])) {
                        $phone_display = $first_order['Hendphone'];
                    } elseif (!empty($first_order['phone'])) {
                        $phone_display = $first_order['phone'];
                    } else {
                        $phone_display = '연락처 정보 없음';
                    }
                    echo htmlspecialchars($phone_display);
                    ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">주소:</div>
                <div class="info-value">
                    <?php 
                    $address_parts = [];
                    
                    // 우편번호 추가
                    if (!empty($first_order['zip'])) {
                        $address_parts[] = '(' . $first_order['zip'] . ')';
                    }
                    
                    // 주소1, 주소2 추가 (다양한 필드명 시도)
                    $address1 = $first_order['zip1'] ?? $first_order['addr1'] ?? $first_order['address1'] ?? '';
                    $address2 = $first_order['zip2'] ?? $first_order['addr2'] ?? $first_order['address2'] ?? '';
                    
                    if (!empty($address1)) $address_parts[] = $address1;
                    if (!empty($address2)) $address_parts[] = $address2;
                    
                    $address_display = !empty($address_parts) ? implode(' ', $address_parts) : '주소 정보 없음';
                    echo htmlspecialchars($address_display);
                    ?>
                </div>
            </div>
        </div>

        <!-- 입금 안내 -->
        <div class="info-card">
            <h3>💳 입금 안내</h3>
            <div class="info-row">
                <div class="info-label">예금주:</div>
                <div class="info-value">두손기획인쇄 차경선</div>
            </div>
            <div class="info-row">
                <div class="info-label">국민은행:</div>
                <div class="info-value">999-1688-2384</div>
            </div>
            <div class="info-row">
                <div class="info-label">신한은행:</div>
                <div class="info-value">110-342-543507</div>
            </div>
            <div class="info-row">
                <div class="info-label">농협:</div>
                <div class="info-value">301-2632-1829</div>
            </div>
            <div class="info-row">
                <div class="info-label">카드결제:</div>
                <div class="info-value">📞 1688-2384</div>
            </div>
            <div style="background: #fff3cd; padding: 8px; border-radius: 5px; margin-top: 10px; font-size: 0.85rem; color: #856404;">
                ⚠️ <strong>입금자명을 주문자명(<?php echo htmlspecialchars($name ?: $first_order['name']); ?>)과 동일하게 해주세요</strong>
            </div>
        </div>
    </div>

    <!-- 🎬 액션 섹션 -->
    <div class="action-section">
        <h3>🛍️ 다음 단계</h3>
        <div class="action-buttons">
            <a href="<?php echo getLastOrderProductUrl($order_list); ?>" class="btn-action btn-continue">
                🛒 계속 쇼핑하기
            </a>
            <button onclick="openPrintWindow()" class="btn-action btn-print">
                🖨️ 주문서 인쇄
            </button>
        </div>
        <p style="margin-top: 15px; font-size: 0.9rem; color: var(--text-secondary);">
            입금 확인 후 제작이 시작됩니다. 궁금한 사항은 <strong>📞 1688-2384</strong>로 연락주세요.
        </p>
    </div>
    
    <!-- 인쇄용 푸터 (화면에서는 숨김, 인쇄시에만 표시) -->
    <div class="print-footer" style="display: none;">
        <div class="print-payment-info">
            <h3>입금 계좌 안내</h3>
            <table class="payment-table">
                <tr>
                    <td><strong>국민은행</strong></td>
                    <td>999-1688-2384</td>
                    <td rowspan="3" style="text-align: center; vertical-align: middle;">
                        <strong>예금주: 두손기획인쇄 차경선</strong><br>
                        <span style="font-size: 9pt; color: #666;">입금자명을 주문자명과 동일하게 해주세요</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>신한은행</strong></td>
                    <td>110-342-543507</td>
                </tr>
                <tr>
                    <td><strong>농협</strong></td>
                    <td>301-2632-1829</td>
                </tr>
            </table>
        </div>
        <div class="print-contact-notice">
            <p><strong>※ 입금 확인 후 제작이 시작됩니다.</strong></p>
            <p>궁금한 사항은 <strong>02-2632-1830</strong> 또는 <strong>1688-2384</strong>로 연락주세요.</p>
        </div>
    </div>
</div>

<!-- 📧 JavaScript (인쇄 및 애니메이션) -->
<script>
// 월스트리트 스타일 주문서 별도 창 열기
function openPrintWindow() {
    const orders = "<?php echo htmlspecialchars($orders ?? ''); ?>";
    const email = "<?php echo htmlspecialchars($email ?? ''); ?>";
    const name = "<?php echo htmlspecialchars($name ?? ''); ?>";
    
    const printUrl = `OrderFormPrint.php?orders=${encodeURIComponent(orders)}&email=${encodeURIComponent(email)}&name=${encodeURIComponent(name)}`;
    
    // 새 창으로 주문서 열기
    window.open(printUrl, 'orderPrint', 'width=800,height=900,scrollbars=yes,resizable=yes');
}

// 페이지 로드 애니메이션
document.addEventListener('DOMContentLoaded', function() {
    // 테이블 행들에 순차적 애니메이션
    const rows = document.querySelectorAll('.order-row');
    rows.forEach((row, index) => {
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // 성공 헤더 펄스 효과
    const header = document.querySelector('.success-header');
    if (header) {
        setTimeout(() => {
            header.style.transform = 'scale(1.02)';
            setTimeout(() => {
                header.style.transform = 'scale(1)';
            }, 200);
        }, 500);
    }
});

// 복사 기능 (계좌번호 등)
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('📋 복사되었습니다: ' + text);
    });
}

// 주문 상세 정보 토글
function toggleOrderDetails(orderNo) {
    const details = document.querySelector(`#details_${orderNo}`);
    if (details) {
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
    }
}

console.log('🌟 Universal OrderComplete System Loaded');
console.log('📊 Order Count:', <?php echo count($order_list); ?>);
console.log('💰 Total Amount:', <?php echo $total_amount_vat; ?>);
</script>

<?php
// 공통 푸터 포함
include "../includes/footer.php";
?>