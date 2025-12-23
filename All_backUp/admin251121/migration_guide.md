# PHP 5.2 → 7.4 마이그레이션 실행 가이드

## 🎯 마이그레이션 전략

### 📋 사전 준비사항
1. **백업 확인**: 모든 파일의 백업이 자동으로 생성됩니다
2. **테스트 환경**: 가능하면 테스트 서버에서 먼저 실행
3. **DB 연결**: 현재 MySQLi 사용 중이므로 호환성 우수

## 🚀 실행 단계

### 1단계: DRY RUN (미리보기)
```bash
# 실제 변경 없이 마이그레이션 계획 확인
php php74_migration_script.php --dry-run
```

### 2단계: 실제 마이그레이션
```bash
# 실제 마이그레이션 실행 (백업 자동 생성)
php php74_migration_script.php
```

### 3단계: 수동 검토 필요 파일들
다음 파일들은 복잡한 로직으로 인해 수동 검토가 필요할 수 있습니다:
- `admin/member_X/admin.php` - 복잡한 폼 처리
- `admin/member_T/admin.php` - 파일 업로드 로직
- `admin/WomanMember/admin.php` - 다중 데이터 처리

## 🔍 마이그레이션 항목별 상세

### Phase 1: 기본 구문 호환성
- ✅ `<?` → `<?php declare(strict_types=1);`
- ✅ `$PHP_AUTH_USER` → `$_SERVER['PHP_AUTH_USER'] ?? ''`
- ✅ `$PHP_AUTH_PW` → `$_SERVER['PHP_AUTH_PW'] ?? ''`
- ✅ 기본 변수 초기화

### Phase 2: 데이터베이스 레이어
- ✅ `mysql_query()` → `mysqli_query($db, ...)`
- ✅ `mysql_fetch_array()` → `mysqli_fetch_array()`
- ✅ `mysql_close()` → `mysqli_close($db)`
- ⚠️ **수동 작업 필요**: Prepared Statements 변환

### Phase 3: 보안 강화
- ✅ `strcmp()` → `hash_equals()` (비밀번호 비교)
- ⚠️ **수동 작업 필요**: XSS 방지 (htmlspecialchars)
- ⚠️ **수동 작업 필요**: SQL Injection 방지 (Prepared Statements)

## 🛡️ 안전장치

### 자동 백업
모든 변경된 파일은 다음 형식으로 백업됩니다:
```
원본파일.backup_php52_20250924140530
```

### 롤백 방법
```bash
# 특정 파일 롤백
cp admin.php.backup_php52_20250924140530 admin.php

# 전체 롤백 (백업에서 복원)
find . -name "*.backup_php52_*" -exec sh -c 'cp "$1" "${1%.backup_php52_*}"' _ {} \;
```

## 🧪 테스트 절차

### 1. 구문 검사
```bash
# PHP 7.4 구문 검사
find . -name "*.php" -exec php -l {} \;
```

### 2. 웹 브라우저 테스트
- 관리자 로그인: `http://localhost/admin/`
- 주요 기능들 개별 테스트

### 3. 데이터베이스 연결 테스트
```bash
# DB 연결 테스트
curl -I http://localhost/admin/index.php
```

## ⚠️ 알려진 제한사항

### 수동 작업이 필요한 영역
1. **복잡한 SQL 쿼리**: Prepared Statements 변환
2. **파일 업로드 로직**: 현대적 방식으로 변경
3. **세션 관리**: PHP 7.4 세션 보안 강화
4. **XSS 방지**: 모든 출력에 htmlspecialchars 적용

### 주의사항
- 🚨 **데이터베이스 백업 필수**
- 🚨 **프로덕션 환경에서는 점검 시간에만 실행**
- 🚨 **PHP 7.4 환경 확인 필수**

## 📞 문제 해결

### 일반적인 오류들
1. **구문 오류**: 로그 파일에서 상세 정보 확인
2. **DB 연결 오류**: `mlangprintauto/db.php` 설정 확인
3. **권한 오류**: 파일 권한 확인 (`chmod 755`)

### 지원
- 로그 파일: `php74_migration_log_[timestamp].txt`
- 백업 파일들: `*.backup_php52_*` 패턴