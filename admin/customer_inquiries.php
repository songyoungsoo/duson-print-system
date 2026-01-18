<?php
declare(strict_types=1);

// 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$our_page = intval($_GET['page'] ?? $_POST['page'] ?? 1);
if ($our_page < 1) $our_page = 1;
$inquiry_id = intval($_GET['inquiry_id'] ?? 0);
$status_filter = $_GET['status'] ?? '';
$search_type = $_GET['search_type'] ?? 'name';
$search_value = $_GET['search_value'] ?? '';

$M123="..";
include "top.php";

$page = $our_page;

// 데이터베이스 연결
$admin_dir = dirname(__FILE__);
include $admin_dir . "/../db.php";

// 답변 처리
if ($mode === 'reply' && $inquiry_id > 0) {
    $admin_reply = $_POST['admin_reply'] ?? '';
    $reply_status = $_POST['reply_status'] ?? '답변완료';

    if (!empty($admin_reply)) {
        $update_query = "UPDATE customer_inquiries SET
                        admin_reply = ?,
                        admin_reply_at = NOW(),
                        status = ?,
                        updated_at = NOW()
                        WHERE inquiry_id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssi", $admin_reply, $reply_status, $inquiry_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo "<script>alert('답변이 등록되었습니다.'); location.href='customer_inquiries.php';</script>";
            exit;
        }
    }
}

// 삭제 처리
if ($mode === 'delete' && $inquiry_id > 0) {
    $delete_query = "DELETE FROM customer_inquiries WHERE inquiry_id = ?";
    $stmt = mysqli_prepare($db, $delete_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $inquiry_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "<script>alert('문의가 삭제되었습니다.'); location.href='customer_inquiries.php';</script>";
        exit;
    }
}

// 페이지당 표시 개수
$per_page = 30;
$offset = max(0, ($page - 1) * $per_page);

// WHERE 조건 구성
$where_conditions = [];
$bind_params = [];
$bind_types = "";

if ($status_filter && $status_filter !== 'all') {
    $where_conditions[] = "status = ?";
    $bind_params[] = $status_filter;
    $bind_types .= "s";
}

if ($search_value) {
    switch ($search_type) {
        case 'name':
            $where_conditions[] = "inquiry_name LIKE ?";
            break;
        case 'email':
            $where_conditions[] = "inquiry_email LIKE ?";
            break;
        case 'phone':
            $where_conditions[] = "inquiry_phone LIKE ?";
            break;
        case 'title':
            $where_conditions[] = "inquiry_subject LIKE ?";
            break;
    }
    $bind_params[] = "%{$search_value}%";
    $bind_types .= "s";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// 전체 문의 개수 조회
$count_query = "SELECT COUNT(*) as total FROM customer_inquiries $where_clause";
$total_count = 0;

if (!empty($bind_params)) {
    $stmt = mysqli_prepare($db, $count_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $count_row = mysqli_fetch_assoc($result);
        $total_count = intval($count_row['total']);
        mysqli_stmt_close($stmt);
    }
} else {
    $count_result = mysqli_query($db, $count_query);
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total_count = intval($count_row['total']);
    }
}

// 상태별 개수 조회
$pending_count = 0;
$completed_count = 0;
$processing_count = 0;

$stats_query = "SELECT status, COUNT(*) as cnt FROM customer_inquiries GROUP BY status";
$stats_result = mysqli_query($db, $stats_query);
if ($stats_result) {
    while ($row = mysqli_fetch_assoc($stats_result)) {
        if ($row['status'] === '대기중') $pending_count = intval($row['cnt']);
        elseif ($row['status'] === '답변완료') $completed_count = intval($row['cnt']);
        elseif ($row['status'] === '처리중') $processing_count = intval($row['cnt']);
    }
}
$all_count = $pending_count + $completed_count + $processing_count;

// 전체 페이지 수 계산
$total_pages = max(1, ceil($total_count / $per_page));

// 문의 목록 조회
$query = "SELECT
    inquiry_id,
    user_id,
    inquiry_name as name,
    inquiry_email as email,
    inquiry_phone as phone,
    inquiry_category as inquiry_type,
    inquiry_subject as title,
    inquiry_message as content,
    status,
    admin_reply,
    admin_reply_at as reply_date,
    created_at
FROM customer_inquiries
$where_clause
ORDER BY created_at DESC
LIMIT ? OFFSET ?";

$inquiries = [];

// 바인딩 파라미터에 LIMIT, OFFSET 추가
$all_params = array_merge($bind_params, [$per_page, $offset]);
$all_types = $bind_types . "ii";

$stmt = mysqli_prepare($db, $query);
if ($stmt) {
    if (!empty($all_params)) {
        mysqli_stmt_bind_param($stmt, $all_types, ...$all_params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $inquiries[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// 검색 쿼리 문자열 생성
$query_string = http_build_query(array_filter([
    'status' => $status_filter,
    'search_type' => $search_type,
    'search_value' => $search_value
]));
?>

<link rel="stylesheet" href="css/admin-common.css">

<div class="admin-container">
    <!-- Header -->
    <div class="admin-header">
        <h1>💬 고객 문의 관리</h1>
        <div class="admin-stats">
            <a href="?status=" class="stat-item <?= !$status_filter ? 'highlight' : '' ?>" style="text-decoration:none;color:inherit;">
                전체: <?= number_format($all_count) ?>건
            </a>
            <a href="?status=대기중" class="stat-item <?= $status_filter === '대기중' ? 'highlight' : '' ?>" style="text-decoration:none;color:inherit;">
                대기중: <?= number_format($pending_count) ?>건
            </a>
            <a href="?status=처리중" class="stat-item <?= $status_filter === '처리중' ? 'highlight' : '' ?>" style="text-decoration:none;color:inherit;">
                처리중: <?= number_format($processing_count) ?>건
            </a>
            <a href="?status=답변완료" class="stat-item <?= $status_filter === '답변완료' ? 'highlight' : '' ?>" style="text-decoration:none;color:inherit;">
                답변완료: <?= number_format($completed_count) ?>건
            </a>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="admin-toolbar">
        <form method="get" class="search-group">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <label>검색:</label>
            <select name="search_type">
                <option value="name" <?= $search_type === 'name' ? 'selected' : '' ?>>작성자</option>
                <option value="email" <?= $search_type === 'email' ? 'selected' : '' ?>>이메일</option>
                <option value="phone" <?= $search_type === 'phone' ? 'selected' : '' ?>>연락처</option>
                <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>제목</option>
            </select>
            <input type="text" name="search_value" placeholder="검색어 입력" value="<?= htmlspecialchars($search_value) ?>" style="width:180px;">
            <button type="submit" class="btn btn--primary btn--sm">검색</button>
            <?php if ($search_value || $status_filter): ?>
            <a href="customer_inquiries.php" class="btn btn--secondary btn--sm">초기화</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:50px;">번호</th>
                    <th style="width:80px;">문의유형</th>
                    <th>제목</th>
                    <th style="width:80px;">작성자</th>
                    <th style="width:100px;">연락처</th>
                    <th style="width:70px;">상태</th>
                    <th style="width:85px;">작성일</th>
                    <th style="width:100px;">관리</th>
                </tr>
            </thead>
            <tbody>
<?php if (empty($inquiries)): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <p><?= $search_value ? "'{$search_value}' 검색 결과가 없습니다." : "등록된 문의가 없습니다." ?></p>
                        </div>
                    </td>
                </tr>
<?php else: ?>
<?php foreach ($inquiries as $index => $inquiry):
    $status = $inquiry['status'] ?? '대기중';
    $badge_class = 'badge--pending';
    if ($status === '답변완료') $badge_class = 'badge--completed';
    elseif ($status === '처리중') $badge_class = 'badge--processing';
?>
                <tr>
                    <td class="text-center"><?= $total_count - (($page - 1) * $per_page) - $index ?></td>
                    <td class="text-center"><?= htmlspecialchars($inquiry['inquiry_type']) ?></td>
                    <td>
                        <a href="customer_inquiry_view.php?inquiry_id=<?= $inquiry['inquiry_id'] ?>" class="title-link">
                            <?= htmlspecialchars($inquiry['title']) ?>
                        </a>
                    </td>
                    <td class="text-center"><?= htmlspecialchars($inquiry['name']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($inquiry['phone']) ?></td>
                    <td class="text-center">
                        <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                    </td>
                    <td class="text-center"><?= date('Y-m-d', strtotime($inquiry['created_at'])) ?></td>
                    <td class="text-center">
                        <div class="action-buttons">
                            <a href="customer_inquiry_view.php?inquiry_id=<?= $inquiry['inquiry_id'] ?>" class="btn btn--primary btn--xs">답변</a>
                            <a href="?mode=delete&inquiry_id=<?= $inquiry['inquiry_id'] ?><?= $query_string ? '&'.$query_string : '' ?>"
                               onclick="return confirm('정말 삭제하시겠습니까?');"
                               class="btn btn--danger btn--xs">삭제</a>
                        </div>
                    </td>
                </tr>
<?php endforeach; ?>
<?php endif; ?>
            </tbody>
        </table>

<?php if (!empty($inquiries)): ?>
        <!-- Pagination -->
        <div class="pagination-wrapper">
            <div class="pagination">
<?php if ($page > 1): ?>
                <a href="?page=1<?= $query_string ? '&'.$query_string : '' ?>">처음</a>
                <a href="?page=<?= $page - 1 ?><?= $query_string ? '&'.$query_string : '' ?>">이전</a>
<?php endif; ?>

<?php
$start_page = max(1, $page - 3);
$end_page = min($total_pages, $page + 3);

for ($i = $start_page; $i <= $end_page; $i++):
?>
                <a href="?page=<?= $i ?><?= $query_string ? '&'.$query_string : '' ?>"
                   class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
<?php endfor; ?>

<?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?><?= $query_string ? '&'.$query_string : '' ?>">다음</a>
                <a href="?page=<?= $total_pages ?><?= $query_string ? '&'.$query_string : '' ?>">마지막</a>
<?php endif; ?>
            </div>
            <div class="pagination-info">
                총 <strong><?= number_format($total_count) ?></strong>건
            </div>
        </div>
<?php endif; ?>
    </div>
</div>

<?php include "down.php"; ?>
