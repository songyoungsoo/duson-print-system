# Member → Users 마이그레이션 가이드

## 📋 개요

레거시 `member` 테이블 데이터를 현대적인 `users` 테이블로 마이그레이션하는 스크립트입니다.

## 🎯 마이그레이션 내용

### 필드 매핑

| member 필드 | users 필드 | 변환 방법 |
|-------------|-----------|----------|
| `id` | `username` | 그대로 복사 |
| `pass` | `password` | bcrypt 해싱 |
| `name` | `name` | 그대로 복사 |
| `email` | `email` | 그대로 복사 |
| `phone1`, `phone2`, `phone3` | `phone` | "02-2632-1830" 형식으로 결합 |
| `hendphone1`, `hendphone2`, `hendphone3` | `phone` | 일반전화 없으면 핸드폰 사용 |
| `sample6_postcode` | `postcode` | 그대로 복사 |
| `sample6_address` | `address` | 그대로 복사 |
| `sample6_detailAddress` | `detail_address` | 그대로 복사 |
| `sample6_extraAddress` | `extra_address` | 그대로 복사 |
| `po1` | `business_number` | 사업자등록번호 |
| `po2` | `business_name` | 상호 |
| `po3` | `business_owner` | 대표자명 |
| `po4` | `business_type` | 업태 |
| `po5` | `business_item` | 종목 |
| `po6` | `business_address` | 사업장주소 |
| `po7` | `tax_invoice_email` | 세금계산서 이메일 |
| `level` | `level` | 그대로 복사 |
| `Logincount` | `login_count` | 그대로 복사 |
| `EndLogin` | `last_login` | 그대로 복사 |
| `date` | `created_at` | 그대로 복사 |
| `no` | `original_member_no` | 원본 추적용 |

### 특수 처리

1. **비밀번호 해싱**
   - member: 평문 또는 약한 암호화 (varchar 20)
   - users: bcrypt 해싱 (varchar 255)

2. **전화번호 통합**
   - member: phone1='02', phone2='2632', phone3='1830'
   - users: phone='02-2632-1830'

3. **중복 방지**
   - 이미 users에 존재하는 username은 건너뜀

## 🚀 실행 방법

### 1. 사전 준비

**백업 생성 (필수!)**
```bash
# users 테이블 백업
mysqldump -u root dsp1830 users > users_backup_$(date +%Y%m%d_%H%M%S).sql

# member 테이블 백업
mysqldump -u root dsp1830 member > member_backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. 스크립트 실행

```bash
# 마이그레이션 스크립트 실행
php /var/www/html/scripts/migrate_member_to_users.php
```

### 3. 실행 과정

```
============================================
Member → Users 마이그레이션 스크립트
============================================

[1] 현재 데이터 확인 중...
   - member 테이블: 216명
   - users 테이블: 244명

[2] 마이그레이션 대상 확인 중...
   - 마이그레이션 대상: 50명
   - 이미 마이그레이션됨: 166명

계속하시겠습니까? (yes/no): yes

[3] 마이그레이션 시작...

   ✓ [1/50] testuser (홍길동)
   ✓ [2/50] company1 (ABC회사)
   ...

[4] 마이그레이션 완료!

============================================
마이그레이션 결과
============================================
총 member 레코드:        216명
신규 마이그레이션:        50명
실패:                     0명
============================================
```

## ✅ 검증

### 1. 데이터 개수 확인

```sql
-- member 테이블
SELECT COUNT(*) FROM member;

-- users 테이블 (마이그레이션된 것만)
SELECT COUNT(*) FROM users WHERE migrated_from_member = 1;
```

### 2. 샘플 데이터 확인

```sql
-- 마이그레이션된 사용자 확인
SELECT
    username,
    name,
    email,
    phone,
    business_name,
    original_member_no
FROM users
WHERE migrated_from_member = 1
LIMIT 10;
```

### 3. 필드 매핑 확인

```sql
-- member와 users 비교
SELECT
    m.id as member_id,
    u.username as users_username,
    CONCAT(m.phone1, '-', m.phone2, '-', m.phone3) as member_phone,
    u.phone as users_phone,
    m.po2 as member_business,
    u.business_name as users_business
FROM member m
INNER JOIN users u ON m.id = u.username
WHERE u.migrated_from_member = 1
LIMIT 10;
```

## 🔄 재실행

스크립트는 **멱등성(idempotent)**을 보장합니다:
- 이미 마이그레이션된 데이터는 건너뜀
- 여러 번 실행해도 안전
- 새로 추가된 member만 마이그레이션

```bash
# 다시 실행해도 안전
php /var/www/html/scripts/migrate_member_to_users.php
```

## ⚠️ 주의사항

### 실행 전
1. **반드시 백업** 수행
2. 테스트 환경에서 먼저 실행 권장
3. 운영 시간 외 실행 권장

### 실행 중
1. 프로세스 중단 금지
2. 트랜잭션 사용으로 실패 시 자동 롤백
3. 대량 데이터 시 시간 소요 가능

### 실행 후
1. 검증 쿼리 실행 필수
2. 로그인 테스트 필수
3. 비밀번호 재설정 안내 (bcrypt 전환됨)

## 🛡️ 안전장치

### 1. 트랜잭션
```php
mysqli_begin_transaction($db);
try {
    // 마이그레이션 작업
    mysqli_commit($db);
} catch (Exception $e) {
    mysqli_rollback($db); // 오류 시 모든 변경 취소
}
```

### 2. 중복 방지
```sql
-- 이미 존재하는 username은 제외
LEFT JOIN users u ON m.id = u.username
WHERE u.username IS NULL
```

### 3. 데이터 검증
- NULL 처리
- 빈 문자열 처리
- 날짜 형식 검증

## 📊 예상 결과

### 마이그레이션 전
- member: 216명
- users: 244명 (신규 가입 포함)

### 마이그레이션 후
- member: 216명 (유지)
- users: 250~300명 (member 데이터 추가)

## 🔧 문제 해결

### 오류: "Duplicate entry for key 'username'"
**원인**: username이 이미 users에 존재
**해결**: 정상 동작. 해당 레코드는 자동으로 건너뜀

### 오류: "Data too long for column 'password'"
**원인**: password 필드가 255자 미만
**해결**:
```sql
ALTER TABLE users MODIFY password VARCHAR(255);
```

### 오류: "Unknown column 'tax_invoice_email'"
**원인**: users 테이블에 필드가 없음
**해결**:
```sql
ALTER TABLE users ADD COLUMN tax_invoice_email VARCHAR(200) DEFAULT NULL AFTER business_address;
```

## 📝 롤백 방법

마이그레이션 실패 또는 문제 발생 시:

```bash
# 1. 백업 복구
mysql -u root dsp1830 < users_backup_YYYYMMDD_HHMMSS.sql

# 2. 또는 마이그레이션된 데이터만 삭제
mysql -u root dsp1830 -e "DELETE FROM users WHERE migrated_from_member = 1;"
```

## 🎓 다음 단계

마이그레이션 완료 후:

1. **로그인 시스템 통합**
   - member와 users 모두 확인하는 하이브리드 로그인
   - 점진적으로 users만 사용하도록 전환

2. **회원가입 시스템**
   - `/member/join.php` → `/member/register_unified.php` 리디렉션
   - 신규 회원은 users 테이블에만 저장

3. **세션 시스템 업데이트**
   - member 기반 세션 → users 기반 세션 전환

4. **마이페이지 업데이트**
   - users 테이블 기반으로 정보 조회/수정

## 📞 지원

문제 발생 시 로그 확인:
- `/var/www/html/scripts/migrate_member_to_users.php` 출력 로그
- MySQL error log
- PHP error log
