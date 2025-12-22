<?php
// db.php 파일을 포함합니다.
include "../db.php";

// 로그인한 사용자 ID를 세션 또는 다른 변수에서 가져옵니다.
$TKmember_id = $_SESSION['TKmember_id'] ?? ''; // 세션에서 가져오거나 기본값으로 빈 문자열을 설정합니다.

// 데이터베이스 연결을 확인합니다.
if ($db) {
    // SQL 쿼리를 준비합니다.
    $stmt = $db->prepare("SELECT * FROM member WHERE id = ?");
    
    // 사용자 ID를 쿼리에 바인딩합니다.
    $stmt->bind_param("s", $TKmember_id);
    
    // 쿼리를 실행합니다.
    $stmt->execute();
    
    // 결과를 가져옵니다.
    $result = $stmt->get_result();
    
    // 결과를 배열로 변환합니다.
    $MenuLogin_id_row = $result->fetch_assoc();

    // 사용자 정보를 변수에 저장합니다.
    $MlangMember_id = htmlspecialchars($MenuLogin_id_row['id']);
    $MlangMember_pass = htmlspecialchars($MenuLogin_id_row['pass']);  
    $MlangMember_name = htmlspecialchars($MenuLogin_id_row['name']); 
    $MlangMember_hendphone1 = htmlspecialchars($MenuLogin_id_row['hendphone1']);
    $MlangMember_hendphone2 = htmlspecialchars($MenuLogin_id_row['hendphone2']);
    $MlangMember_hendphone3 = htmlspecialchars($MenuLogin_id_row['hendphone3']);
    $MlangMember_email = htmlspecialchars($MenuLogin_id_row['email']);    
    $MlangMember_date = htmlspecialchars($MenuLogin_id_row['date']); 
    $MlangMember_Logincount = htmlspecialchars($MenuLogin_id_row['Logincount']);
    $MlangMember_EndLogin = htmlspecialchars($MenuLogin_id_row['EndLogin']); 

    // 쿼리와 연결을 종료합니다.
    $stmt->close();
    $db->close();
} else {
    echo "데이터베이스에 연결할 수 없습니다.";
}
?>
