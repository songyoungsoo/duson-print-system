-- shop_temp 테이블에 파일 관리 필드 추가/수정
-- 실행 전 반드시 백업: CREATE TABLE shop_temp_backup AS SELECT * FROM shop_temp;

-- 1. 기존 img 필드 확장 및 새 필드 추가
ALTER TABLE `shop_temp` 
  -- 기존 img 필드를 더 큰 크기로 변경 (여러 파일명 저장 가능)
  MODIFY COLUMN `img` TEXT DEFAULT NULL COMMENT '업로드된 파일명들 (JSON 또는 구분자로 저장)',
  
  -- 파일 경로 정보 추가
  ADD COLUMN `file_path` VARCHAR(500) DEFAULT NULL COMMENT '파일 저장 경로 (ImgFolder 기준)',
  ADD COLUMN `file_info` TEXT DEFAULT NULL COMMENT '파일 상세 정보 (JSON 형태)',
  ADD COLUMN `upload_log` TEXT DEFAULT NULL COMMENT '업로드 로그 정보 (JSON 형태)',
  
  -- 로그 정보 필드 추가 (기존 시스템과 호환)
  ADD COLUMN `log_url` VARCHAR(100) DEFAULT NULL COMMENT '페이지 구분자',
  ADD COLUMN `log_y` VARCHAR(10) DEFAULT NULL COMMENT '연도',
  ADD COLUMN `log_md` VARCHAR(10) DEFAULT NULL COMMENT '월일',
  ADD COLUMN `log_ip` VARCHAR(50) DEFAULT NULL COMMENT 'IP 주소',
  ADD COLUMN `log_time` VARCHAR(20) DEFAULT NULL COMMENT '타임스탬프',
  
  -- 인덱스 추가
  ADD INDEX `idx_file_path` (`file_path`),
  ADD INDEX `idx_log_info` (`log_url`, `log_y`, `log_md`);

-- 2. 기존 데이터 호환성을 위한 기본값 설정
UPDATE `shop_temp` SET 
  `file_path` = CONCAT(
    COALESCE(`log_url`, 'unknown'), '/',
    COALESCE(`log_y`, YEAR(NOW())), '/',
    COALESCE(`log_md`, DATE_FORMAT(NOW(), '%m%d')), '/',
    COALESCE(`log_ip`, '127.0.0.1'), '/',
    COALESCE(`log_time`, UNIX_TIMESTAMP())
  )
WHERE `file_path` IS NULL AND (`img` IS NOT NULL AND `img` != '');

-- 완료 메시지
SELECT 'shop_temp 테이블 파일 관리 필드 추가 완료' as message;