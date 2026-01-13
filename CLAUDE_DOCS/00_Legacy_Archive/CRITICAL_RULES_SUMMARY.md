# 🔴 CRITICAL RULES - 절대 규칙 요약

**최종 업데이트**: 2026-01-07
**목적**: Claude Code가 반드시 준수해야 하는 핵심 규칙

---

## 1. bind_param 검증 (3번 검증 필수)

```php
// ❌ NEVER: 눈으로 대충 세기
mysqli_stmt_bind_param($stmt, "issss...", ...);

// ✅ ALWAYS: 3번 검증
$placeholder_count = substr_count($query, '?');  // 1
$type_count = strlen($type_string);             // 2
$var_count = 7; // 손으로 세기                   // 3

if ($placeholder_count === $type_count && $type_count === $var_count) {
    mysqli_stmt_bind_param($stmt, $type_string, ...);
}
```

**이유**: 개수 불일치 시 주문자 이름이 '0'으로 저장되는 심각한 버그 발생

---

## 2. Database 규칙

- **테이블명**: 항상 소문자 (`mlangprintauto_namecard`)
- **연결 변수**: `$db` (legacy는 `$conn = $db;` alias)
- **Character Set**: utf8mb4

```php
// ❌ NEVER
$query = "SELECT * FROM MlangPrintAuto_NameCard";

// ✅ ALWAYS
$query = "SELECT * FROM mlangprintauto_namecard";
```

---

## 3. quantity_display 검증 규칙 (필수) ⭐ NEW (2026-01-07)

```php
// ❌ NEVER: quantity_display를 단위 체크 없이 그대로 사용
$line2 = implode(' / ', [$spec_sides, $item['quantity_display'], $spec_design]);

// ✅ ALWAYS: 단위가 없으면 formatQuantity() 호출
$quantity_display = $item['quantity_display'] ?? '';

// 단위 체크: 매, 연, 부, 권, 개, 장
if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
    $quantity_display = $this->formatQuantity($item);
}

$line2 = implode(' / ', [$spec_sides, $quantity_display, $spec_design]);
```

**이유**:
- DB에 `quantity_display = "1"`처럼 단위 없이 저장될 수 있음
- `formatQuantity()`는 `MY_amount=1000` → "1,000매" 자동 변환
- 천 단위 변환 로직 포함 (봉투/명함: `MY_amount < 10`이면 ×1000)

**적용 위치**:
- `ProductSpecFormatter::formatStandardized()` (lines 71-83)
- `ProductSpecFormatter::buildLine2()` (lines 323-331)
- 모든 수량 표시 로직

**상세 문서**: [2026-01-07_quantity_display_validation_fix.md](CHANGELOG/2026-01-07_quantity_display_validation_fix.md)

---

## 4. 파일명 규칙

- **All lowercase**: `cateadmin_title.php` (NOT `CateAdmin_title.php`)
- **Includes**: 소문자 경로만 사용 (Linux case-sensitive)
- **No symlinks**: 실제 디렉토리만 사용

```php
// ❌ NEVER
include "CateAdmin_title.php";

// ✅ ALWAYS
include "cateadmin_title.php";
```

---

## 5. 환경 자동 감지

```php
// ❌ NEVER: 하드코딩
$url = "http://dsp1830.shop/login.php";

// ✅ ALWAYS: 자동 감지
$url = $admin_url . "/login.php";
```

**이유**: DNS 전환만으로 코드 수정 없이 도메인 교체 가능

---

## 6. 테이블 레이아웃 규칙 (UI) ⭐ NEW (2026-01-07)

```php
// ❌ NEVER: colgroup 개수와 실제 컬럼 개수 불일치
<colgroup>
    <col style="width: 10%;">
    <col style="width: 20%;">
    <col style="width: 30%;">  <!-- 3개 정의 -->
</colgroup>
<tr>
    <th>칼럼1</th>
    <th>칼럼2</th>  <!-- 2개만 사용 → 빈 공란 발생! -->
</tr>

// ✅ ALWAYS: colgroup = 실제 컬럼 개수 일치
<colgroup>
    <col style="width: 40%;">
    <col style="width: 60%;">  <!-- 2개 정의 -->
</colgroup>
<tr>
    <th>칼럼1</th>
    <th>칼럼2</th>  <!-- 2개 사용 ✓ -->
</tr>

// ✅ ALWAYS: colspan 값 일관성 확보
<td colspan="6">헤더</td>  <!-- 6개 컬럼이면 모든 행에서 6 사용 -->
```

**이유**:
- colgroup 컬럼 수 > 실제 컬럼 수 → 오른쪽에 빈 공란 발생
- colspan 불일치 → 레이아웃 깨짐
- 너비 합계 ≠ 100% → 예상치 못한 레이아웃

**체크리스트**:
1. colgroup의 `<col>` 개수 = 테이블 헤더 `<th>` 개수
2. 모든 컬럼 너비 합계 = 100%
3. colspan 값이 모든 행에서 일관성 있게 사용
4. 중앙 정렬이 필요한 컬럼은 `text-align: center` 명시

**적용 파일**:
- `mlangorder_printauto/OrderFormOrderTree.php` (lines 1055-1082)

**상세 문서**: [2026-01-07_admin_order_view_layout_fix.md](CHANGELOG/2026-01-07_admin_order_view_layout_fix.md)

---

## 7. Common Pitfalls (자주 하는 실수)

1. ❌ bind_param 개수 불일치 → 주문자 이름 '0' 저장
2. ❌ 대문자 테이블명 사용 → SELECT 실패
3. ❌ 대문자 include 경로 → Linux에서 파일 못 찾음
4. ❌ number_format(0.5) → "1" 반올림 오류
5. ❌ `littleprint`를 `poster`로 변경 → 시스템 전체 오류
6. ❌ **quantity_display 단위 체크 안함** → "1"로 표시
7. ❌ **colgroup 개수 ≠ 실제 컬럼 개수** → 오른쪽 빈 공란 발생 ⭐ NEW

---

## 📚 관련 문서

### 핵심 문서
- [CLAUDE.md](../CLAUDE.md) - 프로젝트 개요 및 빠른 참조
- [CRITICAL_RULES_SUMMARY.md](CRITICAL_RULES_SUMMARY.md) - 이 문서

### Changelog
- [2026-01-07_admin_order_view_layout_fix.md](CHANGELOG/2026-01-07_admin_order_view_layout_fix.md) ⭐ NEW
- [2026-01-07_quantity_display_validation_fix.md](CHANGELOG/2026-01-07_quantity_display_validation_fix.md)
- [2026-01-07_unified_format_implementation.md](CHANGELOG/2026-01-07_unified_format_implementation.md)

### 구현 가이드
- [UNIFIED_DISPLAY_TEMPLATE.md](DESIGN/UNIFIED_DISPLAY_TEMPLATE.md)
- [UNIFIED_FORMAT_USAGE.md](DESIGN/UNIFIED_FORMAT_USAGE.md)

---

**작성자**: Claude Code
**버전**: 1.2 (2026-01-07 updated - UI 레이아웃 규칙 추가)
**중요도**: 🔴 CRITICAL - 모든 개발 작업에서 필수 준수
