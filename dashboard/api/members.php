<?php
require_once __DIR__ . '/base.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $page = intval($_GET['page'] ?? 1);
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $search = $_GET['search'] ?? '';
        
        $where_conditions = ["1=1"];
        
        if ($search !== '') {
            $search_escaped = mysqli_real_escape_string($db, $search);
            $where_conditions[] = "(username LIKE '%$search_escaped%' OR name LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%' OR phone LIKE '%$search_escaped%')";
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $count_query = "SELECT COUNT(*) as total FROM users {$where_clause}";
        $count_result = mysqli_query($db, $count_query);
        $total_items = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_items / $limit);
        
        $query = "SELECT id, username, name, email, phone, created_at 
                  FROM users 
                  {$where_clause}
                  ORDER BY id DESC 
                  LIMIT {$limit} OFFSET {$offset}";
        
        $result = mysqli_query($db, $query);
        $members = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $members[] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'created_at' => $row['created_at']
            ];
        }
        
        jsonResponse(true, 'Members retrieved', [
            'data' => $members,
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
            jsonResponse(false, 'Invalid member ID');
        }
        
        $query = "SELECT id, username, name, email, phone, postcode, address, detail_address, 
                         business_number, business_name, business_owner, created_at 
                  FROM users WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            jsonResponse(true, 'Member found', $row);
        } else {
            jsonResponse(false, 'Member not found');
        }
        break;
        
    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        
        if ($id <= 0) {
            jsonResponse(false, 'Invalid member ID');
        }
        
        $query = "UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $email, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Member updated successfully');
        } else {
            jsonResponse(false, 'Failed to update member');
        }
        break;
        
    case 'email_typo_scan':
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/EmailTypoFixer.php';
        $result = EmailTypoFixer::scanAll($db);
        jsonResponse(true, 'Scan complete', $result);
        break;

    case 'email_typo_fix':
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/EmailTypoFixer.php';
        $userId = intval($_POST['user_id'] ?? 0);
        $newEmail = trim($_POST['new_email'] ?? '');
        if ($userId <= 0 || empty($newEmail)) {
            jsonResponse(false, '잘못된 파라미터');
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, '유효하지 않은 이메일 형식');
        }
        $success = EmailTypoFixer::fix($db, $userId, $newEmail);
        if ($success) {
            jsonResponse(true, '이메일이 수정되었습니다');
        } else {
            jsonResponse(false, '수정 실패: ' . mysqli_error($db));
        }
        break;

    case 'email_typo_fix_all':
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/EmailTypoFixer.php';
        $scanResult = EmailTypoFixer::scanAll($db);
        if (empty($scanResult['typos'])) {
            jsonResponse(true, '수정할 오타가 없습니다', ['fixed' => 0, 'failed' => 0]);
        }
        $fixResult = EmailTypoFixer::fixAll($db, $scanResult['typos']);
        jsonResponse(true, "수정 {$fixResult['fixed']}건, 실패 {$fixResult['failed']}건", $fixResult);
        break;

    default:
        jsonResponse(false, 'Invalid action');
}
