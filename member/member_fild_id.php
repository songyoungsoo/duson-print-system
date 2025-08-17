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
    // SQL 쿼리를 준비합니다.
    $stmt = $db->prepare("SELECT * FROM member WHERE id = ?");
    
    // 사용자 ID를 쿼리에 바인딩합니다.
    $stmt->bind_param("s", $WebtingMemberLogin_id);
    
    // 쿼리를 실행합니다.
    $stmt->execute();
    
    // 결과를 가져옵니다.
    $result = $stmt->get_result();
    
    // 결과를 배열로 변환합니다.
    $MenuLogin_id_row = $result->fetch_assoc();

    // 사용자 정보를 변수에 저장합니다 (null 체크 포함)
    $MlangMember_id = htmlspecialchars($MenuLogin_id_row['id'] ?? '');
    $MlangMember_pass = htmlspecialchars($MenuLogin_id_row['pass'] ?? '');  
    $MlangMember_name = htmlspecialchars($MenuLogin_id_row['name'] ?? ''); 
    $MlangMember_hendphone1 = htmlspecialchars($MenuLogin_id_row['hendphone1'] ?? '');
    $MlangMember_hendphone2 = htmlspecialchars($MenuLogin_id_row['hendphone2'] ?? '');
    $MlangMember_hendphone3 = htmlspecialchars($MenuLogin_id_row['hendphone3'] ?? '');
    $MlangMember_email = htmlspecialchars($MenuLogin_id_row['email'] ?? ''); 
    $MlangMember_date = htmlspecialchars($MenuLogin_id_row['date'] ?? ''); 
    $MlangMember_Logincount = htmlspecialchars($MenuLogin_id_row['Logincount'] ?? '0');
    $MlangMember_EndLogin = htmlspecialchars($MenuLogin_id_row['EndLogin'] ?? '');
    $MlangMember_level = htmlspecialchars($MenuLogin_id_row['level'] ?? '0'); 

    // 쿼리와 연결을 종료합니다.
    $stmt->close();
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
