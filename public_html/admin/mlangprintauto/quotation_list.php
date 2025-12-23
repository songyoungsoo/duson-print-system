<?php
/**
 * 견적서 관리 페이지
 * 저장된 견적서 목록 조회, 상태 관리, 이메일 재발송
 */

include "../../db.php";
include "../../includes/auth.php";

// 관리자 권한 확인
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

mysqli_set_charset($db, 'utf8mb4');

// 페이지네이션 설정
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// 검색 조건
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

// 전체 개수 조회
$count_sql = "SELECT COUNT(*) as total FROM quotations WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $count_sql .= " AND (quotation_no LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status_filter)) {
    $count_sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$stmt = mysqli_prepare($db, $count_sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_row = mysqli_fetch_assoc($count_result);
$total = $total_row['total'];
$totalPages = ceil($total / $perPage);
mysqli_stmt_close($stmt);

// 목록 조회
$list_sql = "SELECT q.*,
             (SELECT COUNT(*) FROM quotation_emails WHERE quotation_id = q.id AND status = 'sent') as email_count
             FROM quotations q WHERE 1=1";

if (!empty($search)) {
    $list_sql .= " AND (q.quotation_no LIKE ? OR q.customer_name LIKE ? OR q.customer_email LIKE ?)";
}

if (!empty($status_filter)) {
    $list_sql .= " AND q.status = ?";
}

$list_sql .= " ORDER BY q.created_at DESC LIMIT ? OFFSET ?";

// 파라미터 재설정
$params = [];
$types = '';

if (!empty($search)) {
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status_filter)) {
    $params[] = $status_filter;
    $types .= 's';
}

$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($db, $list_sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quotations[] = $row;
}
mysqli_stmt_close($stmt);

// 상태별 라벨
$statusLabels = [
    'draft' => ['label' => '임시저장', 'class' => 'status-draft'],
    'sent' => ['label' => '발송완료', 'class' => 'status-sent'],
    'accepted' => ['label' => '수락', 'class' => 'status-accepted'],
    'rejected' => ['label' => '거절', 'class' => 'status-rejected'],
    'expired' => ['label' => '만료', 'class' => 'status-expired']
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 관리 - 두손기획인쇄</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: '맑은 고딕', 'Malgun Gothic', sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { opacity: 0.8; font-size: 14px; }

        .toolbar { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .search-form { display: flex; gap: 10px; align-items: center; }
        .search-form input[type="text"] { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 200px; }
        .search-form select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
        .search-form button { padding: 8px 16px; background: #3498db; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .search-form button:hover { background: #2980b9; }

        .stats { display: flex; gap: 20px; }
        .stat-item { text-align: center; }
        .stat-item .num { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .stat-item .label { font-size: 12px; color: #666; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        tr:hover { background: #f8f9fa; }

        .quotation-no { font-family: monospace; color: #2980b9; font-weight: bold; }
        .customer-name { font-weight: 500; }
        .amount { font-weight: bold; color: #27ae60; text-align: right; }

        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .status-draft { background: #f0f0f0; color: #666; }
        .status-sent { background: #e3f2fd; color: #1976d2; }
        .status-accepted { background: #e8f5e9; color: #388e3c; }
        .status-rejected { background: #ffebee; color: #d32f2f; }
        .status-expired { background: #fff3e0; color: #f57c00; }

        .action-btns { display: flex; gap: 5px; }
        .btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-view { background: #ecf0f1; color: #2c3e50; }
        .btn-email { background: #3498db; color: #fff; }
        .btn-link { background: #9b59b6; color: #fff; }
        .btn-delete { background: #e74c3c; color: #fff; }
        .btn:hover { opacity: 0.8; }

        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 12px 25px;
            border-radius: 25px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .toast.show { opacity: 1; }

        .pagination { padding: 20px; display: flex; justify-content: center; gap: 5px; }
        .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; }
        .pagination a:hover { background: #f0f0f0; }
        .pagination .current { background: #3498db; color: #fff; border-color: #3498db; }

        .empty-message { text-align: center; padding: 50px; color: #666; }

        /* 모달 스타일 */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: #fff; border-radius: 8px; max-width: 900px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { font-size: 18px; }
        .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }
        .modal-body { padding: 20px; }

        .detail-section { margin-bottom: 20px; }
        .detail-section h3 { font-size: 14px; color: #666; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .detail-item { display: flex; }
        .detail-item .label { width: 100px; color: #666; flex-shrink: 0; }
        .detail-item .value { font-weight: 500; }

        .items-table { width: 100%; font-size: 13px; }
        .items-table th { background: #f5f5f5; }
        .items-table td, .items-table th { padding: 8px; border: 1px solid #ddd; }

        .email-history { font-size: 13px; }
        .email-history .email-item { padding: 10px; background: #f9f9f9; border-radius: 4px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>견적서 관리</h1>
            <p>저장된 견적서를 조회하고 관리합니다.</p>
        </div>

        <div class="toolbar">
            <form class="search-form" method="GET">
                <input type="text" name="search" placeholder="견적번호, 담당자, 이메일 검색" value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">전체 상태</option>
                    <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>임시저장</option>
                    <option value="sent" <?php echo $status_filter === 'sent' ? 'selected' : ''; ?>>발송완료</option>
                    <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>수락</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>거절</option>
                    <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>만료</option>
                </select>
                <button type="submit">검색</button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                <a href="quotation_list.php" style="padding: 8px 16px; background: #95a5a6; color: #fff; border-radius: 4px; text-decoration: none;">초기화</a>
                <?php endif; ?>
            </form>

            <div class="stats">
                <div class="stat-item">
                    <div class="num"><?php echo number_format($total); ?></div>
                    <div class="label">총 견적서</div>
                </div>
            </div>
        </div>

        <?php if (empty($quotations)): ?>
        <div class="empty-message">
            <p>조회된 견적서가 없습니다.</p>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 140px;">견적번호</th>
                    <th>담당자</th>
                    <th>이메일</th>
                    <th style="width: 120px; text-align: right;">합계금액</th>
                    <th style="width: 80px;">상태</th>
                    <th style="width: 50px;">발송</th>
                    <th style="width: 140px;">작성일</th>
                    <th style="width: 130px;">작업</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotations as $q): ?>
                <tr>
                    <td class="quotation-no"><?php echo htmlspecialchars($q['quotation_no']); ?></td>
                    <td class="customer-name"><?php echo htmlspecialchars($q['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($q['customer_email'] ?? '-'); ?></td>
                    <td class="amount"><?php echo number_format($q['total_price']); ?>원</td>
                    <td>
                        <?php
                        $st = $q['status'];
                        $stInfo = $statusLabels[$st] ?? ['label' => $st, 'class' => ''];
                        ?>
                        <span class="status-badge <?php echo $stInfo['class']; ?>"><?php echo $stInfo['label']; ?></span>
                    </td>
                    <td style="text-align: center;"><?php echo $q['email_count']; ?>회</td>
                    <td><?php echo date('Y-m-d H:i', strtotime($q['created_at'])); ?></td>
                    <td class="action-btns">
                        <button class="btn btn-view" onclick="viewQuotation(<?php echo $q['id']; ?>)">상세</button>
                        <button class="btn btn-email" onclick="resendEmail(<?php echo $q['id']; ?>)">재발송</button>
                        <?php if (!empty($q['public_token'])): ?>
                        <button class="btn btn-link" onclick="copyPublicLink('<?php echo htmlspecialchars($q['public_token']); ?>')" title="공개 링크 복사">🔗</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">«</a>
            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">‹</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
            <?php if ($i == $page): ?>
            <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">›</a>
            <a href="?page=<?php echo $totalPages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">»</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- 상세보기 모달 -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>견적서 상세</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                로딩 중...
            </div>
        </div>
    </div>

    <script>
    function viewQuotation(id) {
        document.getElementById('detailModal').classList.add('active');
        document.getElementById('modalBody').innerHTML = '로딩 중...';

        fetch('quotation_detail.php?id=' + id)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalBody').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('modalBody').innerHTML = '<p>로드 실패</p>';
            });
    }

    function closeModal() {
        document.getElementById('detailModal').classList.remove('active');
    }

    function resendEmail(id) {
        const email = prompt('견적서를 발송할 이메일 주소를 입력하세요:');
        if (!email) return;

        if (!email.includes('@')) {
            alert('올바른 이메일 주소를 입력해주세요.');
            return;
        }

        fetch('quotation_resend.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, email: email })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('이메일이 발송되었습니다.');
                location.reload();
            } else {
                alert('발송 실패: ' + result.message);
            }
        })
        .catch(error => {
            alert('오류가 발생했습니다.');
        });
    }

    // 모달 외부 클릭 시 닫기
    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // 공개 링크 복사
    function copyPublicLink(token) {
        const url = window.location.origin + '/mlangprintauto/shop/quotation_view.php?token=' + token;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                showToast('✅ 공개 링크가 복사되었습니다');
            }).catch(() => {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showToast('✅ 공개 링크가 복사되었습니다');
        } catch (e) {
            alert('링크: ' + text);
        }
        document.body.removeChild(textarea);
    }

    function showToast(message) {
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = 'toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
    }
    </script>
</body>
</html>
