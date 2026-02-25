## 📞 전화번호 자동 포맷팅 (Phone Number Formatting)

### 시스템 개요

전화번호 입력 시 자동 하이픈 삽입 + DB 기존 데이터 일괄 변환 시스템.

| 항목 | 값 |
|------|-----|
| **JS 위치** | `includes/footer.php` (라인 952~995), `en/checkout.php`, `dashboard/includes/footer.php` |
| **핵심 함수** | `formatKoreanPhone(v)` — 한국 전화번호 자동 하이픈 삽입 |
| **DB 백업** | `phone_backup_20260224` 테이블 (4,099건 원본 보존) |
| **변환 완료** | 2026-02-24, 1단계 (순수 숫자만 6,970건) |

### JS 포맷팅 동작

```javascript
// formatKoreanPhone() — 입력 값에서 숫자만 추출 후 하이픈 자동 삽입
// 02 지역번호: 02-XXX-XXXX (9자리) / 02-XXXX-XXXX (10자리)
// 010/0XX: 0XX-XXX-XXXX (10자리) / 010-XXXX-XXXX (11자리)

// applyPhoneFormat(input) — input 이벤트 리스너 + 페이지 로드 시 기존값 포맷팅
// 자동 탐지: input[type="tel"], input[name="phone"], input[name="Hendphone"]
// ID 기반: customer_phone, customer_mobile, qfm-phone
```

### 적용 파일 (JS 포맷팅)

| 파일 | footer 경로 | 비고 |
|------|------------|------|
| 주문 폼, 마이페이지, 견적위젯 등 | `includes/footer.php` | 대부분의 고객 페이지 |
| EN 체크아웃 | `en/checkout.php` | 별도 footer (includes/footer.php 미사용) |
| 대시보드 (주문등록 등) | `dashboard/includes/footer.php` | 관리자 페이지 |

### DB 변환 이력 (1단계 완료, 2026-02-24)

**변환 대상**: `mlangorder_printauto` 테이블의 `phone`, `Hendphone` 컬럼

| 컬럼 | 변환 건수 | 규칙 |
|------|----------|------|
| `phone` | 3,386건 | 11자리→3-4-4, 10자리 02→2-4-4, 10자리 0XX→3-3-4, 9자리 02→2-3-4 |
| `Hendphone` | 3,584건 | 동일 규칙 |
| **합계** | **6,970건** | |

**미변환 (비정상 4건)**:
- `000000000` — 더미 데이터
- `1030099410` ×2 — 0으로 시작하지 않는 비정상 번호
- `032246311` — 9자리인데 02가 아닌 지역번호 (자릿수 부족)

### 롤백 SQL

```sql
-- phone_backup_20260224 테이블로 원복
UPDATE mlangorder_printauto t
JOIN phone_backup_20260224 b ON t.no = b.no
SET t.phone = b.phone, t.Hendphone = b.Hendphone;
```

### Critical Rules

1. ✅ `phone_backup_20260224` 테이블 삭제 금지 — 롤백용 원본 보존
2. ✅ JS는 `formatKoreanPhone()` 함수 하나로 통일 — 3곳 동일 코드
3. ❌ 2단계 (공백/점/괄호/국제번호) 변환은 미구현 — 별도 작업 필요
4. ❌ `users.phone`은 변환 불필요 — 이미 전체 포맷됨 (332건)


