<?php
session_start();
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../includes/ProofWorkflow.php';
require_once __DIR__ . '/../../../includes/EmailNotification.php';

header('Content-Type: application/json');

// Admin authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => '관리자 권한이 필요합니다']);
    exit;
}

$admin_id = $_SESSION['admin_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다']);
    exit;
}

$action = $_POST['action'] ?? '';
$proof_id = isset($_POST['proof_id']) ? (int)$_POST['proof_id'] : 0;

if (empty($action) || $proof_id <= 0) {
    echo json_encode(['success' => false, 'message' => '필수 파라미터가 누락되었습니다']);
    exit;
}

$proofWorkflow = new ProofWorkflow($db);

try {
    switch ($action) {
        case 'approve':
            $comment = $_POST['comment'] ?? '';
            $result = $proofWorkflow->approveProof($proof_id, $admin_id, $comment);

            if ($result) {
                // 고객 정보 조회 및 이메일 전송
                try {
                    $proof_query = "
                        SELECT ps.order_no, ps.product_type, o.email, o.name
                        FROM proof_status ps
                        LEFT JOIN mlangorder_printauto o ON ps.order_no = o.no
                        WHERE ps.id = ?
                    ";
                    $stmt = mysqli_prepare($db, $proof_query);
                    mysqli_stmt_bind_param($stmt, "i", $proof_id);
                    mysqli_stmt_execute($stmt);
                    $proof_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    mysqli_stmt_close($stmt);

                    if ($proof_data && !empty($proof_data['email'])) {
                        $emailNotification = new EmailNotification();
                        $emailNotification->sendProofApprovedNotification(
                            $proof_data['email'],
                            $proof_data['name'],
                            $proof_data['order_no'],
                            $comment
                        );
                    }
                } catch (Exception $e) {
                    error_log("승인 이메일 전송 실패: " . $e->getMessage());
                }

                echo json_encode([
                    'success' => true,
                    'message' => '교정이 승인되었습니다',
                    'proof_id' => $proof_id
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '승인 처리 중 오류가 발생했습니다'
                ]);
            }
            break;
            
        case 'request_revision':
            $comment = $_POST['comment'] ?? '';
            
            if (empty($comment)) {
                echo json_encode([
                    'success' => false,
                    'message' => '수정 요청 사유를 입력해주세요'
                ]);
                exit;
            }
            
            $result = $proofWorkflow->requestRevision($proof_id, $admin_id, $comment);

            if ($result) {
                // 고객 정보 조회 및 이메일 전송
                try {
                    $proof_query = "
                        SELECT ps.order_no, ps.product_type, o.email, o.name
                        FROM proof_status ps
                        LEFT JOIN mlangorder_printauto o ON ps.order_no = o.no
                        WHERE ps.id = ?
                    ";
                    $stmt = mysqli_prepare($db, $proof_query);
                    mysqli_stmt_bind_param($stmt, "i", $proof_id);
                    mysqli_stmt_execute($stmt);
                    $proof_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    mysqli_stmt_close($stmt);

                    if ($proof_data && !empty($proof_data['email'])) {
                        $emailNotification = new EmailNotification();
                        $emailNotification->sendRevisionRequestNotification(
                            $proof_data['email'],
                            $proof_data['name'],
                            $proof_data['order_no'],
                            $comment
                        );
                    }
                } catch (Exception $e) {
                    error_log("수정 요청 이메일 전송 실패: " . $e->getMessage());
                }

                echo json_encode([
                    'success' => true,
                    'message' => '수정 요청이 전송되었습니다',
                    'proof_id' => $proof_id
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '수정 요청 처리 중 오류가 발생했습니다'
                ]);
            }
            break;
            
        case 'upload_revision':
            // This action is called from customer side (mypage/proof.php)
            // For now, return not implemented
            echo json_encode([
                'success' => false,
                'message' => '고객 페이지에서 사용하는 기능입니다'
            ]);
            break;
            
        case 'get_proof':
            // Get single proof details
            $order_no = $_POST['order_no'] ?? '';
            
            if (empty($order_no)) {
                echo json_encode([
                    'success' => false,
                    'message' => '주문번호가 필요합니다'
                ]);
                exit;
            }
            
            $proof = $proofWorkflow->getProofByOrderNo($order_no);
            
            if ($proof) {
                echo json_encode([
                    'success' => true,
                    'proof' => $proof
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '교정 정보를 찾을 수 없습니다'
                ]);
            }
            break;
            
        case 'get_stats':
            // Get proof statistics
            $stats = $proofWorkflow->getProofStats();
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => '알 수 없는 액션입니다: ' . htmlspecialchars($action)
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Proof API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '서버 오류가 발생했습니다'
    ]);
}
?>
