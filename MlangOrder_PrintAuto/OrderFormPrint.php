<?php
/**
 * 🏦 월스트리트 스타일 주문서 출력 페이지
 * 별도 창으로 열리는 전용 주문서 페이지
 * 파일: MlangOrder_PrintAuto/OrderFormPrint.php
 */

// 세션 시작 및 데이터베이스 연결
session_start();
include "../db.php";

// URL 파라미터에서 주문 정보 받기
$orders = $_GET['orders'] ?? '';
$email = $_GET['email'] ?? '';
$name = $_GET['name'] ?? '';

// 주문번호 파싱
$order_numbers = array_filter(explode(',', $orders));
$order_list = [];

if (!empty($order_numbers)) {
    $order_numbers_str = implode(',', array_map('intval', $order_numbers));
    $query = "SELECT * FROM MlangOrder_PrintAuto WHERE no IN ($order_numbers_str) ORDER BY no DESC";
    $result = mysqli_query($db, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $order_list[] = $row;
        }
    }
}

$first_order = $order_list[0] ?? [];

// 상품 상세 정보 표시 함수
function displayProductDetails($db, $order) {
    $details = [];
    
    // JSON 데이터 파싱 시도
    if (!empty($order['Type_1'])) {
        $json_data = json_decode($order['Type_1'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
            $product_type = $json_data['product_type'] ?? $order['Type'];
            
            switch ($product_type) {
                case 'envelope':
                    $details[] = "📮 " . ($json_data['env_type'] ?? '봉투');
                    if (!empty($json_data['env_paper'])) $details[] = "용지: " . $json_data['env_paper'];
                    if (!empty($json_data['env_print_side'])) $details[] = "인쇄면: " . $json_data['env_print_side'];
                    break;
                    
                case 'sticker':
                    $details[] = "🏷️ 스티커";
                    if (!empty($json_data['jong'])) $details[] = "재질: " . $json_data['jong'];
                    if (!empty($json_data['garo']) && !empty($json_data['sero'])) {
                        $details[] = "크기: " . $json_data['garo'] . "×" . $json_data['sero'] . "mm";
                    }
                    break;
                    
                case 'namecard':
                    $details[] = "💼 명함";
                    if (!empty($json_data['nc_paper'])) $details[] = "용지: " . $json_data['nc_paper'];
                    break;
                    
                default:
                    if (!empty($order['Type'])) $details[] = $order['Type'];
            }
        }
    }
    
    if (empty($details)) {
        $details[] = $order['Type'] ?? '주문 상품';
    }
    
    return implode(' | ', $details);
}

// 수량 추출 함수
function extractQuantity($order) {
    $json_data = json_decode($order['Type_1'] ?? '', true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
        return number_format($json_data['mesu'] ?? $json_data['quantity'] ?? 0) . '매';
    }
    return '1매';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 확인서 - 두손기획인쇄</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* 🏦 월스트리트 스타일 주문서 디자인 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', 'Georgia', 'Times New Roman', serif;
            background: linear-gradient(135deg, 
                #f8f9fa 0%, 
                #e3f2fd 20%, 
                #f1f8e9 40%, 
                #fff3e0 60%, 
                #fce4ec 80%, 
                #f3e5f5 100%);
            min-height: 100vh;
            padding: 10px;
            color: #212529;
            line-height: 1.4;
        }
        
        .document-container {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 15mm;
            border-radius: 8px;
            box-shadow: 
                0 15px 30px rgba(0, 0, 0, 0.08),
                0 0 0 1px rgba(255, 255, 255, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            position: relative;
            overflow: hidden;
        }
        
        /* 🎨 배경 패턴 */
        .document-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(79, 172, 254, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(168, 85, 247, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(34, 197, 94, 0.02) 0%, transparent 50%);
            pointer-events: none;
        }
        
        /* 📋 헤더 섹션 - 간격 최적화 */
        .header-section {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .company-logo {
            font-size: 26pt;
            font-weight: 900;
            color: #1e293b;
            letter-spacing: 3px;
            margin-bottom: 5px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .company-logo::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
            border-radius: 1px;
        }
        
        .company-details {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            margin: 10px auto;
            max-width: 480px;
            font-size: 8pt;
            color: #475569;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .document-title {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin: 15px 0;
            position: relative;
            box-shadow: 0 3px 8px rgba(30, 41, 59, 0.25);
        }
        
        .document-title h1 {
            font-size: 20pt;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 3px;
        }
        
        .document-subtitle {
            font-size: 10pt;
            opacity: 0.9;
            font-weight: 300;
        }
        
        /* 👤 고객 정보 섹션 - 간격 최적화 */
        .customer-section {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .customer-section::before {
            content: '👤';
            position: absolute;
            top: -10px;
            left: 15px;
            background: #059669;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        
        .customer-section h3 {
            font-size: 12pt;
            font-weight: 600;
            color: #065f46;
            margin-bottom: 10px;
            margin-left: 12px;
        }
        
        .customer-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.7);
            padding: 8px 12px;
            border-radius: 5px;
            border-left: 2px solid #10b981;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
            min-width: 60px;
            margin-right: 8px;
            font-size: 9pt;
        }
        
        .info-value {
            color: #111827;
            font-weight: 500;
            font-size: 9pt;
        }
        
        /* 📊 주문 테이블 섹션 - 간격 최적화 */
        .order-section {
            background: linear-gradient(135deg, #fef7ff 0%, #f3e8ff 100%);
            border: 1px solid #d8b4fe;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .order-section::before {
            content: '📊';
            position: absolute;
            top: -10px;
            left: 15px;
            background: #7c3aed;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        
        .order-section h3 {
            font-size: 12pt;
            font-weight: 600;
            color: #581c87;
            margin-bottom: 10px;
            margin-left: 12px;
        }
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .order-table thead {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }
        
        .order-table thead th {
            padding: 8px 10px;
            color: white;
            font-weight: 600;
            font-size: 9pt;
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .order-table thead th:last-child {
            border-right: none;
        }
        
        .order-table tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8pt;
            vertical-align: top;
        }
        
        .order-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .order-table tbody tr:nth-child(even) {
            background: rgba(248, 250, 252, 0.5);
        }
        
        .col-order-no {
            width: 12%;
            text-align: center;
            font-weight: 700;
            color: #3730a3;
        }
        
        .col-product {
            width: 22%;
            font-weight: 600;
            color: #1f2937;
        }
        
        .col-details {
            width: 30%;
            color: #4b5563;
            line-height: 1.4;
        }
        
        .col-quantity {
            width: 12%;
            text-align: center;
            font-weight: 600;
            color: #b45309;
        }
        
        .col-price {
            width: 18%;
            text-align: right;
        }
        
        .col-status {
            width: 6%;
            text-align: center;
        }
        
        .price-container {
            text-align: right;
        }
        
        .price-supply {
            font-size: 11pt;
            font-weight: 700;
            color: #059669;
            margin-bottom: 2px;
        }
        
        .price-total {
            font-size: 9pt;
            color: #6b7280;
        }
        
        .price-vat {
            font-size: 8pt;
            color: #9ca3af;
        }
        
        .status-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
            white-space: nowrap;
        }
        
        /* 💳 결제 정보 섹션 - 간격 최적화 */
        .payment-section {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 12px;
            position: relative;
            margin-bottom: 10px;
        }
        
        .payment-section::before {
            content: '💳';
            position: absolute;
            top: -10px;
            left: 15px;
            background: #d97706;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        
        .payment-section h3 {
            font-size: 12pt;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 10px;
            margin-left: 12px;
        }
        
        .payment-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .bank-item {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid #f3d8a7;
            border-radius: 5px;
            padding: 6px;
            text-align: center;
        }
        
        .bank-name {
            font-weight: 700;
            color: #451a03;
            font-size: 9pt;
            margin-bottom: 2px;
        }
        
        .bank-account {
            font-weight: 600;
            color: #713f12;
            font-size: 8pt;
        }
        
        .account-holder {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
            margin-top: 8px;
            font-weight: 600;
            color: #451a03;
            font-size: 9pt;
        }
        
        /* 📞 연락처 정보 - 간격 최적화 */
        .contact-info {
            text-align: center;
            margin-top: 10px;
            padding: 8px;
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            border: 1px solid #c4b5fd;
            border-radius: 6px;
            font-size: 8pt;
            color: #3730a3;
            font-weight: 500;
        }
        
        .contact-info strong {
            color: #1e1b4b;
        }
        
        /* 🖨️ 인쇄 최적화 - A4 한페이지 맞춤 */
        @media print {
            @page {
                margin: 10mm;
                size: A4;
            }
            
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .document-container {
                max-width: none !important;
                min-height: none !important;
                margin: 0 !important;
                padding: 8mm !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: white !important;
            }
            
            .document-container::before {
                display: none !important;
            }
            
            .header-section {
                margin-bottom: 8px !important;
            }
            
            .customer-section, 
            .order-section, 
            .payment-section {
                margin-bottom: 8px !important;
                padding: 8px !important;
            }
            
            .contact-info {
                margin-top: 5px !important;
                padding: 5px !important;
            }
        }
    </style>
</head>
<body>
    <div class="document-container">
        <!-- 🏢 회사 헤더 -->
        <div class="header-section">
            <div class="company-logo">두손기획인쇄</div>
            <div class="company-details">
                서울 영등포구 영등포로36길 9, 송호빌딩 1층<br>
                TEL: 02-2632-1830 | FAX: 02-2632-1831 | www.dsp114.com
            </div>
        </div>
        
        <!-- 📋 문서 제목 -->
        <div class="document-title">
            <h1>ORDER CONFIRMATION</h1>
            <div class="document-subtitle">주문 확인서 | <?php echo date('Y년 m월 d일'); ?></div>
        </div>
        
        <!-- 👤 고객 정보 -->
        <div class="customer-section">
            <h3>Customer Information</h3>
            <div class="customer-info-grid">
                <div class="info-item">
                    <div class="info-label">고객명:</div>
                    <div class="info-value"><?php echo htmlspecialchars($name ?: $first_order['name'] ?: '정보없음'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">주문일:</div>
                    <div class="info-value"><?php echo htmlspecialchars($first_order['date'] ?? date('Y-m-d')); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">연락처:</div>
                    <div class="info-value">
                        <?php 
                        $phone_display = $first_order['Hendphone'] ?: $first_order['phone'] ?: '정보없음';
                        echo htmlspecialchars($phone_display);
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">이메일:</div>
                    <div class="info-value"><?php echo htmlspecialchars($email ?: $first_order['email'] ?: '정보없음'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- 📊 주문 정보 -->
        <div class="order-section">
            <h3>Order Details</h3>
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
                    <?php foreach ($order_list as $order): ?>
                    <tr>
                        <td class="col-order-no">#<?php echo htmlspecialchars($order['no']); ?></td>
                        <td class="col-product"><?php echo htmlspecialchars($order['Type']); ?></td>
                        <td class="col-details"><?php echo displayProductDetails($db, $order); ?></td>
                        <td class="col-quantity"><?php echo extractQuantity($order); ?></td>
                        <td class="col-price">
                            <div class="price-container">
                                <div class="price-supply">₩<?php echo number_format($order['money_4']); ?></div>
                                <div class="price-total">총액: ₩<?php echo number_format($order['money_5']); ?></div>
                                <div class="price-vat">(VAT ₩<?php echo number_format($order['money_5'] - $order['money_4']); ?> 포함)</div>
                            </div>
                        </td>
                        <td class="col-status">
                            <span class="status-badge">대기</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 💳 결제 정보 -->
        <div class="payment-section">
            <h3>Payment Information</h3>
            <div class="payment-grid">
                <div class="bank-item">
                    <div class="bank-name">국민은행</div>
                    <div class="bank-account">999-1688-2384</div>
                </div>
                <div class="bank-item">
                    <div class="bank-name">신한은행</div>
                    <div class="bank-account">110-342-543507</div>
                </div>
                <div class="bank-item">
                    <div class="bank-name">농협</div>
                    <div class="bank-account">301-2632-1829</div>
                </div>
            </div>
            <div class="account-holder">
                <strong>예금주: 두손기획인쇄 차경선</strong><br>
                <span style="font-size: 9pt; margin-top: 5px; display: inline-block;">
                    입금자명을 주문자명(<?php echo htmlspecialchars($name ?: $first_order['name']); ?>)과 동일하게 해주세요
                </span>
            </div>
        </div>
        
        <!-- 📞 연락처 -->
        <div class="contact-info">
            <strong>입금 확인 후 제작이 시작됩니다.</strong><br>
            궁금한 사항은 <strong>02-2632-1830</strong> 또는 <strong>1688-2384</strong>로 연락주세요.
        </div>
    </div>
    
    <script>
        // 페이지 로드 시 자동 포커스 (인쇄 준비)
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        });
        
        // ESC 키로 창 닫기
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>