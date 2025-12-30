<?php
/**
 * 주문 내역 페이지  
 * 사용자의 전체 주문 조회
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

require_once __DIR__ . '/auth_required.php';

$user_id = $current_user['id'];

// 페이지네이션
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 검색 필터
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// WHERE 조건 구성
// mlangorder_printauto는 email 또는 name으로 사용자 구분
$user_email = $current_user['email'];
$user_name = $current_user['name'];

// email이 있으면 email로 검색, 없으면 name으로 검색
if (!empty($user_email)) {
    $where = ["email = ?"];
    $params = [$user_email];
    $types = "s";
} else if (!empty($user_name)) {
    // email이 없으면 name으로 검색 (주문자명)
    $where = ["name = ?"];
    $params = [$user_name];
    $types = "s";
} else {
    // 둘 다 없으면 빈 결과 반환 (보안상 전체 조회 방지)
    $where = ["1 = 0"];
    $params = [];
    $types = "";
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR Hendphone LIKE ? OR phone LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($status_filter)) {
    $where[] = "OrderStyle = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_from)) {
    $where[] = "date >= ?";
    $params[] = $date_from . " 00:00:00";
    $types .= "s";
}

if (!empty($date_to)) {
    $where[] = "date <= ?";
    $params[] = $date_to . " 23:59:59";
    $types .= "s";
}

$where_clause = implode(" AND ", $where);

// 전체 개수 조회
$count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE {$where_clause}";
$stmt = mysqli_prepare($db, $count_query);
if (!empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$total = $total_result['total'];
$total_pages = ceil($total / $per_page);
mysqli_stmt_close($stmt);

// 주문 목록 조회
$query = "
    SELECT no, Type, Type_1, name, Hendphone, phone, date, OrderStyle, money_1, money_2, money_4
    FROM mlangorder_printauto
    WHERE {$where_clause}
    ORDER BY date DESC
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($db, $query);

$select_params = $params;  // COUNT 쿼리에서 사용한 params 복사
$select_params[] = $per_page;
$select_params[] = $offset;
$select_types = $types . "ii";

$orders = false;
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $select_types, ...$select_params);
    mysqli_stmt_execute($stmt);
    $orders = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
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
    <title>주문 내역 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/mlangprintauto/css/common-styles.css">
    <style>
        body { background: #f5f5f5; padding: 10px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .header { background: white; padding: 15px 20px; border-radius: 6px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); margin-bottom: 10px; }
        .header h1 { color: #333; font-size: 20px; margin: 0; }
        .header p { margin: 5px 0 0 0; }

        .filters { background: white; padding: 12px 15px; border-radius: 6px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); margin-bottom: 10px; }
        .filters form { display: flex; flex-wrap: wrap; gap: 8px; align-items: end; }
        .filters input, .filters select { padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; }
        .filters button { padding: 6px 15px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .filters button:hover { background: #5568d3; }

        .orders-table { background: white; padding: 10px 15px; border-radius: 6px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 8px 6px; text-align: left; font-weight: 600; font-size: 13px; border-bottom: 2px solid #e0e0e0; }
        td { padding: 6px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        tr:hover { background: #f0f4ff; }
        tbody tr { transition: background-color 0.2s; }
        
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .status-0, .status-1 { background: #fff3cd; color: #856404; }
        .status-2, .status-3, .status-4 { background: #d1ecf1; color: #0c5460; }
        .status-5, .status-6, .status-7, .status-9, .status-10 { background: #d4edda; color: #155724; }
        .status-8 { background: #c3e6cb; color: #155724; font-weight: bold; }
        
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
        
        .nav-link { margin: 8px 0; }
        .nav-link a { color: #667eea; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 주문 내역</h1>
            <p style="color: #666; margin-top: 5px;">전체 <?php echo number_format($total); ?>건</p>
        </div>
        
        <div class="nav-link">
            <a href="index.php">← 마이페이지로 돌아가기</a>
        </div>
        
        <div class="filters">
            <form method="GET">
                <input type="text" name="search" placeholder="검색 (이름, 전화번호)" value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">전체 상태</option>
                    <?php foreach ($order_statuses as $code => $label): ?>
                        <option value="<?php echo $code; ?>" <?php echo $status_filter == $code ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="시작일">
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="종료일">
                <button type="submit">검색</button>
                <a href="orders.php" style="padding: 8px 20px; background: #6c757d; color: white; border-radius: 4px; text-decoration: none;">초기화</a>
            </form>
        </div>
        
        <div class="orders-table">
            <?php if (mysqli_num_rows($orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 70px;">주문번호</th>
                            <th style="width: 82px;">제품</th>
                            <th style="width: auto;">주문내용</th>
                            <th style="width: 60px;">주문자</th>
                            <th style="width: 90px;">총금액</th>
                            <th style="width: 80px;">주문일자</th>
                            <th style="width: 70px;">상태</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($orders)):
                            // Type_1 파싱 (2줄 슬래시 형식)
                            $line1_parts = [];
                            $line2_parts = [];
                            $type1_raw = $order['Type_1'] ?? '';
                            $json_data = json_decode($type1_raw, true);

                            if ($json_data) {
                                if (isset($json_data['order_details'])) {
                                    $d = $json_data['order_details'];
                                    // 1줄: 종류 / 용지 / 규격
                                    if (!empty($d['jong'])) $line1_parts[] = $d['jong'];
                                    if (!empty($d['paper'])) $line1_parts[] = $d['paper'];
                                    if (!empty($d['garo']) && !empty($d['sero'])) {
                                        $line1_parts[] = $d['garo'] . '×' . $d['sero'] . 'mm';
                                    }
                                    // 2줄: 인쇄면 / 수량
                                    if (!empty($d['print_side'])) $line2_parts[] = $d['print_side'];
                                    if (!empty($d['mesu'])) {
                                        $line2_parts[] = number_format(intval($d['mesu'])) . '매';
                                    }
                                } elseif (isset($json_data['formatted_display'])) {
                                    $fd = $json_data['formatted_display'];
                                    $parsed = [];
                                    $lines = preg_split('/\\\\n|\n/', $fd);
                                    foreach ($lines as $line) {
                                        if (strpos($line, ':') !== false) {
                                            $parts = explode(':', $line, 2);
                                            $parsed[trim($parts[0])] = trim($parts[1] ?? '');
                                        }
                                    }
                                    if (!empty($parsed['용지'])) $line1_parts[] = $parsed['용지'];
                                    if (!empty($parsed['규격'])) $line1_parts[] = $parsed['규격'];
                                    if (!empty($parsed['인쇄면'])) $line2_parts[] = $parsed['인쇄면'];
                                    if (!empty($parsed['수량'])) $line2_parts[] = $parsed['수량'];
                                } else {
                                    if (!empty($json_data['paper_type'])) $line1_parts[] = $json_data['paper_type'];
                                    if (!empty($json_data['size'])) $line1_parts[] = $json_data['size'];
                                    if (!empty($json_data['print_side'])) $line2_parts[] = $json_data['print_side'];
                                    if (!empty($json_data['quantity'])) $line2_parts[] = $json_data['quantity'];
                                }
                            }

                            $order_content = '';
                            if (!empty($line1_parts) || !empty($line2_parts)) {
                                if (!empty($line1_parts)) {
                                    $order_content .= implode(' / ', $line1_parts);
                                }
                                if (!empty($line2_parts)) {
                                    $order_content .= '<br><span style="color:#666;font-size:12px;">' . implode(' / ', $line2_parts) . '</span>';
                                }
                            } else {
                                $order_content = htmlspecialchars(mb_substr($type1_raw, 0, 30));
                            }

                            // 상태 색상
                            $status_code = $order['OrderStyle'] ?? '0';
                            $status_colors = [
                                '0' => '#6c757d', '1' => '#17a2b8', '2' => '#007bff',
                                '3' => '#28a745', '4' => '#ffc107', '5' => '#fd7e14',
                                '6' => '#6f42c1', '7' => '#e83e8c', '8' => '#28a745',
                                '9' => '#fd7e14', '10' => '#e83e8c'
                            ];
                            $status_color = $status_colors[$status_code] ?? '#6c757d';
                        ?>
                            <tr style="cursor: pointer;" onclick="location.href='order_detail.php?no=<?php echo $order['no']; ?>'">
                                <td><a href="order_detail.php?no=<?php echo $order['no']; ?>" style="color: #667eea; text-decoration: none; font-weight: 500;"><?php echo htmlspecialchars($order['no']); ?></a></td>
                                <td><?php echo htmlspecialchars($order['Type']); ?></td>
                                <td style="text-align: left; font-size: 13px;"><?php echo $order_content; ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td style="text-align: right;">₩<?php echo number_format($order['money_2']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($order['date'])); ?></td>
                                <td>
                                    <span style="display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 11px; background: <?php echo $status_color; ?>20; color: <?php echo $status_color; ?>;">
                                        <?php echo $order_statuses[$status_code] ?? $status_code; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        // 쿼리 파라미터 유지
                        $query_params = http_build_query(array_filter([
                            'search' => $search,
                            'status' => $status_filter,
                            'date_from' => $date_from,
                            'date_to' => $date_to
                        ]));
                        $base_url = "orders.php?" . ($query_params ? $query_params . "&" : "");

                        // 표시할 페이지 범위 계산 (좌우 5개씩)
                        $range = 5;
                        $start_page = max(1, $page - $range);
                        $end_page = min($total_pages, $page + $range);

                        // 맨처음
                        if ($page > 1): ?>
                            <a href="<?php echo $base_url; ?>page=1" class="page-nav" title="맨 처음">«</a>
                        <?php else: ?>
                            <span class="page-nav disabled">«</span>
                        <?php endif; ?>

                        <?php // 이전 (1개씩)
                        if ($page > 1): ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>" class="page-nav" title="이전">‹</a>
                        <?php else: ?>
                            <span class="page-nav disabled">‹</span>
                        <?php endif; ?>

                        <?php // 시작 생략 표시
                        if ($start_page > 1): ?>
                            <span class="page-ellipsis">...</span>
                        <?php endif; ?>

                        <?php // 페이지 번호들
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $i; ?>"
                               class="<?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php // 끝 생략 표시
                        if ($end_page < $total_pages): ?>
                            <span class="page-ellipsis">...</span>
                        <?php endif; ?>

                        <?php // 다음 (1개씩)
                        if ($page < $total_pages): ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>" class="page-nav" title="다음">›</a>
                        <?php else: ?>
                            <span class="page-nav disabled">›</span>
                        <?php endif; ?>

                        <?php // 맨끝
                        if ($page < $total_pages): ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $total_pages; ?>" class="page-nav" title="맨 끝">»</a>
                        <?php else: ?>
                            <span class="page-nav disabled">»</span>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: center; margin-top: 5px; color: #888; font-size: 12px;">
                        <?php echo number_format($page); ?> / <?php echo number_format($total_pages); ?> 페이지
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #999;">주문 내역이 없습니다.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
