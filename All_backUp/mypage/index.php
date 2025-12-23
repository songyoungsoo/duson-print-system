<?php
/**
 * 마이페이지 홈 (대시보드)
 * 경로: /mypage/index.php
 */

session_start();

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/member/login.php';</script>";
    exit;
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

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

// 사용자 이메일 가져오기
$email_query = "SELECT email FROM users WHERE id = ?";
$email_stmt = mysqli_prepare($db, $email_query);
mysqli_stmt_bind_param($email_stmt, "i", $user_id);
mysqli_stmt_execute($email_stmt);
$email_result = mysqli_stmt_get_result($email_stmt);
$user_email_data = mysqli_fetch_assoc($email_result);
$userEmail = $user_email_data['email'] ?? '';
mysqli_stmt_close($email_stmt);

// 전체 주문 수
$count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE email = ?";
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

// 전체 주문 내역 조회
$all_orders_query = "SELECT * FROM mlangorder_printauto WHERE email = ? ORDER BY no DESC LIMIT ?, ?";
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
            padding: 12px 8px;
            text-align: center;
            font-weight: 500;
            font-size: 13px;
        }

        .order-history-table td {
            padding: 12px 8px;
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
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            color: #333;
            text-decoration: none;
        }

        .pagination a:hover {
            background: #f8f9fa;
        }

        .pagination a.active {
            background: #1466BA;
            color: white;
            border-color: #1466BA;
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
                <h2 class="section-title">📦 전체 주문조회 & 배송조회</h2>

                <div class="total-count">
                    총 <strong><?php echo number_format($total_orders); ?></strong>건의 주문
                </div>

                <?php if ($total_orders > 0): ?>
                <table class="order-history-table">
                    <thead>
                        <tr>
                            <th width="8%">주문번호</th>
                            <th width="12%">이름</th>
                            <th width="*">주문내용</th>
                            <th width="12%">총금액</th>
                            <th width="15%">주문일자</th>
                            <th width="10%">상태</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_orders as $order):
                            // Type_1 JSON 파싱
                            $type1_display = $order['Type_1'] ?? '';
                            $json_data = json_decode($type1_display, true);

                            if ($json_data && isset($json_data['formatted_display'])) {
                                $type1_display = $json_data['formatted_display'];
                            } elseif ($json_data && isset($json_data['order_details'])) {
                                $details = $json_data['order_details'];
                                $display_parts = [];

                                if (isset($details['jong'])) $display_parts[] = $details['jong'];
                                if (isset($details['garo']) && isset($details['sero'])) {
                                    $display_parts[] = $details['garo'] . 'mm × ' . $details['sero'] . 'mm';
                                }
                                if (isset($details['mesu'])) $display_parts[] = number_format($details['mesu']) . '매';
                                if (isset($details['domusong'])) $display_parts[] = $details['domusong'];

                                $type1_display = implode(' / ', $display_parts);
                            }
                        ?>
                        <tr>
                            <td>
                                <a href="/session/order_view_my.php?no=<?php echo $order['no']; ?>">
                                    #<?php echo $order['no']; ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($order['name'] ?? ''); ?></td>
                            <td style="text-align: left; padding-left: 20px;">
                                <?php echo nl2br(htmlspecialchars($type1_display)); ?>
                            </td>
                            <td><?php echo number_format($order['money_4'] ?? 0); ?>원</td>
                            <td><?php echo htmlspecialchars($order['date'] ?? ''); ?></td>
                            <td>
                                <?php
                                $status_code = $order['level'] ?? 1;
                                $level_status_map = [
                                    0 => "주문취소",
                                    1 => "주문접수",
                                    2 => "입금확인",
                                    3 => "작업중",
                                    4 => "배송중"
                                ];
                                echo $level_status_map[$status_code] ?? '주문접수';
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
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
