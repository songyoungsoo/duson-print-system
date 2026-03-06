<?php
/**
 * 리뷰 시스템 DB 스키마 자동 생성
 * 경로: includes/review_schema.php
 *
 * ensureRememberTokenTable() (includes/auth.php) 패턴과 동일:
 * SHOW TABLES LIKE → 없으면 CREATE TABLE IF NOT EXISTS
 *
 * 테이블:
 * - reviews: 고객 리뷰 (별점, 본문, 구매인증, 관리자 승인/답변)
 * - review_photos: 리뷰 첨부 사진 (최대 5장)
 * - review_likes: 리뷰 좋아요 (회원 user_id 또는 비회원 IP+UA hash)
 */

/**
 * 리뷰 관련 3개 테이블이 존재하는지 확인하고, 없으면 생성
 *
 * @param mysqli $db DB 연결 객체
 * @return bool 성공 여부
 */
function ensureReviewTables($db) {
    if (!$db) return false;

    // --- reviews 테이블 ---
    $check = mysqli_query($db, "SHOW TABLES LIKE 'reviews'");
    if ($check && mysqli_num_rows($check) == 0) {
        $sql = "CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_type VARCHAR(30) NOT NULL COMMENT '제품 폴더명: namecard, inserted, sticker_new 등',
            order_id INT DEFAULT NULL COMMENT 'mlangorder_printauto.id 참조',
            user_id INT DEFAULT NULL COMMENT 'users.id 참조 (비회원은 NULL)',
            user_name VARCHAR(100) NOT NULL COMMENT '작성자명',
            rating TINYINT NOT NULL DEFAULT 5 COMMENT '별점 1-5',
            title VARCHAR(200) DEFAULT '' COMMENT '리뷰 제목',
            content TEXT NOT NULL COMMENT '리뷰 본문',
            is_verified_purchase TINYINT(1) DEFAULT 0 COMMENT '구매인증 여부',
            is_approved TINYINT(1) DEFAULT 0 COMMENT '관리자 승인 (0=대기, 1=승인, 2=반려)',
            admin_reply TEXT DEFAULT NULL COMMENT '관리자 답변',
            admin_reply_at DATETIME DEFAULT NULL,
            likes_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_product (product_type),
            INDEX idx_approved (is_approved),
            INDEX idx_user (user_id),
            INDEX idx_rating (rating),
            INDEX idx_created (created_at DESC)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($db, $sql);
    }

    // --- review_photos 테이블 ---
    $check = mysqli_query($db, "SHOW TABLES LIKE 'review_photos'");
    if ($check && mysqli_num_rows($check) == 0) {
        $sql = "CREATE TABLE IF NOT EXISTS review_photos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            review_id INT NOT NULL,
            file_path VARCHAR(500) NOT NULL COMMENT '업로드 경로',
            file_name VARCHAR(255) NOT NULL COMMENT '원본 파일명',
            sort_order TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_review (review_id),
            FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($db, $sql);
    }

    // --- review_likes 테이블 ---
    $check = mysqli_query($db, "SHOW TABLES LIKE 'review_likes'");
    if ($check && mysqli_num_rows($check) == 0) {
        $sql = "CREATE TABLE IF NOT EXISTS review_likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            review_id INT NOT NULL,
            user_identifier VARCHAR(100) NOT NULL COMMENT 'user_id 또는 IP+UA hash (비회원)',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_review_user (review_id, user_identifier),
            INDEX idx_review (review_id),
            FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($db, $sql);
    }

    return true;
}
