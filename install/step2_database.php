<?php
/**
 * Step 2: 데이터베이스 설정
 */

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';

    // 연결 테스트
    $conn = @mysqli_connect($db_host, $db_user, $db_pass);

    if (!$conn) {
        $error = '데이터베이스 연결 실패: ' . mysqli_connect_error();
    } else {
        // 데이터베이스 선택 또는 생성
        if (!mysqli_select_db($conn, $db_name)) {
            // 데이터베이스 생성 시도
            if (mysqli_query($conn, "CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                mysqli_select_db($conn, $db_name);
                $success = "데이터베이스 '$db_name'가 생성되었습니다.";
            } else {
                $error = "데이터베이스 생성 실패: " . mysqli_error($conn);
            }
        }

        if (!$error) {
            // 세션에 DB 정보 저장
            $_SESSION['db_host'] = $db_host;
            $_SESSION['db_name'] = $db_name;
            $_SESSION['db_user'] = $db_user;
            $_SESSION['db_pass'] = $db_pass;

            // 테이블 생성 (SQL 파일 실행)
            $sql_file = __DIR__ . '/sql/schema.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                mysqli_multi_query($conn, $sql);

                // 모든 쿼리 결과 처리
                do {
                    if ($result = mysqli_store_result($conn)) {
                        mysqli_free_result($result);
                    }
                } while (mysqli_next_result($conn));

                $success .= " 테이블이 성공적으로 생성되었습니다.";
                $_SESSION['db_installed'] = true;

                // 다음 단계로 리다이렉트
                header('Location: ?step=3');
                exit;
            } else {
                $error = 'SQL 스키마 파일을 찾을 수 없습니다.';
            }
        }

        mysqli_close($conn);
    }
}

// 기본값 설정
$db_host = $_SESSION['db_host'] ?? 'localhost';
$db_name = $_SESSION['db_name'] ?? 'duson_print';
$db_user = $_SESSION['db_user'] ?? '';
$db_pass = $_SESSION['db_pass'] ?? '';
?>

<h2 class="step-title">Step 2: 데이터베이스 설정</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="post">
    <div class="form-group">
        <label>데이터베이스 호스트</label>
        <input type="text" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
        <small>보통 localhost 또는 127.0.0.1 입니다.</small>
    </div>

    <div class="form-group">
        <label>데이터베이스 이름</label>
        <input type="text" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
        <small>존재하지 않으면 자동으로 생성됩니다.</small>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>데이터베이스 사용자</label>
            <input type="text" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" required>
        </div>

        <div class="form-group">
            <label>데이터베이스 비밀번호</label>
            <input type="password" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>">
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>주의:</strong> 기존 데이터베이스를 사용하면 테이블이 덮어씌워질 수 있습니다.
    </div>

    <div class="btn-group">
        <a href="?step=1" class="btn btn-secondary">← 이전</a>
        <button type="submit" class="btn btn-primary">연결 테스트 및 설치 →</button>
    </div>
</form>
