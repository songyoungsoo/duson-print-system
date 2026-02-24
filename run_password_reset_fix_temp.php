<?php
// 임시 마이그레이션 실행 파일 (보안을 위해 실행 후 삭제 필요)
$password = $_POST['password'] ?? $_GET['password'] ?? '';

if ($password !== 'duson2026!migration') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Password Reset Fix</title>
        <style>
            body { font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; }
            .box { border: 1px solid #ddd; padding: 20px; margin: 20px 0; }
            .error { color: red; }
            .success { color: green; }
            input { padding: 8px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <h1>비밀번호 재설정 테이블 수정</h1>
        <div class="box">
            <p>이 스크립트는 비밀번호 재설정 기능을 위한 데이터베이스 테이블을 생성합니다.</p>
            <form method="POST">
                <label>관리자 비밀번호:</label><br>
                <input type="password" name="password" required><br>
                <button type="submit">실행</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

require_once 'db.php';

echo "<!DOCTYPE html><html><head><title>Migration Results</title></head><body><pre>\n";
echo "=== 비밀번호 재설정 테이블 수정 ===\n";
echo "시간: " . date('Y-m-d H:i:s') . "\n\n";

$errors = [];
$success = [];

try {
    $query = "CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `email` VARCHAR(200) NOT NULL,
        `token` VARCHAR(255) NOT NULL UNIQUE,
        `expires_at` DATETIME NOT NULL,
        `used` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_token` (`token`),
        INDEX `idx_email` (`email`),
        INDEX `idx_expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($db, $query)) {
        $success[] = "✅ password_resets 테이블 생성/확인 완료";
    } else {
        $errors[] = "❌ password_resets 테이블 생성 실패: " . mysqli_error($db);
    }
} catch (Exception $e) {
    $errors[] = "❌ 예외 발생: " . $e->getMessage();
}

$check_query = "SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'member' 
                AND COLUMN_NAME = 'reset_token'";
$result = mysqli_query($db, $check_query);
if ($result && mysqli_num_rows($result) == 0) {
    $alter_query = "ALTER TABLE `member` ADD COLUMN `reset_token` VARCHAR(255) DEFAULT NULL";
    if (mysqli_query($db, $alter_query)) {
        $success[] = "✅ member 테이블에 reset_token 컬럼 추가 완료";
    } else {
        $errors[] = "❌ reset_token 컬럼 추가 실패: " . mysqli_error($db);
    }
} else {
    $success[] = "ℹ️ reset_token 컬럼이 이미 존재합니다";
}

$check_query = "SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'member' 
                AND COLUMN_NAME = 'reset_expires'";
$result = mysqli_query($db, $check_query);
if ($result && mysqli_num_rows($result) == 0) {
    $alter_query = "ALTER TABLE `member` ADD COLUMN `reset_expires` DATETIME DEFAULT NULL";
    if (mysqli_query($db, $alter_query)) {
        $success[] = "✅ member 테이블에 reset_expires 컬럼 추가 완료";
    } else {
        $errors[] = "❌ reset_expires 컬럼 추가 실패: " . mysqli_error($db);
    }
} else {
    $success[] = "ℹ️ reset_expires 컬럼이 이미 존재합니다";
}

$cleanup_query = "DELETE FROM `password_resets` WHERE `expires_at` < NOW()";
if (mysqli_query($db, $cleanup_query)) {
    $affected = mysqli_affected_rows($db);
    $success[] = "✅ password_resets에서 $affected 개의 만료된 토큰 삭제";
}

$cleanup_query = "UPDATE `member` SET `reset_token` = NULL, `reset_expires` = NULL WHERE `reset_expires` < NOW()";
if (mysqli_query($db, $cleanup_query)) {
    $affected = mysqli_affected_rows($db);
    $success[] = "✅ member 테이블에서 $affected 개의 만료된 토큰 삭제";
}

echo "\n=== 실행 결과 ===\n\n";

if (!empty($success)) {
    echo "성공:\n";
    foreach ($success as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "오류:\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

echo "\n=== 테이블 구조 확인 ===\n\n";

$verify_query = "DESCRIBE `password_resets`";
$result = mysqli_query($db, $verify_query);
if ($result) {
    echo "password_resets 테이블:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo sprintf("  %-20s %-20s %-10s %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'],
            $row['Key'] ? "KEY:" . $row['Key'] : ""
        );
    }
}

echo "\n=== 마이그레이션 완료 ===\n";

if (empty($errors)) {
    echo "\n<span style='color:green;font-weight:bold;'>✅ 모든 마이그레이션이 성공적으로 완료되었습니다!</span>\n\n";
    echo "비밀번호 재설정 기능을 테스트할 수 있습니다:\n";
    echo "https://dsp114.co.kr/member/password_reset_request.php\n";
    
    echo "\n<span style='color:red;'>⚠️ 보안 경고: 이 파일(run_password_reset_fix_temp.php)을 삭제해주세요!</span>\n";
} else {
    echo "\n<span style='color:orange;'>⚠️ 일부 오류가 발생했습니다. 위 내용을 확인해주세요.</span>\n";
}

echo "</pre></body></html>";

mysqli_close($db);
?>