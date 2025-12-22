<?php
/**
 * 교정/검수 관리 클래스
 * 두손기획인쇄 - 주문 워크플로우 시스템
 *
 * @package DusonPrinting
 * @version 1.0
 */

require_once __DIR__ . '/OrderStatusManager.php';

class ProofreadingManager {
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
     * 교정본 업로드
     *
     * @param string $file_path 교정본 파일 경로
     * @param string $uploaded_by 업로드한 관리자 ID
     * @return bool|int 성공 시 proofreading ID, 실패 시 false
     */
    public function uploadProof($file_path, $uploaded_by = 'admin') {
        // 기존 교정본 조회 (버전 확인)
        $version = $this->getNextProofVersion();

        $stmt = $this->db->prepare("
            INSERT INTO order_proofreading
            (order_no, proof_file_path, proof_uploaded_at, proof_uploaded_by, proof_version)
            VALUES (?, ?, NOW(), ?, ?)
        ");

        $stmt->bind_param('issi',
            $this->order_no,
            $file_path,
            $uploaded_by,
            $version
        );

        if ($stmt->execute()) {
            $proof_id = $stmt->insert_id;

            // 주문 상태를 '교정본 확인 대기'로 변경
            $statusManager = new OrderStatusManager($this->db, $this->order_no);
            $statusManager->changeStatus('proof_ready', $uploaded_by, "교정본 버전 {$version} 업로드");

            // 교정본 확인 이메일 큐에 추가
            $this->sendProofReadyEmail($proof_id);

            return $proof_id;
        }

        return false;
    }

    /**
     * 고객 교정본 확인 처리
     *
     * @param int $proof_id 교정본 ID
     * @param string $confirmation 확인 상태 ('approved' 또는 'rejected')
     * @param string $feedback 고객 피드백
     * @return bool 성공 여부
     */
    public function customerConfirm($proof_id, $confirmation, $feedback = '') {
        if (!in_array($confirmation, ['approved', 'rejected'])) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE order_proofreading
            SET customer_confirmed = ?,
                customer_feedback = ?,
                customer_confirmed_at = NOW()
            WHERE id = ? AND order_no = ?
        ");

        $stmt->bind_param('ssii',
            $confirmation,
            $feedback,
            $proof_id,
            $this->order_no
        );

        if ($stmt->execute()) {
            // 상태에 따른 주문 상태 변경
            $statusManager = new OrderStatusManager($this->db, $this->order_no);

            if ($confirmation === 'approved') {
                // 승인 → 제작 중으로 상태 변경
                $statusManager->changeStatus('proof_approved', 'customer', '고객 교정본 승인');
            } else {
                // 거부 → 교정본 준비 중으로 상태 변경
                $statusManager->changeStatus('proof_preparing', 'customer', '고객 교정본 수정 요청: ' . $feedback);
            }

            return true;
        }

        return false;
    }

    /**
     * 현재 교정본 정보 조회
     *
     * @return array|null 교정본 정보
     */
    public function getCurrentProof() {
        $stmt = $this->db->prepare("
            SELECT * FROM order_proofreading
            WHERE order_no = ?
            ORDER BY proof_version DESC
            LIMIT 1
        ");

        $stmt->bind_param('i', $this->order_no);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * 모든 교정본 이력 조회
     *
     * @return array 교정본 목록
     */
    public function getAllProofs() {
        $stmt = $this->db->prepare("
            SELECT * FROM order_proofreading
            WHERE order_no = ?
            ORDER BY proof_version DESC
        ");

        $stmt->bind_param('i', $this->order_no);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 다음 교정 버전 번호 가져오기
     *
     * @return int 다음 버전 번호
     */
    private function getNextProofVersion() {
        $stmt = $this->db->prepare("
            SELECT MAX(proof_version) as max_version
            FROM order_proofreading
            WHERE order_no = ?
        ");

        $stmt->bind_param('i', $this->order_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return ($row['max_version'] ?? 0) + 1;
    }

    /**
     * 교정본 확인 대기 중인지 확인
     *
     * @return bool 대기 여부
     */
    public function isPendingConfirmation() {
        $proof = $this->getCurrentProof();

        return $proof && $proof['customer_confirmed'] === 'pending';
    }

    /**
     * 교정본 승인 여부 확인
     *
     * @return bool 승인 여부
     */
    public function isApproved() {
        $proof = $this->getCurrentProof();

        return $proof && $proof['customer_confirmed'] === 'approved';
    }

    /**
     * 교정본 통계 조회
     *
     * @return array 통계 정보
     */
    public function getProofStatistics() {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_versions,
                SUM(CASE WHEN customer_confirmed = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN customer_confirmed = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN customer_confirmed = 'pending' THEN 1 ELSE 0 END) as pending_count
            FROM order_proofreading
            WHERE order_no = ?
        ");

        $stmt->bind_param('i', $this->order_no);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * 고객용 교정본 확인 링크 생성
     *
     * @param int $proof_id 교정본 ID
     * @return string 확인 링크
     */
    public function generateProofLink($proof_id) {
        // 보안 토큰 생성
        $token = bin2hex(random_bytes(32));

        // 토큰을 데이터베이스에 저장 (선택사항)
        // 실제로는 JWT나 별도 토큰 테이블 사용 권장

        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        return $base_url . "/customer/proof_confirm.php?order=" . $this->order_no . "&proof=" . $proof_id . "&token=" . $token;
    }

    /**
     * 교정본 파일 경로 검증
     *
     * @param string $file_path 파일 경로
     * @return bool 유효 여부
     */
    public function validateProofFile($file_path) {
        // 파일 존재 여부 확인
        if (!file_exists($file_path)) {
            return false;
        }

        // 파일 크기 확인 (예: 최대 50MB)
        $max_size = 50 * 1024 * 1024;
        if (filesize($file_path) > $max_size) {
            return false;
        }

        // 허용된 확장자 확인
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'ai', 'psd'];
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        return in_array($extension, $allowed_extensions);
    }

    /**
     * 주문 번호 설정
     *
     * @param int $order_no 주문 번호
     */
    public function setOrderNo($order_no) {
        $this->order_no = $order_no;
    }

    /**
     * 교정본 준비 완료 이메일 발송
     *
     * @param int $proof_id 교정본 ID
     * @return bool 성공 여부
     */
    private function sendProofReadyEmail($proof_id) {
        // 주문 정보 조회
        $stmt = mysqli_prepare($this->db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
        mysqli_stmt_bind_param($stmt, 'i', $this->order_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$order) {
            return false;
        }

        // 교정본 확인 링크 생성
        $proof_link = $this->generateProofLink($proof_id);

        // 이메일 큐에 추가
        $stmt = mysqli_prepare($this->db,
            "INSERT INTO order_email_log
             (order_no, email_type, recipient, subject, body, sent_status, created_at)
             VALUES (?, 'proof_ready', ?, '', '', 'pending', NOW())"
        );

        $recipient = $order['email'] ?? '';
        mysqli_stmt_bind_param($stmt, 'is', $this->order_no, $recipient);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }
}
