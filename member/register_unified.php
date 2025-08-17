<?php
/**
 * 통합 회원가입 처리 시스템
 * users 테이블 기반 (기존 member 테이블과 호환)
 */

session_start();
include "../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 폼 데이터 수집
    $id = mysqli_real_escape_string($db, trim($_POST['id'] ?? ''));
    $pass = trim($_POST['pass'] ?? '');
    $pass2 = trim($_POST['pass2'] ?? '');
    $name = mysqli_real_escape_string($db, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($db, trim($_POST['email'] ?? ''));
    
    // 전화번호 조합
    $phone1 = trim($_POST['phone1'] ?? '');
    $phone2 = trim($_POST['phone2'] ?? '');
    $phone3 = trim($_POST['phone3'] ?? '');
    $phone = '';
    if (!empty($phone1) && !empty($phone2) && !empty($phone3)) {
        $phone = $phone1 . '-' . $phone2 . '-' . $phone3;
    }
    
    // hendphone 조합 (선택사항)
    $hendphone1 = trim($_POST['hendphone1'] ?? '');
    $hendphone2 = trim($_POST['hendphone2'] ?? '');
    $hendphone3 = trim($_POST['hendphone3'] ?? '');
    $hendphone = '';
    if (!empty($hendphone1) && !empty($hendphone2) && !empty($hendphone3)) {
        $hendphone = $hendphone1 . '-' . $hendphone2 . '-' . $hendphone3;
    }
    
    // 유효성 검사
    $errors = [];
    
    if (empty($id)) {
        $errors[] = "아이디를 입력해주세요.";
    } elseif (strlen($id) < 4 || strlen($id) > 12) {
        $errors[] = "아이디는 4~12자로 입력해주세요.";
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $id)) {
        $errors[] = "아이디는 영문자와 숫자만 사용 가능합니다.";
    }
    
    if (empty($pass)) {
        $errors[] = "비밀번호를 입력해주세요.";
    } elseif (strlen($pass) < 6) {
        $errors[] = "비밀번호는 6자 이상이어야 합니다.";
    }
    
    if ($pass !== $pass2) {
        $errors[] = "비밀번호가 일치하지 않습니다.";
    }
    
    if (empty($name)) {
        $errors[] = "이름을 입력해주세요.";
    }
    
    // 오류가 있으면 돌아가기
    if (!empty($errors)) {
        $error_message = implode("\\n", $errors);
        echo "<script>
                alert('$error_message');
                history.back();
              </script>";
        exit;
    }
    
    // 중복 확인 (users 테이블에서)
    $check_users = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($db, $check_users);
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>
                alert('이미 사용 중인 아이디입니다.');
                history.back();
              </script>";
        exit;
    }
    
    // 기존 member 테이블에서도 확인 (호환성)
    $check_member = "SELECT id FROM member WHERE id = ?";
    $stmt2 = mysqli_prepare($db, $check_member);
    mysqli_stmt_bind_param($stmt2, "s", $id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    
    if (mysqli_num_rows($result2) > 0) {
        echo "<script>
                alert('이미 사용 중인 아이디입니다. (기존 회원)');
                history.back();
              </script>";
        exit;
    }
    
    // 비밀번호 해시
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
    
    // users 테이블에 삽입
    $insert_users = "INSERT INTO users (
        username, password, name, email, phone, hendphone, 
        member_id, old_password, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt3 = mysqli_prepare($db, $insert_users);
    mysqli_stmt_bind_param($stmt3, "ssssssss", 
        $id, $hashed_password, $name, $email, $phone, $hendphone, $id, $pass
    );
    
    if (mysqli_stmt_execute($stmt3)) {
        // 호환성을 위해 member 테이블에도 추가
        $insert_member = "INSERT INTO member (
            id, pass, name, email, phone1, phone2, phone3, 
            hendphone1, hendphone2, hendphone3, Logincount, EndLogin
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";
        
        $stmt4 = mysqli_prepare($db, $insert_member);
        mysqli_stmt_bind_param($stmt4, "ssssssssss", 
            $id, $pass, $name, $email, $phone1, $phone2, $phone3,
            $hendphone1, $hendphone2, $hendphone3
        );
        
        mysqli_stmt_execute($stmt4); // 실패해도 진행 (users 테이블이 메인)
        
        echo "<script>
                alert('회원가입이 완료되었습니다.\\n\\n로그인 페이지로 이동합니다.');
                location.href = 'login.php';
              </script>";
    } else {
        echo "<script>
                alert('회원가입 중 오류가 발생했습니다.\\n\\n다시 시도해주세요.');
                history.back();
              </script>";
    }
    
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($stmt2);
    mysqli_stmt_close($stmt3);
    if (isset($stmt4)) mysqli_stmt_close($stmt4);
    
} else {
    // POST가 아닌 경우 회원가입 페이지로 리다이렉트
    header("Location: join.php");
    exit;
}

mysqli_close($db);
?>