# 📊 duson1830 데이터베이스 최적화 가이드

## 🎯 최적화 목표
- **쿼리 실행 속도 50% 이상 향상**
- **메모리 사용량 30% 감소**
- **동시 접속 처리 능력 개선**
- **백업/복원 시간 단축**

## 🔧 최적화 도구

### 1. SQL 스크립트 방식
**파일**: `db_optimization_guide.sql`
- 직접 phpMyAdmin이나 MySQL 클라이언트에서 실행
- 단계별 최적화 스크립트 제공
- 백업 필수!

### 2. PHP 웹 도구
**파일**: `db_optimization_php.php`
**접속**: `http://localhost/db_optimization_php.php`
- 브라우저에서 시각적으로 최적화 상태 확인
- 원클릭 최적화 기능
- 실시간 성능 모니터링

## 📋 최적화 단계별 가이드

### STEP 1: 사전 준비 (필수!)
```bash
# 1. 데이터베이스 백업
mysqldump -u duson1830 -pdu1830 duson1830 > backup_before_optimization.sql

# 2. 현재 상태 확인
# http://localhost/db_optimization_php.php 접속하여 현재 상태 확인
```

### STEP 2: 인덱스 최적화
```sql
-- 주요 테이블 인덱스 추가
ALTER TABLE `users` ADD INDEX `idx_userid` (`userid`);
ALTER TABLE `mlangorder_printauto` ADD INDEX `idx_date` (`date`);
ALTER TABLE `shop_temp` ADD INDEX `idx_session_id` (`session_id`);

-- 복합 인덱스 (자주 함께 검색되는 컬럼)
ALTER TABLE `mlangorder_printauto` ADD INDEX `idx_name_phone_date` (`name`, `phone`, `date`);
```

### STEP 3: 테이블 최적화
```sql
-- 주요 테이블 최적화 및 분석
OPTIMIZE TABLE `users`;
OPTIMIZE TABLE `mlangorder_printauto`;
OPTIMIZE TABLE `shop_temp`;
ANALYZE TABLE `users`;
ANALYZE TABLE `mlangorder_printauto`;
```

### STEP 4: 데이터 정리
```sql
-- 오래된 장바구니 데이터 삭제 (30일 이상)
DELETE FROM `shop_temp` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- 테스트 데이터나 불필요한 데이터 정리
-- DELETE FROM `테이블명` WHERE 조건;
```

### STEP 5: MySQL 설정 최적화
**파일 위치**: `C:\xampp\mysql\bin\my.ini` (또는 `/etc/mysql/my.cnf`)

```ini
[mysqld]
# 기본 성능 설정
key_buffer_size = 256M          # MyISAM 테이블용
max_allowed_packet = 64M        # 최대 패킷 크기
table_open_cache = 2000         # 테이블 캐시
sort_buffer_size = 2M           # 정렬 버퍼
read_buffer_size = 2M           # 읽기 버퍼

# 쿼리 캐시 (반복 쿼리 속도 향상)
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# 연결 최적화
max_connections = 200           # 최대 연결 수
thread_cache_size = 8           # 스레드 캐시

# InnoDB 설정 (InnoDB 사용 시)
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1

# 느린 쿼리 로그
slow_query_log = 1
slow_query_log_file = C:/xampp/mysql/data/slow_query.log
long_query_time = 2
```

## 🚨 최적화 후 확인사항

### 1. 성능 측정
```sql
-- 쿼리 실행 계획 확인
EXPLAIN SELECT * FROM mlangorder_printauto WHERE date > '2024-01-01';

-- 테이블 상태 확인
SHOW TABLE STATUS LIKE 'mlangorder_printauto';

-- 인덱스 사용률 확인
SHOW INDEX FROM mlangorder_printauto;
```

### 2. 주요 지표 모니터링
- **쿼리 실행 시간**: 2초 이하 목표
- **테이블 스캔**: 전체 스캔 최소화
- **인덱스 효율성**: 90% 이상
- **메모리 사용량**: 시스템 메모리의 70% 이하

### 3. 애플리케이션 테스트
- [ ] 메인 페이지 로딩 속도 확인
- [ ] 제품 페이지 가격 계산 속도 확인
- [ ] 주문 처리 과정 정상 동작 확인
- [ ] 관리자 페이지 주문 조회 속도 확인

## ⚡ 즉시 적용 가능한 최적화

### PHP 코드 레벨 최적화

#### 1. 연결 최적화
```php
// db.php 개선
mysqli_options($db, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
mysqli_set_charset($db, "utf8mb4");

// 영구 연결 사용 (필요시)
$db = mysqli_connect('p:localhost', 'user', 'pass', 'db');
```

#### 2. 쿼리 최적화
```php
// ❌ 나쁜 예
$query = "SELECT * FROM mlangorder_printauto";

// ✅ 좋은 예
$query = "SELECT no, name, phone, date FROM mlangorder_printauto WHERE date >= ?";
$stmt = mysqli_prepare($db, $query);
```

#### 3. 캐싱 적용
```php
// 세션 캐싱
if (!isset($_SESSION['price_cache'])) {
    $_SESSION['price_cache'] = [];
}

// 파일 캐싱
$cache_file = "cache/price_" . md5($params) . ".json";
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 3600) {
    return json_decode(file_get_contents($cache_file), true);
}
```

## 🔄 정기 유지보수

### 주간 작업
```bash
# 매주 일요일 실행
OPTIMIZE TABLE mlangorder_printauto, shop_temp, users;
ANALYZE TABLE mlangorder_printauto, shop_temp, users;
```

### 월간 작업
```bash
# 매월 1일 실행
# 1. 오래된 데이터 정리
DELETE FROM shop_temp WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

# 2. 로그 파일 정리
# slow_query.log 크기 확인 후 필요시 삭제

# 3. 백업 생성
mysqldump -u duson1830 -pdu1830 duson1830 > monthly_backup.sql
```

## 📈 성능 모니터링

### 1. MySQL 상태 확인
```sql
SHOW GLOBAL STATUS LIKE 'Qcache%';           -- 쿼리 캐시 효율성
SHOW GLOBAL STATUS LIKE 'Key_read%';         -- 키 캐시 효율성
SHOW GLOBAL STATUS LIKE 'Table_locks%';      -- 테이블 락 상태
SHOW PROCESSLIST;                            -- 현재 실행 중인 쿼리
```

### 2. 느린 쿼리 분석
```bash
# 느린 쿼리 로그 분석 (주간 점검)
mysqldumpslow C:/xampp/mysql/data/slow_query.log
```

## 🆘 문제 해결

### 최적화 후 문제 발생 시
1. **백업에서 즉시 복원**
   ```bash
   mysql -u duson1830 -pdu1830 duson1830 < backup_before_optimization.sql
   ```

2. **단계별 재적용**
   - 인덱스만 먼저 적용
   - 설정 변경은 나중에

3. **로그 확인**
   - MySQL 에러 로그 확인
   - PHP 에러 로그 확인

### 일반적인 문제
- **메모리 부족**: innodb_buffer_pool_size 조정
- **연결 실패**: max_connections 증가
- **느린 쿼리**: 인덱스 추가 검토

## 📞 지원

최적화 과정에서 문제가 발생하면:
1. 백업부터 복원
2. 단계별로 천천히 재적용
3. 각 단계마다 테스트 수행

## 🎉 예상 효과

### 성능 개선 예상치
- **페이지 로딩 속도**: 2-3초 → 1초 이하
- **주문 검색**: 5초 → 1초 이하  
- **가격 계산**: 1초 → 0.3초 이하
- **관리자 페이지**: 10초 → 3초 이하

### 사용자 경험 개선
- 페이지 반응 속도 향상
- 주문 처리 시간 단축
- 관리 효율성 증대
- 서버 안정성 향상

---
*생성일: 2025-01-10*  
*최적화 도구 버전: 1.0*  
*적용 대상: duson1830 데이터베이스*