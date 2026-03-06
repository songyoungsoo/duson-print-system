<?php
/**
 * 고객 리뷰 API
 * 경로: api/reviews.php
 *
 * Actions:
 *   GET  ?action=list&product_type=namecard&page=1&per_page=10&sort=newest
 *   GET  ?action=summary&product_type=namecard
 *   POST action=create  (multipart/form-data, 로그인 필수)
 *   POST action=like     review_id
 *
 * 패턴: api/calculate_price.php 동일
 */

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 허용 제품 목록
$VALID_PRODUCTS = [
    'namecard', 'inserted', 'sticker_new', 'msticker',
    'envelope', 'littleprint', 'merchandisebond', 'cadarok', 'ncrflambeau'
];

try {
    require_once __DIR__ . '/../db.php';
    require_once __DIR__ . '/../includes/review_schema.php';

    if (!$db) {
        throw new Exception('데이터베이스 연결에 실패했습니다.');
    }

    // 테이블 자동 생성
    ensureReviewTables($db);

    // action 판별 (GET 또는 POST)
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if (empty($action)) {
        throw new Exception('action 파라미터가 필요합니다.');
    }

    $response = ['success' => false];

    switch ($action) {

        // ─────────────────────────────────────────────
        // (a) 승인된 리뷰 목록 (GET)
        // ─────────────────────────────────────────────
        case 'list':
            $productType = trim($_GET['product_type'] ?? '');
            if (empty($productType) || !in_array($productType, $VALID_PRODUCTS)) {
                throw new Exception('유효한 product_type이 필요합니다.');
            }

            $page    = max(1, (int)($_GET['page'] ?? 1));
            $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 10)));
            $sort    = $_GET['sort'] ?? 'newest';
            $offset  = ($page - 1) * $perPage;

            // 정렬
            $orderBy = 'r.created_at DESC'; // newest (default)
            switch ($sort) {
                case 'rating_high': $orderBy = 'r.rating DESC, r.created_at DESC'; break;
                case 'rating_low':  $orderBy = 'r.rating ASC, r.created_at DESC';  break;
                case 'likes':       $orderBy = 'r.likes_count DESC, r.created_at DESC'; break;
            }

            // 총 건수
            $countStmt = mysqli_prepare($db,
                "SELECT COUNT(*) AS cnt FROM reviews r WHERE r.product_type = ? AND r.is_approved = 1"
            );
            mysqli_stmt_bind_param($countStmt, "s", $productType); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($countStmt);
            $countRow = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt));
            $total = (int)$countRow['cnt'];
            mysqli_stmt_close($countStmt);

            // 평균 별점
            $avgStmt = mysqli_prepare($db,
                "SELECT COALESCE(AVG(r.rating), 0) AS avg_rating FROM reviews r WHERE r.product_type = ? AND r.is_approved = 1"
            );
            mysqli_stmt_bind_param($avgStmt, "s", $productType); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($avgStmt);
            $avgRow = mysqli_fetch_assoc(mysqli_stmt_get_result($avgStmt));
            $avgRating = round((float)$avgRow['avg_rating'], 1);
            mysqli_stmt_close($avgStmt);

            // 리뷰 조회
            $listSql = "SELECT r.id, r.user_name, r.rating, r.title, r.content,
                                r.is_verified_purchase, r.likes_count,
                                r.admin_reply, r.admin_reply_at, r.created_at
                         FROM reviews r
                         WHERE r.product_type = ? AND r.is_approved = 1
                         ORDER BY {$orderBy}
                         LIMIT ? OFFSET ?";
            $listStmt = mysqli_prepare($db, $listSql);
            mysqli_stmt_bind_param($listStmt, "sii", $productType, $perPage, $offset); // ? 3개, 타입 3글자, 변수 3개
            mysqli_stmt_execute($listStmt);
            $listResult = mysqli_stmt_get_result($listStmt);

            $reviews = [];
            while ($row = mysqli_fetch_assoc($listResult)) {
                // 사진 조회
                $photoStmt = mysqli_prepare($db,
                    "SELECT file_path, file_name, sort_order FROM review_photos WHERE review_id = ? ORDER BY sort_order ASC"
                );
                $reviewId = (int)$row['id'];
                mysqli_stmt_bind_param($photoStmt, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
                mysqli_stmt_execute($photoStmt);
                $photoResult = mysqli_stmt_get_result($photoStmt);
                $photos = [];
                while ($photo = mysqli_fetch_assoc($photoResult)) {
                    $photos[] = $photo;
                }
                mysqli_stmt_close($photoStmt);

                $row['photos'] = $photos;
                $row['id'] = (int)$row['id'];
                $row['rating'] = (int)$row['rating'];
                $row['is_verified_purchase'] = (int)$row['is_verified_purchase'];
                $row['likes_count'] = (int)$row['likes_count'];
                $reviews[] = $row;
            }
            mysqli_stmt_close($listStmt);

            $response = [
                'success' => true,
                'data' => [
                    'reviews'       => $reviews,
                    'total'         => $total,
                    'page'          => $page,
                    'per_page'      => $perPage,
                    'avg_rating'    => $avgRating,
                    'total_reviews' => $total,
                ]
            ];
            break;

        // ─────────────────────────────────────────────
        // (b) 제품 별점 요약 (GET)
        // ─────────────────────────────────────────────
        case 'summary':
            $productType = trim($_GET['product_type'] ?? '');
            if (empty($productType) || !in_array($productType, $VALID_PRODUCTS)) {
                throw new Exception('유효한 product_type이 필요합니다.');
            }

            // 평균 + 총 건수
            $sumStmt = mysqli_prepare($db,
                "SELECT COUNT(*) AS total_reviews, COALESCE(AVG(rating), 0) AS avg_rating
                 FROM reviews WHERE product_type = ? AND is_approved = 1"
            );
            mysqli_stmt_bind_param($sumStmt, "s", $productType); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($sumStmt);
            $sumRow = mysqli_fetch_assoc(mysqli_stmt_get_result($sumStmt));
            mysqli_stmt_close($sumStmt);

            // 별점 분포
            $distStmt = mysqli_prepare($db,
                "SELECT rating, COUNT(*) AS cnt
                 FROM reviews WHERE product_type = ? AND is_approved = 1
                 GROUP BY rating"
            );
            mysqli_stmt_bind_param($distStmt, "s", $productType); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($distStmt);
            $distResult = mysqli_stmt_get_result($distStmt);

            $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            while ($dRow = mysqli_fetch_assoc($distResult)) {
                $r = (int)$dRow['rating'];
                if ($r >= 1 && $r <= 5) {
                    $distribution[$r] = (int)$dRow['cnt'];
                }
            }
            mysqli_stmt_close($distStmt);

            $response = [
                'success' => true,
                'data' => [
                    'avg_rating'          => round((float)$sumRow['avg_rating'], 1),
                    'total_reviews'       => (int)$sumRow['total_reviews'],
                    'rating_distribution' => $distribution,
                ]
            ];
            break;

        // ─────────────────────────────────────────────
        // (c) 리뷰 작성 (POST, multipart/form-data)
        // ─────────────────────────────────────────────
        case 'create':
            // 로그인 필수
            if (empty($_SESSION['user_id'])) {
                throw new Exception('리뷰를 작성하려면 로그인이 필요합니다.');
            }

            $userId   = (int)$_SESSION['user_id'];
            $userName = $_SESSION['user_name'] ?? '회원';

            $productType = trim($_POST['product_type'] ?? '');
            if (empty($productType) || !in_array($productType, $VALID_PRODUCTS)) {
                throw new Exception('유효한 product_type이 필요합니다.');
            }

            $rating  = max(1, min(5, (int)($_POST['rating'] ?? 5)));
            $title   = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $orderId = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;

            if (empty($content)) {
                throw new Exception('리뷰 내용을 입력해주세요.');
            }
            if (mb_strlen($content) > 5000) {
                throw new Exception('리뷰 내용은 5,000자 이내로 입력해주세요.');
            }
            if (mb_strlen($title) > 200) {
                throw new Exception('리뷰 제목은 200자 이내로 입력해주세요.');
            }

            // 구매인증: order_id가 주어지면 해당 주문이 이 사용자 것인지 확인
            $isVerified = 0;
            if ($orderId) {
                $verifyStmt = mysqli_prepare($db,
                    "SELECT id FROM mlangorder_printauto WHERE id = ? AND (name = ? OR email = ?)"
                );
                // users 테이블에서 사용자 정보 조회
                $userStmt = mysqli_prepare($db, "SELECT name, email FROM users WHERE id = ?");
                mysqli_stmt_bind_param($userStmt, "i", $userId); // ? 1개, 타입 1글자, 변수 1개
                mysqli_stmt_execute($userStmt);
                $userRow = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));
                mysqli_stmt_close($userStmt);

                if ($userRow) {
                    $uName  = $userRow['name'];
                    $uEmail = $userRow['email'];
                    mysqli_stmt_bind_param($verifyStmt, "iss", $orderId, $uName, $uEmail); // ? 3개, 타입 3글자, 변수 3개
                    mysqli_stmt_execute($verifyStmt);
                    $verifyResult = mysqli_stmt_get_result($verifyStmt);
                    if (mysqli_num_rows($verifyResult) > 0) {
                        $isVerified = 1;
                    }
                    mysqli_stmt_close($verifyStmt);
                } else {
                    mysqli_stmt_close($verifyStmt);
                }
            }

            // INSERT 리뷰
            // 컬럼 8개: product_type, order_id, user_id, user_name, rating, title, content, is_verified_purchase
            // VALUES 8개: ?, ?, ?, ?, ?, ?, ?, ?
            $insertSql = "INSERT INTO reviews (product_type, order_id, user_id, user_name, rating, title, content, is_verified_purchase)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($db, $insertSql);
            mysqli_stmt_bind_param($insertStmt, "siisissi",
                $productType,   // 1 s - product_type
                $orderId,       // 2 i - order_id
                $userId,        // 3 i - user_id
                $userName,      // 4 s - user_name
                $rating,        // 5 i - rating
                $title,         // 6 s - title
                $content,       // 7 s - content
                $isVerified     // 8 i - is_verified_purchase
            ); // ? 8개, 타입 8글자, 변수 8개
            mysqli_stmt_execute($insertStmt);
            $newReviewId = (int)mysqli_insert_id($db);
            mysqli_stmt_close($insertStmt);

            if ($newReviewId <= 0) {
                throw new Exception('리뷰 저장에 실패했습니다.');
            }

            // 사진 업로드 (최대 5장, 각 5MB, jpg/png/webp)
            if (!empty($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
                $uploadDir = __DIR__ . '/../uploads/reviews/' . $newReviewId;
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                $maxPhotos   = 5;
                $photoCount  = min($maxPhotos, count($_FILES['photos']['name']));

                for ($i = 0; $i < $photoCount; $i++) {
                    if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    if ($_FILES['photos']['size'][$i] > $maxFileSize) {
                        continue;
                    }

                    $origName = $_FILES['photos']['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExt)) {
                        continue;
                    }

                    // 안전한 파일명 생성
                    $safeName = $i . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destPath = $uploadDir . '/' . $safeName;

                    if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $destPath)) {
                        $dbPath = '/uploads/reviews/' . $newReviewId . '/' . $safeName;
                        $photoInsert = mysqli_prepare($db,
                            "INSERT INTO review_photos (review_id, file_path, file_name, sort_order) VALUES (?, ?, ?, ?)"
                        );
                        mysqli_stmt_bind_param($photoInsert, "issi",
                            $newReviewId,  // 1 i - review_id
                            $dbPath,       // 2 s - file_path
                            $origName,     // 3 s - file_name
                            $i             // 4 i - sort_order
                        ); // ? 4개, 타입 4글자, 변수 4개
                        mysqli_stmt_execute($photoInsert);
                        mysqli_stmt_close($photoInsert);
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => '리뷰가 등록되었습니다. 관리자 승인 후 게시됩니다.',
                'data'    => ['review_id' => $newReviewId]
            ];
            break;

        // ─────────────────────────────────────────────
        // (d) 좋아요 토글 (POST)
        // ─────────────────────────────────────────────
        case 'like':
            $reviewId = (int)($_POST['review_id'] ?? 0);
            if ($reviewId <= 0) {
                throw new Exception('review_id가 필요합니다.');
            }

            // 리뷰 존재+승인 확인
            $existStmt = mysqli_prepare($db, "SELECT id FROM reviews WHERE id = ? AND is_approved = 1");
            mysqli_stmt_bind_param($existStmt, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($existStmt);
            if (mysqli_num_rows(mysqli_stmt_get_result($existStmt)) === 0) {
                mysqli_stmt_close($existStmt);
                throw new Exception('리뷰를 찾을 수 없습니다.');
            }
            mysqli_stmt_close($existStmt);

            // 사용자 식별자
            if (!empty($_SESSION['user_id'])) {
                $userIdent = 'user_' . $_SESSION['user_id'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $userIdent = 'anon_' . md5($ip . $ua);
            }

            // 이미 좋아요 했는지 확인
            $likeCheck = mysqli_prepare($db,
                "SELECT id FROM review_likes WHERE review_id = ? AND user_identifier = ?"
            );
            mysqli_stmt_bind_param($likeCheck, "is", $reviewId, $userIdent); // ? 2개, 타입 2글자, 변수 2개
            mysqli_stmt_execute($likeCheck);
            $likeResult = mysqli_stmt_get_result($likeCheck);
            $alreadyLiked = (mysqli_num_rows($likeResult) > 0);
            mysqli_stmt_close($likeCheck);

            if ($alreadyLiked) {
                // 좋아요 취소
                $delLike = mysqli_prepare($db,
                    "DELETE FROM review_likes WHERE review_id = ? AND user_identifier = ?"
                );
                mysqli_stmt_bind_param($delLike, "is", $reviewId, $userIdent); // ? 2개, 타입 2글자, 변수 2개
                mysqli_stmt_execute($delLike);
                mysqli_stmt_close($delLike);

                $decrement = mysqli_prepare($db,
                    "UPDATE reviews SET likes_count = GREATEST(0, likes_count - 1) WHERE id = ?"
                );
                mysqli_stmt_bind_param($decrement, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
                mysqli_stmt_execute($decrement);
                mysqli_stmt_close($decrement);

                $liked = false;
            } else {
                // 좋아요 추가
                $addLike = mysqli_prepare($db,
                    "INSERT INTO review_likes (review_id, user_identifier) VALUES (?, ?)"
                );
                mysqli_stmt_bind_param($addLike, "is", $reviewId, $userIdent); // ? 2개, 타입 2글자, 변수 2개
                mysqli_stmt_execute($addLike);
                mysqli_stmt_close($addLike);

                $increment = mysqli_prepare($db,
                    "UPDATE reviews SET likes_count = likes_count + 1 WHERE id = ?"
                );
                mysqli_stmt_bind_param($increment, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
                mysqli_stmt_execute($increment);
                mysqli_stmt_close($increment);

                $liked = true;
            }

            // 현재 좋아요 수 조회
            $cntStmt = mysqli_prepare($db, "SELECT likes_count FROM reviews WHERE id = ?");
            mysqli_stmt_bind_param($cntStmt, "i", $reviewId); // ? 1개, 타입 1글자, 변수 1개
            mysqli_stmt_execute($cntStmt);
            $cntRow = mysqli_fetch_assoc(mysqli_stmt_get_result($cntStmt));
            mysqli_stmt_close($cntStmt);

            $response = [
                'success' => true,
                'data' => [
                    'liked'       => $liked,
                    'likes_count' => (int)($cntRow['likes_count'] ?? 0),
                ]
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
