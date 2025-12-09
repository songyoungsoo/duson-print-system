<?php
/**
 * 주문 취소 API
 * 경로: /api/orders/cancel.php
 * 기능: 주문 상태를 '취소'로 변경
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

// 주문 상태 확인 (결제대기, 접수 상태만 취소 가능)
$check_query = "SELECT OrderStyle FROM mlangorder_printauto WHERE no = ? AND name = ?";
$check_stmt = mysqli_prepare($db, $check_query);

if ($check_stmt) {
    mysqli_stmt_bind_param($check_stmt, "is", $order_no, $user_name);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $order = mysqli_fetch_assoc($result);
    
    if (!$order) {
        echo "<script>alert('주문 정보를 찾을 수 없습니다.'); history.back();</script>";
        exit;
    }
    
    // 취소 가능한 상태인지 확인
    $cancellable_status = ['결제대기', '접수', 'no'];
    if (!in_array($order['OrderStyle'], $cancellable_status)) {
        echo "<script>alert('현재 상태에서는 취소할 수 없습니다.\\n결제대기 또는 접수 상태에서만 취소 가능합니다.'); history.back();</script>";
        exit;
    }
    
    // 주문 취소 처리
    $update_query = "UPDATE mlangorder_printauto SET OrderStyle = '취소' WHERE no = ? AND name = ?";
    $update_stmt = mysqli_prepare($db, $update_query);
    
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "is", $order_no, $user_name);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo "<script>
                alert('주문이 취소되었습니다.');
                window.location.href = '/account/orders.php';
            </script>";
        } else {
            echo "<script>alert('주문 취소 중 오류가 발생했습니다.'); history.back();</script>";
        }
        
        mysqli_stmt_close($update_stmt);
    } else {
        echo "<script>alert('취소 처리 중 오류가 발생했습니다.'); history.back();</script>";
    }
    
    mysqli_stmt_close($check_stmt);
} else {
    echo "<script>alert('데이터베이스 오류가 발생했습니다.'); history.back();</script>";
}

mysqli_close($db);
?>