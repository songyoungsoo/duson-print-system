<?php
/**
 * 관리자 리뷰 관리 API
 * 경로: admin/mlangprintauto/api/reviews.php
 *
 * Actions:
 *   GET/POST action=list     (status, product_type, page, per_page)
 *   POST     action=approve  (review_id)
 *   POST     action=reject   (review_id)
 *   POST     action=reply    (review_id, reply_text)
 *   POST     action=delete   (review_id)
 *
 * 인증: $_SESSION['admin_logged_in'] === true (admin_auth.php 패턴)
 */

session_start();

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 관리자 인증 확인 (proof_api.php 패턴)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => '관리자 로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

try {
    require_once __DIR__ . '/../../../db.php';
    require_once __DIR__ . '/../../../includes/review_schema.php';

    if (!$db) {
        throw new Exception('데이터베이스 연결에 실패했습니다.');
    }

    ensureReviewTables($db);

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if (empty($action)) {
        throw new Exception('action 파라미터가 필요합니다.');
    }

    $response = ['success' => false];

    switch ($action) {

        // ─────────────────────────────────────────────
        // (a) 리뷰 목록 — 모든 승인 상태 + 필터
        // ─────────────────────────────────────────────
        case 'list':
            $status      = $_GET['status']       ?? $_POST['status']       ?? 'all';
            $productType = trim($_GET['product_type'] ?? $_POST['product_type'] ?? '');
            $page        = max(1, (int)($_GET['page']     ?? $_POST['page']     ?? 1));
            $perPage     = max(1, min(100, (int)($_GET['per_page'] ?? $_POST['per_page'] ?? 20)));
            $offset      = ($page - 1) * $perPage;

            // WHERE 조건 동적 구성
            $conditions = [];
            $types      = '';
            $params     = [];

            // 승인 상태 필터
            switch ($status) {
                case 'pending':
                    $conditions[] = 'r.is_approved = 0';
                    break;
                case 'approved':
                    $conditions[] = 'r.is_approved = 1';
                    break;
                case 'rejected':
                    $conditions[] = 'r.is_approved = 2';
                    break;
                // 'all' → 필터 없음
            }

            // 제품 타입 필터
            if (!empty($productType)) {
                $conditions[] = 'r.product_type = ?';
                $types .= 's';
                $params[] = $productType;
            }

            $where = '';
            if (!empty($conditions)) {
                $where = 'WHERE ' . implode(' AND ', $conditions);
            }

            // 총 건수
            $countSql  = "SELECT COUNT(*) AS cnt FROM reviews r {$where}";
            $countStmt = mysqli_prepare($db, $countSql);
            if (!empty($params)) {
                mysqli_stmt_bind_param($countStmt, $types, ...$params); // 동적 bind — spread
            }
            mysqli_stmt_execute($countStmt);
            $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['cnt'];
            mysqli_stmt_close($countStmt);

            // 리뷰 조회
            $listSql = "SELECT r.id, r.product_type, r.order_id, r.user_id, r.user_name,
                                r.rating, r.title, r.content, r.is_verified_purchase,
                                r.is_approved, r.admin_reply, r.admin_reply_at,
                                r.likes_count, r.created_at, r.updated_at
                         FROM reviews r
                         {$where}
                         ORDER BY r.created_at DESC
                         LIMIT ? OFFSET ?";
            $listStmt = mysqli_prepare($db, $listSql);

            // LIMIT, OFFSET 추가
            $listTypes  = $types . 'ii';
            $listParams = $params;
            $listParams[] = $perPage;
            $listParams[] = $offset;
            mysqli_stmt_bind_param($listStmt, $listTypes, ...$listParams); // 동적 bind — spread

            mysqli_stmt_execute($listStmt);
            $listResult = mysqli_stmt_get_result($listStmt);

            $reviews = [];
            while ($row = mysqli_fetch_assoc($listResult)) {
                // 사진 조회
                $photoStmt = mysqli_prepare($db,
                    "SELECT id, file_path, file_name, sort_order FROM review_photos WHERE review_id = ? ORDER BY sort_order ASC"
                );
                $rid = (int)$row['id'];
                mysqli_stmt_bind_param($photoStmt, "i", $rid); // ? 1개, 타입 1글자, 변수 1개
                mysqli_stmt_execute($photoStmt);
                $photoResult = mysqli_stmt_get_result($photoStmt);
                $photos = [];
                while ($photo = mysqli_fetch_assoc($photoResult)) {
                    $photo['id'] = (int)$photo['id'];
                    $photos[] = $photo;
                }
                mysqli_stmt_close($photoStmt);

                $row['id']                   = (int)$row['id'];
                $row['order_id']             = $row['order_id'] !== null ? (int)$row['order_id'] : null;
                $row['user_id']              = $row['user_id'] !== null ? (int)$row['user_id'] : null;
                $row['rating']               = (int)$row['rating'];
                $row['is_verified_purchase']  = (int)$row['is_verified_purchase'];
                $row['is_approved']          = (int)$row['is_approved'];
                $row['likes_count']          = (int)$row['likes_count'];
                $row['photos']               = $photos;

                $reviews[] = $row;
            }
            mysqli_stmt_close($listStmt);

            $response = [
                'success' => true,
                'data' => [
                    'reviews'  => $reviews,
                    'total'    => $total,
                    'page'     => $page,
                    'per_page' => $perPage,
                ]
            ];
            break;

        // ─────────────────────────────────────────────
        // (b) 리뷰 승인
        // ─────────────────────────────────────────────
        case 'approve':
            $reviewId = (int)($_POST['review_id'] ?? 0);
            if ($reviewId <= 0) {
                throw new Exception('review_id가 필요합니다.');
            }

            $stmt = mysqli_prepare($db, "UPDATE reviews SET is_approved = 1 WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($affected === 0) {
                throw new Exception('리뷰를 찾을 수 없습니다.');
            }

            $response = [
                'success' => true,
                'message' => '리뷰가 승인되었습니다.',
            ];
            break;

        // ─────────────────────────────────────────────
        // (c) 리뷰 반려
        // ─────────────────────────────────────────────
        case 'reject':
            $reviewId = (int)($_POST['review_id'] ?? 0);
            if ($reviewId <= 0) {
                throw new Exception('review_id가 필요합니다.');
            }

            $stmt = mysqli_prepare($db, "UPDATE reviews SET is_approved = 2 WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($affected === 0) {
                throw new Exception('리뷰를 찾을 수 없습니다.');
            }

            $response = [
                'success' => true,
                'message' => '리뷰가 반려되었습니다.',
            ];
            break;

        // ─────────────────────────────────────────────
        // (d) 관리자 답변 등록/수정
        // ─────────────────────────────────────────────
        case 'reply':
            $reviewId  = (int)($_POST['review_id'] ?? 0);
            $replyText = trim($_POST['reply_text'] ?? '');

            if ($reviewId <= 0) {
                throw new Exception('review_id가 필요합니다.');
            }
            if (empty($replyText)) {
                throw new Exception('답변 내용을 입력해주세요.');
            }
            if (mb_strlen($replyText) > 5000) {
                throw new Exception('답변은 5,000자 이내로 입력해주세요.');
            }

            $now  = date('Y-m-d H:i:s');
            $stmt = mysqli_prepare($db,
                "UPDATE reviews SET admin_reply = ?, admin_reply_at = ? WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, "ssi", $replyText, $now, $reviewId); // ? 3개, 타입 3글자, 변수 3개
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($affected === 0) {
                throw new Exception('리뷰를 찾을 수 없습니다.');
            }

            $response = [
                'success' => true,
                'message' => '답변이 등록되었습니다.',
            ];
            break;

        // ─────────────────────────────────────────────
        // (e) 리뷰 삭제 (CASCADE → photos, likes 자동 삭제)
        // ─────────────────────────────────────────────
        case 'delete':
            $reviewId = (int)($_POST['review_id'] ?? 0);
            if ($reviewId <= 0) {
                throw new Exception('review_id가 필요합니다.');
            }

            // 사진 파일 물리 삭제 (DB cascade 전에)
            $photoStmt = mysqli_prepare($db,
                "SELECT file_path FROM review_photos WHERE review_id = ?"
            );
            mysqli_stmt_bind_param($photoStmt, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($photoStmt);
            $photoResult = mysqli_stmt_get_result($photoStmt);
            while ($photo = mysqli_fetch_assoc($photoResult)) {
                $fileFull = __DIR__ . '/../../../' . ltrim($photo['file_path'], '/');
                if (file_exists($fileFull)) {
                    unlink($fileFull);
                }
            }
            mysqli_stmt_close($photoStmt);

            // 업로드 디렉토리 삭제
            $uploadDir = __DIR__ . '/../../../uploads/reviews/' . $reviewId;
            if (is_dir($uploadDir)) {
                @rmdir($uploadDir); // 빈 디렉토리만 삭제됨
            }

            // DB 삭제 (CASCADE로 review_photos, review_likes 자동 삭제)
            $delStmt = mysqli_prepare($db, "DELETE FROM reviews WHERE id = ?");
            mysqli_stmt_bind_param($delStmt, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($delStmt);
            $affected = mysqli_stmt_affected_rows($delStmt);
            mysqli_stmt_close($delStmt);

            if ($affected === 0) {
                throw new Exception('리뷰를 찾을 수 없습니다.');
            }

            $response = [
                'success' => true,
                'message' => '리뷰가 삭제되었습니다.',
            ];
            break;

        default:
            throw new Exception("지원하지 않는 action입니다: {$action}");
    }

    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// DB 연결 종료 (모든 쿼리 완료 후 — PHP 8.2 호환)
if (isset($db) && $db) {
    mysqli_close($db);
}

ob_end_flush();
