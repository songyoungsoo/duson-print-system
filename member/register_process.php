<?php
/**
 * 통합 회원가입 처리 (users 테이블 전용)
 *
 * 기능:
 * - users 테이블에만 저장
 * - bcrypt 비밀번호 해싱
 * - 전화번호 통합 (phone1-2-3 → phone)
 * - 사업자정보 매핑 (po1~po7 → business_*)
 * - 회원가입 후 자동 로그인
 */

include "../db.php";

// 에러 처리 함수
function ERROR($msg) {
    $safe_msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    $safe_msg = str_replace(["\\", "'", "\n", "\r"], ["\\\\", "\\'", "\\n", ""], $safe_msg);
    echo "<script language='javascript'>
    window.alert('{$safe_msg}');
    history.go(-1);
    </script>";
    exit;
}

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF 검증
include_once __DIR__ . '/../includes/csrf.php';
csrf_verify_or_die();

// POST 데이터 받기 및 검증
$id = isset($_POST['id']) ? trim($_POST['id']) : null;
$pass1 = isset($_POST['pass1']) ? $_POST['pass1'] : null;
$pass2 = isset($_POST['pass2']) ? $_POST['pass2'] : null;
$name = isset($_POST['name']) ? trim($_POST['name']) : null;

// 전화번호 (분리됨)
$phone1 = isset($_POST['phone1']) ? trim($_POST['phone1']) : '';
$phone2 = isset($_POST['phone2']) ? trim($_POST['phone2']) : '';
$phone3 = isset($_POST['phone3']) ? trim($_POST['phone3']) : '';
$hendphone1 = isset($_POST['hendphone1']) ? trim($_POST['hendphone1']) : '';
$hendphone2 = isset($_POST['hendphone2']) ? trim($_POST['hendphone2']) : '';
$hendphone3 = isset($_POST['hendphone3']) ? trim($_POST['hendphone3']) : '';

// 이메일
$email = isset($_POST['email']) ? trim($_POST['email']) : null;

// 주소
$sample6_postcode = isset($_POST['sample6_postcode']) ? trim($_POST['sample6_postcode']) : '';
$sample6_address = isset($_POST['sample6_address']) ? trim($_POST['sample6_address']) : '';
$sample6_detailAddress = isset($_POST['sample6_detailAddress']) ? trim($_POST['sample6_detailAddress']) : '';
$sample6_extraAddress = isset($_POST['sample6_extraAddress']) ? trim($_POST['sample6_extraAddress']) : '';

// 사업자정보
$po1 = isset($_POST['po1']) ? trim($_POST['po1']) : ''; // 사업자등록번호
$po2 = isset($_POST['po2']) ? trim($_POST['po2']) : ''; // 상호
$po3 = isset($_POST['po3']) ? trim($_POST['po3']) : ''; // 대표자
$po4 = isset($_POST['po4']) ? trim($_POST['po4']) : ''; // 업태
$po5 = isset($_POST['po5']) ? trim($_POST['po5']) : ''; // 종목
$po6 = isset($_POST['po6']) ? trim($_POST['po6']) : ''; // 사업장주소
$po7 = isset($_POST['po7']) ? trim($_POST['po7']) : ''; // 세금계산서 이메일

// 약관 동의 확인
$agree_terms = isset($_POST['agree_terms']) ? 1 : 0;
$agree_privacy = isset($_POST['agree_privacy']) ? 1 : 0;
if (!$agree_terms || !$agree_privacy) {
    ERROR("이용약관 및 개인정보 취급방침에 동의해주세요.");
}

// 필수 항목 검증
if (!$id) {
    ERROR("아이디를 입력해주세요.");
}

if (!$pass1 || !$pass2) {
    ERROR("비밀번호를 입력해주세요.");
}

if ($pass1 !== $pass2) {
    ERROR("비밀번호가 일치하지 않습니다.");
}

if (!$name) {
    ERROR("이름을 입력해주세요.");
}

// 2. 아이디 중복 체크 (users 테이블)
$check_query = "SELECT username FROM users WHERE username = ?";
$check_stmt = mysqli_prepare($db, $check_query);
mysqli_stmt_bind_param($check_stmt, "s", $id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    mysqli_stmt_close($check_stmt);
    ERROR("{$id}는 이미 사용 중인 아이디입니다.");
}
mysqli_stmt_close($check_stmt);

// 3. 전화번호 통합
$phone = '';
if (!empty($phone1) && !empty($phone2) && !empty($phone3)) {
    $phone = $phone1 . '-' . $phone2 . '-' . $phone3;
} elseif (!empty($hendphone1) && !empty($hendphone2) && !empty($hendphone3)) {
    // 일반전화 없으면 핸드폰 사용
    $phone = $hendphone1 . '-' . $hendphone2 . '-' . $hendphone3;
}

// 4. 비밀번호 bcrypt 해싱
$password_hash = password_hash($pass1, PASSWORD_BCRYPT);

// 5. users 테이블에 삽입
$insert_query = "
    INSERT INTO users (
        username,
        password,
        name,
        email,
        phone,
        postcode,
        address,
        detail_address,
        extra_address,
        business_number,
        business_name,
        business_owner,
        business_type,
        business_item,
        business_address,
        tax_invoice_email,
        level,
        login_count,
        created_at,
        migrated_from_member
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
";

$stmt = mysqli_prepare($db, $insert_query);

if (!$stmt) {
    ERROR("회원가입 처리 중 오류가 발생했습니다: " . mysqli_error($db));
}

// 기본값 설정
$level = '5'; // 일반 회원
$login_count = 0;
$migrated_from_member = 0; // 신규 가입

mysqli_stmt_bind_param(
    $stmt,
    "sssssssssssssssssii",
    $id,                    // username
    $password_hash,         // password (bcrypt)
    $name,                  // name
    $email,                 // email
    $phone,                 // phone (통합)
    $sample6_postcode,      // postcode
    $sample6_address,       // address
    $sample6_detailAddress, // detail_address
    $sample6_extraAddress,  // extra_address
    $po1,                   // business_number
    $po2,                   // business_name
    $po3,                   // business_owner
    $po4,                   // business_type
    $po5,                   // business_item
    $po6,                   // business_address
    $po7,                   // tax_invoice_email
    $level,                 // level
    $login_count,           // login_count
    $migrated_from_member   // migrated_from_member
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    ERROR("회원가입 처리 중 오류가 발생했습니다: " . mysqli_stmt_error($stmt));
}

$new_user_id = mysqli_insert_id($db);
mysqli_stmt_close($stmt);

// 5-2. member 테이블에도 동시 저장 (관리자 회원목록 호환)
$member_query = "
    INSERT INTO member (
        id, pass, name,
        phone1, phone2, phone3,
        hendphone1, hendphone2, hendphone3,
        email,
        sample6_postcode, sample6_address, sample6_detailAddress, sample6_extraAddress,
        po1, po2, po3, po4, po5, po6, po7,
        date, level, Logincount
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 0)
";

$member_stmt = mysqli_prepare($db, $member_query);
if ($member_stmt) {
    // bind_param 검증: ? = 22개, 타입 = 22개 (s x 22), 변수 = 22개
    mysqli_stmt_bind_param(
        $member_stmt,
        "ssssssssssssssssssssss",
        $id,                    // id
        $password_hash,         // pass (bcrypt)
        $name,                  // name
        $phone1,                // phone1
        $phone2,                // phone2
        $phone3,                // phone3
        $hendphone1,            // hendphone1
        $hendphone2,            // hendphone2
        $hendphone3,            // hendphone3
        $email,                 // email
        $sample6_postcode,      // sample6_postcode
        $sample6_address,       // sample6_address
        $sample6_detailAddress, // sample6_detailAddress
        $sample6_extraAddress,  // sample6_extraAddress
        $po1,                   // po1 (사업자등록번호)
        $po2,                   // po2 (상호)
        $po3,                   // po3 (대표자)
        $po4,                   // po4 (업태)
        $po5,                   // po5 (종목)
        $po6,                   // po6 (사업장주소)
        $po7,                   // po7 (세금계산서 이메일)
        $level                  // level
    );
    mysqli_stmt_execute($member_stmt);
    mysqli_stmt_close($member_stmt);
}

// 6. 자동 로그인 (세션 설정)
$_SESSION['id_login_ok'] = array(
    'id' => $id,
    'email' => $email,
    'name' => $name,
    'user_id' => $new_user_id
);

// 7. users 테이블의 login_count 업데이트
$update_login = "UPDATE users SET login_count = login_count + 1, last_login = NOW() WHERE id = ?";
$update_stmt = mysqli_prepare($db, $update_login);
mysqli_stmt_bind_param($update_stmt, "i", $new_user_id);
mysqli_stmt_execute($update_stmt);
mysqli_stmt_close($update_stmt);

mysqli_close($db);

// 8. 가입 완료 페이지로 이동
$safe_id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
$safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
echo "<script language='javascript'>
    window.alert('회원가입이 완료되었습니다!\\n\\n아이디: {$safe_id}\\n이름: {$safe_name}\\n\\n자동 로그인되었습니다.');
    location.href='../index.php';
</script>";
exit;
?>
