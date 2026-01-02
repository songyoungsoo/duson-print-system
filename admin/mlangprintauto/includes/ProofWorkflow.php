<?php
/**
 * ProofWorkflow - 교정 확인 워크플로우 클래스
 * 
 * 교정 요청 생성, 승인, 수정 요청, 재업로드 처리
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

class ProofWorkflow {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * 주문 시 교정 요청 자동 생성
     * 
     * @param string $order_no 주문번호
     * @param string $product_type 제품 타입
     * @return bool
     */
    public function createProofRequest($order_no, $product_type) {
        $query = "INSERT INTO proof_status (order_no, product_type, status) VALUES (?, ?, 'pending')";
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log("ProofWorkflow createProofRequest 준비 실패: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ss", $order_no, $product_type);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            error_log("교정 요청 생성: order_no={$order_no}, product_type={$product_type}");
        }
        
        return $result;
    }
    
    /**
     * 교정 승인
     * 
     * @param int $proof_id proof_status ID
     * @param int $admin_id 관리자 user_id
     * @param string $comment 승인 코멘트 (선택)
     * @return bool
     */
    public function approveProof($proof_id, $admin_id, $comment = '') {
        $query = "UPDATE proof_status 
                  SET status = 'approved', 
                      admin_comment = ?, 
                      reviewed_by = ?, 
                      reviewed_at = NOW()
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log("ProofWorkflow approveProof 준비 실패: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sii", $comment, $admin_id, $proof_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            error_log("교정 승인: proof_id={$proof_id}, admin_id={$admin_id}");
        }
        
        return $result;
    }
    
    /**
     * 수정 요청
     * 
     * @param int $proof_id proof_status ID
     * @param int $admin_id 관리자 user_id
     * @param string $comment 수정 요청 사유
     * @return bool
     */
    public function requestRevision($proof_id, $admin_id, $comment) {
        if (empty($comment)) {
            error_log("ProofWorkflow requestRevision: 코멘트가 필요합니다.");
            return false;
        }
        
        $query = "UPDATE proof_status 
                  SET status = 'revision_requested', 
                      admin_comment = ?, 
                      reviewed_by = ?, 
                      reviewed_at = NOW()
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log("ProofWorkflow requestRevision 준비 실패: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sii", $comment, $admin_id, $proof_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            error_log("수정 요청: proof_id={$proof_id}, admin_id={$admin_id}");
        }
        
        return $result;
    }
    
    /**
     * 수정본 업로드
     * 
     * @param int $proof_id proof_status ID
     * @param array $files 업로드된 파일 정보 (JSON 형식)
     * @return bool
     */
    public function uploadRevision($proof_id, $files) {
        $files_json = json_encode($files, JSON_UNESCAPED_UNICODE);
        
        $query = "UPDATE proof_status 
                  SET status = 'revised', 
                      revision_files = ?
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log("ProofWorkflow uploadRevision 준비 실패: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $files_json, $proof_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            error_log("수정본 업로드: proof_id={$proof_id}");
        }
        
        return $result;
    }
    
    /**
     * 주문번호로 교정 상태 조회
     * 
     * @param string $order_no 주문번호
     * @return array|null
     */
    public function getProofByOrderNo($order_no) {
        $query = "SELECT ps.*, u.name as reviewer_name 
                  FROM proof_status ps
                  LEFT JOIN users u ON ps.reviewed_by = u.id
                  WHERE ps.order_no = ?
                  ORDER BY ps.created_at DESC
                  LIMIT 1";
        
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log("ProofWorkflow getProofByOrderNo 준비 실패: " . mysqli_error($this->db));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $order_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $proof = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $proof;
    }
    
    /**
     * 교정 대기 목록 조회 (관리자용)
     * 
     * @param string $status 상태 필터 (선택)
     * @param int $limit 조회 개수
     * @param int $offset 오프셋
     * @return array
     */
    public function getPendingProofs($status = 'pending', $limit = 20, $offset = 0) {
        if (!empty($status)) {
            $query = "SELECT ps.*, o.name, o.Hendphone, o.phone
                      FROM proof_status ps
                      LEFT JOIN mlangorder_printauto o ON ps.order_no = o.no
                      WHERE ps.status = ?
                      ORDER BY ps.created_at ASC
                      LIMIT ? OFFSET ?";
            
            $stmt = mysqli_prepare($this->db, $query);
            mysqli_stmt_bind_param($stmt, "sii", $status, $limit, $offset);
        } else {
            $query = "SELECT ps.*, o.name, o.Hendphone, o.phone
                      FROM proof_status ps
                      LEFT JOIN mlangorder_printauto o ON ps.order_no = o.no
                      ORDER BY ps.created_at DESC
                      LIMIT ? OFFSET ?";
            
            $stmt = mysqli_prepare($this->db, $query);
            mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
        }
        
        if (!$stmt) {
            error_log("ProofWorkflow getPendingProofs 준비 실패: " . mysqli_error($this->db));
            return [];
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $proofs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $proofs[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        
        return $proofs;
    }
    
    /**
     * 상태별 교정 요청 개수
     * 
     * @return array
     */
    public function getProofStats() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'revision_requested' THEN 1 ELSE 0 END) as revision_requested,
                    SUM(CASE WHEN status = 'revised' THEN 1 ELSE 0 END) as revised
                  FROM proof_status";
        
        $result = mysqli_query($this->db, $query);
        
        if (!$result) {
            error_log("ProofWorkflow getProofStats 실패: " . mysqli_error($this->db));
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'revision_requested' => 0,
                'revised' => 0
            ];
        }
        
        return mysqli_fetch_assoc($result);
    }
}
?>
