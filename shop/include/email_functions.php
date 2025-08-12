<?php
// 이메일 발송 관련 함수들
require_once('mail/mailer.lib.php');

function sendOrderEmail($order_id, $items, $customer_info) {
    if (!is_array($customer_info) || !is_array($items)) {
        error_log("Invalid parameters for sendOrderEmail");
        return false;
    }

    try {
        // 고객용 이메일
        $customer_subject = "[두손기획] 주문이 완료되었습니다 (주문번호: {$order_id})";
        $customer_body = generateCustomerEmailBody($order_id, $items, $customer_info);
        
        if (!empty($customer_info['customer_email'])) {
            mailer(
                "두손기획",
                "dsp1830@naver.com",
                $customer_info['customer_email'],
                $customer_subject,
                $customer_body,
                1, "", "", ""
            );
        }

        // 관리자용 이메일
        $admin_subject = "[새 주문] 주문번호: {$order_id}";
        $admin_body = generateAdminEmailBody($order_id, $items, $customer_info);
        
        mailer(
            "두손기획",
            "dsp1830@naver.com",
            "dsp1830@naver.com",
            $admin_subject,
            $admin_body,
            1, "", "", ""
        );

        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

function generateCustomerEmailBody($order_id, $items, $customer_info) {
    ob_start();
    include 'email_templates/customer_order.php';
    return ob_get_clean();
}

function generateAdminEmailBody($order_id, $items, $customer_info) {
    ob_start();
    include 'email_templates/admin_order.php';
    return ob_get_clean();
}
