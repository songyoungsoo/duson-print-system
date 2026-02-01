<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include $_SERVER['DOCUMENT_ROOT'] . "/db.php";

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';

if (empty($id) || empty($pass)) {
    echo "로그인 정보가 올바르지 않습니다.";
    exit;
}

// 1. users 테이블에서 먼저 조회
$query = "SELECT * FROM users WHERE username = ?";
// 3-step verification: placeholders=1, types=1("s"), vars=1
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($user) {
    // users 테이블에서 발견 - bcrypt 또는 plaintext 비밀번호 검증
    $stored_password = $user['password'];
    $login_success = false;

    if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
        // bcrypt 비밀번호
        $login_success = password_verify($pass, $stored_password);
    } else {
        // plaintext 비밀번호 - 검증 후 자동 업그레이드
        if ($pass === $stored_password) {
            $login_success = true;
            // bcrypt로 자동 업그레이드
            $new_hash = password_hash($pass, PASSWORD_DEFAULT);
            $upgrade_query = "UPDATE users SET password = ? WHERE id = ?";
            // 3-step verification: placeholders=2, types=2("si"), vars=2
            $upgrade_stmt = mysqli_prepare($db, $upgrade_query);
            mysqli_stmt_bind_param($upgrade_stmt, "si", $new_hash, $user['id']);
            mysqli_stmt_execute($upgrade_stmt);
            mysqli_stmt_close($upgrade_stmt);
        }
    }

    if ($login_success) {
        // 세션 변수 설정
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['id_login_ok'] = array(
            'id' => $user['username'],
            'email' => $user['email']
        );

        // 로그인 카운트 및 마지막 로그인 업데이트
        $update_query = "UPDATE users SET login_count = login_count + 1, last_login = NOW() WHERE id = ?";
        // 3-step verification: placeholders=1, types=1("i"), vars=1
        $update_stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);

        ?>
        <script>
            location.href='index_01.php';
        </script>
        <?php
        exit;
    }
}

// 2. users에서 실패 시 member 테이블 fallback + 자동 마이그레이션
$query = "SELECT * FROM member WHERE id = ?";
// 3-step verification: placeholders=1, types=1("s"), vars=1
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($member && $pass === $member['pass']) {
    // member 테이블에서 로그인 성공 - users 테이블로 자동 마이그레이션
    $new_hash = password_hash($pass, PASSWORD_DEFAULT);

    // phone 결합
    $phone_parts = array_filter([$member['hendphone1'] ?? '', $member['hendphone2'] ?? '', $member['hendphone3'] ?? '']);
    $phone = !empty($phone_parts) ? implode('-', [$member['hendphone1'], $member['hendphone2'], $member['hendphone3']]) : '';
    if (empty($phone)) {
        $phone_parts2 = array_filter([$member['phone1'] ?? '', $member['phone2'] ?? '', $member['phone3'] ?? '']);
        $phone = !empty($phone_parts2) ? implode('-', [$member['phone1'], $member['phone2'], $member['phone3']]) : '';
    }

    // users 테이블에 INSERT (중복 방지)
    $check_query = "SELECT id FROM users WHERE username = ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $existing = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    if (!$existing) {
        $insert_query = "INSERT INTO users (username, password, name, email, phone, postcode, address, detail_address, extra_address, business_number, business_name, business_owner, business_type, business_item, business_address, tax_invoice_email, created_at, login_count, last_login, level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), ?)";
        // 3-step verification: placeholders=18, types=18("ssssssssssssssssis"), vars=18
        $insert_stmt = mysqli_prepare($db, $insert_query);
        $m_name = $member['name'] ?? '';
        $m_email = $member['email'] ?? '';
        $m_postcode = $member['sample6_postcode'] ?? '';
        $m_address = $member['sample6_address'] ?? '';
        $m_detail = $member['sample6_detailAddress'] ?? '';
        $m_extra = $member['sample6_extraAddress'] ?? '';
        $m_biz_num = $member['po1'] ?? '';
        $m_biz_name = $member['po2'] ?? '';
        $m_biz_owner = $member['po3'] ?? '';
        $m_biz_type = $member['po4'] ?? '';
        $m_biz_item = $member['po5'] ?? '';
        $m_biz_addr = $member['po6'] ?? '';
        $m_tax_email = $member['po7'] ?? '';
        $m_date = $member['date'] ?? date('Y-m-d H:i:s');
        $m_level = intval($member['level'] ?? 1);

        mysqli_stmt_bind_param($insert_stmt, "ssssssssssssssssis",
            $id, $new_hash, $m_name, $m_email, $phone,
            $m_postcode, $m_address, $m_detail, $m_extra,
            $m_biz_num, $m_biz_name, $m_biz_owner, $m_biz_type, $m_biz_item, $m_biz_addr,
            $m_tax_email, $m_date, $m_level
        );
        mysqli_stmt_execute($insert_stmt);
        $new_user_id = mysqli_insert_id($db);
        mysqli_stmt_close($insert_stmt);
    } else {
        $new_user_id = $existing['id'];
    }

    // 세션 변수 설정
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['username'] = $id;
    $_SESSION['user_name'] = $member['name'] ?? '';
    $_SESSION['id_login_ok'] = array(
        'id' => $id,
        'email' => $member['email'] ?? ''
    );

    ?>
    <script>
        location.href='index_01.php';
    </script>
    <?php
    exit;
}

// 로그인 실패
echo "로그인 정보가 올바르지 않습니다.";
?>
