<?php
/**
 * 견적 및 제작관련 문의
 * 1:1 문의 게시판
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// customer_inquiries 테이블이 없으면 생성
$table_check = mysqli_query($db, "SHOW TABLES LIKE 'customer_inquiries'");
if (mysqli_num_rows($table_check) == 0) {
    $create_table_sql = "CREATE TABLE IF NOT EXISTS customer_inquiries (
        inquiry_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        inquiry_name VARCHAR(100) NOT NULL,
        inquiry_email VARCHAR(255) NOT NULL,
        inquiry_phone VARCHAR(20),
        inquiry_category VARCHAR(50) DEFAULT 'general',
        inquiry_subject VARCHAR(255) NOT NULL,
        inquiry_message TEXT NOT NULL,
        is_private TINYINT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'pending',
        admin_reply TEXT,
        admin_reply_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    mysqli_query($db, $create_table_sql);
}

// 로그인 여부 확인
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? '') : '';
$user_email = $is_logged_in ? ($_SESSION['email'] ?? $_SESSION['user_email'] ?? '') : '';

// 문의 제출 처리
$submit_success = false;
$submit_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $category = $_POST['category'] ?? 'general';
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $is_private = isset($_POST['is_private']) ? 1 : 0;

    // 유효성 검증
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $submit_error = '필수 항목을 모두 입력해주세요.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $submit_error = '올바른 이메일 주소를 입력해주세요.';
    } else {
        // 문의 저장
        $sql = "INSERT INTO customer_inquiries
                (user_id, inquiry_name, inquiry_email, inquiry_phone, inquiry_category,
                 inquiry_subject, inquiry_message, is_private, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

        $stmt = mysqli_prepare($db, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issssssi",
                $user_id, $name, $email, $phone, $category, $subject, $message, $is_private);

            if (mysqli_stmt_execute($stmt)) {
                $submit_success = true;

                // 관리자에게 메일 알림 발송
                $categoryLabelsForMail = [
                    'quote' => '견적문의', 'production' => '제작관련',
                    'file' => '파일관련', 'delivery' => '배송관련',
                    'payment' => '결제관련', 'general' => '기타문의'
                ];
                $cat_label = $categoryLabelsForMail[$category] ?? $category;

                $phpmailerPath = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/PHPMailer/';
                if (file_exists($phpmailerPath . 'PHPMailer.php')) {
                    @require_once $phpmailerPath . 'PHPMailer.php';
                    @require_once $phpmailerPath . 'SMTP.php';
                    @require_once $phpmailerPath . 'Exception.php';

                    try {
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        $mail->isSMTP();
                        $mail->SMTPDebug = 0;
                        $mail->Host = 'smtp.naver.com';
                        $mail->Port = 465;
                        $mail->SMTPSecure = 'ssl';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'dsp1830';
                        $mail->Password = 'VC9FU2HG5J8D';
                        $mail->CharSet = 'UTF-8';

                        $mail->setFrom('dsp1830@naver.com', '두손기획인쇄');
                        $mail->addAddress('dsp1830@naver.com');
                        $mail->addReplyTo($email, $name);

                        $mail->isHTML(false);
                        $mail->Subject = "[고객문의] [{$cat_label}] {$subject}";
                        $mail->Body = "고객센터 새 문의가 접수되었습니다.\n\n"
                            . "────────────────────────\n"
                            . "이름: {$name}\n"
                            . "이메일: {$email}\n"
                            . "연락처: " . ($phone ?: '미입력') . "\n"
                            . "유형: {$cat_label}\n"
                            . "────────────────────────\n\n"
                            . "제목: {$subject}\n\n"
                            . "내용:\n{$message}\n\n"
                            . "────────────────────────\n"
                            . "접수시간: " . date('Y-m-d H:i:s') . "\n";

                        $mail->send();
                    } catch (Exception $e) {
                        error_log("문의 메일 발송 실패: " . $e->getMessage());
                    }
                }

                // 폼 초기화
                $_POST = [];
            } else {
                $submit_error = '문의 등록 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
            }

            mysqli_stmt_close($stmt);
        } else {
            $submit_error = '데이터베이스 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
        }
    }
}

// 내 문의 내역 조회 (로그인 시)
$my_inquiries = [];
if ($is_logged_in && $user_id) {
    $sql = "SELECT inquiry_id, inquiry_subject, inquiry_category, status, created_at, admin_reply_at
            FROM customer_inquiries
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10";

    $stmt = mysqli_prepare($db, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $my_inquiries = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }

        mysqli_stmt_close($stmt);
    }
}

// 공통 헤더 포함
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적 및 제작관련 문의 - 두손기획인쇄 고객센터</title>

    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="/css/customer-center.css">
    <style>
        /* 콘텐츠 영역 폭 제한 */
        .customer-content {
            max-width: 900px;
        }
        .inquiry-form-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .form-label .required {
            color: #f44336;
            margin-left: 4px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #2196F3;
        }

        .form-textarea {
            min-height: 200px;
            resize: vertical;
            font-family: inherit;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .form-checkbox input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: #2196F3;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .submit-btn:hover {
            background: #1976D2;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }

        .my-inquiries-section {
            margin-top: 50px;
        }

        .inquiry-history {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .inquiry-history-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 12px;
            background: #fff;
        }

        .inquiry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .inquiry-subject {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-badge.pending {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-badge.answered {
            background: #e8f5e9;
            color: #388e3c;
        }

        .inquiry-meta {
            font-size: 14px;
            color: #666;
        }

        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            background: #e3f2fd;
            color: #2196F3;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 10px;
        }

        .contact-info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .contact-info-box h3 {
            margin: 0 0 15px 0;
            color: #1976d2;
        }

        .contact-info-box p {
            margin: 8px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="customer-center-container">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/customer_sidebar.php'; ?>

        <main class="customer-content">
            <div class="breadcrumb">
                <a href="/">홈</a> &gt; <a href="/sub/customer/">고객센터</a> &gt; <span>견적 및 제작관련 문의</span>
            </div>

            <div class="content-header">
                <h1>💬 견적 및 제작관련 문의</h1>
                <p class="subtitle">1:1 문의를 남겨주시면 빠르게 답변드리겠습니다</p>
            </div>

            <div class="content-body">
                <!-- 성공/에러 메시지 -->
                <?php if ($submit_success): ?>
                    <div class="alert alert-success">
                        ✅ 문의가 성공적으로 등록되었습니다. 빠른 시일 내에 답변드리겠습니다.
                    </div>
                <?php endif; ?>

                <?php if (!empty($submit_error)): ?>
                    <div class="alert alert-error">
                        ❌ <?php echo htmlspecialchars($submit_error); ?>
                    </div>
                <?php endif; ?>

                <!-- 문의 작성 폼 -->
                <section class="inquiry-form-section">
                    <h2 class="section-title">문의 작성</h2>

                    <form method="post" action="" id="inquiryForm">
                        <div class="form-group">
                            <label class="form-label">
                                이름<span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                class="form-input"
                                value="<?php echo htmlspecialchars($user_name); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                이메일<span class="required">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                class="form-input"
                                value="<?php echo htmlspecialchars($user_email); ?>"
                                placeholder="답변받으실 이메일 주소"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">연락처</label>
                            <input
                                type="tel"
                                name="phone"
                                class="form-input"
                                placeholder="010-1234-5678"
                                onkeyup="formatPhoneNumber(this)"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                문의 유형<span class="required">*</span>
                            </label>
                            <select name="category" class="form-select" required>
                                <option value="quote">견적 문의</option>
                                <option value="production">제작 관련</option>
                                <option value="file">파일 관련</option>
                                <option value="delivery">배송 관련</option>
                                <option value="payment">결제 관련</option>
                                <option value="general">기타 문의</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                제목<span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                name="subject"
                                class="form-input"
                                placeholder="문의 제목을 입력하세요"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                문의 내용<span class="required">*</span>
                            </label>
                            <textarea
                                name="message"
                                class="form-textarea"
                                placeholder="문의하실 내용을 상세히 작성해주세요.&#10;&#10;- 제작하실 인쇄물 종류&#10;- 수량, 사이즈, 용지 등&#10;- 원하시는 납기일&#10;- 기타 요청사항"
                                required
                            ></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="is_private" value="1">
                                <span>비밀글로 작성 (본인과 관리자만 확인 가능)</span>
                            </label>
                        </div>

                        <button type="submit" name="submit_inquiry" class="submit-btn">
                            📩 문의하기
                        </button>
                    </form>
                </section>

                <!-- 빠른 연락처 -->
                <div class="contact-info-box">
                    <h3>📞 빠른 상담이 필요하신가요?</h3>
                    <p><strong>전화:</strong> 1688-2384 / 02-2632-1830 (평일 09:00~18:00)</p>
                    <p><strong>카카오톡:</strong> @두손기획인쇄</p>
                    <p><strong>이메일:</strong> dsp114@naver.com</p>
                </div>

                <!-- 내 문의 내역 (로그인 시만) -->
                <?php if ($is_logged_in && count($my_inquiries) > 0): ?>
                    <section class="my-inquiries-section">
                        <h2 class="section-title">내 문의 내역</h2>

                        <ul class="inquiry-history">
                            <?php foreach ($my_inquiries as $inquiry): ?>
                                <li class="inquiry-history-item">
                                    <div class="inquiry-header">
                                        <h3 class="inquiry-subject">
                                            <?php echo htmlspecialchars($inquiry['inquiry_subject']); ?>
                                        </h3>
                                        <span class="status-badge <?php echo $inquiry['status']; ?>">
                                            <?php
                                            $statusLabels = [
                                                'pending' => '답변대기',
                                                'answered' => '답변완료'
                                            ];
                                            echo $statusLabels[$inquiry['status']] ?? $inquiry['status'];
                                            ?>
                                        </span>
                                    </div>
                                    <div class="inquiry-meta">
                                        <span class="category-badge">
                                            <?php
                                            $categoryLabels = [
                                                'quote' => '견적문의',
                                                'production' => '제작관련',
                                                'file' => '파일관련',
                                                'delivery' => '배송관련',
                                                'payment' => '결제관련',
                                                'general' => '기타문의'
                                            ];
                                            echo $categoryLabels[$inquiry['inquiry_category']] ?? $inquiry['inquiry_category'];
                                            ?>
                                        </span>
                                        <span>작성일: <?php echo date('Y.m.d H:i', strtotime($inquiry['created_at'])); ?></span>
                                        <?php if ($inquiry['admin_reply_at']): ?>
                                            <span> | 답변일: <?php echo date('Y.m.d H:i', strtotime($inquiry['admin_reply_at'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <!-- 관련 링크 -->
                <div class="related-links">
                    <h3>더 궁금하신 사항이 있으신가요?</h3>
                    <div class="link-buttons">
                        <a href="/sub/customer/faq.php" class="btn-secondary">자주하는 질문</a>
                        <a href="/sub/customer/how_to_use.php" class="btn-secondary">이용방법</a>
                        <a href="tel:1688-2384 / 02-2632-1830" class="btn-primary">📞 전화상담</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/customer-center.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
