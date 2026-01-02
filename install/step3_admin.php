<?php
/**
 * Step 3: 관리자 계정 생성
 */

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = trim($_POST['admin_id'] ?? '');
    $admin_pass = $_POST['admin_pass'] ?? '';
    $admin_pass_confirm = $_POST['admin_pass_confirm'] ?? '';
    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');

    // 유효성 검사
    if (strlen($admin_id) < 4) {
        $error = '관리자 ID는 4자 이상이어야 합니다.';
    } elseif (strlen($admin_pass) < 6) {
        $error = '비밀번호는 6자 이상이어야 합니다.';
    } elseif ($admin_pass !== $admin_pass_confirm) {
        $error = '비밀번호가 일치하지 않습니다.';
    } elseif (empty($admin_name)) {
        $error = '관리자 이름을 입력하세요.';
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error = '올바른 이메일 주소를 입력하세요.';
    } else {
        // DB 연결
        $conn = mysqli_connect(
            $_SESSION['db_host'],
            $_SESSION['db_user'],
            $_SESSION['db_pass'],
            $_SESSION['db_name']
        );

        if ($conn) {
            mysqli_set_charset($conn, 'utf8mb4');

            // 비밀번호 해시
            $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);

            // 관리자 계정 생성
            $stmt = mysqli_prepare($conn, "INSERT INTO admin_users (admin_id, admin_pass, admin_name, admin_email, admin_level, created_at) VALUES (?, ?, ?, ?, 9, NOW())");
            mysqli_stmt_bind_param($stmt, 'ssss', $admin_id, $hashed_pass, $admin_name, $admin_email);

            if (mysqli_stmt_execute($stmt)) {
                // 세션에 관리자 정보 저장
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_name'] = $admin_name;
                $_SESSION['admin_email'] = $admin_email;
                $_SESSION['admin_created'] = true;

                header('Location: ?step=4');
                exit;
            } else {
                $error = '관리자 계정 생성 실패: ' . mysqli_error($conn);
            }

            mysqli_close($conn);
        } else {
            $error = '데이터베이스 연결 실패';
        }
    }
}

$admin_id = $_SESSION['admin_id'] ?? '';
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';
?>

<h2 class="step-title">Step 3: 관리자 계정 생성</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <div class="form-row">
        <div class="form-group">
            <label>관리자 ID *</label>
            <input type="text" name="admin_id" value="<?php echo htmlspecialchars($admin_id); ?>" required minlength="4">
            <small>4자 이상, 영문/숫자만 사용</small>
        </div>

        <div class="form-group">
            <label>관리자 이름 *</label>
            <input type="text" name="admin_name" value="<?php echo htmlspecialchars($admin_name); ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label>관리자 이메일 *</label>
        <input type="email" name="admin_email" value="<?php echo htmlspecialchars($admin_email); ?>" required>
        <small>주문 알림 등이 이 이메일로 발송됩니다.</small>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>비밀번호 *</label>
            <input type="password" name="admin_pass" required minlength="6">
            <small>6자 이상</small>
        </div>

        <div class="form-group">
            <label>비밀번호 확인 *</label>
            <input type="password" name="admin_pass_confirm" required minlength="6">
        </div>
    </div>

    <div class="btn-group">
        <a href="?step=2" class="btn btn-secondary">← 이전</a>
        <button type="submit" class="btn btn-primary">다음 단계 →</button>
    </div>
</form>
