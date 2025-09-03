# 🎨 SmartFieldComponent System

**두손기획인쇄 스마트 필드 컴포넌트 시스템**

## 🎯 개요

필드명 혼란 문제를 해결하기 위한 **컨텍스트 기반 스마트 필드 렌더링 시스템**입니다.

동일한 필드명(`MY_type`, `PN_type` 등)이 제품별로 다른 의미를 가지는 문제를 **컨텍스트 매핑**과 **JOIN 쿼리**로 해결합니다.

## ⚡ 핵심 기능

- ✅ **컨텍스트 기반 필드 해석**: 동일한 필드가 제품별로 다른 의미로 표시
- ✅ **완전 DB 기반 동적 로딩**: 모든 옵션을 데이터베이스에서 실시간 조회
- ✅ **JOIN 쿼리 제목 매핑**: 번호 대신 실제 제목 표시 (590 → "소량포스터")
- ✅ **스마트 UI 렌더링**: 아이콘과 설명이 포함된 직관적 인터페이스
- ✅ **다중 제품 지원**: 8개 제품 (포스터, 전단지, 명함, 쿠폰, 봉투, 양식지, 자석스티커, 카다록)

## 📁 파일 구조

```
includes/
├── ProductFieldMapper.php      # 제품별 필드 컨텍스트 매핑
├── SmartFieldComponent.php     # 스마트 필드 렌더링 컴포넌트
├── js/
│   └── UniversalPriceHandler.js # 통합 가격 처리 시스템
└── ajax/
    └── update_dependent_fields.php # 연관 필드 업데이트 엔드포인트
```

## 🚀 빠른 시작

### 1. 기본 사용법

```php
<?php
// 데이터베이스 연결
include "db.php";

// 스마트 컴포넌트 로드
include "includes/SmartFieldComponent.php";

// 포스터용 스마트 컴포넌트 생성
$smartComponent = new SmartFieldComponent($db, 'poster');

// 모든 필드 렌더링
echo $smartComponent->renderAllFields();
?>
```

### 2. 개별 필드 렌더링

```php
// 특정 필드만 렌더링
echo $smartComponent->renderField('MY_type');  // 구분
echo $smartComponent->renderField('PN_type');  // 종이규격
echo $smartComponent->renderField('POtype');   // 인쇄면
```

### 3. 현재 값으로 초기화

```php
$current_values = [
    'MY_type' => '590',
    'PN_type' => '610',
    'POtype' => '1'
];

echo $smartComponent->renderAllFields($current_values);
```

## 🎨 지원 제품 목록

| 제품코드 | 제품명 | 주요 필드 매핑 |
|---------|--------|-------------|
| `poster` | 포스터 | MY_type→구분, PN_type→종이규격, POtype→인쇄면 |
| `leaflet` | 전단지 | MY_type→구분, PN_type→종이규격, POtype→인쇄면 |
| `namecard` | 명함 | MY_type→종류, PN_type→명함재질, POtype→인쇄면 |
| `coupon` | 쿠폰/상품권 | MY_type→종류, PN_type→규격선택, POtype→후가공 |
| `envelope` | 봉투 | MY_type→구분, PN_type→종류, POtype→인쇄색상 |
| `form` | 양식지 | MY_type→구분, PN_type→규격, MY_Fsd→인쇄색상 |
| `magnetic_sticker` | 자석스티커 | MY_type→종류, PN_type→규격 |
| `catalog` | 카다록 | MY_type→구분, PN_type→규격, MY_Fsd→종이종류 |

## 🔧 고급 사용법

### 1. 필드별 개별 옵션 설정

```php
$field_options = [
    'MY_type' => [
        'help_text' => '포스터 용도에 맞는 종류를 선택해주세요',
        'class' => 'custom-select-class'
    ],
    'POtype' => [
        'show_icon' => false,
        'required' => false
    ]
];

echo $smartComponent->renderAllFields($current_values, $field_options);
```

### 2. 연관 필드 업데이트 (AJAX)

```javascript
// UniversalPriceHandler가 자동으로 처리
// 필드 변경 시 연관된 필드들이 자동으로 업데이트됨

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('smart-field')) {
        // 자동으로 연관 필드 업데이트 및 가격 계산 실행
    }
});
```

### 3. 가격 계산 통합

```php
<!-- JavaScript 라이브러리 포함 -->
<script src="includes/js/UniversalPriceHandler.js"></script>

<script>
// 자동으로 초기화됨
// window.universalPriceHandler.calculatePrice() 로 수동 호출 가능
</script>
```

## 📊 데이터베이스 구조

### 핵심 테이블 관계

```
mlangprintauto_transactioncate (마스터 테이블)
├─ no: 590 → title: "소량포스터"
├─ no: 610 → title: "국2절"  
└─ no: 604 → title: "120아트/스노우"

mlangprintauto_littleprint (포스터 가격 데이터)
├─ style: 590 (소량포스터)
├─ Section: 610 (국2절)
├─ TreeSelect: 604|679 (종이종류)
├─ POtype: 1|2 (인쇄면)
├─ quantity: 10,20,50,100 (수량)
└─ DesignMoney: 30000 (편집비)
```

### JOIN 쿼리 예시

```sql
SELECT DISTINCT 
    lt.style as value,
    COALESCE(tc.title, lt.style) as text
FROM mlangprintauto_littleprint lt 
LEFT JOIN mlangprintauto_transactioncate tc ON tc.no = lt.style
WHERE lt.style IS NOT NULL 
ORDER BY lt.style
```

## 🎭 컨텍스트 매핑 시스템

### 동일한 필드, 다른 의미

```php
// PN_type 필드가 제품별로 다른 의미
'poster' => [
    'PN_type' => ['label' => '종이규격', 'icon' => '📏', 'type' => 'size']
],
'namecard' => [
    'PN_type' => ['label' => '명함재질', 'icon' => '🏷️', 'type' => 'material']
],
'coupon' => [
    'PN_type' => ['label' => '규격선택', 'icon' => '📏', 'type' => 'size']
]
```

### POtype 컨텍스트별 해석

```php
// 포스터/전단지/명함: 인쇄면
'sides' => ['1' => '단면 (앞면만)', '2' => '양면 (앞뒤 모두)']

// 봉투/양식지: 인쇄색상  
'color' => ['1' => '1도 (흑백)', '2' => '2도 (2색)', '3' => '3도 (3색)']

// 쿠폰: 후가공
'finishing' => ['1' => '후가공 없음', '2' => '코팅', '3' => '특수 후가공']
```

## 🔍 디버깅

### 1. 디버그 모드 활성화

```php
// URL에 ?debug=1 추가하면 디버그 정보 표시
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    echo $smartComponent->debugComponent();
    echo ProductFieldMapper::debugProductMapping('poster');
}
```

### 2. 필드 매핑 상태 확인

```php
// 특정 제품의 활성 필드 확인
$active_fields = ProductFieldMapper::getActiveFields('poster');
print_r($active_fields);

// 필드 컨텍스트 확인  
$context = ProductFieldMapper::getFieldContext('poster', 'PN_type');
print_r($context);
```

### 3. 데이터베이스 연결 테스트

```php
// 컴포넌트 상태 확인
echo $smartComponent->debugComponent();
```

## ⚠️ 주의사항

### 1. 데이터베이스 연결 필수
```php
// 반드시 유효한 mysqli 연결 객체 전달
$smartComponent = new SmartFieldComponent($db, 'product_code');
```

### 2. 제품 코드 정확성
```php
// 지원하는 제품 코드만 사용
$valid_products = ProductFieldMapper::getAllProductCodes();
// ['poster', 'leaflet', 'namecard', 'coupon', 'envelope', 'form', 'magnetic_sticker', 'catalog']
```

### 3. JavaScript 라이브러리 포함
```html
<!-- UniversalPriceHandler 사용 시 필수 -->
<script src="includes/js/UniversalPriceHandler.js"></script>
```

## 🎉 예제 결과

### Before (기존 시스템)
```html
<select name="MY_type">
    <option value="590">590</option>  ❌ 의미 불명
</select>
```

### After (스마트 컴포넌트)
```html
<label>🎨 구분 (포스터 종류 구분)</label>
<select name="MY_type" class="smart-field" data-product="poster">
    <option value="590">소량포스터</option>  ✅ 명확한 의미
</select>
```

## 📞 지원

- **개발자**: AI Assistant (Kiro)
- **버전**: v2.0 (2025년 8월)
- **상태**: Production Ready ✅

---

**"현실을 받아들이되, 스마트하게 극복하자!"** 🚀