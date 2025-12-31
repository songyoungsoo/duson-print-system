<?php
/**
 * 거래내역조회
 * 경로: /mypage/transactions.php
 */

// 세션 및 인증 처리 (8시간 유지, 자동 로그인 30일)
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('세션이 만료되었습니다. 다시 로그인해주세요.'); location.href='/member/login.php';</script>";
    exit;
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// 데이터베이스 연결 확인
if (!$db) {
    die('데이터베이스 연결 실패: ' . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// 사용자 이메일 조회
$email_query = "SELECT email FROM users WHERE id = ?";
$stmt = mysqli_prepare($db, $email_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
$user_email = $user_data['email'];
mysqli_stmt_close($stmt);

// 필터 파라미터
$search_type = $_GET['type'] ?? '';
$search_date_from = $_GET['date_from'] ?? '';
$search_date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// WHERE 조건 구성
$where_conditions = ["email = ?"];
$params = [$user_email];
$param_types = 's';

// 주문 내역을 거래내역으로 조회
$query = "SELECT
            no as transaction_id,
            date as transaction_date,
            'payment' as transaction_type,
            CAST(money_5 AS UNSIGNED) as amount,
            OrderStyle as status,
            Type,
            name,
            CONCAT('주문번호 #', no) as description
          FROM mlangorder_printauto
          WHERE email = ?";

if ($search_date_from) {
    $query .= " AND date >= ?";
    $params[] = $search_date_from . ' 00:00:00';
    $param_types .= 's';
}

if ($search_date_to) {
    $query .= " AND date <= ?";
    $params[] = $search_date_to . ' 23:59:59';
    $param_types .= 's';
}

$query .= " ORDER BY date DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, $param_types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transactions = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// 총 거래 수 조회
$count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE email = ?";
$count_params = [$user_email];
$count_types = 's';

if ($search_date_from) {
    $count_query .= " AND date >= ?";
    $count_params[] = $search_date_from . ' 00:00:00';
    $count_types .= 's';
}

if ($search_date_to) {
    $count_query .= " AND date <= ?";
    $count_params[] = $search_date_to . ' 23:59:59';
    $count_types .= 's';
}

$stmt = mysqli_prepare($db, $count_query);
mysqli_stmt_bind_param($stmt, $count_types, ...$count_params);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_count = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_count / $limit);
mysqli_stmt_close($stmt);

// 거래 통계
$stats_query = "SELECT
                  COUNT(*) as total_transactions,
                  SUM(CAST(money_5 AS UNSIGNED)) as total_amount
                FROM mlangorder_printauto
                WHERE email = ?";
$stmt = mysqli_prepare($db, $stats_query);
mysqli_stmt_bind_param($stmt, "s", $user_email);
mysqli_stmt_execute($stmt);
$stats_result = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($stats_result);
mysqli_stmt_close($stmt);

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
    <title>거래내역조회 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/common-styles.css">
    <style>
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

        .stats-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: linear-gradient(135deg, #1466BA 0%, #0d4d8a 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-label {
            font-size: 13px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(3, 1fr) auto;
            gap: 12px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }

        .filter-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #1466BA;
            color: white;
        }

        .btn-primary:hover {
            background: #0d4d8a;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .transactions-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }

        .transactions-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }

        .transactions-table tr:hover {
            background: #f8f9fa;
        }

        .transaction-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .type-payment {
            background: #e3f2fd;
            color: #1976d2;
        }

        .type-refund {
            background: #ffebee;
            color: #c62828;
        }

        .amount-positive {
            color: #388e3c;
            font-weight: 600;
        }

        .amount-negative {
            color: #d32f2f;
            font-weight: 600;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2px;
            margin-top: 15px;
            flex-wrap: nowrap;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            height: 26px;
            padding: 0 6px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 12px;
            transition: all 0.2s;
        }

        .pagination a:hover:not(.active):not(.disabled) {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination a.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
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

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-data p {
            margin: 0 0 15px 0;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .mypage-container {
                grid-template-columns: 1fr;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .transactions-table {
                font-size: 12px;
            }

            .transactions-table th,
            .transactions-table td {
                padding: 8px;
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
            <h1 class="page-title">거래내역조회</h1>

            <!-- 통계 -->
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-label">총 거래 건수</div>
                    <div class="stat-value"><?php echo number_format($stats['total_transactions'] ?? 0); ?>건</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">총 거래 금액</div>
                    <div class="stat-value"><?php echo number_format($stats['total_amount'] ?? 0); ?>원</div>
                </div>
            </div>

            <!-- 검색 필터 -->
            <div class="filter-section">
                <form method="get" class="filter-form">
                    <div class="filter-group">
                        <label class="filter-label">시작일</label>
                        <input type="date" name="date_from" class="filter-input" value="<?php echo htmlspecialchars($search_date_from); ?>">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">종료일</label>
                        <input type="date" name="date_to" class="filter-input" value="<?php echo htmlspecialchars($search_date_to); ?>">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">🔍 검색</button>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">&nbsp;</label>
                        <a href="/mypage/transactions.php" class="btn btn-secondary">초기화</a>
                    </div>
                </form>
            </div>

            <!-- 거래 내역 테이블 -->
            <?php if (count($transactions) > 0): ?>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th width="100">거래일시</th>
                        <th width="80">유형</th>
                        <th>내역</th>
                        <th width="100">상품</th>
                        <th width="120" style="text-align: right;">금액</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($transaction['transaction_date'])); ?></td>
                        <td>
                            <span class="transaction-type type-<?php echo $transaction['transaction_type']; ?>">
                                <?php echo $transaction['transaction_type'] == 'payment' ? '결제' : '환불'; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td><?php echo $type_map[$transaction['Type']] ?? $transaction['Type']; ?></td>
                        <td style="text-align: right;">
                            <span class="amount-<?php echo $transaction['transaction_type'] == 'payment' ? 'negative' : 'positive'; ?>">
                                <?php echo number_format($transaction['amount']); ?>원
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- 페이지네이션 -->
            <?php if ($total_pages > 1): ?>
            <?php
                $query_params = http_build_query(array_filter([
                    'date_from' => $search_date_from,
                    'date_to' => $search_date_to
                ]));
                $base_url = "?" . ($query_params ? $query_params . "&" : "");
                $range = 5;
                $start_page = max(1, $page - $range);
                $end_page = min($total_pages, $page + $range);
            ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?php echo $base_url; ?>page=1" class="page-nav" title="맨 처음">«</a>
                    <a href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>" class="page-nav" title="이전">‹</a>
                <?php else: ?>
                    <span class="page-nav disabled">«</span>
                    <span class="page-nav disabled">‹</span>
                <?php endif; ?>

                <?php if ($start_page > 1): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="<?php echo $base_url; ?>page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>" class="page-nav" title="다음">›</a>
                    <a href="<?php echo $base_url; ?>page=<?php echo $total_pages; ?>" class="page-nav" title="맨 끝">»</a>
                <?php else: ?>
                    <span class="page-nav disabled">›</span>
                    <span class="page-nav disabled">»</span>
                <?php endif; ?>
            </div>
            <div style="text-align: center; margin-top: 5px; color: #888; font-size: 12px;">
                <?php echo number_format($page); ?> / <?php echo number_format($total_pages); ?> 페이지
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="no-data">
                <p>📋 거래 내역이 없습니다.</p>
                <?php if ($search_date_from || $search_date_to): ?>
                <a href="/mypage/transactions.php" class="btn btn-primary">전체 내역 보기</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
<?php
mysqli_close($db);
?>
