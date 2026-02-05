<?php
require_once __DIR__ . '/base.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $page = intval($_GET['page'] ?? 1);
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $status = $_GET['status'] ?? '';
        $product_type = $_GET['product_type'] ?? '';
        $period = $_GET['period'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $where_conditions = ["1=1"];
        
        if ($status !== '') {
            $where_conditions[] = "OrderStyle = '" . mysqli_real_escape_string($db, $status) . "'";
        }
        
        if ($product_type !== '') {
            $where_conditions[] = "Type LIKE '%" . mysqli_real_escape_string($db, $product_type) . "%'";
        }
        
        if ($period !== '') {
            switch ($period) {
                case 'today':
                    $where_conditions[] = "DATE(date) = CURDATE()";
                    break;
                case '7days':
                    $where_conditions[] = "date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case '30days':
                    $where_conditions[] = "date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case '3months':
                    $where_conditions[] = "date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
                    break;
            }
        }
        
        if ($search !== '') {
            $search_escaped = mysqli_real_escape_string($db, $search);
            $where_conditions[] = "(no LIKE '%$search_escaped%' OR name LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%')";
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto {$where_clause}";
        $count_result = mysqli_query($db, $count_query);
        $total_items = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_items / $limit);
        
        $query = "SELECT no, Type, name, email, money_5, date, OrderStyle 
                  FROM mlangorder_printauto 
                  {$where_clause}
                  ORDER BY no DESC 
                  LIMIT {$limit} OFFSET {$offset}";
        
        $result = mysqli_query($db, $query);
        $orders = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = [
                'no' => $row['no'],
                'type' => $row['Type'],
                'name' => $row['name'],
                'email' => $row['email'],
                'amount' => intval($row['money_5']),
                'date' => $row['date'],
                'status' => $row['OrderStyle']
            ];
        }
        
        jsonResponse(true, 'Orders retrieved', [
            'data' => $orders,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => intval($total_items),
                'per_page' => $limit
            ]
        ]);
        break;
        
    case 'view':
        $no = intval($_GET['no'] ?? 0);
        
        if ($no <= 0) {
            jsonResponse(false, 'Invalid order number');
        }
        
        $query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            jsonResponse(true, 'Order found', $row);
        } else {
            jsonResponse(false, 'Order not found');
        }
        break;
        
    case 'update':
        $no = intval($_POST['no'] ?? 0);
        $order_style = $_POST['order_style'] ?? '';
        
        if ($no <= 0) {
            jsonResponse(false, 'Invalid order number');
        }
        
        $query = "UPDATE mlangorder_printauto SET OrderStyle = ? WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "si", $order_style, $no);
        
        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Order updated successfully');
        } else {
            jsonResponse(false, 'Failed to update order');
        }
        break;
        
    case 'delete':
        $no = intval($_POST['no'] ?? 0);
        
        if ($no <= 0) {
            jsonResponse(false, 'Invalid order number');
        }
        
        $query = "UPDATE mlangorder_printauto SET OrderStyle = 'deleted' WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $no);
        
        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Order deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete order');
        }
        break;
        
    default:
        jsonResponse(false, 'Invalid action');
}
