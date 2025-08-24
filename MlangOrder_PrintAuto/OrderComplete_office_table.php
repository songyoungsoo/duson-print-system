<?php
/**
 * 사무용 표형태 주문 완료 페이지
 * 파스텔 톤의 세련된 사무용 디자인
 * 경로: MlangOrder_PrintAuto/OrderComplete_office_table.php
 */

session_start();

// 데이터베이스 연결
include "../db.php";
$connect = $db;

// 카테고리 번호로 한글명 조회 함수
function getCategoryName($connect, $category_no) {
    if (!$category_no) return '';
    
    $query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no = ? LIMIT 1";
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
    return $category_no;
}

// 마지막 주문 품목 페이지 URL 생성 함수
function getLastOrderProductUrl($order_list) {
    if (empty($order_list)) {
        return '../MlangPrintAuto/shop/cart.php'; // 기본값: 장바구니
    }
    
    // 가장 최근 주문 (첫 번째 주문)
    $latest_order = $order_list[0];
    $product_type = $latest_order['Type'] ?? '';
    
    // 주문 데이터에서 상품 타입 확인
    $type_data = $latest_order['Type_1'] ?? '';
    $json_data = json_decode($type_data, true);
    
    if ($json_data && isset($json_data['product_type'])) {
        $product_type_key = $json_data['product_type'];
    } else {
        // Type 필드에서 상품 타입 추정
        $product_type_lower = strtolower($product_type);
        
        if (strpos($product_type_lower, 'sticker') !== false || strpos($product_type_lower, '스티커') !== false) {
            $product_type_key = 'sticker';
        } elseif (strpos($product_type_lower, 'namecard') !== false || strpos($product_type_lower, '명함') !== false) {
            $product_type_key = 'namecard';
        } elseif (strpos($product_type_lower, 'envelope') !== false || strpos($product_type_lower, '봉투') !== false) {
            $product_type_key = 'envelope';
        } elseif (strpos($product_type_lower, 'poster') !== false || strpos($product_type_lower, '포스터') !== false) {
            $product_type_key = 'littleprint';
        } elseif (strpos($product_type_lower, 'leaflet') !== false || strpos($product_type_lower, '전단') !== false) {
            $product_type_key = 'inserted';
        } elseif (strpos($product_type_lower, 'catalog') !== false || strpos($product_type_lower, '카다록') !== false) {
            $product_type_key = 'cadarok';
        } elseif (strpos($product_type_lower, 'bond') !== false || strpos($product_type_lower, '상품권') !== false) {
            $product_type_key = 'merchandisebond';
        } elseif (strpos($product_type_lower, 'ncr') !== false || strpos($product_type_lower, '전표') !== false) {
            $product_type_key = 'ncrflambeau';
        } elseif (strpos($product_type_lower, 'magnetic') !== false || strpos($product_type_lower, '자석') !== false) {
            $product_type_key = 'msticker';
        } else {
            $product_type_key = 'sticker'; // 기본값
        }
    }
    
    // 상품 타입별 URL 매핑
    $product_urls = [
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
    
    return $product_urls[$product_type_key] ?? '../MlangPrintAuto/shop/cart.php';
}

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
$page_title = '📊 주문 완료 - 사무용 표';
$current_page = 'order_complete';

// 공통 헤더 포함
include "../includes/header.php";
include "../includes/nav.php";
?>

<!-- 사무용 표형태 스타일 -->
<style>
/* 사무용 파스텔 톤 디자인 시스템 */
:root {
    --pastel-blue: #E6F3FF;
    --pastel-lavender: #F0E6FF;
    --pastel-mint: #E6FFF0;
    --pastel-peach: #FFE6E6;
    --pastel-yellow: #FFFCE6;
    --text-primary: #2c3e50;
    --text-secondary: #566a7e;
    --border-light: #e1e8ed;
    --shadow-light: 0 2px 8px rgba(0,0,0,0.08);
}

.office-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow-light);
    font-size: 0.9rem;
}

/* 헤더 섹션 - 압축형 */
.office-header {
    background: linear-gradient(135deg, var(--pastel-blue) 0%, var(--pastel-lavender) 100%);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    text-align: center;
    border: 1px solid var(--border-light);
}

.office-header h1 {
    font-size: 1.6rem;
    color: var(--text-primary);
    margin: 0 0 8px 0;
    font-weight: 700;
}

.office-header .summary-stats {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.summary-stat {
    text-align: center;
}

.summary-stat .value {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--text-primary);
}

.summary-stat .label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: 2px;
}

/* 사무용 테이블 스타일 */
.office-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow-light);
}

.office-table thead th {
    background: linear-gradient(135deg, var(--pastel-blue) 0%, var(--pastel-mint) 100%);
    color: var(--text-primary);
    font-weight: 700;
    padding: 12px 8px;
    font-size: 0.85rem;
    text-align: center;
    border-bottom: 2px solid var(--border-light);
    position: sticky;
    top: 0;
    z-index: 10;
}

.office-table tbody tr {
    transition: background-color 0.2s ease;
}

.office-table tbody tr:nth-child(even) {
    background: var(--pastel-yellow);
}

.office-table tbody tr:nth-child(odd) {
    background: var(--pastel-mint);
}

.office-table tbody tr:hover {
    background: var(--pastel-peach) !important;
    transform: scale(1.01);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.office-table td {
    padding: 10px 8px;
    border-bottom: 1px solid var(--border-light);
    font-size: 0.85rem;
    vertical-align: top;
}

/* 셀 별 스타일 */
.col-order-no {
    width: 8%;
    text-align: center;
    font-weight: 600;
    color: #667eea;
}

.col-product {
    width: 20%;
    font-weight: 600;
}

.col-details {
    width: 30%;
    line-height: 1.4;
}

.col-quantity {
    width: 8%;
    text-align: center;
}

.col-price {
    width: 12%;
    text-align: right;
    font-weight: 600;
    color: #e74c3c;
}

.col-date {
    width: 10%;
    text-align: center;
    font-size: 0.8rem;
}

.col-actions {
    width: 12%;
    text-align: center;
}

/* 상품 옵션 스타일 */
.product-options {
    margin-top: 5px;
    padding: 8px;
    background: rgba(255,255,255,0.7);
    border-radius: 4px;
    font-size: 0.75rem;
    line-height: 1.3;
}

.option-item {
    display: inline-block;
    margin-right: 10px;
    margin-bottom: 3px;
    padding: 2px 6px;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 3px;
    color: var(--text-secondary);
}

/* 요청사항 스타일 */
.request-note {
    margin-top: 5px;
    padding: 6px;
    background: var(--pastel-yellow);
    border-left: 3px solid #ffc107;
    border-radius: 3px;
    font-size: 0.75rem;
    color: #856404;
}

/* 고객정보 및 입금안내 - 컴팩트 카드 */
.info-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.info-card {
    background: var(--pastel-lavender);
    border-radius: 8px;
    padding: 15px;
    border: 1px solid var(--border-light);
}

.info-card h3 {
    margin: 0 0 12px 0;
    font-size: 1.1rem;
    color: var(--text-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-row {
    display: flex;
    margin-bottom: 8px;
    font-size: 0.85rem;
}

.info-label {
    width: 80px;
    font-weight: 600;
    color: #2c3e50 !important;
}

.info-value {
    flex: 1;
    color: #2c3e50 !important;
    font-weight: 500;
}

/* 메시징 패널 스타일 */
.messaging-panel {
    background: linear-gradient(135deg, var(--pastel-mint) 0%, var(--pastel-blue) 100%);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid var(--border-light);
}

.messaging-panel h3 {
    margin: 0 0 15px 0;
    font-size: 1.2rem;
    color: var(--text-primary);
}

.messaging-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.msg-btn {
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 120px;
    text-decoration: none;
    display: inline-block;
}

.msg-btn.email {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.msg-btn.sms {
    background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
    color: #2d3436;
}

.msg-btn.kakao {
    background: linear-gradient(135deg, #fee500 0%, #fdd835 100%);
    color: #2d3436;
}

.msg-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.msg-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* 액션 버튼 */
.action-buttons {
    text-align: center;
    margin-top: 20px;
}

.btn-action {
    display: inline-block;
    padding: 14px 28px;
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    margin: 0 10px;
    transition: all 0.3s ease;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
}

/* 반응형 디자인 */
@media (max-width: 1024px) {
    .info-cards {
        grid-template-columns: 1fr;
    }
    
    .messaging-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .msg-btn {
        min-width: 200px;
    }
}

@media (max-width: 768px) {
    .office-container {
        padding: 10px;
        font-size: 0.8rem;
    }
    
    .office-table {
        font-size: 0.75rem;
    }
    
    .office-table td {
        padding: 6px 4px;
    }
    
    .summary-stats {
        flex-direction: column;
        gap: 15px;
    }
    
    .col-details {
        width: 35%;
    }
    
    .col-product {
        width: 25%;
    }
}

/* 인쇄 스타일 */
@media print {
    .messaging-panel,
    .action-buttons {
        display: none;
    }
    
    .office-container {
        box-shadow: none;
        padding: 0;
    }
    
    .office-table {
        font-size: 0.7rem;
    }
}

/* 로딩 및 성공 메시지 */
.message-status {
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
    text-align: center;
    font-size: 0.85rem;
    display: none;
}

.message-status.success {
    background: var(--pastel-mint);
    color: #27ae60;
    border: 1px solid #27ae60;
}

.message-status.error {
    background: var(--pastel-peach);
    color: #e74c3c;
    border: 1px solid #e74c3c;
}

.message-status.loading {
    background: var(--pastel-yellow);
    color: #f39c12;
    border: 1px solid #f39c12;
}
</style>

<div class="office-container">
    <!-- 압축형 헤더 -->
    <div class="office-header">
        <h1>🎉 주문 완료 - <?php echo htmlspecialchars($name); ?> 고객님</h1>
        <div class="summary-stats">
            <div class="summary-stat">
                <div class="value"><?php echo count($order_list); ?>건</div>
                <div class="label">주문 건수</div>
            </div>
            <div class="summary-stat">
                <div class="value"><?php echo number_format($total_amount); ?>원</div>
                <div class="label">총 주문금액</div>
            </div>
            <div class="summary-stat">
                <div class="value"><?php echo number_format($total_amount_vat); ?>원</div>
                <div class="label">VAT 포함</div>
            </div>
        </div>
    </div>

    <!-- 사무용 주문 테이블 -->
    <table class="office-table">
        <thead>
            <tr>
                <th class="col-order-no">주문번호</th>
                <th class="col-product">상품명</th>
                <th class="col-details">상세 옵션</th>
                <th class="col-quantity">수량</th>
                <th class="col-price">금액(VAT포함)</th>
                <th class="col-date">주문일시</th>
                <th class="col-actions">상태</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_list as $order): ?>
            <tr>
                <!-- 주문번호 -->
                <td class="col-order-no">
                    #<?php echo htmlspecialchars($order['no']); ?>
                </td>
                
                <!-- 상품명 -->
                <td class="col-product">
                    <?php echo htmlspecialchars($order['Type']); ?>
                </td>
                
                <!-- 상세 옵션 -->
                <td class="col-details">
                    <?php
                    if (!empty($order['Type_1'])) {
                        $type_data = $order['Type_1'];
                        $json_data = json_decode($type_data, true);
                        
                        echo '<div class="product-options">';
                        
                        if ($json_data && is_array($json_data)) {
                            // JSON 데이터 처리
                            $product_type = $json_data['product_type'] ?? '';
                            
                            switch($product_type) {
                                case 'sticker':
                                    // 실제 데이터 구조에 맞게 수정
                                    $details = $json_data['order_details'] ?? $json_data;
                                    if (isset($details['jong'])) echo '<span class="option-item">재질: ' . htmlspecialchars($details['jong']) . '</span>';
                                    if (isset($details['garo']) && isset($details['sero'])) {
                                        echo '<span class="option-item">크기: ' . htmlspecialchars($details['garo']) . '×' . htmlspecialchars($details['sero']) . 'mm</span>';
                                    }
                                    if (isset($details['mesu'])) echo '<span class="option-item">수량: ' . number_format($details['mesu']) . '매</span>';
                                    if (isset($details['uhyung'])) echo '<span class="option-item">편집: ' . htmlspecialchars($details['uhyung']) . '</span>';
                                    if (isset($details['domusong'])) echo '<span class="option-item">모양: ' . htmlspecialchars($details['domusong']) . '</span>';
                                    break;
                                    
                                case 'envelope':
                                    if (isset($json_data['MY_type'])) echo '<span class="option-item">타입: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                                    if (isset($json_data['MY_Fsd'])) echo '<span class="option-item">용지: ' . getCategoryName($connect, $json_data['MY_Fsd']) . '</span>';
                                    if (isset($json_data['MY_amount'])) echo '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '매</span>';
                                    if (isset($json_data['POtype'])) echo '<span class="option-item">인쇄: ' . ($json_data['POtype'] == '1' ? '단면' : '양면') . '</span>';
                                    break;
                                    
                                case 'namecard':
                                    if (isset($json_data['MY_type'])) echo '<span class="option-item">타입: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                                    if (isset($json_data['Section'])) echo '<span class="option-item">용지: ' . getCategoryName($connect, $json_data['Section']) . '</span>';
                                    if (isset($json_data['MY_amount'])) echo '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '매</span>';
                                    if (isset($json_data['POtype'])) echo '<span class="option-item">인쇄: ' . ($json_data['POtype'] == '1' ? '단면' : '양면') . '</span>';
                                    break;
                                    
                                case 'merchandisebond':
                                    if (isset($json_data['MY_type'])) echo '<span class="option-item">구분: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                                    if (isset($json_data['MY_Fsd'])) echo '<span class="option-item">종류: ' . getCategoryName($connect, $json_data['MY_Fsd']) . '</span>';
                                    if (isset($json_data['MY_amount'])) echo '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '매</span>';
                                    break;
                                    
                                case 'cadarok':
                                    if (isset($json_data['MY_type'])) echo '<span class="option-item">타입: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                                    if (isset($json_data['MY_Fsd'])) echo '<span class="option-item">스타일: ' . getCategoryName($connect, $json_data['MY_Fsd']) . '</span>';
                                    if (isset($json_data['MY_amount'])) echo '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '</span>';
                                    break;
                                    
                                case 'littleprint':
                                    if (isset($json_data['MY_type'])) echo '<span class="option-item">타입: ' . getCategoryName($connect, $json_data['MY_type']) . '</span>';
                                    if (isset($json_data['MY_Fsd'])) echo '<span class="option-item">용지: ' . getCategoryName($connect, $json_data['MY_Fsd']) . '</span>';
                                    if (isset($json_data['MY_amount'])) echo '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '</span>';
                                    break;
                                    
                                default:
                                    foreach ($json_data as $key => $value) {
                                        if (!empty($value) && $key != 'product_type') {
                                            $display_key = ucfirst($key);
                                            $display_value = is_numeric($value) && in_array($key, ['MY_type', 'MY_Fsd', 'PN_type']) 
                                                ? getCategoryName($connect, $value) 
                                                : $value;
                                            echo '<span class="option-item">' . htmlspecialchars($display_key) . ': ' . htmlspecialchars($display_value) . '</span>';
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
                                    echo '<span class="option-item">' . htmlspecialchars($line) . '</span>';
                                }
                            }
                        }
                        
                        echo '</div>';
                    }
                    
                    // 요청사항 표시
                    if (!empty($order['cont'])) {
                        echo '<div class="request-note">';
                        echo '<strong>💬 요청사항:</strong><br>';
                        echo nl2br(htmlspecialchars($order['cont']));
                        echo '</div>';
                    }
                    ?>
                </td>
                
                <!-- 수량 (JSON에서 추출) -->
                <td class="col-quantity">
                    <?php
                    if (!empty($order['Type_1'])) {
                        $json_data = json_decode($order['Type_1'], true);
                        if ($json_data && is_array($json_data)) {
                            // JSON 데이터에서 수량 추출
                            $details = $json_data['order_details'] ?? $json_data;
                            if (isset($details['MY_amount'])) {
                                echo number_format($details['MY_amount']);
                            } elseif (isset($details['mesu'])) {
                                echo number_format($details['mesu']);
                            } else {
                                echo '1';
                            }
                        } else {
                            // 일반 텍스트에서 수량 추출
                            if (preg_match('/수량:\s*([0-9.]+)매/', $order['Type_1'], $matches)) {
                                echo number_format(floatval($matches[1]));
                            } else {
                                echo '1';
                            }
                        }
                    } else {
                        echo '1';
                    }
                    ?>
                </td>
                
                <!-- 금액 -->
                <td class="col-price">
                    <?php echo number_format($order['money_5']); ?>원
                </td>
                
                <!-- 주문일시 -->
                <td class="col-date">
                    <?php 
                    if (isset($order['date']) && !empty($order['date']) && $order['date'] !== '0000-00-00 00:00:00') {
                        echo date('m/d H:i', strtotime($order['date']));
                    } else {
                        echo date('m/d H:i'); // 현재 시간
                    }
                    ?>
                </td>
                
                <!-- 상태 -->
                <td class="col-actions">
                    <span style="background: var(--pastel-yellow); padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; color: #856404;">
                        입금대기
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- 압축형 정보 카드들 -->
    <div class="info-cards">
        <!-- 고객 정보 -->
        <div class="info-card">
            <h3>👤 고객 정보</h3>
            <div class="info-row">
                <div class="info-label">성명:</div>
                <div class="info-value"><?php echo htmlspecialchars($first_order['name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">이메일:</div>
                <div class="info-value"><?php echo htmlspecialchars($first_order['email']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">연락처:</div>
                <div class="info-value">
                    <?php if(!empty($first_order['Hendphone'])): ?>
                        <?php echo htmlspecialchars($first_order['Hendphone']); ?>
                    <?php elseif(!empty($first_order['phone'])): ?>
                        <?php echo htmlspecialchars($first_order['phone']); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">주소:</div>
                <div class="info-value">
                    <?php if(!empty($first_order['zip'])): ?>
                        (<?php echo htmlspecialchars($first_order['zip']); ?>) 
                    <?php endif; ?>
                    <?php echo htmlspecialchars($first_order['zip1'] . ' ' . $first_order['zip2']); ?>
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
                <div class="info-value">1688-2384 전화</div>
            </div>
        </div>
    </div>

    <!-- 메시징 패널 -->
    <div class="messaging-panel">
        <h3>📤 주문내역 전송</h3>
        <div class="messaging-buttons">
            <button class="msg-btn email" onclick="sendEmail()">
                📧 이메일 발송
            </button>
            <button class="msg-btn sms" onclick="sendSMS()" disabled>
                📱 SMS 발송 (준비중)
            </button>
            <button class="msg-btn kakao" onclick="sendKakao()" disabled>
                💬 카카오톡 (준비중)
            </button>
        </div>
        <div id="messageStatus" class="message-status"></div>
    </div>

    <!-- 액션 버튼 -->
    <div class="action-buttons">
        <a href="<?php echo getLastOrderProductUrl($order_list); ?>" class="btn-action">
            🛒 계속 쇼핑하기
        </a>
        <a href="javascript:window.print()" class="btn-action">
            🖨️ 인쇄하기
        </a>
    </div>
</div>

<!-- 이메일 발송 스크립트 -->
<script>
function sendEmail() {
    const btn = document.querySelector('.msg-btn.email');
    const status = document.getElementById('messageStatus');
    
    // 버튼 비활성화 및 로딩 상태
    btn.disabled = true;
    btn.textContent = '📧 발송 중...';
    
    status.className = 'message-status loading';
    status.textContent = '이메일을 발송하고 있습니다...';
    status.style.display = 'block';
    
    // 주문 데이터 준비
    const orderData = {
        orders: <?php echo json_encode($orders); ?>,
        email: <?php echo json_encode($email); ?>,
        name: <?php echo json_encode($name); ?>,
        orderList: <?php echo json_encode($order_list); ?>,
        totalAmount: <?php echo intval($total_amount); ?>,
        totalAmountVat: <?php echo intval($total_amount_vat); ?>
    };
    
    // 이메일 발송 요청
    fetch('send_order_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            status.className = 'message-status success';
            status.textContent = '✅ 이메일이 성공적으로 발송되었습니다!';
            btn.textContent = '📧 발송 완료';
        } else {
            throw new Error(data.message || '이메일 발송에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        status.className = 'message-status error';
        status.textContent = '❌ ' + error.message;
        btn.disabled = false;
        btn.textContent = '📧 이메일 발송';
    });
}

function sendSMS() {
    alert('SMS 발송 기능은 준비 중입니다.\nAPI 키 설정 후 이용 가능합니다.');
}

function sendKakao() {
    alert('카카오톡 발송 기능은 준비 중입니다.\n비즈니스 계정 승인 후 이용 가능합니다.');
}

// 페이지 로드 시 애니메이션
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.office-table tbody tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, index * 100);
    });
});
</script>

<?php
// 푸터 포함
include "../includes/footer.php";
?>