<?php
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/includes/ProofWorkflow.php';

// Admin authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin_login.php");
    exit;
}

$proofWorkflow = new ProofWorkflow($db);

// Get filter parameters
$status_filter = $_GET['status'] ?? 'pending';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get proof list
$proofs = $proofWorkflow->getPendingProofs($status_filter, $limit, $offset);

// Get statistics
$stats = $proofWorkflow->getProofStats();

// Count total for pagination
$count_query = "SELECT COUNT(*) as total FROM proof_status";
if (!empty($status_filter)) {
    $count_query .= " WHERE status = ?";
    $stmt = mysqli_prepare($db, $count_query);
    mysqli_stmt_bind_param($stmt, "s", $status_filter);
    mysqli_stmt_execute($stmt);
    $count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
} else {
    $count_result = mysqli_fetch_assoc(mysqli_query($db, $count_query));
}

$total_items = $count_result['total'];
$total_pages = ceil($total_items / $limit);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>교정 확인 관리 - 두손기획인쇄</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', sans-serif; background: #f5f5f5; padding: 20px; }
        
        .container { max-width: 1400px; margin: 0 auto; }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 { color: #333; margin-bottom: 10px; }
        .header p { color: #666; }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #667eea; }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .filters select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .proof-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .proof-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 20px;
        }
        
        .proof-item:last-child { border-bottom: none; }
        
        .proof-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .proof-info h3 { color: #333; margin-bottom: 5px; }
        .proof-info p { color: #666; font-size: 14px; }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-revision_requested { background: #f8d7da; color: #721c24; }
        .status-revised { background: #d1ecf1; color: #0c5460; }
        
        .proof-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item { color: #666; font-size: 14px; }
        .detail-item strong { color: #333; }
        
        .files-preview {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .files-preview h4 { color: #333; margin-bottom: 10px; font-size: 14px; }
        .file-list { display: flex; flex-wrap: wrap; gap: 10px; }
        .file-item {
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-approve:hover { background: #218838; }
        
        .btn-revision {
            background: #ffc107;
            color: #333;
        }
        
        .btn-revision:hover { background: #e0a800; }
        
        .btn-view {
            background: #667eea;
            color: white;
        }
        
        .btn-view:hover { background: #5568d3; }
        
        .comment-section {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            display: none;
        }
        
        .comment-section.active { display: block; }
        
        .comment-section textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Malgun Gothic', sans-serif;
            resize: vertical;
        }
        
        .comment-section .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination a:hover { background: #f0f0f0; }
        .pagination .current { background: #667eea; color: white; border-color: #667eea; }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 교정 확인 관리</h1>
            <p>고객이 업로드한 파일을 검토하고 승인 또는 수정을 요청할 수 있습니다</p>
        </div>
        
        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>전체 교정 요청</h3>
                <div class="number"><?php echo number_format($stats['total'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>대기중</h3>
                <div class="number" style="color: #ffc107;"><?php echo number_format($stats['pending'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>승인됨</h3>
                <div class="number" style="color: #28a745;"><?php echo number_format($stats['approved'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>수정 요청</h3>
                <div class="number" style="color: #dc3545;"><?php echo number_format($stats['revision_requested'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>수정 완료</h3>
                <div class="number" style="color: #17a2b8;"><?php echo number_format($stats['revised'] ?? 0); ?></div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <label>상태 필터:</label>
            <select id="statusFilter" onchange="filterByStatus(this.value)">
                <option value="">전체</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>대기중</option>
                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>승인됨</option>
                <option value="revision_requested" <?php echo $status_filter === 'revision_requested' ? 'selected' : ''; ?>>수정 요청</option>
                <option value="revised" <?php echo $status_filter === 'revised' ? 'selected' : ''; ?>>수정 완료</option>
            </select>
            <a href="index.php" style="margin-left: auto; color: #667eea; text-decoration: none;">← 대시보드로</a>
        </div>
        
        <!-- Proof List -->
        <div class="proof-list">
            <?php if (empty($proofs)): ?>
                <div class="no-results">
                    <p>교정 요청이 없습니다.</p>
                </div>
            <?php else: ?>
                <?php foreach ($proofs as $proof): ?>
                    <div class="proof-item">
                        <div class="proof-header">
                            <div class="proof-info">
                                <h3>주문번호: <?php echo htmlspecialchars($proof['order_no']); ?></h3>
                                <p>제품: <?php echo htmlspecialchars($proof['product_type']); ?> | 고객: <?php echo htmlspecialchars($proof['name'] ?? '-'); ?></p>
                            </div>
                            <span class="status-badge status-<?php echo $proof['status']; ?>">
                                <?php
                                $status_text = [
                                    'pending' => '대기중',
                                    'approved' => '승인됨',
                                    'revision_requested' => '수정 요청',
                                    'revised' => '수정 완료'
                                ];
                                echo $status_text[$proof['status']] ?? $proof['status'];
                                ?>
                            </span>
                        </div>
                        
                        <div class="proof-details">
                            <div class="detail-item">
                                <strong>요청일:</strong> <?php echo date('Y-m-d H:i', strtotime($proof['created_at'])); ?>
                            </div>
                            <?php if ($proof['reviewed_at']): ?>
                                <div class="detail-item">
                                    <strong>검토일:</strong> <?php echo date('Y-m-d H:i', strtotime($proof['reviewed_at'])); ?>
                                </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <strong>연락처:</strong> <?php echo htmlspecialchars($proof['Hendphone'] ?? $proof['phone'] ?? '-'); ?>
                            </div>
                        </div>
                        
                        <?php if ($proof['admin_comment']): ?>
                            <div class="files-preview">
                                <h4>관리자 코멘트:</h4>
                                <p style="color: #666;"><?php echo nl2br(htmlspecialchars($proof['admin_comment'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($proof['revision_files']): ?>
                            <div class="files-preview">
                                <h4>수정본 파일:</h4>
                                <div class="file-list">
                                    <?php
                                    $revision_files = json_decode($proof['revision_files'], true);
                                    if (!empty($revision_files)) {
                                        foreach ($revision_files as $file) {
                                            echo '<div class="file-item">📄 ' . htmlspecialchars($file['original_name'] ?? 'file') . '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="actions">
                            <button class="btn btn-view" onclick="window.open('/mlangorder_printauto/order_detail.php?no=<?php echo urlencode($proof['order_no']); ?>', '_blank')">
                                주문 상세보기
                            </button>
                            
                            <?php if ($proof['status'] === 'pending' || $proof['status'] === 'revised'): ?>
                                <button class="btn btn-approve" onclick="approveProof(<?php echo $proof['id']; ?>, '<?php echo htmlspecialchars($proof['order_no']); ?>')">
                                    ✓ 승인
                                </button>
                                <button class="btn btn-revision" onclick="toggleCommentSection('comment-<?php echo $proof['id']; ?>')">
                                    ✎ 수정 요청
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="comment-section" id="comment-<?php echo $proof['id']; ?>">
                            <textarea id="comment-text-<?php echo $proof['id']; ?>" placeholder="수정 요청 사유를 입력하세요..."></textarea>
                            <div class="btn-group">
                                <button class="btn btn-revision" onclick="requestRevision(<?php echo $proof['id']; ?>, '<?php echo htmlspecialchars($proof['order_no']); ?>')">
                                    수정 요청 전송
                                </button>
                                <button class="btn" onclick="toggleCommentSection('comment-<?php echo $proof['id']; ?>')" style="background: #6c757d; color: white;">
                                    취소
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?status=<?php echo urlencode($status_filter); ?>&page=<?php echo $page - 1; ?>">← 이전</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 5); $i <= min($total_pages, $page + 5); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?status=<?php echo urlencode($status_filter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?status=<?php echo urlencode($status_filter); ?>&page=<?php echo $page + 1; ?>">다음 →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function filterByStatus(status) {
            window.location.href = '?status=' + encodeURIComponent(status);
        }
        
        function toggleCommentSection(id) {
            const section = document.getElementById(id);
            section.classList.toggle('active');
        }
        
        function approveProof(proofId, orderNo) {
            if (!confirm('이 교정을 승인하시겠습니까?\n주문번호: ' + orderNo)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'approve');
            formData.append('proof_id', proofId);
            
            fetch('api/proof_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('교정이 승인되었습니다.');
                    location.reload();
                } else {
                    alert('오류: ' + (data.message || '승인 실패'));
                }
            })
            .catch(error => {
                alert('오류: ' + error.message);
            });
        }
        
        function requestRevision(proofId, orderNo) {
            const comment = document.getElementById('comment-text-' + proofId).value.trim();
            
            if (!comment) {
                alert('수정 요청 사유를 입력해주세요.');
                return;
            }
            
            if (!confirm('수정을 요청하시겠습니까?\n주문번호: ' + orderNo)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'request_revision');
            formData.append('proof_id', proofId);
            formData.append('comment', comment);
            
            fetch('api/proof_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('수정 요청이 전송되었습니다.');
                    location.reload();
                } else {
                    alert('오류: ' + (data.message || '요청 실패'));
                }
            })
            .catch(error => {
                alert('오류: ' + error.message);
            });
        }
    </script>
</body>
</html>
