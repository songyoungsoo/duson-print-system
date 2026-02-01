<?php
// db.php 파일을 포함합니다.
include "../db.php";

// 세션을 시작합니다 (이미 시작된 경우 무시)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 로그인한 사용자 ID를 세션에서 가져옵니다.
$WebtingMemberLogin_id = $_SESSION['id_login_ok'] ?? '';

// 데이터베이스 연결을 확인합니다.
if ($db) {
    // users 테이블에서 조회 (member.id → users.username)
    $stmt = mysqli_prepare($db, "SELECT * FROM users WHERE username = ?");
    
    // 사용자 ID를 쿼리에 바인딩합니다.
    mysqli_stmt_bind_param($stmt, "s", $WebtingMemberLogin_id);
    
    // 쿼리를 실행합니다.
    mysqli_stmt_execute($stmt);
    
    // 결과를 가져옵니다.
    $result = mysqli_stmt_get_result($stmt);
    
    // 결과를 배열로 변환합니다.
    $MenuLogin_id_row = mysqli_fetch_assoc($result);

    // 사용자 정보를 변수에 저장합니다 (null 체크 포함)
    $MlangMember_id = htmlspecialchars($MenuLogin_id_row['username'] ?? '');
    $MlangMember_pass = '********';  // 비밀번호 해시 노출 금지
    $MlangMember_name = htmlspecialchars($MenuLogin_id_row['name'] ?? '');

    // phone → hendphone1/2/3 분리
    list($hp1, $hp2, $hp3) = array_pad(explode('-', $MenuLogin_id_row['phone'] ?? ''), 3, '');
    $MlangMember_hendphone1 = htmlspecialchars($hp1);
    $MlangMember_hendphone2 = htmlspecialchars($hp2);
    $MlangMember_hendphone3 = htmlspecialchars($hp3);

    $MlangMember_email = htmlspecialchars($MenuLogin_id_row['email'] ?? '');
    $MlangMember_date = htmlspecialchars($MenuLogin_id_row['created_at'] ?? '');
    $MlangMember_Logincount = htmlspecialchars($MenuLogin_id_row['login_count'] ?? '0');
    $MlangMember_EndLogin = htmlspecialchars($MenuLogin_id_row['last_login'] ?? '');
    $MlangMember_level = htmlspecialchars($MenuLogin_id_row['level'] ?? '0');

    // 쿼리를 종료합니다.
    mysqli_stmt_close($stmt);
} else {
    // 로그인하지 않은 경우 기본값 설정
    $MlangMember_id = '';
    $MlangMember_pass = '';
    $MlangMember_name = '';
    $MlangMember_hendphone1 = '';
    $MlangMember_hendphone2 = '';
    $MlangMember_hendphone3 = '';
    $MlangMember_email = '';
    $MlangMember_date = '';
    $MlangMember_Logincount = '0';
    $MlangMember_EndLogin = '';
    $MlangMember_level = '0';
}
?>
