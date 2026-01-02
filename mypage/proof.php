<?php
require_once __DIR__ . '/auth_required.php';
require_once __DIR__ . '/../admin/mlangprintauto/includes/ProofWorkflow.php';
require_once __DIR__ . '/../includes/EmailNotification.php';

$user_id = $current_user['id'];
$message = '';
$error = '';

$proofWorkflow = new ProofWorkflow($db);

// Handle revision upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_revision'])) {
    $proof_id = isset($_POST['proof_id']) ? (int)$_POST['proof_id'] : 0;
    
    if ($proof_id > 0 && !empty($_FILES['revision_files']['name'][0])) {
        require_once __DIR__ . '/../includes/StandardUploadHandler.php';
        
        try {
            $upload_result = StandardUploadHandler::processUpload('proof_revision', $_FILES['revision_files']);

            if ($proofWorkflow->uploadRevision($proof_id, $upload_result['files'])) {
                $message = '수정본이 성공적으로 업로드되었습니다. 관리자가 확인 후 연락드립니다.';

                // 관리자에게 알림 이메일 전송
                try {
                    $proof_query = "
                        SELECT ps.order_no, ps.product_type
                        FROM proof_status ps
                        WHERE ps.id = ?
                    ";
                    $stmt = mysqli_prepare($db, $proof_query);
                    mysqli_stmt_bind_param($stmt, "i", $proof_id);
                    mysqli_stmt_execute($stmt);
                    $proof_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    mysqli_stmt_close($stmt);

                    if ($proof_data) {
                        $emailNotification = new EmailNotification();
                        $emailNotification->sendRevisionSubmittedNotification(
                            $current_user['name'],
                            $proof_data['order_no'],
                            $proof_data['product_type']
                        );
                    }
                } catch (Exception $e) {
                    error_log("수정본 제출 알림 이메일 전송 실패: " . $e->getMessage());
                }
            } else {
                $error = '수정본 업로드 중 오류가 발생했습니다.';
            }
        } catch (Exception $e) {
            $error = '파일 업로드 실패: ' . $e->getMessage();
        }
    } else {
        $error = '파일을 선택해주세요.';
    }
}

// Get user's orders with proof status
$query = "
    SELECT 
        o.no,
        o.Type as product_type,
        o.name,
        o.date,
        o.money_2,
        ps.id as proof_id,
        ps.status as proof_status,
        ps.admin_comment,
        ps.revision_files,
        ps.reviewed_at,
        ps.created_at as proof_created_at
    FROM mlangorder_printauto o
    LEFT JOIN proof_status ps ON o.no = ps.order_no
    WHERE o.user_id = ?
    ORDER BY o.date DESC
    LIMIT 50
";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>교정 확인 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/mlangprintauto/css/common-styles.css">
    <style>
        body { background: #f5f5f5; padding: 20px; font-family: 'Malgun Gothic', sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 { color: #333; margin-bottom: 10px; }
        .header p { color: #666; margin-top: 5px; }
        
        .nav-link { margin: 20px 0; }
        .nav-link a { color: #667eea; text-decoration: none; }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .proof-list { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        .proof-item {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
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
        .status-none { background: #e9ecef; color: #495057; }
        
        .proof-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .proof-details p {
            margin: 8px 0;
            color: #666;
            font-size: 14px;
        }
        
        .proof-details strong { color: #333; }
        
        .admin-comment {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }
        
        .admin-comment h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .admin-comment p {
            color: #333;
            line-height: 1.6;
        }
        
        .upload-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-top: 15px;
        }
        
        .upload-form h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .file-input {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        .upload-btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        
        .upload-btn:hover { background: #5568d3; }
        
        .no-proof {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .revision-files {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        
        .revision-files h4 {
            color: #0c5460;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .file-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .file-item {
            padding: 8px 12px;
            background: white;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            font-size: 13px;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 교정 확인</h1>
            <p>주문하신 파일의 교정 상태를 확인하고 수정본을 업로드할 수 있습니다</p>
        </div>
        
        <div class="nav-link">
            <a href="index.php">← 마이페이지로 돌아가기</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="proof-list">
            <?php if (mysqli_num_rows($orders) === 0): ?>
                <div class="no-proof">
                    <p>주문 내역이 없습니다.</p>
                </div>
            <?php else: ?>
                <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <div class="proof-item">
                        <div class="proof-header">
                            <div class="proof-info">
                                <h3>주문번호: <?php echo htmlspecialchars($order['no']); ?></h3>
                                <p>
                                    제품: <?php echo htmlspecialchars($order['product_type']); ?> | 
                                    주문일: <?php echo date('Y-m-d', strtotime($order['date'])); ?> | 
                                    금액: ₩<?php echo number_format($order['money_2']); ?>
                                </p>
                            </div>
                            <span class="status-badge status-<?php echo $order['proof_status'] ?? 'none'; ?>">
                                <?php
                                $status_text = [
                                    'pending' => '교정 대기중',
                                    'approved' => '승인 완료',
                                    'revision_requested' => '수정 요청',
                                    'revised' => '수정본 제출됨'
                                ];
                                echo $status_text[$order['proof_status']] ?? '교정 미요청';
                                ?>
                            </span>
                        </div>
                        
                        <?php if ($order['proof_status']): ?>
                            <div class="proof-details">
                                <p>
                                    <strong>교정 요청일:</strong> 
                                    <?php echo date('Y-m-d H:i', strtotime($order['proof_created_at'])); ?>
                                </p>
                                <?php if ($order['reviewed_at']): ?>
                                    <p>
                                        <strong>검토일:</strong> 
                                        <?php echo date('Y-m-d H:i', strtotime($order['reviewed_at'])); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($order['proof_status'] === 'approved'): ?>
                                    <p style="color: #28a745; font-weight: 600;">
                                        ✓ 교정이 승인되었습니다. 곧 생산에 들어갑니다.
                                    </p>
                                <?php elseif ($order['proof_status'] === 'pending'): ?>
                                    <p style="color: #ffc107; font-weight: 600;">
                                        ⏳ 관리자가 교정을 확인하고 있습니다.
                                    </p>
                                <?php elseif ($order['proof_status'] === 'revised'): ?>
                                    <p style="color: #17a2b8; font-weight: 600;">
                                        📤 수정본이 제출되었습니다. 관리자 확인 대기 중입니다.
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($order['admin_comment']): ?>
                                <div class="admin-comment">
                                    <h4>⚠️ 관리자 요청사항</h4>
                                    <p><?php echo nl2br(htmlspecialchars($order['admin_comment'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['revision_files']): ?>
                                <div class="revision-files">
                                    <h4>📎 제출한 수정본:</h4>
                                    <div class="file-list">
                                        <?php
                                        $revision_files = json_decode($order['revision_files'], true);
                                        if (!empty($revision_files)) {
                                            foreach ($revision_files as $file) {
                                                echo '<div class="file-item">📄 ' . htmlspecialchars($file['original_name'] ?? 'file') . '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['proof_status'] === 'revision_requested'): ?>
                                <div class="upload-form">
                                    <h4>📤 수정본 업로드</h4>
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="proof_id" value="<?php echo $order['proof_id']; ?>">
                                        <input 
                                            type="file" 
                                            name="revision_files[]" 
                                            multiple 
                                            accept=".jpg,.jpeg,.png,.pdf,.ai,.psd"
                                            class="file-input"
                                            required
                                        >
                                        <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                                            허용 파일: JPG, PNG, PDF, AI, PSD (최대 15MB)
                                        </p>
                                        <button type="submit" name="upload_revision" class="upload-btn">
                                            수정본 업로드
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="proof-details">
                                <p style="color: #999;">
                                    아직 교정 요청이 생성되지 않았습니다.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
