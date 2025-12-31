<?php
/**
 * 회원가입 페이지
 * 신규 users 테이블 기반 회원가입 시스템
 */

session_start();

// 이미 로그인된 사용자는 메인으로 리다이렉트
if (isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok'])) {
    echo "<script>
            alert('이미 로그인되어 있습니다.');
            location.href = '/';
          </script>";
    exit;
}

// 데이터베이스 연결
include "../db.php";

// 아이디 중복 체크 (legacy support for old join.php?id=xxx pattern)
$id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : '';

if ($id) {
    // users 테이블에서 확인
    $query = "SELECT username FROM users WHERE username = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>
                alert('$id 는 이미 등록되어 있는 아이디입니다.');
                history.go(-1);
              </script>";
        exit;
    }
    mysqli_stmt_close($stmt);
}

// 회원가입 폼 설정
$action = "register_process.php";

// 회원가입 폼 표시
include "form.php";
?>
