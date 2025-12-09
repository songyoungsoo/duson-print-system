# 옵션추가 시스템 완성 가이드 📋

## 📊 프로젝트 개요
**목표**: 전단지(inserted) 페이지에 코팅, 접지, 오시 추가 옵션 시스템 구축  
**완성일**: 2025-01-08  
**구현 방식**: 모듈형 아키텍처로 다른 제품 페이지에서도 재사용 가능  

---

## 🚨 발생한 주요 오류들과 해결책

### 1. 장바구니 추가 오류 (Database Column Missing)
**❌ 오류 증상**:
```
장바구니 추가 중 오류가 발생했습니다: 데이터베이스 준비 오류: 
Unknown column 'coating_enabled' in 'field list'
```

**🔍 원인**: 추가 옵션 필드들이 데이터베이스 테이블에 존재하지 않음

**✅ 해결책**: 데이터베이스 스키마 업데이트
```sql
-- shop_temp 테이블 (임시 장바구니)
ALTER TABLE shop_temp ADD COLUMN coating_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE shop_temp ADD COLUMN coating_type VARCHAR(50);
ALTER TABLE shop_temp ADD COLUMN coating_price INT DEFAULT 0;
ALTER TABLE shop_temp ADD COLUMN folding_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE shop_temp ADD COLUMN folding_type VARCHAR(50);
ALTER TABLE shop_temp ADD COLUMN folding_price INT DEFAULT 0;
ALTER TABLE shop_temp ADD COLUMN creasing_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE shop_temp ADD COLUMN creasing_lines INT DEFAULT 0;
ALTER TABLE shop_temp ADD COLUMN creasing_price INT DEFAULT 0;
ALTER TABLE shop_temp ADD COLUMN additional_options_total INT DEFAULT 0;

-- mlangorder_printauto 테이블 (최종 주문)
ALTER TABLE mlangorder_printauto ADD COLUMN coating_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE mlangorder_printauto ADD COLUMN coating_type VARCHAR(50);
ALTER TABLE mlangorder_printauto ADD COLUMN coating_price INT DEFAULT 0;
ALTER TABLE mlangorder_printauto ADD COLUMN folding_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE mlangorder_printauto ADD COLUMN folding_type VARCHAR(50);
ALTER TABLE mlangorder_printauto ADD COLUMN folding_price INT DEFAULT 0;
ALTER TABLE mlangorder_printauto ADD COLUMN creasing_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE mlangorder_printauto ADD COLUMN creasing_lines INT DEFAULT 0;
ALTER TABLE mlangorder_printauto ADD COLUMN creasing_price INT DEFAULT 0;
ALTER TABLE mlangorder_printauto ADD COLUMN additional_options_total INT DEFAULT 0;
```

### 2. PHP Fatal Error (참조 전달 오류)
**❌ 오류 증상**:
```php
PHP Fatal Error: Only variables can be passed by reference 
in add_to_basket.php on line 218
```

**🔍 원인**: 문자열 리터럴 'leaflet'를 mysqli_stmt_bind_param()에 직접 참조로 전달

**✅ 해결책**: 문자열을 변수에 저장 후 전달
```php
// ❌ 잘못된 코드
mysqli_stmt_bind_param($stmt, "ssssssssddssssisissiiiii", 
    $session_id, 'leaflet', $MY_type, ...);

// ✅ 수정된 코드
$product_type = 'leaflet'; // 변수로 저장
mysqli_stmt_bind_param($stmt, "ssssssssddssssisissiiiii", 
    $session_id, $product_type, $MY_type, ...);
```

### 3. 수량 변경 시 검증 오류 (Quantity Change Alert)
**❌ 오류 증상**:
```
수량 드롭다운 변경 시 "모든 옵션을 선택해주세요" 경고창 표시
```

**🔍 원인**: 추가 옵션 시스템이 calculatePrice() 함수를 자동 호출할 때 검증 모드로 실행

**✅ 해결책**: 자동 호출 시 isAuto 플래그 사용
```javascript
// ❌ 문제 코드 (검증 모드로 실행)
if (typeof calculatePrice === 'function') {
    calculatePrice(); // 검증 경고창 발생
}

// ✅ 수정 코드 (자동 모드로 실행)
if (typeof calculatePrice === 'function') {
    calculatePrice(true); // isAuto = true로 alert 방지
}
```

### 4. 매개변수 바인딩 오류 (Parameter Binding Mismatch)
**❌ 오류 증상**:
```
Parameter binding mismatch - 실제 파라미터 수와 타입 문자열 불일치
```

**🔍 원인**: 추가된 10개 옵션 필드로 인한 매개변수 개수 변경

**✅ 해결책**: 타입 문자열 수정 (14개 → 24개 매개변수)
```php
// ❌ 기존 (14개 매개변수)
mysqli_stmt_bind_param($stmt, "ssssssssddsss", ...);

// ✅ 수정 (24개 매개변수)
mysqli_stmt_bind_param($stmt, "ssssssssddssssisissiiiii", 
    $session_id, $product_type, $MY_type, $PD_type, $MY_Fsd, 
    $PN_type, $MY_amount, $ordertype, $Total_amount, $designfee, 
    $Total_amount_DesignFee, $printtype, $GangRun, $pageContents,
    $coating_enabled, $coating_type, $coating_price,
    $folding_enabled, $folding_type, $folding_price,
    $creasing_enabled, $creasing_lines, $creasing_price,
    $additional_options_total
);
```

---

## 🗂️ 데이터베이스 스키마 변경사항

### shop_temp 테이블 (임시 장바구니)
| 필드명 | 타입 | 기본값 | 설명 |
|--------|------|--------|------|
| `coating_enabled` | TINYINT(1) | 0 | 코팅 옵션 활성화 |
| `coating_type` | VARCHAR(50) | NULL | 코팅 종류 (single/double/single_matte/double_matte) |
| `coating_price` | INT | 0 | 코팅 가격 |
| `folding_enabled` | TINYINT(1) | 0 | 접지 옵션 활성화 |
| `folding_type` | VARCHAR(50) | NULL | 접지 종류 (2fold/3fold/accordion/gate) |
| `folding_price` | INT | 0 | 접지 가격 |
| `creasing_enabled` | TINYINT(1) | 0 | 오시 옵션 활성화 |
| `creasing_lines` | INT | 0 | 오시 줄 수 (1/2/3) |
| `creasing_price` | INT | 0 | 오시 가격 |
| `additional_options_total` | INT | 0 | 추가 옵션 총액 |

### mlangorder_printauto 테이블 (최종 주문)
**동일한 10개 필드를 추가** (shop_temp와 구조 일치)

---

## 🔧 코드 변경사항

### 1. 백엔드 PHP 파일들

#### `includes/AdditionalOptions.php` (신규 생성)
**목적**: 추가 옵션 시스템의 핵심 모듈형 클래스
```php
class AdditionalOptions {
    // 가격 구조 (1연 기준)
    private $BASE_PRICES = [
        'coating' => [
            'single' => 80000,        // 단면유광코팅
            'double' => 160000,       // 양면유광코팅
            'single_matte' => 90000,  // 단면무광코팅
            'double_matte' => 180000  // 양면무광코팅
        ],
        'folding' => [
            '2fold' => 40000,     // 2단접지
            '3fold' => 40000,     // 3단접지
            'accordion' => 60000, // 병풍접지
            'gate' => 100000      // 대문접지
        ],
        'creasing' => [
            '1line' => 40000,  // 1줄 오시
            '2line' => 40000,  // 2줄 오시
            '3line' => 45000   // 3줄 오시
        ]
    ];
}
```

#### `MlangPrintAuto/inserted/add_to_basket.php` 수정
**주요 변경사항**:
1. **매개변수 개수 증가**: 14개 → 24개
2. **참조 전달 오류 수정**: 'leaflet' → $product_type 변수
3. **추가 옵션 데이터 처리** 로직 추가

```php
// 추가 옵션 데이터 수집
$coating_enabled = isset($_POST['coating_enabled']) ? 1 : 0;
$coating_type = $_POST['coating_type'] ?? null;
$coating_price = (int)($_POST['coating_price'] ?? 0);
// ... 나머지 옵션들
```

#### `MlangPrintAuto/inserted/index.php` 수정
**주요 변경사항**:
1. **AdditionalOptions 클래스 include**
2. **HTML 폼에 추가 옵션 섹션 삽입**
3. **JavaScript 파일 로딩**

```php
<?php include_once '../../includes/AdditionalOptions.php'; ?>
<!-- HTML 폼 내부에 추가 -->
<?php
$additionalOptions = new AdditionalOptions($connect);
echo $additionalOptions->generateOptionsHtml('inserted');
?>
```

### 2. 프론트엔드 JavaScript 파일들

#### `MlangPrintAuto/inserted/js/additional-options.js` (신규 생성)
**목적**: 실시간 가격 계산 및 UI 관리
```javascript
class AdditionalOptionsManager {
    constructor() {
        this.basePrices = {
            coating: {
                single: 80000, double: 160000,
                single_matte: 90000, double_matte: 180000
            },
            folding: {
                '2fold': 40000, '3fold': 40000,
                'accordion': 60000, 'gate': 100000
            },
            creasing: { 1: 40000, 2: 40000, 3: 45000 }
        };
    }
}
```

#### `MlangPrintAuto/inserted/js/leaflet-compact.js` 수정
**주요 변경사항**:
1. **수량 변경 감지 로직 개선**
2. **자동 계산 시 검증 모드 비활성화**
3. **이벤트 리스너 충돌 방지**

```javascript
// 수량 변경 시 조건부 계산
if (formData.get('MY_type') && formData.get('MY_Fsd') && 
    formData.get('PN_type') && formData.get('MY_amount') && 
    formData.get('ordertype')) {
    console.log('📊 수량 변경 → 자동 가격 계산 실행');
    calculatePrice(true); // isAuto = true로 alert 방지
}
```

### 3. CSS 스타일링

#### `css/common-styles.css` 추가
```css
/* 한 줄 체크박스 레이아웃 */
.option-headers-row {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 5px;
}

.option-checkbox-group {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* 옵션 상세 드롭다운 */
.option-details {
    margin-left: 20px;
    margin-bottom: 5px;
}

.option-select {
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 13px;
}
```

---

## 🎯 완성된 기능들

### ✅ 1. 사용자 인터페이스
- **한 줄 체크박스 레이아웃**: 코팅, 접지, 오시 옵션이 한 줄에 배치
- **실시간 가격 표시**: 옵션 선택 시 즉시 가격 업데이트
- **반응형 디자인**: 모바일에서도 최적화된 레이아웃

### ✅ 2. 가격 계산 시스템
- **연수 기반 계산**: 1000매 = 1연 기준, 0.5연 이하는 1연 가격
- **실시간 업데이트**: 수량 변경 시 자동 재계산
- **총액 표시**: 메인 가격에 추가 옵션 가격 자동 합산

### ✅ 3. 장바구니 연동
- **완전한 데이터 전달**: 모든 옵션 정보가 장바구니로 전달
- **세션 관리**: 사용자별 개별 장바구니 관리
- **최종 주문 연동**: shop_temp → mlangorder_printauto 데이터 흐름

### ✅ 4. 모듈화 아키텍처
- **재사용 가능**: 다른 제품 페이지에서도 사용 가능
- **확장 가능**: 새로운 옵션 추가가 용이한 구조
- **유지보수 용이**: 중앙집중식 가격 관리

---

## 🔧 다음 제품 페이지 적용 가이드

### 1. 기본 요구사항
- AdditionalOptions.php 클래스 사용 (이미 완성)
- additional-options.js 파일 포함
- 데이터베이스 테이블에 10개 옵션 필드 추가

### 2. 적용 단계
```php
// 1. PHP 파일에서 클래스 include
<?php include_once '../../includes/AdditionalOptions.php'; ?>

// 2. HTML 폼에 옵션 섹션 추가
<?php
$additionalOptions = new AdditionalOptions($connect);
echo $additionalOptions->generateOptionsHtml('제품명');
?>

// 3. JavaScript 파일 로딩
<script src="js/additional-options.js"></script>

// 4. add_to_basket.php 수정 (매개변수 24개로 확장)
```

### 3. 데이터베이스 스키마
**각 제품별 테이블에 동일한 10개 필드 추가 필요**

---

## 🎉 최종 결과

### 성공 지표
- ✅ **오류 0개**: 모든 장바구니 오류 해결
- ✅ **검증 통과**: 수량 변경 시 오류 메시지 해결
- ✅ **가격 정확성**: 백엔드-프론트엔드 가격 동기화 완료
- ✅ **사용자 경험**: 직관적이고 간편한 옵션 선택 UI
- ✅ **확장성**: 다른 제품 페이지 적용 가능한 모듈형 구조

### 테스트 완료 항목
1. **옵션 선택 → 가격 계산** ✅
2. **수량 변경 → 자동 재계산** ✅  
3. **장바구니 추가 → 데이터 저장** ✅
4. **최종 주문 → 데이터 전달** ✅

---

## 📚 참고 파일 위치
```
C:\xampp\htdocs\
├── includes\AdditionalOptions.php              # 핵심 클래스
├── MlangPrintAuto\inserted\
│   ├── index.php                              # 메인 페이지 (수정)
│   ├── add_to_basket.php                      # 장바구니 처리 (수정)
│   └── js\
│       ├── additional-options.js              # 옵션 관리 JS
│       └── leaflet-compact.js                 # 메인 계산 JS (수정)
└── css\common-styles.css                      # 공통 스타일 (추가)
```

---
**📅 작업 완료일**: 2025-01-08  
**🔗 테스트 URL**: http://localhost/mlangprintauto/inserted/  
**💡 핵심 키워드**: 모듈형 추가 옵션 시스템, 실시간 가격 계산, 장바구니 연동