<?php
require_once __DIR__ . '/base.php';
require_once __DIR__ . '/../../db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        handleList();
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function handleList() {
    global $db;
    
    $page = intval($_GET['page'] ?? 1);
    $period = $_GET['period'] ?? 'month';
    $status = $_GET['status'] ?? 'all';
    $method = $_GET['method'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    $limit = 18;
    $offset = ($page - 1) * $limit;
    
    $where = [];
    $params = [];
    $types = "";
    
    if ($period !== 'all') {
        $date_condition = getPeriodCondition($period);
        if ($date_condition) {
            $where[] = "date >= ?";
            $params[] = $date_condition;
            $types .= "s";
        }
    }
    
    if ($status !== 'all') {
        if ($status === 'completed') {
            $where[] = "(bank IS NOT NULL AND bank != '' AND bank != '미입금')";
        } elseif ($status === 'pending') {
            $where[] = "(bank IS NULL OR bank = '' OR bank = '미입금')";
        } elseif ($status === 'cancelled') {
            $where[] = "bank = '취소'";
        }
    }
    
    if ($method !== 'all') {
        $where[] = "bank = ?";
        $params[] = $method;
        $types .= "s";
    }
    
    if (!empty($search)) {
        $where[] = "(no LIKE ? OR bankname LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }
    
    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $query = "
        SELECT 
            no as order_no,
            name as customer_name,
            CAST(COALESCE(NULLIF(money_5, ''), 0) AS DECIMAL(12,0)) as amount,
            bank as payment_method,
            bankname as depositor_name,
            CASE 
                WHEN bank = '취소' THEN 'cancelled'
                WHEN bank IS NOT NULL AND bank != '' AND bank != '미입금' THEN 'completed'
                ELSE 'pending'
            END as status,
            date as order_date
        FROM mlangorder_printauto
        {$where_clause}
        ORDER BY date DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = mysqli_prepare($db, $query);
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $payments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
    mysqli_stmt_close($stmt);
    
    $count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto {$where_clause}";
    $count_stmt = mysqli_prepare($db, $count_query);
    $count_types = substr($types, 0, -2);
    $count_params = array_slice($params, 0, -2);
    
    if (!empty($count_params)) {
        mysqli_stmt_bind_param($count_stmt, $count_types, ...$count_params);
    }
    
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt));
    mysqli_stmt_close($count_stmt);
    
    $total_items = $count_result['total'];
    $total_pages = ceil($total_items / $limit);
    
    $stats = getPaymentStats($period);
    
    jsonResponse(true, 'Success', [
        'data' => $payments,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_items,
            'per_page' => $limit
        ],
        'stats' => $stats
    ]);
}

function getPeriodCondition($period) {
    switch ($period) {
        case 'today':
            return date('Y-m-d 00:00:00');
        case 'week':
            return date('Y-m-d 00:00:00', strtotime('-7 days'));
        case 'month':
            return date('Y-m-d 00:00:00', strtotime('-30 days'));
        case '3months':
            return date('Y-m-d 00:00:00', strtotime('-90 days'));
        default:
            return null;
    }
}

function getStatusValue($status) {
    switch ($status) {
        case 'completed':
            return 'completed';
        case 'cancelled':
            return 'cancelled';
        case 'pending':
            return 'pending';
        default:
            return '';
    }
}

function getPaymentStats($period) {
    global $db;
    
    $where = "";
    $params = [];
    $types = "";
    
    if ($period !== 'all') {
        $date_condition = getPeriodCondition($period);
        if ($date_condition) {
            $where = "WHERE date >= ?";
            $params[] = $date_condition;
            $types = "s";
        }
    }
    
    $stats_query = "
        SELECT 
            SUM(CAST(COALESCE(NULLIF(money_5, ''), 0) AS DECIMAL(12,0))) as total_amount,
            SUM(CASE WHEN bank IS NOT NULL AND bank != '' AND bank != '미입금' AND bank != '취소' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN bank IS NULL OR bank = '' OR bank = '미입금' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN bank = '취소' THEN 1 ELSE 0 END) as cancelled_count
        FROM mlangorder_printauto
        {$where}
    ";
    
    $stmt = mysqli_prepare($db, $stats_query);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    return [
        'total_amount' => intval($result['total_amount'] ?? 0),
        'completed_count' => intval($result['completed_count'] ?? 0),
        'pending_count' => intval($result['pending_count'] ?? 0),
        'cancelled_count' => intval($result['cancelled_count'] ?? 0)
    ];
}
