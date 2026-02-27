<?php
/**
 * EmailNotification - 이메일 알림 헬퍼 클래스
 * 
 * 교정 확인 워크플로우에 대한 이메일 알림 전송
 * mailer.lib.php (네이버 SMTP)를 통해 발송
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

// 네이버 SMTP 발송 함수 로드
require_once __DIR__ . '/../mlangorder_printauto/mailer.lib.php';
class EmailNotification {
    private $from_email;
    private $from_name;
    private $admin_email;
    
    public function __construct() {
        $this->from_email = 'noreply@dsp114.com';
        $this->from_name = '두손기획인쇄';
        $this->admin_email = 'dsp1830@naver.com';
    }
    
    /**
     * 교정 요청 생성 알림 (고객에게)
     * 
     * @param string $customer_email 고객 이메일
     * @param string $customer_name 고객 이름
     * @param string $order_no 주문번호
     * @param string $product_type 제품 타입
     * @return bool
     */
    public function sendProofRequestNotification($customer_email, $customer_name, $order_no, $product_type) {
        if (empty($customer_email)) {
            error_log("EmailNotification: 고객 이메일이 없습니다.");
            return false;
        }
        
        $subject = "[두손기획인쇄] 주문 접수 및 교정 확인 안내 - 주문번호: {$order_no}";
        
        $message = "
안녕하세요, {$customer_name}님.

두손기획인쇄를 이용해 주셔서 감사합니다.

주문번호 {$order_no}가 정상적으로 접수되었습니다.

【주문 정보】
- 주문번호: {$order_no}
- 제품: {$product_type}

업로드하신 파일은 현재 관리자가 검토 중입니다.
파일 검토가 완료되면 교정 승인 또는 수정 요청 안내를 드리겠습니다.

마이페이지에서 교정 상태를 확인하실 수 있습니다:
https://dsp114.com/mypage/proof.php

감사합니다.

--
두손기획인쇄
https://dsp114.com
";
        
        return $this->send($customer_email, $subject, $message);
    }
    
    /**
     * 교정 승인 알림 (고객에게)
     * 
     * @param string $customer_email 고객 이메일
     * @param string $customer_name 고객 이름
     * @param string $order_no 주문번호
     * @param string $admin_comment 관리자 코멘트
     * @return bool
     */
    public function sendProofApprovedNotification($customer_email, $customer_name, $order_no, $admin_comment = '') {
        if (empty($customer_email)) {
            error_log("EmailNotification: 고객 이메일이 없습니다.");
            return false;
        }
        
        $subject = "[두손기획인쇄] 교정 승인 안내 - 주문번호: {$order_no}";
        
        $comment_section = '';
        if (!empty($admin_comment)) {
            $comment_section = "\n【관리자 메모】\n{$admin_comment}\n";
        }
        
        $message = "
안녕하세요, {$customer_name}님.

주문번호 {$order_no}의 교정이 승인되었습니다.
{$comment_section}
곧 인쇄 작업에 들어가며, 완료 후 배송해 드리겠습니다.

진행 상황은 마이페이지에서 확인하실 수 있습니다:
https://dsp114.com/mypage/orders.php

감사합니다.

--
두손기획인쇄
https://dsp114.com
";
        
        return $this->send($customer_email, $subject, $message);
    }
    
    /**
     * 수정 요청 알림 (고객에게)
     * 
     * @param string $customer_email 고객 이메일
     * @param string $customer_name 고객 이름
     * @param string $order_no 주문번호
     * @param string $admin_comment 수정 요청 사유
     * @return bool
     */
    public function sendRevisionRequestNotification($customer_email, $customer_name, $order_no, $admin_comment) {
        if (empty($customer_email)) {
            error_log("EmailNotification: 고객 이메일이 없습니다.");
            return false;
        }
        
        $subject = "[두손기획인쇄] 파일 수정 요청 - 주문번호: {$order_no}";
        
        $message = "
안녕하세요, {$customer_name}님.

주문번호 {$order_no}의 파일 검토 결과, 일부 수정이 필요합니다.

【수정 요청 사유】
{$admin_comment}

마이페이지에서 수정본을 업로드해 주시기 바랍니다:
https://dsp114.com/mypage/proof.php

수정본 업로드 후 다시 검토해 드리겠습니다.

감사합니다.

--
두손기획인쇄
https://dsp114.com
";
        
        return $this->send($customer_email, $subject, $message);
    }
    
    /**
     * 수정본 제출 알림 (관리자에게)
     *
     * @param string $customer_name 고객 이름
     * @param string $order_no 주문번호
     * @param string $product_type 제품 타입
     * @return bool
     */
    public function sendRevisionSubmittedNotification($customer_name, $order_no, $product_type) {
        $subject = "[두손기획인쇄 관리자] 수정본 제출 알림 - 주문번호: {$order_no}";

        $message = "
고객이 수정본을 제출했습니다.

【주문 정보】
- 주문번호: {$order_no}
- 고객명: {$customer_name}
- 제품: {$product_type}

교정 관리 페이지에서 확인하세요:
https://dsp114.com/admin/mlangprintauto/proof_manager.php

--
두손기획인쇄 시스템
";

        return $this->send($this->admin_email, $subject, $message);
    }

    /**
     * 견적서 → 주문 전환 알림 (고객에게)
     *
     * @param string $customer_email 고객 이메일
     * @param string $customer_name 고객 이름
     * @param string $quote_no 견적 번호
     * @param array $order_numbers 생성된 주문 번호 배열
     * @param int $order_count 생성된 주문 개수
     * @param int $total_amount 총 금액
     * @return bool
     */
    public function sendOrderConvertedNotification($customer_email, $customer_name, $quote_no, $order_numbers, $order_count, $total_amount = 0) {
        if (empty($customer_email)) {
            error_log("EmailNotification: 고객 이메일이 없습니다.");
            return false;
        }

        $subject = "[두손기획인쇄] 견적서 주문 전환 완료 - 견적번호: {$quote_no}";

        $order_list = '';
        foreach ($order_numbers as $index => $order_no) {
            $order_list .= "  " . ($index + 1) . ". 주문번호: {$order_no}\n";
        }

        $amount_section = '';
        if ($total_amount > 0) {
            $amount_section = "\n총 주문금액: " . number_format($total_amount) . "원 (VAT 포함)";
        }

        $message = "
안녕하세요, {$customer_name}님.

견적서가 정상적으로 주문으로 전환되었습니다.

【견적 정보】
- 견적번호: {$quote_no}

【주문 정보】
- 생성된 주문: {$order_count}건{$amount_section}

{$order_list}
주문 상세 내역은 마이페이지에서 확인하실 수 있습니다:
https://dsp114.com/mypage/orders.php

곧 담당자가 주문을 확인하고 인쇄 작업을 시작하겠습니다.
파일 교정이 필요한 경우 별도로 안내드리겠습니다.

문의사항이 있으시면 언제든지 연락 주시기 바랍니다.

감사합니다.

--
두손기획인쇄
https://dsp114.com
전화: 02-2632-1830
";

        return $this->send($customer_email, $subject, $message);
    }
    
    /**
     * 이메일 전송 (내부 메서드)
     * 
     * @param string $to 받는 사람 이메일
     * @param string $subject 제목
     * @param string $message 본문
     * @return bool
     */
    private function send($to, $subject, $message) {
        try {
            // mailer.lib.php의 mailer() 함수 사용 (네이버 SMTP)
            // mailer($fname, $fmail, $to, $subject, $content, $type, $file, $cc, $bcc)
            // type=1: HTML, type=0: text
            // 본문을 HTML로 변환 (줄바꿈 → <br>)
            $htmlMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
            
            // 출력 버퍼링 — mailer()가 내부에서 echo로 디버그 메시지를 출력함
            ob_start();
            $result = @mailer(
                $this->from_name,
                'dsp1830@naver.com',
                $to,
                $subject,
                $htmlMessage,
                1,    // HTML 형식
                '',   // 첨부파일 없음
                '',   // CC 없음
                ''    // BCC 없음
            );
            ob_end_clean();
            
            if ($result) {
                error_log("EmailNotification: 이메일 전송 성공 (SMTP): to={$to}, subject={$subject}");
            } else {
                error_log("EmailNotification: 이메일 전송 실패 (SMTP): to={$to}, subject={$subject}");
            }
            
            return (bool)$result;
        } catch (Exception $e) {
            error_log("EmailNotification: 이메일 전송 예외: " . $e->getMessage());
            return false;
        }
    }
}
?>
