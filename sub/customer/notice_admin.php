<?php
/**
 * 공지사항 관리 페이지 (관리자 전용)
 */

session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/db.php";

// 관리자 권한 확인 (level <= 1 필요)
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $query = "SELECT id, username, level FROM users WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    // level이 1 이하인 경우 관리자로 인정 (5=일반회원, 1=관리자)
    $is_admin = ($user && intval($user['level']) <= 1);
    mysqli_stmt_close($stmt);
}

if (!$is_admin) {
    echo "<script>alert('관리자만 접근 가능합니다. (level 1 필요)'); location.href='notice.php';</script>";
    exit;
}

// 처리 액션
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $is_important = isset($_POST['is_important']) ? 1 : 0;

        if ($title && $content) {
            $query = "INSERT INTO notices (title, content, is_important) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $is_important);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            echo "<script>alert('공지사항이 등록되었습니다.'); location.href='notice_admin.php';</script>";
            exit;
        }
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        $query = "DELETE FROM notices WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo "<script>alert('공지사항이 삭제되었습니다.'); location.href='notice_admin.php';</script>";
        exit;
    }
}

// 공지사항 목록
$query = "SELECT * FROM notices ORDER BY is_important DESC, created_at DESC";
$result = mysqli_query($db, $query);
$notices = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공지사항 관리 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/style250801.css">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 15px;
            font-size: 13px;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        h2 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .back-btn {
            padding: 6px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }
        .back-btn:hover {
            background: #545b62;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 15px;
        }
        .add-section, .list-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .add-section h3, .list-section h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #333;
            border-bottom: 2px solid #1466BA;
            padding-bottom: 8px;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 13px;
        }
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            font-family: inherit;
            box-sizing: border-box;
        }
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .submit-btn {
            width: 100%;
            padding: 10px;
            background: #1466BA;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        .submit-btn:hover {
            background: #0d4d8a;
        }
        .notice-table {
            width: 100%;
            border-collapse: collapse;
        }
        .notice-table th {
            background: #f8f9fa;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
        }
        .notice-table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        .notice-table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }
        .badge-important {
            background: #ff6b6b;
            color: white;
        }
        .delete-btn {
            padding: 4px 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .delete-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <h2>🛠️ 공지사항 관리</h2>
            <a href="notice.php" class="back-btn">← 공지사항 보기</a>
        </div>

        <div class="content-grid">
            <!-- 공지사항 등록 -->
            <div class="add-section">
                <h3>✏️ 공지사항 등록</h3>
                <form method="post">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group">
                        <label>제목 *</label>
                        <input type="text" name="title" required placeholder="공지사항 제목을 입력하세요">
                    </div>

                    <div class="form-group">
                        <label>내용 *</label>
                        <textarea name="content" required placeholder="공지사항 내용을 입력하세요"></textarea>
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_important" id="is_important">
                            <label for="is_important" style="margin: 0;">중요 공지로 표시</label>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">공지사항 등록</button>
                </form>
            </div>

            <!-- 공지사항 목록 -->
            <div class="list-section">
                <h3>📋 등록된 공지사항 (총 <?php echo count($notices); ?>건)</h3>
                <?php if (count($notices) > 0): ?>
                <table class="notice-table">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th width="80">구분</th>
                            <th>제목</th>
                            <th width="100">작성일</th>
                            <th width="70">조회수</th>
                            <th width="70">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notices as $notice): ?>
                        <tr>
                            <td>#<?php echo $notice['id']; ?></td>
                            <td>
                                <?php if ($notice['is_important']): ?>
                                    <span class="badge badge-important">중요</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #e9ecef; color: #666;">일반</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($notice['title']); ?></strong>
                                <div style="font-size: 11px; color: #999; margin-top: 3px;">
                                    <?php echo mb_substr(htmlspecialchars($notice['content']), 0, 50); ?>...
                                </div>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($notice['created_at'])); ?></td>
                            <td><?php echo number_format($notice['view_count']); ?></td>
                            <td>
                                <form method="post" style="display: inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $notice['id']; ?>">
                                    <button type="submit" class="delete-btn">삭제</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; color: #999; padding: 40px 0;">등록된 공지사항이 없습니다.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
<?php
mysqli_close($db);
?>
