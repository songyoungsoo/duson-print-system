<?php
/**
 * 교정보기 팝업 비밀번호 인증
 * 경로: sub/verify_popup.php
 */
session_start();
include "../db.php";
include "../includes/auth.php";

$response = ['success' => false, 'message' => ''];

// 관리자 로그인 체크
$is_admin = false;
if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == '1') {
    $is_admin = true;
} elseif (isset($_SESSION['username']) && $_SESSION['username'] == 'admin') {
    $is_admin = true;
} elseif (isset($_SESSION['level']) && $_SESSION['level'] == '1') {
    $is_admin = true;
}

if ($_POST && isset($_POST['order_no'])) {
    $order_no = intval($_POST['order_no']);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // 관리자는 비밀번호 확인 건너뛰기
    if ($is_admin) {
        $response['success'] = true;
        $response['redirect_url'] = '/mlangorder_printauto/WindowSian.php?mode=OrderView&no=' . $order_no;
        $response['message'] = '관리자 권한으로 접근합니다.';
    } else {
        // 일반 사용자는 비밀번호 확인
        if (empty($password)) {
            $response['message'] = '비밀번호를 입력해주세요.';
        } else {
            // 주문 정보 조회 (phone과 Hendphone 모두 확인)
            $query = "SELECT name, phone, Hendphone FROM mlangorder_printauto WHERE no = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "i", $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($order = mysqli_fetch_array($result)) {
                // 비밀번호 생성 규칙: 이름 + 전화번호 뒷자리 4자리
                $phone_last4 = '';
                $hendphone_last4 = '';
                
                // phone 필드에서 뒷자리 4자리 추출
                if (!empty($order['phone'])) {
                    $phone_last4 = substr(preg_replace('/[^0-9]/', '', $order['phone']), -4);
                }
                
                // Hendphone 필드에서 뒷자리 4자리 추출 (2025년 8월 이전 주문용)
                if (!empty($order['Hendphone'])) {
                    $hendphone_last4 = substr(preg_replace('/[^0-9]/', '', $order['Hendphone']), -4);
                }
                
                // 전화번호 뒷자리 4자리만 확인 (이름 없이)
                if ($password === $phone_last4 || $password === $hendphone_last4) {
                    $response['success'] = true;
                    $response['redirect_url'] = '/mlangorder_printauto/WindowSian.php?mode=OrderView&no=' . $order_no;
                } else {
                    $response['message'] = '전화번호 뒷자리 4자리가 일치하지 않습니다.';
                }
            } else {
                $response['message'] = '주문 정보를 찾을 수 없습니다.';
            }
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>