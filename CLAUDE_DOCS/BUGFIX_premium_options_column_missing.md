# 버그 수정: premium_options 테이블 컬럼 누락으로 인한 500 에러

**발생일자**: 2026-03-09
**심각도**: 높음 (주문 상세 페이지 500 에러)

## 문제 현상

### 증상
- 교정용 주문(교정 파일 있음): 정상 작동
- 일반 주문(교정 파일 없음): 500 에러 발생

### 에러 메시지
```
Unknown column 'o.option_key' in 'field list'
파일: /var/www/vhosts/dsp114.com/httpdocs/includes/PremiumOptionsConfig.php:130
```

## 원인 분석

### 근본 원인
운영 서버(dsp114.com)의 `premium_options` 테이블이 로컬 개발 환경과 다른 구조를 가짐

| 환경 | 테이블 존재 | option_key 컬럼 |
|------|-----------|----------------|
| 로컬 | ✅ 존재 | ✅ 있음 |
| 운영 | ✅ 존재 | ❌ 없음 |

### 기존 코드의 문제
```php
// PremiumOptionsConfig.php Line 122-127 (기존)
$tableCheck = mysqli_query($dbConn, "SHOW TABLES LIKE 'premium_options'");
if (!$tableCheck || mysqli_num_rows($tableCheck) == 0) {
    return []; // 테이블이 없으면 빈 배열 반환
}

// 문제: 테이블은 있지만 컬럼이 없는 경우 미처리
$stmt = mysqli_prepare($dbConn, "SELECT o.option_key, ..."); // 500 에러!
```

**문제점**:
1. 테이블 존재 확인만 하고 컬럼 구조는 확인하지 않음
2. `option_key` 컬럼이 없으면 쿼리 실패 → 500 에러
3. 교정용 주문은 이 코드를 실행하지 않아서 작동 (조건문 차이)

### 일반 주문 vs 교정용 주문 차이
- **일반 주문**: `$premium_options_json`이 있으면 `PremiumOptionsConfig::parseSelectedOptions()` 호출 → `getOptions()` → DB 쿼리 실행
- **교정용 주문**: 코드 경로가 달라 DB 쿼리 실행 안 함

## 해결 방법

### 수정 내용

**파일**: `includes/PremiumOptionsConfig.php`

```php
// Line 122-136 (수정 후)

// 테이블 존재 확인 (PHP 8.2+ 호환성)
$tableCheck = mysqli_query($dbConn, "SHOW TABLES LIKE 'premium_options'");
if (!$tableCheck || mysqli_num_rows($tableCheck) == 0) {
    // 테이블이 없으면 빈 배열 반환 (레거시 시스템)
    return [];
}

// ✅ 추가: 컬럼 존재 확인 (테이블이 있어도 구조가 다를 수 있음)
$columnCheck = mysqli_query($dbConn, "SHOW COLUMNS FROM premium_options LIKE 'option_key'");
if (!$columnCheck || mysqli_num_rows($columnCheck) == 0) {
    // 컬럼이 없으면 빈 배열 반환 (구버전 테이블)
    return [];
}

// 쿼리 실행
$stmt = mysqli_prepare($dbConn, "SELECT o.option_key, ...");
```

### 해결 로직

```
1. premium_options 테이블 존재?
   ↓ NO
   빈 배열 반환 (레거시 시스템)
   ↓ YES
2. option_key 컬럼 존재?
   ↓ NO
   빈 배열 반환 (구버전 테이블)
   ↓ YES
3. 정상 쿼리 실행
```

## 함께 적용한 수정

### 1. view.php - PHP 8.2 호환성 개선
```php
// Line 53-58
if (!empty($type1_raw) && $type1_raw[0] === '{') {
    $type1_data = json_decode($type1_raw, true);
    // PHP 8.2 호환성: JSON 파싱 실패 시 빈 배열로 초기화
    if ($type1_data === null) {
        $type1_data = [];
    }
}
```

### 2. view.php - 에러 추적 추가
```php
// 전체 코드를 try-catch로 감싸서 상세 에러 메시지 표시
try {
    // ... 기존 코드 ...
} catch (Throwable $e) {
    // 에러 메시지, 파일, 줄번호, 스택 트레이스 표시
}
```

## 배포 내역

| 파일 | 변경 사항 |
|------|----------|
| `dashboard/orders/view.php` | PHP 8.2 null 체크, try-catch 추가 |
| `includes/PremiumOptionsConfig.php` | 컬럼 존재 확인 추가 |
| `dashboard/clear_opcache.php` | OPcache 클리너 (새 파일) |

## 교훈

### 1. DB 마이그레이션 시 체크리스트
- [ ] 테이블 존재 확인
- [ ] **컬럼 구조 확인** ← 누락되기 쉬움
- [ ] 인덱스 확인
- [ ] FK 제약조건 확인

### 2. 레거시 시스템 호환성
- 운영 서버는 다양한 버전의 DB 구조가 공존할 수 있음
- "테이블이 있으면 제대로 되겠지"라는 가정 위험
- **컬럼 단위 검증** 필요

### 3. 에러 디버깅 개선
- try-catch로 상세 에러 메시지 표시
- 500 에러 대신 구체적인 원인 파악 가능
- 개발 시간 단축

## 향후 작업

### 권장사항
1. **premium_options 테이블 구조 표준화**
   - 로컬과 운영 서버 DB 구조 동기화
   - 마이그레이션 스크립트 작성

2. **DB 구조 검증 유틸**
   ```php
   function validateTableStructure($tableName, array $requiredColumns) {
       // 테이블 및 컬럼 존재 확인
   }
   ```

3. **통합 테스트**
   - 로컬/운영 서버 DB 구조 비교
   - 배포 전 자동 검증

---

**수정자**: Claude (Sonnet 4.6 → Opus 4.6)
**검증자**: 사용자 확인 (작동 완료)
