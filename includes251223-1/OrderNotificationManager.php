<?php
/**
 * 주문 알림 관리 클래스
 * 두손기획인쇄 - 이메일 알림 시스템
 *
 * @package DusonPrinting
 * @version 1.0
 */

require_once __DIR__ . '/../mlangorder_printauto/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../mlangorder_printauto/PHPMailer/SMTP.php';
require_once __DIR__ . '/../mlangorder_printauto/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OrderNotificationManager {
    private $db;
    private $mailer;

    /**
     * 생성자
     *
     * @param mysqli $db 데이터베이스 연결
     */
    public function __construct($db) {
        $this->db = $db;
        $this->initializeMailer();
    }

    /**
     * PHPMailer 초기화
     */
    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);

        // SMTP 설정 (운영 환경에 맞게 수정 필요)
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.example.com'; // SMTP 서버 주소
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'your-email@example.com'; // SMTP 사용자명
        $this->mailer->Password = 'your-password'; // SMTP 비밀번호
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->CharSet = 'UTF-8';

        // 발신자 정보
        $this->mailer->setFrom('noreply@dsp1830.shop', '두손기획인쇄');
    }

    /**
     * 대기 중인 이메일 발송
     *
     * @param int $limit 최대 발송 개수
     * @return array 발송 결과 통계
     */
    public function sendPendingEmails($limit = 10) {
        $result = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // 대기 중인 이메일 조회
        $stmt = $this->db->prepare("
            SELECT * FROM order_email_log
            WHERE sent_status = 'pending'
            ORDER BY created_at ASC
            LIMIT ?
        ");

        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $emails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($emails as $email_log) {
            try {
                $this->sendEmail($email_log);
                $result['sent']++;
            } catch (Exception $e) {
                $result['failed']++;
                $result['errors'][] = [
                    'email_id' => $email_log['id'],
                    'error' => $e->getMessage()
                ];

                // 에러 로그 업데이트
                $this->updateEmailLog($email_log['id'], 'failed', $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * 이메일 발송
     *
     * @param array $email_log 이메일 로그 정보
     * @throws Exception
     */
    private function sendEmail($email_log) {
        // 주문 정보 조회
        $order = $this->getOrderInfo($email_log['order_no']);

        if (!$order) {
            throw new Exception("주문 정보를 찾을 수 없습니다.");
        }

        // 이메일 템플릿 가져오기
        $template = $this->getEmailTemplate($email_log['email_type'], $order);

        // 이메일 설정
        $this->mailer->clearAddresses();
        $this->mailer->addAddress($email_log['recipient_email'], $order['name'] ?? '고객님');

        $this->mailer->isHTML(true);
        $this->mailer->Subject = $template['subject'];
        $this->mailer->Body = $template['body'];
        $this->mailer->AltBody = strip_tags($template['body']);

        // 발송
        $this->mailer->send();

        // 성공 로그 업데이트
        $this->updateEmailLog($email_log['id'], 'sent', null, $template['subject']);
    }

    /**
     * 주문 정보 조회
     *
     * @param int $order_no 주문 번호
     * @return array|null 주문 정보
     */
    private function getOrderInfo($order_no) {
        $stmt = $this->db->prepare("
            SELECT o.*, m.status_name_ko
            FROM mlangorder_printauto o
            LEFT JOIN order_status_master m ON o.OrderStyle = m.status_code
            WHERE o.no = ?
        ");

        $stmt->bind_param('i', $order_no);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * 이메일 템플릿 생성
     *
     * @param string $email_type 이메일 유형
     * @param array $order 주문 정보
     * @return array 템플릿 (subject, body)
     */
    private function getEmailTemplate($email_type, $order) {
        $customer_name = $order['name'] ?? '고객님';
        $order_no = $order['no'];
        $order_type = $order['Type'] ?? '';

        switch ($email_type) {
            case 'order_received':
                return [
                    'subject' => "[두손기획인쇄] 주문이 접수되었습니다 (주문번호: {$order_no})",
                    'body' => $this->renderTemplate('order_received', [
                        'customer_name' => $customer_name,
                        'order_no' => $order_no,
                        'order_type' => $order_type,
                        'order_date' => $order['date']
                    ])
                ];

            case 'payment_confirmed':
                return [
                    'subject' => "[두손기획인쇄] 입금이 확인되었습니다 (주문번호: {$order_no})",
                    'body' => $this->renderTemplate('payment_confirmed', [
                        'customer_name' => $customer_name,
                        'order_no' => $order_no,
                        'amount' => $order['money_2'] ?? $order['money_1']
                    ])
                ];

            case 'proof_ready':
                return [
                    'subject' => "[두손기획인쇄] 교정본이 준비되었습니다 (주문번호: {$order_no})",
                    'body' => $this->renderTemplate('proof_ready', [
                        'customer_name' => $customer_name,
                        'order_no' => $order_no,
                        'proof_link' => $this->getProofLink($order_no)
                    ])
                ];

            case 'in_production':
                return [
                    'subject' => "[두손기획인쇄] 제작이 시작되었습니다 (주문번호: {$order_no})",
                    'body' => $this->renderTemplate('in_production', [
                        'customer_name' => $customer_name,
                        'order_no' => $order_no
                    ])
                ];

            case 'shipped':
                return [
                    'subject' => "[두손기획인쇄] 제품이 발송되었습니다 (주문번호: {$order_no})",
                    'body' => $this->renderTemplate('shipped', [
                        'customer_name' => $customer_name,
                        'order_no' => $order_no,
                        'tracking_number' => $order['tracking_number'] ?? '배송준비중'
                    ])
                ];

            case 'delivered':
                return [
                    'subject' => "[두손기획인쇄] 배송이 완료되었습니다 (주문번호: {$order_no})",
                    'body' => $this->renderTemplate('delivered', [
                        'customer_name' => $customer_name,
                        'order_no' => $order_no
                    ])
                ];

            default:
                return [
                    'subject' => "[두손기획인쇄] 주문 알림 (주문번호: {$order_no})",
                    'body' => "주문 상태가 업데이트되었습니다."
                ];
        }
    }

    /**
     * 이메일 템플릿 렌더링
     *
     * @param string $template_name 템플릿 이름
     * @param array $data 템플릿 데이터
     * @return string 렌더링된 HTML
     */
    private function renderTemplate($template_name, $data) {
        $customer_name = $data['customer_name'] ?? '고객님';
        $order_no = $data['order_no'] ?? '';

        // 공통 헤더
        $html = <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
        .content { background: white; padding: 30px; margin-top: 20px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .btn { display: inline-block; padding: 12px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
        .info-box { background: #ecf0f1; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>두손기획인쇄</h1>
            <p>전문 인쇄 서비스</p>
        </div>
        <div class="content">
            <p><strong>{$customer_name}</strong>께,</p>
HTML;

        // 템플릿별 내용
        switch ($template_name) {
            case 'order_received':
                $html .= <<<HTML
            <p>주문해 주셔서 감사합니다!</p>
            <div class="info-box">
                <p><strong>주문번호:</strong> {$order_no}</p>
                <p><strong>주문일시:</strong> {$data['order_date']}</p>
                <p><strong>제품:</strong> {$data['order_type']}</p>
            </div>
            <p>입금 확인 후 제작을 시작하겠습니다.</p>
            <p>입금계좌: 농협 123-456-7890 (예금주: 두손기획)</p>
HTML;
                break;

            case 'payment_confirmed':
                $html .= <<<HTML
            <p>입금이 확인되었습니다.</p>
            <div class="info-box">
                <p><strong>주문번호:</strong> {$order_no}</p>
                <p><strong>입금액:</strong> {$data['amount']}원</p>
            </div>
            <p>곧 파일 검수 후 교정본을 보내드리겠습니다.</p>
HTML;
                break;

            case 'proof_ready':
                $html .= <<<HTML
            <p>교정본이 준비되었습니다!</p>
            <div class="info-box">
                <p><strong>주문번호:</strong> {$order_no}</p>
            </div>
            <p>아래 버튼을 클릭하여 교정본을 확인해주세요.</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['proof_link']}" class="btn">교정본 확인하기</a>
            </p>
            <p style="color: #e74c3c;">※ 교정본 확인 후 승인해주셔야 제작이 진행됩니다.</p>
HTML;
                break;

            case 'in_production':
                $html .= <<<HTML
            <p>제작이 시작되었습니다!</p>
            <div class="info-box">
                <p><strong>주문번호:</strong> {$order_no}</p>
            </div>
            <p>최상의 품질로 제작하여 빠르게 발송해드리겠습니다.</p>
HTML;
                break;

            case 'shipped':
                $html .= <<<HTML
            <p>제품이 발송되었습니다!</p>
            <div class="info-box">
                <p><strong>주문번호:</strong> {$order_no}</p>
                <p><strong>송장번호:</strong> {$data['tracking_number']}</p>
            </div>
            <p>빠른 시일 내에 받아보실 수 있습니다.</p>
HTML;
                break;

            case 'delivered':
                $html .= <<<HTML
            <p>배송이 완료되었습니다!</p>
            <div class="info-box">
                <p><strong>주문번호:</strong> {$order_no}</p>
            </div>
            <p>두손기획인쇄를 이용해주셔서 감사합니다.</p>
            <p>다음에도 더 나은 서비스로 보답하겠습니다.</p>
HTML;
                break;
        }

        // 공통 푸터
        $html .= <<<HTML
        </div>
        <div class="footer">
            <p>두손기획인쇄 | 전화: 02-2632-1830 | 이메일: dsp1830@naver.com</p>
            <p>서울시 영등포구 | www.dsp1830.shop</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * 교정본 확인 링크 생성
     *
     * @param int $order_no 주문 번호
     * @return string 링크
     */
    private function getProofLink($order_no) {
        // 최신 교정본 ID 조회
        $stmt = mysqli_prepare($this->db,
            "SELECT id FROM order_proofreading
             WHERE order_no = ?
             ORDER BY proof_version DESC
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'i', $order_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $proof_id = $row['id'] ?? 0;

        // 보안 토큰 생성 (간단한 버전 - 프로덕션에서는 DB 저장 권장)
        $token = bin2hex(random_bytes(16));

        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

        return $base_url . "/customer/proof_confirm.php?order=" . $order_no . "&proof=" . $proof_id . "&token=" . $token;
    }

    /**
     * 이메일 로그 업데이트
     *
     * @param int $log_id 로그 ID
     * @param string $status 발송 상태
     * @param string|null $error 에러 메시지
     * @param string|null $subject 제목
     */
    private function updateEmailLog($log_id, $status, $error = null, $subject = null) {
        if ($subject) {
            $stmt = $this->db->prepare("
                UPDATE order_email_log
                SET sent_status = ?,
                    sent_at = NOW(),
                    subject = ?,
                    error_message = ?
                WHERE id = ?
            ");
            $stmt->bind_param('sssi', $status, $subject, $error, $log_id);
        } else {
            $stmt = $this->db->prepare("
                UPDATE order_email_log
                SET sent_status = ?,
                    sent_at = NOW(),
                    error_message = ?
                WHERE id = ?
            ");
            $stmt->bind_param('ssi', $status, $error, $log_id);
        }

        $stmt->execute();
    }

    /**
     * 즉시 이메일 발송 (큐에 추가하지 않고 바로 발송)
     *
     * @param int $order_no 주문 번호
     * @param string $email_type 이메일 유형
     * @return bool 성공 여부
     */
    public function sendImmediately($order_no, $email_type) {
        $order = $this->getOrderInfo($order_no);

        if (!$order || !$order['email']) {
            return false;
        }

        // 임시 로그 생성
        $email_log = [
            'id' => 0,
            'order_no' => $order_no,
            'email_type' => $email_type,
            'recipient_email' => $order['email']
        ];

        try {
            $this->sendEmail($email_log);
            return true;
        } catch (Exception $e) {
            error_log("이메일 발송 실패: " . $e->getMessage());
            return false;
        }
    }
}
