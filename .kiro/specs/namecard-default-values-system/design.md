# 명함 페이지 기본값 자동 설정 시스템 설계

## 시스템 개요

명함 주문 페이지에서 사용자 편의성을 향상시키기 위한 데이터베이스 기반 기본값 자동 설정 시스템입니다.

## 아키텍처

### 전체 구조도

```
사용자 브라우저
    ↓ (페이지 접속)
명함 페이지 (index.php)
    ↓ (기본값 조회)
데이터베이스 (MlangPrintAuto_transactionCate, MlangPrintAuto_namecard)
    ↓ (기본값 반환)
JavaScript (자동 옵션 로드)
    ↓ (AJAX 요청)
AJAX 파일들 (get_paper_types.php, get_quantities.php)
    ↓ (완료)
사용자에게 기본값 설정된 폼 표시
```

## 컴포넌트 설계

### 1. PHP 기본값 설정 로직

**위치:** `MlangPrintAuto/NameCard/index.php`

```php
// 기본값 설정 (데이터베이스에서 가져오기)
$default_values = [
    'MY_type' => '',      // 명함 종류
    'Section' => '',      // 명함 재질
    'POtype' => '1',      // 인쇄면 (기본: 단면)
    'MY_amount' => '',    // 수량
    'ordertype' => 'print' // 편집디자인 (기본: 인쇄만)
];

// 1단계: 명함 종류 선택 (일반명함(쿠폰) 우선)
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='NameCard' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%일반명함%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";

// 2단계: 해당 종류의 첫 번째 재질 선택
$section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                  WHERE Ttable='NameCard' AND BigNo='{$selected_type}' 
                  ORDER BY no ASC LIMIT 1";

// 3단계: 해당 조합의 기본 수량 선택 (500매 우선)
$quantity_query = "SELECT DISTINCT quantity FROM MlangPrintAuto_namecard 
                  WHERE style='{$selected_type}' AND Section='{$selected_section}' 
                  ORDER BY CASE WHEN quantity='500' THEN 1 ELSE 2 END, 
                           CAST(quantity AS UNSIGNED) ASC LIMIT 1";
```

### 2. HTML 폼 기본값 적용

**기능:** select 태그에 selected 속성 자동 설정

```php
// 명함 종류 select
foreach ($categories as $category) {
    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
    echo "<option value='{$category['no']}' $selected>{$category['title']}</option>";
}

// 인쇄면 select
<option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
<option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>

// 편집디자인 select
<option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
<option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
```

### 3. JavaScript 자동 로드 시스템

**위치:** `MlangPrintAuto/NameCard/index.php` (하단 script 태그)

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // 페이지 로드 시 기본값이 선택되어 있으면 자동으로 하위 옵션들 로드
    if (typeSelect.value) {
        loadPaperTypes(typeSelect.value);
    }
});

// 명함 재질 로드 함수
function loadPaperTypes(style) {
    fetch(`get_paper_types.php?style=${style}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSelectWithOptions(paperSelect, data.data, '명함 재질을 선택해주세요');
                
                // 기본값이 있으면 선택하고 수량 로드
                paperSelect.value = '<?php echo $default_values['Section']; ?>';
                if (paperSelect.value && sideSelect.value) {
                    loadQuantities();
                }
            }
        });
}

// 수량 로드 함수
function loadQuantities() {
    fetch(`get_quantities.php?style=${style}&section=${section}&potype=${potype}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                
                // 기본값이 있으면 선택
                quantitySelect.value = '<?php echo $default_values['MY_amount']; ?>';
            }
        });
}
```

## 데이터 모델

### 1. 기본값 우선순위 규칙

```sql
-- 명함 종류: "일반명함" 포함 항목 우선
ORDER BY CASE WHEN title LIKE '%일반명함%' THEN 1 ELSE 2 END, no ASC

-- 명함 재질: 번호 순서대로 첫 번째
ORDER BY no ASC LIMIT 1

-- 수량: 500매 우선, 없으면 숫자 순서
ORDER BY CASE WHEN quantity='500' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC
```

### 2. 데이터베이스 테이블 관계

```
MlangPrintAuto_transactionCate (카테고리 테이블)
├── Ttable = 'NameCard'
├── BigNo = '0' (최상위 카테고리 = 명함 종류)
└── BigNo = '{parent_no}' (하위 카테고리 = 명함 재질)

MlangPrintAuto_namecard (가격 테이블)
├── style (명함 종류 번호)
├── Section (명함 재질 번호)
├── quantity (수량)
└── POtype (인쇄면)
```

## 오류 처리

### 1. 데이터베이스 오류 처리

```php
// 쿼리 실행 실패 시 빈 값으로 폴백
$type_result = mysqli_query($db, $type_query);
if (!$type_result || mysqli_num_rows($type_result) == 0) {
    // 기본값을 빈 값으로 유지
    error_log("명함 종류 조회 실패: " . mysqli_error($db));
}
```

### 2. JavaScript 오류 처리

```javascript
fetch(`get_paper_types.php?style=${style}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 성공 처리
        } else {
            console.error('재질 로드 실패:', data.message);
        }
    })
    .catch(error => {
        console.error('재질 로드 오류:', error);
        // 사용자에게 수동 선택 안내
    });
```

## 성능 최적화

### 1. 쿼리 최적화

- LIMIT 1 사용으로 불필요한 데이터 조회 방지
- 인덱스 활용을 위한 WHERE 조건 최적화
- CASE WHEN을 사용한 우선순위 정렬

### 2. JavaScript 최적화

- 중복 AJAX 요청 방지
- 조건부 로드로 불필요한 요청 제거
- 에러 발생 시 빠른 폴백

## 테스트 전략

### 1. 단위 테스트

- 각 기본값 설정 쿼리 개별 테스트
- JavaScript 함수별 동작 테스트
- 오류 상황별 처리 테스트

### 2. 통합 테스트

- 전체 기본값 설정 플로우 테스트
- 다양한 데이터 조합에서의 동작 테스트
- 브라우저별 호환성 테스트

### 3. 디버깅 도구

**파일:** `MlangPrintAuto/NameCard/debug_defaults.php`

- 기본값 설정 과정 시각화
- 실행된 쿼리 및 결과 표시
- 오류 발생 지점 추적

## 배포 고려사항

### 1. 기존 시스템과의 호환성

- 기존 AJAX 파일들과 완전 호환
- 기존 UI/UX 변경 없음
- 기존 데이터베이스 스키마 유지

### 2. 점진적 배포

1. 디버그 모드로 기본값 설정 테스트
2. 일부 사용자 대상 베타 테스트
3. 전체 사용자 대상 배포

### 3. 모니터링

- 기본값 설정 성공률 모니터링
- AJAX 요청 실패율 추적
- 사용자 행동 패턴 분석

## 확장 가능성

### 1. 다른 품목 적용

동일한 패턴을 다른 품목(카다록, 전단지 등)에도 적용 가능

### 2. 개인화 기본값

사용자별 주문 이력 기반 개인화된 기본값 설정

### 3. A/B 테스트

다양한 기본값 조합의 효과 측정 및 최적화