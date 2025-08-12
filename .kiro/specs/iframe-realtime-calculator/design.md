# Design Document

## Overview

iframe 기반 실시간 가격 계산 시스템은 숨겨진 iframe을 활용하여 비동기적으로 서버와 통신하면서도 부모 창의 DOM을 직접 조작할 수 있는 하이브리드 패턴입니다. 이 방식은 AJAX보다 더 넓은 브라우저 호환성을 제공하면서도 실시간 반응성을 보장합니다.

## Architecture

### 전체 시스템 구조
```
[메인 페이지 (index.php)]
         ↓ 사용자 옵션 선택
[JavaScript 이벤트 핸들러]
         ↓ URL 생성 및 호출
[숨겨진 iframe (name="cal")]
         ↓ 서버 요청
[가격 계산 서버 (price_cal.php)]
         ↓ DB 조회 및 계산
[JavaScript 응답 (parent.document 조작)]
         ↓ DOM 업데이트
[메인 페이지 가격 필드 업데이트]
```

### 핵심 컴포넌트
1. **메인 페이지**: 사용자 인터페이스와 폼
2. **숨겨진 iframe**: 비동기 통신 채널
3. **가격 계산 서버**: 독립적인 계산 로직
4. **AJAX 옵션 로더**: 동적 옵션 업데이트

## Product Classification

### 품목별 인쇄면 옵션 분류

#### 양면/단면 선택 가능 품목:
- 전단지 (inserted)
- 명함 (NameCard)  
- 리플렛 (cadarok)
- 카탈로그 (cadarokTwo)
- 포스터
- 상품권 (MerchandiseBond)

#### 단면 인쇄만 가능한 품목:
- 서식/양식 (NcrFlambeau)
- 상장
- 봉투 (envelope)
- 종이자석
- 스티커 (msticker)

## Components and Interfaces

### 1. 메인 페이지 컴포넌트 (index.php)

#### HTML 구조
```html
<!-- 숨겨진 iframe 설정 -->
<iframe name="cal" frameborder="0" width="0" height="0"></iframe>

<!-- 메인 폼 -->
<form name="choiceForm" method="post">
    <select name="MY_type" onchange="change_Field(this.value)">
    <select name="MY_Fsd" onchange="calc_re();">
    <select name="PN_type" onchange="calc_re();">
    <select name="MY_amount" onchange="calc_ok();">
    
    <!-- 가격 표시 필드 -->
    <input type="text" name="Price" readonly>
    <input type="text" name="DS_Price" readonly>
    <input type="text" name="Order_Price" readonly>
</form>
```

#### JavaScript 함수들
```javascript
// 즉시 가격 계산
function calc_ok() {
    var form = document.forms["choiceForm"];
    var url = 'price_cal.php?' + 
              'MY_type=' + form.MY_type.value + 
              '&PN_type=' + form.PN_type.value + 
              '&MY_Fsd=' + form.MY_Fsd.value + 
              '&MY_amount=' + form.MY_amount.value + 
              '&POtype=1&ordertype=' + form.ordertype.value;
    
    console.log("가격 계산 URL:", url);
    cal.document.location.href = url;
}

// 지연된 가격 계산 (옵션 변경 후)
function calc_re() {
    setTimeout(function() {
        calc_ok();
    }, 100);
}

// 상위 옵션 변경 시 하위 옵션 업데이트
function change_Field(val) {
    updateSubOptions(val);
    setTimeout(calc_ok, 200);
}
```

### 2. 가격 계산 서버 컴포넌트 (price_cal.php)

#### 데이터 처리 흐름
```php
// 1. 파라미터 수신 및 검증
$MY_type = $_GET['MY_type'] ?? '';
$PN_type = $_GET['PN_type'] ?? '';
$MY_Fsd = $_GET['MY_Fsd'] ?? '';
$MY_amount = $_GET['MY_amount'] ?? '';

// 2. 수량 매핑 (UI 값 → DB 값)
$quantity_map = [
    '1000' => '1', '2000' => '2', '3000' => '3',
    '4000' => '4', '5000' => '5', '기타' => '1'
];
$mapped_quantity = $quantity_map[$MY_amount] ?? $MY_amount;

// 3. 데이터베이스 조회
$query = "SELECT * FROM price_table 
          WHERE style='$MY_type' 
            AND Section='$MY_Fsd' 
            AND quantity='$mapped_quantity' 
            AND TreeSelect='$PN_type' 
            AND POtype='$POtype'";

// 4. 가격 계산
if ($row = mysqli_fetch_array(mysqli_query($db, $query))) {
    $price = calculatePrice($row, $ordertype);
    outputSuccessScript($price);
} else {
    outputErrorScript();
}
```

#### JavaScript 응답 생성
```php
function outputSuccessScript($priceData) {
    echo "<script>";
    echo "console.log('가격 계산 성공');";
    echo "parent.document.forms['choiceForm'].Price.value = '{$priceData['formatted_price']}';";
    echo "parent.document.forms['choiceForm'].DS_Price.value = '{$priceData['design_price']}';";
    echo "parent.document.forms['choiceForm'].Order_Price.value = '{$priceData['total_price']}';";
    echo "</script>";
}
```

### 3. AJAX 옵션 로더 컴포넌트

#### 동적 옵션 업데이트
```javascript
function updateSubOptions(parentValue) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var options = JSON.parse(xhr.responseText);
            updateSelectOptions('MY_Fsd', options);
            updatePaperTypes(parentValue);
        }
    };
    xhr.open("GET", "get_sizes.php?CV_no=" + parentValue, true);
    xhr.send();
}
```

#### 서버 측 옵션 제공
```php
// get_sizes.php
$result = mysqli_query($db, "SELECT * FROM options WHERE parent_id='$parent_id'");
$options = [];
while ($row = mysqli_fetch_array($result)) {
    $options[] = ['no' => $row['no'], 'title' => $row['title']];
}
header('Content-Type: application/json');
echo json_encode($options);
```

## Data Models

### 가격 데이터 모델
```sql
CREATE TABLE price_table (
    no INT PRIMARY KEY,
    style VARCHAR(100),      -- 상품 구분
    Section VARCHAR(200),    -- 규격/크기
    quantity FLOAT,          -- 수량 (1,2,3...)
    TreeSelect INT,          -- 재료/종이종류
    POtype VARCHAR(50),      -- 처리 타입
    money VARCHAR(200),      -- 기본 가격
    DesignMoney INT,         -- 디자인 비용
    quantityTwo VARCHAR(100) -- 추가 정보
);
```

### 옵션 데이터 모델
```sql
CREATE TABLE option_categories (
    no INT PRIMARY KEY,
    title VARCHAR(200),      -- 옵션명
    Ttable VARCHAR(50),      -- 테이블 구분
    BigNo VARCHAR(50),       -- 상위 카테고리
    TreeNo VARCHAR(50)       -- 연관 카테고리
);
```

## Error Handling

### 클라이언트 측 오류 처리
```javascript
// 네트워크 오류 감지
function handleCalculationError() {
    console.error("가격 계산 실패");
    document.forms["choiceForm"].Price.value = "";
    alert("가격 정보를 불러올 수 없습니다. 다시 시도해주세요.");
}

// 타임아웃 처리
setTimeout(function() {
    if (!calculationCompleted) {
        handleCalculationError();
    }
}, 5000);
```

### 서버 측 오류 처리
```php
// 데이터 없음 처리
if (!$row) {
    error_log("No price data found: style=$MY_type, Section=$MY_Fsd");
    echo "<script>";
    echo "console.log('가격 정보 없음');";
    echo "parent.document.forms['choiceForm'].Price.value = '';";
    echo "alert('해당 조건의 가격 정보가 없습니다.');";
    echo "</script>";
    exit;
}

// 데이터베이스 연결 오류
if (!$db) {
    error_log("Database connection failed");
    echo "<script>alert('시스템 오류가 발생했습니다.');</script>";
    exit;
}
```

## Testing Strategy

### 단위 테스트
1. **가격 계산 로직 테스트**
   - 다양한 옵션 조합에 대한 정확한 가격 계산
   - 수량 매핑 함수 검증
   - 할인/부가세 계산 정확성

2. **AJAX 통신 테스트**
   - 옵션 로딩 성공/실패 시나리오
   - JSON 파싱 오류 처리
   - 네트워크 타임아웃 처리

### 통합 테스트
1. **전체 플로우 테스트**
   - 사용자 옵션 선택 → 가격 업데이트 전체 과정
   - 여러 옵션 연속 변경 시 정확성
   - 브라우저 호환성 테스트

2. **성능 테스트**
   - 동시 다중 사용자 가격 계산
   - 대용량 옵션 데이터 로딩
   - 메모리 누수 검사

### 사용자 테스트
1. **사용성 테스트**
   - 직관적인 옵션 선택 플로우
   - 가격 업데이트 반응 속도
   - 오류 메시지 명확성

## Performance Considerations

### 최적화 전략
1. **캐싱**: 자주 사용되는 옵션 데이터 캐싱
2. **지연 로딩**: 필요한 시점에만 옵션 로드
3. **디바운싱**: 연속된 옵션 변경 시 마지막 요청만 처리
4. **인덱싱**: 데이터베이스 조회 성능 최적화

### 확장성 고려사항
1. **모듈화**: 계산 로직의 독립적 모듈화
2. **API화**: REST API로 확장 가능한 구조
3. **캐시 전략**: Redis/Memcached 활용 방안
4. **로드 밸런싱**: 다중 서버 환경 대응