# Member → Users 테이블 마이그레이션 가이드

## 🎯 목적
`member` 테이블 의존성을 완전히 제거하고 `users` 테이블로 통합

## 📋 마이그레이션 단계

### Step 1: 백업 및 준비 (`01_backup_and_prepare.php`)
- member 테이블 백업 생성
- users 테이블 백업 생성  
- users 테이블 구조 준비 (필요한 컬럼 추가)

```bash
php 01_backup_and_prepare.php
```

### Step 2: 데이터 마이그레이션 (`02_migrate_data.php`)
- member 테이블의 모든 데이터를 users 테이블로 이전
- 비밀번호 해시화 처리
- 중복 체크 및 업데이트

```bash
php 02_migrate_data.php
```

### Step 3: 참조 업데이트 (`03_update_references.php`)
- 모든 db.php 파일에서 `$admin_table = "member"`를 `"users"`로 변경
- SQL 쿼리 헬퍼 파일 생성

```bash
php 03_update_references.php
```

### Step 4: 테스트 및 검증 (`04_test_and_verify.php`)
- users 테이블 존재 확인
- 데이터 마이그레이션 검증
- 필수 컬럼 확인
- 데이터 무결성 체크

```bash
php 04_test_and_verify.php
```

### Step 5: 최종 완료 (`05_finalize.php`) - 선택사항
⚠️ **주의**: 모든 테스트 완료 후 실행!
- member 테이블 아카이빙
- 호환성 뷰 생성
- 최종 리포트 생성

```bash
php 05_finalize.php
```

## 🔄 롤백 절차

문제 발생 시 이전 상태로 복원:

```bash
php rollback.php
```

## 📊 컬럼 매핑

| member 테이블 | users 테이블 |
|--------------|-------------|
| no | member_no |
| id | username |
| pass | password (hashed) |
| name | name |
| email | email |
| phone1,2,3 | phone + phone1,2,3 |
| hendphone1,2,3 | hendphone1,2,3 |
| sample6_* | sample6_* |
| Logincount | login_count |
| EndLogin | last_login |
| level | level |
| date | created_at |

## ⚠️ 주의사항

1. **백업 필수**: 마이그레이션 전 반드시 백업 생성
2. **테스트 환경**: 프로덕션 적용 전 테스트 환경에서 먼저 실행
3. **순차 실행**: Step 1부터 순서대로 실행
4. **검증 필수**: Step 4에서 모든 테스트 통과 확인
5. **롤백 준비**: 문제 발생 시 즉시 rollback.php 실행

## 🔍 체크리스트

- [ ] 현재 데이터베이스 백업 완료
- [ ] Step 1: 백업 및 준비 실행
- [ ] Step 2: 데이터 마이그레이션 실행
- [ ] Step 3: 참조 업데이트 실행
- [ ] Step 4: 테스트 통과 확인
- [ ] 웹사이트 기능 테스트
  - [ ] 로그인/로그아웃
  - [ ] 회원가입
  - [ ] 주문 처리
  - [ ] 관리자 기능
- [ ] Step 5: 최종 완료 (선택)
- [ ] 백업 파일 안전한 곳에 보관

## 📁 생성되는 파일

- `member_backup_YYYYMMDD_HHMMSS`: member 테이블 백업
- `users_backup_YYYYMMDD_HHMMSS`: users 테이블 백업
- `migration_report_*.txt`: 마이그레이션 리포트
- `*.backup_*`: 수정된 파일들의 백업
- `cleanup_backups.php`: 백업 정리 스크립트

## 🆘 문제 해결

### 로그인이 안 되는 경우
1. users 테이블의 username과 password 확인
2. 비밀번호가 해시화되었는지 확인
3. includes/auth.php가 users 테이블을 사용하는지 확인

### 데이터가 누락된 경우
1. Step 4의 무결성 체크 결과 확인
2. member_backup 테이블에서 원본 데이터 확인
3. 필요시 수동으로 데이터 복구

### 페이지 오류 발생
1. error_log 확인
2. SQL 쿼리에서 member 테이블 참조 확인
3. includes/member_to_users_helper.php 활용

## 📞 지원
문제 발생 시 rollback.php를 먼저 실행하여 이전 상태로 복원하세요.