<?php
/**
 * 통합 주문 관리 페이지
 *
 * mlangorder_printauto 테이블 통합 관리
 * - 제품별 필터링
 * - 주문 상태 관리
 * - 검색 기능
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/includes/ProductConfig.php';

// 관리자 인증 필수
requireAdminAuth();

// 파라미터 받기
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// 필터 파라미터
$product_filter = $_GET['product'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search_keyword = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// 액션 처리
if ($mode === 'delete' && !empty($no)) {
    $stmt = mysqli_prepare($db, "DELETE FROM mlangorder_printauto WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "주문 #{$no}이(가) 삭제되었습니다.";
    } else {
        $_SESSION['error'] = '삭제 실패: ' . mysqli_error($db);
    }

    mysqli_stmt_close($stmt);
    header("Location: order_manager.php");
    exit;
}

if ($mode === 'update_status' && !empty($no) && isset($_POST['new_status'])) {
    $new_status = $_POST['new_status'];

    $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET OrderStyle = ? WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_status, $no);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "주문 상태가 변경되었습니다.";
    } else {
        $_SESSION['error'] = '상태 변경 실패: ' . mysqli_error($db);
    }

    mysqli_stmt_close($stmt);
    header("Location: order_manager.php");
    exit;
}

// 주문 상태 매핑
$order_statuses = [
    '0' => '미선택',
    '1' => '견적접수',
    '2' => '주문접수',
    '3' => '접수완료',
    '4' => '입금대기',
    '5' => '시안제작중',
    '6' => '시안',
    '7' => '교정',
    '8' => '작업완료',
    '9' => '작업중',
    '10' => '교정작업중'
];

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 관리 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .header h1 {
            color: #333;
        }

        .filters {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .filters form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
            font-size: 12px;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-secondary {
            background: #757575;
            color: white;
        }

        .btn-danger {
            background: #f44336;
            color: white;
            font-size: 12px;
            padding: 6px 12px;
        }

        .btn:hover {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }

        .status-0 { background: #eee; color: #666; }
        .status-1 { background: #fff3cd; color: #856404; }
        .status-2 { background: #d1ecf1; color: #0c5460; }
        .status-3 { background: #d4edda; color: #155724; }
        .status-4 { background: #f8d7da; color: #721c24; }
        .status-5, .status-6 { background: #cce5ff; color: #004085; }
        .status-7, .status-10 { background: #fff3cd; color: #856404; }
        .status-8 { background: #d4edda; color: #155724; }
        .status-9 { background: #cce5ff; color: #004085; }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            padding: 8px 12px;
            margin: 0 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }

        .pagination a.active {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
        }

        .actions {
            white-space: nowrap;
        }

        .product-type {
            font-weight: bold;
            color: #4CAF50;
        }

        .order-no {
            font-weight: bold;
            color: #333;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 주문 관리</h1>
            <a href="product_manager.php" class="btn btn-secondary">← 제품 관리</a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php
        // 통계 조회
        $stats_query = "
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN OrderStyle IN ('2', '3', '9') THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN OrderStyle = '8' THEN 1 ELSE 0 END) as completed,
                SUM(CAST(money_2 AS UNSIGNED)) as total_revenue
            FROM mlangorder_printauto
        ";
        $stats_result = mysqli_query($db, $stats_query);
        $stats = mysqli_fetch_assoc($stats_result);
        ?>

        <div class="stats">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">전체 주문</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-value"><?= number_format($stats['processing']) ?></div>
                <div class="stat-label">진행중</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-value"><?= number_format($stats['completed']) ?></div>
                <div class="stat-label">완료</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stat-value">₩<?= number_format($stats['total_revenue']) ?></div>
                <div class="stat-label">총 매출</div>
            </div>
        </div>

        <!-- 필터 -->
        <div class="filters">
            <form method="GET">
                <div class="filter-group">
                    <label>제품 분류</label>
                    <select name="product">
                        <option value="">전체</option>
                        <?php
                        // Type 값 (레거시)
                        $legacy_types = ['전단지', '명함', '봉투', '스티카', '자석스티카', '카다록', '소량인쇄', '상품권', 'NCR양식지'];
                        foreach ($legacy_types as $type) {
                            $selected = ($product_filter === $type) ? 'selected' : '';
                            echo "<option value='{$type}' {$selected}>{$type}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>주문 상태</label>
                    <select name="status">
                        <option value="">전체</option>
                        <?php foreach ($order_statuses as $code => $label): ?>
                            <option value="<?= $code ?>" <?= $status_filter === $code ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>시작일</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>

                <div class="filter-group">
                    <label>종료일</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>

                <div class="filter-group">
                    <label>검색어 (고객명/전화번호)</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search_keyword) ?>" placeholder="검색어 입력">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">검색</button>
                    <a href="order_manager.php" class="btn btn-secondary">초기화</a>
                </div>
            </form>
        </div>

        <?php
        // WHERE 조건 구성
        $where_clauses = [];
        $params = [];
        $types = '';

        if (!empty($product_filter)) {
            $where_clauses[] = "Type = ?";
            $params[] = $product_filter;
            $types .= 's';
        }

        if (!empty($status_filter)) {
            $where_clauses[] = "OrderStyle = ?";
            $params[] = $status_filter;
            $types .= 's';
        }

        if (!empty($search_keyword)) {
            $where_clauses[] = "(name LIKE ? OR Hendphone LIKE ? OR phone LIKE ?)";
            $search_param = "%{$search_keyword}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= 'sss';
        }

        if (!empty($date_from)) {
            $where_clauses[] = "date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }

        if (!empty($date_to)) {
            $where_clauses[] = "date <= ?";
            $params[] = $date_to . ' 23:59:59';
            $types .= 's';
        }

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        // 전체 개수 조회
        $count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto {$where_sql}";
        if (!empty($types)) {
            $count_stmt = mysqli_prepare($db, $count_query);
            mysqli_stmt_bind_param($count_stmt, $types, ...$params);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
        } else {
            $count_result = mysqli_query($db, $count_query);
        }

        $count_row = mysqli_fetch_assoc($count_result);
        $total = $count_row['total'];
        $total_pages = ceil($total / $limit);

        // 주문 목록 조회
        $query = "SELECT * FROM mlangorder_printauto {$where_sql} ORDER BY no DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($db, $query);

        // bind_param에 LIMIT, OFFSET 추가
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        ?>

        <p>전체 <strong><?= number_format($total) ?></strong>개</p>

        <table>
            <thead>
                <tr>
                    <th>주문번호</th>
                    <th>주문일</th>
                    <th>제품</th>
                    <th>고객명</th>
                    <th>연락처</th>
                    <th>금액</th>
                    <th>상태</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $product_name = $row['Type'];
                    $status_label = $order_statuses[$row['OrderStyle']] ?? $row['OrderStyle'];
                    $price = is_numeric($row['money_2']) ? $row['money_2'] : 0;
                    ?>
                    <tr>
                        <td class="order-no">#<?= $row['no'] ?></td>
                        <td><?= date('Y-m-d', strtotime($row['date'])) ?></td>
                        <td class="product-type"><?= htmlspecialchars($product_name) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['Hendphone']) ?></td>
                        <td><?= number_format($price) ?>원</td>
                        <td><span class="status-badge status-<?= $row['OrderStyle'] ?>"><?= $status_label ?></span></td>
                        <td class="actions">
                            <a href="order_detail.php?no=<?= $row['no'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">상세</a>
                            <a href="order_manager.php?mode=delete&no=<?= $row['no'] ?>" class="btn btn-danger" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php
                    $query_params = http_build_query(array_filter([
                        'product' => $product_filter,
                        'status' => $status_filter,
                        'search' => $search_keyword,
                        'date_from' => $date_from,
                        'date_to' => $date_to,
                        'page' => $i
                    ]));
                    ?>
                    <a href="?<?= $query_params ?>" class="<?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
