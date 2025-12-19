<?php
/**
 * 주문 목록 페이지 - 개선 버전
 * 두손기획인쇄 - 새 워크플로우 시스템 통합
 */

// DB 연결
include "../../db.php";
include "../../includes/auth.php";

// 새 워크플로우 시스템
require_once "../../includes/OrderStatusManager.php";
require_once "../../includes/ProofreadingManager.php";

// DB 확인
$mysqli = $db;
if (!$mysqli) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

// 변수 초기화
$mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
$check = $_POST['check'] ?? [];
$no = intval($_REQUEST['no'] ?? 0);
$status_filter = $_GET['status'] ?? '';
$search_keyword = $_GET['search'] ?? '';
$page = intval($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 일괄 삭제
if ($mode === "ChickBoxAll") {
    if (empty($check)) {
        echo "<script>alert('삭제할 항목을 선택하세요.'); history.go(-1);</script>";
        exit;
    }

    foreach ($check as $id) {
        $id = intval($id);
        $stmt = mysqli_prepare($mysqli, "DELETE FROM mlangorder_printauto WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    echo "<script>alert('선택한 항목을 삭제했습니다.'); location.href='OrderList_improved.php';</script>";
    exit;
}

// 일괄 상태 변경
if ($mode === "BulkStatusChange") {
    $new_status = $_POST['new_status'] ?? '';

    if (empty($check) || !$new_status) {
        echo "<script>alert('항목과 상태를 선택하세요.'); history.go(-1);</script>";
        exit;
    }

    $admin_id = $_SESSION['admin_id'] ?? 'admin';

    foreach ($check as $order_no) {
        $order_no = intval($order_no);
        $statusManager = new OrderStatusManager($mysqli, $order_no);
        $statusManager->changeStatus($new_status, $admin_id, '일괄 상태 변경');
    }

    echo "<script>alert('선택한 주문의 상태를 변경했습니다.'); location.href='OrderList_improved.php';</script>";
    exit;
}

// 대시보드 통계 조회
function getDashboardStats($db) {
    $stats = [];

    // 전체 주문 수
    $result = mysqli_query($db, "SELECT COUNT(*) as total FROM mlangorder_printauto");
    $stats['total'] = mysqli_fetch_assoc($result)['total'];

    // 상태별 통계
    $result = mysqli_query($db, "
        SELECT o.OrderStyle, m.status_name_ko, m.color_code, COUNT(*) as count
        FROM mlangorder_printauto o
        LEFT JOIN order_status_master m ON o.OrderStyle = m.status_code
        GROUP BY o.OrderStyle
        ORDER BY m.status_order
    ");

    $stats['by_status'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['by_status'][] = $row;
    }

    // 오늘 주문
    $result = mysqli_query($db, "
        SELECT COUNT(*) as today_count
        FROM mlangorder_printauto
        WHERE DATE(date) = CURDATE()
    ");
    $stats['today'] = mysqli_fetch_assoc($result)['today_count'];

    // 이번 주 주문
    $result = mysqli_query($db, "
        SELECT COUNT(*) as week_count
        FROM mlangorder_printauto
        WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)
    ");
    $stats['this_week'] = mysqli_fetch_assoc($result)['week_count'];

    // 교정 대기 중
    $result = mysqli_query($db, "
        SELECT COUNT(*) as proof_pending
        FROM mlangorder_printauto
        WHERE OrderStyle = 'proof_ready'
    ");
    $stats['proof_pending'] = mysqli_fetch_assoc($result)['proof_pending'];

    return $stats;
}

$dashboard_stats = getDashboardStats($mysqli);

// 주문 목록 조회 쿼리 생성
$where_conditions = [];
$params = [];
$types = '';

if ($status_filter) {
    $where_conditions[] = "o.OrderStyle = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($search_keyword) {
    $where_conditions[] = "(o.name LIKE ? OR o.email LIKE ? OR o.phone LIKE ? OR o.no = ?)";
    $search_param = "%{$search_keyword}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = intval($search_keyword);
    $types .= 'sssi';
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// 전체 개수 조회
$count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto o $where_sql";
if (!empty($params)) {
    $count_stmt = mysqli_prepare($mysqli, $count_query);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
} else {
    $count_result = mysqli_query($mysqli, $count_query);
}
$total_count = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_count / $per_page);

// 주문 목록 조회
$list_query = "
    SELECT o.*, m.status_name_ko, m.color_code
    FROM mlangorder_printauto o
    LEFT JOIN order_status_master m ON o.OrderStyle = m.status_code
    $where_sql
    ORDER BY o.no DESC
    LIMIT ? OFFSET ?
";

if (!empty($params)) {
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';
    $list_stmt = mysqli_prepare($mysqli, $list_query);
    mysqli_stmt_bind_param($list_stmt, $types, ...$params);
    mysqli_stmt_execute($list_stmt);
    $list_result = mysqli_stmt_get_result($list_stmt);
} else {
    $list_query .= " LIMIT $per_page OFFSET $offset";
    $list_result = mysqli_query($mysqli, $list_query);
}

$orders = [];
while ($row = mysqli_fetch_assoc($list_result)) {
    $orders[] = $row;
}

// 모든 상태 목록
$all_statuses = OrderStatusManager::getAllStatuses($mysqli);
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
            background: #f5f7fa;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #3498db;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .filter-group label {
            font-weight: bold;
            color: #2c3e50;
        }
        select, input[type="text"], input[type="search"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #34495e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
            padding: 20px;
        }
        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #3498db;
            text-decoration: none;
            background: white;
        }
        .page-link.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        .bulk-actions {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .action-link {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .action-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 헤더 -->
        <div class="header">
            <h1>📦 주문 관리</h1>
            <p>전체 <?php echo number_format($total_count); ?>건의 주문</p>
        </div>

        <!-- 대시보드 통계 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($dashboard_stats['total']); ?></div>
                <div class="stat-label">전체 주문</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($dashboard_stats['today']); ?></div>
                <div class="stat-label">오늘 주문</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($dashboard_stats['this_week']); ?></div>
                <div class="stat-label">이번 주</div>
            </div>
            <div class="stat-card" style="background: #fff3cd;">
                <div class="stat-value" style="color: #f39c12;"><?php echo number_format($dashboard_stats['proof_pending']); ?></div>
                <div class="stat-label">교정 대기</div>
            </div>
        </div>

        <!-- 상태별 통계 -->
        <div class="stats-grid">
            <?php foreach ($dashboard_stats['by_status'] as $stat): ?>
                <div class="stat-card" style="cursor: pointer;" onclick="location.href='?status=<?php echo $stat['OrderStyle']; ?>'">
                    <div class="stat-value" style="color: <?php echo $stat['color_code'] ?? '#999'; ?>">
                        <?php echo number_format($stat['count']); ?>
                    </div>
                    <div class="stat-label"><?php echo $stat['status_name_ko'] ?? $stat['OrderStyle']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 필터 -->
        <div class="filters">
            <form method="GET" class="filter-row">
                <div class="filter-group">
                    <label>상태:</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="">전체</option>
                        <?php foreach ($all_statuses as $status): ?>
                            <option value="<?php echo $status['status_code']; ?>"
                                <?php echo ($status_filter === $status['status_code']) ? 'selected' : ''; ?>>
                                <?php echo $status['status_name_ko']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>검색:</label>
                    <input type="search" name="search" placeholder="주문번호, 이름, 이메일, 전화번호"
                           value="<?php echo htmlspecialchars($search_keyword); ?>" style="width: 300px;">
                </div>

                <button type="submit" class="btn btn-primary">검색</button>
                <a href="OrderList_improved.php" class="btn">초기화</a>
            </form>
        </div>

        <!-- 일괄 작업 -->
        <div class="bulk-actions">
            <form method="POST" id="bulkForm" style="display: flex; gap: 10px; align-items: center; width: 100%;">
                <input type="hidden" name="mode" value="">
                <label><input type="checkbox" id="selectAll" onclick="toggleAll(this)"> 전체 선택</label>

                <select name="new_status" id="bulkStatus" style="flex: 0 0 200px;">
                    <option value="">상태 변경...</option>
                    <?php foreach ($all_statuses as $status): ?>
                        <option value="<?php echo $status['status_code']; ?>">
                            → <?php echo $status['status_name_ko']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="button" class="btn btn-success" onclick="bulkStatusChange()">선택 항목 상태 변경</button>
                <button type="button" class="btn btn-danger" onclick="bulkDelete()">선택 항목 삭제</button>

                <span style="margin-left: auto; color: #7f8c8d;">
                    선택된 항목: <span id="selectedCount">0</span>개
                </span>
            </form>
        </div>

        <!-- 주문 목록 테이블 -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAllTable" onclick="toggleAll(this)"></th>
                        <th width="80">주문번호</th>
                        <th width="120">주문일시</th>
                        <th width="100">제품</th>
                        <th>고객명</th>
                        <th>연락처</th>
                        <th width="120">금액</th>
                        <th width="150">상태</th>
                        <th width="150">작업</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                                주문 내역이 없습니다.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="check[]" value="<?php echo $order['no']; ?>"
                                           class="order-checkbox" form="bulkForm">
                                </td>
                                <td><strong><?php echo $order['no']; ?></strong></td>
                                <td><?php echo substr($order['date'], 0, 16); ?></td>
                                <td><?php echo htmlspecialchars($order['Type']); ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><?php echo htmlspecialchars($order['phone'] ?: $order['Hendphone']); ?></td>
                                <td><?php echo number_format($order['money_2'] ?: $order['money_1']); ?>원</td>
                                <td>
                                    <span class="status-badge" style="background: <?php echo $order['color_code'] ?? '#999'; ?>">
                                        <?php echo $order['status_name_ko'] ?? $order['OrderStyle']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="admin_fixed.php?mode=OrderView&no=<?php echo $order['no']; ?>"
                                       class="action-link" target="_blank">상세보기</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search_keyword ? '&search=' . urlencode($search_keyword) : ''; ?>"
                       class="page-link">이전</a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 5);
                $end_page = min($total_pages, $page + 5);
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search_keyword ? '&search=' . urlencode($search_keyword) : ''; ?>"
                       class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search_keyword ? '&search=' . urlencode($search_keyword) : ''; ?>"
                       class="page-link">다음</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleAll(checkbox) {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.order-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = count;
        }

        document.querySelectorAll('.order-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function bulkStatusChange() {
            const selected = document.querySelectorAll('.order-checkbox:checked');
            const newStatus = document.getElementById('bulkStatus').value;

            if (selected.length === 0) {
                alert('항목을 선택하세요.');
                return;
            }

            if (!newStatus) {
                alert('변경할 상태를 선택하세요.');
                return;
            }

            if (confirm(`선택한 ${selected.length}개 주문의 상태를 변경하시겠습니까?`)) {
                document.getElementById('bulkForm').mode.value = 'BulkStatusChange';
                document.getElementById('bulkForm').submit();
            }
        }

        function bulkDelete() {
            const selected = document.querySelectorAll('.order-checkbox:checked');

            if (selected.length === 0) {
                alert('항목을 선택하세요.');
                return;
            }

            if (confirm(`선택한 ${selected.length}개 주문을 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.`)) {
                document.getElementById('bulkForm').mode.value = 'ChickBoxAll';
                document.getElementById('bulkForm').submit();
            }
        }
    </script>
</body>
</html>
