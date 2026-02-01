<?php
// db.php 파일을 포함하여 데이터베이스 연결을 설정합니다.
if (!isset($db) || !$db) {
    $db_path = isset($db_dir) ? "$db_dir/db.php" : __DIR__ . '/../db.php';
    include_once($db_path);
}

// 사용자 ID: 이미 설정된 $no 변수 사용, 없으면 GET에서 가져옴
if (!isset($no) || $no === '') {
    $no = isset($_GET['no']) ? $_GET['no'] : '';
}

if ($db) {
    // users 테이블에서 조회 (member.no → users.id)
    $stmt = mysqli_prepare($db, "SELECT * FROM users WHERE id = ?");
    
    // 사용자 ID를 쿼리에 바인딩합니다.
    mysqli_stmt_bind_param($stmt, "i", $no);
    
    // 쿼리를 실행합니다.
    mysqli_stmt_execute($stmt);
    
    // 결과를 가져옵니다.
    $result = mysqli_stmt_get_result($stmt);
    
    // 결과를 배열로 변환합니다.
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $MlangMember_id = htmlspecialchars($row['username'] ?? '');
        $MlangMember_pass1 = '********';  // 비밀번호 해시 노출 금지
        $MlangMember_name = htmlspecialchars($row['name'] ?? '');

        // phone → phone1/2/3 분리
        list($p1, $p2, $p3) = array_pad(explode('-', $row['phone'] ?? ''), 3, '');
        $MlangMember_phone1 = htmlspecialchars($p1);
        $MlangMember_phone2 = htmlspecialchars($p2);
        $MlangMember_phone3 = htmlspecialchars($p3);
        // hendphone도 동일 phone 값 사용
        $MlangMember_hendphone1 = htmlspecialchars($p1);
        $MlangMember_hendphone2 = htmlspecialchars($p2);
        $MlangMember_hendphone3 = htmlspecialchars($p3);

        $MlangMember_email = htmlspecialchars($row['email'] ?? '');
        $MlangMember_sample6_postcode = htmlspecialchars($row['postcode'] ?? '');
        $MlangMember_sample6_address = htmlspecialchars($row['address'] ?? '');
        $MlangMember_sample6_detailAddress = htmlspecialchars($row['detail_address'] ?? '');
        $MlangMember_sample6_extraAddress = htmlspecialchars($row['extra_address'] ?? '');
        $MlangMember_po1 = htmlspecialchars($row['business_number'] ?? '');
        $MlangMember_po2 = htmlspecialchars($row['business_name'] ?? '');
        $MlangMember_po3 = htmlspecialchars($row['business_owner'] ?? '');
        $MlangMember_po4 = htmlspecialchars($row['business_type'] ?? '');
        $MlangMember_po5 = htmlspecialchars($row['business_item'] ?? '');
        $MlangMember_po6 = htmlspecialchars($row['business_address'] ?? '');
        $MlangMember_po7 = htmlspecialchars($row['tax_invoice_email'] ?? '');

        $MlangMember_connent = ''; // users 테이블에 해당 필드 없음

        $MlangMember_date = htmlspecialchars($row['created_at'] ?? '');
        $MlangMember_level = htmlspecialchars($row['level'] ?? '0');
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

    // statement만 종료 (DB 연결은 유지 - 이후 코드에서 사용)
    mysqli_stmt_close($stmt);
    // $db->close(); // 주석처리: 연결은 호출자가 관리
} else {
    echo "데이터베이스에 연결할 수 없습니다.";
}
?>
