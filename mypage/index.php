<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/ProductSpecFormatter.php';

// 세션 만료 vs 미로그인 구분하여 적절한 메시지 표시
requireLogin('/member/login.php');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// 최근 주문 3건 조회
$recent_orders_query = "SELECT no, name, date, OrderStyle, Type, Type_1
                        FROM mlangorder_printauto
                        WHERE email = (SELECT email FROM users WHERE id = ?)
                        ORDER BY date DESC
                        LIMIT 3";
$stmt = mysqli_prepare($db, $recent_orders_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$recent_orders_result = mysqli_stmt_get_result($stmt);
$recent_orders = mysqli_fetch_all($recent_orders_result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// 주문 통계
$stats_query = "SELECT
                    COUNT(*) as total_orders,
                    SUM(CAST(money_5 AS UNSIGNED)) as total_amount
                FROM mlangorder_printauto
                WHERE email = (SELECT email FROM users WHERE id = ?)";
$stmt = mysqli_prepare($db, $stats_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$stats_result = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($stats_result);
mysqli_stmt_close($stmt);

// 미결제 주문 수
$unpaid_query = "SELECT COUNT(*) as unpaid_count
                 FROM mlangorder_printauto
                 WHERE email = (SELECT email FROM users WHERE id = ?)
                 AND OrderStyle IN ('2', '3', '4')";
$stmt = mysqli_prepare($db, $unpaid_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$unpaid_result = mysqli_stmt_get_result($stmt);
$unpaid = mysqli_fetch_assoc($unpaid_result);
mysqli_stmt_close($stmt);

// 전체 주문 내역 (페이징 처리)
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if (!$page) $page = 1;

// 상태 필터 (OrderStyle 기반 고객용 그룹핑)
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$customer_status_filter_map = [
    'received'   => '주문접수',
    'confirmed'  => '접수완료',
    'working'    => '작업중',
    'completed'  => '작업완료',
    'shipping'   => '배송중'
];

// OrderStyle → 고객용 상태 그룹핑 함수
function getCustomerStatus($orderStyle, $order = null) {
    $os = (string)$orderStyle;
    // 송장번호가 있으면 배송중
    if ($order) {
        $tracking = ($order['waybill_no'] ?? '') ?: ($order['logen_tracking_no'] ?? '');
        if (!empty($tracking)) {
            return ['text' => '배송중', 'color' => '#28a745', 'group' => 'shipping'];
        }
    }
    switch ($os) {
        case '0': case '1': case '2':
            return ['text' => '주문접수', 'color' => '#6c757d', 'group' => 'received'];
        case '3': case '4':
            return ['text' => '접수완료', 'color' => '#17a2b8', 'group' => 'confirmed'];
        case '5': case '6': case '7': case '9': case '10':
            return ['text' => '작업중', 'color' => '#f59e0b', 'group' => 'working'];
        case '8':
            return ['text' => '작업완료', 'color' => '#10b981', 'group' => 'completed'];
        case 'deleted':
            return ['text' => '주문취소', 'color' => '#dc3545', 'group' => 'cancelled'];
        default:
            return ['text' => '주문접수', 'color' => '#6c757d', 'group' => 'received'];
    }
}

// 고객 필터 → OrderStyle WHERE 조건 매핑
function getStatusFilterCondition($filterGroup) {
    switch ($filterGroup) {
        case 'received':  return "OrderStyle IN ('0','1','2')";
        case 'confirmed': return "OrderStyle IN ('3','4')";
        case 'working':   return "OrderStyle IN ('5','6','7','9','10')";
        case 'completed': return "OrderStyle = '8'";
        case 'shipping':  return "(waybill_no IS NOT NULL AND waybill_no != '' OR logen_tracking_no IS NOT NULL AND logen_tracking_no != '')";
        default: return '';
    }
}

// 사용자 이메일 가져오기
$email_query = "SELECT email FROM users WHERE id = ?";
$email_stmt = mysqli_prepare($db, $email_query);
mysqli_stmt_bind_param($email_stmt, "i", $user_id);
mysqli_stmt_execute($email_stmt);
$email_result = mysqli_stmt_get_result($email_stmt);
$user_email_data = mysqli_fetch_assoc($email_result);
$userEmail = $user_email_data['email'] ?? '';
mysqli_stmt_close($email_stmt);

// 전체 주문 수 (필터 적용 - OrderStyle 기반)
$count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE email = ?";
$filter_condition = '';
if ($status_filter !== '') {
    $filter_condition = getStatusFilterCondition($status_filter);
    if ($filter_condition) {
        $count_query .= " AND " . $filter_condition;
    }
}
$count_stmt = mysqli_prepare($db, $count_query);
mysqli_stmt_bind_param($count_stmt, "s", $userEmail);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_data = mysqli_fetch_assoc($count_result);
$total_orders = $count_data['total'];
mysqli_stmt_close($count_stmt);

// 페이지 설정
$pagenum = 10;
$pages = ceil($total_orders / $pagenum);
$offset = $pagenum * ($page - 1);

// 전체 주문 내역 조회 (필터 적용 - OrderStyle 기반)
$all_orders_query = "SELECT * FROM mlangorder_printauto WHERE email = ?";
if ($status_filter !== '' && $filter_condition) {
    $all_orders_query .= " AND " . $filter_condition;
}
$all_orders_query .= " ORDER BY no DESC LIMIT ?, ?";
$all_orders_stmt = mysqli_prepare($db, $all_orders_query);
mysqli_stmt_bind_param($all_orders_stmt, "sii", $userEmail, $offset, $pagenum);
mysqli_stmt_execute($all_orders_stmt);
$all_orders_result = mysqli_stmt_get_result($all_orders_stmt);
$all_orders = mysqli_fetch_all($all_orders_result, MYSQLI_ASSOC);
mysqli_stmt_close($all_orders_stmt);

// 상태 텍스트 매핑
$status_map = [
    '2' => '접수중',
    '3' => '접수완료',
    '4' => '입금대기',
    '5' => '시안제작중',
    '6' => '시안완료',
    '7' => '교정중',
    '8' => '작업완료',
    '9' => '작업중',
    '10' => '교정작업중'
];

$type_map = [
    'inserted' => '전단지',
    'sticker' => '스티커',
    'NameCard' => '명함',
    'MerchandiseBond' => '상품권',
    'envelope' => '봉투',
    'NcrFlambeau' => '양식지',
    'cadarok' => '카탈로그',
    'LittlePrint' => '소량인쇄'
];

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>마이페이지 홈 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/common-styles.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background: #f5f5f5;
            font-size: 13px;
        }

        .mypage-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
        }

        .mypage-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 900px;
        }

        .page-title {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #ffffff;
        }

        .welcome-section {
            background: linear-gradient(135deg, #1466BA 0%, #0d4d8a 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .welcome-section h2 {
            margin: 0 0 8px 0;
            font-size: 20px;
        }

        .welcome-section p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: #1466BA;
            transform: translateY(-2px);
        }

        .stat-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1466BA;
        }

        .stat-unit {
            font-size: 14px;
            color: #999;
            margin-left: 4px;
        }

        .section-title {
            font-size: 18px;
            color: #333;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #1466BA;
        }

        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 30px;
        }

        .order-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #1466BA;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }

        .order-item:hover {
            background: #e9ecef;
        }

        .order-info {
            flex: 1;
        }

        .order-number {
            font-size: 14px;
            font-weight: 600;
            color: #1466BA;
            margin-bottom: 4px;
        }

        .order-details {
            font-size: 13px;
            color: #666;
        }

        .order-status {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-2, .status-3, .status-4 {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-5, .status-6, .status-7, .status-9, .status-10 {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-8 {
            background: #e8f5e9;
            color: #388e3c;
        }

        .alert-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-icon {
            font-size: 24px;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-size: 14px;
            font-weight: 600;
            color: #856404;
            margin: 0 0 4px 0;
        }

        .alert-text {
            font-size: 13px;
            color: #856404;
            margin: 0;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .quick-link {
            background: #f8f9fa;
            padding: 20px 15px;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            color: #333;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .quick-link:hover {
            background: #1466BA;
            color: white;
            border-color: #1466BA;
            transform: translateY(-2px);
        }

        .quick-link-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .quick-link-text {
            font-size: 13px;
            font-weight: 500;
        }

        .empty-orders {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-orders p {
            margin: 0 0 15px 0;
            font-size: 14px;
        }

        .btn-primary {
            display: inline-block;
            padding: 10px 20px;
            background: #1466BA;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #0d4d8a;
        }

        /* 전체 주문 내역 테이블 */
        .order-history-section {
            margin-top: 40px;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }

        .order-history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .order-history-table th {
            background: #1466BA;
            color: white;
            padding: 8px 6px;
            text-align: center;
            font-weight: 500;
            font-size: 13px;
        }

        .order-history-table td {
            padding: 6px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
            font-size: 13px;
        }

        .order-history-table tr:hover td {
            background: #f8f9fa;
        }

        .order-history-table a {
            color: #1466BA;
            text-decoration: none;
            font-weight: 500;
        }

        .order-history-table a:hover {
            text-decoration: underline;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2px;
            margin-top: 15px;
            flex-wrap: nowrap;
        }

        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            height: 26px;
            padding: 0 6px;
            background: white;
            color: #1466BA;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 12px;
            transition: all 0.2s;
        }

        .pagination a:hover:not(.active):not(.disabled) {
            background: #1466BA;
            color: white;
            border-color: #1466BA;
        }

        .pagination a.active {
            background: #1466BA;
            color: white;
            border-color: #1466BA;
            font-weight: bold;
        }

        .pagination a.disabled,
        .pagination span.disabled {
            background: #f5f5f5;
            color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination .page-nav {
            font-weight: 500;
        }

        .pagination .page-ellipsis {
            border: none;
            background: transparent;
            color: #999;
        }

        .pagination-info {
            text-align: center;
            margin-top: 8px;
            color: #888;
            font-size: 11px;
        }

        .total-count {
            font-size: 13px;
            color: #666;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .mypage-container {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-links {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="mypage-container">
        <!-- 사이드바 -->
        <?php include 'sidebar.php'; ?>

        <!-- 메인 컨텐츠 -->
        <div class="mypage-content">
            <h1 class="page-title">마이페이지</h1>

            <!-- 환영 섹션 -->
            <div class="welcome-section">
                <h2>안녕하세요, <?php echo htmlspecialchars($user_name); ?>님! 👋</h2>
                <p>두손기획인쇄를 이용해 주셔서 감사합니다.</p>
            </div>

            <!-- 통계 카드 -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">총 주문 건수</div>
                    <div class="stat-value">
                        <span class="stat-number" data-target="<?php echo intval($stats['total_orders'] ?? 0); ?>">0</span>
                        <span class="stat-unit">건</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">총 구매 금액</div>
                    <div class="stat-value">
                        <span class="stat-number" data-target="<?php echo intval($stats['total_amount'] ?? 0); ?>" data-currency="true">0</span>
                        <span class="stat-unit">원</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">미결제 주문</div>
                    <div class="stat-value">
                        <span class="stat-number" data-target="<?php echo intval($unpaid['unpaid_count'] ?? 0); ?>">0</span>
                        <span class="stat-unit">건</span>
                    </div>
                </div>
            </div>

            <!-- 미결제 주문 알림 -->
            <?php if (isset($unpaid['unpaid_count']) && $unpaid['unpaid_count'] > 0): ?>
            <div class="alert-box">
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                    <p class="alert-title">미결제 주문이 있습니다</p>
                    <p class="alert-text">
                        입금 대기 중인 주문이 <?php echo $unpaid['unpaid_count']; ?>건 있습니다.
                        입금 후 제작이 시작됩니다.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- 전체 주문조회 & 배송조회 (orderhistory.php 통합) -->
            <div id="order-history" class="order-history-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 class="section-title" style="margin: 0; border-bottom: none;">📦 전체 주문조회 & 배송조회</h2>
                    <form method="get" action="" style="display: flex; gap: 8px; align-items: center;">
                        <select name="status" onchange="this.form.submit()" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                            <option value="">전체 상태</option>
                            <?php foreach ($customer_status_filter_map as $code => $text): ?>
                                <option value="<?php echo $code; ?>" <?php echo $status_filter === $code ? 'selected' : ''; ?>>
                                    <?php echo $text; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($status_filter !== ''): ?>
                            <a href="?#order-history" style="font-size: 12px; color: #1466BA;">초기화</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="total-count">
                    <?php if ($status_filter !== ''): ?>
                        <strong><?php echo $customer_status_filter_map[$status_filter] ?? ''; ?></strong> 상태:
                    <?php endif; ?>
                    총 <strong><?php echo number_format($total_orders); ?></strong>건의 주문
                </div>

                <?php if ($total_orders > 0): ?>
                <table class="order-history-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">주문번호</th>
                            <th style="width: 80px;">이름</th>
                            <th>주문내용</th>
                            <th style="width: 100px; text-align: right;">총금액</th>
                            <th style="width: 90px; text-align: center;">주문일자</th>
                            <th style="width: 70px; text-align: center;">상태</th>
                            <th style="width: 70px; text-align: center;">배송</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // ProductSpecFormatter 인스턴스 생성 (SSOT - 모든 JSON 포맷 처리)
                        $specFormatter = new ProductSpecFormatter($db);
                        foreach ($all_orders as $order):
                            // ProductSpecFormatter 사용 (v1/v2/레거시 모든 포맷 자동 처리)
                            $spec_result = $specFormatter->format($order);
                            $display_line1 = $spec_result['line1'] ?? '';
                            $display_line2 = $spec_result['line2'] ?? '';

                            // fallback: formatter가 빈 결과를 반환하면 원본 텍스트
                            if (empty($display_line1) && empty($display_line2)) {
                                $type1_raw = $order['Type_1'] ?? '';
                                if (!empty($type1_raw)) {
                                    $display_line1 = mb_substr($type1_raw, 0, 50);
                                }
                            }
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <a href="/session/order_view_my.php?no=<?php echo $order['no']; ?>" style="color: #1466BA; font-weight: 500;">
                                    <?php echo $order['no']; ?>
                                </a>
                            </td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($order['name'] ?? ''); ?></td>
                            <td style="text-align: left; padding: 8px 12px; line-height: 1.5;">
                                <?php if ($display_line1): ?>
                                    <div style="color: #333;"><?php echo htmlspecialchars($display_line1); ?></div>
                                <?php endif; ?>
                                <?php if ($display_line2): ?>
                                    <div style="color: #666; font-size: 13px;"><?php echo htmlspecialchars($display_line2); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; padding-right: 12px; font-weight: 500;">
                                <?php echo number_format($order['money_5'] ?? $order['money_4'] ?? 0); ?>원
                                <?php
                                $lf_type = $order['logen_fee_type'] ?? '';
                                $lf_fee = intval($order['logen_delivery_fee'] ?? 0);
                                if ($lf_type === '선불'):
                                    if ($lf_fee > 0):
                                        $lf_vat = round($lf_fee * 0.1);
                                        $lf_total = $lf_fee + $lf_vat;
                                ?>
                                <div style="font-size: 12px; color: #155724; margin-top: 2px;">+ 택배비 ₩<?php echo number_format($lf_total); ?></div>
                                <?php else: ?>
                                <div style="font-size: 12px; color: #e67e22; margin-top: 2px;">+ 택배비 확인중</div>
                                <?php endif; endif; ?>
                            </td>
                            <td style="text-align: center; color: #666;"><?php echo date('Y-m-d', strtotime($order['date'] ?? '')); ?></td>
                            <td style="text-align: center;">
                                <?php
                                $status = getCustomerStatus($order['OrderStyle'] ?? '0', $order);
                                ?>
                                <span style="color: <?php echo $status['color']; ?>; font-weight: 500;"><?php echo $status['text']; ?></span>
                            </td>
                            <td style="text-align: center;">
                                <?php
                                $tracking = $order['waybill_no'] ?? $order['logen_tracking_no'] ?? '';
                                if (!empty($tracking)):
                                ?>
                                <a href="https://www.ilogen.com/web/personal/trace/<?php echo urlencode($tracking); ?>"
                                   target="_blank"
                                   style="color: #667eea; text-decoration: none; font-size: 12px; white-space: nowrap;">
                                   배송조회
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($pages > 1): ?>
                <div class="pagination">
                    <?php
                    // 표시할 페이지 범위 계산 (좌우 5개씩)
                    $range = 5;
                    $start_page = max(1, $page - $range);
                    $end_page = min($pages, $page + $range);
                    $status_param = $status_filter !== '' ? '&status=' . $status_filter : '';

                    // 맨처음
                    if ($page > 1): ?>
                        <a href="?page=1<?php echo $status_param; ?>#order-history" class="page-nav" title="맨 처음">«</a>
                    <?php else: ?>
                        <span class="page-nav disabled">«</span>
                    <?php endif;

                    // 이전
                    if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $status_param; ?>#order-history" class="page-nav" title="이전">‹</a>
                    <?php else: ?>
                        <span class="page-nav disabled">‹</span>
                    <?php endif;

                    // 시작 생략 표시
                    if ($start_page > 1): ?>
                        <span class="page-ellipsis">...</span>
                    <?php endif;

                    // 페이지 번호들
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $status_param; ?>#order-history"
                           class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor;

                    // 끝 생략 표시
                    if ($end_page < $pages): ?>
                        <span class="page-ellipsis">...</span>
                    <?php endif;

                    // 다음
                    if ($page < $pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $status_param; ?>#order-history" class="page-nav" title="다음">›</a>
                    <?php else: ?>
                        <span class="page-nav disabled">›</span>
                    <?php endif;

                    // 맨끝
                    if ($page < $pages): ?>
                        <a href="?page=<?php echo $pages; ?><?php echo $status_param; ?>#order-history" class="page-nav" title="맨 끝">»</a>
                    <?php else: ?>
                        <span class="page-nav disabled">»</span>
                    <?php endif; ?>
                </div>
                <div class="pagination-info">
                    <?php echo number_format($page); ?> / <?php echo number_format($pages); ?> 페이지
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="empty-orders">
                    <p style="font-size: 18px; margin-bottom: 10px;">📭</p>
                    <p>주문 내역이 없습니다.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    <script>
    (function() {
        function animateNumber(el, target, duration) {
            if (!target) { el.textContent = '0'; return; }
            var isCurrency = el.dataset.currency === 'true';
            var start = null;
            function ease(t) { return t === 1 ? 1 : 1 - Math.pow(2, -10 * t); }
            function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / duration, 1);
                var val = Math.round(ease(p) * target);
                el.textContent = val.toLocaleString('ko-KR');
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }
        document.querySelectorAll('.stat-number').forEach(function(el) {
            animateNumber(el, parseInt(el.dataset.target) || 0, 800);
        });
    })();
    </script>
</body>
</html>
<?php
mysqli_close($db);
?>
