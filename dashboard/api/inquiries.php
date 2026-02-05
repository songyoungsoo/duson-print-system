<?php
require_once __DIR__ . '/base.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $page = intval($_GET['page'] ?? 1);
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $status = $_GET['status'] ?? '';
        
        $where_conditions = ["1=1"];
        
        if ($status !== '') {
            $where_conditions[] = "status = '" . mysqli_real_escape_string($db, $status) . "'";
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $count_query = "SELECT COUNT(*) as total FROM customer_inquiries {$where_clause}";
        $count_result = mysqli_query($db, $count_query);
        $total_items = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_items / $limit);
        
        $query = "SELECT inquiry_id, inquiry_name, inquiry_email, inquiry_subject, 
                         inquiry_category, status, created_at, admin_reply_at
                  FROM customer_inquiries 
                  {$where_clause}
                  ORDER BY inquiry_id DESC 
                  LIMIT {$limit} OFFSET {$offset}";
        
        $result = mysqli_query($db, $query);
        $inquiries = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $inquiries[] = [
                'id' => $row['inquiry_id'],
                'name' => $row['inquiry_name'],
                'email' => $row['inquiry_email'],
                'subject' => $row['inquiry_subject'],
                'category' => $row['inquiry_category'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'replied_at' => $row['admin_reply_at']
            ];
        }
        
        jsonResponse(true, 'Inquiries retrieved', [
            'data' => $inquiries,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => intval($total_items),
                'per_page' => $limit
            ]
        ]);
        break;
        
    case 'view':
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            jsonResponse(false, 'Invalid inquiry ID');
        }
        
        $query = "SELECT * FROM customer_inquiries WHERE inquiry_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            jsonResponse(true, 'Inquiry found', $row);
        } else {
            jsonResponse(false, 'Inquiry not found');
        }
        break;
        
    case 'reply':
        $id = intval($_POST['id'] ?? 0);
        $reply = $_POST['reply'] ?? '';
        
        if ($id <= 0 || empty($reply)) {
            jsonResponse(false, 'Invalid parameters');
        }
        
        $query = "UPDATE customer_inquiries 
                  SET admin_reply = ?, admin_reply_at = NOW(), status = 'answered' 
                  WHERE inquiry_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "si", $reply, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Reply posted successfully');
        } else {
            jsonResponse(false, 'Failed to post reply');
        }
        break;
        
    default:
        jsonResponse(false, 'Invalid action');
}
