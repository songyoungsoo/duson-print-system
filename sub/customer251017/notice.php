<?php
/**
 * 공지사항 목록 페이지
 */

session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/db.php";

// 페이징 처리
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 관리자 확인
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $query = "SELECT level FROM users WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    $is_admin = ($user && $user['level'] >= 5);
}

// 전체 공지사항 수
$count_query = "SELECT COUNT(*) as total FROM notices";
$count_result = mysqli_query($db, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$total = $count_data['total'];
$total_pages = ceil($total / $per_page);

// 공지사항 목록 조회
$query = "SELECT * FROM notices ORDER BY is_important DESC, created_at DESC LIMIT ?, ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "ii", $offset, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$notices = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공지사항 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/style250801.css">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 15px;
            font-size: 13px;
        }
        .notice-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1466BA;
        }
        h2 {
            color: #333;
            margin: 0;
            font-size: 18px;
        }
        .admin-btn {
            padding: 6px 15px;
            background: #1466BA;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }
        .admin-btn:hover {
            background: #0d4d8a;
        }
        .notice-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notice-item {
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 10px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notice-item:hover {
            background: #f8f9fa;
        }
        .notice-item.important {
            background: #fff9e6;
        }
        .notice-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        .badge-important {
            background: #ff6b6b;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 500;
        }
        .notice-title {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            flex: 1;
        }
        .notice-meta {
            font-size: 12px;
            color: #999;
            display: flex;
            gap: 15px;
        }
        .notice-content {
            display: none;
            padding: 15px;
            margin-top: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            white-space: pre-line;
            line-height: 1.6;
            font-size: 13px;
        }
        .notice-item.active .notice-content {
            display: block;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            display: inline-block;
            padding: 6px 10px;
            margin: 0 3px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            font-size: 13px;
        }
        .pagination a.active {
            background: #1466BA;
            color: white;
            border-color: #1466BA;
        }
        .pagination a:hover:not(.active) {
            background: #f8f9fa;
        }
        .no-notices {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 16px;
            background: #6c757d;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
        }
        .back-link:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="notice-container">
        <div class="header">
            <h2>📢 공지사항</h2>
            <?php if ($is_admin): ?>
                <a href="notice_admin.php" class="admin-btn">✏️ 공지사항 관리</a>
            <?php endif; ?>
        </div>

        <?php if (count($notices) > 0): ?>
        <ul class="notice-list">
            <?php foreach ($notices as $notice): ?>
            <li class="notice-item <?php echo $notice['is_important'] ? 'important' : ''; ?>"
                onclick="toggleNotice(<?php echo $notice['id']; ?>)">
                <div class="notice-header">
                    <?php if ($notice['is_important']): ?>
                        <span class="badge-important">중요</span>
                    <?php endif; ?>
                    <span class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></span>
                </div>
                <div class="notice-meta">
                    <span>👤 <?php echo htmlspecialchars($notice['author']); ?></span>
                    <span>📅 <?php echo date('Y-m-d', strtotime($notice['created_at'])); ?></span>
                    <span>👁️ <?php echo number_format($notice['view_count']); ?></span>
                </div>
                <div class="notice-content" id="content-<?php echo $notice['id']; ?>">
                    <?php echo htmlspecialchars($notice['content']); ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="no-notices">
            <p style="font-size: 18px; margin-bottom: 10px;">📭</p>
            <p>등록된 공지사항이 없습니다.</p>
        </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="/sub/customer/how_to_use.php" class="back-link">← 고객센터로 돌아가기</a>
        </div>
    </div>

    <script>
        function toggleNotice(id) {
            const item = event.currentTarget;
            const wasActive = item.classList.contains('active');

            // 모든 항목 닫기
            document.querySelectorAll('.notice-item').forEach(el => {
                el.classList.remove('active');
            });

            // 클릭한 항목만 열기 (이미 열려있었으면 닫힘)
            if (!wasActive) {
                item.classList.add('active');

                // 조회수 증가 (AJAX)
                fetch('notice_view.php?id=' + id)
                    .catch(err => console.log('View count update failed'));
            }
        }
    </script>
</body>
</html>
<?php
mysqli_close($db);
?>
