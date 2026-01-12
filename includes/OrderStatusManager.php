<?php
/**
 * 주문 상태 관리 클래스
 * 두손기획인쇄 - 주문 워크플로우 시스템
 *
 * @package DusonPrinting
 * @version 1.0
 */

class OrderStatusManager {
    private $db;
    private $order_no;

    /**
     * 생성자
     *
     * @param mysqli $db 데이터베이스 연결
     * @param int $order_no 주문 번호
     */
    public function __construct($db, $order_no = null) {
        $this->db = $db;
        $this->order_no = $order_no;
    }

    /**
     * 주문 상태 변경
     *
     * @param string $new_status 새로운 상태 코드
     * @param string $changed_by 변경자 (관리자 ID)
     * @param string $reason 변경 사유
     * @return bool 성공 여부
     */
    public function changeStatus($new_status, $changed_by = 'system', $reason = '') {
        if (!$this->order_no) {
            return false;
        }

        // 현재 상태 조회
        $current_status = $this->getCurrentStatus();

        // 상태 코드 유효성 검사
        if (!$this->isValidStatus($new_status)) {
            return false;
        }

        // 트랜잭션 시작
        mysqli_begin_transaction($this->db);

        try {
            // 1. 주문 상태 업데이트
            $stmt = $this->db->prepare("
                UPDATE mlangorder_printauto
                SET OrderStyle = ?,
                    updated_at = NOW()
                WHERE no = ?
            ");
            $stmt->bind_param('si', $new_status, $this->order_no);
            $stmt->execute();

            // 2. 상태 변경 히스토리 기록
            $this->logStatusChange($current_status, $new_status, $changed_by, $reason);

            // 3. 특정 상태에 따른 추가 처리
            $this->handleStatusSpecificActions($new_status);

            // 커밋
            mysqli_commit($this->db);

            return true;

        } catch (Exception $e) {
            // 롤백
            mysqli_rollback($this->db);
            error_log("주문 상태 변경 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 현재 주문 상태 조회
     *
     * @return string|null 현재 상태 코드
     */
    public function getCurrentStatus() {
        $stmt = $this->db->prepare("SELECT OrderStyle FROM mlangorder_printauto WHERE no = ?");
        $stmt->bind_param('i', $this->order_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['OrderStyle'];
        }

        return null;
    }

    /**
     * 주문 상태 상세 정보 조회
     *
     * @return array|null 상태 정보
     */
    public function getStatusDetails() {
        $status = $this->getCurrentStatus();

        if (!$status) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM order_status_master WHERE status_code = ?
        ");
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * 주문 상태 히스토리 조회
     *
     * @return array 상태 변경 이력
     */
    public function getStatusHistory() {
        $stmt = $this->db->prepare("
            SELECT h.*, m.status_name_ko
            FROM order_status_history h
            LEFT JOIN order_status_master m ON h.new_status = m.status_code
            WHERE h.order_no = ?
            ORDER BY h.changed_at DESC
        ");
        $stmt->bind_param('i', $this->order_no);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 모든 가능한 주문 상태 목록 조회
     *
     * @return array 상태 목록
     */
    public static function getAllStatuses($db) {
        $result = mysqli_query($db, "
            SELECT * FROM order_status_master
            ORDER BY status_order
        ");

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    /**
     * 상태 코드 유효성 검사
     *
     * @param string $status_code 상태 코드
     * @return bool 유효 여부
     */
    private function isValidStatus($status_code) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM order_status_master WHERE status_code = ?
        ");
        $stmt->bind_param('s', $status_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['count'] > 0;
    }

    /**
     * 상태 변경 히스토리 기록
     *
     * @param string $old_status 이전 상태
     * @param string $new_status 새로운 상태
     * @param string $changed_by 변경자
     * @param string $reason 변경 사유
     */
    private function logStatusChange($old_status, $new_status, $changed_by, $reason) {
        $stmt = $this->db->prepare("
            INSERT INTO order_status_history
            (order_no, old_status, new_status, changed_by, change_reason)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('issss',
            $this->order_no,
            $old_status,
            $new_status,
            $changed_by,
            $reason
        );
        $stmt->execute();
    }

    /**
     * 특정 상태에 따른 추가 처리
     *
     * @param string $status 상태 코드
     */
    private function handleStatusSpecificActions($status) {
        // 주문 정보 조회
        $order = $this->getOrderInfo();

        switch ($status) {
            case 'payment_confirmed':
                // 결제 확인 알림
                $this->sendNotification('payment_complete', $order);
                break;

            case 'proof_ready':
                // 교정본 준비 완료 알림
                $proofUrl = 'https://dsp1830.shop/proof/view.php?order=' . $this->order_no;
                $this->sendProofRequestNotification($order, $proofUrl);
                break;

            case 'in_production':
                // 제작 시작 알림
                $this->sendNotification('production_start', $order);
                break;

            case 'shipped':
                // 배송 시작 시간 기록
                $stmt = $this->db->prepare("
                    UPDATE mlangorder_printauto
                    SET shipped_at = NOW()
                    WHERE no = ?
                ");
                $stmt->bind_param('i', $this->order_no);
                $stmt->execute();

                // 배송 시작 알림
                $this->sendShippingNotification($order);
                break;

            case 'delivered':
                // 배송 완료 시간 기록
                $stmt = $this->db->prepare("
                    UPDATE mlangorder_printauto
                    SET delivered_at = NOW()
                    WHERE no = ?
                ");
                $stmt->bind_param('i', $this->order_no);
                $stmt->execute();

                // 배송 완료 알림
                $this->sendNotification('delivery_complete', $order);
                break;
        }
    }

    /**
     * 주문 정보 조회
     */
    private function getOrderInfo() {
        $stmt = $this->db->prepare("
            SELECT no, name, Hendphone, email, money_4, courier, tracking_number
            FROM mlangorder_printauto WHERE no = ?
        ");
        $stmt->bind_param('i', $this->order_no);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * 알림 서비스를 통한 알림 발송
     */
    private function sendNotification($type, $order) {
        if (empty($order['Hendphone'])) return;

        try {
            require_once __DIR__ . '/services/NotificationService.php';
            $notification = new NotificationService($this->db);

            switch ($type) {
                case 'payment_complete':
                    $notification->sendPaymentComplete(
                        $this->order_no,
                        $order['Hendphone'],
                        $order['name'],
                        $order['money_4'],
                        '결제'
                    );
                    break;

                case 'production_start':
                    $notification->send([
                        'type' => NotificationService::TYPE_KAKAO_ALIMTALK,
                        'template' => NotificationService::TEMPLATE_PRODUCTION_START,
                        'phone' => $order['Hendphone'],
                        'message' => "[두손기획] 제작 시작 안내\n\n{$order['name']}님, 주문번호 {$this->order_no}의 제작이 시작되었습니다.\n\n제작 완료 후 배송 안내 드리겠습니다.\n\n감사합니다.",
                        'order_id' => $this->order_no
                    ]);
                    break;

                case 'delivery_complete':
                    $notification->send([
                        'type' => NotificationService::TYPE_KAKAO_ALIMTALK,
                        'template' => NotificationService::TEMPLATE_DELIVERY_COMPLETE,
                        'phone' => $order['Hendphone'],
                        'message' => "[두손기획] 배송 완료\n\n{$order['name']}님, 주문하신 상품이 배송 완료되었습니다.\n\n주문번호: {$this->order_no}\n\n이용해 주셔서 감사합니다.",
                        'order_id' => $this->order_no
                    ]);
                    break;
            }
        } catch (Exception $e) {
            error_log("[OrderStatusManager] 알림 발송 실패: " . $e->getMessage());
        }
    }

    /**
     * 교정 요청 알림 발송
     */
    private function sendProofRequestNotification($order, $proofUrl) {
        if (empty($order['Hendphone'])) return;

        try {
            require_once __DIR__ . '/services/NotificationService.php';
            $notification = new NotificationService($this->db);
            $notification->sendProofRequest(
                $this->order_no,
                $order['Hendphone'],
                $order['name'],
                $proofUrl
            );
        } catch (Exception $e) {
            error_log("[OrderStatusManager] 교정 요청 알림 실패: " . $e->getMessage());
        }
    }

    /**
     * 배송 시작 알림 발송
     */
    private function sendShippingNotification($order) {
        if (empty($order['Hendphone']) || empty($order['courier']) || empty($order['tracking_number'])) {
            return;
        }

        try {
            require_once __DIR__ . '/services/NotificationService.php';
            $notification = new NotificationService($this->db);
            $notification->sendShippingStart(
                $this->order_no,
                $order['Hendphone'],
                $order['name'],
                $order['courier'],
                $order['tracking_number']
            );
        } catch (Exception $e) {
            error_log("[OrderStatusManager] 배송 알림 실패: " . $e->getMessage());
        }
    }

    /**
     * 알림 대기열에 추가
     *
     * @param string $notification_type 알림 유형
     */
    private function queueNotification($notification_type) {
        // 주문 정보 조회
        $stmt = $this->db->prepare("
            SELECT email FROM mlangorder_printauto WHERE no = ?
        ");
        $stmt->bind_param('i', $this->order_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        if ($order && $order['email']) {
            // 이메일 로그에 기록 (pending 상태)
            $stmt = $this->db->prepare("
                INSERT INTO order_email_log
                (order_no, email_type, recipient_email, sent_status)
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->bind_param('iss',
                $this->order_no,
                $notification_type,
                $order['email']
            );
            $stmt->execute();
        }
    }

    /**
     * 다음 가능한 상태들 조회
     * (워크플로우 규칙에 따라)
     *
     * @return array 다음 가능한 상태 목록
     */
    public function getNextPossibleStatuses() {
        $current = $this->getCurrentStatus();

        // 워크플로우 규칙 정의
        $workflow_rules = [
            'pending' => ['payment_confirmed', 'cancelled'],
            'payment_confirmed' => ['file_checking', 'cancelled'],
            'file_checking' => ['proof_preparing', 'cancelled'],
            'proof_preparing' => ['proof_ready', 'cancelled'],
            'proof_ready' => ['proof_approved', 'proof_preparing', 'cancelled'],
            'proof_approved' => ['in_production', 'cancelled'],
            'in_production' => ['quality_check', 'cancelled'],
            'quality_check' => ['shipping_ready', 'in_production'],
            'shipping_ready' => ['shipped'],
            'shipped' => ['delivered'],
            'delivered' => ['completed'],
            'completed' => ['refunded'],
            'cancelled' => [],
            'refunded' => []
        ];

        $next_codes = $workflow_rules[$current] ?? [];

        // 상태 정보 조회
        if (empty($next_codes)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($next_codes), '?'));
        $stmt = $this->db->prepare("
            SELECT * FROM order_status_master
            WHERE status_code IN ($placeholders)
            ORDER BY status_order
        ");

        $types = str_repeat('s', count($next_codes));
        $stmt->bind_param($types, ...$next_codes);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 주문 번호 설정
     *
     * @param int $order_no 주문 번호
     */
    public function setOrderNo($order_no) {
        $this->order_no = $order_no;
    }
}
