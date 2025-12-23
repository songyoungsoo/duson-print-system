<?php
/**
 * 재주문 API
 * 경로: /api/orders/reorder.php
 * 기능: 기존 주문을 복사하여 새로운 주문 생성
 */

session_start();
include "../../db.php";

// 로그인 체크
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

if (!$is_logged_in) {
    header('Location: /member/login.php');
    exit;
}

// 사용자 정보
if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_name'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
} elseif (isset($_COOKIE['id_login_ok'])) {
    $user_name = $_COOKIE['id_login_ok'];
}

// 주문번호 파라미터
$order_no = $_GET['order_no'] ?? $_POST['order_no'] ?? '';

if (empty($order_no)) {
    echo "<script>alert('주문번호가 없습니다.'); history.back();</script>";
    exit;
}

// 기존 주문 정보 조회
$query = "SELECT * FROM mlangorder_printauto WHERE no = ? AND name = ?";
$stmt = mysqli_prepare($db, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "is", $order_no, $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $original_order = mysqli_fetch_assoc($result);
    
    if (!$original_order) {
        echo "<script>alert('주문 정보를 찾을 수 없습니다.'); history.back();</script>";
        exit;
    }
    
    // 새 주문 생성 (최소 필수 필드만)
    $insert_query = "INSERT INTO mlangorder_printauto (
        Type, name, money_1, date, OrderStyle
    ) VALUES (
        ?, ?, ?, NOW(), '결제대기'
    )";
    
    $insert_stmt = mysqli_prepare($db, $insert_query);
    
    if ($insert_stmt) {
        // 바인딩 파라미터 준비 (최소 필수 필드만)
        $type = $original_order['Type'] ?? '명함';
        $name = $original_order['name'] ?? $user_name;
        $money = $original_order['money_1'] ?? '50000';
        
        mysqli_stmt_bind_param($insert_stmt, 
            "sss",
            $type,
            $name,
            $money
        );
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $new_order_no = mysqli_insert_id($db);
            
            // 성공 메시지와 함께 주문 상세 페이지로 이동
            echo "<script>
                alert('재주문이 생성되었습니다. 주문번호: " . $new_order_no . "');
                window.location.href = '/account/orders/detail.php?order_no=" . $new_order_no . "';
            </script>";
        } else {
            $error = mysqli_error($db);
            $stmt_error = mysqli_stmt_error($insert_stmt);
            echo "<script>alert('재주문 생성 중 오류가 발생했습니다:\\nMySQL Error: " . addslashes($error) . "\\nStatement Error: " . addslashes($stmt_error) . "'); history.back();</script>";
        }
        
        mysqli_stmt_close($insert_stmt);
    } else {
        echo "<script>alert('재주문 처리 중 오류가 발생했습니다.'); history.back();</script>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "<script>alert('데이터베이스 오류가 발생했습니다.'); history.back();</script>";
}

mysqli_close($db);
?>