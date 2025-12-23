# 추가 옵션 표시 시스템 수정 완료 보고서

**작성일**: 2025-10-09
**작업자**: SuperClaude
**카테고리**: 버그 수정 (Bug Fix)

---

## 📋 목차

1. [문제 개요](#문제-개요)
2. [원인 분석](#원인-분석)
3. [데이터베이스 구조 확인](#데이터베이스-구조-확인)
4. [수정 내용](#수정-내용)
5. [테스트 케이스](#테스트-케이스)
6. [관련 파일](#관련-파일)
7. [향후 개선 사항](#향후-개선-사항)

---

## 문제 개요

### 증상
- **URL**: `http://localhost/admin/MlangPrintAuto/admin.php?mode=OrderView&no=90081`
- **문제**: 주문 상세 정보에서 추가 옵션(양면테이프, 코팅, 접지, 오시)이 표시되지 않음
- **주문 데이터**: 주문 90081에는 양면테이프 옵션(1,000개, 40,000원)이 설정되어 있음

### 영향 범위
1. **웹 화면**: 주문 상세 정보 (admin.php?mode=OrderView)
2. **인쇄 화면**: 관리자용 주문서
3. **인쇄 화면**: 직원용 주문서

---

## 원인 분석

### 근본 원인

코드가 **Type_1 JSON의 `additional_options` 필드**에서만 옵션을 찾고 있었고, **데이터베이스 개별 컬럼**을 확인하지 않았습니다.

```php
// ❌ 이전 로직 (잘못된 방식)
if (isset($typeData['additional_options'])) {
    $options = $typeData['additional_options'];
    if (isset($options['coating']) && $options['coating']['enabled']) {
        // 코팅 표시
    }
}
```

### 실제 데이터 저장 위치

추가 옵션은 **개별 컬럼**에 저장됩니다:

| 컬럼명 | 설명 | 예시 값 |
|--------|------|---------|
| `coating_enabled` | 코팅 사용 여부 | 0 또는 1 |
| `coating_type` | 코팅 종류 | single, double, single_matte, double_matte |
| `coating_price` | 코팅 가격 | 15000 |
| `folding_enabled` | 접지 사용 여부 | 0 또는 1 |
| `folding_type` | 접지 종류 | 2fold, 3fold, accordion, gate |
| `folding_price` | 접지 가격 | 20000 |
| `creasing_enabled` | 오시 사용 여부 | 0 또는 1 |
| `creasing_lines` | 오시 줄 수 | 1, 2, 3 |
| `creasing_price` | 오시 가격 | 10000 |
| `envelope_tape_enabled` | 양면테이프 사용 여부 | 0 또는 1 |
| `envelope_tape_quantity` | 양면테이프 수량 | 1000 |
| `envelope_tape_price` | 양면테이프 가격 | 40000 |

---

## 데이터베이스 구조 확인

### mlangorder_printauto 테이블

```sql
SHOW COLUMNS FROM mlangorder_printauto WHERE Field LIKE '%coating%'
    OR Field LIKE '%folding%'
    OR Field LIKE '%creasing%'
    OR Field LIKE '%envelope%'
    OR Field LIKE '%tape%';
```

**결과**:
```
coating_enabled          tinyint(1)  DEFAULT 0
coating_type             varchar(20) DEFAULT NULL
coating_price            int         DEFAULT 0
folding_enabled          tinyint(1)  DEFAULT 0
folding_type             varchar(20) DEFAULT NULL
folding_price            int         DEFAULT 0
creasing_enabled         tinyint(1)  DEFAULT 0
creasing_lines           int         DEFAULT 0
creasing_price           int         DEFAULT 0
envelope_tape_enabled    tinyint(1)  DEFAULT 0
envelope_tape_quantity   int         DEFAULT 0
envelope_tape_price      int         DEFAULT 0
envelope_additional_options_total  int  DEFAULT 0
```

### shop_temp 테이블 (동일 구조)

`shop_temp`와 `mlangorder_printauto` 테이블은 동일한 추가 옵션 컬럼을 가지고 있습니다.

### 주문 90081 데이터 확인

```sql
SELECT no, coating_enabled, folding_enabled, creasing_enabled,
       envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price
FROM mlangorder_printauto WHERE no = 90081;
```

**결과**:
```
no: 90081
coating_enabled: 0
folding_enabled: 0
creasing_enabled: 0
envelope_tape_enabled: 1
envelope_tape_quantity: 1000
envelope_tape_price: 40000
```

✅ **양면테이프 옵션이 개별 컬럼에 정상적으로 저장되어 있음**

---

## 수정 내용

### 1. 웹 화면용 주문 상세 정보

**파일**: `/var/www/html/mlangorder_printauto/OrderFormOrderTree.php`
**라인**: 954-1085

#### 변경 사항

**이전 구조**:
```php
// Type_1 JSON의 additional_options 필드에서만 검색
if (isset($typeData['additional_options'])) {
    $options = $typeData['additional_options'];
    if (isset($options['coating']) && $options['coating']['enabled']) {
        // 코팅 표시
    }
}
```

**새로운 구조**:
```php
// 🔧 최우선: 데이터베이스 개별 컬럼에서 직접 읽기

// 1. 코팅 옵션
if (!empty($row['coating_enabled']) && $row['coating_enabled'] == 1) {
    $coating_price = intval($row['coating_price'] ?? 0);
    $coating_type = htmlspecialchars($row['coating_type'] ?? '');

    // 타입 한글 변환
    $coating_type_kr = $coating_type;
    if ($coating_type == 'single') $coating_type_kr = '단면유광코팅';
    elseif ($coating_type == 'double') $coating_type_kr = '양면유광코팅';
    elseif ($coating_type == 'single_matte') $coating_type_kr = '단면무광코팅';
    elseif ($coating_type == 'double_matte') $coating_type_kr = '양면무광코팅';

    // HTML 출력
}

// 2. 접지 옵션
if (!empty($row['folding_enabled']) && $row['folding_enabled'] == 1) {
    // 접지 표시 로직
}

// 3. 오시 옵션
if (!empty($row['creasing_enabled']) && $row['creasing_enabled'] == 1) {
    // 오시 표시 로직
}

// 4. 양면테이프 옵션
if (!empty($row['envelope_tape_enabled']) && $row['envelope_tape_enabled'] == 1) {
    $tape_quantity = intval($row['envelope_tape_quantity'] ?? 0);
    $tape_price = intval($row['envelope_tape_price'] ?? 0);
    // 양면테이프 표시
}

// 🔧 Fallback: Type_1 JSON (레거시 데이터용)
if (!empty($View_Type_1)) {
    // DB 컬럼에 없는 경우만 JSON 확인
}
```

### 2. 인쇄용 주문서 (관리자용 + 직원용)

**파일**: `/var/www/html/mlangorder_printauto/OrderFormOrderTree.php`
**라인**: 641-713 (관리자용), 742-814 (직원용)

#### 변경 사항

**이전**: 프리미엄 옵션만 표시
```php
// 프리미엄 옵션 표시
if (!empty($row['premium_options'])) {
    $premium_opts = json_decode($row['premium_options'], true);
    $opt_names = ['foil' => '박', 'numbering' => '넘버링', ...];
    // 박, 넘버링, 미싱, 모서리라운딩만 표시
}
```

**개선**: 추가 옵션 + 프리미엄 옵션 모두 표시
```php
// 🔧 추가 옵션 표시 (코팅, 접지, 오시, 양면테이프)
$print_opt_list = [];

// 1. 코팅
if (!empty($row['coating_enabled']) && $row['coating_enabled'] == 1) {
    $coating_type_kr = $row['coating_type'] ?? '';
    if ($coating_type_kr == 'single') $coating_type_kr = '단면유광코팅';
    elseif ($coating_type_kr == 'double') $coating_type_kr = '양면유광코팅';
    elseif ($coating_type_kr == 'single_matte') $coating_type_kr = '단면무광코팅';
    elseif ($coating_type_kr == 'double_matte') $coating_type_kr = '양면무광코팅';
    $coating_price = intval($row['coating_price'] ?? 0);
    if ($coating_price > 0) {
        $print_opt_list[] = '코팅(' . $coating_type_kr . ') ' . number_format($coating_price) . '원';
    }
}

// 2. 접지
if (!empty($row['folding_enabled']) && $row['folding_enabled'] == 1) {
    $folding_type_kr = $row['folding_type'] ?? '';
    if ($folding_type_kr == '2fold') $folding_type_kr = '2단접지';
    elseif ($folding_type_kr == '3fold') $folding_type_kr = '3단접지';
    elseif ($folding_type_kr == 'accordion') $folding_type_kr = '아코디언접지';
    elseif ($folding_type_kr == 'gate') $folding_type_kr = '게이트접지';
    $folding_price = intval($row['folding_price'] ?? 0);
    if ($folding_price > 0) {
        $print_opt_list[] = '접지(' . $folding_type_kr . ') ' . number_format($folding_price) . '원';
    }
}

// 3. 오시
if (!empty($row['creasing_enabled']) && $row['creasing_enabled'] == 1) {
    $creasing_lines = intval($row['creasing_lines'] ?? 0);
    $creasing_price = intval($row['creasing_price'] ?? 0);
    if ($creasing_price > 0) {
        $print_opt_list[] = '오시(' . $creasing_lines . '줄) ' . number_format($creasing_price) . '원';
    }
}

// 4. 양면테이프
if (!empty($row['envelope_tape_enabled']) && $row['envelope_tape_enabled'] == 1) {
    $tape_quantity = intval($row['envelope_tape_quantity'] ?? 0);
    $tape_price = intval($row['envelope_tape_price'] ?? 0);
    if ($tape_price > 0) {
        $print_opt_list[] = '양면테이프(' . number_format($tape_quantity) . '개) ' . number_format($tape_price) . '원';
    }
}

// 5. 프리미엄 옵션 (박, 넘버링, 미싱, 모서리라운딩)
if (!empty($row['premium_options'])) {
    $premium_opts = json_decode($row['premium_options'], true);
    if ($premium_opts && is_array($premium_opts)) {
        $opt_names = ['foil' => '박', 'numbering' => '넘버링', 'perforation' => '미싱', 'rounding' => '모서리라운딩'];
        foreach ($opt_names as $key => $name) {
            if (!empty($premium_opts[$key . '_enabled']) && $premium_opts[$key . '_enabled'] == 1) {
                $price = intval($premium_opts[$key . '_price'] ?? 0);
                if ($price > 0) {
                    $print_opt_list[] = $name . ' ' . number_format($price) . '원';
                }
            }
        }
    }
}

// 옵션 출력
if (!empty($print_opt_list)) {
    echo '<div style="margin-top: 1mm; font-size: 9pt; color: #e65100;">└ 옵션: ' . implode(', ', $print_opt_list) . '</div>';
}
```

---

## 테스트 케이스

### 테스트 1: 주문 90081 (양면테이프)

**URL**: `http://localhost/admin/MlangPrintAuto/admin.php?mode=OrderView&no=90081`

**기대 결과**:
```
주문상세
타입: 소봉투
용지: 소봉투(100모조 220*105)
수량: 1,000매
인쇄: 마스터1도
디자인: 인쇄만

인쇄비 75,000 / 디자인비 0 / 합계 82,500
└ 옵션: 양면테이프(1,000개) 40,000원
```

**인쇄 화면**:
- 관리자용 주문서: ✅ 옵션 표시
- 직원용 주문서: ✅ 옵션 표시

### 테스트 2: 코팅 옵션

**조건**: `coating_enabled = 1`, `coating_type = 'double'`, `coating_price = 15000`

**기대 결과**:
```
└ 옵션: 코팅(양면유광코팅) 15,000원
```

### 테스트 3: 접지 옵션

**조건**: `folding_enabled = 1`, `folding_type = '3fold'`, `folding_price = 20000`

**기대 결과**:
```
└ 옵션: 접지(3단접지) 20,000원
```

### 테스트 4: 오시 옵션

**조건**: `creasing_enabled = 1`, `creasing_lines = 2`, `creasing_price = 10000`

**기대 결과**:
```
└ 옵션: 오시(2줄) 10,000원
```

### 테스트 5: 복합 옵션

**조건**: 코팅 + 접지 + 오시 + 양면테이프 모두 활성화

**기대 결과**:
```
└ 옵션: 코팅(양면유광코팅) 15,000원, 접지(3단접지) 20,000원, 오시(2줄) 10,000원, 양면테이프(1,000개) 40,000원
```

### 테스트 6: 레거시 데이터

**조건**: 개별 컬럼이 비어있고 Type_1 JSON에만 데이터 존재

**기대 결과**: JSON fallback 로직으로 정상 표시

---

## 관련 파일

### 수정된 파일

| 파일 경로 | 수정 내용 | 라인 |
|-----------|----------|------|
| `/var/www/html/mlangorder_printauto/OrderFormOrderTree.php` | 웹 화면용 옵션 표시 로직 개선 | 954-1085 |
| `/var/www/html/mlangorder_printauto/OrderFormOrderTree.php` | 인쇄용 관리자 주문서 옵션 표시 추가 | 641-713 |
| `/var/www/html/mlangorder_printauto/OrderFormOrderTree.php` | 인쇄용 직원 주문서 옵션 표시 추가 | 742-814 |

### 참조 파일

| 파일 경로 | 설명 |
|-----------|------|
| `/var/www/html/CLAUDE_DOCS/06_ARCHIVE/Options_Storage_Analysis.md` | 추가 옵션 저장 시스템 전체 분석 문서 |
| `/var/www/html/mlangprintauto/envelope/add_to_basket.php` | 장바구니 추가 시 옵션 저장 로직 |
| `/var/www/html/mlangorder_printauto/OnlineOrder_unified.php` | 주문 처리 시 옵션 복사 로직 |

---

## 데이터 흐름 검증

### shop_temp → mlangorder_printauto

**장바구니 추가**:
```javascript
// calculator.js
formData.append("envelope_tape_enabled", 1);
formData.append("envelope_tape_quantity", 1000);
formData.append("envelope_tape_price", 40000);
```

**장바구니 저장**:
```php
// add_to_basket.php
INSERT INTO shop_temp (
    envelope_tape_enabled,
    envelope_tape_quantity,
    envelope_tape_price
) VALUES (?, ?, ?)
```

**주문 처리**:
```php
// OnlineOrder_unified.php
INSERT INTO mlangorder_printauto (
    envelope_tape_enabled,
    envelope_tape_quantity,
    envelope_tape_price
) SELECT
    envelope_tape_enabled,
    envelope_tape_quantity,
    envelope_tape_price
FROM shop_temp
WHERE session_id = ?
```

**주문서 표시**:
```php
// OrderFormOrderTree.php
if (!empty($row['envelope_tape_enabled']) && $row['envelope_tape_enabled'] == 1) {
    $tape_quantity = intval($row['envelope_tape_quantity'] ?? 0);
    $tape_price = intval($row['envelope_tape_price'] ?? 0);
    echo "양면테이프(" . number_format($tape_quantity) . "개) " . number_format($tape_price) . "원";
}
```

✅ **전체 데이터 흐름이 정상적으로 연결됨**

---

## 향후 개선 사항

### 1. 옵션 표시 로직 통합

현재 세 곳에서 유사한 로직이 중복됩니다:
- 웹 화면용 (954-1085줄)
- 관리자용 주문서 (641-713줄)
- 직원용 주문서 (742-814줄)

**개선 방안**:
```php
// includes/AdditionalOptionsDisplay.php 확장
class AdditionalOptionsDisplay {
    /**
     * 인쇄용 옵션 표시 (한 줄 형식)
     */
    public function renderForPrint($row) {
        $print_opt_list = [];

        // 코팅, 접지, 오시, 양면테이프 처리
        // ...

        if (!empty($print_opt_list)) {
            return '<div style="...">└ 옵션: ' . implode(', ', $print_opt_list) . '</div>';
        }
        return '';
    }
}
```

### 2. 옵션 타입 변환 함수화

타입 한글 변환 로직을 함수로 분리:
```php
// includes/option_helpers.php
function getCoatingTypeKorean($type) {
    $types = [
        'single' => '단면유광코팅',
        'double' => '양면유광코팅',
        'single_matte' => '단면무광코팅',
        'double_matte' => '양면무광코팅'
    ];
    return $types[$type] ?? $type;
}

function getFoldingTypeKorean($type) {
    $types = [
        '2fold' => '2단접지',
        '3fold' => '3단접지',
        'accordion' => '아코디언접지',
        'gate' => '게이트접지'
    ];
    return $types[$type] ?? $type;
}
```

### 3. 단위 테스트 추가

```php
// tests/AdditionalOptionsDisplayTest.php
class AdditionalOptionsDisplayTest extends PHPUnit\Framework\TestCase {
    public function testRenderCoatingOption() {
        $row = [
            'coating_enabled' => 1,
            'coating_type' => 'double',
            'coating_price' => 15000
        ];

        $display = new AdditionalOptionsDisplay($db);
        $result = $display->renderForPrint($row);

        $this->assertStringContainsString('코팅(양면유광코팅) 15,000원', $result);
    }

    public function testRenderEnvelopeTapeOption() {
        $row = [
            'envelope_tape_enabled' => 1,
            'envelope_tape_quantity' => 1000,
            'envelope_tape_price' => 40000
        ];

        $display = new AdditionalOptionsDisplay($db);
        $result = $display->renderForPrint($row);

        $this->assertStringContainsString('양면테이프(1,000개) 40,000원', $result);
    }
}
```

### 4. 로깅 추가

디버깅을 위한 로깅:
```php
if (is_local_environment() && !empty($_GET['debug_options'])) {
    error_log("Additional Options Debug:");
    error_log("coating_enabled: " . ($row['coating_enabled'] ?? 'null'));
    error_log("folding_enabled: " . ($row['folding_enabled'] ?? 'null'));
    error_log("creasing_enabled: " . ($row['creasing_enabled'] ?? 'null'));
    error_log("envelope_tape_enabled: " . ($row['envelope_tape_enabled'] ?? 'null'));
}
```

---

## 결론

### 해결된 문제
✅ 주문 상세 화면에서 추가 옵션(코팅, 접지, 오시, 양면테이프) 표시
✅ 인쇄용 관리자 주문서에서 추가 옵션 표시
✅ 인쇄용 직원 주문서에서 추가 옵션 표시
✅ 프리미엄 옵션과 추가 옵션 통합 표시

### 핵심 개선 사항
1. **데이터베이스 개별 컬럼 우선** - Type_1 JSON보다 개별 컬럼을 먼저 확인
2. **Fallback 로직** - 레거시 데이터를 위한 JSON 검색 유지
3. **한글 변환** - 옵션 타입을 사용자 친화적인 한글로 표시
4. **일관성** - 웹 화면과 인쇄 화면 모두 동일한 로직 적용

### 테스트 완료
- ✅ 주문 90081 (양면테이프 1,000개 40,000원) 정상 표시 확인

---

**문서 버전**: 1.0
**최종 수정일**: 2025-10-09
**작성자**: SuperClaude
**관련 이슈**: 주문서 출력 옵션 표시 문제 (#90081)
