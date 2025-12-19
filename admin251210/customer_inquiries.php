<?php
declare(strict_types=1);

// 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
// Save our page value before including top.php
$our_page = intval($_GET['page'] ?? $_POST['page'] ?? 1);
if ($our_page < 1) $our_page = 1; // Ensure page is at least 1
$inquiry_id = intval($_GET['inquiry_id'] ?? 0);

include "top.php";

// Restore our page value after top.php (which might override it)
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
$per_page = 20;
$offset = max(0, ($page - 1) * $per_page);

// 전체 문의 개수 조회
$count_query = "SELECT COUNT(*) as total FROM customer_inquiries";
$count_result = mysqli_query($db, $count_query);
$total_count = 0;
if ($count_result) {
    $count_row = mysqli_fetch_assoc($count_result);
    $total_count = intval($count_row['total']);
}

// 전체 페이지 수 계산
$total_pages = ceil($total_count / $per_page);

// 문의 목록 조회 (최신순)
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
ORDER BY created_at DESC
LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($db, $query);
$inquiries = [];

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $per_page, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $inquiries[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
} else {
    // Handle query preparation error
    echo "<!-- Query preparation error: " . mysqli_error($db) . " -->";
}
?>

<style>
.inquiry-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
.inquiry-table th {
    background: #4A90E2;
    color: white;
    padding: 10px;
    text-align: left;
    font-size: 11pt;
}
.inquiry-table td {
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 10pt;
}
.inquiry-table tr:hover {
    background: #f5f5f5;
}
.status-pending {
    color: #FF6B00;
    font-weight: bold;
}
.status-completed {
    color: #4CAF50;
}
.status-processing {
    color: #2196F3;
}
.btn-reply {
    background: #4A90E2;
    color: white;
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 3px;
}
.btn-delete {
    background: #f44336;
    color: white;
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 3px;
}
.pagination {
    text-align: center;
    margin: 20px 0;
}
.pagination a {
    padding: 5px 10px;
    margin: 0 2px;
    background: #f0f0f0;
    text-decoration: none;
    border: 1px solid #ddd;
}
.pagination a.active {
    background: #4A90E2;
    color: white;
}
.summary-box {
    background: #f9f9f9;
    padding: 15px;
    margin: 20px 0;
    border-left: 4px solid #4A90E2;
}
</style>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
    <td>
        <h2 style="margin: 20px 0;">💬 고객 문의 관리</h2>

        <div class="summary-box">
            <strong>전체 문의:</strong> <?php echo number_format($total_count); ?>건 |
            <strong>대기중:</strong>
            <?php
            $pending_query = "SELECT COUNT(*) as cnt FROM customer_inquiries WHERE status = '대기중'";
            $pending_result = mysqli_query($db, $pending_query);
            if ($pending_result) {
                $pending_row = mysqli_fetch_assoc($pending_result);
                echo number_format(intval($pending_row['cnt']));
            } else {
                echo "0";
            }
            ?>건 |
            <strong>답변완료:</strong>
            <?php
            $completed_query = "SELECT COUNT(*) as cnt FROM customer_inquiries WHERE status = '답변완료'";
            $completed_result = mysqli_query($db, $completed_query);
            if ($completed_result) {
                $completed_row = mysqli_fetch_assoc($completed_result);
                echo number_format(intval($completed_row['cnt']));
            } else {
                echo "0";
            }
            ?>건
        </div>

        <?php if (empty($inquiries)): ?>
        <div style="text-align: center; padding: 50px 0; background: #f5f5f5; margin: 20px 0;">
            <p style="font-size: 14pt; color: #666;">등록된 문의가 없습니다.</p>
        </div>
        <?php else: ?>
        <table class="inquiry-table">
            <thead>
                <tr>
                    <th width="60">번호</th>
                    <th width="100">문의유형</th>
                    <th>제목</th>
                    <th width="100">작성자</th>
                    <th width="120">연락처</th>
                    <th width="100">상태</th>
                    <th width="120">작성일</th>
                    <th width="150">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inquiries as $index => $inquiry): ?>
                <tr>
                    <td align="center"><?php echo $total_count - (($page - 1) * $per_page) - $index; ?></td>
                    <td align="center"><?php echo htmlspecialchars($inquiry['inquiry_type']); ?></td>
                    <td>
                        <a href="customer_inquiry_view.php?inquiry_id=<?php echo $inquiry['inquiry_id']; ?>"
                           style="color: #333; text-decoration: none;">
                            <?php echo htmlspecialchars($inquiry['title']); ?>
                        </a>
                    </td>
                    <td align="center"><?php echo htmlspecialchars($inquiry['name']); ?></td>
                    <td align="center"><?php echo htmlspecialchars($inquiry['phone']); ?></td>
                    <td align="center">
                        <?php
                        $status = $inquiry['status'] ?? '대기중';
                        $status_class = '';
                        if ($status === '대기중') {
                            $status_class = 'status-pending';
                        } elseif ($status === '답변완료') {
                            $status_class = 'status-completed';
                        } elseif ($status === '처리중') {
                            $status_class = 'status-processing';
                        }
                        ?>
                        <span class="<?php echo $status_class; ?>"><?php echo $status; ?></span>
                    </td>
                    <td align="center"><?php echo date('Y-m-d', strtotime($inquiry['created_at'])); ?></td>
                    <td align="center">
                        <a href="customer_inquiry_view.php?inquiry_id=<?php echo $inquiry['inquiry_id']; ?>" class="btn-reply">답변</a>
                        <a href="?mode=delete&inquiry_id=<?php echo $inquiry['inquiry_id']; ?>"
                           onclick="return confirm('정말 삭제하시겠습니까?');"
                           class="btn-delete">삭제</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 페이지네이션 -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1">처음</a>
                <a href="?page=<?php echo $page - 1; ?>">이전</a>
            <?php endif; ?>

            <?php
            $start_page = max(1, $page - 5);
            $end_page = min($total_pages, $page + 5);

            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <a href="?page=<?php echo $i; ?>"
                   class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">다음</a>
                <a href="?page=<?php echo $total_pages; ?>">마지막</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </td>
</tr>
</table>

<?php include "down.php"; ?>