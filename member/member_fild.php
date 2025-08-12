<?php
// db.php 파일을 포함하여 데이터베이스 연결을 설정합니다.
// include "../db.php";
include_once(__DIR__ . '/../db.php');

// 사용자 ID를 세션 또는 GET/POST 요청에서 가져옵니다.
$no = isset($_GET['no']) ? $_GET['no'] : '';

if ($db) {
    // SQL 쿼리를 준비합니다.
    $stmt = $db->prepare("SELECT * FROM member WHERE no = ?");
    
    // 사용자 ID를 쿼리에 바인딩합니다.
    $stmt->bind_param("s", $no);
    
    // 쿼리를 실행합니다.
    $stmt->execute();
    
    // 결과를 가져옵니다.
    $result = $stmt->get_result();
    
    // 결과를 배열로 변환합니다.
    $row = $result->fetch_assoc();

    if ($row) {
        $MlangMember_id = htmlspecialchars($row['id']);
        $MlangMember_pass1 = htmlspecialchars($row['pass']);  
        $MlangMember_name = htmlspecialchars($row['name']); 
        $MlangMember_phone1 = htmlspecialchars($row['phone1']); 
        $MlangMember_phone2 = htmlspecialchars($row['phone2']);
        $MlangMember_phone3 = htmlspecialchars($row['phone3']);
        $MlangMember_hendphone1 = htmlspecialchars($row['hendphone1']);
        $MlangMember_hendphone2 = htmlspecialchars($row['hendphone2']);
        $MlangMember_hendphone3 = htmlspecialchars($row['hendphone3']);
        $MlangMember_email = htmlspecialchars($row['email']);   
        $MlangMember_sample6_postcode = htmlspecialchars($row['sample6_postcode']);
        $MlangMember_sample6_address = htmlspecialchars($row['sample6_address']);
        $MlangMember_sample6_detailAddress = htmlspecialchars($row['sample6_detailAddress']);
        $MlangMember_sample6_extraAddress = htmlspecialchars($row['sample6_extraAddress']);
        $MlangMember_po1 = htmlspecialchars($row['po1']); 
        $MlangMember_po2 = htmlspecialchars($row['po2']); 
        $MlangMember_po3 = htmlspecialchars($row['po3']); 
        $MlangMember_po4 = htmlspecialchars($row['po4']); 
        $MlangMember_po5 = htmlspecialchars($row['po5']); 
        $MlangMember_po6 = htmlspecialchars($row['po6']); 
        $MlangMember_po7 = htmlspecialchars($row['po7']);

        $CONTENT = $row['connent'];
        $CONTENT = str_replace("<", "&lt;", $CONTENT);
        $CONTENT = str_replace(">", "&gt;", $CONTENT);
        $CONTENT = str_replace("\"", "&quot;", $CONTENT);
        $CONTENT = str_replace("\|", "&#124;", $CONTENT);
        $CONTENT = str_replace("\r\n\r\n", "<P>", $CONTENT);
        $CONTENT = str_replace("\r\n", "<BR>", $CONTENT);
        $MlangMember_connent = $CONTENT;

        $MlangMember_date = htmlspecialchars($row['date']); 
        $MlangMember_level = htmlspecialchars($row['level']); 
    } else {
        if ($op == "back") {
            echo ("<script language='javascript'>
            window.alert('신청하신 정보를 찾을 수 없습니다.\\n\\n이미 처리된 상태입니다.');
            history.go(-1);
            </script>");
            exit;
        }
        if ($op == "pop") {
            echo ("<script language='javascript'>
            window.alert('신청하신 정보를 찾을 수 없습니다.\\n\\n이미 처리된 상태입니다.');
            window.self.close();
            </script>");
            exit;
        }
    }

    // 데이터베이스 연결을 종료합니다.
    $stmt->close();
    $db->close();
} else {
    echo "데이터베이스에 연결할 수 없습니다.";
}
?>
