-- 이메일 발송 로그 테이블 생성
-- 사무용 표형태 주문완료 시스템용

CREATE TABLE IF NOT EXISTS `email_send_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_numbers` varchar(500) NOT NULL COMMENT '주문번호들 (콤마 구분)',
  `recipient_email` varchar(255) NOT NULL COMMENT '수신자 이메일',
  `recipient_name` varchar(100) NOT NULL COMMENT '수신자 이름',
  `subject` varchar(500) NOT NULL COMMENT '이메일 제목',
  `sent_at` datetime NOT NULL COMMENT '발송 일시',
  `status` enum('success','failed') NOT NULL DEFAULT 'success' COMMENT '발송 상태',
  `error_message` text NULL COMMENT '에러 메시지 (실패시)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recipient_email` (`recipient_email`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='이메일 발송 로그';

-- 포트폴리오 업로드 로그 테이블도 함께 생성 (팝업 갤러리용)
CREATE TABLE IF NOT EXISTS `portfolio_upload_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(100) NOT NULL COMMENT '세션 ID',
  `category` varchar(50) NOT NULL COMMENT '제품 카테고리',
  `file_data` longtext NOT NULL COMMENT '업로드된 파일 정보 (JSON)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_category` (`category`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='포트폴리오 업로드 로그';