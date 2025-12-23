<?php
header('Content-Type: text/plain; charset=utf-8');

include 'db.php';

echo "=== admin 비밀번호 해시 변환 ===\n\n";

$username = 'admin';
$plain_password = 'du701018';

// 1. 현재 상태 확인
$query = "SELECT id, password FROM users WHERE username = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    die("❌ users 테이블에 admin 계정이 없습니다.\n");
}

echo "현재 password: {$user['password']}\n";
echo "비밀번호 길이: " . strlen($user['password']) . "\n\n";

// 2. 비밀번호 해시 생성
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
echo "생성된 해시: $hashed_password\n";
echo "해시 길이: " . strlen($hashed_password) . "\n\n";

// 3. 업데이트 실행 확인
$confirm = $_GET['confirm'] ?? '';
if ($confirm !== 'yes') {
    echo "⚠️ 이 작업은 users 테이블의 admin 비밀번호를 해시로 변환합니다.\n\n";
    echo "실행하려면 URL에 ?confirm=yes를 추가하세요.\n";
    echo "예: " . $_SERVER['PHP_SELF'] . "?confirm=yes\n\n";
    echo "변경 내용:\n";
    echo "  평문: $plain_password\n";
    echo "  해시: $hashed_password\n";
    exit;
}

// 4. 비밀번호 업데이트
$update_query = "UPDATE users SET password = ? WHERE username = ?";
$update_stmt = mysqli_prepare($db, $update_query);
mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $username);

if (mysqli_stmt_execute($update_stmt)) {
    echo "✅ 비밀번호 해시 변환 완료!\n\n";

    // 5. 검증
    $query = "SELECT password FROM users WHERE username = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    echo "업데이트된 password: " . substr($user['password'], 0, 50) . "...\n";
    echo "해시 형식: " . (strpos($user['password'], '$2y$') === 0 ? '✅ bcrypt' : '❌ 잘못된 형식') . "\n\n";

    // password_verify 테스트
    if (password_verify($plain_password, $user['password'])) {
        echo "✅ password_verify() 테스트 성공!\n";
        echo "   로그인 가능: admin / du701018\n";
    } else {
        echo "❌ password_verify() 테스트 실패\n";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "❌ 업데이트 실패: " . mysqli_error($db) . "\n";
}

mysqli_stmt_close($update_stmt);
mysqli_close($db);

echo "\n\n=== 다음 단계 ===\n\n";
echo "1. http://localhost/member/login.php 접속\n";
echo "2. 아이디: admin\n";
echo "3. 비밀번호: du701018\n";
echo "4. 로그인 버튼 클릭\n";
echo "5. 로그인 성공 후 http://localhost/sub/checkboard.php에서 관리자 권한 확인\n";
?>
