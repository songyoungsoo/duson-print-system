<?php
/**
 * 회원탈퇴
 * 경로: /mypage/withdraw.php
 */

// 세션 및 인증 처리 (8시간 유지, 자동 로그인 30일)
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('세션이 만료되었습니다. 다시 로그인해주세요.'); location.href='/member/login.php';</script>";
    exit;
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 회원탈퇴 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['withdraw'])) {
    $password = $_POST['password'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $confirm = isset($_POST['confirm']);

    if (!$password) {
        $error = "비밀번호를 입력해주세요.";
    } elseif (!$confirm) {
        $error = "회원탈퇴 동의에 체크해주세요.";
    } else {
        // 비밀번호 확인
        $query = "SELECT password, email FROM users WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // 비밀번호 검증 (bcrypt 해시 또는 평문 모두 지원)
        $stored_password = $user['password'];
        $password_valid = false;

        // bcrypt 해시인 경우 ($2y$로 시작하고 60자)
        if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
            $password_valid = password_verify($password, $stored_password);
        } else {
            // 평문 비밀번호인 경우 직접 비교
            $password_valid = ($password === $stored_password);
        }

        if (!$password_valid) {
            $error = "비밀번호가 일치하지 않습니다.";
        } else {
            // 회원 탈퇴 처리 (소프트 삭제)
            $anonymized_email = 'deleted_' . $user_id . '@deleted.com';
            $anonymized_name = '탈퇴회원';

            // 주문 정보는 유지하되 개인정보는 익명화
            $update_query = "UPDATE users SET
                            status = 'deleted',
                            email = ?,
                            name = ?,
                            phone = NULL,
                            zipcode = NULL,
                            address = NULL,
                            detail_address = NULL,
                            business_number = NULL,
                            business_name = NULL,
                            business_cert_path = NULL,
                            deleted_at = NOW(),
                            deleted_reason = ?
                            WHERE id = ?";

            $stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($stmt, "sssi", $anonymized_email, $anonymized_name, $reason, $user_id);

            if (mysqli_stmt_execute($stmt)) {
                // 사업자등록증 파일 삭제
                if ($user['business_cert_path']) {
                    $cert_file = $_SERVER['DOCUMENT_ROOT'] . $user['business_cert_path'];
                    if (file_exists($cert_file)) {
                        unlink($cert_file);
                    }
                }

                mysqli_stmt_close($stmt);

                // 세션 종료
                session_destroy();

                echo "<script>
                    alert('회원탈퇴가 완료되었습니다. 그동안 이용해 주셔서 감사합니다.');
                    location.href='/';
                </script>";
                exit;
            } else {
                $error = "회원탈퇴 처리 중 오류가 발생했습니다.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// 사용자 주문 통계
// 먼저 사용자 이메일 조회
$email_query = "SELECT email FROM users WHERE id = ?";
$email_stmt = mysqli_prepare($db, $email_query);
mysqli_stmt_bind_param($email_stmt, "i", $user_id);
mysqli_stmt_execute($email_stmt);
$email_result = mysqli_stmt_get_result($email_stmt);
$user_email_data = mysqli_fetch_assoc($email_result);
mysqli_stmt_close($email_stmt);

$stats = ['order_count' => 0, 'total_amount' => 0];
if ($user_email_data && $user_email_data['email']) {
    $user_email = $user_email_data['email'];

    $stats_query = "SELECT COUNT(*) as order_count, SUM(CAST(money_5 AS UNSIGNED)) as total_amount
                    FROM mlangorder_printauto
                    WHERE email = ?";
    $stmt = mysqli_prepare($db, $stats_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user_email);
        mysqli_stmt_execute($stmt);
        $stats_result = mysqli_stmt_get_result($stmt);
        $stats = mysqli_fetch_assoc($stats_result);
        mysqli_stmt_close($stmt);
    }
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원탈퇴 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/common-styles.css">
    <style>
        body {
            background: #f5f5f5;
            font-size: 13px;
        }

        .mypage-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
        }

        .mypage-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 900px;
        }

        .page-title {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #ffffff;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .warning-box h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #856404;
        }

        .warning-list {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }

        .warning-list li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .stats-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .stats-box h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #333;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            font-weight: 600;
            color: #666;
        }

        .stat-value {
            color: #333;
            font-weight: 600;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #1466BA;
        }

        .checkbox-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border: 2px solid #dc3545;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 14px;
            color: #333;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 6px;
        }

        @media (max-width: 768px) {
            .mypage-container {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="mypage-container">
        <!-- 사이드바 -->
        <?php include 'sidebar.php'; ?>

        <!-- 메인 컨텐츠 -->
        <div class="mypage-content">
            <h1 class="page-title">회원탈퇴</h1>

            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- 경고 메시지 -->
            <div class="warning-box">
                <h3>⚠️ 회원탈퇴 전 꼭 확인하세요</h3>
                <ul class="warning-list">
                    <li>탈퇴 후 회원정보는 복구할 수 없습니다.</li>
                    <li>주문 내역은 상법 및 전자상거래법에 따라 5년간 보관됩니다.</li>
                    <li>개인정보(이름, 연락처, 주소)는 즉시 삭제됩니다.</li>
                    <li>탈퇴 후 동일 이메일로 재가입이 가능하지만 기존 정보는 복구되지 않습니다.</li>
                    <li>미처리된 주문이 있는 경우 고객센터로 문의해주세요.</li>
                </ul>
            </div>

            <!-- 회원 통계 -->
            <div class="stats-box">
                <h3>회원 이용 현황</h3>
                <div class="stat-item">
                    <span class="stat-label">총 주문 건수</span>
                    <span class="stat-value"><?php echo number_format($stats['order_count'] ?? 0); ?>건</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">총 주문 금액</span>
                    <span class="stat-value"><?php echo number_format($stats['total_amount'] ?? 0); ?>원</span>
                </div>
            </div>

            <!-- 탈퇴 신청 폼 -->
            <form method="post" onsubmit="return confirmWithdraw();">
                <div class="form-section">
                    <div class="form-group">
                        <label class="form-label">비밀번호 확인 *</label>
                        <input type="password" name="password" class="form-input" placeholder="현재 비밀번호를 입력하세요" required>
                        <div class="help-text">본인 확인을 위해 비밀번호를 입력해주세요.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">탈퇴 사유</label>
                        <select name="reason" class="form-select">
                            <option value="">선택하세요 (선택사항)</option>
                            <option value="서비스 불만족">서비스가 만족스럽지 않아서</option>
                            <option value="이용 빈도 낮음">이용 빈도가 낮아서</option>
                            <option value="타 업체 이용">다른 업체를 이용하게 되어서</option>
                            <option value="개인정보 우려">개인정보 보호가 우려되어서</option>
                            <option value="재가입 예정">잠시 쉬었다가 재가입 예정</option>
                            <option value="기타">기타</option>
                        </select>
                        <div class="help-text">탈퇴 사유는 서비스 개선에 활용됩니다.</div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="confirm" required>
                                <span>위 내용을 모두 확인했으며, 회원탈퇴에 동의합니다.</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="withdraw" class="btn btn-danger">회원탈퇴</button>
                    <a href="/mypage/index.php" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmWithdraw() {
            return confirm('정말로 회원탈퇴를 진행하시겠습니까?\n\n탈퇴 후에는 계정 정보를 복구할 수 없습니다.');
        }
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
<?php
mysqli_close($db);
?>
