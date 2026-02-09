<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

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

// 상태 필터
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$level_filter_map = [
    '0' => '주문취소',
    '1' => '주문접수',
    '2' => '입금확인',
    '3' => '작업중',
    '4' => '배송중'
];

// 사용자 이메일 가져오기
$email_query = "SELECT email FROM users WHERE id = ?";
$email_stmt = mysqli_prepare($db, $email_query);
mysqli_stmt_bind_param($email_stmt, "i", $user_id);
mysqli_stmt_execute($email_stmt);
$email_result = mysqli_stmt_get_result($email_stmt);
$user_email_data = mysqli_fetch_assoc($email_result);
$userEmail = $user_email_data['email'] ?? '';
mysqli_stmt_close($email_stmt);

// 전체 주문 수 (필터 적용)
$count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE email = ?";
if ($status_filter !== '') {
    $count_query .= " AND level = ?";
    $count_stmt = mysqli_prepare($db, $count_query);
    mysqli_stmt_bind_param($count_stmt, "si", $userEmail, $status_filter);
} else {
    $count_stmt = mysqli_prepare($db, $count_query);
    mysqli_stmt_bind_param($count_stmt, "s", $userEmail);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_data = mysqli_fetch_assoc($count_result);
$total_orders = $count_data['total'];
mysqli_stmt_close($count_stmt);

// 페이지 설정
$pagenum = 10;
$pages = ceil($total_orders / $pagenum);
$offset = $pagenum * ($page - 1);

// 전체 주문 내역 조회 (필터 적용)
$all_orders_query = "SELECT * FROM mlangorder_printauto WHERE email = ?";
if ($status_filter !== '') {
    $all_orders_query .= " AND level = ?";
    $all_orders_query .= " ORDER BY no DESC LIMIT ?, ?";
    $all_orders_stmt = mysqli_prepare($db, $all_orders_query);
    mysqli_stmt_bind_param($all_orders_stmt, "siii", $userEmail, $status_filter, $offset, $pagenum);
} else {
    $all_orders_query .= " ORDER BY no DESC LIMIT ?, ?";
    $all_orders_stmt = mysqli_prepare($db, $all_orders_query);
    mysqli_stmt_bind_param($all_orders_stmt, "sii", $userEmail, $offset, $pagenum);
}
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
                        <?php echo number_format($stats['total_orders'] ?? 0); ?>
                        <span class="stat-unit">건</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">총 구매 금액</div>
                    <div class="stat-value">
                        <?php echo number_format($stats['total_amount'] ?? 0); ?>
                        <span class="stat-unit">원</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">미결제 주문</div>
                    <div class="stat-value">
                        <?php echo number_format($unpaid['unpaid_count'] ?? 0); ?>
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
                            <?php foreach ($level_filter_map as $code => $text): ?>
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
                        <strong><?php echo $level_filter_map[$status_filter] ?? ''; ?></strong> 상태:
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
                        <?php foreach ($all_orders as $order):
                            // Type_1 JSON 파싱 - 2줄 슬래시 형식 (라벨 제외)
                            $type1_raw = $order['Type_1'] ?? '';
                            $json_data = json_decode($type1_raw, true);
                            $product_type = strtolower($order['Type'] ?? '');

                            // 1줄: 규격 정보 (종류 / 용지 / 규격)
                            // 2줄: 옵션 정보 (인쇄면 / 수량)
                            $line1_parts = [];
                            $line2_parts = [];

                            if ($json_data) {
                                // order_details가 있는 경우 (스티커 등)
                                if (isset($json_data['order_details'])) {
                                    $d = $json_data['order_details'];

                                    // 1줄: 종류 / 용지 / 규격
                                    if (!empty($d['jong'])) $line1_parts[] = $d['jong'];
                                    if (!empty($d['paper'])) $line1_parts[] = $d['paper'];
                                    if (!empty($d['garo']) && !empty($d['sero'])) {
                                        $line1_parts[] = $d['garo'] . '×' . $d['sero'] . 'mm';
                                    }

                                    // 2줄: 수량 / 모양
                                    if (!empty($d['mesu'])) {
                                        $line2_parts[] = number_format(intval($d['mesu'])) . '매';
                                    }
                                    if (!empty($d['domusong']) && $d['domusong'] != '00000 사각') {
                                        $line2_parts[] = $d['domusong'];
                                    }
                                }
                                // formatted_display에서 파싱 (전단지, 봉투 등)
                                elseif (isset($json_data['formatted_display'])) {
                                    $fd = $json_data['formatted_display'];
                                    // 줄바꿈으로 분리하고 라벨 제거
                                    $lines = preg_split('/\\\\n|\n/', $fd);
                                    $parsed = [];
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        if (empty($line)) continue;
                                        // "라벨: 값" 형식에서 값만 추출
                                        if (strpos($line, ':') !== false) {
                                            $parts = explode(':', $line, 2);
                                            $parsed[trim($parts[0])] = trim($parts[1] ?? '');
                                        } else {
                                            $parsed[] = $line;
                                        }
                                    }

                                    // 1줄: 용지 / 규격
                                    if (!empty($parsed['용지'])) $line1_parts[] = $parsed['용지'];
                                    if (!empty($parsed['규격'])) $line1_parts[] = $parsed['규격'];
                                    if (!empty($parsed['타입'])) $line1_parts[] = $parsed['타입'];
                                    if (!empty($parsed['구분'])) $line1_parts[] = $parsed['구분'];
                                    if (!empty($parsed['재질'])) $line1_parts[] = $parsed['재질'];
                                    if (!empty($parsed['크기'])) $line1_parts[] = $parsed['크기'];

                                    // 2줄: 인쇄면 / 수량
                                    if (!empty($parsed['인쇄면'])) $line2_parts[] = $parsed['인쇄면'];
                                    if (!empty($parsed['인쇄'])) $line2_parts[] = $parsed['인쇄'];
                                    if (!empty($parsed['수량'])) $line2_parts[] = $parsed['수량'];
                                }
                                // MY_type_name, Section_name 등 직접 필드 사용 (양식지 등)
                                else {
                                    if (!empty($json_data['MY_type_name'])) $line1_parts[] = $json_data['MY_type_name'];
                                    if (!empty($json_data['Section_name'])) $line1_parts[] = $json_data['Section_name'];
                                    if (!empty($json_data['PN_type_name'])) $line2_parts[] = $json_data['PN_type_name'];
                                    if (!empty($json_data['MY_amount'])) {
                                        $qty = $json_data['MY_amount'];
                                        $line2_parts[] = number_format(intval($qty)) . '매';
                                    }
                                }
                            } elseif (!empty($type1_raw)) {
                                // JSON이 아닌 경우 원본 텍스트 사용
                                $line1_parts[] = $type1_raw;
                            }

                            // 최종 표시 문자열 생성
                            $display_line1 = implode(' / ', $line1_parts);
                            $display_line2 = implode(' / ', $line2_parts);
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
                            <td style="text-align: right; padding-right: 12px; font-weight: 500;"><?php echo number_format($order['money_4'] ?? 0); ?>원</td>
                            <td style="text-align: center; color: #666;"><?php echo date('Y-m-d', strtotime($order['date'] ?? '')); ?></td>
                            <td style="text-align: center;">
                                <?php
                                $status_code = $order['level'] ?? 1;
                                $level_status_map = [
                                    0 => ['text' => '주문취소', 'color' => '#dc3545'],
                                    1 => ['text' => '주문접수', 'color' => '#6c757d'],
                                    2 => ['text' => '입금확인', 'color' => '#17a2b8'],
                                    3 => ['text' => '작업중', 'color' => '#ffc107'],
                                    4 => ['text' => '배송중', 'color' => '#28a745']
                                ];
                                $status = $level_status_map[$status_code] ?? ['text' => '주문접수', 'color' => '#6c757d'];
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
</body>
</html>
<?php
mysqli_close($db);
?>
