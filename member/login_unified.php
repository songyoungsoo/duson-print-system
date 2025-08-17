<?php
/**
 * 통합 로그인 처리 시스템
 * users 테이블 기반 (기존 member 테이블과 호환)
 */

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mode = $_POST['mode'] ?? '';
    
    if ($mode == "member_login") {
        include "../db.php";
        
        $id = mysqli_real_escape_string($db, $_POST['id'] ?? '');
        $pass = $_POST['pass'] ?? '';
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '/';
        
        if (empty($id) || empty($pass)) {
            echo "<script>
                    alert('아이디와 비밀번호를 입력해주세요.');
                    history.back();
                  </script>";
            exit;
        }
        
        // 1. 신규 users 테이블에서 확인
        $query = "SELECT * FROM users WHERE username = ? OR member_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ss", $id, $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            $login_success = false;
            
            // 해시된 비밀번호 확인
            if (password_verify($pass, $user['password'])) {
                $login_success = true;
            }
            // 기존 평문 비밀번호 확인 (호환성)
            elseif (!empty($user['old_password']) && $pass === $user['old_password']) {
                $login_success = true;
                
                // 비밀번호를 해시로 업데이트
                $new_hash = password_hash($pass, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($db, $update_query);
                mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
            
            if ($login_success) {
                // 로그인 통계 업데이트
                $login_count = ($user['login_count'] ?? 0) + 1;
                $login_time = date("Y-m-d H:i:s");
                
                $update_stats = "UPDATE users SET login_count = ?, last_login = ? WHERE id = ?";
                $stats_stmt = mysqli_prepare($db, $update_stats);
                mysqli_stmt_bind_param($stats_stmt, "isi", $login_count, $login_time, $user['id']);
                mysqli_stmt_execute($stats_stmt);
                mysqli_stmt_close($stats_stmt);
                
                // 세션 설정 (양쪽 시스템 호환)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['name'];
                
                // 기존 시스템 호환
                $_SESSION['id_login_ok'] = array(
                    'id' => $user['member_id'] ?: $user['username'],
                    'pass' => $pass
                );
                
                setcookie("id_login_ok", $user['member_id'] ?: $user['username'], 0, "/");
                
                echo "<script>
                        alert('정상적으로 로그인 되셨습니다.\\n\\n좋은 하루 되시기를 바랍니다.....*^^*');
                        " . (!empty($redirect) ? "location.href = '$redirect';" : "location.href = '../';") . "
                      </script>";
                exit;
            }
        }
        
        // 2. 로그인 실패 시 기존 member 테이블에서 확인 (fallback)
        $member_query = "SELECT * FROM member WHERE id = ?";
        $member_stmt = mysqli_prepare($db, $member_query);
        mysqli_stmt_bind_param($member_stmt, "s", $id);
        mysqli_stmt_execute($member_stmt);
        $member_result = mysqli_stmt_get_result($member_stmt);
        
        if ($member = mysqli_fetch_assoc($member_result)) {
            if ($pass === $member['pass']) {
                // member 테이블 데이터를 users로 마이그레이션
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                
                $migrate_query = "INSERT INTO users (username, password, name, email, phone, member_id, old_password, login_count, last_login) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $migrate_stmt = mysqli_prepare($db, $migrate_query);
                $login_count = ($member['Logincount'] ?? 0) + 1;
                $last_login = date("Y-m-d H:i:s");
                
                mysqli_stmt_bind_param($migrate_stmt, "sssssssis", 
                    $member['id'], $hashed_password, $member['name'], 
                    $member['email'], $member['phone'], $member['id'], 
                    $member['pass'], $login_count, $last_login
                );
                
                if (mysqli_stmt_execute($migrate_stmt)) {
                    // 세션 설정
                    $_SESSION['user_id'] = mysqli_insert_id($db);
                    $_SESSION['username'] = $member['id'];
                    $_SESSION['user_name'] = $member['name'];
                    $_SESSION['id_login_ok'] = array('id' => $member['id'], 'pass' => $pass);
                    
                    setcookie("id_login_ok", $member['id'], 0, "/");
                    
                    // member 테이블 업데이트
                    $update_member = "UPDATE member SET Logincount = ?, EndLogin = ? WHERE id = ?";
                    $update_stmt = mysqli_prepare($db, $update_member);
                    mysqli_stmt_bind_param($update_stmt, "iss", $login_count, $last_login, $member['id']);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                    
                    echo "<script>
                            alert('정상적으로 로그인 되셨습니다.\\n\\n(계정이 새 시스템으로 이전되었습니다)');
                            " . (!empty($redirect) ? "location.href = '$redirect';" : "location.href = '../';") . "
                          </script>";
                    exit;
                }
                mysqli_stmt_close($migrate_stmt);
            }
        }
        
        mysqli_stmt_close($member_stmt);
        mysqli_stmt_close($stmt);
        
        // 로그인 실패
        echo "<script>
                alert('아이디 또는 비밀번호가 올바르지 않습니다.');
                history.back();
              </script>";
        exit;
    }
}

// GET 요청이거나 잘못된 접근
echo "<script>
        alert('잘못된 접근입니다.');
        location.href = 'login.php';
      </script>";
?>