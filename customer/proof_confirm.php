<?php
/**
 * 고객 교정본 확인 페이지
 * 두손기획인쇄 - 고객용 교정본 승인/거부 시스템
 */

// DB 연결
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/ProofreadingManager.php';
require_once __DIR__ . '/../includes/OrderStatusManager.php';

// 파라미터 받기
$order_no = intval($_GET['order'] ?? 0);
$proof_id = intval($_GET['proof'] ?? 0);
$token = $_GET['token'] ?? '';
$action = $_POST['action'] ?? '';

// 보안: 토큰 검증 (간단한 버전 - 실제로는 DB에 토큰 저장 권장)
function validateToken($order_no, $token) {
    // 간단한 토큰 검증 (실제로는 DB에서 확인해야 함)
    // 지금은 주문번호가 있으면 통과
    return $order_no > 0;
}

if (!$order_no || !validateToken($order_no, $token)) {
    die("
    <!DOCTYPE html>
    <html lang='ko'>
    <head>
        <meta charset='UTF-8'>
        <title>오류 - 두손기획인쇄</title>
        <style>
            body { font-family: 'Malgun Gothic', sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
            .error-box { background: white; padding: 40px; border-radius: 8px; max-width: 500px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #e74c3c; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>⚠️ 접근 오류</h1>
            <p>유효하지 않은 링크입니다.</p>
            <p>이메일에서 받으신 링크를 다시 확인해주세요.</p>
        </div>
    </body>
    </html>
    ");
}

// 주문 정보 조회
$stmt = mysqli_prepare($db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    die("주문 정보를 찾을 수 없습니다.");
}

// 교정본 관리자
$proofManager = new ProofreadingManager($db, $order_no);

// 교정본 정보 조회
if ($proof_id > 0) {
    // 특정 교정본 조회
    $stmt = mysqli_prepare($db, "SELECT * FROM order_proofreading WHERE id = ? AND order_no = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $proof_id, $order_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_proof = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    // 최신 교정본 조회
    $current_proof = $proofManager->getCurrentProof();
    $proof_id = $current_proof['id'] ?? 0;
}

// 교정본 승인/거부 처리
if ($action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = trim($_POST['feedback'] ?? '');

    if ($action === 'approve') {
        $success = $proofManager->customerConfirm($proof_id, 'approved', '고객 승인');

        if ($success) {
            header('Location: proof_thank_you.php?order=' . $order_no . '&token=' . $token . '&action=approved');
            exit;
        }
    } elseif ($action === 'reject') {
        if (empty($feedback)) {
            $error_message = '거부 사유를 입력해주세요.';
        } else {
            $success = $proofManager->customerConfirm($proof_id, 'rejected', $feedback);

            if ($success) {
                header('Location: proof_thank_you.php?order=' . $order_no . '&token=' . $token . '&action=rejected');
                exit;
            }
        }
    }
}

// 완료 메시지 표시
$status = $_GET['status'] ?? '';
if ($status) {
    $statusManager = new OrderStatusManager($db, $order_no);
    $current_status = $statusManager->getCurrentStatus();
}

// 모든 교정본 이력
$all_proofs = $proofManager->getAllProofs();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>교정본 확인 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
        }
        .content {
            padding: 40px;
        }
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        .success-message h2 {
            margin-bottom: 10px;
        }
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            color: #212529;
            font-weight: 500;
        }
        .proof-viewer {
            margin-bottom: 30px;
        }
        .proof-viewer h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .proof-image-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .proof-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .proof-pdf-link {
            display: inline-block;
            padding: 15px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .proof-pdf-link:hover {
            background: #2980b9;
        }
        .action-section {
            margin-top: 30px;
        }
        .action-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .btn {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-approve {
            background: #27ae60;
            color: white;
        }
        .btn-approve:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }
        .btn-reject {
            background: #e74c3c;
            color: white;
        }
        .btn-reject:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }
        .feedback-form {
            display: none;
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ffeeba;
        }
        .feedback-form.active {
            display: block;
        }
        .feedback-form textarea {
            width: 100%;
            min-height: 120px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }
        .feedback-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .btn-submit {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-cancel {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .proof-history {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #ecf0f1;
        }
        .proof-history h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .history-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
        }
        .history-version {
            font-weight: bold;
            color: #3498db;
        }
        .history-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 교정본 확인</h1>
            <p>두손기획인쇄 - 주문번호 #<?php echo $order_no; ?></p>
        </div>

        <div class="content">
            <?php if ($status === 'approved'): ?>
                <div class="success-message">
                    <h2>✅ 교정본이 승인되었습니다!</h2>
                    <p>감사합니다. 곧 제작을 시작하여 빠르게 발송해드리겠습니다.</p>
                    <p style="margin-top: 15px; font-size: 14px; opacity: 0.8;">
                        현재 주문 상태: <strong><?php echo $current_status === 'proof_approved' ? '교정본 승인됨' : '제작 진행 중'; ?></strong>
                    </p>
                </div>
            <?php elseif ($status === 'rejected'): ?>
                <div class="success-message" style="background: #fff3cd; border-color: #ffeeba; color: #856404;">
                    <h2>📝 수정 요청이 접수되었습니다</h2>
                    <p>고객님의 요청사항을 반영하여 수정 후 다시 교정본을 보내드리겠습니다.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- 주문 정보 -->
            <div class="order-info">
                <h2 style="margin-bottom: 15px; color: #2c3e50;">주문 정보</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">주문번호</div>
                        <div class="info-value"><?php echo $order_no; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">주문일시</div>
                        <div class="info-value"><?php echo substr($order['date'], 0, 16); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">제품</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['Type']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">고객명</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['name']); ?></div>
                    </div>
                </div>
            </div>

            <?php if ($current_proof && $current_proof['customer_confirmed'] === 'pending'): ?>
                <!-- 교정본 보기 -->
                <div class="proof-viewer">
                    <h2>교정본 (버전 <?php echo $current_proof['proof_version']; ?>)</h2>
                    <div class="proof-image-container">
                        <?php
                        $file_path = $current_proof['proof_file_path'];
                        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])):
                        ?>
                            <img src="/<?php echo htmlspecialchars($file_path); ?>" alt="교정본" class="proof-image">
                        <?php else: ?>
                            <p style="margin-bottom: 20px;">PDF 파일을 확인하려면 아래 버튼을 클릭하세요.</p>
                            <a href="/<?php echo htmlspecialchars($file_path); ?>" target="_blank" class="proof-pdf-link">
                                📄 교정본 PDF 열기
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 승인/거부 액션 -->
                <div class="action-section">
                    <h2>교정본을 확인하셨나요?</h2>
                    <p style="color: #6c757d; margin-bottom: 20px;">
                        교정본을 승인하시면 바로 제작에 들어갑니다. 수정이 필요하시면 거부 후 수정사항을 알려주세요.
                    </p>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-approve" onclick="approveProof()">
                            ✅ 승인합니다 (제작 진행)
                        </button>
                        <button type="button" class="btn btn-reject" onclick="showRejectForm()">
                            ❌ 수정이 필요합니다
                        </button>
                    </div>

                    <!-- 승인 폼 (숨김) -->
                    <form method="POST" id="approveForm" style="display:none;">
                        <input type="hidden" name="action" value="approve">
                    </form>

                    <!-- 거부 폼 (숨김) -->
                    <div class="feedback-form" id="rejectForm">
                        <h3 style="margin-bottom: 15px;">수정 요청사항을 입력해주세요</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="reject">
                            <textarea name="feedback" placeholder="예: 로고 크기를 조금 더 크게 해주세요.&#10;글씨 색상을 파란색으로 변경해주세요." required></textarea>
                            <div class="feedback-buttons">
                                <button type="submit" class="btn-submit">수정 요청하기</button>
                                <button type="button" class="btn-cancel" onclick="hideRejectForm()">취소</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 교정본 이력 -->
            <?php if (count($all_proofs) > 1): ?>
                <div class="proof-history">
                    <h3>교정본 이력</h3>
                    <?php foreach ($all_proofs as $proof): ?>
                        <div class="history-item">
                            <span class="history-version">버전 <?php echo $proof['proof_version']; ?></span>
                            <span class="history-status status-<?php echo $proof['customer_confirmed']; ?>">
                                <?php
                                $status_labels = [
                                    'pending' => '확인 대기',
                                    'approved' => '승인됨',
                                    'rejected' => '수정 요청'
                                ];
                                echo $status_labels[$proof['customer_confirmed']] ?? '알 수 없음';
                                ?>
                            </span>
                            <div style="margin-top: 5px; font-size: 14px; color: #6c757d;">
                                <?php echo $proof['proof_uploaded_at']; ?>
                            </div>
                            <?php if ($proof['customer_feedback']): ?>
                                <div style="margin-top: 10px; font-size: 14px;">
                                    <strong>피드백:</strong> <?php echo htmlspecialchars($proof['customer_feedback']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 문의 안내 -->
            <div style="margin-top: 40px; padding: 20px; background: #e3f2fd; border-radius: 8px; text-align: center;">
                <p style="margin-bottom: 10px;"><strong>문의사항이 있으신가요?</strong></p>
                <p style="color: #1976d2; font-size: 18px; font-weight: bold;">📞 02-2632-1830</p>
                <p style="margin-top: 5px; color: #6c757d; font-size: 14px;">평일 09:00 ~ 18:00</p>
            </div>
        </div>
    </div>

    <script>
        function approveProof() {
            if (confirm('교정본을 승인하시겠습니까?\n\n승인하시면 바로 제작에 들어갑니다.')) {
                document.getElementById('approveForm').submit();
            }
        }

        function showRejectForm() {
            document.getElementById('rejectForm').classList.add('active');
            document.querySelector('.action-buttons').style.display = 'none';
        }

        function hideRejectForm() {
            document.getElementById('rejectForm').classList.remove('active');
            document.querySelector('.action-buttons').style.display = 'flex';
        }
    </script>
</body>
</html>
