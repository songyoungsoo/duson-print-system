<?php
/**
 * 알림 센터 - 알림 발송 현황 및 관리
 */
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../db.php';

requireAdminAuth();

// 필터 파라미터
$status = $_GET['status'] ?? 'all';
$type = $_GET['type'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// 조건 빌드
$where = "1=1";
$params = [];
$types = '';

if ($status !== 'all') {
    $where .= " AND status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($type !== 'all') {
    $where .= " AND notification_type = ?";
    $params[] = $type;
    $types .= 's';
}

// 총 개수 조회
$countQuery = "SELECT COUNT(*) as cnt FROM notification_logs WHERE $where";
$stmt = mysqli_prepare($db, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$countResult = mysqli_stmt_get_result($stmt);
$totalCount = mysqli_fetch_assoc($countResult)['cnt'];
$totalPages = ceil($totalCount / $perPage);

// 데이터 조회
$query = "SELECT n.*, o.name as order_name, o.Type as order_type
          FROM notification_logs n
          LEFT JOIN mlangorder_printauto o ON n.order_id = o.no
          WHERE $where
          ORDER BY n.created_at DESC
          LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= 'ii';

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}

// 통계 조회
$statsQuery = "SELECT
    COUNT(*) as total,
    SUM(status = 'sent') as sent,
    SUM(status = 'failed') as failed,
    SUM(status = 'pending') as pending
    FROM notification_logs
    WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
$statsResult = mysqli_query($db, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>알림 센터 - 두손기획</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans KR', sans-serif; background: #f5f5f5; }
        .header {
            background: #fff;
            padding: 16px 24px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 20px; font-weight: 500; }
        .container { max-width: 1400px; margin: 0 auto; padding: 24px; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-card .label { font-size: 13px; color: #666; margin-bottom: 8px; }
        .stat-card .value { font-size: 28px; font-weight: 500; }
        .stat-card.sent .value { color: #34a853; }
        .stat-card.failed .value { color: #ea4335; }
        .stat-card.pending .value { color: #fbbc04; }
        .filters {
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #f5f5f5; }
        .btn-primary { background: #1a73e8; color: #fff; border-color: #1a73e8; }
        .table-wrapper {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: 500; color: #5f6368; font-size: 13px; }
        tr:hover { background: #f8f9fa; }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-sent { background: #d4edda; color: #155724; }
        .badge-failed { background: #f8d7da; color: #721c24; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-alimtalk { background: #ffe066; color: #333; }
        .badge-sms { background: #4dabf7; color: #fff; }
        .badge-email { background: #868e96; color: #fff; }
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #666;
            font-size: 13px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .pagination a.active { background: #1a73e8; color: #fff; border-color: #1a73e8; }
        .empty { text-align: center; padding: 40px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔔 알림 센터</h1>
        <div>
            <button class="btn" onclick="location.href='admin.php'">← 관리자</button>
            <button class="btn" onclick="location.href='/admin/dashboard.php'">📊 대시보드</button>
        </div>
    </div>

    <div class="container">
        <!-- 통계 카드 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">📊 최근 7일 전체</div>
                <div class="value"><?php echo number_format($stats['total']); ?>건</div>
            </div>
            <div class="stat-card sent">
                <div class="label">✅ 발송 성공</div>
                <div class="value"><?php echo number_format($stats['sent']); ?>건</div>
            </div>
            <div class="stat-card failed">
                <div class="label">❌ 발송 실패</div>
                <div class="value"><?php echo number_format($stats['failed']); ?>건</div>
            </div>
            <div class="stat-card pending">
                <div class="label">⏳ 대기 중</div>
                <div class="value"><?php echo number_format($stats['pending']); ?>건</div>
            </div>
        </div>

        <!-- 필터 -->
        <div class="filters">
            <select onchange="filterByStatus(this.value)">
                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>모든 상태</option>
                <option value="sent" <?php echo $status === 'sent' ? 'selected' : ''; ?>>발송 성공</option>
                <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>발송 실패</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>대기 중</option>
            </select>
            <select onchange="filterByType(this.value)">
                <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>모든 유형</option>
                <option value="kakao_alimtalk" <?php echo $type === 'kakao_alimtalk' ? 'selected' : ''; ?>>카카오 알림톡</option>
                <option value="sms" <?php echo $type === 'sms' ? 'selected' : ''; ?>>SMS</option>
                <option value="email" <?php echo $type === 'email' ? 'selected' : ''; ?>>이메일</option>
            </select>
            <button class="btn" onclick="retryFailed()">🔄 실패 건 재발송</button>
        </div>

        <!-- 알림 목록 -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>주문번호</th>
                        <th>유형</th>
                        <th>수신자</th>
                        <th>내용</th>
                        <th>상태</th>
                        <th>발송일시</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notifications)): ?>
                    <tr><td colspan="7" class="empty">알림 내역이 없습니다.</td></tr>
                    <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                    <tr>
                        <td><?php echo $n['id']; ?></td>
                        <td>
                            <?php if ($n['order_id']): ?>
                            <a href="detail.php?no=<?php echo $n['order_id']; ?>" style="color: #1a73e8;">
                                #<?php echo $n['order_id']; ?>
                            </a>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $typeBadge = [
                                'kakao_alimtalk' => 'badge-alimtalk',
                                'sms' => 'badge-sms',
                                'email' => 'badge-email'
                            ][$n['notification_type']] ?? '';
                            $typeLabel = [
                                'kakao_alimtalk' => '알림톡',
                                'sms' => 'SMS',
                                'email' => '이메일'
                            ][$n['notification_type']] ?? $n['notification_type'];
                            ?>
                            <span class="badge <?php echo $typeBadge; ?>"><?php echo $typeLabel; ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($n['recipient']); ?></td>
                        <td class="message-preview" title="<?php echo htmlspecialchars($n['message']); ?>">
                            <?php echo htmlspecialchars(mb_substr($n['message'], 0, 50)); ?>...
                        </td>
                        <td>
                            <?php
                            $statusBadge = [
                                'sent' => 'badge-sent',
                                'failed' => 'badge-failed',
                                'pending' => 'badge-pending'
                            ][$n['status']] ?? '';
                            $statusLabel = [
                                'sent' => '성공',
                                'failed' => '실패',
                                'pending' => '대기'
                            ][$n['status']] ?? $n['status'];
                            ?>
                            <span class="badge <?php echo $statusBadge; ?>"><?php echo $statusLabel; ?></span>
                        </td>
                        <td><?php echo $n['sent_at'] ?? $n['created_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?status=<?php echo $status; ?>&type=<?php echo $type; ?>&page=<?php echo $i; ?>"
               class="<?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function filterByStatus(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('status', value);
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }

        function filterByType(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('type', value);
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }

        async function retryFailed() {
            if (!confirm('실패한 알림을 재발송하시겠습니까?')) return;

            try {
                const response = await fetch('/admin/api/notification_retry.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert(result.error || '재발송 실패');
                }
            } catch (error) {
                alert('서버 오류가 발생했습니다.');
            }
        }
    </script>
</body>
</html>
